<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pruebas de rendimiento con volumen realista de datos.
 *
 * Siembra: 500 clientes, 300 productos, 1000 ventas (+1000 detalles)
 * y 300 reparaciones, y verifica que las páginas principales:
 * - Respondan dentro de tiempos aceptables
 * - Estén paginadas (no renderizan todas las filas)
 * - No sufran consultas N+1 (conteo de queries acotado)
 *
 * Umbrales generosos pensados para detectar colapsos catastróficos
 * (listados sin paginar, N+1 masivos), no micro-optimizaciones.
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENTES = 500;
    private const PRODUCTOS = 300;
    private const VENTAS = 1000;
    private const REPARACIONES = 300;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Rendimiento',
            'subdominio' => 'rendimiento',
            'email_contacto' => 'perf@test.com',
            'telefono_contacto' => '999999999',
            'plan' => 'empresarial',
            'estado' => 'activo',
            'max_usuarios' => 50,
            'max_productos' => 100000,
        ]);

        $this->user = User::create([
            'name' => 'Admin Perf',
            'email' => 'admin@perf.com',
            'password' => 'PasswordPerf1',
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->seedDatos();
    }

    // ------------------------------------------------------------------
    // Siembra de datos masiva (inserts por lotes, filtrados por esquema)
    // ------------------------------------------------------------------

    private function insertarMasivo(string $tabla, array $filas): void
    {
        $columnas = array_flip(Schema::getColumnListing($tabla));
        $ahora = now()->format('Y-m-d H:i:s');

        $limpias = [];
        foreach ($filas as $fila) {
            // Solo columnas que existen realmente en la tabla
            $fila = array_intersect_key($fila, $columnas);
            $fila['created_at'] = $ahora;
            $fila['updated_at'] = $ahora;
            $limpias[] = $fila;
        }

        foreach (array_chunk($limpias, 200) as $chunk) {
            DB::table($tabla)->insert($chunk);
        }
    }

    private function seedDatos(): void
    {
        // ── Clientes ──
        $clientes = [];
        for ($i = 1; $i <= self::CLIENTES; $i++) {
            $clientes[] = [
                'nombre' => 'Cliente' . $i,
                'apellido' => 'Perf',
                'telefono' => '9' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'tenant_id' => $this->tenant->id,
            ];
        }
        $this->insertarMasivo('clientes', $clientes);

        // ── Categoría y marca (requeridas por productos) ──
        $categoria = \App\Models\Categoria::create([
            'nombre' => 'Categoria Perf',
            'tenant_id' => $this->tenant->id,
        ]);
        $marca = \App\Models\Marca::create([
            'nombre' => 'Marca Perf',
            'tenant_id' => $this->tenant->id,
        ]);

        // ── Productos ──
        $productos = [];
        for ($i = 1; $i <= self::PRODUCTOS; $i++) {
            $productos[] = [
                'nombre' => 'Producto ' . $i,
                'codigo' => 'PERF-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'categoria_id' => $categoria->id,
                'marca_id' => $marca->id,
                'precio_compra' => 1000,
                'precio_venta' => 1500,
                'stock' => 50,
                'stock_minimo' => 5,
                'activo' => 1,
                'tenant_id' => $this->tenant->id,
            ];
        }
        $this->insertarMasivo('productos', $productos);

        // ── Ventas ──
        $clienteIds = DB::table('clientes')->where('tenant_id', $this->tenant->id)->pluck('id')->all();
        $productoIds = DB::table('productos')->where('tenant_id', $this->tenant->id)->pluck('id')->all();

        $ventas = [];
        for ($i = 1; $i <= self::VENTAS; $i++) {
            $subtotal = 1000 + $i;
            $ventas[] = [
                'numero_venta' => 'V-PERF-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'cliente_id' => $clienteIds[$i % count($clienteIds)],
                'user_id' => $this->user->id,
                'fecha_venta' => now()->subDays($i % 365)->format('Y-m-d H:i:s'),
                'subtotal' => $subtotal,
                'descuento' => 0,
                'impuesto' => round($subtotal * 0.19),
                'total' => round($subtotal * 1.19),
                'metodo_pago' => 'efectivo',
                'estado' => 'completada',
                'tenant_id' => $this->tenant->id,
            ];
        }
        $this->insertarMasivo('ventas', $ventas);

        // ── Detalles de venta (1 por venta) ──
        $ventaIds = DB::table('ventas')->where('tenant_id', $this->tenant->id)->pluck('id')->all();
        $detalles = [];
        foreach ($ventaIds as $n => $ventaId) {
            $detalles[] = [
                'venta_id' => $ventaId,
                'producto_id' => $productoIds[$n % count($productoIds)],
                'cantidad' => 1,
                'precio_unitario' => 1000,
                'subtotal' => 1000,
            ];
        }
        $this->insertarMasivo('detalle_ventas', $detalles);

        // ── Reparaciones ──
        $reparaciones = [];
        for ($i = 1; $i <= self::REPARACIONES; $i++) {
            $reparaciones[] = [
                'numero_orden' => 'R-PERF-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'cliente_id' => $clienteIds[$i % count($clienteIds)],
                'tecnico_id' => $this->user->id,
                'dispositivo' => 'Equipo ' . $i,
                'marca' => 'Samsung',
                'falla_reportada' => 'No enciende',
                'estado' => 'recibido',
                'fecha_recepcion' => now()->subDays($i % 180)->format('Y-m-d H:i:s'),
                'tenant_id' => $this->tenant->id,
            ];
        }
        $this->insertarMasivo('reparaciones', $reparaciones);
    }

    // ------------------------------------------------------------------
    // Helpers de medición
    // ------------------------------------------------------------------

    private function medirSegundos(callable $accion): float
    {
        $inicio = microtime(true);
        $accion();

        return microtime(true) - $inicio;
    }

    private function contarConsultas(callable $accion): int
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        $accion();
        $total = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $total;
    }

    // ------------------------------------------------------------------
    // Pruebas
    // ------------------------------------------------------------------

    public function test_listado_ventas_con_1000_registros_responde_rapido(): void
    {
        $this->actingAs($this->user);

        $segundos = $this->medirSegundos(fn () => $this->get('/ventas')->assertStatus(200));

        $this->assertLessThan(3.0, $segundos,
            "El listado de ventas tardó {$segundos}s con " . self::VENTAS . " registros. Posible listado sin paginar o N+1 masivo.");
    }

    public function test_listado_ventas_esta_paginado(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/ventas')->assertStatus(200);

        // Con 1000 ventas, un listado paginado renderiza pocas filas por página.
        // Si aparecen cientos de números de venta, no hay paginación → colapso.
        $filasRenderizadas = substr_count($response->getContent(), 'V-PERF-');
        $this->assertLessThan(60, $filasRenderizadas,
            "El listado de ventas renderizó {$filasRenderizadas} filas de " . self::VENTAS . ". Falta paginación.");
    }

    public function test_listado_ventas_sin_consultas_n_plus_1(): void
    {
        $this->actingAs($this->user);

        $consultas = $this->contarConsultas(fn () => $this->get('/ventas')->assertStatus(200));

        $this->assertLessThan(40, $consultas,
            "Se ejecutaron {$consultas} consultas para listar ventas. Posible N+1 (cliente/vendedor/detalles sin eager loading).");
    }

    public function test_listado_clientes_con_500_registros_responde_rapido(): void
    {
        $this->actingAs($this->user);

        $segundos = $this->medirSegundos(fn () => $this->get('/clientes')->assertStatus(200));

        $this->assertLessThan(3.0, $segundos,
            "El listado de clientes tardó {$segundos}s con " . self::CLIENTES . " registros.");
    }

    public function test_listado_clientes_sin_consultas_n_plus_1(): void
    {
        $this->actingAs($this->user);

        $consultas = $this->contarConsultas(fn () => $this->get('/clientes')->assertStatus(200));

        $this->assertLessThan(30, $consultas,
            "Se ejecutaron {$consultas} consultas para listar clientes. Posible N+1.");
    }

    public function test_busqueda_de_clientes_con_volumen_responde_rapido(): void
    {
        $this->actingAs($this->user);

        $segundos = $this->medirSegundos(
            fn () => $this->get('/clientes?buscar=Cliente250')->assertStatus(200)
        );

        $this->assertLessThan(2.0, $segundos, "La búsqueda de clientes tardó {$segundos}s.");
    }

    public function test_listado_productos_con_volumen_responde_rapido(): void
    {
        $this->actingAs($this->user);

        $segundos = $this->medirSegundos(fn () => $this->get('/productos')->assertStatus(200));

        $this->assertLessThan(3.0, $segundos,
            "El listado de productos tardó {$segundos}s con " . self::PRODUCTOS . " registros.");
    }

    public function test_listado_reparaciones_con_volumen_responde_rapido(): void
    {
        $this->actingAs($this->user);

        $segundos = $this->medirSegundos(fn () => $this->get('/reparaciones')->assertStatus(200));

        $this->assertLessThan(3.0, $segundos,
            "El listado de reparaciones tardó {$segundos}s con " . self::REPARACIONES . " registros.");
    }

    public function test_dashboard_con_volumen_de_datos_responde_rapido(): void
    {
        $this->actingAs($this->user);

        $segundos = $this->medirSegundos(fn () => $this->get('/dashboard')->assertStatus(200));

        $this->assertLessThan(5.0, $segundos,
            "El dashboard tardó {$segundos}s con volumen de datos. Revisar agregaciones (SUM/COUNT sin índice o sobre toda la tabla).");
    }

    public function test_detalle_de_venta_carga_rapido_y_sin_n_plus_1(): void
    {
        $this->actingAs($this->user);

        $ventaId = DB::table('ventas')->where('tenant_id', $this->tenant->id)->first()->id;

        $segundos = $this->medirSegundos(fn () => $this->get("/ventas/{$ventaId}")->assertStatus(200));
        $this->assertLessThan(2.0, $segundos, "El detalle de venta tardó {$segundos}s.");

        $consultas = $this->contarConsultas(fn () => $this->get("/ventas/{$ventaId}"));
        $this->assertLessThan(25, $consultas,
            "Se ejecutaron {$consultas} consultas para el detalle de venta. Posible N+1.");
    }

    public function test_perfil_de_cliente_con_historial_carga_rapido(): void
    {
        $this->actingAs($this->user);

        $clienteId = DB::table('clientes')->where('tenant_id', $this->tenant->id)->first()->id;

        $segundos = $this->medirSegundos(fn () => $this->get("/clientes/{$clienteId}")->assertStatus(200));

        $this->assertLessThan(2.0, $segundos,
            "El perfil del cliente (con historial de compras y reparaciones) tardó {$segundos}s.");
    }
}