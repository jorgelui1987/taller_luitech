<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Personalización de colores de marca por empresa:
 * - Defaults = tema Luitech
 * - El admin los guarda en Configuración (configuracion_extra del tenant)
 * - La web pública del taller los muestra (variables CSS)
 */
class ColoresMarcaTest extends TestCase
{
    use RefreshDatabase;

    private function crearEmpresa(string $slug = 'mi-tienda'): array
    {
        $tenant = Tenant::create([
            'empresa'        => 'Mi Taller',
            'subdominio'     => $slug,
            'slug_publico'   => $slug,
            'email_contacto' => $slug . '@test.com',
            'plan'           => 'basico',
            'estado'         => 'activo',
        ]);

        $admin = User::create([
            'name'      => 'Admin ' . $slug,
            'email'     => $slug . '-admin@test.com',
            'password'  => bcrypt('password123'),
            'rol'       => 'admin',
            'activo'    => true,
            'tenant_id' => $tenant->id,
        ]);

        return [$tenant, $admin];
    }

    public function test_colores_por_defecto_son_tema_luitech(): void
    {
        [$tenant] = $this->crearEmpresa();

        $this->assertSame('#0891b2', $tenant->colores()['primario']);
        $this->assertSame('#3b82f6', $tenant->colores()['secundario']);
    }

    public function test_hex_a_rgba_convierte_y_tolera_formatos(): void
    {
        $this->assertSame('rgba(255,102,0,0.18)', Tenant::hexARgba('#ff6600', 0.18));
        $this->assertSame('rgba(8,145,178,1.00)', Tenant::hexARgba('0891b2', 1.0));
        // Formato inválido → color por defecto (nunca explota)
        $this->assertSame('rgba(8,145,178,1.00)', Tenant::hexARgba('#invi', 1.0));
    }

    public function test_admin_guarda_colores_y_web_publica_los_muestra(): void
    {
        [$tenant, $admin] = $this->crearEmpresa();

        Configuracion::create([
            'nombre_tienda'         => 'Mi Taller',
            'pagina_publica_activa' => true,
            'tenant_id'             => $tenant->id,
        ]);

        $this->actingAs($admin);

        $this->post(route('configuracion.colores'), [
            'color_primario'   => '#ff6600',
            'color_secundario' => '#00aa88',
        ]);

        $tenant->refresh();
        $this->assertSame('#FF6600', $tenant->configuracion_extra['color_primario']);
        $this->assertSame('#00AA88', $tenant->configuracion_extra['color_secundario']);

        // La web pública del taller inyecta los colores como variables CSS
        $this->get(route('public.tienda', $tenant->slug_publico))
            ->assertOk()
            ->assertSee('#FF6600', false)
            ->assertSee('#00AA88', false);
    }

    public function test_solo_admin_puede_guardar_colores(): void
    {
        [$tenant, $admin] = $this->crearEmpresa();

        $tecnico = User::create([
            'name'      => 'Tecnico',
            'email'     => 'tecnico-colores@test.com',
            'password'  => bcrypt('password123'),
            'rol'       => 'tecnico',
            'activo'    => true,
            'tenant_id' => $tenant->id,
        ]);

        $this->actingAs($tecnico);

        $this->post(route('configuracion.colores'), [
            'color_primario'   => '#ff6600',
            'color_secundario' => '#00aa88',
        ])->assertStatus(403);

        $tenant->refresh();
        $this->assertNull($tenant->configuracion_extra['color_primario'] ?? null);
    }
}
