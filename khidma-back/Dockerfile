FROM php:8.3-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install \
    intl \
    opcache \
    pdo \
    pdo_pgsql

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

# Installation des dépendances Composer
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-progress --no-suggest --no-interaction

# Copie du reste du code
COPY . .

RUN mkdir -p var/cache var/log \
    && chmod -R 777 var/cache var/log