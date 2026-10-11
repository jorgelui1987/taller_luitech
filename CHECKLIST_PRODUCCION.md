# ✅ Checklist pase a PRODUCCIÓN — Taller Luitech

Generado: 2026-10-11. Ejecutar en orden antes de cada deploy en Dokploy.

## 1. Limpieza de demos (YA HECHO en este pase)
- [x] Borrados `demo-*.html` (8 archivos) de la raíz
- [x] Borradas vistas backup `reparaciones/*-backup*`, `*-old*`, `*-respaldo*` (5 archivos)
- [x] Borrados scripts debug raíz: `check_portal.php`, `check_tablas.php`, `check_tenant.php`, `test_datos.php`
- [x] Borrada carpeta `backups/` local (4 archivos con datos)
- [x] `DatabaseSeeder`: usuarios demo SOLO en no-production + `DemoDataSeeder` solo si `!production`
- Verificar: `git status --short` no debe mostrar demos ni backups

## 2. Seguridad (YA APLICADO — verificar en Dokploy)
- [x] Throttle en rutas públicas: `/r/*`, `/buscar-orden`, `/estado`, `/api/cupon/validar` (60/30 por min), `/registro` (30/10), webhook MP (60/1)
- [x] `SecurityHeaders` global (HSTS, CSP, X-Frame DENY, nosniff)
- [x] Backups con `basename()` anti path-traversal; storage solo extensiones permitidas
- [x] Webhook Mercado Pago valida firma HMAC (401 si falla)
- [x] Recovery superadmin desactivado sin `SUPERADMIN_RECOVERY_TOKEN`
- Pendiente EN DOKploy (variables de entorno):
  - [ ] `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`
  - [ ] `APP_KEY` generada (no reutilizar la de dev)
  - [ ] `FORCE_HTTPS=true`, `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES=*`
  - [ ] `SUPERADMIN_PASSWORD` larga y única (cambiar el `password` por defecto)
  - [ ] `SUPERADMIN_RECOVERY_TOKEN` vacío salvo emergencia
  - [ ] Revisar que NO exista `.env` commiteado (`git log --all -- .env`)

## 3. Rendimiento / volumen (YA APLICADO)
- [x] `VentaController@create`: clientes limit 200, productos limit 500 (antes `->get()` total)
- [x] `ProductoController@codigosBarras`: paginado 50 + links en vista (antes `->get()` total)
- [x] `FinancieroController` + `ReporteController`: sumas de inventario en SQL (`SUM(stock*precio)`)
- [x] Sidebar: contadores cacheados 120s por tenant (`sidebar.stock_bajo.*`, `sidebar.rep_listo.*`)
- [x] Migración `2026_10_12_000001_add_performance_indexes.php` (ventas, reparaciones, productos, clientes, detalle_ventas)
- En Dokploy tras deploy: `php artisan migrate --force` y vigilar `storage/logs/laravel.log`

## 4. Test de estrés (cómo correrlo)
```powershell
# 1) Smoke test tras cada deploy:
.\smoke-test-produccion.ps1 -Url https://tu-dominio.com

# 2) Estrés con volumen (requiere vendor/ + .env staging con BD de prueba):
php artisan test-estres --registros=1000   # inserta 1000 ventas+reparaciones de prueba y mide tiempos
php artisan test-estres --limpiar          # borra los datos de prueba (prefijo ESTRES-)
```
Umbrales esperados en staging: dashboard < 2s, listados paginados < 1s, financiero < 3s.
Si algo supera: revisar `DB::enableQueryLog()` / `explain` y los índices del punto 3.

## 5. Deploy en Dokploy (orden)
1. Push a `main`, Dokploy rebuild
2. Verificar entrypoint: `.env` generado, migraciones OK, `optimize` OK
3. `php artisan migrate --force` (aplica los índices nuevos)
4. `.\smoke-test-produccion.ps1 -Url https://tu-dominio.com` → todo OK
5. Backup manual inicial desde `/backup` y descarga de resguardo
6. Cambiar `SUPERADMIN_PASSWORD` y guardar en gestor de claves
