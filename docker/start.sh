#!/usr/bin/env sh
set -eu

mkdir -p \
    storage/app/private/backups \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

php artisan config:cache
php artisan route:cache

php-fpm -D
exec nginx -g "daemon off;"
