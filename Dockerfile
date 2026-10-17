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

# OPcache: revalidar archivos en cada deploy.
# Sin esto, Apache sirve bytecode viejo en memoria aunque la imagen traiga
# código nuevo, y los cambios "no salen" hasta reiniciar el VPS.
RUN { echo 'opcache.enable=1'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=2'; \
      echo 'opcache.max_accelerated_files=20000'; \
    } > /usr/local/etc/php/conf.d/opcache-deploy.ini

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
# Dokploy inyecta el SHA del commit vía compose (BUILD_ID + CACHE_BUST).
# CACHE_BUST se declara aunque no se use en un RUN: su valor distinto en
# cada deploy invalida la caché de TODAS las capas siguientes.
ARG BUILD_ID=local
ARG CACHE_BUST=local
ENV BUILD_ID=${BUILD_ID}
RUN echo "BUILD ${BUILD_ID} ${CACHE_BUST} $(date -u +%Y%m%d%H%M%S)" > /tmp/BUILD_STAMP

# Copiar archivos del proyecto (excluye archivos sensibles via .dockerignore)
# NOTA: NO copiar storage/ completo: el compose monta el volumen persistente
# en storage/app y ese mount oculta lo copiado. Solo se copian las carpetas
# que deben ir frescas en cada deploy (framework/views/cache, logs).
# storage/app (uploads/backups/logos) vive en el volumen y lo recrea el entrypoint.
COPY app /var/www/html/app
COPY bootstrap /var/www/html/bootstrap
COPY config /var/www/html/config
COPY database /var/www/html/database
COPY lang /var/www/html/lang
COPY public /var/www/html/public
COPY resources /var/www/html/resources
COPY routes /var/www/html/routes
COPY storage/framework /var/www/html/storage/framework
COPY storage/logs /var/www/html/storage/logs
COPY artisan /var/www/html/artisan
COPY composer.json /var/www/html/composer.json
COPY composer.lock /var/www/html/composer.lock
COPY .htaccess /var/www/html/.htaccess
COPY docker /var/www/html/docker

# Sello de build visible en /BUILD_ID.txt (lo sirve Apache directo,
# también lo usa el HEALTHCHECK para no depender de Laravel/DB).
# Se escribe en el entrypoint (runtime) ADEMÁS del build: así incluso si
# Dokploy reutiliza una imagen cacheada, el sello refleja el código que
# realmente arrancó y /__build delata la imagen vieja sin reiniciar el VPS.
RUN echo "BUILD ${BUILD_ID}" > /var/www/html/public/BUILD_ID.txt

# Esqueleto de storage/app en la imagen (el volumen lo oculta al montar,
# el entrypoint lo recrea, pero así el build nunca queda con storage roto).
RUN mkdir -p /var/www/html/storage/app/public /var/www/html/storage/app/backups

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