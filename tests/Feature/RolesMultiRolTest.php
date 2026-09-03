<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica el sistema multi-rol: un usuario puede tener varios roles
 * (ej. ['tecnico','vendedor']) manteniendo retrocompatibilidad con el
 * rol único antiguo (columna rol).
 */
class RolesMultiRolTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'empresa'        => 'Tienda Test',
            'subdominio'     => 'tiendatest',
            'email_contacto' => 'tienda@test.com',
            'plan'           => 'basico',
            'estado'         => 'activo',
        ]);
    }

    private function crearUsuario(array $extra = []): User
    {
        return User::create(array_merge([
            'name'      => 'Usuario Test',
            'email'     => 'user' . uniqid() . '@test.com',
            'password'  => bcrypt('password123'),
            'rol'       => 'tecnico',
            'activo'    => true,
            'tenant_id' => $this->tenant->id,
        ], $extra));
    }

    public function test_rol_unico_legacy_sigue_funcionando(): void
    {
        $user = $this->crearUsuario(['rol' => 'tecnico', 'roles' => null]);

        $this->assertTrue($user->esTecnico());
        $this->assertTrue($user->tieneRol('tecnico'));
        $this->assertTrue($user->puedeReparar());
        $this->assertFalse($user->puedeVender());
        $this->assertFalse($user->puedeEliminar());
    }

    public function test_usuario_multi_rol_vende_y_repara(): void
    {
        $user = $this->crearUsuario([
            'rol'   => 'tecnico',
            'roles' => ['tecnico', 'vendedor'],
        ]);

        $this->assertTrue($user->esTecnico());
        $this->assertTrue($user->esVendedor());
        $this->assertTrue($user->puedeVender());
        $this->assertTrue($user->puedeReparar());
        // El borrado sigue reservado al admin
        $this->assertFalse($user->puedeEliminar());
        $this->assertFalse($user->esAdmin());
    }

    public function test_dashboard_combinado_para_tecnico_vendedor(): void
    {
        $user = $this->crearUsuario(['roles' => ['tecnico', 'vendedor']]);
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_tecnico_simple_sigue_viendo_su_dashboard(): void
    {
        $user = $this->crearUsuario(['roles' => ['tecnico']]);
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_vendedor_simple_sigue_viendo_su_dashboard(): void
    {
        $user = $this->crearUsuario(['rol' => 'vendedor', 'roles' => ['vendedor']]);
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_registro_crea_usuario_con_roles_multiples(): void
    {
        $admin = $this->crearUsuario([
            'rol'   => 'admin',
            'roles' => ['admin'],
            'email' => 'admin-multirol@test.com',
        ]);
        $this->actingAs($admin);

        $response = $this->post(route('register.post'), [
            'name'                  => 'Tecnico Vendedor',
            'email'                 => 'multirol@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles'                 => ['tecnico', 'vendedor'],
        ]);

        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'multirol@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(['tecnico', 'vendedor'], $user->roles);
        $this->assertSame('tecnico', $user->rol);
        $response->assertRedirect(route('register'));
    }

    public function test_registro_solo_admin_puede_crear_usuarios(): void
    {
        $vendedor = $this->crearUsuario([
            'rol'   => 'vendedor',
            'roles' => ['vendedor'],
            'email' => 'vendedor-multirol@test.com',
        ]);
        $this->actingAs($vendedor);

        $response = $this->post(route('register.post'), [
            'name'                  => 'Intento Admin',
            'email'                 => 'intento-multirol@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles'                 => ['admin'],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'intento-multirol@test.com']);
    }

    public function test_configuracion_crea_usuario_con_roles_multiples(): void
    {
        $admin = $this->crearUsuario([
            'rol'   => 'admin',
            'roles' => ['admin'],
            'email' => 'admin-config@test.com',
        ]);
        $this->actingAs($admin);

        $this->post(route('configuracion.storeUsuario'), [
            'name'                  => 'Multi Config',
            'email'                 => 'config-multi@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles'                 => ['tecnico', 'vendedor'],
        ]);

        $user = User::where('email', 'config-multi@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(['tecnico', 'vendedor'], $user->roles);
        $this->assertTrue($user->puedeVender());
        $this->assertTrue($user->puedeReparar());
        $this->assertFalse($user->puedeEliminar());
    }

    public function test_configuracion_protege_al_ultimo_admin(): void
    {
        $admin = $this->crearUsuario([
            'rol'   => 'admin',
            'roles' => ['admin'],
            'email' => 'ultimo-admin@test.com',
        ]);
        $this->actingAs($admin);

        $this->put(route('configuracion.updateUsuario', $admin->id), [
            'name'                  => 'Ultimo Admin',
            'email'                 => 'ultimo-admin@test.com',
            'roles'                 => ['vendedor'],
            'password'              => '',
            'password_confirmation' => '',
        ]);

        $admin->refresh();
        $this->assertTrue($admin->esAdmin(), 'El único admin de la empresa no puede perder el rol admin');
        $this->assertSame(['admin'], $admin->roles);
    }
}
