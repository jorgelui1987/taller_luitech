<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La landing pública (dominio principal) vende la PLATAFORMA y siempre usa
 * el branding de LUITECH, sin importar qué tenants existan en la BD.
 */
class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_muestra_branding_de_la_plataforma(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('LUITECH', false);
        // No debe tomar el branding de un tenant (ej. Tecnicell)
        $response->assertDontSee('tecnicell', false);
    }

    public function test_pagina_de_planes_es_publica(): void
    {
        $this->withoutExceptionHandling();

        $this->get(route('planes'))->assertOk();
    }
}
