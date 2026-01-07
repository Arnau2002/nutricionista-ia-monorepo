# nutricionista-ia-monorepo

construir: docker-compose up -d --build
levantar: docker-compose up -d

extraer datos:
docker-compose run --rm etl-scripts python DIA/dia_unificado.py
docker-compose run --rm etl-scripts python MERCADONA/mercadona.py


subir a base vectorial Qdrant:
docker-compose run --rm etl-scripts python load_data.py