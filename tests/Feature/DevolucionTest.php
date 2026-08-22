<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevolucionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Cliente $cliente;
    private Venta $venta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Devoluciones',
            'subdominio' => 'tiendadevoluciones',
            'email_contacto' => 'devoluciones@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Devoluciones',
            'email' => 'admin@devoluciones.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Devolucion',
            'telefono' => '999999999',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->venta = Venta::create([
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

        $this->actingAs($this->user);
    }

    public function test_generar_numero_devolucion_crea_formato_correcto(): void
    {
        $numero = Devolucion::generarNumero();

        $this->assertStringStartsWith('DEV-', $numero);
        $this->assertMatchesRegularExpression('/^DEV-\d{6}$/', $numero);
    }

    public function test_crear_devolucion_con_datos_validos(): void
    {
        $devolucion = Devolucion::create([
            'numero_devolucion' => Devolucion::generarNumero(),
            'venta_id' => $this->venta->id,
            'cliente_id' => $this->cliente->id,
            'user_id' => $this->user->id,
            'fecha_devolucion' => now(),
            'motivo' => 'Producto defectuoso',
            'tipo' => 'total',
            'estado' => 'completada',
            'subtotal' => 1000,
            'descuento' => 0,
            'impuesto' => 190,
            'total' => 1190,
            'tipo_reembolso' => 'efectivo',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals('Producto defectuoso', $devolucion->motivo);
        $this->assertEquals('completada', $devolucion->estado);
        $this->assertEquals(1190, (float) $devolucion->total);
        $this->assertEquals($this->tenant->id, $devolucion->tenant_id);
    }

    public function test_devolucion_solo_visible_para_su_tenant(): void
    {
        $otroTenant = Tenant::create([
            'empresa' => 'Otra Tienda',
            'subdominio' => 'otradevoluciones',
            'email_contacto' => 'otra@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        Devolucion::create([
            'numero_devolucion' => 'DEV-000001',
            'venta_id' => $this->venta->id,
            'cliente_id' => $this->cliente->id,
            'user_id' => $this->user->id,
            'fecha_devolucion' => now(),
            'motivo' => 'Devolucion propia',
            'tipo' => 'total',
            'estado' => 'completada',
            'subtotal' => 1000,
            'descuento' => 0,
            'impuesto' => 190,
            'total' => 1190,
            'tipo_reembolso' => 'efectivo',
            'tenant_id' => $this->tenant->id,
        ]);

        \Illuminate\Support\Facades\Auth::logout();

        Devolucion::create([
            'numero_devolucion' => 'DEV-000002',
            'venta_id' => $this->venta->id,
            'cliente_id' => $this->cliente->id,
            'user_id' => $this->user->id,
            'fecha_devolucion' => now(),
            'motivo' => 'Devolucion otra',
            'tipo' => 'total',
            'estado' => 'completada',
            'subtotal' => 2000,
            'descuento' => 0,
            'impuesto' => 380,
            'total' => 2380,
            'tipo_reembolso' => 'tarjeta',
            'tenant_id' => $otroTenant->id,
        ]);

        $this->actingAs($this->user);

        $devoluciones = Devolucion::all();

        $this->assertCount(1, $devoluciones);
        $this->assertEquals('DEV-000001', $devoluciones->first()->numero_devolucion);
    }
}