# Guía básica de despliegue en hosting

## 1. Preparar el servidor
- Subir todos los archivos del proyecto.
- Asegurar que PHP y Composer estén disponibles.
- Crear la base de datos MySQL.

## 2. Configurar entorno
- Copiar `.env` y ajustar:
  - `APP_URL` con el dominio real.
  - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
  - `APP_ENV=production`.
  - `APP_DEBUG=false`.

## 3. Instalar dependencias
- Ejecutar `composer install --optimize-autoloader --no-dev`.
- Ejecutar `php artisan migrate`.
- Ejecutar `php artisan db:seed` si es necesario.

## 4. Permisos
- Dar permisos de escritura a `storage` y `bootstrap/cache`.
- Asegurar que `public` sea la carpeta raíz del hosting.

## 5. Verificar
- Probar login, registro y rutas principales.
- Revisar que los archivos subidos se guarden correctamente.
- Confirmar que HTTPS funcione.
