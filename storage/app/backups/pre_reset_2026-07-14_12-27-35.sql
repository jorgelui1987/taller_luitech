-- ==============================================================
--  CRM Tienda Celulares — Backup Completo
--  Generado  : 14/07/2026 12:27:35
--  Base datos: tiendacelulares_crm
--  MySQL     : 8.4.3
-- ==============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------
-- Tabla: `categorias`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` VALUES
('1', 'Smartphones', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('2', 'Tablets', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('3', 'Accesorios', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('4', 'Audífonos', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('5', 'Cargadores', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('6', 'Cases y Fundas', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('7', 'Repuestos', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47');

-- --------------------------------------------------------------
-- Tabla: `clientes`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `celular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `ciudad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `tipo` enum('particular','empresa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'particular',
  `empresa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_email_unique` (`email`),
  UNIQUE KEY `clientes_dni_unique` (`dni`),
  KEY `clientes_tenant_id_index` (`tenant_id`),
  CONSTRAINT `clientes_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `clientes` VALUES
('1', NULL, 'María', 'García', 'maria.garcia@gmail.com', '987654321', NULL, '45123456', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('2', NULL, 'Carlos', 'López', 'carlos.lopez@gmail.com', '965432187', NULL, '32145678', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('3', NULL, 'Ana', 'Martínez', 'ana.martinez@hotmail.com', '974561230', NULL, '56789012', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('4', NULL, 'Pedro', 'Sánchez', 'pedro.sanchez@outlook.com', '912345678', NULL, '78901234', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('5', NULL, 'Lucía', 'Torres', NULL, '998765432', NULL, '89012345', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('6', NULL, 'Roberto', 'Flores', 'roberto.flores@gmail.com', '945678901', NULL, '12345671', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('7', NULL, 'Elena', 'Vásquez', 'elena.vasquez@techcorp.pe', '934567890', NULL, '23456782', NULL, 'Lima', NULL, 'empresa', 'TechCorp SAC', '20512345671', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('8', NULL, 'Miguel', 'Quispe', 'miguel.quispe@outlook.com', '923456789', NULL, '34567893', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('9', NULL, 'Sofía', 'Mendoza', 'sofia.mendoza@gmail.com', '912345670', NULL, '45678904', NULL, 'Lima', NULL, 'particular', NULL, NULL, NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('10', NULL, 'Diego', 'Herrera', 'diego.herrera@movilstore.pe', '901234567', NULL, '56789015', NULL, 'Lima', NULL, 'empresa', 'MovilStore EIRL', '20612345672', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47');

-- --------------------------------------------------------------
-- Tabla: `configuracion`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `nombre_tienda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `igv` decimal(5,2) NOT NULL DEFAULT '18.00',
  `moneda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PEN',
  `simbolo_moneda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S/.',
  `terminos_garantia` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `configuracion_tenant_id_index` (`tenant_id`),
  CONSTRAINT `configuracion_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracion` VALUES
('1', NULL, 'LUITECH', '22484469-7', 'ohiginn 564', '982209690', '+56982209690', 'luitechchile@gmail.com', 'storage/logos/yubRGqIDwr6KEiynIEKPwc9Fh0Kgxhw2ItfAGOfT.jpg', '18.00', 'CLP', '$', NULL, '2026-07-14 10:42:19', '2026-07-14 10:42:19'),
('2', '1', 'GETCELU', NULL, NULL, NULL, NULL, NULL, NULL, '18.00', 'PEN', 'S/', NULL, '2026-07-14 12:17:44', '2026-07-14 12:17:44');

-- --------------------------------------------------------------
-- Tabla: `detalle_ventas`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `detalle_ventas`;
CREATE TABLE `detalle_ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `venta_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL,
  `imei_vendido` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_ventas_venta_id_foreign` (`venta_id`),
  KEY `detalle_ventas_producto_id_foreign` (`producto_id`),
  KEY `detalle_ventas_tenant_id_index` (`tenant_id`),
  CONSTRAINT `detalle_ventas_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `detalle_ventas_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `detalle_ventas` VALUES
('1', NULL, '1', '1', '1', '899.00', '0.00', '899.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('2', NULL, '1', '8', '2', '25.00', '0.00', '50.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('3', NULL, '2', '3', '1', '3499.00', '0.00', '3499.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('4', NULL, '3', '4', '1', '999.00', '0.00', '999.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('5', NULL, '3', '6', '1', '199.00', '0.00', '199.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('6', NULL, '4', '5', '2', '699.00', '0.00', '1398.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('7', NULL, '5', '2', '1', '1299.00', '0.00', '1299.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('8', NULL, '5', '7', '2', '35.00', '0.00', '70.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('9', NULL, '6', '9', '1', '849.00', '0.00', '849.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('10', NULL, '6', '6', '1', '199.00', '0.00', '199.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('11', NULL, '7', '10', '1', '629.00', '0.00', '629.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('12', NULL, '7', '8', '3', '25.00', '0.00', '75.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('13', NULL, '8', '3', '1', '3499.00', '0.00', '3499.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('14', NULL, '8', '7', '1', '35.00', '0.00', '35.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('15', NULL, '9', '1', '1', '899.00', '0.00', '899.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('16', NULL, '10', '2', '1', '1299.00', '0.00', '1299.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('17', NULL, '11', '4', '2', '999.00', '0.00', '1998.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('18', NULL, '12', '5', '1', '699.00', '0.00', '699.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('19', NULL, '12', '6', '2', '199.00', '0.00', '398.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('20', NULL, '13', '9', '2', '849.00', '0.00', '1698.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('21', NULL, '14', '3', '1', '3499.00', '0.00', '3499.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('22', NULL, '14', '8', '1', '25.00', '0.00', '25.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('23', NULL, '15', '10', '1', '629.00', '0.00', '629.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('24', NULL, '15', '7', '3', '35.00', '0.00', '105.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('25', NULL, '16', '1', '1', '899.00', '0.00', '899.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('26', NULL, '16', '6', '1', '199.00', '0.00', '199.00', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48');

-- --------------------------------------------------------------
-- Tabla: `marcas`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `marcas`;
CREATE TABLE `marcas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `marcas` VALUES
('1', 'Samsung', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('2', 'Apple', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('3', 'Xiaomi', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('4', 'Motorola', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('5', 'Huawei', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('6', 'OPPO', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('7', 'Realme', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('8', 'OnePlus', NULL, '1', '2026-07-14 10:37:47', '2026-07-14 10:37:47');

-- --------------------------------------------------------------
-- Tabla: `migrations`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES
('1', '2019_12_14_000001_create_personal_access_tokens_table', '1'),
('2', '2024_01_01_000001_create_users_table', '1'),
('3', '2024_01_01_000002_create_clientes_table', '1'),
('4', '2024_01_01_000003_create_categorias_table', '1'),
('5', '2024_01_01_000004_create_marcas_table', '1'),
('6', '2024_01_01_000005_create_productos_table', '1'),
('7', '2024_01_01_000006_create_ventas_table', '1'),
('8', '2024_01_01_000007_create_detalle_ventas_table', '1'),
('9', '2024_01_01_000008_create_reparaciones_table', '1'),
('10', '2024_01_01_000009_create_configuracion_table', '1'),
('11', '2024_01_01_000010_add_whatsapp_to_configuracion', '1'),
('12', '2024_01_01_000011_add_tipo_codigo_to_reparaciones', '1'),
('13', '2024_01_01_000012_add_tipo_codigo_to_reparaciones', '1'),
('14', '2024_01_01_000013_add_patron_secuencia_to_reparaciones', '1'),
('15', '2024_01_01_000014_make_dispositivo_nullable_in_reparaciones', '1'),
('16', '2024_01_01_000015_add_abono_to_reparaciones', '2'),
('17', '2024_01_01_000016_add_total_to_reparaciones', '3'),
('18', '2026_07_14_114016_create_tenants_table', '4'),
('19', '2026_07_14_114020_add_tenant_id_to_tables', '4'),
('20', '2026_07_14_114613_add_superadmin_role_to_users', '5');

-- --------------------------------------------------------------
-- Tabla: `personal_access_tokens`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- Tabla: `productos`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `categoria_id` bigint unsigned NOT NULL,
  `marca_id` bigint unsigned NOT NULL,
  `modelo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `almacenamiento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `stock_minimo` int NOT NULL DEFAULT '5',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imei` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicion` enum('nuevo','reacondicionado','usado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nuevo',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productos_codigo_unique` (`codigo`),
  KEY `productos_categoria_id_foreign` (`categoria_id`),
  KEY `productos_marca_id_foreign` (`marca_id`),
  KEY `productos_tenant_id_index` (`tenant_id`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `productos_marca_id_foreign` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `productos_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `productos` VALUES
('1', NULL, 'SAM-A54-128', 'Samsung Galaxy A54', NULL, '1', '1', 'A54', NULL, '128GB', '8GB', '650.00', '899.00', '12', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('2', NULL, 'SAM-S24-256', 'Samsung Galaxy S24', NULL, '1', '1', 'S24', NULL, '256GB', '12GB', '950.00', '1299.00', '6', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('3', NULL, 'APP-IPH15-128', 'iPhone 15', NULL, '1', '2', '15', NULL, '128GB', '6GB', '2500.00', '3499.00', '2', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('4', NULL, 'XIA-13T-256', 'Xiaomi 13T', NULL, '1', '3', '13T', NULL, '256GB', '12GB', '700.00', '999.00', '9', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('5', NULL, 'MOT-G84-256', 'Motorola Moto G84', NULL, '1', '4', 'G84', NULL, '256GB', '12GB', '480.00', '699.00', '7', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('6', NULL, 'AUD-SAM-TW', 'Samsung Galaxy Buds2', NULL, '4', '1', 'Buds2', NULL, NULL, NULL, '120.00', '199.00', '15', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('7', NULL, 'CAR-USB-C-65', 'Cargador USB-C 65W', NULL, '5', '3', NULL, NULL, NULL, NULL, '18.00', '35.00', '44', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('8', NULL, 'CASE-IPH15', 'Case iPhone 15 Pro', NULL, '6', '2', NULL, NULL, NULL, NULL, '8.00', '25.00', '24', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('9', NULL, 'HUA-NOV11-128', 'Huawei Nova 11', NULL, '1', '5', 'Nova 11', NULL, '128GB', '8GB', '580.00', '849.00', '15', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48'),
('10', NULL, 'OPP-A58-128', 'OPPO A58', NULL, '1', '6', 'A58', NULL, '128GB', '6GB', '420.00', '629.00', '18', '3', NULL, NULL, 'nuevo', '1', '2026-07-14 10:37:47', '2026-07-14 10:37:48');

-- --------------------------------------------------------------
-- Tabla: `reparaciones`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `reparaciones`;
CREATE TABLE `reparaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `numero_orden` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `tecnico_id` bigint unsigned DEFAULT NULL,
  `dispositivo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marca` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imei` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_dispositivo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_equipo` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patron_secuencia` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `falla_reportada` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `solucion` text COLLATE utf8mb4_unicode_ci,
  `presupuesto` decimal(10,2) DEFAULT NULL,
  `costo_final` decimal(10,2) DEFAULT NULL,
  `abono` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` enum('recibido','en_diagnostico','esperando_repuesto','en_reparacion','listo','entregado','no_reparable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recibido',
  `prioridad` enum('baja','media','alta','urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `fecha_recepcion` datetime NOT NULL,
  `fecha_estimada` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `garantia` tinyint(1) NOT NULL DEFAULT '0',
  `dias_garantia` int NOT NULL DEFAULT '30',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reparaciones_numero_orden_unique` (`numero_orden`),
  KEY `reparaciones_cliente_id_foreign` (`cliente_id`),
  KEY `reparaciones_tecnico_id_foreign` (`tecnico_id`),
  KEY `reparaciones_tenant_id_index` (`tenant_id`),
  CONSTRAINT `reparaciones_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reparaciones_tecnico_id_foreign` FOREIGN KEY (`tecnico_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reparaciones_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reparaciones` VALUES
('1', NULL, 'REP-000001', '1', '3', 'Samsung Galaxy A32', 'Samsung', 'A32', NULL, NULL, NULL, NULL, NULL, NULL, 'Pantalla rota por caída', 'LCD fragmentado, táctil sin respuesta', 'Reemplazo módulo LCD + táctil', '180.00', '160.00', '0.00', '0.00', 'entregado', 'media', '2026-02-10 09:00:00', '2026-02-13 00:00:00', '2026-02-13 17:30:00', NULL, '1', '30', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('2', NULL, 'REP-000002', '2', '3', 'iPhone 13', 'Apple', '13', NULL, NULL, NULL, NULL, NULL, NULL, 'Batería no carga, apagados repentinos', 'Batería degradada al 64%', 'Cambio batería original Apple', '220.00', '200.00', '0.00', '0.00', 'entregado', 'alta', '2026-02-18 10:30:00', '2026-02-21 00:00:00', '2026-02-20 15:00:00', NULL, '1', '90', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('3', NULL, 'REP-000003', '3', '3', 'Xiaomi Redmi Note 11', 'Xiaomi', 'Redmi Note 11', NULL, NULL, NULL, NULL, NULL, NULL, 'Se apaga solo cada 30 minutos', 'Software corrupto + batería débil', 'Flash firmware MIUI + reemplazo batería', '150.00', '130.00', '0.00', '0.00', 'entregado', 'baja', '2026-03-01 11:00:00', '2026-03-06 00:00:00', '2026-03-05 16:45:00', NULL, '0', '0', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('4', NULL, 'REP-000004', '4', '3', 'Samsung Galaxy S21', 'Samsung', 'S21', NULL, NULL, NULL, NULL, NULL, NULL, 'Cámara trasera no enfoca', 'Módulo de cámara principal dañado', 'Reemplazo módulo cámara 108MP Samsung', '350.00', '320.00', '0.00', '0.00', 'entregado', 'urgente', '2026-03-15 09:30:00', '2026-03-19 00:00:00', '2026-03-18 14:00:00', NULL, '1', '60', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('5', NULL, 'REP-000005', '5', '3', 'Motorola Moto G52', 'Motorola', 'Moto G52', NULL, NULL, NULL, NULL, NULL, NULL, 'Micrófono no funciona en llamadas', 'Micrófono MEMS dañado por humedad', 'Reemplazo micrófono + limpieza placa', '120.00', '100.00', '0.00', '0.00', 'listo', 'media', '2026-04-03 10:00:00', '2026-04-07 00:00:00', NULL, NULL, '1', '30', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('6', NULL, 'REP-000006', '1', '3', 'Xiaomi Redmi 10C', 'Xiaomi', 'Redmi 10C', NULL, NULL, NULL, NULL, NULL, NULL, 'Conector de carga suelto', 'Puerto USB-C desgastado, pines doblados', 'Reemplazo módulo USB-C', '80.00', '70.00', '0.00', '0.00', 'listo', 'media', '2026-04-22 14:30:00', '2026-04-27 00:00:00', NULL, NULL, '1', '30', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('7', NULL, 'REP-000007', '2', '3', 'iPhone 12 Pro', 'Apple', '12 Pro', NULL, NULL, NULL, NULL, NULL, NULL, 'Face ID no funciona', 'Sensor TrueDepth dañado', NULL, '450.00', NULL, '0.00', '0.00', 'en_reparacion', 'alta', '2026-04-12 09:00:00', '2026-04-18 00:00:00', NULL, NULL, '0', '0', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('8', NULL, 'REP-000008', '3', '3', 'Huawei P30 Lite', 'Huawei', 'P30 Lite', NULL, NULL, NULL, NULL, NULL, NULL, 'Pantalla parpadea y se pone verde', 'Flex de pantalla defectuoso, repuesto importado', NULL, '200.00', NULL, '0.00', '0.00', 'esperando_repuesto', 'media', '2026-04-20 11:15:00', '2026-05-05 00:00:00', NULL, NULL, '0', '0', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('9', NULL, 'REP-000009', '4', '3', 'Samsung Galaxy A54', 'Samsung', 'A54', NULL, NULL, NULL, NULL, NULL, NULL, 'Caída al agua, no enciende', NULL, NULL, NULL, NULL, '0.00', '0.00', 'recibido', 'urgente', '2026-05-01 09:00:00', '2026-05-06 00:00:00', NULL, NULL, '0', '0', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('10', NULL, 'REP-000010', '5', '3', 'Xiaomi 12', 'Xiaomi', '12', NULL, NULL, NULL, NULL, NULL, NULL, 'Parte superior del táctil no responde', NULL, NULL, NULL, NULL, '0.00', '0.00', 'en_diagnostico', 'media', '2026-05-01 10:30:00', '2026-05-08 00:00:00', NULL, NULL, '0', '0', '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('11', NULL, 'REP-000011', '3', '3', NULL, 'Samsung', 'dfdzfdz', 'DX3F3Q3MKXK1', 'celular', '1902', 'pin', NULL, NULL, 'dfhgfdhdhjfgjhgfjfghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghghj', NULL, NULL, '70000.00', NULL, '0.00', '0.00', 'recibido', 'media', '2026-07-14 10:40:55', '2026-07-15 00:00:00', NULL, NULL, '0', '30', '2026-07-14 10:40:55', '2026-07-14 10:40:55'),
('12', NULL, 'REP-000012', '3', '3', NULL, 'Xiaomi', 'cnvc', 'DX3K33VQN736', 'celular', NULL, 'patron', '1-2-3-6-5-8', 'dsfdf', 'SDRDSRFDSF', NULL, NULL, '40000.00', NULL, '10000.00', '30000.00', 'recibido', 'media', '2026-07-14 11:17:55', '2026-07-15 00:00:00', NULL, NULL, '0', '30', '2026-07-14 11:17:55', '2026-07-14 11:17:55'),
('13', NULL, 'REP-000013', '3', '3', NULL, 'Apple', 'S24', 'DX3G7P7ZN735', 'celular', NULL, 'patron', '1-2-3-6-5-8', NULL, 'STFDSTFD', NULL, NULL, '60000.00', NULL, '5000.00', '55000.00', 'recibido', 'media', '2026-07-14 11:22:32', NULL, NULL, NULL, '0', '30', '2026-07-14 11:22:32', '2026-07-14 11:22:32');

-- --------------------------------------------------------------
-- Tabla: `tenants`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subdominio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dominio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_contacto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono_contacto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gratis',
  `estado` enum('activo','suspendido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moneda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PEN',
  `simbolo_moneda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S/',
  `impuesto` decimal(5,2) NOT NULL DEFAULT '18.00',
  `max_usuarios` int NOT NULL DEFAULT '5',
  `max_productos` int NOT NULL DEFAULT '100',
  `fecha_expiracion` timestamp NULL DEFAULT NULL,
  `configuracion_extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_empresa_unique` (`empresa`),
  UNIQUE KEY `tenants_subdominio_unique` (`subdominio`),
  UNIQUE KEY `tenants_dominio_unique` (`dominio`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tenants` VALUES
('1', 'GETCELU', 'getcelu', NULL, 'jorgecastro19876@gmail.com', NULL, 'gratis', 'activo', NULL, NULL, 'PEN', 'S/', '18.00', '3', '50', NULL, NULL, '2026-07-14 12:17:44', '2026-07-14 12:25:49');

-- --------------------------------------------------------------
-- Tabla: `users`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('admin','vendedor','tecnico','superadmin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_tenant_id_index` (`tenant_id`),
  CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES
('1', NULL, 'Administrador', 'admin@tienda.com', '$2y$12$Pd696HikMXjeHj0nbb.Ls.cF1mOyK2enX5UKguXVhQTzV49VWwnGa', 'admin', NULL, NULL, '1', NULL, NULL, '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('2', NULL, 'Juan Vendedor', 'vendedor@tienda.com', '$2y$12$DRU32jr7sUoQSX7lFhgclur0PHOC3.P30dJQdm.8yLwxU4KWFQI5y', 'vendedor', NULL, NULL, '1', NULL, NULL, '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('3', NULL, 'Carlos Técnico', 'tecnico@tienda.com', '$2y$12$4jR97rxwS0CwGprD/l70QenQ6IkgapeBQ8MytkGjDJgHI.zMtDkzu', 'tecnico', NULL, NULL, '1', NULL, NULL, '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('4', NULL, 'Super Admin', 'superadmin@admin.com', '$2y$12$3hglEu8bfl8YiA.LhbtrJ.CQ2S1eJbqshKppfVJBmDVafSpYoesVa', 'superadmin', NULL, NULL, '1', NULL, NULL, '2026-07-14 11:46:47', '2026-07-14 11:46:47'),
('5', NULL, 'jorge luis castro caceres', 'camila1987hijo@gmail.com', '$2y$12$rfad6E6.nlZxTgUG4Hcw9.LFyxrv9zOvoSl0WgxCTneJPFuN/uLLW', 'tecnico', '982209690', NULL, '1', NULL, NULL, '2026-07-14 12:00:52', '2026-07-14 12:00:52'),
('6', '1', 'jorge luis', 'jorgecastro19876@gmail.com', '$2y$12$VnVfR8Ysk652xSF4iljhy.10JnwnAiSrUNfLoJtbIrgej2DRi.sgW', 'admin', NULL, NULL, '1', NULL, NULL, '2026-07-14 12:17:44', '2026-07-14 12:17:44');

-- --------------------------------------------------------------
-- Tabla: `ventas`
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `numero_venta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `fecha_venta` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('efectivo','tarjeta','transferencia','cuotas','yape','plin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `estado` enum('pendiente','completada','cancelada','devuelta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completada',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_numero_venta_unique` (`numero_venta`),
  KEY `ventas_cliente_id_foreign` (`cliente_id`),
  KEY `ventas_user_id_foreign` (`user_id`),
  KEY `ventas_tenant_id_index` (`tenant_id`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ventas` VALUES
('1', NULL, 'VTA-000001', '1', '2', '2025-11-05 10:20:00', '949.00', '0.00', '170.82', '1119.82', 'efectivo', 'completada', NULL, '2026-07-14 10:37:47', '2026-07-14 10:37:47'),
('2', NULL, 'VTA-000002', '2', '1', '2025-11-18 15:45:00', '3499.00', '0.00', '629.82', '4128.82', 'tarjeta', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('3', NULL, 'VTA-000003', '3', '2', '2025-12-03 11:00:00', '1198.00', '0.00', '215.64', '1413.64', 'yape', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('4', NULL, 'VTA-000004', '4', '1', '2025-12-20 16:30:00', '1398.00', '0.00', '251.64', '1649.64', 'efectivo', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('5', NULL, 'VTA-000005', '5', '2', '2026-01-08 09:15:00', '1369.00', '0.00', '246.42', '1615.42', 'transferencia', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('6', NULL, 'VTA-000006', '1', '1', '2026-01-22 14:00:00', '1048.00', '0.00', '188.64', '1236.64', 'tarjeta', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('7', NULL, 'VTA-000007', '2', '2', '2026-02-04 10:30:00', '704.00', '0.00', '126.72', '830.72', 'yape', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('8', NULL, 'VTA-000008', '3', '1', '2026-02-14 13:00:00', '3534.00', '0.00', '636.12', '4170.12', 'efectivo', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('9', NULL, 'VTA-000009', '4', '2', '2026-02-28 17:10:00', '899.00', '0.00', '161.82', '1060.82', 'plin', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('10', NULL, 'VTA-000010', '5', '1', '2026-03-05 09:45:00', '1299.00', '0.00', '233.82', '1532.82', 'tarjeta', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('11', NULL, 'VTA-000011', '1', '2', '2026-03-14 11:30:00', '1998.00', '0.00', '359.64', '2357.64', 'efectivo', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('12', NULL, 'VTA-000012', '2', '1', '2026-03-25 16:00:00', '1097.00', '0.00', '197.46', '1294.46', 'yape', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('13', NULL, 'VTA-000013', '3', '2', '2026-04-02 10:00:00', '1698.00', '0.00', '305.64', '2003.64', 'transferencia', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('14', NULL, 'VTA-000014', '4', '1', '2026-04-15 14:30:00', '3524.00', '0.00', '634.32', '4158.32', 'tarjeta', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('15', NULL, 'VTA-000015', '5', '2', '2026-04-28 09:00:00', '734.00', '0.00', '132.12', '866.12', 'efectivo', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48'),
('16', NULL, 'VTA-000016', '1', '1', '2026-05-01 09:30:00', '1098.00', '0.00', '197.64', '1295.64', 'yape', 'completada', NULL, '2026-07-14 10:37:48', '2026-07-14 10:37:48');

SET FOREIGN_KEY_CHECKS = 1;
