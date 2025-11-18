import pandas as pd
import os
from sqlalchemy import create_engine, text

# --- CONFIGURACIÓN ---
DB_USER = os.getenv('DB_USER', 'root')
DB_PASS = os.getenv('DB_PASS', 'tu_password_segura') 
DB_HOST = os.getenv('DB_HOST', 'db-tiendas')
DB_NAME = os.getenv('DB_NAME', 'precios_comparados')
# Usamos pymysql como driver seguro
DB_URL = f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}"

def crear_vistas_automaticas(connection):
    print("👓 Creando/Actualizando Vistas SQL automáticas...")
    
    # 1. Vista: price_obs_enriched (Calcula precio por unidad base)
    sql_enriched = """
    CREATE OR REPLACE VIEW price_obs_enriched AS
    SELECT
      po.price_obs_id,
      po.product_id,
      po.store_id,
      po.price_total,
      po.currency,
      po.observed_at,
      po.source,
      p.uom_code,
      p.pack_qty,
      u.base_type,
      u.to_base,
      CASE
        WHEN u.base_type IN ('mass', 'volume')
          THEN po.price_total / NULLIF(p.pack_qty * u.to_base, 0)
        ELSE po.price_total / NULLIF(p.pack_qty, 0)
      END AS price_per_base
    FROM price_observation po
    JOIN product p ON p.product_id = po.product_id
    JOIN uom u ON u.uom_code = p.uom_code;
    """
    connection.execute(text(sql_enriched))

    # 2. Vista: latest_price (La última foto de precios para la App)
    sql_latest = """
    CREATE OR REPLACE VIEW latest_price AS
    WITH ranked AS (
      SELECT
        poe.price_obs_id,
        poe.product_id,
        poe.store_id,
        poe.price_total,
        poe.price_per_base,
        poe.observed_at,
        ROW_NUMBER() OVER (
            PARTITION BY poe.product_id, poe.store_id 
            ORDER BY poe.observed_at DESC
        ) AS rn
      FROM price_obs_enriched poe
    )
    SELECT
      r.product_id,
      p.name as producto,
      r.store_id,
      r.price_total as precio_actual,
      r.price_per_base as precio_unidad_real,
      r.observed_at as fecha
    FROM ranked r
    JOIN product p ON p.product_id = r.product_id
    WHERE r.rn = 1;
    """
    connection.execute(text(sql_latest))
    print("✅ Vistas 'price_obs_enriched' y 'latest_price' creadas correctamente.")

def cargar_datos():
    print("🚀 Iniciando Carga a Base de Datos...")
    
    try:
        engine = create_engine(DB_URL)
        # Test de conexión
        with engine.connect() as conn:
            print("✅ Conexión a BD exitosa.")
    except Exception as e:
        print(f"❌ Error conectando a la BD: {e}")
        return

    try:
        df = pd.read_csv("export/productos_limpios_estandarizados.csv")
        print(f"📄 CSV cargado: {len(df)} productos.")
    except FileNotFoundError:
        print("❌ No se encuentra el archivo CSV. Ejecuta clean_data.py primero.")
        return

    # Lógica de Carga (Chain, Store, Productos...)
    with engine.begin() as connection:
        # 1. Chain y Store
        connection.execute(text("INSERT IGNORE INTO chain (name) VALUES ('Mercadona')"))
        chain_id = connection.execute(text("SELECT chain_id FROM chain WHERE name = 'Mercadona'")).scalar()
        
        connection.execute(text(f"INSERT IGNORE INTO store (chain_id, name) VALUES ({chain_id}, 'Mercadona Online')"))
        store_id = connection.execute(text(f"SELECT store_id FROM store WHERE chain_id = {chain_id} LIMIT 1")).scalar()
        
        print(f"🏢 Trabajando con Tienda ID: {store_id} (Mercadona)")

        count_nuevos = 0
        
        # 2. Carga de Productos y Precios
        for _, row in df.iterrows():
            uom = row['unidad_medida'] if pd.notna(row['unidad_medida']) else 'ud'
            uom = str(uom).lower().strip()
            
            if uom in ['l', 'ml', 'cl']: base_type = 'volume'
            elif uom in ['kg', 'g', 'mg']: base_type = 'mass'
            else: base_type = 'unit'
            
            # Factores de conversión simples para el MVP
            to_base = 1.0
            if uom == 'ml': to_base = 0.001
            if uom == 'cl': to_base = 0.01
            if uom == 'g': to_base = 0.001
            if uom == 'mg': to_base = 0.000001

            # Categoría
            cat_name = row['categoria']
            connection.execute(text("INSERT IGNORE INTO category (name) VALUES (:name)"), {"name": cat_name})
            cat_id = connection.execute(text("SELECT category_id FROM category WHERE name = :name"), {"name": cat_name}).scalar()

            # Unidad (UOM)
            connection.execute(text("""
                INSERT IGNORE INTO uom (uom_code, to_base, base_type) 
                VALUES (:uom, :to_base, :base_type)
            """), {"uom": uom, "to_base": to_base, "base_type": base_type})

            # Producto
            prod_id = connection.execute(text("SELECT product_id FROM product WHERE name = :name"), {"name": row['nombre_estandar']}).scalar()
            
            if not prod_id:
                result = connection.execute(text("""
                    INSERT INTO product (name, category_id, uom_code, brand)
                    VALUES (:name, :cat, :uom, 'Hacendado')
                """), {"name": row['nombre_estandar'], "cat": cat_id, "uom": uom})
                prod_id = result.lastrowid
                count_nuevos += 1

            # Precio
            connection.execute(text("""
                INSERT INTO price_observation (product_id, store_id, price_total)
                VALUES (:pid, :sid, :price)
            """), {"pid": prod_id, "sid": store_id, "price": row['precio_actual']})

        # 3. ¡AQUÍ ESTÁ LA MAGIA! CREAR LAS VISTAS AUTOMÁTICAMENTE
        crear_vistas_automaticas(connection)

    print(f"🎉 Carga Finalizada!")
    print(f"   - Nuevos Productos creados: {count_nuevos}")

if __name__ == "__main__":
    cargar_datos()