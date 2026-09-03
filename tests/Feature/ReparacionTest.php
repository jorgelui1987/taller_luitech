<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Reparacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReparacionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private User $tecnico;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Taller Test',
            'subdominio' => 'tallertest',
            'email_contacto' => 'taller@test.com',
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

        $this->tecnico = User::create([
            'name' => 'Tecnico Test',
            'email' => 'tecnico@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'tecnico',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
            'comision_porcentaje' => 20,
        ]);

        $this->cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Test',
            'telefono' => '999999999',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_generar_numero_orden_crea_formato_correcto(): void
    {
        $numero = Reparacion::generarNumero();

        $this->assertStringStartsWith('RPT-', $numero);
        // Nuevo formato con sufijo anti-adivinanza: RPT-NNNNNN-XXXX
        $this->assertMatchesRegularExpression('/^RPT-\d{6}-[A-Z0-9]{4}$/', $numero);
        // El sufijo no usa caracteres ambiguos (0, O, 1, I, L)
        $this->assertDoesNotMatchRegularExpression('/[0O1IL]/', substr($numero, -4));
    }

    public function test_generar_numero_orden_es_unico(): void
    {
        $numeros = [];
        for ($i = 0; $i < 20; $i++) {
            $numeros[] = Reparacion::generarNumero();
        }

        $this->assertCount(20, array_unique($numeros));
    }

    public function test_calcular_total_resta_abono(): void
    {
        $reparacion = new Reparacion([
            'presupuesto' => 1000,
            'abono' => 200,
            'costo_final' => null,
        ]);

        $this->assertEquals(800, $reparacion->calcularTotal());
    }

    public function test_calcular_total_usa_costo_final_si_existe(): void
    {
        $reparacion = new Reparacion([
            'presupuesto' => 1000,
            'abono' => 100,
            'costo_final' => 1500,
        ]);

        $this->assertEquals(1400, $reparacion->calcularTotal());
    }

    public function test_base_comision_resta_costo_repuestos(): void
    {
        $reparacion = new Reparacion([
            'presupuesto' => 1000,
            'costo_final' => 1200,
            'costo_repuesto' => 200,
        ]);

        $this->assertEquals(1000, $reparacion->baseComision());
    }

    public function test_monto_comision_calcula_porcentaje_del_tecnico(): void
    {
        $reparacion = new Reparacion([
            'presupuesto' => 1000,
            'costo_final' => 1000,
            'costo_repuesto' => 0,
            'comision_porcentaje' => 20,
        ]);

        $this->assertEquals(200, $reparacion->montoComision());
    }

    public function test_monto_comision_usa_porcentaje_del_tecnico_si_no_tiene(): void
    {
        $reparacion = new Reparacion([
            'presupuesto' => 1000,
            'costo_final' => 1000,
            'costo_repuesto' => 0,
            'comision_porcentaje' => null,
        ]);
        $reparacion->tecnico = $this->tecnico;

        $this->assertEquals(200, $reparacion->montoComision());
    }

    public function test_crear_reparacion_asigna_tenant_id_automaticamente(): void
    {
        $reparacion = Reparacion::create([
            'numero_orden' => Reparacion::generarNumero(),
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_dispositivo' => 'celular',
            'falla_reportada' => 'Pantalla rota',
            'estado' => 'recibido',
            'prioridad' => 'media',
            'fecha_recepcion' => now(),
            'presupuesto' => 500,
            'abono' => 0,
            'total' => 500,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals($this->tenant->id, $reparacion->tenant_id);
        $this->assertEquals('recibido', $reparacion->estado);
    }

    public function test_reparacion_solo_visible_para_su_tenant(): void
    {
        $otroTenant = Tenant::create([
            'empresa' => 'Otro Taller',
            'subdominio' => 'otrotaller',
            'email_contacto' => 'otro@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        Reparacion::create([
            'numero_orden' => 'RPT-000001',
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_dispositivo' => 'celular',
            'falla_reportada' => 'Bateria',
            'estado' => 'recibido',
            'prioridad' => 'media',
            'fecha_recepcion' => now(),
            'presupuesto' => 500,
            'abono' => 0,
            'total' => 500,
            'tenant_id' => $this->tenant->id,
        ]);

        // Cerrar sesión para que el trait BelongsToTenant no sobrescriba el tenant_id
        \Illuminate\Support\Facades\Auth::logout();

        Reparacion::create([
            'numero_orden' => 'RPT-000002',
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_dispositivo' => 'celular',
            'falla_reportada' => 'Pantalla',
            'estado' => 'recibido',
            'prioridad' => 'alta',
            'fecha_recepcion' => now(),
            'presupuesto' => 800,
            'abono' => 0,
            'total' => 800,
            'tenant_id' => $otroTenant->id,
        ]);

        // Volver a iniciar sesión
        $this->actingAs($this->user);

        $reparaciones = Reparacion::all();

        $this->assertCount(1, $reparaciones);
        $this->assertEquals('RPT-000001', $reparaciones->first()->numero_orden);
    }
}