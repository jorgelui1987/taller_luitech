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
mkdir -p /var/www/html/storage/app/backups
mkdir -p /var/www/html/bootstrap/cache

# ── Generar .env desde variables de entorno ──────────────────────────
echo "Creando .env desde variables de entorno..."

# Generar APP_KEY si no está definida
if [ -z "$APP_KEY" ]; then
    echo "Generando nueva APP_KEY..."
    APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
fi

# Asegurar que APP_URL tenga protocolo
APP_URL="${APP_URL:-}"
if [ -n "$APP_URL" ] && [[ "$APP_URL" != http* ]]; then
    APP_URL="https://${APP_URL}"
    echo "✓ APP_URL corregida: $APP_URL"
fi

# ⚠️ IMPORTANTE: Ninguna variable tiene valor por defecto para DB.
# Todas las credenciales se definen SOLO en Dokploy → Variables.
# Esto evita que contraseñas reales queden expuestas en el repositorio.

{
    echo "APP_NAME=\"${APP_NAME:-CRM Tienda Celulares}\""
    echo "APP_ENV=${APP_ENV:-production}"
    echo "APP_KEY=${APP_KEY}"
    echo "APP_DEBUG=${APP_DEBUG:-false}"
    echo "APP_URL=${APP_URL}"
    echo "FORCE_HTTPS=${FORCE_HTTPS:-true}"
    echo "SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}"
    echo ""
    echo "DB_CONNECTION=${DB_CONNECTION}"
    echo "DB_HOST=${DB_HOST}"
    echo "DB_PORT=${DB_PORT}"
    echo "DB_DATABASE=${DB_DATABASE}"
    echo "DB_USERNAME=${DB_USERNAME}"
    echo "DB_PASSWORD=${DB_PASSWORD}"
    echo ""
    echo "SESSION_DRIVER=${SESSION_DRIVER:-file}"
    echo "SESSION_LIFETIME=${SESSION_LIFETIME:-120}"
    echo "CACHE_DRIVER=${CACHE_DRIVER:-file}"
    echo "QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}"
    echo ""
    echo "LOG_LEVEL=${LOG_LEVEL:-warning}"
    echo "TRUSTED_PROXIES=${TRUSTED_PROXIES}"
    echo ""
    echo "# Credenciales del SuperAdmin (panel /superadmin/login)"
    echo "SUPERADMIN_EMAIL=${SUPERADMIN_EMAIL:-luitechserena@gmail.com}"
    echo "SUPERADMIN_PASSWORD=${SUPERADMIN_PASSWORD:-password}"
} > /var/www/html/.env

echo "✓ .env generado correctamente"

# ── Configurar permisos ─────────────────────────────────────────────
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R appuser:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ── Limpiar caché (como appuser no-root) ────────────────────────────
echo "Limpiando cachés..."
runuser -u appuser -- php artisan config:clear 2>/dev/null || true
runuser -u appuser -- php artisan cache:clear 2>/dev/null || true
runuser -u appuser -- php artisan view:clear 2>/dev/null || true

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
    runuser -u appuser -- php artisan storage:link --force 2>/dev/null || true
fi

# ── Migraciones ─────────────────────────────────────────────────────
echo "Verificando conexión a base de datos..."
DB_CHECK=$(php -r "
    try {
        \$driver = '${DB_CONNECTION}';
        if (\$driver === 'pgsql' || \$driver === 'postgresql') {
            \$pdo = new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}', [PDO::ATTR_TIMEOUT => 5]);
        } else {
            \$pdo = new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}', [PDO::ATTR_TIMEOUT => 5]);
        }
        echo 'OK';
    } catch (PDOException \$e) {
        echo 'FAIL:' . \$e->getMessage();
    }
" 2>/dev/null || echo "FAIL:unknown")

