<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Caja',
            'subdominio' => 'tiendacaja',
            'email_contacto' => 'caja@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Caja',
            'email' => 'admin@caja.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_abrir_caja_crea_registro_abierta(): void
    {
        $caja = CierreCaja::create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'monto_inicial' => 50000,
            'fecha_apertura' => now(),
            'estado' => 'abierta',
        ]);

        $this->assertEquals('abierta', $caja->estado);
        $this->assertEquals(50000, (float) $caja->monto_inicial);
        $this->assertEquals($this->tenant->id, $caja->tenant_id);
    }

    public function test_detectar_caja_abierta(): void
    {
        CierreCaja::create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'monto_inicial' => 50000,
            'fecha_apertura' => now(),
            'estado' => 'abierta',
        ]);

        $this->assertTrue(CierreCaja::hayCajaAbierta());
        $this->assertNotNull(CierreCaja::cajaAbierta());
    }

    public function test_no_detectar_caja_abierta_si_no_hay(): void
    {
        $this->assertFalse(CierreCaja::hayCajaAbierta());
        $this->assertNull(CierreCaja::cajaAbierta());
    }

    public function test_cerrar_caja_cambia_estado(): void
    {
        $caja = CierreCaja::create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'monto_inicial' => 50000,
            'fecha_apertura' => now(),
            'estado' => 'abierta',
        ]);

        $caja->update([
            'estado' => 'cerrada',
            'fecha_cierre' => now(),
            'total_esperado' => 100000,
            'total_contado' => 100000,
            'diferencia' => 0,
        ]);

        $this->assertEquals('cerrada', $caja->fresh()->estado);
        $this->assertNotNull($caja->fresh()->fecha_cierre);
        $this->assertEquals(0, (float) $caja->fresh()->diferencia);
    }

    public function test_ventas_del_dia_suma_por_metodo_pago(): void
    {
        Venta::create([
            'numero_venta' => 'VTA-000001',
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

        Venta::create([
            'numero_venta' => 'VTA-000002',
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 2000,
            'descuento' => 0,
            'impuesto' => 380,
            'total' => 2380,
            'metodo_pago' => 'tarjeta',
            'estado' => 'completada',
            'tenant_id' => $this->tenant->id,
        ]);

        $ventas = CierreCaja::ventasDelDia();

        $this->assertEquals(1190, $ventas['efectivo']);
        $this->assertEquals(2380, $ventas['tarjeta']);
        $this->assertEquals(3570, $ventas['total_ingresos']);
        $this->assertEquals(2, $ventas['num_ventas']);
    }
}