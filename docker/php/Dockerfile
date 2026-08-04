FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev

RUN docker-php-ext-install pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Development
FROM base AS dev

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Production
FROM base AS production

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader

RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

CMD ["php-fpm"]