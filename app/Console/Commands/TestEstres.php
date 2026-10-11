<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Cliente;
use App\Models\Producto;

/**
 * Test de estrés con volumen: inserta N ventas + N reparaciones de prueba
 * (prefijo ESTRES-) y mide tiempos de las consultas críticas.
 *
 * USO (solo staging/desarrollo, NUNCA en producción real):
 *   php artisan test-estres --registros=1000
 *   php artisan test-estres --limpiar
 */
class TestEstres extends Command
{
    protected $signature = 'test-estres {--registros=500} {--limpiar}';
    protected $description = 'Inserta datos de prueba con volumen y mide rendimiento (solo staging)';

    public function handle(): int
    {
        if ($this->option('limpiar')) {
            return $this->limpiar();
        }

        if (app()->environment('production')) {
            $this->error('⛔ BLOQUEADO en production. Usa un staging con BD de prueba.');
            return 1;
        }

        $n = max(10, (int) $this->option('registros'));
        $this->info("=== TEST ESTRÉS: {$n} ventas + {$n} reparaciones ===");

        $cliente = Cliente::withoutGlobalScopes()->first();
        $producto = Producto::withoutGlobalScopes()->first();
        $tenantId = $cliente?->tenant_id;

        if (!$cliente || !$producto) {
            $this->error('Sin clientes/productos base. Corre los seeders primero.');
            return 1;
        }

        // ── Insertar ventas de prueba (bulk) ──
        $t0 = microtime(true);
        $lote = [];
        for ($i = 1; $i <= $n; $i++) {
            $total = rand(100, 5000);
            $lote[] = [
                'numero_venta' => 'ESTRES-V' . str_pad((string) $i . rand(100, 999), 9, '0', STR_PAD_LEFT),
                'cliente_id' => $cliente->id,
                'user_id' => 1,
                'tenant_id' => $tenantId,
                'fecha_venta' => now()->subDays(rand(0, 365)),
                'subtotal' => $total, 'descuento' => 0, 'impuesto' => $total * 0.19, 'total' => $total,
                'metodo_pago' => 'efectivo', 'estado' => 'completada',
                'created_at' => now(), 'updated_at' => now(),
            ];
            if (count($lote) >= 200) {
                DB::table('ventas')->insert($lote);
                $lote = [];
            }
        }
        if ($lote) {
            DB::table('ventas')->insert($lote);
        }
        $tVentas = round(microtime(true) - $t0, 2);
        $this->info("✓ {$n} ventas insertadas en {$tVentas}s");

        // ── Insertar reparaciones de prueba (bulk) ──
        $t0 = microtime(true);
        $lote = [];
        for ($i = 1; $i <= $n; $i++) {
            $lote[] = [
                'numero_orden' => 'ESTRES-R' . str_pad((string) $i . rand(100, 999), 9, '0', STR_PAD_LEFT),
                'cliente_id' => $cliente->id,
                'tenant_id' => $tenantId,
                'dispositivo' => 'Equipo estrés ' . $i,
                'falla_reportada' => 'Falla de prueba de volumen',
                'estado' => collect(['recibido', 'en_reparacion', 'listo', 'entregado'])->random(),
                'prioridad' => 'media',
                'fecha_recepcion' => now()->subDays(rand(0, 365)),
                'created_at' => now(), 'updated_at' => now(),
            ];
            if (count($lote) >= 200) {
                DB::table('reparaciones')->insert($lote);
                $lote = [];
            }
        }
        if ($lote) {
            DB::table('reparaciones')->insert($lote);
        }
        $tRep = round(microtime(true) - $t0, 2);
        $this->info("✓ {$n} reparaciones insertadas en {$tRep}s");

        // ── Medir consultas críticas ──
        $this->info('');
        $this->info('--- Tiempos de consultas críticas ---');
        $this->medir('Listado ventas paginado', fn() => Venta::with(['cliente'])->orderByDesc('fecha_venta')->paginate(15));
        $this->medir('Listado reparaciones paginado', fn() => Reparacion::with(['cliente'])->orderByDesc('fecha_recepcion')->paginate(15));
        $this->medir('Listado productos paginado', fn() => Producto::with(['categoria', 'marca'])->orderByDesc('created_at')->paginate(15));
        $this->medir('Suma inventario SQL', fn() => Producto::where('activo', true)->selectRaw('COALESCE(SUM(stock * precio_compra),0) as t')->value('t'));
        $this->medir('Contador stock bajo', fn() => Producto::whereColumn('stock', '<=', 'stock_minimo')->count());
        $this->medir('Contador reparaciones listo', fn() => Reparacion::where('estado', 'listo')->count());

        $this->info('');
        $this->warn("Para borrar los datos de prueba: php artisan test-estres --limpiar");

        return 0;
    }

    private function medir(string $nombre, callable $fn): void
    {
        $t0 = microtime(true);
        $fn();
        $ms = round((microtime(true) - $t0) * 1000);
        $estado = $ms < 1000 ? 'OK  ' : ($ms < 3000 ? 'LENTO' : 'MAL  ');
        $this->line("  [{$estado}] {$nombre}: {$ms} ms");
    }

    private function limpiar(): int
    {
        $v = DB::table('ventas')->where('numero_venta', 'like', 'ESTRES-%')->delete();
        $r = DB::table('reparaciones')->where('numero_orden', 'like', 'ESTRES-%')->delete();
        $this->info("✓ Limpieza: {$v} ventas + {$r} reparaciones de prueba eliminadas.");
        return 0;
    }
}
