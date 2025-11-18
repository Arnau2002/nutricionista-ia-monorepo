import pandas as pd
import re
from datetime import datetime
import os
from sqlalchemy import create_engine # Comentado hasta que lo usemos en el paso de carga

# --- FUNCIÓN CLAVE: ESTANDARIZACIÓN ---
def estandarizar_nombre(nombre_crudo, tienda):
    """
    Limpia el nombre de producto para crear una clave universal.
    """
    if pd.isna(nombre_crudo):
        return None
        
    # 1. CORRECCIÓN DE ENCODING "SUCIO" (UTF-8 interpretado como Latin-1)
    # Esto arregla los caracteres Ãº, Ã³, etc. si ya vienen rotos
    try:
        # Intentamos arreglar el texto si viene mal codificado
        nombre = str(nombre_crudo).encode('cp1252').decode('utf-8')
    except:
        # Si falla, lo dejamos como estaba (ya era correcto)
        nombre = str(nombre_crudo)

    nombre = nombre.lower()
    
    # 2. ELIMINAR MARCAS COMUNES
    if tienda == 'Mercadona':
        nombre = re.sub(r'\b(hacendado|bosque verde|gran seleccion|picual|casa juncal)\b', '', nombre)
    elif tienda == 'Dia':
        nombre = re.sub(r'\b(dia|basico|selección|seleccion)\b', '', nombre)
        
    # 3. ELIMINAR INFORMACIÓN DE ENVASE (CUIDANDO LOS GRADOS º)
    # Eliminamos '1 L', '500 g', 'pack', etc.
    # El (?<![\d,]) asegura que no borremos algo si es parte de una medida técnica
    unidades = r'(g|kg|ml|l|ud|unidades|bote|paquete|envase|brik|garrafa|docena|spray|unidad)'
    nombre = re.sub(r'(\d+[.,]?\d*)\s*' + unidades, '', nombre)
    
    # 4. LIMPIEZA DE "BASURA" PERO RESPETANDO GRADOS (º) Y PORCENTAJES (%)
    # Borramos signos raros, pero dejamos º (para aceite) y % (para chocolate/leche)
    nombre = re.sub(r'[^\w\s\-\.,/%º]', '', nombre) 
    
    # 5. LIMPIEZA FINAL DE ESPACIOS
    nombre = re.sub(r'\s+', ' ', nombre).strip()
    
    return nombre

# --- FUNCIÓN DE ORQUESTACIÓN ---
def gestionar_transformacion(ruta_mercadona, ruta_dia):
    print("🚀 Iniciando Transformación de Datos (T)...")
    
    # 1. Cargar datos de Mercadona
    # Usamos encoding='utf-8' explícito para leer
    try:
        df_m = pd.read_csv(ruta_mercadona, encoding='utf-8')
    except UnicodeDecodeError:
        # Plan B si falla: intentar con latin-1
        df_m = pd.read_csv(ruta_mercadona, encoding='latin-1')
        
    df_m['tienda'] = 'Mercadona'
    
    # 2. Consolidar (por ahora solo Mercadona)
    df_total = df_m 
    
    # 3. Aplicar la estandarización
    df_total['nombre_estandar'] = df_total.apply(
        lambda row: estandarizar_nombre(row['nombre'], row['tienda']), axis=1
    )
    
    # 4. Arreglar precios (tu corrección anterior)
    df_limpio = df_total.copy()
    df_limpio['precio_referencia'] = df_limpio['precio_referencia'].astype(str).str.replace(r'[^0-9.,]', '', regex=True).str.replace(',', '.').astype(float)

    # Seleccionar columnas finales
    cols_finales = ['id_producto', 'nombre', 'nombre_estandar', 'precio_actual', 'precio_referencia', 'categoria', 'tienda', 'unidad_medida']
    # Filtramos solo las que existan para evitar errores si falta alguna
    cols_existentes = [c for c in cols_finales if c in df_limpio.columns]
    df_limpio = df_limpio[cols_existentes]

    print(f"✅ Se han estandarizado {len(df_limpio)} productos.")
    
    # 5. GUARDAR CON ENCODING MÁGICO PARA EXCEL (utf-8-sig)
    output_path_clean = "export/productos_limpios_estandarizados.csv"
    df_limpio.to_csv(output_path_clean, index=False, encoding='utf-8-sig')
    print(f"💾 Datos limpios guardados en: {output_path_clean}")
    
    return df_limpio

if __name__ == "__main__":
    ruta_m = "export/productos_mercadona_raw.csv"
    ruta_d = "export/productos_dia_raw.csv" # Placeholder
    gestionar_transformacion(ruta_m, ruta_d)