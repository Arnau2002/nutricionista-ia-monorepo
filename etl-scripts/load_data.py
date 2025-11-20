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

# Configuración Qdrant
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
        qdrant = QdrantClient(host=QDRANT_HOST, port=QDRANT_PORT)
        
        # Modelo de IA para convertir texto a vector (multilingüe)
        print("🧠 Cargando modelo de IA (esto puede tardar un poco)...")
        encoder = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
        
        print("✅ Conexiones y Modelo listos.")
    except Exception as e:
        print(f"❌ Error de inicialización: {e}")
        return

    # 2. Preparar Qdrant (Crear colección si no existe)
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

    # 4. Proceso de Carga
    count_nuevos = 0
    vectors_to_upload = []
    payloads_to_upload = []
    ids_to_upload = []

    with engine.begin() as connection:
        # Setup inicial de SQL (Chain, Store...)
        connection.execute(text("INSERT IGNORE INTO chain (name) VALUES ('Mercadona')"))
        chain_id = connection.execute(text("SELECT chain_id FROM chain WHERE name = 'Mercadona'")).scalar()
        connection.execute(text(f"INSERT IGNORE INTO store (chain_id, name) VALUES ({chain_id}, 'Mercadona Online')"))
        store_id = connection.execute(text(f"SELECT store_id FROM store WHERE chain_id = {chain_id} LIMIT 1")).scalar()

        print("🔄 Procesando productos...")
        
        for idx, row in df.iterrows():
            # --- LÓGICA SQL ---
            uom = str(row['unidad_medida']).lower().strip() if pd.notna(row['unidad_medida']) else 'ud'
            base_type = 'unit'
            if uom in ['l', 'ml', 'cl']: base_type = 'volume'
            elif uom in ['kg', 'g', 'mg']: base_type = 'mass'
            
            to_base = 1.0
            if uom == 'ml': to_base = 0.001
            if uom == 'g': to_base = 0.001

            # Categoría y Unidad
            connection.execute(text("INSERT IGNORE INTO category (name) VALUES (:name)"), {"name": row['categoria']})
            cat_id = connection.execute(text("SELECT category_id FROM category WHERE name = :name"), {"name": row['categoria']}).scalar()
            
            connection.execute(text("INSERT IGNORE INTO uom (uom_code, to_base, base_type) VALUES (:u, :t, :b)"), 
                             {"u": uom, "t": to_base, "b": base_type})

            # Producto
            prod_id = connection.execute(text("SELECT product_id FROM product WHERE name = :name"), {"name": row['nombre_estandar']}).scalar()
            
            es_nuevo = False
            if not prod_id:
                result = connection.execute(text("INSERT INTO product (name, category_id, uom_code) VALUES (:name, :cat, :uom)"),
                                          {"name": row['nombre_estandar'], "cat": cat_id, "uom": uom})
                prod_id = result.lastrowid
                es_nuevo = True
                count_nuevos += 1

            # Precio
            connection.execute(text("INSERT INTO price_observation (product_id, store_id, price_total) VALUES (:pid, :sid, :p)"),
                             {"pid": prod_id, "sid": store_id, "p": row['precio_actual']})

            # --- LÓGICA VECTORIAL ---
            # Solo vectorizamos si es un producto nuevo o si queremos actualizar todo
            # Preparamos los datos para subir a Qdrant en lotes
            if es_nuevo: 
                # El texto que la IA "leerá" para entender el producto
                texto_a_vectorizar = f"{row['nombre_estandar']} categoria: {row['categoria']}"
                
                # Guardamos para procesar en lote después
                vectors_to_upload.append(texto_a_vectorizar)
                ids_to_upload.append(prod_id)
                payloads_to_upload.append({
                    "product_id": prod_id,
                    "nombre": row['nombre_estandar'],
                    "categoria": row['categoria'],
                    "tienda": "Mercadona"
                })

        # 5. Subida Masiva a Qdrant (Más eficiente)
        if vectors_to_upload:
            print(f"🧠 Vectorizando {len(vectors_to_upload)} productos nuevos...")
            embeddings = encoder.encode(vectors_to_upload).tolist()
            
            print("asd Subiendo a Qdrant...")
            qdrant.upsert(
                collection_name=COLLECTION_NAME,
                points=models.Batch(
                    ids=ids_to_upload,
                    vectors=embeddings,
                    payloads=payloads_to_upload
                )
            )

        crear_vistas_automaticas(connection)

    print(f"🎉 Carga Híbrida Finalizada. {count_nuevos} productos nuevos vectorizados.")

if __name__ == "__main__":
    cargar_datos()