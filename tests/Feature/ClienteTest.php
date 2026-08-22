<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Clientes',
            'subdominio' => 'tiendaclientes',
            'email_contacto' => 'clientes@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Clientes',
            'email' => 'admin@clientes.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_crear_cliente_con_datos_validos(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'telefono' => '987654321',
            'email' => 'juan@test.com',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals('Juan', $cliente->nombre);
        $this->assertEquals('Perez', $cliente->apellido);
        $this->assertEquals($this->tenant->id, $cliente->tenant_id);
    }

    public function test_nombre_completo_se_genera_correctamente(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Maria',
            'apellido' => 'Gonzalez',
            'telefono' => '123456789',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals('Maria Gonzalez', $cliente->nombre_completo);
    }

    public function test_total_compras_suma_solo_ventas_completadas(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Carlos',
            'apellido' => 'Lopez',
            'telefono' => '555555555',
            'tenant_id' => $this->tenant->id,
        ]);

        Venta::create([
            'numero_venta' => 'VTA-000001',
            'cliente_id' => $cliente->id,
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
            'cliente_id' => $cliente->id,
            'user_id' => $this->user->id,
            'fecha_venta' => now(),
            'subtotal' => 500,
            'descuento' => 0,
            'impuesto' => 95,
            'total' => 595,
            'metodo_pago' => 'tarjeta',
            'estado' => 'cancelada',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(1190, $cliente->totalCompras());
    }

    public function test_cliente_solo_visible_para_su_tenant(): void
    {
        $otroTenant = Tenant::create([
            'empresa' => 'Otra Tienda',
            'subdominio' => 'otraclientes',
            'email_contacto' => 'otra@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        Cliente::create([
            'nombre' => 'Cliente Propio',
            'apellido' => 'Test',
            'telefono' => '111111111',
            'tenant_id' => $this->tenant->id,
        ]);

        \Illuminate\Support\Facades\Auth::logout();

        Cliente::create([
            'nombre' => 'Cliente Otro',
            'apellido' => 'Test',
            'telefono' => '222222222',
            'tenant_id' => $otroTenant->id,
        ]);

        $this->actingAs($this->user);

        $clientes = Cliente::all();

        $this->assertCount(1, $clientes);
        $this->assertEquals('Cliente Propio', $clientes->first()->nombre);
    }
}