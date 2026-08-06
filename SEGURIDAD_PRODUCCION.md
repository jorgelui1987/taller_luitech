# Checklist de seguridad para producción

## 1. Variables de entorno
- Definir valores reales para `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` y correo.
- Mantener `APP_DEBUG=false`.
- Usar HTTPS y cookies seguras.

## 2. Acceso y autenticación
- Usar contraseñas largas y únicas.
- Evitar compartir cuentas de administrador.
- Revisar periódicamente usuarios activos y roles.

## 3. Rutas sensibles
- Asegurar que solo usuarios autorizados puedan entrar a rutas de admin y superadmin.
- Mantener throttling en login y acciones sensibles.

## 4. Archivos subidos
- Limitar tipos de imagen permitidos.
- Restringir tamaño máximo de archivos.
- Revisar que los archivos se guarden en carpetas seguras.

## 5. Base de datos
- Hacer respaldos periódicos.
- Proteger credenciales de acceso.
- Mantener copias de seguridad fuera del entorno de producción principal.

## 6. Dependencias y servidor
- Mantener Laravel, PHP y paquetes actualizados.
- Revisar logs de errores y accesos regularmente.
- Configurar SSL/TLS correctamente.

## 7. Recomendación final
- Probar el sistema completo en un entorno de staging antes de publicar.
- Confirmar que HTTPS funcione correctamente y que no haya rutas expuestas.
