#!/bin/bash

set -o pipefail

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "ERROR: vendor/autoload.php no encontrado"
fi

cd /var/www/html

# Limpiar DATABASE_URL que Dokploy pueda haber inyectado (causa error "connection mariadb not configured")
unset DATABASE_URL

# Generar APP_KEY
echo "Generando APP_KEY..."
APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));" 2>/dev/null)
if [ -z "$APP_KEY" ]; then
    APP_KEY=$(openssl rand -base64 32 2>/dev/null)
    APP_KEY="base64:${APP_KEY}"
fi
export APP_KEY

# Escribir .env con valores literales (sin ${VAR} syntax)
echo "Escribiendo .env..."
echo "APP_NAME=\"CRM Tienda Celulares\"" > /var/www/html/.env
echo "APP_ENV=${APP_ENV:-production}" >> /var/www/html/.env
echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
echo "APP_DEBUG=${APP_DEBUG:-false}" >> /var/www/html/.env
echo "APP_URL=${APP_URL:-http://localhost}" >> /var/www/html/.env
echo "FORCE_HTTPS=${FORCE_HTTPS:-true}" >> /var/www/html/.env
echo "SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "DB_CONNECTION=mysql" >> /var/www/html/.env
echo "DB_HOST=${DB_HOST:-127.0.0.1}" >> /var/www/html/.env
echo "DB_PORT=${DB_PORT:-3306}" >> /var/www/html/.env
echo "DB_DATABASE=${DB_DATABASE:-tiendacelulares_crm}" >> /var/www/html/.env
echo "DB_USERNAME=${DB_USERNAME:-root}" >> /var/www/html/.env
echo "DB_PASSWORD=${DB_PASSWORD:-}" >> /var/www/html/.env
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
