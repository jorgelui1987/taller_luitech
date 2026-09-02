<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica la generación/autogeneración del slug público de los tenants,
 * usado por la URL pública de la Sala de Espera: /pantalla/{slug}.
 */
class TenantsSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_generar_slug_unico_devuelve_el_base_cuando_esta_libre(): void
    {
        $this->assertSame('tecnicell', Tenant::generarSlugUnico('tecnicell'));
    }

    public function test_generar_slug_unico_agrega_sufijo_si_el_base_existe(): void
    {
        Tenant::create([
            'empresa'        => 'Tecnicell',
            'subdominio'     => 'tecnicell',
            'slug_publico'   => 'tecnicell',
            'email_contacto' => 'tecnicell@test.com',
            'estado'         => 'activo',
        ]);

        $this->assertSame('tecnicell-2', Tenant::generarSlugUnico('tecnicell'));
    }

    public function test_generar_slug_unico_normaliza_texto_con_espacios_y_tildes(): void
    {
        $this->assertSame('tecn-i-cell', Tenant::generarSlugUnico('Tecn í Cell'));
    }

    public function test_comando_asigna_slug_a_los_tenants_que_no_lo_tienen(): void
    {
        $tenant = Tenant::create([
            'empresa'        => 'Tecnicell',
            'subdominio'     => 'tecnicell',
            'slug_publico'   => null,
            'email_contacto' => 'tecnicell@test.com',
            'estado'         => 'activo',
        ]);

        $this->artisan('tenant:asignar-slugs')->assertSuccessful();

        $tenant->refresh();
        $this->assertSame('tecnicell', $tenant->slug_publico);
    }

    public function test_update_tenant_autogenera_slug_si_queda_vacio(): void
    {
        $superadmin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'super@slugs.test',
            'password'  => bcrypt('password123'),
            'rol'       => 'superadmin',
            'activo'    => true,
            'tenant_id' => null,
        ]);

        $tenant = Tenant::create([
            'empresa'        => 'Tecnicell',
            'subdominio'     => 'tecnicell',
            'slug_publico'   => null,
            'email_contacto' => 'tecnicell@test.com',
            'plan'           => 'basico',
            'estado'         => 'activo',
        ]);

        $this->actingAs($superadmin)
            ->put(route('superadmin.tenants.update', $tenant->id), [
                'empresa'        => 'Tecnicell',
                'subdominio'     => 'tecnicell',
                'slug_publico'   => '',
                'email_contacto' => 'tecnicell@test.com',
                'plan'           => 'basico',
                'estado'         => 'activo',
            ])
            ->assertRedirect();

        $tenant->refresh();
        $this->assertSame('tecnicell', $tenant->slug_publico);
    }
}
