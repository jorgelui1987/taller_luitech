<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Categoria $categoria;
    private Marca $marca;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Productos',
            'subdominio' => 'tiendaproductos',
            'email_contacto' => 'productos@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Productos',
            'email' => 'admin@productos.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->categoria = Categoria::create([
            'nombre' => 'Celulares',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->marca = Marca::create([
            'nombre' => 'Samsung',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_crear_producto_con_datos_validos(): void
    {
        $producto = Producto::create([
            'nombre' => 'iPhone 13',
            'codigo' => 'IP13-128',
            'precio_venta' => 500000,
            'precio_compra' => 400000,
            'stock' => 10,
            'stock_minimo' => 2,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals('iPhone 13', $producto->nombre);
        $this->assertEquals(10, $producto->stock);
        $this->assertEquals($this->tenant->id, $producto->tenant_id);
    }

    public function test_detectar_stock_bajo(): void
    {
        $producto = Producto::create([
            'nombre' => 'Cargador',
            'codigo' => 'CARG-01',
            'precio_venta' => 10000,
            'precio_compra' => 5000,
            'stock' => 2,
            'stock_minimo' => 5,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertTrue($producto->tieneStockBajo());
    }

    public function test_no_detectar_stock_bajo_cuando_hay_suficiente(): void
    {
        $producto = Producto::create([
            'nombre' => 'Audifonos',
            'codigo' => 'AUD-01',
            'precio_venta' => 20000,
            'precio_compra' => 10000,
            'stock' => 20,
            'stock_minimo' => 5,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertFalse($producto->tieneStockBajo());
    }

    public function test_calcular_margen_de_ganancia(): void
    {
        $producto = Producto::create([
            'nombre' => 'Tablet',
            'codigo' => 'TAB-01',
            'precio_venta' => 300000,
            'precio_compra' => 200000,
            'stock' => 5,
            'stock_minimo' => 1,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(50, $producto->margen);
    }

    public function test_margen_cero_si_no_hay_precio_compra(): void
    {
        $producto = Producto::create([
            'nombre' => 'Funda',
            'codigo' => 'FUN-01',
            'precio_venta' => 5000,
            'precio_compra' => 0,
            'stock' => 50,
            'stock_minimo' => 10,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(0, $producto->margen);
    }

    public function test_producto_solo_visible_para_su_tenant(): void
    {
        $otroTenant = Tenant::create([
            'empresa' => 'Otra Tienda',
            'subdominio' => 'otraproductos',
            'email_contacto' => 'otra@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        Producto::create([
            'nombre' => 'Producto Propio',
            'codigo' => 'PROP-01',
            'precio_venta' => 100,
            'precio_compra' => 50,
            'stock' => 10,
            'stock_minimo' => 2,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'tenant_id' => $this->tenant->id,
        ]);

        \Illuminate\Support\Facades\Auth::logout();

        $categoriaOtro = Categoria::create([
            'nombre' => 'Categoria Otro',
            'activo' => true,
            'tenant_id' => $otroTenant->id,
        ]);

        $marcaOtro = Marca::create([
            'nombre' => 'Marca Otro',
            'activo' => true,
            'tenant_id' => $otroTenant->id,
        ]);

        Producto::create([
            'nombre' => 'Producto Otro',
            'codigo' => 'PROT-01',
            'precio_venta' => 200,
            'precio_compra' => 100,
            'stock' => 20,
            'stock_minimo' => 5,
            'categoria_id' => $categoriaOtro->id,
            'marca_id' => $marcaOtro->id,
            'tenant_id' => $otroTenant->id,
        ]);

        $this->actingAs($this->user);

        $productos = Producto::all();

        $this->assertCount(1, $productos);
        $this->assertEquals('Producto Propio', $productos->first()->nombre);
    }
}