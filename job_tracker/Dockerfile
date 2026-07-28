# --------------------------------------------------
# Stage 1: Build the Vite frontend assets
# --------------------------------------------------
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build


# --------------------------------------------------
# Stage 2: Laravel application with PHP and Apache
# --------------------------------------------------
FROM php:8.4-apache-bookworm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        intl \
        mbstring \
        opcache \
        pdo_pgsql \
        pgsql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configure Apache to serve Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf \
    && printf '%s\n' \
        "<Directory ${APACHE_DOCUMENT_ROOT}>" \
        "    AllowOverride All" \
        "    Require all granted" \
        "</Directory>" \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html

# Copy Laravel project
COPY . .

# Install production Composer packages
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# Copy the compiled Vite assets
COPY --from=frontend /app/public/build ./public/build

# Prepare writable Laravel directories
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Render provides the PORT environment variable.
# Migrations run before Apache starts.
CMD ["sh", "-c", "sed -ri \"s/Listen 80/Listen ${PORT:-10000}/\" /etc/apache2/ports.conf && sed -ri \"s/:80>/:${PORT:-10000}>/\" /etc/apache2/sites-available/000-default.conf && php artisan migrate --force && apache2-foreground"]