if [[ "$DB_CHECK" == "OK" ]]; then
    echo "✓ Conexión a BD exitosa"
    echo "Ejecutando migraciones..."
    if runuser -u appuser -- php artisan migrate --force; then
        echo "✓ Migraciones ejecutadas"
    else
        echo "⚠ Error en migraciones. Intentando de nuevo con migrar pendientes..."
        runuser -u appuser -- php artisan migrate --force --pretend 2>&1 | head -20 || true
        runuser -u appuser -- php artisan migrate --force 2>&1 || true
    fi

    # ── Asegurar que el SuperAdmin existe con las credenciales configuradas ──
    # Esto corrige errores de "credenciales inválidas" cuando la BD fue
    # restaurada de un backup o la migración de credenciales corrió con
    # otras variables de entorno.
    echo "Asegurando existencia del SuperAdmin..."
    if runuser -u appuser -- php artisan superadmin:reset --force 2>&1; then
        echo "✓ SuperAdmin verificado/creado"
    else
        echo "⚠ No se pudo asegurar el SuperAdmin (revisar credenciales manualmente)"
    fi

    # ── Verificar si hay datos existentes ──────────────────────────────
    echo "Verificando si hay datos existentes..."
    HAY_DATOS=$(runuser -u appuser -- php artisan tinker --execute="echo (App\\Models\\Venta::exists() || App\\Models\\Reparacion::exists()) ? 'SI' : 'NO';" 2>/dev/null || echo "ERROR")

    if [ "$HAY_DATOS" == "SI" ]; then
        echo "⚠ Se detectaron datos existentes. Se omiten seeders y restauración."

    elif [ "$HAY_DATOS" == "NO" ]; then
        echo "Base de datos vacía. Buscando backup para restaurar..."

        # ⚠️ IMPORTANTE: En PostgreSQL NO se restauran backups .sql de MySQL.
        # Los dumps de MySQL usan backticks (`) y SET FOREIGN_KEY_CHECKS,
        # sintaxis incompatible con PostgreSQL que corrompe la base de datos
        # (causa conocida de errores en garantia_detalles y login).
        # PostgreSQL solo recibe migraciones y seeders de Laravel.
        if [ "$DB_CONNECTION" == "pgsql" ] || [ "$DB_CONNECTION" == "postgresql" ]; then
            echo "ℹ Base de datos PostgreSQL: se omiten backups MySQL por incompatibilidad de sintaxis."
        else
            # Buscar el backup .sql más reciente en storage/app/backups/
            BACKUP_DIR="/var/www/html/storage/app/backups"
            BACKUP_RECIENTE=$(ls -t "$BACKUP_DIR"/*.sql 2>/dev/null | head -1 || true)

            if [ -n "$BACKUP_RECIENTE" ] && [ -f "$BACKUP_RECIENTE" ]; then
                echo "✓ Backup encontrado: $(basename "$BACKUP_RECIENTE")"
            echo "Restaurando backup automáticamente..."

            # Extraer las sentencias SQL y ejecutarlas
            # (misma lógica que BackupController::restaurar pero sin subir archivo)
            RESTORE_RESULT=$(runuser -u appuser -- php artisan tinker --execute="
                \$contenido = file_get_contents('$BACKUP_RECIENTE');
                \$statements = preg_split('/;\s*[\r\n]+/', \$contenido);
                \$comandosBloqueados = ['DROP DATABASE', 'DROP USER', 'GRANT', 'REVOKE', 'ALTER USER', 'CREATE USER'];
                \$contador = 0;
                foreach (\$statements as \$stmt) {
                    \$stmt = trim(\$stmt);
                    if (empty(\$stmt) || preg_match('/^--/', \$stmt) || preg_match('/^\/\*/', \$stmt)) continue;
                    \$stmtUpper = strtoupper(\$stmt);
                    \$bloqueado = false;
                    foreach (\$comandosBloqueados as \$comando) {
                        if (strpos(\$stmtUpper, \$comando) !== false) { \$bloqueado = true; break; }
                    }
                    if (\$bloqueado) continue;
                    try {
                        \Illuminate\Support\Facades\DB::unprepared(\$stmt);
                        \$contador++;
                    } catch (\Throwable \$e) {
                        // Continuar con la siguiente sentencia
                    }
                }
                echo \"OK:{\$contador} sentencias ejecutadas\";
            " 2>&1 || echo "ERROR")

            if [[ "$RESTORE_RESULT" == *"OK:"* ]]; then
                echo "✅ Restauración completada: $RESTORE_RESULT"
                echo "Verificando datos después de restaurar..."
                HAY_DATOS_AFTER=$(runuser -u appuser -- php artisan tinker --execute="echo (App\\Models\\Venta::exists() || App\\Models\\Reparacion::exists()) ? 'SI' : 'NO';" 2>/dev/null || echo "ERROR")
                if [ "$HAY_DATOS_AFTER" == "SI" ]; then
                    echo "✅ Base de datos restaurada correctamente con el backup."
                else
                    echo "⚠ El backup no contenía datos de ventas/reparaciones. Se continuará."
                fi
            else
                echo "⚠ Error al restaurar el backup: $RESTORE_RESULT"
                echo "  Se continuará sin restaurar."
            fi
            else
                echo "⚠ No se encontraron backups en storage/app/backups/"
            fi
        fi

        # ── Seeders SOLO si no hay datos después del intento de restauración ──
        HAY_DATOS_FINAL=$(runuser -u appuser -- php artisan tinker --execute="echo (App\\Models\\Venta::exists() || App\\Models\\Reparacion::exists()) ? 'SI' : 'NO';" 2>/dev/null || echo "ERROR")

        if [ "$HAY_DATOS_FINAL" == "NO" ]; then
            echo "Base de datos sigue vacía. Ejecutando seeders básicos (usuarios y catálogo)..."

            # Ejecutar DatabaseSeeder con flag para NO crear datos demo
            # SOLO crea SuperAdmin, usuarios básicos, categorías y marcas
            if [ "$APP_ENV" == "production" ]; then
                echo "Modo producción: ejecutando ONLY seeders seguros (usuarios, categorías, marcas)..."
                SEED_RESULT=$(runuser -u appuser -- php artisan db:seed --class=DatabaseSeeder --force 2>&1 || echo "ERROR")
                if [[ "$SEED_RESULT" == *"ERROR"* ]]; then
                    echo "⚠ Error en seeders básicos: $SEED_RESULT"
                else
                    echo "✓ Seeders básicos ejecutados"
                fi
            else
                echo "Modo desarrollo: ejecutando todos los seeders (incluye datos demo)..."
                runuser -u appuser -- php artisan db:seed --force 2>&1 && echo "✓ Seeders ejecutados" || echo "⚠ Error en seeders"
            fi
        fi
    else
        echo "⚠ No se pudo verificar. Se omiten los seeders por seguridad."
    fi
