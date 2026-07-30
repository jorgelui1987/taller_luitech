-- ==========================================
-- CREAR SUPERADMIN EN HOSTINGER
-- Ejecutar esto en phpMyAdmin (SQL)
-- ==========================================
-- Primero verifica si ya existe:
-- SELECT * FROM users WHERE rol = 'superadmin';

-- Si no existe, ejecuta esto:
INSERT INTO `users` (`name`, `email`, `password`, `rol`, `activo`, `created_at`, `updated_at`) 
SELECT 'Super Admin', 'camila1987chile@gmail.com', '$2y$12$LJ3m4ys3Lk0HRMBMHlpOcO0Xq1xH', 'superadmin', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `rol` = 'superadmin');

-- Verificar
SELECT id, name, email, rol, activo FROM users WHERE rol = 'superadmin';