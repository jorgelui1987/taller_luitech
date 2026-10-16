FROM php:8.3-apache AS base

# Variables de entorno para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instalar dependencias del sistema y configurar/instalar extensiones PHP
RUN apt-get update -qq && apt-get install -y -qq \
    git \
    curl \
    cron \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libicu-dev \
    gettext-base \
    util-linux \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_pgsql mbstring exif bcmath gd zip intl \
    && a2enmod rewrite

# Configurar el DocumentRoot de Apache para Laravel
RUN echo '<VirtualHost *:80>' > /etc/apache2/sites-available/000-default.conf && \
    echo '    DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    <Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf

# Configurar AllowOverride en el Directory de apache2.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Healthcheck interno: usa archivo estático (no depende de Laravel/DB).
# Antes apuntaba a /health (ruta PHP): si la app tardaba en migrar o la DB
# estaba caída, el chequeo fallaba, Dokploy marcaba unhealthy y Traefik
# devolvía 502 Bad Gateway. BUILD_ID.txt lo sirve Apache directo.
HEALTHCHECK --interval=30s --timeout=5s --start-period=120s --retries=3 \
    CMD curl -f http://127.0.0.1:80/BUILD_ID.txt || exit 1

# ---- Stage de Composer ----
FROM composer:2.7.7 AS composer-stage

# ---- Stage final ----
# NOSONAR - Apache necesita root para el puerto 80, luego baja a www-data
FROM base AS final

# Instalar Composer
COPY --from=composer-stage /usr/bin/composer /usr/bin/composer

# ── Rompe-caché: DEBE ir ANTES de los COPY de código ──
# Si el ARG va después de los COPY, Docker reutiliza las capas de código
# cacheadas y los cambios "no salen" hasta resetear el VPS.
# Dokploy inyecta el SHA del commit vía compose (BUILD_ID).
ARG BUILD_ID=local
ENV BUILD_ID=${BUILD_ID}
RUN echo "BUILD ${BUILD_ID} $(date -u +%Y%m%d%H%M%S)" > /tmp/BUILD_STAMP

# Copiar archivos del proyecto (excluye archivos sensibles via .dockerignore)
COPY app /var/www/html/app
COPY bootstrap /var/www/html/bootstrap
COPY config /var/www/html/config
COPY database /var/www/html/database
COPY lang /var/www/html/lang
COPY public /var/www/html/public
COPY resources /var/www/html/resources
COPY routes /var/www/html/routes
COPY storage /var/www/html/storage
COPY artisan /var/www/html/artisan
COPY composer.json /var/www/html/composer.json
COPY composer.lock /var/www/html/composer.lock
COPY .htaccess /var/www/html/.htaccess
COPY docker /var/www/html/docker

# Sello de build visible en /BUILD_ID.txt (lo sirve Apache directo,
# también lo usa el HEALTHCHECK para no depender de Laravel/DB).
RUN echo "BUILD ${BUILD_ID}" > /var/www/html/public/BUILD_ID.txt

# Crear usuario no-root, configurar permisos, instalar dependencias y limpiar caché
RUN useradd -m -u 1001 -s /bin/bash appuser \
    && usermod -aG www-data appuser \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chown -R appuser:www-data /var/www/html/storage \
    && chown -R appuser:www-data /var/www/html/bootstrap/cache \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && composer run-script post-autoload-dump --no-interaction 2>/dev/null || true \
    && composer clear-cache

# Copiar entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

# Seguridad: El entrypoint se ejecuta como root solo para tareas de inicialización
# (generar .env, permisos, migraciones). Luego Apache baja automáticamente a www-data
# para procesar peticiones. El usuario appuser se usa para tareas de aplicación.
# NOSONAR - Apache 2 necesita root para enlazar al puerto 80, luego baja a www-data
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]