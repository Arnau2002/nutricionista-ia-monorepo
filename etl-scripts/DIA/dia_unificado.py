import requests
import pandas as pd
import time
import os
from datetime import datetime

# --- CONFIGURACIÓN ---
URL_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-insight/initial_analytics/charcuteria-y-quesos/jamon-cocido-lacon-fiambres-y-mortadela/c/L2001?navigation=L2001"
URL_PRODUCTS_BY_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-back/reduced/"

# Leer la cookie de la variable de entorno (que pasaremos por comando Docker)
COOKIE_DIA = os.getenv('COOKIE_DIA', '')

HEADERS_REQUEST_DIA = {
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Encoding': 'gzip, deflate, br',
    'Accept-Language': "es-ES,es;q=0.9",
    'Cookie': COOKIE_DIA,
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
}

def procesar_nodo(nodo, parent_path=""):
    """Extrae recursivamente los IDs de las categorías del JSON de DIA."""
    ids = []
    for key, value in nodo.items():
        if value.get('path'):
            ids.append(value['path'])
        
        children = value.get('children', {})
        if children:
            ids.extend(procesar_nodo(children))
    return ids

def get_ids_categorys_dia():
    """Obtiene la lista maestra de categorías."""
    print("📡 Obteniendo árbol de categorías de DIA...")
    try:
        response = requests.get(URL_CATEGORY_DIA, headers=HEADERS_REQUEST_DIA, timeout=10)
        if response.status_code != 200:
            print(f"❌ Error conectando a DIA (Status {response.status_code}). ¿Cookie caducada?")
            return []
            
        data = response.json()
        info = data.get('menu_analytics', {})
        category_ids = procesar_nodo(info)
        
        # Limpiar duplicados
        unique_ids = list(set([c for c in category_ids if c]))
        print(f"✅ Se han encontrado {len(unique_ids)} categorías en DIA.")
        return unique_ids
    except Exception as e:
        print(f"❌ Error obteniendo categorías: {e}")
        return []

def get_products_by_category_dia(category_id):
    """Descarga productos paginados de una categoría."""
    products_list = []
    page = 1
    
    while True:
        url = f"{URL_PRODUCTS_BY_CATEGORY_DIA}{category_id}?page={page}&size=20"
        try:
            response = requests.get(url, headers=HEADERS_REQUEST_DIA, timeout=10)
            if response.status_code != 200:
                break 
                
            data = response.json()
            items = data.get("plp_items", [])
            
            if not items:
                break # Fin de productos
                
            for item in items:
                # INTENTO DE EXTRACCIÓN DE PRECIOS MÁS ROBUSTO
                # DIA suele devolver un objeto "prices" anidado
                prices_info = item.get('prices', {})
                
                # Si no está anidado, intentamos buscar en la raíz (tu lógica anterior)
                # Pero la API de DIA suele ser: item['prices']['price']
                
                precio_actual = prices_info.get('price')
                if precio_actual is None:
                     # Plan B: buscar con claves planas antiguas por si acaso
                    precio_actual = item.get('prices_price')

                unidad = prices_info.get('measure_unit')
                if unidad is None:
                    unidad = item.get('prices_measure_unit')

                precio_ref = prices_info.get('price_per_unit')
                if precio_ref is None:
                    precio_ref = item.get('prices_price_per_unit')

                product_data = {
                    'id_producto': item.get('object_id'),
                    'nombre': item.get('display_name'),
                    'precio_actual': precio_actual,
                    'unidad_medida': unidad,
                    'precio_referencia': precio_ref,
                    'categoria': category_id, 
                    'tienda': 'Dia',
                    'fecha_extraccion': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                }
                products_list.append(product_data)
            
            page += 1
            time.sleep(0.5) 
            
        except Exception as e:
            print(f"⚠️ Error en pág {page} de {category_id}: {e}")
            break
            
    return products_list

def gestion_dia():
    print("🚀 Iniciando Scraper de DIA (Versión Unificada)...")
    
    if not COOKIE_DIA:
        print("⚠️ ERROR CRÍTICO: No has pasado la COOKIE_DIA. El scraper fallará.")
        return

    all_products = []
    categories = get_ids_categorys_dia()
    
    # Iterar sobre categorías
    for i, cat_id in enumerate(categories):
        print(f"[{i+1}/{len(categories)}] Procesando: {cat_id}...")
        products = get_products_by_category_dia(cat_id)
        all_products.extend(products)
        
    # Guardar en CSV (Importante para el paso siguiente)
    if all_products:
        df = pd.DataFrame(all_products)
        
        if not os.path.exists("export"):
            os.makedirs("export")
            
        output_path = "export/productos_dia_raw.csv"
        df.to_csv(output_path, index=False)
        print(f"🎉 Extracción DIA completada. {len(df)} productos guardados en: {output_path}")
    else:
        print("❌ No se han extraído productos.")

if __name__ == "__main__":
    gestion_dia()