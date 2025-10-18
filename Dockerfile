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

# Stage 2 — PHP-FPM backend
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip curl nginx supervisor \
    libzip-dev libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev libssl-dev \
    libicu-dev \
    && pecl install apcu \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath intl \
    && docker-php-ext-enable apcu opcache intl \
    && rm -rf /var/lib/apt/lists/*

RUN echo "opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=0\n\
opcache.revalidate_freq=0\n" > /usr/local/etc/php/conf.d/opcache.ini

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -i 's/pm = dynamic/pm = dynamic/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;pm.max_children = 5/pm.max_children = 20/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;pm.start_servers = 2/pm.start_servers = 5/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;pm.min_spare_servers = 1/pm.min_spare_servers = 3/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;pm.max_spare_servers = 3/pm.max_spare_servers = 10/' /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_requests = 500" >> /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
COPY --from=frontend /app/public/build ./public/build

RUN cp .env.ci .env && php artisan key:generate

RUN mkdir -p storage/logs storage/framework bootstrap/cache && \
    touch storage/logs/laravel.log && \
    chmod 666 storage/logs/laravel.log && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 775 storage bootstrap/cache

# ---- Nginx configuration ----
COPY docker/nginx.conf /etc/nginx/nginx.conf

# ---- Supervisor configuration (launch both nginx + php-fpm) ----
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]