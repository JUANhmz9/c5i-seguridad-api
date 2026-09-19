FROM php:8.2-apache

# Habilita la extension de PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copia todos los archivos PHP de esta carpeta al servidor web
COPY . /var/www/html/

# Apache escucha en el puerto 80 por defecto, Render lo detecta automaticamente
EXPOSE 80
