import os
import json
import time
from playwright.sync_api import sync_playwright

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

COOKIES_FILE = os.path.join(os.path.dirname(__file__), 'cookies.json')

def fetch_cookies_for_city(city_name, postal_code, browser):
    """Navega a DIA con un código postal específico y extrae las cookies generadas."""
    print(f"Buscando cookies para {city_name.capitalize()} (CP: {postal_code})...")
    
    # Usar un contexto nuevo para cada ciudad para evitar que se mezclen
    context = browser.new_context(
        user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        viewport={'width': 1280, 'height': 720}
    )
    page = context.new_page()
    
    try:
        # Visitar primero la web normal
        page.goto("https://www.dia.es/", timeout=30000)
        # Esperar un poco a que cargue la página inicial y los retos antibot (Akamai)
        page.wait_for_timeout(3000)
        
        # Ahora forzar el cambio de código postal
        page.goto(f"https://www.dia.es/?postalCode={postal_code}", timeout=30000)
        # Esperar para asegurar que las cookies se fijen correctamente con el CP local
        page.wait_for_timeout(4000)
        
        # Extraer cookies
        cookies = context.cookies()
        
        # Formatear a un string usable por requests: "clave=valor; clave2=valor2"
        cookie_string = "; ".join([f"{c['name']}={c['value']}" for c in cookies])
        
        if len(cookie_string) < 50:
            print(f"⚠️ Aviso: String de cookies inusualmente corto para {city_name}.")
            
        print(f"✅ Cookies obtenidas para {city_name.capitalize()} ({len(cookies)} items)")
        return cookie_string
        
    except Exception as e:
        print(f"❌ Error al obtener cookies para {city_name}: {e}")
        return ""
    finally:
        context.close()

def run_cookie_fetcher(cities=None):
    """Ejecuta el proceso para múltiples ciudades y guarda en cookies.json"""
    if cities is None:
        cities = list(POSTAL_CODES_DIA.keys())
        
    print(f"Iniciando Cookie Fetcher para {len(cities)} ciudades...")
    
    cookies_data = {}
    
    # Cargar archivo existente si lo hay
    if os.path.exists(COOKIES_FILE):
        try:
            with open(COOKIES_FILE, 'r') as f:
                cookies_data = json.load(f)
        except Exception:
            pass

    with sync_playwright() as p:
        # Lanza Chromium (headless by default, necesario en servidor)
        browser = p.chromium.launch(headless=True)
        
        for city in cities:
            city_lower = city.lower()
            if city_lower in POSTAL_CODES_DIA:
                cp = POSTAL_CODES_DIA[city_lower]
                cookie_str = fetch_cookies_for_city(city_lower, cp, browser)
                if cookie_str:
                    env_key = f"COOKIE_DIA_{city_lower.upper()}"
                    cookies_data[env_key] = cookie_str
            else:
                print(f"⚠️ Ciudad desconocida: {city}")
                
        browser.close()
        
    # Guardar en archivo
    with open(COOKIES_FILE, 'w') as f:
        json.dump(cookies_data, f, indent=4)
        
    print(f"🏁 Fetcher finalizado. Cookies guardadas en {COOKIES_FILE}")

if __name__ == "__main__":
    run_cookie_fetcher()
