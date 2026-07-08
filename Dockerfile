FROM php:8.3-cli-alpine AS vendor

WORKDIR /app

RUN apk add --no-cache \
        curl-dev \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        curl \
        dom \
        fileinfo \
        gd \
        intl \
        mbstring \
        pdo_pgsql \
        simplexml \
        tokenizer \
        xml \
        xmlreader \
        xmlwriter \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --ignore-platform-req=ext-pcntl

COPY . .
RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --no-interaction

FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl-dev \
        curl \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        nginx \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        curl \
        dom \
        fileinfo \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_pgsql \
        simplexml \
        tokenizer \
        xml \
        xmlreader \
        xmlwriter \
        zip

COPY --from=vendor /app /var/www/html
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

RUN chmod +x /var/www/html/docker/start.sh /var/www/html/docker/run-backup-cron.sh \
    && mkdir -p /run/nginx /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 10000

CMD ["./docker/start.sh"]
