# Stage 1 — Frontend build
FROM node:18 AS frontend

WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.cjs ./

RUN npm ci

COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# Stage 2 — PHP + Apache | Switch to php-fpm + nginx on prod    
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git zip unzip curl \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    && pecl install apcu \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl \
    && docker-php-ext-enable apcu \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-req=ext-*

COPY --from=frontend /app/public/build ./public/build

RUN cp .env.ci .env \
    && php artisan key:generate

RUN mkdir -p storage/app/private storage/logs \
    && openssl genrsa -out storage/app/private/private.pem 2048 \
    && openssl rsa -in storage/app/private/private.pem -outform PEM -pubout -out storage/app/private/public.pem \
    && touch storage/logs/laravel.log

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 600 storage/app/private/private.pem \
    && chmod 644 storage/app/private/public.pem

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY laravel-start.sh /usr/local/bin/laravel-start.sh
RUN chmod +x /usr/local/bin/laravel-start.sh

EXPOSE 80
CMD ["/usr/local/bin/laravel-start.sh"]
