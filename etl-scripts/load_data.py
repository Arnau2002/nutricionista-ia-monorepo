import pandas as pd
import os
import uuid
from sqlalchemy import create_engine, text
from qdrant_client import QdrantClient
from qdrant_client.http import models
from sentence_transformers import SentenceTransformer

# --- CONFIGURACIÓN CORREGIDA PARA WINDOWS ---
DB_USER = os.getenv('DB_USER', 'root')
DB_PASS = os.getenv('DB_PASS', 'password_segura') 

# Usamos 'localhost' para que funcione desde tu terminal
DB_HOST = 'localhost' 
DB_NAME = os.getenv('DB_NAME', 'precios_comparados')
DB_URL = f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}"

# Usamos 'localhost' para Qdrant también
QDRANT_HOST = 'localhost'
QDRANT_PORT = 6333
COLLECTION_NAME = "productos_supermercado"

def inicializar_tablas(connection):
    archivo_sql = "consulta.sql"
    if not os.path.exists(archivo_sql): return
    with open(archivo_sql, 'r', encoding='utf-8') as f:
        comandos = f.read().split(';')
    for cmd in comandos:
        if cmd.strip():
            try: connection.execute(text(cmd))
            except: pass

def crear_vistas_automaticas(connection):
    connection.execute(text("""
    CREATE OR REPLACE VIEW price_obs_enriched AS
    SELECT po.price_obs_id, po.product_id, po.store_id, po.price_total, po.currency, po.observed_at, po.source,
           p.uom_code, p.pack_qty, u.base_type, u.to_base,
           CASE
             WHEN u.base_type IN ('mass', 'volume') THEN po.price_total / NULLIF(p.pack_qty * u.to_base, 0)
             ELSE po.price_total / NULLIF(p.pack_qty, 0)
           END AS price_per_base
    FROM price_observation po
    JOIN product p ON p.product_id = po.product_id
    JOIN uom u ON u.uom_code = p.uom_code;
    """))
    connection.execute(text("""
    CREATE OR REPLACE VIEW latest_price AS
    WITH ranked AS (
      SELECT poe.price_obs_id, poe.product_id, poe.store_id, poe.price_total, poe.price_per_base, poe.observed_at,
             ROW_NUMBER() OVER (PARTITION BY poe.product_id, poe.store_id ORDER BY poe.observed_at DESC) AS rn
      FROM price_obs_enriched poe
    )
    SELECT r.product_id, p.name as producto, r.store_id, r.price_total as precio_actual,
           r.price_per_base as precio_unidad_real, r.observed_at as fecha
    FROM ranked r JOIN product p ON p.product_id = r.product_id WHERE r.rn = 1;
    """))

