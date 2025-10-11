# Passer sur du nginx + php-fpm en prod
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git zip unzip curl \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    && pecl install apcu \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl \
    && docker-php-ext-enable apcu

# Pour laisser Laravel intercepter les requests
RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --dev --ignore-platform-req=ext-*

COPY laravel-start.sh /usr/local/bin/laravel-start.sh
RUN chmod +x /usr/local/bin/laravel-start.sh

RUN mv .env.ci .env \
    && php artisan key:generate

RUN mkdir -p storage/app/private \
    && openssl genrsa -out storage/app/private/private.pem 2048 \
    && openssl rsa -in storage/app/private/private.pem -outform PEM -pubout -out storage/app/private/public.pem \
    && chown -R www-data:www-data storage/app/private \
    && chmod 600 storage/app/private/private.pem \
    && chmod 644 storage/app/private/public.pem

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && mkdir -p storage/logs \
    && touch storage/logs/laravel.log \
    && chown www-data:www-data storage/logs/laravel.log \
    && chmod 664 storage/logs/laravel.log

# Décale la racine Apache vers le dossier public de Laravel (avec l'index.php)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# On overide les URLs par défaut de Apache pour pointer vers le dossier public de Laravel
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
CMD ["/usr/local/bin/laravel-start.sh"]
