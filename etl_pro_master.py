import sys
import os
import pandas as pd
from datetime import datetime
from concurrent.futures import ThreadPoolExecutor, as_completed

# Añadir etl-scripts al path para poder importar
sys.path.append(os.path.join(os.getcwd(), 'etl-scripts'))

try:
    from MERCADONA.mercadona import gestion_mercadona
    from DIA.dia_unificado import gestion_dia
    from clean_data import limpiar_datos_multi_ciudad  # Necesito asegurar este nombre
    from load_data import cargar_datos_qdrant
except ImportError as e:
    print(f"❌ Error importando módulos: {e}")
    sys.exit(1)

def run_full_etl(cities):
    start_time = datetime.now()
    print(f"INICIANDO ETL PRO MASTER - {start_time.strftime('%H:%M:%S')}")
    print(f"Ciudades: {', '.join(cities)}")
    print("-" * 50)

    all_data = []

    # 1. Scraping Paralelo de Ciudades
    # Usamos ThreadPoolExecutor para procesar varias ciudades a la vez
    # No somos demasiado agresivos para evitar bans de IP (máximo 2-3 ciudades simultáneas)
    max_city_workers = min(len(cities), 2) 
    
    with ThreadPoolExecutor(max_workers=max_city_workers) as executor:
        # Una ciudad por cada hilo
        future_to_city = {}
        
        for city in cities:
            future_to_city[executor.submit(gestion_mercadona, city)] = f"{city}_mercadona"
            future_to_city[executor.submit(gestion_dia, city)] = f"{city}_dia"
        
        for future in as_completed(future_to_city):
            task_name = future_to_city[future]
            try:
                products = future.result()
                all_data.extend(products)
                print(f"Tarea completada: {task_name} ({len(products)} items)")
            except Exception as e:
                print(f"Error en tarea {task_name}: {e}")

    print("-" * 50)
    print(f"Scraping finalizado. Total acumulado: {len(all_data)} productos.")

    # 2. Limpieza y Consolidación
    print("\nIniciando limpieza y estandarización...")
    try:
        # Re-utilizamos la lógica de clean_data que lee de la carpeta export/
        df_limpio = limpiar_datos_multi_ciudad()
        if df_limpio is None or df_limpio.empty:
            print("Error: No hay datos para cargar.")
            return
    except Exception as e:
        print(f"Error en limpieza: {e}")
        return

    # 3. Carga en Qdrant
    print("\nCargando en Base de Datos Vectorial...")
    try:
        cargar_datos_qdrant(df_limpio)
    except Exception as e:
        print(f"Error en carga: {e}")
        return

    end_time = datetime.now()
    duration = end_time - start_time
    print("-" * 50)
    print(f"PROCESO COMPLETADO EXIOTOSAMENTE EN {duration.total_seconds():.1f} SEGUNDOS")
    print(f"Finalizado a las: {end_time.strftime('%H:%M:%S')}")

if __name__ == "__main__":
    supported_cities = ["valencia", "madrid", "barcelona", "sevilla", "malaga", "zaragoza", "bilbao"]
    
    if len(sys.argv) < 2:
        print("Uso: python etl_pro_master.py <ciudad1> <ciudad2> ... o 'all'")
        sys.exit(1)
        
    if sys.argv[1].lower() == "all":
        target_cities = supported_cities
    else:
        target_cities = [c.lower() for c in sys.argv[1:]]
        
    run_full_etl(target_cities)
