# Single-stage production image: PHP 8.5 FPM + NGINX + Supervisor
# Alpine packages come from ONE repo version — no ABI mismatches
FROM php:8.5-fpm-alpine

# ── Runtime packages + build deps (layer 1/2: cached for re-runs) ──
RUN apk add --no-cache \
    libpq \
    icu-libs \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    oniguruma \
    libxml2 \
    imagemagick \
    libsodium \
    curl \
    ca-certificates \
    tzdata \
    fcgi \
    nginx \
    supervisor

# ── Build-time dependencies (layer 2/2: removed after compilation) ───
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    imagemagick-dev \
    linux-headers

# ── Timezone ─────────────────────────────────────────────────────────
ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# ── PHP extensions ───────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        bcmath \
        zip \
        intl \
        soap \
        exif \
        pcntl && \
    pecl install redis && docker-php-ext-enable redis && \
    pecl install imagick && docker-php-ext-enable imagick

# ── PHP opcache (production settings) + imagick skip version check ───
RUN { \
        echo "opcache.enable=1"; \
        echo "opcache.memory_consumption=128"; \
        echo "opcache.interned_strings_buffer=8"; \
        echo "opcache.max_accelerated_files=10000"; \
        echo "opcache.revalidate_freq=2"; \
        echo "opcache.fast_shutdown=1"; \
        echo "imagick.skip_version_check=1"; \
    } > /usr/local/etc/php/conf.d/99-opcache.ini

# ── PHP-FPM: listen on Unix socket ───────────────────────────────────
RUN sed -i 's|^;listen = 127.0.0.1:9000|listen = /var/run/php-fpm.sock|' \
        /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's|^;listen.owner = .*|listen.owner = nginx|' \
        /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's|^;listen.group = .*|listen.group = nginx|' \
        /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's|^;listen.mode = 0660|listen.mode = 0660|' \
        /usr/local/etc/php-fpm.d/www.conf

# ── Composer ─────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Application ──────────────────────────────────────────────────────
WORKDIR /var/www/html
COPY . .

RUN composer install \
        --no-dev \
        --no-interaction \
        --optimize-autoloader \
        --no-scripts && \
    chown -R www-data:www-data storage bootstrap/cache && \
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs && \
    chmod -R 775 storage bootstrap/cache

# ── NGINX config ─────────────────────────────────────────────────────
RUN rm -f /etc/nginx/http.d/default.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
RUN mkdir -p /run/nginx /run

# ── Supervisor config ────────────────────────────────────────────────
RUN printf '%s\n' \
    '[supervisord]' \
    'nodaemon=true' \
    'user=root' \
    '' \
    '[program:php-fpm]' \
    'command=/usr/local/sbin/php-fpm -F' \
    'stdout_logfile=/dev/stdout' \
    'stdout_logfile_maxbytes=0' \
    'stderr_logfile=/dev/stderr' \
    'stderr_logfile_maxbytes=0' \
    '' \
    '[program:nginx]' \
    'command=/usr/sbin/nginx -g "daemon off;"' \
    'stdout_logfile=/dev/stdout' \
    'stdout_logfile_maxbytes=0' \
    'stderr_logfile=/dev/stderr' \
    'stderr_logfile_maxbytes=0' \
    > /etc/supervisord.conf

# ── Cleanup build-time dependencies ──────────────────────────────────
RUN apk del $PHPIZE_DEPS linux-headers && \
    rm -rf /tmp/* /var/cache/apk/*

# ── Final ────────────────────────────────────────────────────────────
WORKDIR /var/www/html
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
