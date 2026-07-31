#!/bin/bash

set -e

# Verificar que vendor/autoload.php existe
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "ERROR: vendor/autoload.php no encontrado. Ejecuta 'composer install' primero."
    exit 1
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

# ── APP_KEY ──────────────────────────────────────────────────────────────
# Si no existe .env, crearlo desde variables de entorno
if [ ! -f /var/www/html/.env ]; then
    echo "Creando .env desde variables de entorno..."

    # Si APP_KEY no está definida como variable de entorno, generar una
    if [ -z "$APP_KEY" ]; then
        echo "Generando nueva APP_KEY..."
        APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
        echo "APP_KEY generada: ${APP_KEY:0:20}..."
    fi

    {
        echo "APP_NAME=\"${APP_NAME:-CRM Tienda Celulares}\""
        echo "APP_ENV=${APP_ENV:-production}"
        echo "APP_KEY=${APP_KEY}"
        echo "APP_DEBUG=${APP_DEBUG:-false}"
        echo "APP_URL=${APP_URL:-http://localhost}"
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
        echo "# Seguridad"
        echo "APP_DEBUG=${APP_DEBUG:-false}"
        echo "TRUSTED_PROXIES=${TRUSTED_PROXIES:-*}"
        echo "LOG_LEVEL=${LOG_LEVEL:-warning}"
    } > /var/www/html/.env
    echo ".env creado exitosamente"
else
    echo "Usando .env existente"
fi

# Verificar que APP_KEY no esté vacía en .env
if grep -q "APP_KEY=$" /var/www/html/.env 2>/dev/null; then
    echo "ERROR: APP_KEY está vacía en .env. Generando nueva..."
    NEW_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    sed -i "s/APP_KEY=$/APP_KEY=${NEW_KEY}/" /var/www/html/.env
    echo "Nueva APP_KEY generada: ${NEW_KEY:0:20}..."
fi

echo "=== Configuración actual (ocultando contraseñas) ==="
grep -v "PASSWORD\|DB_PASSWORD" /var/www/html/.env 2>/dev/null || true
echo "============================"

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

# Crear enlace simbólico de storage si no existe
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creando enlace simbólico de storage..."
    cd /var/www/html && php artisan storage:link --force 2>/dev/null || true
fi

# Ejecutar migraciones (solo si hay conexión a BD)
echo "Verificando conexión a base de datos..."
if php -r "
    \$host = getenv('DB_HOST') ?: '127.0.0.1';
    \$port = getenv('DB_PORT') ?: '3306';
    \$db = getenv('DB_DATABASE') ?: '';
    \$user = getenv('DB_USERNAME') ?: 'root';
    \$pass = getenv('DB_PASSWORD') ?: '';
    try {
        \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_TIMEOUT => 5]);
        echo \"OK\n\";
    } catch (PDOException \$e) {
        echo \"ERROR: \" . \$e->getMessage() . \"\n\";
        exit(1);
    }
" 2>/dev/null; then
    echo "Conexión a BD exitosa. Ejecutando migraciones..."
    cd /var/www/html && php artisan migrate --force 2>/dev/null || echo "Migraciones ya ejecutadas o no necesarias"
    echo "Migraciones completadas"

    # Ejecutar seeders solo si la tabla de usuarios está vacía
    USERS_COUNT=$(cd /var/www/html && php -r "try { echo DB::table('users')->count(); } catch (\Exception \$e) { echo '0'; }" 2>/dev/null || echo "0")
    if [ "$USERS_COUNT" = "0" ]; then
        echo "Ejecutando seeders iniciales..."
        cd /var/www/html && php artisan db:seed --force 2>/dev/null || echo "Seeders no ejecutados"
        echo "Seeders completados"
    else
        echo "Seeders omitidos (datos ya existen)"
    fi
else
    echo "WARNING: No se pudo conectar a la BD. Las migraciones se ejecutarán cuando la BD esté disponible."
fi

# Optimizar Laravel para producción
echo "Optimizando Laravel para producción..."
cd /var/www/html && php artisan optimize 2>/dev/null || true

echo "=========================================="
echo "Aplicación lista! Servidor iniciado."
echo "=========================================="

exec "$@"