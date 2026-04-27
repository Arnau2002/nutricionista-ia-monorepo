# 1. Imagen base: Usamos la misma imagen de Python
FROM python:3.11-slim

# 2. Establecer el directorio de trabajo
# Establecer zona horaria de España
ENV TZ=Europe/Madrid
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /app

# 3. Copiar e instalar las librerías (como Pandas, Requests, etc.)
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
RUN playwright install --with-deps chromium

# 4. Copiar el resto de tus scripts de scraping y limpieza
COPY . .

# 5. Comando por defecto
# Este contenedor no es un servidor, así que lo dejamos listo
# para ejecutar un script cuando lo llamemos.
CMD ["python"]