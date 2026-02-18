import requests
import pandas as pd
import time
import os
from datetime import datetime

# --- CONFIGURACIÓN ---
URL_CATEGORIES = "https://tienda.mercadona.es/api/categories/?lang=es&wh=vlc1"
URL_CATEGORY_DETAIL = "https://tienda.mercadona.es/api/categories/{}/?lang=es&wh=vlc1"

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json'
}

def get_all_category_ids():
    print(f"📡 Obteniendo árbol de categorías desde: {URL_CATEGORIES}")
    try:
        response = requests.get(URL_CATEGORIES, headers=HEADERS, timeout=10)
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
        print(f"❌ Error fatal obteniendo categorías: {e}")
        return []

def get_products_by_category(category_info):
    cat_id = category_info['id']
    cat_name = category_info['name']
    url = URL_CATEGORY_DETAIL.format(cat_id)
    products_list = []
    
    try:
        response = requests.get(url, headers=HEADERS, timeout=10)
        if response.status_code != 200: return []
            
        data = response.json()
        subgrupos = data.get('categories', [])
        
        for subgrupo in subgrupos:
            subgrupo_nom = subgrupo.get('name')
            for product in subgrupo.get('products', []):
                price_info = product.get('price_instructions', {})
                
                # --- CORRECCIÓN: USAMOS 'thumbnail' DIRECTAMENTE ---
                imagen_url = product.get('thumbnail')
                # ---------------------------------------------------

                product_data = {
                    'id_producto': product.get('id'),
                    'nombre': product.get('display_name'),
                    'imagen': imagen_url,  # <--- GUARDAMOS LA FOTO AQUÍ
                    'precio_actual': price_info.get('unit_price'),
                    'unidad_medida': price_info.get('reference_format'), 
                    'precio_referencia': price_info.get('reference_price'), 
                    'categoria': cat_name,
                    'subcategoria': subgrupo_nom,
                    'grupo_principal': category_info['parent'],
                    'tienda': 'Mercadona',
                    'fecha_extraccion': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                }
                products_list.append(product_data)
    except Exception as e:
        print(f"⚠️ Excepción en categoría {cat_id}: {e}")
        
    return products_list

def gestion_mercadona():
    print("🚀 Iniciando Scraper de Mercadona (Con Fotos)...")
    all_products = []
    categories = get_all_category_ids()
    
    for i, cat in enumerate(categories):
        print(f"[{i+1}/{len(categories)}] Procesando: {cat['name']}...")
        products = get_products_by_category(cat)
        all_products.extend(products)
        time.sleep(0.5) 
        
    if all_products:
        df = pd.DataFrame(all_products)
        if not os.path.exists("export"): os.makedirs("export")
        output_path = "export/productos_mercadona_raw.csv"
        df.to_csv(output_path, index=False)
        print(f"💾 Datos guardados en: {output_path} (Total: {len(df)})")
    else:
        print("❌ No se han extraído productos.")

if __name__ == "__main__":
    gestion_mercadona()