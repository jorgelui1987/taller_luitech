<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pruebas del registro de efectivo recibido y vuelto en ventas.
 *
 * Escenario del usuario: se vende un chip de $1.000 y el cliente
 * paga con $5.000 → el sistema debe registrar vuelto $4.000.
 */
class VueltoTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Vuelto',
            'subdominio' => 'vuelto',
            'email_contacto' => 'vuelto@test.com',
            'telefono_contacto' => '999999999',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@vuelto.com',
            'password' => 'PasswordV123',
            'rol' => 'vendedor',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        // Configuración Chile: el precio YA incluye IVA → total = precio de venta
        Configuracion::create([
            'nombre_tienda' => 'Tienda CL',
            'pais' => 'CL',
            'igv' => 19,
            'moneda' => 'CLP',
            'simbolo_moneda' => '$',
            'tenant_id' => $this->tenant->id,
        ]);

        $categoria = Categoria::create([
            'nombre' => 'General',
            'tenant_id' => $this->tenant->id,
        ]);
        $marca = \App\Models\Marca::create([
            'nombre' => 'Marca Test',
            'tenant_id' => $this->tenant->id,
        ]);

        // El chip de $1.000 del ejemplo del usuario
        $this->producto = Producto::create([
            'nombre' => 'Chip',
            'codigo' => 'CHIP-001',
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'precio_venta' => 1000,
            'stock' => 10,
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);

        // Abrir caja (requisito para registrar ventas)
        DB::table('cierres_caja')->insert([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'monto_inicial' => 0,
            'fecha_apertura' => now(),
            'estado' => 'abierta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function venderEfectivo(?float $montoRecibido = null): Venta
    {
        $datos = [
            'metodo_pago' => 'efectivo',
            'productos' => [['id' => $this->producto->id, 'cantidad' => 1]],
        ];
        if ($montoRecibido !== null) {
            $datos['monto_recibido'] = $montoRecibido;
        }

        $response = $this->actingAs($this->user)->post('/ventas', $datos);
        $response->assertStatus(302);

        $venta = Venta::latest('id')->first();
        $this->assertNotNull($venta);

        return $venta;
    }

    public function test_chip_de_1000_pagado_con_5000_registra_vuelto_de_4000(): void
    {
        $venta = $this->venderEfectivo(5000);

        $this->assertSame(1000.0, (float) $venta->total);
        $this->assertSame(5000.0, (float) $venta->monto_recibido);
        $this->assertSame(4000.0, (float) $venta->vuelto, 'El vuelto de 5000 - 1000 debe ser 4000');
    }

    public function test_pago_exacto_no_registra_vuelto(): void
    {
        $venta = $this->venderEfectivo(1000);

        $this->assertSame(1000.0, (float) $venta->monto_recibido);
        $this->assertNull($venta->vuelto, 'Pago exacto no debe mostrar vuelto');
    }

    public function test_pago_menor_al_total_no_registra_vuelto(): void
    {
        $venta = $this->venderEfectivo(500);

        $this->assertSame(500.0, (float) $venta->monto_recibido);
        $this->assertNull($venta->vuelto, 'Pago menor al total no genera vuelto');
    }

    public function test_sin_monto_recibido_no_registra_nada(): void
    {
        $venta = $this->venderEfectivo(null);

        $this->assertNull($venta->monto_recibido);
        $this->assertNull($venta->vuelto);
    }

    public function test_pago_con_tarjeta_ignora_monto_recibido(): void
    {
        $response = $this->actingAs($this->user)->post('/ventas', [
            'metodo_pago' => 'tarjeta',
            'productos' => [['id' => $this->producto->id, 'cantidad' => 1]],
            'monto_recibido' => 5000, // intento de inyección en pago sin vuelto
        ]);

        $response->assertStatus(302);

        $venta = Venta::latest('id')->first();
        $this->assertNotNull($venta);
        $this->assertNull($venta->monto_recibido, 'Con tarjeta no debe registrar efectivo recibido');
        $this->assertNull($venta->vuelto);
    }
}