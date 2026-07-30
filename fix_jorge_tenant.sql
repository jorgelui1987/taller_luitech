-- Ver si hay tenants
SELECT id, name, slug, created_at FROM tenants LIMIT 5;

-- Ver el usuario jorge
SELECT id, name, email, tenant_id FROM users WHERE name LIKE '%jorge%' OR email LIKE '%jorge%';

-- Si hay al menos 1 tenant, asignarle el primero
-- UPDATE users SET tenant_id = (SELECT id FROM tenants ORDER BY id LIMIT 1) WHERE name LIKE '%jorge%' AND tenant_id IS NULL;