ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-fpm-alpine AS base

RUN apk add --no-cache \
        nginx supervisor unzip git curl \
        libzip-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql zip \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/nginx/default.conf   /etc/nginx/http.d/default.conf
COPY docker/php/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www
EXPOSE 80

# Development
FROM base AS dev

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

CMD ["supervisord", "-c", "/etc/supervisord.conf", "-n"]

# Production
FROM base AS prod

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader

RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

CMD ["supervisord", "-c", "/etc/supervisord.conf", "-n"]
