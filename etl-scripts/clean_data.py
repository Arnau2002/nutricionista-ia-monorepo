import pandas as pd
import re
import os

# --- FUNCIÓN CLAVE: ESTANDARIZACIÓN ---
def estandarizar_nombre(nombre_crudo, tienda):
    if pd.isna(nombre_crudo): return None
    
    # 1. Corrección de Encoding (Tildes y símbolos)
    try:
        nombre = str(nombre_crudo).encode('cp1252').decode('utf-8')
    except:
        nombre = str(nombre_crudo)
    
    nombre = nombre.lower()
    
    # 2. Eliminar Marcas (Para que "Leche Hacendado" sea igual a "Leche DIA")
    if tienda == 'Mercadona':
        nombre = re.sub(r'\b(hacendado|bosque verde|deliplus|compy)\b', '', nombre)
    elif tienda == 'Dia':
        nombre = re.sub(r'\b(dia|selección mundial|nuestras marcas|delicious)\b', '', nombre)
        
    # 3. Limpieza de Unidades (1L, 500g...)
    unidades = r'(g|kg|ml|l|ud|unidades|bote|paquete|envase|brik|garrafa|docena|spray|unidad)'
    nombre = re.sub(r'(\d+[.,]?\d*)\s*' + unidades, '', nombre)
    
    # 4. Limpieza final
    nombre = re.sub(r'[^\w\s\-\.,/%º]', '', nombre) 
    nombre = re.sub(r'\s+', ' ', nombre).strip()
    
    return nombre

def gestionar_transformacion(ruta_mercadona, ruta_dia):
    print("🚀 Iniciando Transformación Dual (Mercadona + DIA)...")
    
    df_final = pd.DataFrame()
    
    # --- PROCESAR MERCADONA ---
    if os.path.exists(ruta_mercadona):
        print("🛒 Procesando Mercadona...")
        try:
            df_m = pd.read_csv(ruta_mercadona, encoding='utf-8')
        except:
            df_m = pd.read_csv(ruta_mercadona, encoding='latin-1')
            
        df_m['tienda'] = 'Mercadona'
        # Normalizar columnas si es necesario (Mercadona ya viene bien del script)
        df_final = pd.concat([df_final, df_m], ignore_index=True)
    else:
        print("⚠️ No se encontró el archivo de Mercadona.")

    # --- PROCESAR DIA ---
    if os.path.exists(ruta_dia):
        print("🔴 Procesando DIA...")
        try:
            df_d = pd.read_csv(ruta_dia, encoding='utf-8')
        except:
            df_d = pd.read_csv(ruta_dia, encoding='latin-1')

        df_d['tienda'] = 'Dia'
        
        # IMPORTANTE: DIA puede tener nombres de columnas distintos. Los unificamos aquí.
        # Asegúrate de que tu scraper de DIA genere columnas compatibles o renómbralas aquí:
        # df_d = df_d.rename(columns={'nombre_producto': 'nombre', 'precio': 'precio_actual', ...})
        
        df_final = pd.concat([df_final, df_d], ignore_index=True)
    else:
        print("⚠️ No se encontró el archivo de DIA.")

    if df_final.empty:
        print("❌ No hay datos para procesar.")
        return

    # --- ESTANDARIZACIÓN COMÚN ---
    print("🧹 Limpiando y Estandarizando nombres...")
    df_final['nombre_estandar'] = df_final.apply(
        lambda row: estandarizar_nombre(row['nombre'], row['tienda']), axis=1
    )
    
    # Limpieza de precios
    df_final['precio_referencia'] = df_final['precio_referencia'].astype(str).str.replace(r'[^0-9.,]', '', regex=True).str.replace(',', '.').astype(float)

    # Seleccionar columnas finales (AÑADIMOS 'imagen')
    cols = ['id_producto', 'nombre', 'nombre_estandar', 'imagen', 'precio_actual', 'precio_referencia', 'categoria', 'tienda', 'unidad_medida']
    
    # ... código siguiente ...
    df_final = df_final[[c for c in cols if c in df_final.columns]]

    # Guardar
    output = "export/productos_limpios_estandarizados.csv"
    df_final.to_csv(output, index=False, encoding='utf-8-sig')
    print(f"✅ ¡Éxito! Archivo combinado guardado con {len(df_final)} productos.")

if __name__ == "__main__":
    gestionar_transformacion(
        "export/productos_mercadona_raw.csv",
        "export/productos_dia_raw.csv"
    )