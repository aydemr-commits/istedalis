FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --ignore-platform-req=ext-pcntl

COPY . .
RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R 775 bootstrap/cache storage
RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --no-interaction

FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        nginx \
        oniguruma-dev \
        postgresql-dev \
    && apk add --no-cache --virtual .php-build-deps $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_pgsql \
        zip \
    && apk del .php-build-deps

RUN php -m \
    && php -r '$required = ["bcmath", "dom", "gd", "intl", "mbstring", "Zend OPcache", "pdo_pgsql", "SimpleXML", "xml", "xmlreader", "xmlwriter", "zip"]; foreach ($required as $extension) { if (! extension_loaded($extension)) { fwrite(STDERR, "Missing PHP extension: {$extension}\n"); exit(1); } }'

COPY --from=vendor /app /var/www/html
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

RUN { \
        echo 'log_errors=On'; \
        echo 'error_log=/proc/self/fd/2'; \
        echo 'display_errors=Off'; \
    } > /usr/local/etc/php/conf.d/render-logging.ini \
    && { \
        echo 'catch_workers_output = yes'; \
        echo 'php_admin_flag[log_errors] = on'; \
        echo 'php_admin_value[error_log] = /proc/self/fd/2'; \
    } >> /usr/local/etc/php-fpm.d/www.conf

RUN chmod +x /var/www/html/docker/start.sh /var/www/html/docker/run-backup-cron.sh \
    && mkdir -p /run/nginx /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 10000

CMD ["./docker/start.sh"]
