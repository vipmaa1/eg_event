FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    curl \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo_mysql zip gd bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

COPY backend/ .

RUN mkdir -p storage/app storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000

CMD ["php-fpm"]
