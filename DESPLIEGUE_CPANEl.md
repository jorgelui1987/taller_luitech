# Despliegue en cPanel

## 1. Preparar el hosting
- Crear una base de datos MySQL.
- Crear un usuario para esa base de datos.
- Añadir el dominio o subdominio.

## 2. Subir archivos
- Subir el proyecto completo a la carpeta pública del dominio.
- Si el hosting usa `public_html`, colocar el contenido de `public/` allí y dejar el resto fuera del directorio público.

## 3. Configurar .env
Ajustar estas variables:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://tu-dominio.com`
- `DB_CONNECTION=mysql`
- `DB_HOST=localhost`
- `DB_DATABASE=tu_base`
- `DB_USERNAME=tu_usuario`
- `DB_PASSWORD=tu_password`

## 4. Instalar dependencias
- Ejecutar desde Terminal o SSH:
  - `composer install --no-dev --optimize-autoloader`
  - `php artisan migrate`
  - `php artisan storage:link`

## 5. Permisos
- Dar permisos de escritura a:
  - `storage`
  - `bootstrap/cache`

## 6. Verificar
- Probar login, registro y módulos principales.
- Revisar que las imágenes y archivos se guarden correctamente.
