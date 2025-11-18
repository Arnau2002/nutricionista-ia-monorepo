# 1. Imagen base: Usamos una imagen oficial y ligera de Python
FROM python:3.11-slim

# 2. Establecer el directorio de trabajo dentro del contenedor
WORKDIR /app

# 3. Copiar el archivo de requisitos e instalar las librerías
# Hacemos esto primero para que Docker pueda cachear este paso
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# 4. Copiar el resto del código de tu backend a la carpeta /app del contenedor
COPY . .

# 5. Comando para ejecutar la aplicación cuando el contenedor se inicie
# (Esto asume que usas FastAPI/Uvicorn. Ajústalo a tu servidor)
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]