#!/bin/bash

set -e

echo "=========================================="
echo "Iniciando contenedor de Taller Luitech..."
echo "=========================================="

cd /var/www/html

# Verificar que vendor/autoload.php existe
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "ERROR: vendor/autoload.php no encontrado. Ejecuta 'composer install' primero."
    exit 1
fi

# Limpiar DATABASE_URL que Dokploy pueda haber inyectado (causa error "connection mariadb not configured")
unset DATABASE_URL

# Crear directorios necesarios para Laravel
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# ── Generar .env desde variables de entorno ──────────────────────────
echo "Creando .env desde variables de entorno..."

# Generar APP_KEY si no está definida
if [ -z "$APP_KEY" ]; then
    echo "Generando nueva APP_KEY..."
    APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
fi

# Asegurar que APP_URL tenga protocolo
APP_URL="${APP_URL}"
if [ -n "$APP_URL" ] && [[ "$APP_URL" != http* ]]; then
    APP_URL="https://${APP_URL}"
    echo "✓ APP_URL corregida: $APP_URL"
fi

{
    echo "APP_NAME=\"${APP_NAME:-CRM Tienda Celulares}\""
    echo "APP_ENV=${APP_ENV:-production}"
    echo "APP_KEY=${APP_KEY}"
    echo "APP_DEBUG=false"
    echo "APP_URL=${APP_URL}"
    echo "FORCE_HTTPS=${FORCE_HTTPS:-true}"
    echo "SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}"
    echo ""
    echo "DB_CONNECTION=${DB_CONNECTION:-mysql}"
    echo "DB_HOST=${DB_HOST:-127.0.0.1}"
    echo "DB_PORT=${DB_PORT:-3306}"
    echo "DB_DATABASE=${DB_DATABASE:-luitech}"
    echo "DB_USERNAME=${DB_USERNAME:-root}"
    echo "DB_PASSWORD=${DB_PASSWORD:-}"
    echo ""
    echo "SESSION_DRIVER=${SESSION_DRIVER:-file}"
    echo "SESSION_LIFETIME=${SESSION_LIFETIME:-120}"
    echo "CACHE_DRIVER=${CACHE_DRIVER:-file}"
    echo "QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}"
    echo ""
    echo "LOG_LEVEL=warning"
    echo "TRUSTED_PROXIES=*"
} > /var/www/html/.env

echo "✓ .env generado correctamente"

# ── Configurar permisos ─────────────────────────────────────────────
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ── Limpiar caché ───────────────────────────────────────────────────
echo "Limpiando cachés..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# ── Crear .htaccess si no existe ────────────────────────────────────
if [ ! -f /var/www/html/public/.htaccess ]; then
    echo "Creando .htaccess..."
    cat > /var/www/html/public/.htaccess << 'HTACCESS'
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
HTACCESS
    echo "✓ .htaccess creado"
fi

# ── Storage Link ────────────────────────────────────────────────────
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creando enlace simbólico de storage..."
    php artisan storage:link --force 2>/dev/null || true
fi

# ── Migraciones ─────────────────────────────────────────────────────
echo "Verificando conexión a base de datos..."
DB_CHECK=$(php -r "
    try {
        \$driver = '${DB_CONNECTION:-mysql}';
        if (\$driver === 'pgsql') {
            \$pdo = new PDO('pgsql:host=${DB_HOST:-127.0.0.1};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-luitech}', '${DB_USERNAME:-root}', '${DB_PASSWORD:-}', [PDO::ATTR_TIMEOUT => 5]);
        } else {
            \$pdo = new PDO('mysql:host=${DB_HOST:-127.0.0.1};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-luitech}', '${DB_USERNAME:-root}', '${DB_PASSWORD:-}', [PDO::ATTR_TIMEOUT => 5]);
        }
        echo 'OK';
    } catch (PDOException \$e) {
        echo 'FAIL:' . \$e->getMessage();
    }
" 2>/dev/null || echo "FAIL:unknown")

if [[ "$DB_CHECK" == "OK" ]]; then
    echo "✓ Conexión a BD exitosa"
    echo "Ejecutando migraciones..."
    if php artisan migrate --force; then
        echo "✓ Migraciones ejecutadas"
    else
        echo "⚠ Error en migraciones. Intentando de nuevo con migrar pendientes..."
        php artisan migrate --force --pretend 2>&1 | head -20 || true
        php artisan migrate --force 2>&1 || true
    fi

    echo "Ejecutando seeders..."
    php artisan db:seed --force 2>&1 && echo "✓ Seeders ejecutados" || echo "⚠ Seeders ya ejecutados o no necesarios"
else
    echo "⚠ No se pudo conectar a la BD: $DB_CHECK"
    echo "  Las migraciones se ejecutarán manualmente después."
fi

# ── Optimizar Laravel ───────────────────────────────────────────────
echo "Optimizando Laravel..."
php artisan optimize 2>/dev/null || true

echo "=========================================="
echo "✅ Aplicación lista!"
echo "=========================================="

exec "$@"