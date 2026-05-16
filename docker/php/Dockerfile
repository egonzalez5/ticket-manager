FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpq-dev libzip-dev \
    autoconf g++ make \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install     pdo     pdo_pgsql     zip

# phpredis -- requerida para REDIS_CLIENT=phpredis y QUEUE_CONNECTION=redis
RUN pecl install redis     && docker-php-ext-enable redis     && rm -rf /tmp/pear

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
