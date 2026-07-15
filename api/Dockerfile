# ── Stage de build ──────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS builder

# Dépendances système nécessaires pour les extensions PHP
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    icu-dev \
    libxml2-dev

# Extensions PHP requises par Laravel + Supabase (PostgreSQL) + Excel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    xml \
    soap \
    opcache

# Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier composer.json et installer les dépendances (sans dev)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copier le reste du code source
COPY . .

# Autoloader optimisé pour la production
RUN composer dump-autoload --optimize --no-dev

# Permissions sur le dossier storage et bootstrap/cache
RUN mkdir -p storage/logs \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/api-docs \
             bootstrap/cache \
 && chmod -R 777 storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

# ── Stage de production ──────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine

# Extensions runtime
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    libzip \
    oniguruma \
    libpq \
    icu \
    libxml2 \
    nginx \
    supervisor \
    curl \
    ca-certificates

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

WORKDIR /var/www/html

# Copier l'application buildée
COPY --from=builder /var/www/html /var/www/html
COPY --from=builder /usr/bin/composer /usr/bin/composer

# Config Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Config Supervisor (gère Nginx + PHP-FPM ensemble)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Config PHP opcache pour la production
COPY docker/php-production.ini /usr/local/etc/php/conf.d/99-production.ini

# Script de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Permissions finales
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/api-docs \
             /var/www/html/bootstrap/cache \
 && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["/start.sh"]
