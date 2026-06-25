# syntax=docker/dockerfile:1

#######################################
# Stage 1 - PHP dependencies (composer)
#######################################
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --prefer-dist \
    --ignore-platform-reqs

#######################################
# Stage 2 - Frontend assets (vite)
#######################################
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources

RUN npm run build

#######################################
# Stage 3 - Application image (PHP-FPM)
#######################################
FROM php:8.2-fpm AS app

# Install system dependencies and PHP extensions
# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \          
        libonig-dev \
        libzip-dev \
        libicu-dev \
        default-mysql-client \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \  
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
# Copy composer binary from the official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application source
COPY . .

# Copy vendor dependencies built in the "vendor" stage
COPY --from=vendor /app/vendor ./vendor

# Copy compiled frontend assets built in the "frontend" stage
COPY --from=frontend /app/public/build ./public/build

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Custom php.ini settings (upload size, etc)
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Permissions for Laravel writable directories
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/public storage/app/private bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]