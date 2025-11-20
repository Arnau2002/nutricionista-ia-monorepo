import pandas as pd
import os
from sqlalchemy import create_engine, text
from qdrant_client import QdrantClient
from qdrant_client.http import models
from sentence_transformers import SentenceTransformer

# --- CONFIGURACIÓN ---
DB_USER = os.getenv('DB_USER', 'root')
DB_PASS = os.getenv('DB_PASS', 'tu_password_segura') 
DB_HOST = os.getenv('DB_HOST', 'db-tiendas')
DB_NAME = os.getenv('DB_NAME', 'precios_comparados')
DB_URL = f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}"

# Configuración Qdrant (Aumentamos timeout por seguridad)
QDRANT_HOST = os.getenv('QDRANT_HOST', 'vector-db')
QDRANT_PORT = 6333
COLLECTION_NAME = "productos_supermercado"

def crear_vistas_automaticas(connection):
    print("👓 Creando/Actualizando Vistas SQL automáticas...")
    sql_enriched = """
    CREATE OR REPLACE VIEW price_obs_enriched AS
    SELECT
      po.price_obs_id, po.product_id, po.store_id, po.price_total, po.currency,
      po.observed_at, po.source, p.uom_code, p.pack_qty, u.base_type, u.to_base,
      CASE
        WHEN u.base_type IN ('mass', 'volume') THEN po.price_total / NULLIF(p.pack_qty * u.to_base, 0)
        ELSE po.price_total / NULLIF(p.pack_qty, 0)
      END AS price_per_base
    FROM price_observation po
    JOIN product p ON p.product_id = po.product_id
    JOIN uom u ON u.uom_code = p.uom_code;
    """
    connection.execute(text(sql_enriched))

    sql_latest = """
    CREATE OR REPLACE VIEW latest_price AS
    WITH ranked AS (
      SELECT
        poe.price_obs_id, poe.product_id, poe.store_id, poe.price_total, poe.price_per_base, poe.observed_at,
        ROW_NUMBER() OVER (PARTITION BY poe.product_id, poe.store_id ORDER BY poe.observed_at DESC) AS rn
      FROM price_obs_enriched poe
    )
    SELECT r.product_id, p.name as producto, r.store_id, r.price_total as precio_actual,
      r.price_per_base as precio_unidad_real, r.observed_at as fecha
    FROM ranked r
    JOIN product p ON p.product_id = r.product_id
    WHERE r.rn = 1;
    """
    connection.execute(text(sql_latest))

