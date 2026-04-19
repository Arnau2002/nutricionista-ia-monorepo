import pandas as pd
import re
import os
import glob

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

def limpiar_datos_multi_ciudad():
    print("Iniciando Transformacion Multi-Ciudad (Consolidando Export/*)...")
    
    df_final = pd.DataFrame()
    carpeta_export = "export"
    
    # --- BUSCAR ARCHIVOS DE MERCADONA ---
    archivos_mercadona = glob.glob(os.path.join(carpeta_export, "productos_mercadona_*_raw.csv"))
    for f in archivos_mercadona:
        print(f"Procesando Mercadona: {f}...")
        try:
            df_m = pd.read_csv(f, encoding='utf-8')
        except:
            df_m = pd.read_csv(f, encoding='latin-1')
        
        if 'ciudad' not in df_m.columns:
            ciudad_nom = f.split("_")[2].capitalize()
            df_m['ciudad'] = ciudad_nom
            
        df_m['tienda'] = 'Mercadona'
        df_final = pd.concat([df_final, df_m], ignore_index=True)

    # --- BUSCAR ARCHIVOS DE DIA ---
    archivos_dia = glob.glob(os.path.join(carpeta_export, "productos_dia_*_raw.csv"))
    for f in archivos_dia:
        print(f"Procesando DIA: {f}...")
        try:
            df_d = pd.read_csv(f, encoding='utf-8')
        except:
            df_d = pd.read_csv(f, encoding='latin-1')

        if 'ciudad' not in df_d.columns:
            ciudad_nom = f.split("_")[2].capitalize()
            df_d['ciudad'] = ciudad_nom

        df_d['tienda'] = 'Dia'
        df_final = pd.concat([df_final, df_d], ignore_index=True)

    if df_final.empty:
        print("No hay datos para procesar.")
        return pd.DataFrame()

    # --- ESTANDARIZACIÓN COMÚN ---
    print("Limpiando y Estandarizando nombres...")
    df_final['nombre_estandar'] = df_final.apply(
        lambda row: estandarizar_nombre(row['nombre'], row['tienda']), axis=1
    )
    
    # Limpieza de precios (referencia)
    try:
        df_final['precio_referencia'] = df_final['precio_referencia'].astype(str).str.replace(r'[^0-9.,]', '', regex=True).str.replace(',', '.').astype(float)
    except:
        pass

    cols = ['id_producto', 'nombre', 'nombre_estandar', 'imagen', 'precio_actual', 'precio_referencia', 'categoria', 'tienda', 'unidad_medida', 'ciudad']
    df_final = df_final[[c for c in cols if c in df_final.columns]]

    # Guardar
    output = "export/productos_limpios_estandarizados.csv"
    df_final.to_csv(output, index=False, encoding='utf-8-sig')
    print(f"Exito! Archivo combinado guardado con {len(df_final)} productos.")
    
    return df_final

if __name__ == "__main__":
    limpiar_datos_multi_ciudad()