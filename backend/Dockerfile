# --- Composer build stage ---------------------------------------------------
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
      --no-dev \
      --no-interaction \
      --no-progress \
      --no-scripts \
      --prefer-dist \
      --optimize-autoloader

# --- Runtime stage ----------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# System deps + PHP extensions Laravel + Postgres need.
RUN apk add --no-cache \
        nginx \
        bash \
        supervisor \
        postgresql-client \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        autoconf \
        g++ \
        make \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        bcmath \
        intl \
        opcache \
    && apk del autoconf g++ make

# nginx + php-fpm + supervisor configuration
COPY docker/nginx.conf       /etc/nginx/nginx.conf
COPY docker/php-fpm.conf     /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh    /usr/local/bin/entrypoint
RUN  chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

# Copy application code, then vendor from the build stage.
COPY . /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor

# Laravel needs writable storage + bootstrap/cache.
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Optimize for production. APP_KEY etc come from the runtime env.
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint"]
