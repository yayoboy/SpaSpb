FROM php:8.1-apache

LABEL maintainer="SpaSpb Page Builder"
LABEL description="PHP Page Builder with SQLite and Apache"

# Installa dipendenze di sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configura e installa estensioni PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_sqlite \
    zip \
    opcache

# Abilita moduli Apache
RUN a2enmod rewrite headers expires deflate

# Configura PHP
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 10M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'date.timezone = Europe/Rome'; \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/log/apache2/php_errors.log'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.cookie_secure = 0'; \
    echo 'session.use_strict_mode = 1'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Configura opcache per performance
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Copia configurazione Apache personalizzata
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Imposta directory di lavoro
WORKDIR /var/www/html

# Copia l'applicazione
COPY --chown=www-data:www-data . /var/www/html/

# Crea directory necessarie con permessi corretti
RUN mkdir -p /var/www/html/builder/db \
    && mkdir -p /var/www/html/builder/uploads \
    && mkdir -p /var/www/html/builder/export \
    && mkdir -p /var/www/html/assets/css \
    && mkdir -p /var/www/html/assets/img \
    && chown -R www-data:www-data /var/www/html/builder/db \
    && chown -R www-data:www-data /var/www/html/builder/uploads \
    && chown -R www-data:www-data /var/www/html/builder/export \
    && chown -R www-data:www-data /var/www/html/assets \
    && chmod -R 755 /var/www/html/builder/db \
    && chmod -R 755 /var/www/html/builder/uploads \
    && chmod -R 755 /var/www/html/assets

# Esponi porta 80
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/builder/ || exit 1

# Avvia Apache
CMD ["apache2-foreground"]
