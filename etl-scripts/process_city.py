import os
import sys
import subprocess

def run_cmd(cmd, env=None):
    print(f"Executing: {cmd}")
    new_env = os.environ.copy()
    if env:
        new_env.update(env)
    
    process = subprocess.Popen(cmd, shell=True, env=new_env)
    process.wait()
    if process.returncode != 0:
        print(f"❌ Error executing: {cmd}")
        return False
    return True

def main():
    if len(sys.argv) < 2:
        print("Usage: python etl-scripts/process_city.py <city1> <city2> ...")
        print("Example: python etl-scripts/process_city.py madrid barcelona valencia")
        print("Or: python etl-scripts/process_city.py all")
        return

    supported_cities = ["valencia", "madrid", "barcelona", "sevilla", "malaga", "zaragoza"]
    
    if sys.argv[1].lower() == "all":
        cities = supported_cities
    else:
        cities = [c.lower() for c in sys.argv[1:]]

    print(f"🌆 Iniciando proceso masivo para: {', '.join([c.capitalize() for c in cities])}")

    # 1. Refrescar Cookies para todas las ciudades primero (Seguridad Anti-Ban)
    print("\n--- 🍪 RENOVANDO SESIONES (Anti-Ban) ---")
    if not run_cmd("python etl-scripts/cookie_fetcher.py"):
        print("⚠️ Error renovando cookies. El scraping podría fallar o ser bloqueado.")
        # Podríamos continuar, pero es arriesgado. Dejamos que intente.

    # 2. & 3. Scrapear cada ciudad
    for city in cities:
        print(f"\n--- 🏙️ PROCESANDO: {city.upper()} ---")
        
        # Scrape Mercadona (Ahora usa cookies automáticas)
        env_m = {"CIUDAD_MERCADONA": city}
        if not run_cmd("python etl-scripts/MERCADONA/mercadona.py", env=env_m):
            print(f"⚠️ Fallo en Mercadona ({city}), saltando a la siguiente...")
            continue

        # Scrape DIA (Ahora usa cookies automáticas)
        env_d = {"CIUDAD_DIA": city.capitalize()}
        if not run_cmd("python etl-scripts/DIA/dia_unificado.py", env=env_d):
            print(f"⚠️ Fallo en DIA ({city}), saltando a la siguiente...")
            continue

    # 4. Limpieza de Datos (Una sola vez para todas las ciudades)
    print("\n--- 扫 CONSOLIDANDO TODAS LAS CIUDADES ---")
    if not run_cmd("python etl-scripts/clean_data.py"):
        return

    # 5. Carga en Base de Datos Vectorial (Zero-Downtime)
    print("\n--- 🚀 CARGANDO EN VECTOR DB (QDRANT) ---")
    if not run_cmd("python etl-scripts/load_data.py"):
        return

    print(f"\n✅ ¡Proceso finalizado con éxito para: {', '.join([c.capitalize() for c in cities])}!")
    print("🚀 La base de datos se ha actualizado sin tiempo de inactividad.")

if __name__ == "__main__":
    main()