def cargar_datos():
    print("🚀 Iniciando Carga Híbrida (SQL + Vectorial)...")
    
    try:
        engine = create_engine(DB_URL)
        qdrant = QdrantClient(host=QDRANT_HOST, port=QDRANT_PORT, timeout=60)
        encoder = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
    except Exception as e:
        print(f"❌ Error config: {e}")
        return

    # Reiniciar colección para asegurar limpieza
    try: qdrant.delete_collection(COLLECTION_NAME)
    except: pass
    
    qdrant.create_collection(
        collection_name=COLLECTION_NAME,
        vectors_config=models.VectorParams(size=384, distance=models.Distance.COSINE)
    )

    try: df = pd.read_csv("export/productos_limpios_estandarizados.csv")
    except: 
        print("❌ Error: No encuentro el CSV.")
        return

    df = df.where(pd.notnull(df), None)
    print(f"🔄 Procesando {len(df)} productos...")
    
    with engine.begin() as connection:
        inicializar_tablas(connection)
        
        # Mapeo de Tiendas
        store_map = {}
        for cadena in ['Mercadona', 'Dia']:
            connection.execute(text("INSERT IGNORE INTO chain (name) VALUES (:n)"), {"n": cadena})
            cid = connection.execute(text("SELECT chain_id FROM chain WHERE name = :n"), {"n": cadena}).scalar()
            connection.execute(text(f"INSERT IGNORE INTO store (chain_id, name) VALUES ({cid}, '{cadena} Online')"))
            sid = connection.execute(text(f"SELECT s.store_id FROM store s JOIN chain c ON s.chain_id = c.chain_id WHERE c.name = '{cadena}' LIMIT 1")).scalar()
            store_map[cadena] = sid

        vectors_to_upload = []
        payloads_to_upload = []
        ids_to_upload = []

        for idx, row in df.iterrows():
            tienda_nombre = 'Dia' if str(row.get('tienda', '')).lower() == 'dia' else 'Mercadona'
            store_id = store_map.get(tienda_nombre)
            if not store_id: continue

            # Normalización UOM
            raw_uom = str(row.get('unidad_medida', 'ud'))
            uom = raw_uom.lower().strip() if raw_uom != 'None' else 'ud'
            
            base_type = 'unit'
            to_base = 1.0
            if uom in ['l', 'ml', 'cl']: base_type, to_base = 'volume', {'ml': 0.001, 'cl': 0.01}.get(uom, 1.0)
            elif uom in ['kg', 'g', 'mg']: base_type, to_base = 'mass', {'g': 0.001, 'mg': 0.000001}.get(uom, 1.0)

            # Inserciones SQL
            connection.execute(text("INSERT IGNORE INTO category (name) VALUES (:name)"), {"name": row.get('categoria', 'Sin Categoría')})
            cat_id = connection.execute(text("SELECT category_id FROM category WHERE name = :name"), {"name": row.get('categoria')}).scalar()
            connection.execute(text("INSERT IGNORE INTO uom (uom_code, to_base, base_type) VALUES (:u, :t, :b)"), {"u": uom, "t": to_base, "b": base_type})

            nombre_estandar = row.get('nombre_estandar', 'Desconocido')
            prod_id = connection.execute(text("SELECT product_id FROM product WHERE name = :name"), {"name": nombre_estandar}).scalar()
            if not prod_id:
                result = connection.execute(text("INSERT INTO product (name, category_id, uom_code) VALUES (:name, :cat, :uom)"),
                                          {"name": nombre_estandar, "cat": cat_id, "uom": uom})
                prod_id = result.lastrowid

            precio = float(row['precio_actual']) if row['precio_actual'] else 0.0
            if precio > 0:
                connection.execute(text("INSERT INTO price_observation (product_id, store_id, price_total) VALUES (:pid, :sid, :p)"),
                                 {"pid": prod_id, "sid": store_id, "p": precio})

            # --- QDRANT (Generación ID Robusta) ---
            # Usamos idx para garantizar que CADA fila del CSV entra, aunque sea duplicada.
            unique_uuid = str(uuid.uuid5(uuid.NAMESPACE_DNS, f"row_{idx}_{tienda_nombre}"))
            
            vectors_to_upload.append(f"{nombre_estandar} {row.get('categoria', '')}")
            ids_to_upload.append(unique_uuid)
            payloads_to_upload.append({
                "product_id": prod_id,
                "nombre": row.get('nombre', nombre_estandar),
                "nombre_estandar": nombre_estandar,
                
                "imagen": row.get('imagen', ''), # Guardamos la URL
                
                "categoria": row.get('categoria', ''),
                "tienda": tienda_nombre,
                "precio": precio,
                "unidad": uom
            })

        crear_vistas_automaticas(connection)

    if vectors_to_upload:
        print(f"🧠 Vectorizando {len(vectors_to_upload)} ofertas...")
        embeddings = encoder.encode(vectors_to_upload, batch_size=64, show_progress_bar=True).tolist()
        
        BATCH_SIZE = 250
        total = len(ids_to_upload)
        for i in range(0, total, BATCH_SIZE):
            fin = min(i + BATCH_SIZE, total)
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
                print(f"⚠️ Error lote {i}: {e}")

    print("🎉 Carga Finalizada.")

if __name__ == "__main__":
    cargar_datos()