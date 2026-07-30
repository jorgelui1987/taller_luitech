-- EJECUTA ESTO EN PHPMYADMIN O TERMINAL DE MYSQL
-- Para que el campo 'Tipo de Producto' funcione (celular/accesorio/otro)

ALTER TABLE `productos`
ADD COLUMN `tipo` VARCHAR(20) NOT NULL DEFAULT 'celular' AFTER `id`;