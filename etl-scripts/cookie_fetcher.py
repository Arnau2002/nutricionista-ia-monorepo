import os
import json
import time
from playwright.sync_api import sync_playwright

# Mapeo unificado de códigos postales
POSTAL_CODES = {
    "valencia": "46001",
    "madrid": "28001",
    "barcelona": "08001",
    "sevilla": "41001",
    "malaga": "29001",
    "zaragoza": "50001",
    "bilbao": "48001"
}

COOKIES_FILE = os.path.join(os.path.dirname(__file__), 'cookies.json')

def fetch_cookies_dia(city_name, postal_code, browser):
    """Navega a DIA y extrae cookies."""
    print(f"🍪 [DIA] Buscando cookies para {city_name.capitalize()} (CP: {postal_code})...")
    context = browser.new_context(
        user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        viewport={'width': 1280, 'height': 720}
    )
    page = context.new_page()
    try:
        page.goto("https://www.dia.es/", timeout=30000)
        page.wait_for_timeout(2000)
        page.goto(f"https://www.dia.es/?postalCode={postal_code}", timeout=30000)
        page.wait_for_timeout(4000)
        cookies = context.cookies()
        cookie_string = "; ".join([f"{c['name']}={c['value']}" for c in cookies])
        print(f"✅ [DIA] Cookies obtenidas ({len(cookies)} items)")
        return cookie_string
    except Exception as e:
        print(f"❌ [DIA] Error en {city_name}: {e}")
        return ""
    finally:
        context.close()

def fetch_cookies_mercadona(city_name, postal_code, browser):
    """Navega a Mercadona y extrae cookies de sesión."""
    print(f"🍪 [MERCADONA] Buscando cookies para {city_name.capitalize()} (CP: {postal_code})...")
    context = browser.new_context(
        user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        viewport={'width': 1280, 'height': 720}
    )
    page = context.new_page()
    try:
        page.goto("https://tienda.mercadona.es/", timeout=30000)
        page.wait_for_timeout(3000)
        
        # Esperar a que aparezca el input de código postal si no estamos ya dentro
        try:
            # Mercadona suele pedir el CP en un modal/input al entrar
            input_selector = 'input[name="postalCode"]'
            page.wait_for_selector(input_selector, timeout=5000)
            page.fill(input_selector, postal_code)
            page.press(input_selector, 'Enter')
            page.wait_for_timeout(4000)
        except:
            print(f"ℹ️ [MERCADONA] No se detectó modal de CP para {city_name}, es posible que ya tenga sesión.")

        cookies = context.cookies()
        cookie_string = "; ".join([f"{c['name']}={c['value']}" for c in cookies])
        print(f"✅ [MERCADONA] Cookies obtenidas ({len(cookies)} items)")
        return cookie_string
    except Exception as e:
        print(f"❌ [MERCADONA] Error en {city_name}: {e}")
        return ""
    finally:
        context.close()

def run_cookie_fetcher(cities=None):
    """Refresca cookies para ambas tiendas y todas las ciudades."""
    if cities is None:
        cities = list(POSTAL_CODES.keys())
        
    print(f"🚀 Iniciando Cookie Fetcher para {len(cities)} ciudades...")
    cookies_data = {}

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        for city in cities:
            city_lower = city.lower()
            if city_lower in POSTAL_CODES:
                cp = POSTAL_CODES[city_lower]
                
                # DIA
                cookie_dia = fetch_cookies_dia(city_lower, cp, browser)
                if cookie_dia:
                    cookies_data[f"COOKIE_DIA_{city_lower.upper()}"] = cookie_dia
                
                # MERCADONA
                cookie_merca = fetch_cookies_mercadona(city_lower, cp, browser)
                if cookie_merca:
                    cookies_data[f"COOKIE_MERCADONA_{city_lower.upper()}"] = cookie_merca
            
        browser.close()
        
    with open(COOKIES_FILE, 'w') as f:
        json.dump(cookies_data, f, indent=4)
        
    print(f"🏁 Fetcher finalizado. Cookies guardadas en {COOKIES_FILE}")

if __name__ == "__main__":
    run_cookie_fetcher()
