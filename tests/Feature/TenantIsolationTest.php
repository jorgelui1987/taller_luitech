<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant1;
    private Tenant $tenant2;
    private User $user1;
    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant1 = Tenant::create([
            'empresa' => 'Tienda Uno',
            'subdominio' => 'tienda1',
            'email_contacto' => 'tienda1@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->tenant2 = Tenant::create([
            'empresa' => 'Tienda Dos',
            'subdominio' => 'tienda2',
            'email_contacto' => 'tienda2@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user1 = User::create([
            'name' => 'Admin Uno',
            'email' => 'admin1@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant1->id,
        ]);

        $this->user2 = User::create([
            'name' => 'Admin Dos',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant2->id,
        ]);
    }

    public function test_usuario_solo_ve_clientes_de_su_tenant(): void
    {
        Cliente::create([
            'nombre' => 'Cliente Uno',
            'apellido' => 'Test',
            'telefono' => '111111111',
            'tenant_id' => $this->tenant1->id,
        ]);

        // Cerrar sesión para que el trait no sobrescriba el tenant_id
        \Illuminate\Support\Facades\Auth::logout();

        Cliente::create([
            'nombre' => 'Cliente Dos',
            'apellido' => 'Test',
            'telefono' => '222222222',
            'tenant_id' => $this->tenant2->id,
        ]);

        $this->actingAs($this->user1);

        $clientes = Cliente::all();

        $this->assertCount(1, $clientes);
        $this->assertEquals('Cliente Uno', $clientes->first()->nombre);
    }

    public function test_usuario_solo_ve_productos_de_su_tenant(): void
    {
        $categoria1 = Categoria::create([
            'nombre' => 'Categoria Uno',
            'activo' => true,
            'tenant_id' => $this->tenant1->id,
        ]);

        $marca1 = Marca::create([
            'nombre' => 'Marca Uno',
            'activo' => true,
            'tenant_id' => $this->tenant1->id,
        ]);

        Producto::create([
            'nombre' => 'Producto Uno',
            'codigo' => 'P001',
            'precio_venta' => 100,
            'precio_compra' => 50,
            'stock' => 10,
            'stock_minimo' => 2,
            'categoria_id' => $categoria1->id,
            'marca_id' => $marca1->id,
            'tenant_id' => $this->tenant1->id,
        ]);

        // Cerrar sesión para que el trait no sobrescriba el tenant_id
        \Illuminate\Support\Facades\Auth::logout();

        $categoria2 = Categoria::create([
            'nombre' => 'Categoria Dos',
            'activo' => true,
            'tenant_id' => $this->tenant2->id,
        ]);

        $marca2 = Marca::create([
            'nombre' => 'Marca Dos',
            'activo' => true,
            'tenant_id' => $this->tenant2->id,
        ]);

        Producto::create([
            'nombre' => 'Producto Dos',
            'codigo' => 'P002',
            'precio_venta' => 200,
            'precio_compra' => 100,
            'stock' => 20,
            'stock_minimo' => 5,
            'categoria_id' => $categoria2->id,
            'marca_id' => $marca2->id,
            'tenant_id' => $this->tenant2->id,
        ]);

        $this->actingAs($this->user1);

        $productos = Producto::all();

        $this->assertCount(1, $productos);
        $this->assertEquals('Producto Uno', $productos->first()->nombre);
    }

    public function test_usuario_sin_tenant_no_ve_nada(): void
    {
        Cliente::create([
            'nombre' => 'Cliente Uno',
            'apellido' => 'Test',
            'telefono' => '111111111',
            'tenant_id' => $this->tenant1->id,
        ]);

        $userSinTenant = User::create([
            'name' => 'Sin Tenant',
            'email' => 'sintenant@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => null,
        ]);

        $this->actingAs($userSinTenant);

        $clientes = Cliente::all();

        $this->assertCount(0, $clientes);
    }

    public function test_superadmin_ve_todos_los_datos(): void
    {
        Cliente::create([
            'nombre' => 'Cliente Uno',
            'apellido' => 'Test',
            'telefono' => '111111111',
            'tenant_id' => $this->tenant1->id,
        ]);

        // Cerrar sesión para que el trait no sobrescriba el tenant_id
        \Illuminate\Support\Facades\Auth::logout();

        Cliente::create([
            'nombre' => 'Cliente Dos',
            'apellido' => 'Test',
            'telefono' => '222222222',
            'tenant_id' => $this->tenant2->id,
        ]);

        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'superadmin',
            'activo' => true,
            'tenant_id' => null,
        ]);

        $this->actingAs($superadmin);

        $clientes = Cliente::all();

        $this->assertCount(2, $clientes);
    }

    public function test_crear_registro_asigna_tenant_id_automaticamente(): void
    {
        $this->actingAs($this->user1);

        $cliente = Cliente::create([
            'nombre' => 'Nuevo Cliente',
            'apellido' => 'Test',
            'telefono' => '333333333',
        ]);

        $this->assertEquals($this->tenant1->id, $cliente->tenant_id);
    }
}
