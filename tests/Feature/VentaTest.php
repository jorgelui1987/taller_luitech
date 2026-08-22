<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Test',
            'subdominio' => 'tiendatest',
            'email_contacto' => 'tienda@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_generar_numero_venta_crea_formato_correcto(): void
    {
        $numero = Venta::generarNumero();

        $this->assertStringStartsWith('VTA-', $numero);
        $this->assertMatchesRegularExpression('/^VTA-\d{6}$/', $numero);
    }

    public function test_generar_numero_venta_incrementa_secuencialmente(): void
    {
        Venta::create([
            'numero_venta' => 'VTA-000001',
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'descuento' => 0,
            'impuesto' => 19,
            'total' => 119,
            'metodo_pago' => 'efectivo',
            'estado' => 'completada',
            'tenant_id' => $this->tenant->id,
        ]);

        $numero = Venta::generarNumero();

        $this->assertEquals('VTA-000002', $numero);
    }

    public function test_crear_venta_asigna_tenant_id_automaticamente(): void
    {
        $venta = Venta::create([
            'numero_venta' => Venta::generarNumero(),
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'descuento' => 0,
            'impuesto' => 19,
            'total' => 119,
            'metodo_pago' => 'efectivo',
            'estado' => 'completada',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals($this->tenant->id, $venta->tenant_id);
        $this->assertEquals('efectivo', $venta->metodo_pago);
    }

    public function test_venta_solo_visible_para_su_tenant(): void
    {
        $otroTenant = Tenant::create([
            'empresa' => 'Otra Tienda',
            'subdominio' => 'otratienda',
            'email_contacto' => 'otra@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        Venta::create([
            'numero_venta' => 'VTA-000001',
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'descuento' => 0,
            'impuesto' => 19,
            'total' => 119,
            'metodo_pago' => 'efectivo',
            'estado' => 'completada',
            'tenant_id' => $this->tenant->id,
        ]);

        // Cerrar sesión para que el trait BelongsToTenant no sobrescriba el tenant_id
        \Illuminate\Support\Facades\Auth::logout();

        Venta::create([
            'numero_venta' => 'VTA-000002',
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 200,
            'descuento' => 0,
            'impuesto' => 38,
            'total' => 238,
            'metodo_pago' => 'tarjeta',
            'estado' => 'completada',
            'tenant_id' => $otroTenant->id,
        ]);

        // Volver a iniciar sesión
        $this->actingAs($this->user);

        $ventas = Venta::all();

        $this->assertCount(1, $ventas);
        $this->assertEquals('VTA-000001', $ventas->first()->numero_venta);
    }

    public function test_calcular_total_con_impuesto(): void
    {
        $venta = Venta::create([
            'numero_venta' => Venta::generarNumero(),
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 1000,
            'descuento' => 100,
            'impuesto' => 171,
            'total' => 1071,
            'metodo_pago' => 'transferencia',
            'estado' => 'completada',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(1000, (float) $venta->subtotal);
        $this->assertEquals(100, (float) $venta->descuento);
        $this->assertEquals(171, (float) $venta->impuesto);
        $this->assertEquals(1071, (float) $venta->total);
    }
}