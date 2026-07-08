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

if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

if ! php -r '$key = getenv("APP_KEY") ?: ""; if (str_starts_with($key, "base64:")) { $key = base64_decode(substr($key, 7), true) ?: ""; } exit(in_array(strlen($key), [16, 32], true) ? 0 : 1);'; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY was missing or invalid; generated a runtime key for this container."
fi

sed -i "s/listen 10000;/listen ${PORT:-10000};/" /etc/nginx/http.d/default.conf

php artisan optimize:clear || true
php artisan config:cache
php artisan route:cache

php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class); $request = Illuminate\Http\Request::create("/", "GET"); $response = $kernel->handle($request); if ($response->getStatusCode() >= 500) { fwrite(STDERR, $response->getContent().PHP_EOL); exit(1); } $kernel->terminate($request, $response);'

if [ "${RUN_MIGRATIONS:-true}" != "false" ]; then
    php artisan migrate --force || echo "Database migration could not run during startup."
fi

if [ -n "${ADMIN_PASSWORD:-}" ]; then
    php artisan admin:ensure --force || echo "Admin account could not be prepared during startup."
fi

php-fpm -D
exec nginx -g "daemon off;"
