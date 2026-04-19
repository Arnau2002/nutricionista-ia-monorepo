import requests
import pandas as pd
import time
import os
from datetime import datetime
from dotenv import load_dotenv
from concurrent.futures import ThreadPoolExecutor, as_completed

load_dotenv()

# --- CONFIGURACIÓN ---
CIUDADES_MERCADONA = {
    "valencia": "vlc1",
    "madrid": "mad1",
    "barcelona": "bcn1",
    "sevilla": "sev1",
    "malaga": "mal1",
    "zaragoza": "zgz1",
    "bilbao": "4716"
}

CIUDAD_DEFAULT = os.getenv("CIUDAD_MERCADONA", "valencia").lower()
WH_ID = CIUDADES_MERCADONA.get(CIUDAD_DEFAULT, "vlc1")

# Las URLs se generarán dinámicamente en las funciones para soportar cambios de ciudad (WH_ID)
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json'
}

def get_all_category_ids(wh_id):
    url_categories = f"https://tienda.mercadona.es/api/categories/?lang=es&wh={wh_id}"
    print(f"📡 Obteniendo árbol de categorías desde: {url_categories}")
    try:
        response = requests.get(url_categories, headers=HEADERS, timeout=10)
        response.raise_for_status()
        data = response.json()
        ids_para_procesar = []
        
        results = data.get('results', [])
        for parent_category in results:
            parent_name = parent_category.get('name')
            for child_category in parent_category.get('categories', []):
                ids_para_procesar.append({
                    'id': child_category['id'],
                    'name': child_category['name'],
                    'parent': parent_name
                })
        print(f"✅ Se han encontrado {len(ids_para_procesar)} subcategorías.")
        return ids_para_procesar
    except Exception as e:
        print(f"Error fatal obteniendo categorias: {e}")
        return []

def get_category_detail(category_id, wh_id, city_name):
    """Obtiene los productos de una categoría específica."""
    # Re-calculamos la URL por si acaso la base cambió
    url = f"https://tienda.mercadona.es/api/categories/{category_id}/?lang=es&wh={wh_id}"
    
    products_list = []
    try:
        response = requests.get(url, headers=HEADERS, timeout=10)
        if response.status_code != 200: return []
        
        category_info = response.json()
        for subgrupo in category_info.get('categories', []):
            subgrupo_nom = subgrupo.get('name')
            for product in subgrupo.get('products', []):
                # Extraer precio: El API da 'price_insructions' -> 'unit_price'
                price_info = product.get('price_instructions', {})
                precio_und = price_info.get('unit_price', 0)
                precio_ref = price_info.get('reference_price', 0)
                uom = price_info.get('unit_name', 'ud')

                product_data = {
                    'id_producto': product.get('id'),
                    'nombre': product.get('display_name'),
                    'imagen': product.get('thumbnail'),
                    'precio_actual': precio_und,
                    'precio_referencia': precio_ref,
                    'unidad_medida': uom,
                    'categoria': category_info.get('parent', category_info.get('name')),
                    'subcategoria': subgrupo_nom,
                    'tienda': 'Mercadona',
                    'ciudad': city_name.capitalize(),
                    'fecha_extraccion': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                }
                products_list.append(product_data)
    except:
        pass
    return products_list

def gestion_mercadona(city=None):
    # Ya no dependemos de globals para el grueso del trabajo
    current_city = city.lower() if city else os.getenv("CIUDAD_MERCADONA", "valencia").lower()
    current_wh = CIUDADES_MERCADONA.get(current_city, "vlc1")

    print(f"Iniciando Scraper de Mercadona en {current_city.capitalize()} (WH: {current_wh}) con hilos...")
    all_products = []
    categories = get_all_category_ids(current_wh)
    
    if not categories:
        return []

    # Uso de hilos para descargar categorías en paralelo (Acelera x5)
    with ThreadPoolExecutor(max_workers=10) as executor:
        futures = {executor.submit(get_category_detail, cat['id'], current_wh, current_city): cat['id'] for cat in categories}
        for future in as_completed(futures):
            results = future.result()
            all_products.extend(results)
    
    print(f"Mercadona {current_city.capitalize()}: {len(all_products)} productos extraidos.")
    
    if all_products:
        df = pd.DataFrame(all_products)
        if not os.path.exists("export"): os.makedirs("export")
        output_path = f"export/productos_mercadona_{current_city}_raw.csv"
        df.to_csv(output_path, index=False)
        print(f"💾 Datos guardados en: {output_path} (Total: {len(df)})")
    else:
        print("❌ No se han extraído productos.")
        
    return all_products

if __name__ == "__main__":
    gestion_mercadona()