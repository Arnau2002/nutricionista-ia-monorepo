import requests
import pandas as pd
import time
import os
from datetime import datetime
from dotenv import load_dotenv
from concurrent.futures import ThreadPoolExecutor, as_completed

# Cargar variables de entorno (para COOKIE_DIA)
load_dotenv()

# --- CONFIGURACIÓN ---
URL_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-insight/initial_analytics/charcuteria-y-quesos/jamon-cocido-lacon-fiambres-y-mortadela/c/L2001?navigation=L2001"
URL_PRODUCTS_BY_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-back/reduced/"

# Eliminamos variables globales para evitar colisiones en paralelo
# Las cookies y ciudades se gestionan ahora dentro de cada hilo gestion_dia()

# Mapeo de ciudades a códigos postales para DIA
POSTAL_CODES_DIA = {
    "valencia": "46001",
    "madrid": "28001",
    "barcelona": "08001",
    "sevilla": "41001",
    "malaga": "29001",
    "zaragoza": "50001",
    "bilbao": "48001"
}

# El CP se calcula ahora dinámicamente por ciudad en gestion_dia()

def get_headers_dia(city_name=None):
    """Genera las cabeceras dinámicamente usando la cookie específica de la ciudad si existe."""
    cookie = ""
    if city_name:
        env_var = f"COOKIE_DIA_{city_name.upper()}"
        cookie = os.getenv(env_var, os.getenv("COOKIE_DIA", ""))
    else:
        cookie = os.getenv("COOKIE_DIA", "")
        
    return {
        'Accept': 'application/json, text/plain, */*',
        'Accept-Encoding': 'gzip, deflate',
        'Accept-Language': "es-ES,es;q=0.9",
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Cookie': cookie
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

def establecer_localizacion_dia(session, city_name):
    """Fuerza a DIA a cambiar la ubicación reiniciando la sesión para evitar caché de Valencia."""
    session.cookies.clear() # Limpiamos rastro de sesiones anteriores
    
    # Obtenemos el CP y cookie para esta ciudad
    cp = POSTAL_CODES_DIA.get(city_name.lower(), "46001")
    url = f"https://www.dia.es/?postalCode={cp}"
    headers = get_headers_dia(city_name)
    
    print(f"Estableciendo sesion en DIA para {city_name} (CP: {cp})...")
    try:
        # 1. Primer contacto con sus headers y cookies manuales
        session.get("https://www.dia.es/", headers=headers, timeout=10)
        # 2. Refuerzo de localización
        session.get(url, headers=headers, timeout=10)
        return True
    except Exception as e:
        print(f"Error estableciendo localizacion: {e}")
        return False

# Base de headers para el primer contacto
HEADERS_REQUEST_DIA_BASE = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

# Usaremos una sesión por ciudad para evitar conflictos en paralelo
# session_dia = requests.Session() # ELIMINADA GLOBAL

def get_ids_categorys_dia(session, city_name):
    """Obtiene la lista maestra de categorías."""
    establecer_localizacion_dia(session, city_name)
    print(f"Obteniendo arbol de categorias de DIA ({city_name})...")
    try:
        response = session.get(URL_CATEGORY_DIA, headers=get_headers_dia(city_name), timeout=10)
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

def get_products_by_category_dia(category_id, session, city_name):
    """Descarga productos paginados de una categoría."""
    products_list = []
    page = 1
    headers = get_headers_dia(city_name)
    
    while True:
        # Usamos la sesión que ya tiene el CP inyectado
        params = {"page": page, "size": 20}
        try:
            response = session.get(f"{URL_PRODUCTS_BY_CATEGORY_DIA}{category_id}", 
                                      headers=headers, 
                                      params=params, 
                                      timeout=10)
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




                imagen_url = item.get('image')
                # A veces Dia devuelve rutas relativas, aseguramos la URL completa
                if imagen_url and not imagen_url.startswith('http'):
                    imagen_url = f"https://www.dia.es{imagen_url}"
                # --------------------------------

                product_data = {
                    'id_producto': item.get('object_id'),
                    'nombre': item.get('display_name'),
                    # --- AÑADE ESTO ---
                    'imagen': imagen_url,
                    # ------------------
                    'precio_actual': precio_actual,
                    'unidad_medida': unidad,
                    'precio_referencia': precio_ref,
                    'categoria': category_id, 
                    'tienda': 'Dia',
                'ciudad': city_name.capitalize(),
                'fecha_extraccion': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            }
            products_list.append(product_data)
        
        page += 1
        time.sleep(0.5) 
        
    except Exception as e:
        print(f"⚠️ Error en pág {page} de {category_id}: {e}")
        break
        
    return products_list

def gestion_dia(city=None):
    city_name = city if city else os.getenv("CIUDAD_DIA", "Valencia")
    city_name = city_name.capitalize()
    cp_actual = POSTAL_CODES_DIA.get(city_name.lower(), "46001")

    print(f"Iniciando Scraper de DIA en {city_name} (CP: {cp_actual}) con hilos...")
    
    # SECCION CLAVE: Sesion individual por hilo
    local_session = requests.Session()
    
    # Verificamos si tenemos cookie para esta ciudad
    env_var = f"COOKIE_DIA_{city_name.upper()}"
    current_cookie = os.getenv(env_var, os.getenv("COOKIE_DIA", ""))

    if not current_cookie:
        print(f"ERROR: No hay COOKIE_DIA para {city_name}. DIA podria bloquear la peticion.")
        # No cancelamos, quizas el handshake automatico funciona, pero avisamos.

    establecer_localizacion_dia(local_session, city_name)
    categories = get_ids_categorys_dia(local_session, city_name)
    
    if not categories:
        return []

    all_products = []
    
    # Procesamiento paralelo de categorías para DIA usando la sesion local
    with ThreadPoolExecutor(max_workers=5) as executor:
        futures = {executor.submit(get_products_by_category_dia, cat_id, local_session, city_name): cat_id for cat_id in categories}
        for future in as_completed(futures):
            results = future.result()
            all_products.extend(results)

    print(f"DIA {city_name}: {len(all_products)} productos extraidos.")
    
    if all_products:
        df = pd.DataFrame(all_products)
        if not os.path.exists("export"): os.makedirs("export")
        output_path = f"export/productos_dia_{city_name.lower()}_raw.csv"
        df.to_csv(output_path, index=False)
    
    return all_products

if __name__ == "__main__":
    gestion_dia()