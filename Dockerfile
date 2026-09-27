# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.3
ARG NODE_VERSION=22.14.0

FROM php:${PHP_VERSION}-cli-bookworm AS php-dependencies
WORKDIR /var/www/html

COPY --from=composer:2.8.12 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd zip \
    && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./
RUN rm -rf vendor \
    && composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

COPY . .
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -s /var/www/html/storage/app/public public/storage

FROM node:${NODE_VERSION}-bookworm-slim AS frontend
WORKDIR /var/www/html
ENV PUPPETEER_SKIP_DOWNLOAD=true

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build \
    && rm -rf node_modules

FROM php:${PHP_VERSION}-fpm-bookworm AS runtime
WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6 \
        libfreetype6-dev \
        libjpeg62-turbo \
        libjpeg62-turbo-dev \
        libpng16-16 \
        libpng-dev \
        libwebp7 \
        libwebp-dev \
        libicu72 \
        libicu-dev \
        libonig-dev \
        libonig5 \
        libxml2 \
        libxml2-dev \
        libzip4 \
        libzip-dev \
        zlib1g \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j2 bcmath exif gd intl mbstring opcache pcntl pdo_mysql zip \
    && pecl install redis-6.2.0 \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/* /tmp/pear /usr/src/php

RUN rm -f /usr/local/etc/php-fpm.d/www.conf

COPY --from=php-dependencies /var/www/html/vendor ./vendor
COPY --from=php-dependencies /var/www/html/app ./app
COPY --from=php-dependencies /var/www/html/bootstrap ./bootstrap
COPY --from=php-dependencies /var/www/html/config ./config
COPY --from=php-dependencies /var/www/html/database ./database
COPY --from=php-dependencies /var/www/html/lang ./lang
COPY --from=php-dependencies /var/www/html/public ./public
COPY --from=php-dependencies /var/www/html/resources ./resources
COPY --from=php-dependencies /var/www/html/routes ./routes
COPY --from=php-dependencies /var/www/html/artisan ./artisan
COPY --from=php-dependencies /var/www/html/composer.json ./composer.json
COPY --from=php-dependencies /var/www/html/composer.lock ./composer.lock
COPY --from=frontend /var/www/html/public/build ./public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/scripts/deploy.sh docker/scripts/worker-healthcheck.sh /usr/local/bin/

RUN groupadd --gid 1000 app \
    && useradd --uid 1000 --gid 1000 --create-home --shell /usr/sbin/nologin app \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -s /var/www/html/storage/app/public public/storage \
    && chown -R app:app /var/www/html \
    && chmod -R ug+rwX storage bootstrap/cache \
    && chmod 755 /usr/local/bin/deploy.sh /usr/local/bin/worker-healthcheck.sh

USER app
EXPOSE 9000
CMD ["php-fpm", "-F"]

FROM nginxinc/nginx-unprivileged:1.27-alpine AS web
WORKDIR /var/www/html
COPY --from=php-dependencies /var/www/html/public ./public
COPY --from=frontend /var/www/html/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 8080

FROM runtime AS railway

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY docker/nginx/railway.conf /etc/nginx/nginx.conf
COPY docker/scripts/railway-start.sh /usr/local/bin/railway-start.sh

RUN chmod 755 /usr/local/bin/railway-start.sh \
    && mkdir -p /var/lib/nginx /var/log/nginx \
    && chown -R app:app /var/lib/nginx /var/log/nginx

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/railway-start.sh"]
