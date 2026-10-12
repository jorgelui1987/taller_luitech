<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;

class TestLimites extends Command
{
    protected $signature = 'test-limites {--productos=5000} {--ventas=2000} {--reparaciones=2000} {--solo-medir} {--limpiar}';
    protected $description = 'Test de limites con volumen masivo (solo staging)';

    public function handle(): int
    {
        if ($this->option('limpiar')) {
            $p = DB::table('productos')->where('codigo', 'like', 'LIM-%')->delete();
            $v = DB::table('ventas')->where('numero_venta', 'like', 'LIM-%')->delete();
            $r = DB::table('reparaciones')->where('numero_orden', 'like', 'LIM-%')->delete();
            $this->info("Limpieza: {$p} prod + {$v} ventas + {$r} rep eliminados.");
            return 0;
        }
        if (app()->environment('production')) {
            $this->error('BLOQUEADO en production.');
            return 1;
        }
        if (!$this->option('solo-medir')) {
            $this->reventarProductos((int) $this->option('productos'));
            $this->reventarVentas((int) $this->option('ventas'));
            $this->reventarReparaciones((int) $this->option('reparaciones'));
        }
        $this->info('=== VOLUMEN ===');
        $this->line('  Productos: ' . number_format(Producto::withoutGlobalScopes()->count()));
        $this->line('  Ventas: ' . number_format(Venta::count()));
        $this->line('  Reparaciones: ' . number_format(Reparacion::count()));
        $this->info('=== MEDICION ===');
        $mal = 0;
        $mal += $this->medir('1.Ventas pag(15)', fn() => Venta::with(['cliente'])->orderByDesc('fecha_venta')->paginate(15), 1000);
        $mal += $this->medir('2.Rep pag(15)', fn() => Reparacion::with(['cliente'])->orderByDesc('fecha_recepcion')->paginate(15), 1000);
        $mal += $this->medir('3.Prod pag(15)', fn() => Producto::with(['categoria', 'marca'])->orderByDesc('created_at')->paginate(15), 1000);
        $mal += $this->medir('4.CodBarras pag(50)', fn() => Producto::with(['categoria', 'marca'])->where('activo', true)->orderBy('nombre')->paginate(50), 1000);
        $mal += $this->medir('5.Buscador prod', fn() => Producto::where('activo', true)->where('stock', '>', 0)->where('nombre', 'like', '%acc%')->limit(25)->get(['id', 'nombre']), 500);
        $mal += $this->medir('6.Buscador cli', fn() => Cliente::where('activo', true)->where('nombre', 'like', '%mar%')->limit(15)->get(['id', 'nombre']), 500);
        $mal += $this->medir('7.Suma inventario', fn() => Producto::where('activo', true)->selectRaw('COALESCE(SUM(stock * precio_compra),0) as t')->value('t'), 500);
        $mal += $this->medir('8.Stock bajo', fn() => Producto::whereColumn('stock', '<=', 'stock_minimo')->count(), 500);
        $mal += $this->medir('9.Rep listo', fn() => Reparacion::where('estado', 'listo')->count(), 500);
        $mal += $this->medir('10.Financiero 12m', function () {
            for ($i = 11; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                Venta::whereBetween('fecha_venta', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->where('estado', 'completada')->sum('total');
                Reparacion::whereBetween('fecha_recepcion', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->where('estado', 'entregado')->sum('total');
            }
        }, 3000);
        $this->info('');
        if ($mal > 0) {
            $this->error("COLAPSA: {$mal} fuera de umbral.");
        } else {
            $this->info('AGUANTA: todo en umbral.');
        }
        $this->line('  Pico memoria: ' . round(memory_get_peak_usage(true) / 1048576, 1) . ' MB');
        return $mal > 0 ? 2 : 0;
    }

    private function medir(string $n, callable $fn, int $u): int
    {
        $t0 = microtime(true);
        try {
            $fn();
            $ms = (int) round((microtime(true) - $t0) * 1000);
            $e = $ms <= $u ? 'OK' : ($ms <= $u * 2 ? 'LENTO' : 'COLAPSA');
        } catch (\Throwable $ex) {
            $ms = -1;
            $e = 'COLAPSA';
            $n .= ' [ERR:' . substr($ex->getMessage(), 0, 60) . ']';
        }
        if ($e === 'OK') {
            $this->info("  [OK] {$n}: {$ms}ms (u{$u})");
            return 0;
        }
        if ($e === 'LENTO') {
            $this->warn("  [LENTO] {$n}: {$ms}ms (u{$u})");
            return 0;
        }
        $this->error("  [COLAPSA] {$n}: " . ($ms >= 0 ? "{$ms}ms" : 'EXC'));
        return 1;
    }
    private function reventarProductos(int $n): void
    {
        $ya = Producto::withoutGlobalScopes()->where('codigo', 'like', 'LIM-%')->count();
        if ($ya >= $n) {
            $this->line("  Prod limite OK ({$ya}). Skip.");
            return;
        }
        $faltan = $n - $ya;
        $this->info("Insertando {$faltan} productos...");
        $cat = Categoria::withoutGlobalScopes()->first()?->id;
        $mar = Marca::withoutGlobalScopes()->first()?->id;
        $tenant = Cliente::withoutGlobalScopes()->first()?->tenant_id;
        $nombres = ['funda', 'cargador', 'cable', 'audifono', 'mica', 'case'];
        $lote = [];
        $hechos = 0;
        for ($i = $ya + 1; $i <= $n; $i++) {
            $lote[] = ['codigo' => 'LIM-' . str_pad((string) $i, 7, '0', STR_PAD_LEFT), 'nombre' => 'Accesorio ' . $nombres[$i % 6] . ' ' . $i, 'categoria_id' => $cat, 'marca_id' => $mar, 'tenant_id' => $tenant, 'tipo' => 'accesorio', 'condicion' => 'nuevo', 'activo' => true, 'precio_compra' => rand(5, 200), 'precio_venta' => rand(10, 500), 'stock' => rand(0, 100), 'stock_minimo' => 5, 'created_at' => now(), 'updated_at' => now()];
            if (count($lote) >= 500) {
                DB::table('productos')->insert($lote);
                $lote = [];
                $hechos += 500;
                $this->line("    ...{$hechos}/{$faltan}");
            }
        }
        if ($lote) {
            DB::table('productos')->insert($lote);
        }
        $this->info('  Productos listos.');
    }

    private function reventarVentas(int $n): void
    {
        $ya = DB::table('ventas')->where('numero_venta', 'like', 'LIM-%')->count();
        if ($ya >= $n) {
            $this->line("  Ventas limite OK ({$ya}). Skip.");
            return;
        }
        $faltan = $n - $ya;
        $this->info("Insertando {$faltan} ventas...");
        $cli = Cliente::withoutGlobalScopes()->first();
        $metodos = ['efectivo', 'tarjeta', 'transferencia'];
        $lote = [];
        $hechos = 0;
        for ($i = $ya + 1; $i <= $n; $i++) {
            $total = rand(50, 5000);
            $lote[] = ['numero_venta' => 'LIM-V' . str_pad((string) $i, 7, '0', STR_PAD_LEFT), 'cliente_id' => $cli->id, 'user_id' => 1, 'tenant_id' => $cli->tenant_id, 'fecha_venta' => now()->subDays(rand(0, 365)), 'subtotal' => $total, 'descuento' => 0, 'impuesto' => round($total * 0.19, 2), 'total' => $total, 'metodo_pago' => $metodos[$i % 3], 'estado' => 'completada', 'created_at' => now(), 'updated_at' => now()];
            if (count($lote) >= 500) {
                DB::table('ventas')->insert($lote);
                $lote = [];
                $hechos += 500;
                $this->line("    ...{$hechos}/{$faltan}");
            }
        }
        if ($lote) {
            DB::table('ventas')->insert($lote);
        }
        $this->info('  Ventas listas.');
    }

    private function reventarReparaciones(int $n): void
    {
        $ya = DB::table('reparaciones')->where('numero_orden', 'like', 'LIM-%')->count();
        if ($ya >= $n) {
            $this->line("  Rep limite OK ({$ya}). Skip.");
            return;
        }
        $faltan = $n - $ya;
        $this->info("Insertando {$faltan} reparaciones...");
        $cli = Cliente::withoutGlobalScopes()->first();
        $estados = ['recibido', 'en_diagnostico', 'en_reparacion', 'listo', 'entregado'];
        $lote = [];
        $hechos = 0;
        for ($i = $ya + 1; $i <= $n; $i++) {
            $lote[] = ['numero_orden' => 'LIM-R' . str_pad((string) $i, 7, '0', STR_PAD_LEFT), 'cliente_id' => $cli->id, 'tenant_id' => $cli->tenant_id, 'dispositivo' => 'Equipo limite ' . $i, 'falla_reportada' => 'Falla prueba limites', 'estado' => $estados[$i % 5], 'prioridad' => 'media', 'fecha_recepcion' => now()->subDays(rand(0, 365)), 'created_at' => now(), 'updated_at' => now()];
            if (count($lote) >= 500) {
                DB::table('reparaciones')->insert($lote);
                $lote = [];
                $hechos += 500;
                $this->line("    ...{$hechos}/{$faltan}");
            }
        }
        if ($lote) {
            DB::table('reparaciones')->insert($lote);
        }
        $this->info('  Reparaciones listas.');
    }
}
