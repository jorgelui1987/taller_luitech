<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReparacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Storage;

// ── PANEL SUPERADMIN (SIN tenant) ──────────────────────────────────────────
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login', [SuperAdminController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SuperAdminController::class, 'login'])->name('login.post');
    Route::post('/logout', [SuperAdminController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'check.tenant'])->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/crear', [SuperAdminController::class, 'createTenant'])->name('tenants.create');
        Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
        Route::get('/tenants/{id}/editar', [SuperAdminController::class, 'editTenant'])->name('tenants.edit');
        Route::put('/tenants/{id}', [SuperAdminController::class, 'updateTenant'])->name('tenants.update');
        Route::post('/tenants/{id}/toggle', [SuperAdminController::class, 'toggleTenant'])->name('tenants.toggle');
        Route::delete('/tenants/{id}', [SuperAdminController::class, 'destroyTenant'])->name('tenants.destroy');
        Route::get('/tenants/{id}/login-as', [SuperAdminController::class, 'loginAsTenant'])->name('tenants.login-as');
        Route::get('/tenants/{id}/usuarios', [SuperAdminController::class, 'tenantUsers'])->name('tenants.users');
        Route::post('/usuarios/{usuario}/cambiar-password', [SuperAdminController::class, 'changeUserPassword'])->name('usuarios.change-password');

        // Gestión de precios de planes
        Route::get('/planes-precios', [SuperAdminController::class, 'planPreciosIndex'])->name('planes-precios');
        Route::put('/planes-precios/{planPrecio}', [SuperAdminController::class, 'planPreciosUpdate'])->name('planes-precios.update');
    });
});

// ── RUTA PÚBLICA PARA QR DE REPARACIONES (sin autenticación) ──────────────
// El middleware CheckTenantStatus no afecta usuarios no autenticados (solo redirige si hay sesión y el tenant
// está suspendido/expirado). La ruta es accesible públicamente para que los clientes escaneen el QR.
Route::get('/r/{numero_orden}', [\App\Http\Controllers\PublicReparacionController::class, 'status'])
    ->name('reparaciones.public-status');

// ── RUTA PARA SERVIR ARCHIVOS DE STORAGE (sin symlink) ──────────────
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.serve');

// ── HEALTH CHECK PARA DOCKER ──────────────────────────────────────────
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ── RUTAS PÚBLICAS (Landing page para registrar nuevo tenant) ──────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

Route::get('/planes', function () {
    return view('landing');
})->name('planes')->withoutMiddleware([\App\Http\Middleware\CheckTenantStatus::class]);

Route::get('/registro', [SuperAdminController::class, 'showRegistroTenant'])->name('registro.tenant');
Route::post('/registro', [SuperAdminController::class, 'registrarTenant'])->name('registro.tenant.store');

