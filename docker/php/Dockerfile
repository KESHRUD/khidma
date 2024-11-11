# docker/php/Dockerfile
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure non-root user
RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data && \
    chown -R www-data:www-data /var/www

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/

USER www-data

EXPOSE 9000
CMD ["php-fpm"]