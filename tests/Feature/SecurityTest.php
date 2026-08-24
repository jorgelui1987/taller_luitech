<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Suite de pruebas de seguridad contra ataques comunes.
 *
 * Cubre:
 * 1. Bypass de autenticación (rutas protegidas sin sesión)
 * 2. Configuración CSRF (solo el webhook de MP exento, protegido por firma)
 * 3. XSS almacenado (script inyectado debe salir escapado)
 * 4. SQL Injection (payloads en búsquedas y formularios)
 * 5. IDOR / Aislamiento de tenants (un tenant no ve datos de otro)
 * 6. Webhook de Mercado Pago (firma inválida rechazada, válida aceptada)
 * 7. Hash de contraseñas (nunca en texto plano)
 * 8. Asignación masiva (tenant_id foráneo no se puede inyectar)
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;
    private Tenant $tenantB;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'empresa' => 'Tienda A',
            'subdominio' => 'tiendaa',
            'email_contacto' => 'a@test.com',
            'telefono_contacto' => '111111111',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->tenantB = Tenant::create([
            'empresa' => 'Tienda B',
            'subdominio' => 'tiendab',
            'email_contacto' => 'b@test.com',
            'telefono_contacto' => '222222222',
            'plan' => 'basico',
            'estado' => 'activo',
            'max_usuarios' => 5,
            'max_productos' => 100,
        ]);

        $this->userA = User::create([
            'name' => 'Admin A',
            'email' => 'admin@a.com',
            'password' => 'PasswordA123',
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenantA->id,
        ]);

        $this->userB = User::create([
            'name' => 'Admin B',
            'email' => 'admin@b.com',
            'password' => 'PasswordB123',
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenantB->id,
        ]);
    }

    // ------------------------------------------------------------------
    // 1. Bypass de autenticación
    // ------------------------------------------------------------------

    public function test_rutas_protegidas_redirigen_a_login_sin_sesion(): void
    {
        $rutasProtegidas = [
            '/dashboard', '/ventas', '/clientes', '/productos',
            '/reparaciones', '/backup', '/configuracion', '/caja',
        ];

        foreach ($rutasProtegidas as $ruta) {
            $response = $this->get($ruta);
            $this->assertContains(
                $response->status(),
                [302, 403],
                "La ruta {$ruta} debe estar protegida (302/403), recibió {$response->status()}"
            );
            if ($response->status() === 302) {
                $this->assertGuest();
            }
        }
    }

    public function test_accion_de_escritura_sin_sesion_es_rechazada(): void
    {
        $response = $this->post('/clientes', [
            'nombre' => 'Intruso',
            'apellido' => 'Test',
            'telefono' => '999',
        ]);

        $this->assertContains($response->status(), [419, 302, 403]);
        $this->assertDatabaseMissing('clientes', ['nombre' => 'Intruso']);
    }

    // ------------------------------------------------------------------
    // 2. Configuración CSRF
    // ------------------------------------------------------------------

    public function test_configuracion_csrf_solo_excluye_webhook_de_mercadopago(): void
    {
        // Nota: con APP_ENV=testing Laravel desactiva la verificación CSRF
        // (VerifyCsrfToken::runningUnitTests()), por lo que un 419 no puede
        // simularse en pruebas de feature. En su lugar verificamos la
        // configuración: la ÚNICA ruta exenta debe ser el webhook de Mercado
        // Pago (protegido por firma HMAC-SHA256). Todo lo demás queda protegido.
        $propiedad = new \ReflectionProperty(VerifyCsrfToken::class, 'except');
        $excluidas = $propiedad->getDefaultValue();

        $this->assertSame(['webhooks/mercadopago'], $excluidas);
    }

    // ------------------------------------------------------------------
    // 3. XSS almacenado
    // ------------------------------------------------------------------

    public function test_xss_en_nombre_de_cliente_sale_escapado(): void
    {
        $payload = '<script>alert("xss")</script>';

        $cliente = Cliente::create([
            'nombre' => $payload,
            'apellido' => 'Test',
            'telefono' => '999888777',
            'tenant_id' => $this->tenantA->id,
        ]);

        $response = $this->actingAs($this->userA)->get("/clientes/{$cliente->id}");

        $response->assertStatus(200);
        // El script NUNCA debe aparecer sin escapar en el HTML
        $this->assertStringNotContainsString('<script>alert', $response->getContent());
    }

    public function test_xss_en_busqueda_no_se_ejecuta(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/clientes?buscar=<script>alert(1)</script>');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $response->getContent());
    }

    // ------------------------------------------------------------------
    // 4. SQL Injection
    // ------------------------------------------------------------------

    public function test_sql_injection_en_busqueda_no_filtra_datos_ni_rompe(): void
    {
        Cliente::create([
            'nombre' => 'Cliente Legitimo',
            'apellido' => 'Test',
            'telefono' => '111',
            'tenant_id' => $this->tenantA->id,
        ]);

        $this->actingAs($this->userA);

        $payloads = [
            "X' OR '1'='1",
            "'; DROP TABLE clientes; --",
            "' UNION SELECT * FROM users --",
            "1' OR 1=1 --",
        ];

        foreach ($payloads as $payload) {
            $response = $this->get('/clientes?buscar=' . urlencode($payload));
            $this->assertSame(
                200,
                $response->status(),
                "La búsqueda con payload SQLi debe responder 200 sin error. Payload: {$payload}"
            );
        }

        // La tabla sigue intacta y el cliente legítimo sigue ahí
        $this->assertDatabaseHas('clientes', ['nombre' => 'Cliente Legitimo']);
        $this->assertDatabaseCount('clientes', 1);
    }

    public function test_sql_injection_en_login_no_autentica(): void
    {
        $response = $this->post('/login', [
            'email' => "admin@a.com' OR '1'='1",
            'password' => "' OR '1'='1",
        ]);

        $this->assertGuest();
    }

    public function test_sql_injection_en_creacion_se_guarda_como_texto_literal(): void
    {
        $this->actingAs($this->userA);

        $payload = "Robert'); DROP TABLE clientes;--";

        $response = $this->post('/clientes', [
            'nombre' => $payload,
            'apellido' => 'Test',
            'telefono' => '555',
            'tipo' => 'particular',
        ]);

        $response->assertStatus(302);
        // El "ataque" se guardó como texto literal y la tabla sigue existiendo
        $this->assertDatabaseHas('clientes', ['nombre' => $payload]);
        $this->assertDatabaseCount('clientes', 1);
    }

    // ------------------------------------------------------------------
    // 5. IDOR / Aislamiento de tenants
    // ------------------------------------------------------------------

    public function test_usuario_no_puede_ver_venta_de_otro_tenant(): void
    {
        $ventaB = Venta::create([
            'numero_venta' => 'V-SEC-B1',
            'user_id' => $this->userB->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'total' => 119,
            'estado' => 'completada',
            'tenant_id' => $this->tenantB->id,
        ]);

        $response = $this->actingAs($this->userA)->get("/ventas/{$ventaB->id}");

        $response->assertStatus(404);
    }

    public function test_usuario_no_puede_ver_cliente_de_otro_tenant(): void
    {
        $clienteB = Cliente::create([
            'nombre' => 'Cliente De Tienda B',
            'apellido' => 'Test',
            'telefono' => '222',
            'tenant_id' => $this->tenantB->id,
        ]);

        $response = $this->actingAs($this->userA)->get("/clientes/{$clienteB->id}");

        $response->assertStatus(404);
    }

    public function test_usuario_no_puede_modificar_cliente_de_otro_tenant(): void
    {
        $clienteB = Cliente::create([
            'nombre' => 'Cliente B',
            'apellido' => 'Test',
            'telefono' => '222',
            'tenant_id' => $this->tenantB->id,
        ]);

        $response = $this->actingAs($this->userA)
            ->put("/clientes/{$clienteB->id}", [
                'nombre' => 'Hackeado',
                'apellido' => 'Test',
                'telefono' => '666',
            ]);

        $this->assertContains($response->status(), [404, 403]);
        $this->assertDatabaseHas('clientes', [
            'id' => $clienteB->id,
            'nombre' => 'Cliente B',
        ]);
    }

    public function test_listado_de_clientes_solo_muestra_su_tenant(): void
    {
        Cliente::create([
            'nombre' => 'Cliente A1', 'apellido' => 'Test',
            'telefono' => '111', 'tenant_id' => $this->tenantA->id,
        ]);
        Cliente::create([
            'nombre' => 'Cliente B1', 'apellido' => 'Test',
            'telefono' => '222', 'tenant_id' => $this->tenantB->id,
        ]);

        $response = $this->actingAs($this->userA)->get('/clientes');

        $response->assertStatus(200);
        $response->assertSee('Cliente A1');
        $response->assertDontSee('Cliente B1');
    }

    public function test_usuario_sin_tenant_no_ve_ningun_dato(): void
    {
        $sinTenant = User::create([
            'name' => 'Sin Tenant',
            'email' => 'sintenant@test.com',
            'password' => 'PasswordX123',
            'rol' => 'vendedor',
            'activo' => true,
            'tenant_id' => null,
        ]);

        Cliente::create([
            'nombre' => 'Cliente A1', 'apellido' => 'Test',
            'telefono' => '111', 'tenant_id' => $this->tenantA->id,
        ]);

        $response = $this->actingAs($sinTenant)->get('/clientes');

        $response->assertStatus(200);
        $response->assertDontSee('Cliente A1');
    }

    // ------------------------------------------------------------------
    // 6. Webhook de Mercado Pago (firma HMAC)
    // ------------------------------------------------------------------

    private function crearConfiguracionConSecret(string $secret): Configuracion
    {
        // Asignación directa de propiedades (no mass assignment) para probar
        // la lógica de validación de firma independientemente del $fillable.
        $config = new Configuracion();
        $config->nombre_tienda = 'Tienda Webhook';
        $config->mercadopago_webhook_secret = $secret;
        $config->tenant_id = $this->tenantA->id;
        $config->save();

        return $config;
    }

    private function firmarWebhook(string $secret, string $requestId, string $ts): string
    {
        $manifest = "id:{$requestId};request-id:{$requestId};ts:{$ts};";

        return "ts={$ts},v1=" . hash_hmac('sha256', $manifest, $secret);
    }

    public function test_webhook_sin_firma_valida_es_rechazado_y_no_marca_pago(): void
    {
        $this->crearConfiguracionConSecret('secreto-super-seguro');

        $venta = Venta::create([
            'numero_venta' => 'V-SEC-W1',
            'user_id' => $this->userA->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'total' => 119,
            'estado' => 'pendiente',
            'tenant_id' => $this->tenantA->id,
        ]);

        // Notificación FALSIFICADA sin cabeceras de firma
        $response = $this->post('/webhooks/mercadopago', [
            'type' => 'order',
            'action' => 'order.processed',
            'data' => [
                'external_reference' => 'VENTA-V-SEC-W1',
                'status' => 'processed',
            ],
        ]);

        $response->assertStatus(401);

        $venta->refresh();
        $this->assertSame('pendiente', $venta->estado, 'Un atacante no debe poder marcar la venta como pagada');
        $this->assertSame('pendiente', $venta->estado_pago);
    }

    public function test_webhook_con_firma_valida_es_aceptado(): void
    {
        $this->crearConfiguracionConSecret('secreto-super-seguro');

        $venta = Venta::create([
            'numero_venta' => 'V-SEC-W2',
            'user_id' => $this->userA->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'total' => 119,
            'estado' => 'pendiente',
            'tenant_id' => $this->tenantA->id,
        ]);

        $requestId = 'req-test-123';
        $ts = (string) time();

        $response = $this->post('/webhooks/mercadopago', [
            'type' => 'order',
            'action' => 'order.processed',
            'data' => [
                'external_reference' => 'VENTA-V-SEC-W2',
                'status' => 'processed',
            ],
        ], [
            'x-request-id' => $requestId,
            'x-signature' => $this->firmarWebhook('secreto-super-seguro', $requestId, $ts),
        ]);

        $response->assertStatus(200);

        $venta->refresh();
        $this->assertSame('completada', $venta->estado);
        $this->assertSame('pagado', $venta->estado_pago);
    }

    public function test_webhook_con_firma_de_otro_secret_es_rechazado(): void
    {
        $this->crearConfiguracionConSecret('secreto-real-de-produccion');

        $venta = Venta::create([
            'numero_venta' => 'V-SEC-W3',
            'user_id' => $this->userA->id,
            'fecha_venta' => now(),
            'subtotal' => 100,
            'total' => 119,
            'estado' => 'pendiente',
            'tenant_id' => $this->tenantA->id,
        ]);

        // Firmado con un secret DISTINTO al configurado
        $requestId = 'req-atacante';
        $ts = (string) time();

        $response = $this->post('/webhooks/mercadopago', [
            'type' => 'order',
            'action' => 'order.processed',
            'data' => [
                'external_reference' => 'VENTA-V-SEC-W3',
                'status' => 'processed',
            ],
        ], [
            'x-request-id' => $requestId,
            'x-signature' => $this->firmarWebhook('secreto-del-atacante', $requestId, $ts),
        ]);

        $response->assertStatus(401);

        $venta->refresh();
        $this->assertSame('pendiente', $venta->estado);
    }

    public function test_webhook_secret_se_puede_guardar_desde_configuracion(): void
    {
        // Regresión: mercadopago_webhook_secret faltaba en $fillable y en el
        // controlador, por lo que NUNCA se podía configurar desde la UI y el
        // webhook quedaba desprotegido (aceptaba notificaciones sin firmar).
        $this->actingAs($this->userA);

        Configuracion::create([
            'nombre_tienda' => 'Tienda Config',
            'tenant_id' => $this->tenantA->id,
        ]);

        $response = $this->post(route('configuracion.updateEmpresa'), [
            'nombre_tienda' => 'Tienda Config',
            'igv' => 19,
            'moneda' => 'CLP',
            'simbolo_moneda' => '$',
            'mercadopago_webhook_secret' => '  secreto-desde-ui  ',
        ]);

        $response->assertStatus(302);

        $config = Configuracion::where('tenant_id', $this->tenantA->id)->first();
        $this->assertNotNull($config);
        $this->assertSame(
            'secreto-desde-ui',
            $config->mercadopago_webhook_secret,
            'El webhook secret debe guardarse (trim incluido) desde la UI de configuración'
        );
    }

    // ------------------------------------------------------------------
    // 7. Hash de contraseñas
    // ------------------------------------------------------------------

    public function test_passwords_nunca_se_guardan_en_texto_plano(): void
    {
        $user = User::create([
            'name' => 'Hash Test',
            'email' => 'hash@test.com',
            'password' => 'MiPasswordSecreto99',
            'rol' => 'admin',
            'activo' => true,
            'tenant_id' => $this->tenantA->id,
        ]);

        $enBd = $user->fresh()->password;

        $this->assertNotSame('MiPasswordSecreto99', $enBd);
        $this->assertStringStartsWith('$2y$', $enBd, 'El hash debe ser bcrypt');
        $this->assertTrue(Hash::check('MiPasswordSecreto99', $enBd));
    }

    // ------------------------------------------------------------------
    // 8. Asignación masiva (mass assignment)
    // ------------------------------------------------------------------

    public function test_no_se_puede_inyectar_tenant_id_ajeno_al_crear_cliente(): void
    {
        $this->actingAs($this->userA);

        $response = $this->post('/clientes', [
            'nombre' => 'Cliente Inyeccion',
            'apellido' => 'Test',
            'telefono' => '777',
            'tipo' => 'particular',
            'tenant_id' => $this->tenantB->id, // intento de inyección
        ]);

        $response->assertStatus(302);

        $cliente = Cliente::where('nombre', 'Cliente Inyeccion')->first();
        $this->assertNotNull($cliente);
        $this->assertSame(
            $this->tenantA->id,
            (int) $cliente->tenant_id,
            'El cliente debe quedar en el tenant del usuario autenticado, no en el inyectado'
        );
    }
}