// ── AUTENTICACIÓN DE TENANT ────────────────────────────────────────────────
Route::middleware(['tenant'])->group(function () {

    // Autenticación (sin check.tenant para evitar bucles)
    Route::middleware('guest')->group(function () {
        Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
        Route::get('/register',  [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'register'])->name('register.post')->middleware('throttle:3,1');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ── Rutas protegidas (requieren autenticación y verificación de tenant) ──
    Route::middleware(['auth', 'check.tenant'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Clientes
        Route::resource('clientes', ClienteController::class);

        // Productos
        Route::resource('productos', ProductoController::class);
        Route::post('/productos/marca-ajax', [\App\Http\Controllers\ProductoController::class, 'storeMarcaAjax'])->name('productos.marca-ajax');
        Route::post('/productos/categoria-ajax', [\App\Http\Controllers\ProductoController::class, 'storeCategoriaAjax'])->name('productos.categoria-ajax');

        // Proveedores (sin route model binding para evitar TenantScope)
        Route::get('/proveedores', [\App\Http\Controllers\ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/proveedores/crear', [\App\Http\Controllers\ProveedorController::class, 'create'])->name('proveedores.create');
        Route::post('/proveedores', [\App\Http\Controllers\ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/proveedores/{id}', [\App\Http\Controllers\ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('/proveedores/{id}/editar', [\App\Http\Controllers\ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('/proveedores/{id}', [\App\Http\Controllers\ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/proveedores/{id}', [\App\Http\Controllers\ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        Route::match(['post', 'patch'], '/proveedores/{id}/toggle', [\App\Http\Controllers\ProveedorController::class, 'toggle'])->name('proveedores.toggle');

        // Órdenes de Compra
        Route::middleware('check.admin')->group(function () {
            Route::get('/compras', [\App\Http\Controllers\OrdenCompraController::class, 'index'])->name('compras.index');
            Route::get('/compras/crear', [\App\Http\Controllers\OrdenCompraController::class, 'create'])->name('compras.create');
            Route::post('/compras', [\App\Http\Controllers\OrdenCompraController::class, 'store'])->name('compras.store');
            Route::get('/compras/{ordenCompra}', [\App\Http\Controllers\OrdenCompraController::class, 'show'])->name('compras.show');
            Route::get('/compras/{ordenCompra}/editar', [\App\Http\Controllers\OrdenCompraController::class, 'edit'])->name('compras.edit');
            Route::put('/compras/{ordenCompra}', [\App\Http\Controllers\OrdenCompraController::class, 'update'])->name('compras.update');
            Route::delete('/compras/{ordenCompra}', [\App\Http\Controllers\OrdenCompraController::class, 'destroy'])->name('compras.destroy');
            Route::post('/compras/{ordenCompra}/estado', [\App\Http\Controllers\OrdenCompraController::class, 'cambiarEstado'])->name('compras.estado');
        });

        // Movimientos de Stock, Ajustes y Alertas (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/stock/movimientos', [\App\Http\Controllers\StockMovimientoController::class, 'index'])->name('stock.movimientos');
            Route::get('/stock/ajuste', [\App\Http\Controllers\StockMovimientoController::class, 'ajusteForm'])->name('stock.ajuste');
            Route::post('/stock/ajuste', [\App\Http\Controllers\StockMovimientoController::class, 'ajusteStore'])->name('stock.ajuste.store');
            Route::get('/stock/producto/{producto}', [\App\Http\Controllers\StockMovimientoController::class, 'porProducto'])->name('stock.producto');
            Route::get('/stock/bajo', [\App\Http\Controllers\StockBajoController::class, 'index'])->name('stock.bajo');
            Route::get('/stock/bajo/{producto}/whatsapp', [\App\Http\Controllers\StockBajoController::class, 'notificacionWhatsApp'])->name('stock.bajo.whatsapp');
            Route::get('/api/stock/bajo/contador', [\App\Http\Controllers\StockBajoController::class, 'apiContador'])->name('api.stock.bajo');

            // Exportación e Importación
            Route::get('/exportar/productos/csv', [\App\Http\Controllers\ExportacionController::class, 'productosExcel'])->name('productos.exportar.csv');
            Route::get('/exportar/productos/html', [\App\Http\Controllers\ExportacionController::class, 'productosPDF'])->name('productos.exportar.html');
            Route::get('/exportar/productos/plantilla', [\App\Http\Controllers\ExportacionController::class, 'productosPlantillaImportacion'])->name('productos.plantilla');
            Route::get('/importar/productos', [\App\Http\Controllers\ImportacionController::class, 'importarForm'])->name('productos.importar');
            Route::post('/importar/productos', [\App\Http\Controllers\ImportacionController::class, 'importarStore'])->name('productos.importar.store');
        });

        // Ventas (solo admin y vendedor)
        Route::middleware('check.ventas')->group(function () {
            Route::resource('ventas', VentaController::class)->except(['edit', 'update', 'destroy']);
            Route::patch('/ventas/{venta}/cancelar', [VentaController::class, 'cancelar'])->name('ventas.cancelar');
            Route::get('/ventas/{venta}/ticket', [VentaController::class, 'printTicket'])->name('ventas.ticket');
        });

        // Reparaciones (solo admin y técnico)
        Route::middleware('check.reparaciones')->group(function () {
            Route::resource('reparaciones', ReparacionController::class)->parameters(['reparaciones' => 'reparacion']);
            Route::get('/reparaciones/{reparacion}/ticket', [ReparacionController::class, 'printTicket'])->name('reparaciones.ticket');

            // Firmas y fotos (AJAX)
            Route::post('/reparaciones/{reparacion}/firma', [ReparacionController::class, 'subirFirma'])->name('reparaciones.firma');
            Route::post('/reparaciones/{reparacion}/fotos', [ReparacionController::class, 'subirFoto'])->name('reparaciones.fotos.subir');
            Route::delete('/reparaciones/fotos/{foto}', [ReparacionController::class, 'eliminarFoto'])->name('reparaciones.fotos.eliminar');
        });

        // Reportes (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        });

        // Estado Financiero (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/financiero', [\App\Http\Controllers\FinancieroController::class, 'index'])->name('financiero.index');
            Route::get('/financiero/estado-resultados', [\App\Http\Controllers\FinancieroController::class, 'estadoResultados'])->name('financiero.estado-resultados');
            Route::get('/financiero/balance-general', [\App\Http\Controllers\FinancieroController::class, 'balanceGeneral'])->name('financiero.balance-general');
            Route::get('/financiero/flujo-caja', [\App\Http\Controllers\FinancieroController::class, 'flujoCaja'])->name('financiero.flujo-caja');
            Route::get('/financiero/indicadores', [\App\Http\Controllers\FinancieroController::class, 'indicadores'])->name('financiero.indicadores');
        });

        // Configuración (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
            Route::post('/configuracion/empresa', [ConfiguracionController::class, 'updateEmpresa'])->name('configuracion.updateEmpresa');
            Route::post('/configuracion/usuarios', [ConfiguracionController::class, 'storeUsuario'])->name('configuracion.storeUsuario');
            Route::patch('/configuracion/usuarios/{usuario}/toggle', [ConfiguracionController::class, 'toggleUsuario'])->name('configuracion.toggleUsuario');
            Route::put('/configuracion/usuarios/{usuario}', [ConfiguracionController::class, 'updateUsuario'])->name('configuracion.updateUsuario');
            Route::delete('/configuracion/usuarios/{usuario}', [ConfiguracionController::class, 'destroyUsuario'])->name('configuracion.destroyUsuario');
        });

        // Backup & Restauración (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/backup',                       [BackupController::class, 'index'])->name('backup.index');
            Route::post('/backup/crear',                [BackupController::class, 'crear'])->name('backup.crear');
            Route::get('/backup/descargar/{nombre}',    [BackupController::class, 'descargar'])->name('backup.descargar');
            Route::delete('/backup/eliminar/{nombre}',  [BackupController::class, 'eliminar'])->name('backup.eliminar');
            Route::post('/backup/restaurar',            [BackupController::class, 'restaurar'])->name('backup.restaurar');
            Route::post('/backup/resetear',             [BackupController::class, 'resetear'])->name('backup.resetear');
        });

        // Comisiones de técnicos (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/comisiones', [\App\Http\Controllers\ComisionController::class, 'index'])->name('comisiones.index');
            Route::post('/comisiones/{reparacion}/pagar', [\App\Http\Controllers\ComisionController::class, 'pagar'])->name('comisiones.pagar');
            Route::post('/comisiones/tecnico/{tecnico}/pagar-todo', [\App\Http\Controllers\ComisionController::class, 'pagarTodo'])->name('comisiones.pagar-todo');
        });

        // Gastos Fijos (solo admin)
        Route::middleware('check.admin')->group(function () {
            Route::get('/gastos', [\App\Http\Controllers\GastoFijoController::class, 'index'])->name('gastos.index');
            Route::post('/gastos', [\App\Http\Controllers\GastoFijoController::class, 'store'])->name('gastos.store');
            Route::put('/gastos/{gasto}', [\App\Http\Controllers\GastoFijoController::class, 'update'])->name('gastos.update');
            Route::patch('/gastos/{gasto}/toggle', [\App\Http\Controllers\GastoFijoController::class, 'toggle'])->name('gastos.toggle');
            Route::delete('/gastos/{gasto}', [\App\Http\Controllers\GastoFijoController::class, 'destroy'])->name('gastos.destroy');
        });

        // API interna para búsqueda de productos (para el formulario de ventas)
        Route::get('/api/productos/buscar', function () {
            $productos = \App\Models\Producto::with(['marca'])
                ->where('activo', true)
                ->where('stock', '>', 0)
                ->when(request('q'), fn($q, $buscar) =>
                    $q->where('nombre', 'like', "%$buscar%")
                      ->orWhere('codigo', 'like', "%$buscar%")
                )
                ->limit(10)
                ->get(['id', 'nombre', 'codigo', 'precio_venta', 'stock', 'marca_id']);

            return response()->json($productos);
        })->name('api.productos.buscar');

        // API interna para datos del dashboard (AJAX)
        Route::get('/api/dashboard/ventas-semana', function () {
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            $fechaExpr = $driver === 'pgsql'
                ? "TO_CHAR(fecha_venta, 'YYYY-MM-DD')"
                : 'DATE(fecha_venta)';

            $datos = \App\Models\Venta::select(
                    \Illuminate\Support\Facades\DB::raw("$fechaExpr as fecha"),
                    \Illuminate\Support\Facades\DB::raw('SUM(total) as total')
                )
                ->where('estado', 'completada')
                ->where('fecha_venta', '>=', \Carbon\Carbon::now()->subDays(6)->startOfDay())
                ->groupBy(\Illuminate\Support\Facades\DB::raw($fechaExpr))
                ->orderBy('fecha')
                ->get();

            return response()->json($datos);
        })->name('api.dashboard.ventas');
    });
});