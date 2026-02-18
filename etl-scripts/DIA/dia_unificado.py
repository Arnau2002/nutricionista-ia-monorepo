import requests
import pandas as pd
import time
import os
from datetime import datetime

# --- CONFIGURACIÓN ---
URL_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-insight/initial_analytics/charcuteria-y-quesos/jamon-cocido-lacon-fiambres-y-mortadela/c/L2001?navigation=L2001"
URL_PRODUCTS_BY_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-back/reduced/"

# Leer la cookie de la variable de entorno (que pasaremos por comando Docker)
COOKIE_DIA = '6db05f72-3127-4b70-b72d-18a612e23f84; AKA_A2=A; ak_bmsc=0D03AB42395EC1B1A95A17562E5D6B75~000000000000000000000000000000~YAAQ2tc7F8zAxmmcAQAA0DfrcR4Kxoh6UdWoxElf3n1FgclduOt978LXH1oAaldNX5NvW5fjzxuVawIzTYJ19QUgJlo3LVFsem7Rn2kMLYyh+VfxPUo5m8V6g9R2G7gc4QdkaBXMZjE+TjM5mT0dPQ2JfFiiuYoiKkaaHMTzIo1UXdYGnGwkX0HbNAY1DPcwSrpsxIvSIvlLvK6NBns2CgZDpXHm+sBmmq873+UNoH/t42DX1tQD4hNzQU4zkdkwJkinV2Z8u4zKWw7ReW/aHbex/RidfNLNLQSqg+fNo3Swbbo+4BDRzQvG5y3F9qIvEMI0yrB3jdxwlmGNaZ2xWgAIbpsJU2mXYSRoDj9wuBoNo6wICkVvwy2mDuUDr1FFayEB4FmxTb+Qtwzi9A==; OptanonAlertBoxClosed=2026-02-18T18:02:48.384Z; _gcl_gs=2.1.k1$i1771437765$u177259312; _gcl_au=1.1.326401302.1771437768; eupubconsent-v2=CQf0X1gQf0X1gAcABBESCSFgAPLAAELAAAYgF5wAgAtgLzAvOACAvMAA.flgACFgAAAAA.IF5wAgAtgLzA; _ga=GA1.1.620299532.1771437767; _fbp=fb.1.1771437768698.750743466574416766; _tt_enable_cookie=1; _ttp=01KHRYPG58HHY9ZR49F64Q7829_.tt.1; _abck=551110C41D2B4AE04C230A6FD03C03E1~0~YAAQ2tc7F/TFxmmcAQAA7xjtcQ/PvMiQ0RilE2QIuv5sJtG3w57+0GIQ5P3g5cSaawgXSE9BfaEbF5dtC3gpVecFbMSUzptuykXiTV+8p7Snx8LxZlIl9AqbUWyw3r8CJxlDsVptP3yBSgbFOFsRAbkwxhR7QRseY0egTGHtdBOfQSrSTn5ujU7188GXZe20FahvmN2SCSg40CZ9qqPtFztJuZ0/J3nS9EBgQ+WSnQAdddGRehi9Y2eAOK31Pc1vV8rp3Tse9VphUAyz6wPJkgdFwKO4u4b0oYoJUbzfPMxO/oNhs+BXkiYMiGGt22Od/QxVuVh48Y1uwKlesJoOHMhcwzUobdNVlaax4l8lbi+RJzNK5BDXisdmDm+zLQyCWLEqXQH4jlnFti4p95WYmrDuj4Lawlr7dTXHf9R4yZ+WPNpCr9dWw30rETCMQ1Dqjdo/tHSje45yPOIci0b819LG0NPDJNwGGbfRu+wS5WLCA0331UutN/hTX+7F30qkijbu410cqr7mg8GYc6m1fD8B2PUGA1TBtDQ4mgQfl241YsT6sCebUO4luIgQKxlr7G6jGYWFMnsHZkNbnNAYAUhJZYp7/RiZlctNovR60Co6Ey/RJhFVDI84z/EW/g==~-1~-1~-1~AAQAAAAF%2f%2f%2f%2f%2f62O1nY9wKpfm2ES93yDlppxhVJeT6OBarBAEi+nelCwJWTfVwKpmmobHMAnNdxEJZaYq2vBEGzInv%2f9Jcq4P1zTXMeIQE6o2SqH~-1; _gcl_aw=GCL.1771438119.Cj0KCQiA49XMBhDRARIsAOOKJHZLai17DWwQhHtSloV30S6UVjRR7_itD9CEwZFR-QR6KeqqU6gZGyoaAuy6EALw_wcB; _gcl_dc=GCL.1771438119.Cj0KCQiA49XMBhDRARIsAOOKJHZLai17DWwQhHtSloV30S6UVjRR7_itD9CEwZFR-QR6KeqqU6gZGyoaAuy6EALw_wcB; bm_sz=3A8E65DBD3BBD468456FB1344E2E3F4A~YAAQ+Nc7F2CFCWacAQAAoK/wcR58zH4uFx0Qx3lwQuyg55M1URc1yvUf45kwT1nk20GoLH8YOE2FuKs9FE/S66v1bbodiQw8RBerMw5wzEgh80g1049oOjsGydaTYWsJi/+NexoTK0attdp4Ezk83mK9rWwg1fJ9Td6BffANo4zW6FN+G5x+6hPTzezZ/oChhO3xmG6q+OJXWVpwgM9Gno5fCMxRocARQOMd0Sc3+nl6h6EdAwNfffsaruL+fIiSdgvKvqyp8DWlDvT0WSC4BPaXn6ziKYBhLgWMc04AM+UKWpql41I/fdfF6Bx5uW6ad4cq3lTbjb2BD9v/rImVzt7SS3J6CiVPuQKpkMH4SD6DGTx3mXKBtri31mIkRCwOHmH7sVUqqi1lwX201cNXTqAVPHaetkUMd+9Fptg6Dlw/yUZD~4601911~3294787; OptanonConsent=isGpcEnabled=0&datestamp=Wed+Feb+18+2026+19%3A08%3A44+GMT%2B0100+(hora+est%C3%A1ndar+de+Europa+central)&version=202510.2.0&browserGpcFlag=0&isIABGlobal=false&hosts=&consentId=b7f2c359-afe8-4cd7-9f44-f49565dd042b&interactionCount=1&isAnonUser=1&landingPath=NotLandingPage&groups=C0001%3A1%2CC0002%3A1%2CC0004%3A1%2CV2STACK42%3A1&intType=1&geolocation=ES%3BMD&AwaitingReconsent=false; bm_sv=56D02E2D5C842176DBE948BAA96B72E3~YAAQ+Nc7F2yFCWacAQAADrPwcR5GudJh7drk6luqV5uvZf9/gtDlsOZL9mmmk+9Qw1Bs3VDK17LAdurkIAVem5XUdZ1JwPsIg7XnXSPKrZHpeKuV2f7eZ1qkLDPYI+9mpMXE9sIAiG0MQHJW+YCQ4uj1gixWjXhfSpk1gXplrziJ36lcHfuJE9d5T+koB4iVsHQjF1GtdFynniocim96S/7rrgxSyTaFkRLOBI3VT8FH/8zv8rF3+MXSVeUNX4DABw==~1; cto_bundle=qnJJsl9nSjI5N3ZiOUJVeWl6akNtQkpySktCJTJCRGN6UUJQNjBlUURBV0xTOEd0ZWc3Nm41R1JMNjJPYmo2Tzd1RWlnYnNEUyUyQlUzRVRTWGpRZmVudTZUYUd5bGdOcWlzNlV4cDBrdzh6bVU1RDNTajBrNVgwUzVGNEZRVDhpd1ZBVVMlMkJrYk9UckhzTUs1c0R0RncwQW1hSzA0VmclM0QlM0Q; ttcsid=1771437768876::iPPGpoR6k4hSAFw9cCfn.1.1771438125161.0; ttcsid_CPV6UFJC77UA4KP64QDG=1771437768876::K3nKFQOdJJdZ-kR4dmyc.1.1771438125162.1; _ga_2J064YK74E=GS2.1.s1771437766$o1$g1$t1771438129$j47$l0$h0'

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