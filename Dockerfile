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

# On every start: make sure the uploads folder exists and is owned by
# www-data (Apache/PHP run as www-data). This must happen at start time,
# not build time, because the persistent disk mounts over /uploads after
# the image is built — so its ownership has to be fixed each boot.
CMD mkdir -p /var/www/html/uploads \
 && chown -R www-data:www-data /var/www/html/uploads \
 && apache2-foreground