else
    echo "⚠ No se pudo conectar a la BD: $DB_CHECK"
    echo "  Las migraciones se ejecutarán manualmente después."
fi

# ── Configurar cron para el scheduler de Laravel ────────────────────
echo "Configurando cron para el programador de Laravel..."

# Añadir la entrada cron para el scheduler si no existe
# Esto ejecuta "php artisan schedule:run" cada minuto como el usuario appuser,
# que dispara el backup automático diario a las 2:00 AM
CRON_EXISTS=$(crontab -l 2>/dev/null | grep "php artisan schedule:run" || true)
if [ -z "$CRON_EXISTS" ]; then
    echo "* * * * * runuser -u appuser -- php /var/www/html/artisan schedule:run >> /dev/null 2>&1" | crontab -
    echo "✓ Cron configurado para el scheduler de Laravel"
else
    echo "✓ Cron ya estaba configurado"
fi

# ── Optimizar Laravel ───────────────────────────────────────────────
echo "Optimizando Laravel..."
runuser -u appuser -- php artisan optimize 2>/dev/null || true

# ── Iniciar cron en segundo plano (para que los backups automáticos funcionen) ──
echo "Iniciando cron en segundo plano..."
cron -f &
CRON_PID=$!
echo "✓ Cron iniciado (PID: $CRON_PID)"

echo "=========================================="
echo "✅ Aplicación lista!"
echo "=========================================="

exec "$@"