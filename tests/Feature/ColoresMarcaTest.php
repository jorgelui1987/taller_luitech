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
            ->assertSee('#ff6600', false)
            ->assertSee('#00aa88', false);
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

    public function test_plantillas_whatsapp_se_guardan(): void
    {
        [$tenant, $admin] = $this->crearEmpresa();
        $this->actingAs($admin);

        $this->post(route('configuracion.whatsapp'), [
            'plantilla_recibido' => 'Hola {cliente}, recibimos tu {equipo}. Código: {codigo} — {tienda}',
            'plantilla_listo'    => '',
        ]);

        $tenant->refresh();
        $this->assertSame(
            'Hola {cliente}, recibimos tu {equipo}. Código: {codigo} — {tienda}',
            $tenant->configuracion_extra['plantilla_recibido']
        );
        // Vacío → null (usa el mensaje por defecto)
        $this->assertNull($tenant->configuracion_extra['plantilla_listo'] ?? null);
    }

    public function test_promos_se_guardan_desde_el_panel(): void
    {
        [$tenant, $admin] = $this->crearEmpresa();
        $this->actingAs($admin);

        $this->post(route('configuracion.promos'), [
            'promos_texto' => 'Cristales desde $15 | Con vidrio templado incluido' . "\n" . 'Baterías promo',
        ]);

        $tenant->refresh();
        $this->assertCount(2, $tenant->configuracion_extra['promos']);
        $this->assertSame('Cristales desde $15', $tenant->configuracion_extra['promos'][0]['titulo']);
        $this->assertSame('Con vidrio templado incluido', $tenant->configuracion_extra['promos'][0]['texto']);
        $this->assertSame('Baterías promo', $tenant->configuracion_extra['promos'][1]['titulo']);
    }

    public function test_panel_inyecta_colores_de_marca_en_el_html(): void
    {
        [$tenant, $admin] = $this->crearEmpresa();

        Configuracion::create([
            'nombre_tienda' => 'Mi Taller',
            'tenant_id'     => $tenant->id,
        ]);

        $this->actingAs($admin);

        $this->post(route('configuracion.colores'), [
            'color_primario'   => '#ff6600',
            'color_secundario' => '#00aa88',
        ]);

        // El panel (layout) debe inyectar los colores como variables en el <html>
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('#ff6600', false)
            ->assertSee('--accent1', false);
    }

    public function test_colores_muy_claros_se_oscurecen_automaticamente(): void
    {
        [$tenant] = $this->crearEmpresa('tienda-clara');
        $tenant->update(['configuracion_extra' => [
            'color_primario'   => '#ffff00',
            'color_secundario' => '#ccffcc',
        ]]);

        $c = $tenant->fresh()->colores();

        // El color PURO se mantiene tal cual (para fondos/botones)
        $this->assertSame('#ffff00', $c['primario_puro']);
        // Pero la versión para TEXTOS se oscurece automáticamente
        $this->assertNotSame('#ffff00', $c['primario']);
        $this->assertTrue(\App\Models\Tenant::luminancia($c['primario']) <= 0.45);
        // Sobre un fondo amarillo, el texto debe ser oscuro
        $this->assertSame('#0f172a', $c['texto_sobre_primario']);
    }

    public function test_texto_sobre_color_oscuro_es_blanco(): void
    {
        $this->assertSame('#ffffff', \App\Models\Tenant::textoSobre('#111827'));
        $this->assertSame('#0f172a', \App\Models\Tenant::textoSobre('#ffffff'));
        $this->assertSame('#ffffff', \App\Models\Tenant::textoSobre('#0891b2'));
    }

    public function test_vista_configuracion_renderiza_con_las_tarjetas_nuevas(): void
    {
        [$tenant, $admin] = $this->crearEmpresa();
        $this->actingAs($admin);

        $this->get(route('configuracion.index'))
            ->assertOk()
            ->assertSee('Colores de mi empresa')
            ->assertSee('Mensajes de WhatsApp')
            ->assertSee('Promociones para la pantalla TV')
            // Las tarjetas viven dentro de sus pestañas
            ->assertSee('tab-publicidad', false)
            // La pestaña Colores existe con su botón y su panel
            ->assertSee('tab-colores', false);
    }
}
