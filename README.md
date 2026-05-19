# Nutricionista IA - Monorepo (TFM) 🍎💰

Plataforma inteligente que combina **Inteligencia Artificial** y **Scraping de Datos** para generar menús nutricionales personalizados, comparar precios en tiempo real entre supermercados (Mercadona y DIA) y asistir al usuario a través de un chatbot culinario (Copiloto).

## 🚀 Características Principales

*   **Planificador Inteligente**: Generación de menús semanales o diarios basados en los gustos, alergias y objetivos calóricos del usuario mediante LLMs (Pollinations AI).
*   **Comparador Automático**: Búsqueda vectorial (Qdrant) de los ingredientes en una base de datos actualizada para encontrar la cesta de la compra más económica.
*   **Copiloto IA**: Chatbot integrado que actúa como un chef personal, respondiendo dudas sobre recetas e ingredientes en base al menú planificado.
*   **Dashboard y Autenticación**: Historial de cestas guardadas con estadísticas de ahorro y sistema de login seguro.
*   **ETL Automatizado**: Sistema de extracción de datos que se ejecuta de forma autónoma en segundo plano para mantener los precios siempre actualizados.

## 🏗️ Arquitectura del Sistema

El proyecto está diseñado usando una arquitectura de microservicios orquestada con Docker Compose:

1.  **Frontend (PHP Vanilla + JS + CSS)**: Interfaz de usuario, gestión de sesiones y comunicación con el backend.
2.  **Backend Lógica (FastAPI - Python)**: API REST que maneja la lógica de negocio, integración con LLMs y algoritmos de comparación.
3.  **ETL Pipeline (Python)**: Scripts de web scraping y limpieza de datos (BeautifulSoup, requests) que alimentan la base de datos.
4.  **Vector DB (Qdrant)**: Almacenamiento y búsqueda semántica de productos.
5.  **Relational DB (MySQL)**: Almacenamiento de usuarios, perfiles, historial de cestas y datos estructurados de supermercados.

## ⚙️ Despliegue y Ejecución (Docker)

La forma más recomendada de levantar el proyecto en producción o local es mediante Docker.

### 1. Requisitos Previos
Crea archivos `.env` basándote en la configuración de tu servidor.
Asegúrate de incluir:
*   API Keys (Pollinations AI, Google Maps).
*   Cookies de DIA (para evitar bloqueos en el scraping).
*   Credenciales seguras para MySQL.

### 2. Levantar la Infraestructura
En la consola (raíz del proyecto), ejecuta:
```bash
docker-compose up -d --build
```

Esto levantará **5 contenedores simultáneos**:
*   `frontend-mvp` (Puerto expuesto 3000)
*   `backend-mvp` (Puerto expuesto 8001 para la API REST)
*   `nutricionista-mysql` (Base de datos relacional)
*   `nutricionista-qdrant` (Base de datos vectorial en el puerto 6333)
*   `etl-pipeline` (Demonio en segundo plano extrayendo datos cíclicamente)

## 🗂️ Estructura del Monorepositorio

*   **`/frontend_logica`**: Código fuente de la web (Vistas, Controladores, estilos, enrutador PHP).
*   **`/backend_logica`**: API FastAPI, endpoints de IA (Planificador, Copiloto) y Core de emparejamiento semántico.
*   **`/etl-scripts`**: Scrapers de Mercadona y DIA, scripts de normalización e inyección vectorial.
*   **`docker-compose.yml`**: Archivo orquestador principal de la infraestructura completa.

## ⚠️ Notas de Desarrollo (Troubleshooting)

*   **Sincronización Windows/Linux**: El repositorio aplica *case-sensitivity* correcta en las carpetas (`app/Views/`, `app/Controllers/`). Si desarrollas en Windows, asegúrate de mantener esta convención para no romper el despliegue en Linux.
*   **Zona Horaria**: El frontend procesa las fechas guardadas en MySQL (en UTC) y las convierte dinámicamente a la zona horaria local de España (`Europe/Madrid`) antes de mostrarlas en el Dashboard.
*   **ETL Daemon**: El ETL funciona en modo bucle perpetuo y se actualiza de forma paramétrica (168 horas por defecto en el comando de ejecución del `docker-compose.yml`).