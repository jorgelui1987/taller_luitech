#!/bin/bash

set -o pipefail

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "ERROR: vendor/autoload.php no encontrado"
fi

cd /var/www/html

# Limpiar DATABASE_URL que Dokploy pueda haber inyectado (causa error "connection mariadb not configured")
unset DATABASE_URL

# Crear directorios necesarios para Laravel (excluidos en .dockerignore)
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# APP_KEY fija (no regenerar cada vez)
APP_KEY="base64:QYszpHHh20nMzsI8IvPX80EBD4BhMG/xQj52l2fauYA="
export APP_KEY

# Escribir .env con valores literales (sin ${VAR} syntax)
echo "Escribiendo .env..."
echo "APP_NAME=\"CRM Tienda Celulares\"" > /var/www/html/.env
echo "APP_ENV=production" >> /var/www/html/.env
echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
echo "APP_DEBUG=false" >> /var/www/html/.env
echo "APP_URL=https://luitech.fun" >> /var/www/html/.env
echo "FORCE_HTTPS=true" >> /var/www/html/.env
echo "SESSION_SECURE_COOKIE=true" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "DB_CONNECTION=mysql" >> /var/www/html/.env
echo "DB_HOST=luitech-luitech-rb1llz" >> /var/www/html/.env
echo "DB_PORT=3306" >> /var/www/html/.env
echo "DB_DATABASE=luitech" >> /var/www/html/.env
echo "DB_USERNAME=luitech" >> /var/www/html/.env
echo "DB_PASSWORD=Castro16@@" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "SESSION_DRIVER=file" >> /var/www/html/.env
echo "SESSION_LIFETIME=120" >> /var/www/html/.env
echo "SESSION_SECURE_COOKIE=true" >> /var/www/html/.env
echo "CACHE_DRIVER=file" >> /var/www/html/.env
echo "QUEUE_CONNECTION=sync" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "# Seguridad" >> /var/www/html/.env
echo "APP_DEBUG=false" >> /var/www/html/.env
echo "TRUSTED_PROXIES=*" >> /var/www/html/.env

echo "=== .env generado ==="
head -12 /var/www/html/.env
echo "======================"

# Limpiar caché de configuración para que Laravel lea el .env fresco
cd /var/www/html && php artisan config:clear 2>/dev/null || true
cd /var/www/html && php artisan cache:clear 2>/dev/null || true
cd /var/www/html && php artisan view:clear 2>/dev/null || true

# Crear .htaccess si no existe (necesario para routing de Laravel)
if [ ! -f /var/www/html/public/.htaccess ]; then
    echo "Creando .htaccess..."
    cat > /var/www/html/public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF
    echo ".htaccess creado exitosamente"
fi

# Ejecutar migraciones y seeders
echo "Ejecutando migraciones..."
cd /var/www/html && php artisan migrate --force 2>/dev/null || echo "Migraciones no ejecutadas"
echo "Migraciones completadas"

echo "Ejecutando seeders..."
cd /var/www/html && php artisan db:seed --force 2>/dev/null || echo "Seeders no ejecutados"
echo "Seeders completados"

echo "Aplicación lista!"
exec "$@"