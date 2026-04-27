import requests
import pandas as pd
import time
import os
import json
import random
from datetime import datetime
from dotenv import load_dotenv
from concurrent.futures import ThreadPoolExecutor, as_completed

load_dotenv()

# --- CONFIGURACIÓN DE SEGURIDAD ---
USER_AGENTS = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0'
]

CIUDADES_MERCADONA = {
    "valencia": "vlc1", "madrid": "mad1", "barcelona": "bcn1",
    "sevilla": "sev1", "malaga": "mal1", "zaragoza": "zgz1", "bilbao": "4716"
}

COOKIES_FILE = "etl-scripts/cookies.json"

def get_session_for_city(city_name):
    """Carga cookies y headers para una ciudad específica."""
    session = requests.Session()
    headers = {
        'User-Agent': random.choice(USER_AGENTS),
        'Accept': 'application/json',
        'Accept-Language': 'es-ES,es;q=0.9',
        'Referer': 'https://tienda.mercadona.es/',
        'Origin': 'https://tienda.mercadona.es'
    }
    
    if os.path.exists(COOKIES_FILE):
        try:
            with open(COOKIES_FILE, 'r') as f:
                data = json.load(f)
                cookie_str = data.get(f"COOKIE_MERCADONA_{city_name.upper()}")
                if cookie_str:
                    headers['Cookie'] = cookie_str
        except: pass
        
    session.headers.update(headers)
    return session

def check_for_misuse(response_json):
    """Detecta si Mercadona nos ha bloqueado por uso indebido."""
    if isinstance(response_json, dict) and response_json.get('code') == 3:
        print("🚨 ¡ALERTA! Mercadona ha detectado 'Uso Indebido'. Deteniendo para proteger IP.")
        return True
    return False

def get_all_category_ids(session, wh_id):
    url = f"https://tienda.mercadona.es/api/categories/?lang=es&wh={wh_id}"
    try:
        response = session.get(url, timeout=15)
        data = response.json()
        if check_for_misuse(data): return []
        
        ids_para_procesar = []
        for parent in data.get('results', []):
            p_name = parent.get('name')
            for child in parent.get('categories', []):
                ids_para_procesar.append({'id': child['id'], 'name': child['name'], 'parent': p_name})
        return ids_para_procesar
    except Exception as e:
        print(f"Error categorias: {e}")
        return []

def get_category_detail(session, category_id, wh_id, city_name):
    url = f"https://tienda.mercadona.es/api/categories/{category_id}/?lang=es&wh={wh_id}"
    try:
        # Pequeña pausa humana antes de cada categoría
        time.sleep(random.uniform(1.0, 3.0))
        response = session.get(url, timeout=15)
        if response.status_code != 200: return []
        
        data = response.json()
        if check_for_misuse(data): return []
        
        products_list = []
        for subgrupo in data.get('categories', []):
            sub_nom = subgrupo.get('name')
            for product in subgrupo.get('products', []):
                p_info = product.get('price_instructions', {})
                products_list.append({
                    'id_producto': product.get('id'),
                    'nombre': product.get('display_name'),
                    'imagen': product.get('thumbnail'),
                    'precio_actual': p_info.get('unit_price', 0),
                    'precio_referencia': p_info.get('reference_price', 0),
                    'unidad_medida': p_info.get('unit_name', 'ud'),
                    'categoria': data.get('parent', data.get('name')),
                    'subcategoria': sub_nom,
                    'tienda': 'Mercadona',
                    'ciudad': city_name.capitalize(),
                    'fecha_extraccion': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                })
        return products_list
    except: return []

def gestion_mercadona(city=None):
    city = city.lower() if city else os.getenv("CIUDAD_MERCADONA", "valencia").lower()
    wh = CIUDADES_MERCADONA.get(city, "vlc1")
    
    session = get_session_for_city(city)
    print(f"🕵️‍♂️ Mercadona {city.capitalize()} (Modo Sigilo) - WH: {wh}")
    
    categories = get_all_category_ids(session, wh)
    if not categories: return []

    all_products = []
    # Bajamos a 2 hilos para ser extremadamente cautelosos
    with ThreadPoolExecutor(max_workers=2) as executor:
        futures = {executor.submit(get_category_detail, session, c['id'], wh, city): c['id'] for c in categories}
        for future in as_completed(futures):
            res = future.result()
            if not res and all_products: # Si de repente falla y ya teníamos datos, es posible que nos hayan cortado
                print("⚠️ Posible bloqueo detectado durante la descarga.")
            all_products.extend(res)
    
    if all_products:
        df = pd.DataFrame(all_products)
        os.makedirs("export", exist_ok=True)
        path = f"export/productos_mercadona_{city}_raw.csv"
        df.to_csv(path, index=False)
        print(f"✅ Guardados {len(df)} productos en {path}")
        
    return all_products

if __name__ == "__main__":
    gestion_mercadona()
    gestion_mercadona()