def cargar_datos():
    print("🚀 Iniciando Carga Híbrida (SQL + Vectorial)...")
    
    # 1. Conexiones
    try:
        engine = create_engine(DB_URL)
        # Cliente Qdrant con timeout extendido
        qdrant = QdrantClient(host=QDRANT_HOST, port=QDRANT_PORT, timeout=60)
        
        print("🧠 Cargando modelo de IA...")
        encoder = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
        print("✅ Conexiones y Modelo listos.")
    except Exception as e:
        print(f"❌ Error de inicialización: {e}")
        return

    # 2. Preparar Qdrant
    try:
        qdrant.get_collection(COLLECTION_NAME)
    except:
        print(f"📦 Creando colección vectorial: {COLLECTION_NAME}")
        qdrant.create_collection(
            collection_name=COLLECTION_NAME,
            vectors_config=models.VectorParams(size=384, distance=models.Distance.COSINE)
        )

    # 3. Cargar CSV
    try:
        df = pd.read_csv("export/productos_limpios_estandarizados.csv")
    except FileNotFoundError:
        print("❌ No hay CSV. Ejecuta clean_data.py")
        return

    # 4. Proceso de Carga SQL
    count_nuevos = 0
    vectors_to_upload = []
    payloads_to_upload = []
    ids_to_upload = []

    print("🔄 Insertando en MySQL y preparando vectores...")
    with engine.begin() as connection:
        # Inicializar Maestros
        for cadena in ['Mercadona', 'Dia']:
            connection.execute(text("INSERT IGNORE INTO chain (name) VALUES (:n)"), {"n": cadena})
            cid = connection.execute(text("SELECT chain_id FROM chain WHERE name = :n"), {"n": cadena}).scalar()
            connection.execute(text(f"INSERT IGNORE INTO store (chain_id, name) VALUES ({cid}, '{cadena} Online')"))

        # Mapeo de IDs de tiendas para ir rápido
        store_map = {}
        for tienda in ['Mercadona', 'Dia']:
            sid = connection.execute(text(f"SELECT s.store_id FROM store s JOIN chain c ON s.chain_id = c.chain_id WHERE c.name = '{tienda}' LIMIT 1")).scalar()
            store_map[tienda] = sid

        for idx, row in df.iterrows():
            # Normalizar tienda (asegurar coincidencia con 'Mercadona'/'Dia')
            tienda_nombre = 'Dia' if str(row['tienda']).lower() == 'dia' else 'Mercadona'
            store_id = store_map.get(tienda_nombre)
            
            if not store_id: continue # Skip si falla algo raro

            # UOM
            uom = str(row['unidad_medida']).lower().strip() if pd.notna(row['unidad_medida']) else 'ud'
            base_type = 'unit'
            if uom in ['l', 'ml', 'cl']: base_type = 'volume'
            elif uom in ['kg', 'g', 'mg']: base_type = 'mass'
            
            to_base = 1.0
            if uom == 'ml': to_base = 0.001
            if uom == 'g': to_base = 0.001

            connection.execute(text("INSERT IGNORE INTO category (name) VALUES (:name)"), {"name": row['categoria']})
            cat_id = connection.execute(text("SELECT category_id FROM category WHERE name = :name"), {"name": row['categoria']}).scalar()
            
            connection.execute(text("INSERT IGNORE INTO uom (uom_code, to_base, base_type) VALUES (:u, :t, :b)"), 
                             {"u": uom, "t": to_base, "b": base_type})

            # Producto (Insertar si no existe)
            prod_id = connection.execute(text("SELECT product_id FROM product WHERE name = :name"), {"name": row['nombre_estandar']}).scalar()
            
            es_nuevo = False
            if not prod_id:
                result = connection.execute(text("INSERT INTO product (name, category_id, uom_code) VALUES (:name, :cat, :uom)"),
                                          {"name": row['nombre_estandar'], "cat": cat_id, "uom": uom})
                prod_id = result.lastrowid
                es_nuevo = True
                count_nuevos += 1

            # Precio
            if pd.notna(row['precio_actual']):
                connection.execute(text("INSERT INTO price_observation (product_id, store_id, price_total) VALUES (:pid, :sid, :p)"),
                                 {"pid": prod_id, "sid": store_id, "p": row['precio_actual']})

            # Recolectar para vectorizar (solo si es nuevo o quieres forzar update)
            # NOTA: Para este fix, asumimos que si count_nuevos es 0, tal vez quieras forzar vectorización.
            # Pero lo dejamos solo para nuevos para ser eficientes.
            if es_nuevo: 
                texto = f"{row['nombre_estandar']} categoria: {row['categoria']}"
                vectors_to_upload.append(texto)
                ids_to_upload.append(prod_id)
                payloads_to_upload.append({
                    "product_id": prod_id,
                    "nombre": row['nombre_estandar'],
                    "categoria": row['categoria'],
                    "tienda": tienda_nombre
                })

        crear_vistas_automaticas(connection)

    # 5. VECTORIZACIÓN Y SUBIDA POR LOTES (BATCHING)
    if vectors_to_upload:
        print(f"🧠 Generando Embeddings para {len(vectors_to_upload)} productos...")
        # Convertimos texto a números (esto es rápido, se puede hacer de golpe o en lotes grandes)
        embeddings = encoder.encode(vectors_to_upload).tolist()
        
        print(f"🚀 Subiendo a Qdrant en lotes pequeños...")
        
        # TAMAÑO DEL LOTE (BATCH SIZE) - Clave para evitar Timeouts
        BATCH_SIZE = 250 
        total = len(ids_to_upload)
        
        for i in range(0, total, BATCH_SIZE):
            fin = min(i + BATCH_SIZE, total)
            print(f"   📦 Subiendo lote {i} a {fin} de {total}...")
            
            try:
                qdrant.upsert(
                    collection_name=COLLECTION_NAME,
                    points=models.Batch(
                        ids=ids_to_upload[i:fin],
                        vectors=embeddings[i:fin],
                        payloads=payloads_to_upload[i:fin]
                    ),
                    wait=True
                )
            except Exception as e:
                print(f"   ⚠️ Error subiendo lote {i}: {e}")

    print(f"🎉 Carga Finalizada. {count_nuevos} productos nuevos procesados.")

if __name__ == "__main__":
    cargar_datos()