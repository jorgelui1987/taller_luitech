<?php

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupTest extends TestCase
{
    private BackupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BackupService::class);
    }

    public function test_divisor_separa_sentencias_respetando_strings_y_comentarios(): void
    {
        $sql = <<<'SQL'
-- Comentario de encabezado
SET NAMES utf8mb4;
DROP TABLE IF EXISTS demo; -- comentario al final de la línea
CREATE TABLE demo (
  id INTEGER PRIMARY KEY,
  texto TEXT
);
INSERT INTO demo VALUES (1, 'valor con ; punto y coma');
INSERT INTO demo VALUES (2, 'comilla '' doble');
INSERT INTO demo VALUES (3, 'barra \ invertida');
/* bloque
   comentado */
INSERT INTO demo VALUES (4, 'último');
SQL;

        $sentencias = $this->service->dividirSentencias($sql);

        // 7 sentencias: los comentarios NO generan sentencias ni ocultan las siguientes
        $this->assertCount(7, $sentencias);
        $this->assertSame('SET NAMES utf8mb4', $sentencias[0]);
        $this->assertSame('DROP TABLE IF EXISTS demo', $sentencias[1]);
        $this->assertStringContainsString('CREATE TABLE demo', $sentencias[2]);
        // El ';' dentro del string NO corta la sentencia
        $this->assertSame("INSERT INTO demo VALUES (1, 'valor con ; punto y coma')", $sentencias[3]);
        // La comilla escapada ('') NO cierra el string antes de tiempo
        $this->assertSame("INSERT INTO demo VALUES (2, 'comilla '' doble')", $sentencias[4]);
        $this->assertSame("INSERT INTO demo VALUES (3, 'barra \\ invertida')", $sentencias[5]);
        $this->assertSame("INSERT INTO demo VALUES (4, 'último')", $sentencias[6]);
    }

    public function test_restauracion_ejecuta_drop_create_insert_con_datos_especiales(): void
    {
        DB::statement('DROP TABLE IF EXISTS backup_demo');

        // Formato idéntico al que genera el sistema: comentarios antes del DROP
        $sql = <<<'SQL'
-- --------------------------------------------------------------
-- Tabla: backup_demo
-- --------------------------------------------------------------
DROP TABLE IF EXISTS backup_demo;
CREATE TABLE backup_demo (id INTEGER PRIMARY KEY, nombre TEXT NOT NULL DEFAULT '');
INSERT INTO backup_demo (id, nombre) VALUES (1, 'Ana; Pérez');
INSERT INTO backup_demo (id, nombre) VALUES (2, 'O''Brien');
INSERT INTO backup_demo (id, nombre) VALUES (3, 'multi ; línea');
SQL;

        $stats = $this->service->restaurarDesdeContenido($sql);

        $this->assertSame(0, $stats['fallidas'], 'Errores: ' . implode(' | ', $stats['errores']));
        $this->assertSame(5, $stats['ejecutadas']);
        $this->assertSame(3, DB::table('backup_demo')->count());
        $this->assertSame('Ana; Pérez', DB::table('backup_demo')->where('id', 1)->value('nombre'));
        $this->assertSame("O'Brien", DB::table('backup_demo')->where('id', 2)->value('nombre'));

        DB::statement('DROP TABLE IF EXISTS backup_demo');
    }

    public function test_restaurar_dos_veces_deja_datos_identicos_sin_duplicados(): void
    {
        // Regresión del bug original: antes, el DROP TABLE se saltaba por los
        // comentarios previos, los INSERT fallaban por IDs duplicados en silencio
        // y solo se restauraban las tablas vacías.
        DB::statement('DROP TABLE IF EXISTS backup_demo');

        $sql = <<<'SQL'
-- Tabla: backup_demo
DROP TABLE IF EXISTS backup_demo;
CREATE TABLE backup_demo (id INTEGER PRIMARY KEY, nombre TEXT NOT NULL DEFAULT '');
INSERT INTO backup_demo (id, nombre) VALUES (1, 'Primera carga');
INSERT INTO backup_demo (id, nombre) VALUES (2, 'Segunda fila');
SQL;

        $this->service->restaurarDesdeContenido($sql);

        // Segunda pasada sobre datos ya existentes: debe limpiar y volver a cargar
        $stats2 = $this->service->restaurarDesdeContenido($sql);

        $this->assertSame(0, $stats2['fallidas'], 'Errores: ' . implode(' | ', $stats2['errores']));
        $this->assertSame(2, DB::table('backup_demo')->count(), 'No debe haber duplicados');
        $this->assertSame('Primera carga', DB::table('backup_demo')->where('id', 1)->value('nombre'));

        DB::statement('DROP TABLE IF EXISTS backup_demo');
    }

    public function test_restauracion_bloquea_comandos_peligrosos(): void
    {
        $sql = "DROP TABLE IF EXISTS tabla_inofensiva;\nDROP DATABASE base_importante;\nCREATE TABLE tabla_temporal_backup_test (x INT);";

        $stats = $this->service->restaurarDesdeContenido($sql);

        $this->assertSame(1, $stats['bloqueadas'], 'DROP DATABASE debe bloquearse');
        $this->assertSame(2, $stats['ejecutadas']);
        $this->assertSame(0, $stats['fallidas']);
    }

    public function test_errores_sql_se_reportan_y_no_se_silencian(): void
    {
        $sql = "INSERT INTO tabla_que_no_existe_xyz VALUES (1);";

        $stats = $this->service->restaurarDesdeContenido($sql);

        $this->assertSame(1, $stats['fallidas'], 'El error debe contarse como fallido');
        $this->assertNotEmpty($stats['errores'], 'El error debe reportarse en la lista de errores');
        $this->assertSame(0, $stats['ejecutadas']);
    }

    public function test_ciclo_completo_backup_restaurar_en_mysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('El ciclo completo requiere una base de datos MySQL.');
        }

        // Datos iniciales
        DB::table('clientes')->insert([
            'nombre' => 'Juan', 'apellido' => 'Pérez', 'telefono' => '999888777',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $clientesAntes = DB::table('clientes')->count();
        $usuariosAntes = DB::table('users')->count();

        // Generar backup real y destruir los datos
        $sql = $this->service->generarSQL();
        DB::table('clientes')->delete();

        $this->assertSame(0, DB::table('clientes')->count());

        // Restaurar y verificar que TODO vuelve (no solo una tabla)
        $stats = $this->service->restaurarDesdeContenido($sql);

        $this->assertSame(0, $stats['fallidas'], 'Errores: ' . implode(' | ', $stats['errores']));
        $this->assertSame($clientesAntes, DB::table('clientes')->count());
        $this->assertSame($usuariosAntes, DB::table('users')->count());
        $this->assertSame('Juan', DB::table('clientes')->where('telefono', '999888777')->value('nombre'));
    }
}