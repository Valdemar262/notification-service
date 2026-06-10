FROM php:8.5-cli

ARG DEBIAN_FRONTEND=noninteractive

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libonig-dev \
        libpq-dev \
        unzip \
    && docker-php-ext-install \
        mbstring \
        pcntl \
        pdo_pgsql \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN COMPOSER_CACHE_DIR=/tmp/composer-cache composer install \
    --no-interaction \
    --prefer-dist \
    --no-scripts

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-notification-service.ini
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
