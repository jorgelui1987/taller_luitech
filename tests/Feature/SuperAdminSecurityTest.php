<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests de seguridad del sistema de recuperación del SuperAdmin
 * antes de pasar a producción.
 *
 * Cubre:
 * - La ruta de emergencia /recuperar-superadmin/{token} está DESACTIVADA por defecto (404).
 * - Tokens incorrectos son rechazados.
 * - El reset valida la contraseña (mínimo 8 caracteres + confirmación).
 * - El reset limpia el 2FA y asigna rol/estado correctos.
 * - Rate limiting en GET (10/min) y POST (5/min).
 * - La página no expone hashes de contraseñas.
 * - El panel /superadmin/dashboard está protegido por auth + CheckSuperAdmin.
 * - El comando artisan superadmin:reset funciona y valida la contraseña.
 */
class SuperAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-secreto-de-prueba-2026';
    private ?string $tokenOriginal = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Guardar estado previo y garantizar que el token esté DESACTIVADO
        $actual = getenv('SUPERADMIN_RECOVERY_TOKEN');
        $this->tokenOriginal = $actual === false ? null : $actual;
        putenv('SUPERADMIN_RECOVERY_TOKEN');
    }

    protected function tearDown(): void
    {
        // Restaurar estado original de la variable de entorno
        if ($this->tokenOriginal === null) {
            putenv('SUPERADMIN_RECOVERY_TOKEN');
        } else {
            putenv('SUPERADMIN_RECOVERY_TOKEN=' . $this->tokenOriginal);
        }

        parent::tearDown();
    }

    private function activarToken(): void
    {
        putenv('SUPERADMIN_RECOVERY_TOKEN=' . self::TOKEN);
    }

    // ------------------------------------------------------------------
    // 1. La ruta de recuperación debe estar desactivada por defecto
    // ------------------------------------------------------------------

    public function test_ruta_recuperacion_desactivada_por_defecto_devuelve_404(): void
    {
        $response = $this->get('/recuperar-superadmin/cualquier-token');

        $response->assertStatus(404);
        $response->assertViewIs('superadmin.recuperar-desactivada');
    }

    public function test_ruta_recuperacion_con_token_incorrecto_devuelve_404(): void
    {
        $this->activarToken();

        $response = $this->get('/recuperar-superadmin/token-equivocado');

        $response->assertStatus(404);
        $response->assertViewIs('superadmin.recuperar-desactivada');
    }

    public function test_post_recuperacion_con_token_incorrecto_no_modifica_usuarios(): void
    {
        $this->activarToken();

        User::create([
            'name' => 'Víctima',
            'email' => 'victima@test.com',
            'password' => 'PasswordOriginal1',
            'rol' => 'superadmin',
            'activo' => true,
            'tenant_id' => null,
        ]);

        $response = $this->post('/recuperar-superadmin/token-equivocado', [
            'email' => 'atacante@malicioso.com',
            'password' => 'PasswordAtacante1',
            'password_confirmation' => 'PasswordAtacante1',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('users', ['email' => 'atacante@malicioso.com']);

        $victima = User::where('email', 'victima@test.com')->first();
        $this->assertTrue(Hash::check('PasswordOriginal1', $victima->password));
    }

    // ------------------------------------------------------------------
    // 2. Con token válido se muestra el formulario
    // ------------------------------------------------------------------

    public function test_ruta_recuperacion_con_token_valido_muestra_formulario(): void
    {
        $this->activarToken();

        $response = $this->get('/recuperar-superadmin/' . self::TOKEN);

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.recuperar');
        $response->assertSee('type="email"', false);
        $response->assertSee('type="password"', false);
    }

    public function test_pagina_recuperacion_no_expone_hashes_de_password(): void
    {
        $this->activarToken();

        User::create([
            'name' => 'Super Admin',
            'email' => 'sa@test.com',
            'password' => 'PasswordSecreta99',
            'rol' => 'superadmin',
            'activo' => true,
            'tenant_id' => null,
        ]);

        $response = $this->get('/recuperar-superadmin/' . self::TOKEN);

        $response->assertStatus(200);
        $contenido = $response->getContent();
        $this->assertStringNotContainsString('$2y$', $contenido);
        $this->assertStringNotContainsString('PasswordSecreta99', $contenido);
    }

    // ------------------------------------------------------------------
    // 3. Flujo de reset con token válido
    // ------------------------------------------------------------------

    public function test_reset_crea_superadmin_cuando_el_email_no_existe(): void
    {
        $this->activarToken();

        $response = $this->post('/recuperar-superadmin/' . self::TOKEN, [
            'email' => 'nuevo-sa@test.com',
            'password' => 'NuevaClaveSegura123',
            'password_confirmation' => 'NuevaClaveSegura123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $user = User::where('email', 'nuevo-sa@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('superadmin', $user->rol);
        $this->assertTrue($user->activo);
        $this->assertNull($user->tenant_id);
        $this->assertTrue(Hash::check('NuevaClaveSegura123', $user->password));
    }

    public function test_reset_actualiza_usuario_existente_y_limpia_2fa(): void
    {
        $this->activarToken();

        $tenant = \App\Models\Tenant::create([
            'empresa' => 'Tienda Test',
            'subdominio' => 'tiendatest',
            'email_contacto' => 'tienda@test.com',
            'telefono_contacto' => '999999999',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $user = User::create([
            'name' => 'Usuario Bloqueado',
            'email' => 'bloqueado@test.com',
            'password' => 'PasswordVieja123',
            'rol' => 'admin',
            'activo' => false,
            'tenant_id' => $tenant->id,
            'two_factor_secret' => 'secreto-2fa-viejo',
            'two_factor_recovery_codes' => 'codigos-viejos',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->post('/recuperar-superadmin/' . self::TOKEN, [
            'email' => 'bloqueado@test.com',
            'password' => 'ClaveRecuperada456',
            'password_confirmation' => 'ClaveRecuperada456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('superadmin', $user->rol);
        $this->assertTrue($user->activo);
        $this->assertNull($user->tenant_id);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertTrue(Hash::check('ClaveRecuperada456', $user->password));
        $this->assertFalse(Hash::check('PasswordVieja123', $user->password));
    }

    public function test_login_superadmin_funciona_tras_la_recuperacion_web(): void
    {
        $this->activarToken();

        $this->post('/recuperar-superadmin/' . self::TOKEN, [
            'email' => 'recuperado@test.com',
            'password' => 'ClaveFinal789',
            'password_confirmation' => 'ClaveFinal789',
        ])->assertSessionHas('success');

        $response = $this->post('/superadmin/login', [
            'email' => 'recuperado@test.com',
            'password' => 'ClaveFinal789',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticated();

        $user = User::where('email', 'recuperado@test.com')->first();
        $this->assertSame('superadmin', $user->rol);
    }

    // ------------------------------------------------------------------
    // 4. Validaciones del formulario de reset
    // ------------------------------------------------------------------

    public function test_reset_rechaza_password_corta(): void
    {
        $this->activarToken();

        $response = $this->from('/recuperar-superadmin/' . self::TOKEN)
            ->post('/recuperar-superadmin/' . self::TOKEN, [
                'email' => 'nuevo-sa@test.com',
                'password' => 'corta12',
                'password_confirmation' => 'corta12',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'nuevo-sa@test.com']);
    }

    public function test_reset_rechaza_confirmacion_distinta(): void
    {
        $this->activarToken();

        $response = $this->from('/recuperar-superadmin/' . self::TOKEN)
            ->post('/recuperar-superadmin/' . self::TOKEN, [
                'email' => 'nuevo-sa@test.com',
                'password' => 'ClaveValida123',
                'password_confirmation' => 'OtraClaveDistinta',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'nuevo-sa@test.com']);
    }

    public function test_reset_rechaza_email_invalido(): void
    {
        $this->activarToken();

        $response = $this->from('/recuperar-superadmin/' . self::TOKEN)
            ->post('/recuperar-superadmin/' . self::TOKEN, [
                'email' => 'no-es-un-email',
                'password' => 'ClaveValida123',
                'password_confirmation' => 'ClaveValida123',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'no-es-un-email']);
    }

    // ------------------------------------------------------------------
    // 5. Rate limiting (fuerza bruta)
    // ------------------------------------------------------------------

    public function test_rate_limiting_bloquea_post_tras_5_intentos(): void
    {
        $this->activarToken();

        // throttle:5,1 → los primeros 5 pasan (aunque devuelvan 404), el 6º debe ser 429
        for ($i = 0; $i < 5; $i++) {
            $this->post('/recuperar-superadmin/token-fuerza-bruta', [
                'email' => 'x@x.com',
                'password' => 'ClaveCualquiera1',
                'password_confirmation' => 'ClaveCualquiera1',
            ]);
        }

        $response = $this->post('/recuperar-superadmin/token-fuerza-bruta', [
            'email' => 'x@x.com',
            'password' => 'ClaveCualquiera1',
            'password_confirmation' => 'ClaveCualquiera1',
        ]);

        $response->assertStatus(429);
    }

    public function test_rate_limiting_bloquea_get_tras_10_intentos(): void
    {
        $this->activarToken();

        // throttle:10,1 → los primeros 10 pasan, el 11º debe ser 429
        for ($i = 0; $i < 10; $i++) {
            $this->get('/recuperar-superadmin/token-fuerza-bruta');
        }

        $response = $this->get('/recuperar-superadmin/token-fuerza-bruta');

        $response->assertStatus(429);
    }

    // ------------------------------------------------------------------
    // 6. Protección del panel /superadmin/dashboard
    // ------------------------------------------------------------------

    public function test_dashboard_superadmin_requiere_autenticacion(): void
    {
        $response = $this->get('/superadmin/dashboard');

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_dashboard_superadmin_rechaza_usuarios_normales(): void
    {
        $user = User::create([
            'name' => 'Usuario Normal',
            'email' => 'normal@test.com',
            'password' => 'PasswordNormal1',
            'rol' => 'vendedor',
            'activo' => true,
            'tenant_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/superadmin/dashboard');

        $response->assertStatus(403);
        $this->assertAuthenticatedAs($user);
    }

    // ------------------------------------------------------------------
    // 7. Comando artisan superadmin:reset
    // ------------------------------------------------------------------

    public function test_comando_superadmin_reset_crea_usuario(): void
    {
        $this->artisan('superadmin:reset', [
            '--email' => 'cli-sa@test.com',
            '--password' => 'ClavePorCli123',
            '--force' => true,
        ])->assertExitCode(0);

        $user = User::where('email', 'cli-sa@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('superadmin', $user->rol);
        $this->assertTrue($user->activo);
        $this->assertNull($user->tenant_id);
        $this->assertNull($user->two_factor_secret);
        $this->assertTrue(Hash::check('ClavePorCli123', $user->password));
    }

    public function test_comando_superadmin_reset_actualiza_y_limpia_2fa(): void
    {
        $user = User::create([
            'name' => 'Existente',
            'email' => 'existente@test.com',
            'password' => 'PasswordAntigua1',
            'rol' => 'admin',
            'activo' => false,
            'tenant_id' => null,
            'two_factor_secret' => 'secreto-antiguo',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->artisan('superadmin:reset', [
            '--email' => 'existente@test.com',
            '--password' => 'PasswordNueva99',
            '--force' => true,
        ])->assertExitCode(0);

        $user->refresh();
        $this->assertSame('superadmin', $user->rol);
        $this->assertTrue($user->activo);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertTrue(Hash::check('PasswordNueva99', $user->password));
    }

    public function test_comando_superadmin_reset_rechaza_password_debil(): void
    {
        $this->artisan('superadmin:reset', [
            '--email' => 'debil@test.com',
            '--password' => 'corta',
            '--force' => true,
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'debil@test.com']);
    }
}