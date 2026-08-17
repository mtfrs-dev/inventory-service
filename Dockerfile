ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm-bookworm

# Runtime: nginx + supervisor + tools, plus libraries for the PHP extensions below.
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor unzip git curl \
        libpng-dev libjpeg-dev libfreetype6-dev \
        libzip-dev libicu-dev libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# pdo_mysql: DB driver. zip: laravel-auditing/composer archives. gd: endroid/qr-code.
# intl/mbstring: standard Laravel requirements.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip gd intl mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php.ini          /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/www.conf         /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx.conf       /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf

WORKDIR /var/www/html
EXPOSE 80
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/app.conf", "-n"]
