# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# 1. Instalar extensiones necesarias para conectar con MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 2. Activar mod_rewrite de Apache (necesario para tu enrutamiento index.php?r=...)
RUN a2enmod rewrite

# 3. Copiar todo tu código a la carpeta pública del servidor
COPY . /var/www/html/

# 4. Dar permisos al usuario de Apache (www-data) para que pueda leer/escribir si es necesario
RUN chown -R www-data:www-data /var/www/html

# Apache expone el puerto 80 internamente
EXPOSE 80