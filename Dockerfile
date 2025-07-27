# Passer sur du nginx + php-fpm en prod
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git zip unzip curl \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Pour laisser Laravel intercepter les requests
RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-req=ext-*

COPY laravel-start.sh /usr/local/bin/laravel-start.sh
RUN chmod +x /usr/local/bin/laravel-start.sh

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Décale la racine Apache vers le dossier public de Laravel (avec l'index.php)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# On overide les URLs par défaut de Apache pour pointer vers le dossier public de Laravel
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
CMD ["/usr/local/bin/laravel-start.sh"]
