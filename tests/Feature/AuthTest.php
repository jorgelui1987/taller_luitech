<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa' => 'Tienda Auth',
            'subdominio' => 'tiendaauth',
            'email_contacto' => 'auth@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->user = User::create([
            'name' => 'Admin Auth',
            'email' => 'admin@auth.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_login_con_credenciales_correctas(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@auth.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticated();
    }

    public function test_login_con_credenciales_incorrectas(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@auth.com',
            'password' => 'password-incorrecta',
        ]);

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_login_con_email_inexistente(): void
    {
        $response = $this->post('/login', [
            'email' => 'noexiste@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $userInactivo = User::create([
            'name' => 'Inactivo',
            'email' => 'inactivo@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'admin',
            'activo' => false,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactivo@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_usuario_puede_cerrar_sesion(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_acceso_a_ruta_protegida_sin_autenticacion(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}