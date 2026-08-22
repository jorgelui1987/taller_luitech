<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cupon;
use App\Models\Venta;
use App\Models\Reparacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuponTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Cupones',
            'subdominio' => 'tiendacupones',
            'email_contacto' => 'cupones@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Cupones',
            'email' => 'admin@cupones.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Cupon',
            'telefono' => '999999999',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_generar_codigo_cupon_tiene_formato_correcto(): void
    {
        $codigo = Cupon::generarCodigo();

        $this->assertStringStartsWith('CUP-', $codigo);
        $this->assertMatchesRegularExpression('/^CUP-[A-F0-9]{6}-\d{3}$/', $codigo);
    }

    public function test_cupon_activo_es_valido(): void
    {
        $cupon = Cupon::create([
            'codigo' => 'CUP-TEST-001',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'estado' => 'activo',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertTrue($cupon->esValido());
    }

    public function test_cupon_usado_no_es_valido(): void
    {
        $cupon = Cupon::create([
            'codigo' => 'CUP-TEST-002',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'estado' => 'usado',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertFalse($cupon->esValido());
    }

    public function test_cupon_expirado_no_es_valido(): void
    {
        $cupon = Cupon::create([
            'codigo' => 'CUP-TEST-003',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'estado' => 'activo',
            'fecha_expiracion' => now()->subDay(),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertFalse($cupon->esValido());
    }

    public function test_marcar_cupon_como_usado(): void
    {
        $venta = Venta::create([
            'numero_venta' => 'VTA-000001',
            'cliente_id' => $this->cliente->id,
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 1000,
            'descuento' => 0,
            'impuesto' => 190,
            'total' => 1190,
            'metodo_pago' => 'efectivo',
            'estado' => 'completada',
            'tenant_id' => $this->tenant->id,
        ]);

        $cupon = Cupon::create([
            'codigo' => 'CUP-TEST-004',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'estado' => 'activo',
            'tenant_id' => $this->tenant->id,
        ]);

        $cupon->marcarUsado($venta->id);

        $this->assertEquals('usado', $cupon->fresh()->estado);
        $this->assertNotNull($cupon->fresh()->fecha_uso);
        $this->assertEquals($venta->id, $cupon->fresh()->venta_id);
    }

    public function test_marcar_cupon_como_usado_en_reparacion(): void
    {
        $reparacion = Reparacion::create([
            'numero_orden' => 'RPT-000001',
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->user->id,
            'tipo_dispositivo' => 'celular',
            'falla_reportada' => 'Pantalla',
            'estado' => 'recibido',
            'prioridad' => 'media',
            'fecha_recepcion' => now(),
            'presupuesto' => 500,
            'abono' => 0,
            'total' => 500,
            'tenant_id' => $this->tenant->id,
        ]);

        $cupon = Cupon::create([
            'codigo' => 'CUP-TEST-005',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'estado' => 'activo',
            'tenant_id' => $this->tenant->id,
        ]);

        $cupon->marcarUsadoEnReparacion($reparacion->id);

        $this->assertEquals('usado', $cupon->fresh()->estado);
        $this->assertNotNull($cupon->fresh()->fecha_uso);
        $this->assertEquals($reparacion->id, $cupon->fresh()->reparacion_uso_id);
    }
}