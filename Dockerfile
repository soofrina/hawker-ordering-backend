# PHP 8.2 + Apache, with the MySQL PDO driver your app uses.
FROM php:8.2-apache

# Install pdo_mysql so PHP can reach your filess.io database.
RUN docker-php-ext-install pdo_mysql

# Copy the backend folder into Apache's web root.
COPY . /var/www/html/

# Render expects the app to listen on port 10000.
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
 && sed -i 's/:80/:10000/' /etc/apache2/sites-available/000-default.conf
EXPOSE 10000

CMD ["apache2-foreground"]
