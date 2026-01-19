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
    nginx supervisor git zip unzip curl nano \
    libzip libpng oniguruma libxml2 \
    libjpeg-turbo freetype icu-libs ffmpeg \
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

RUN cat <<'EOF' > /usr/local/etc/php/conf.d/opcache.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
EOF

RUN echo "apc.enable_cli=1" > /usr/local/etc/php/conf.d/00-apcu-cli.ini

RUN cat <<'EOF' > /usr/local/etc/php-fpm.d/www.conf
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10
pm.max_requests = 500
catch_workers_output = yes
decorate_workers_output = no
php_admin_flag[log_errors] = on
php_admin_value[error_log] = /proc/self/fd/2
EOF

RUN cat <<'EOF' > /usr/local/etc/php/conf.d/uploads.ini
post_max_size=32M
upload_max_filesize=32M
EOF

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
