import requests
import pandas as pd
import time
import os
from datetime import datetime

# --- CONFIGURACIÓN ---
# Usamos la URL que has encontrado con tu código postal (wh=vlc1 es Valencia, ajusta si quieres)
URL_CATEGORIES = "https://tienda.mercadona.es/api/categories/?lang=es&wh=vlc1"
# Esta es la URL para pedir el detalle de una categoría concreta (donde están los productos)
URL_CATEGORY_DETAIL = "https://tienda.mercadona.es/api/categories/{}/?lang=es&wh=vlc1"

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json'
}

def get_all_category_ids():
    """
    Analiza el JSON maestro para sacar los IDs de las subcategorías (Nivel 2).
    """
    print(f"📡 Obteniendo árbol de categorías desde: {URL_CATEGORIES}")
    try:
        response = requests.get(URL_CATEGORIES, headers=HEADERS, timeout=10)
        response.raise_for_status()
        data = response.json()
        
        ids_para_procesar = []
        
        # 1. Iteramos sobre los resultados principales (Nivel 1: "Agua", "Bodega", etc.)
        results = data.get('results', [])
        for parent_category in results:
            parent_name = parent_category.get('name')
            
            # 2. Iteramos sobre las subcategorías (Nivel 2: "Vino tinto", "Cerveza", etc.)
            # Estas son las que contienen los productos cuando las llamas individualmente.
            for child_category in parent_category.get('categories', []):
                ids_para_procesar.append({
                    'id': child_category['id'],        # El ID que necesitamos (ej. 112)
                    'name': child_category['name'],    # El nombre (ej. "Aceite...")
                    'parent': parent_name              # El padre (ej. "Aceite y especias")
                })
                
        print(f"✅ Se han encontrado {len(ids_para_procesar)} subcategorías para procesar.")
        return ids_para_procesar
        
    except Exception as e:
        print(f"❌ Error fatal obteniendo categorías: {e}")
        return []

def get_products_by_category(category_info):
    """
    Descarga los productos de una categoría específica (ID).
    """
    cat_id = category_info['id']
    cat_name = category_info['name']
    url = URL_CATEGORY_DETAIL.format(cat_id)
    products_list = []
    
    try:
        response = requests.get(url, headers=HEADERS, timeout=10)
        if response.status_code != 200:
            print(f"⚠️ Error HTTP {response.status_code} en categoría {cat_id}")
            return []
            
        data = response.json()
        
        # En el detalle, a veces hay un nivel más de anidación ("categories" dentro de la categoría)
        # Mercadona organiza: Categoria (112) -> Subgrupos -> Productos
        subgrupos = data.get('categories', [])
        
        for subgrupo in subgrupos:
            subgrupo_nom = subgrupo.get('name')
            
            # Aquí están los productos
            for product in subgrupo.get('products', []):
                price_info = product.get('price_instructions', {})
                
                product_data = {
                    'id_producto': product.get('id'),
                    'nombre': product.get('display_name'),
                    'precio_actual': price_info.get('unit_price'),
                    'unidad_medida': price_info.get('reference_format'), 
                    'precio_referencia': price_info.get('reference_price'), 
                    'categoria': cat_name,
                    'subcategoria': subgrupo_nom, # Nivel 3 (ej. "Aceite de oliva virgen")
                    'grupo_principal': category_info['parent'],
                    'tienda': 'Mercadona',
                    'fecha_extraccion': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                }
                products_list.append(product_data)
                
    except Exception as e:
        print(f"⚠️ Excepción en categoría {cat_id}: {e}")
        
    return products_list

def gestion_mercadona():
    """
    Orquestador principal
    """
    print("🚀 Iniciando Scraper de Mercadona...")
    start_time = time.time()
    all_products = []
    
    # 1. Obtener lista de categorías
    categories = get_all_category_ids()
    
    # 2. Iterar (¡Aquí está la magia!)
    # Para pruebas rápidas, puedes poner: categories[:5] para hacer solo las 5 primeras
    for i, cat in enumerate(categories):
        print(f"[{i+1}/{len(categories)}] Procesando: {cat['name']} (ID: {cat['id']})...")
        
        products = get_products_by_category(cat)
        all_products.extend(products)
        
        # Pausa para que Mercadona no nos bloquee
        time.sleep(0.5) 
        
    # 3. Guardar resultados
    if all_products:
        df = pd.DataFrame(all_products)
        print(f"\n🎉 Extracción completada. Total productos: {len(df)}")
        
        # Aseguramos que la carpeta export existe
        if not os.path.exists("export"):
            os.makedirs("export")
            
        output_path = "export/productos_mercadona_raw.csv"
        df.to_csv(output_path, index=False)
        print(f"💾 Datos guardados en: {output_path}")
    else:
        print("❌ No se han extraído productos. Revisa la conexión o la URL.")

if __name__ == "__main__":
    gestion_mercadona()