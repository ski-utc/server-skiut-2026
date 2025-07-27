#!/bin/sh

set -eu

chown www-data:www-data /var/www/html/.env

mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage
chmod -R 775 storage
touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

php artisan key:generate --force
php artisan jwt:generate

php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan event:cache --no-interaction
php artisan optimize --no-interaction

php artisan storage:link

touch database/database.sqlite
php artisan migrate --force

exec apache2-foreground