# Nutricionista IA - ETL Pro Master 🍎💰

Este repositorio contiene la lógica para extraer precios de supermercados (Mercadona y DIA) y cargarlos en una base de datos vectorial Qdrant para alimentar al recomendador de menús inteligente.

## 🚀 Guía de Uso del ETL

El sistema ha sido refactorizado para permitir ejecuciones paralelas, multi-ciudad y carga masiva automática.

### 1. Requisitos Previos
Asegúrate de tener las cookies de DIA actualizadas en tu archivo `.env` en la raíz del proyecto para evitar bloqueos:
- `COOKIE_DIA_MADRID`
- `COOKIE_DIA_VALENCIA`
- (Sigue el mismo patrón para el resto de ciudades)

### 2. Ejecución Local (Recomendado para pruebas)
Desde la raíz del proyecto, puedes ejecutar el script maestro:

```powershell
# Extraer y cargar TODAS las ciudades (Valencia, Madrid, Barcelona, Sevilla, etc.)
python etl_pro_master.py all

# Ejecutar solo una ciudad específica
python etl_pro_master.py valencia
```

**¿Qué hace este script?**
1. Lanza los scrapers de Mercadona y DIA en paralelo por ciudad.
2. Genera archivos CSV limpios en la carpeta `export/`.
3. Llama automáticamente a `clean_data.py` para unificar todos los CSV.
4. Llama a `load_data.py` para subir los vectores y metadatos a **Qdrant** y a la base de datos SQL.

### 3. Ejecución vía Docker
Si prefieres usar los contenedores:

```bash
# Levantar la infraestructura (Qdrant, MySQL, Backend)
docker-compose up -d

# Ejecutar el ETL completo dentro del contenedor
docker-compose run --rm etl-scripts python /app/etl_pro_master.py all
```

---

## 🛠️ Estructura del ETL

- **`etl_pro_master.py`**: El punto de entrada principal. Coordina la extracción y la carga.
- **`etl-scripts/DIA/dia_unificado.py`**: Lógica de extracción de DIA con gestión de sesiones independientes.
- **`etl-scripts/MERCADONA/mercadona.py`**: Lógica de extracción de Mercadona (ahora usa nombres de categorías descriptivos).
- **`etl-scripts/load_data.py`**: Script que toma los CSV procesados y los inyecta en Qdrant (Base Vectorial).

## ⚠️ Notas Importantes
- **Categorías**: Mercadona ahora guarda nombres descriptivos (ej. "Carnicería") en lugar de IDs numéricos para mejorar la precisión del Chef.
- **Estabilidad**: El backend (`main.py`) tiene un parche para manejar diferencias de mayúsculas/minúsculas en los nombres de las ciudades.
- **Limpieza**: Si quieres hacer una carga limpia, borra la colección en Qdrant antes de ejecutar el script.