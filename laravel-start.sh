#!/bin/sh

set -eu

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

php artisan storage:link || true

touch database/database.sqlite
php artisan migrate --force

exec apache2-foreground