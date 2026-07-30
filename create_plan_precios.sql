-- =============================================
-- Crear tabla de precios de planes
-- Ejecutar esto en la base de datos de producción
-- =============================================

CREATE TABLE IF NOT EXISTS `plan_precios` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `plan_key` varchar(50) NOT NULL,
    `nombre` varchar(100) NOT NULL,
    `precio_mensual` decimal(10,2) NOT NULL DEFAULT '0.00',
    `moneda` varchar(10) NOT NULL DEFAULT 'PEN',
    `simbolo` varchar(10) NOT NULL DEFAULT 'S/',
    `descripcion` text,
    `activo` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `plan_precios_plan_key_unique` (`plan_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar los 4 planes con precios por defecto
INSERT INTO `plan_precios` (`plan_key`, `nombre`, `precio_mensual`, `moneda`, `simbolo`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
('gratis', 'Gratis', 0.00, 'PEN', 'S/', 'Para empezar', 1, NOW(), NOW()),
('basico', 'Básico', 49.00, 'PEN', 'S/', 'Para negocios pequeños', 1, NOW(), NOW()),
('profesional', 'Profesional', 99.00, 'PEN', 'S/', 'Para negocios en crecimiento', 1, NOW(), NOW()),
('empresarial', 'Empresarial', 199.00, 'PEN', 'S/', 'Para grandes tiendas', 1, NOW(), NOW());