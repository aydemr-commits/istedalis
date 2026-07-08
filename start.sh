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

. ./docker/prepare-laravel.sh

sed -i "s/listen 10000;/listen ${PORT:-10000};/" /etc/nginx/http.d/default.conf

php artisan optimize:clear || true
php artisan config:cache
php artisan route:cache

if [ "${RUN_MIGRATIONS:-true}" != "false" ]; then
    php artisan migrate --force || echo "Database migration could not run during startup."
fi

if [ -n "${ADMIN_PASSWORD:-}" ]; then
    php artisan admin:ensure --force || echo "Admin account could not be prepared during startup."
fi

php-fpm -D
exec nginx -g "daemon off;"
