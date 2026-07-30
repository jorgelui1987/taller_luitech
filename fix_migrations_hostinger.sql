-- =============================================
-- SCRIPT DE LIMPIEZA PARA HOSTINGER
-- Ejecutar esto ANTES de hacer git pull y php artisan migrate
-- =============================================

-- 1. Eliminar tablas creadas parcialmente (si existen)
DROP TABLE IF EXISTS `movimientos_stock`;
DROP TABLE IF EXISTS `proveedores`;

-- 2. Eliminar registros de migraciones fallidas (los nombres viejos)
DELETE FROM `migrations` WHERE `migration` LIKE '%movimientos_stock%';
DELETE FROM `migrations` WHERE `migration` LIKE '%proveedores%';
DELETE FROM `migrations` WHERE `migration` LIKE '%000017%';
DELETE FROM `migrations` WHERE `migration` LIKE '%000019%';

-- 3. Verificar que la tabla tenants existe y tiene InnoDB engine
ALTER TABLE `tenants` ENGINE = InnoDB;

-- 4. Verificar que todas las tablas referenciadas tengan InnoDB y charset correcto
ALTER TABLE `productos` ENGINE = InnoDB;
ALTER TABLE `users` ENGINE = InnoDB;
ALTER TABLE `ventas` ENGINE = InnoDB;
ALTER TABLE `configuracion` ENGINE = InnoDB;

SELECT 'LIMPIEZA COMPLETADA' AS resultado;