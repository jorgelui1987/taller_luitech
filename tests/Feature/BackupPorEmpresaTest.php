<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests de los backups individuales POR EMPRESA:
 * - generarSQLPorTenant() incluye solo los datos de esa empresa
 * - El comando backup:automatico genera un archivo por cada empresa activa
 * - La vista del superadmin identifica la empresa de cada archivo
 */
class BackupPorEmpresaTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Limpiar archivos generados durante los tests
        foreach (glob(storage_path('app/backups') . '/backup_*.sql') ?: [] as $f) {
            @unlink($f);
        }

        parent::tearDown();
    }

    private function crearTenantConDatos(string $empresa, string $subdominio): Tenant
    {
        $tenant = Tenant::create([
            'empresa'        => $empresa,
            'subdominio'     => $subdominio,
            'email_contacto' => $subdominio . '@test.com',
            'plan'           => 'basico',
            'estado'         => 'activo',
            'max_usuarios'   => 5,
            'max_productos'  => 100,
        ]);

        Cliente::create([
            'nombre'   => "Cliente de {$empresa}",
            'apellido' => 'Prueba',
            'telefono' => '9' . str_pad((string) $tenant->id, 8, '0', STR_PAD_LEFT),
            'dni'      => 'DNI-' . $tenant->id,
            'tipo'     => 'particular',
            'activo'   => true,
            'tenant_id'=> $tenant->id,
        ]);

        return $tenant;
    }

    public function test_backup_por_tenant_solo_incluye_datos_de_esa_empresa(): void
    {
        $tenantA = $this->crearTenantConDatos('Empresa Alpha', 'alpha');
        $tenantB = $this->crearTenantConDatos('Empresa Beta', 'beta');

        $sql = app(\App\Services\BackupService::class)->generarSQLPorTenant($tenantA->id);

        // Contiene los datos de la empresa A
        $this->assertStringContainsString("Empresa Alpha", $sql);
        $this->assertStringContainsString("Cliente de Empresa Alpha", $sql);

        // NO contiene los datos de la empresa B
        $this->assertStringNotContainsString("Cliente de Empresa Beta", $sql);
    }

    public function test_comando_automatico_genera_archivo_por_cada_empresa(): void
    {
        $this->crearTenantConDatos('Tienda Uno', 'tiendauno');
        $this->crearTenantConDatos('Tienda Dos', 'tiendados');

        $this->artisan('backup:automatico')->assertExitCode(0);

        $archivos = glob(storage_path('app/backups') . '/backup_empresa-*.sql') ?: [];
        $this->assertCount(2, $archivos, 'Debe generarse un archivo por cada empresa activa');

        // Verificar contenido del primero que coincida con Tienda Uno
        $archivoUno = null;
        foreach ($archivos as $archivo) {
            if (str_contains($archivo, 'tienda-uno')) {
                $archivoUno = $archivo;
                break;
            }
        }
        $this->assertNotNull($archivoUno, 'Debe existir el archivo de Tienda Uno');
        $this->assertStringContainsString('Cliente de Tienda Uno', file_get_contents($archivoUno));
    }

    public function test_comando_automatico_genera_tambien_el_global(): void
    {
        $this->crearTenantConDatos('Solo Una', 'solouna');

        $this->artisan('backup:automatico')->assertExitCode(0);

        $globales = glob(storage_path('app/backups') . '/backup_auto_*.sql') ?: [];
        $this->assertCount(1, $globales, 'Debe seguir generándose el backup global');
    }

    public function test_vista_superadmin_identifica_la_empresa_del_backup(): void
    {
        $superAdmin = User::create([
            'name' => 'SA Vista',
            'email' => 'sa-vista@test.com',
            'password' => Hash::make('PasswordSecreta99'),
            'rol' => 'superadmin',
            'activo' => true,
            'tenant_id' => null,
        ]);

        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/backup_empresa-tienda-juan_2026-08-25_02-00-00.sql', "-- prueba\n");

        $response = $this->actingAs($superAdmin)->get('/superadmin/backups');

        $response->assertStatus(200);
        // ucfirst sobre el slug: "Tienda juan" (primera letra mayúscula)
        $response->assertSee('Tienda juan');
        $response->assertSee('backup_empresa-tienda-juan_2026-08-25_02-00-00.sql');
    }
}