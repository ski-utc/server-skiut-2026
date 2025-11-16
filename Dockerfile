# Stage 1 — Frontend build
FROM node:18-alpine AS frontend
WORKDIR /app
COPY package*.json vite.config.js tailwind.config.js postcss.config.cjs ./
RUN npm ci --prefer-offline --no-audit
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm run build

# Stage 2 — PHP-FPM backend
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx supervisor git zip unzip curl \
    libzip libpng oniguruma libxml2 \
    libjpeg-turbo freetype icu-libs \
    && apk add --no-cache --virtual .build-deps \
    autoconf g++ make \
    libzip-dev libpng-dev oniguruma-dev libxml2-dev \
    libjpeg-turbo-dev freetype-dev icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mbstring zip exif pcntl bcmath intl gd opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'pm = dynamic'; \
        echo 'pm.max_children = 20'; \
        echo 'pm.start_servers = 5'; \
        echo 'pm.min_spare_servers = 3'; \
        echo 'pm.max_spare_servers = 10'; \
        echo 'pm.max_requests = 500'; \
        echo 'catch_workers_output = yes'; \
        echo 'decorate_workers_output = no'; \
        echo 'php_admin_flag[log_errors] = on'; \
        echo 'php_admin_value[error_log] = /proc/self/fd/2';
    } >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "post_max_size=32M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "upload_max_filesize=32M" >> /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN cp .env.ci .env \
    && php artisan key:generate \
    && mkdir -p storage/{logs,framework,app/public} bootstrap/cache \
    && touch storage/logs/laravel.log \
    && chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
