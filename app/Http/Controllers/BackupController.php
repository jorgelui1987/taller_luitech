<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use Carbon\Carbon;
use PDO;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    // ── Vista principal ──────────────────────────────────────────────────────
    public function index()
    {
        $archivos = glob($this->backupDir . '/*.sql') ?: [];
        $backups  = [];

        foreach ($archivos as $archivo) {
            $backups[] = [
                'nombre'  => basename($archivo),
                'tamanio' => filesize($archivo),
                'fecha'   => Carbon::createFromTimestamp(filemtime($archivo)),
            ];
        }

        usort($backups, fn($a, $b) => $b['fecha'] <=> $a['fecha']);

        $stats = [
            'ventas'      => Venta::count(),
            'clientes'    => Cliente::count(),
            'productos'   => Producto::count(),
            'reparaciones'=> Reparacion::count(),
            'backups'     => count($backups),
            'ultimo'      => count($backups) > 0 ? $backups[0]['fecha'] : null,
            'tamTotal'    => array_sum(array_column($backups, 'tamanio')),
        ];

        return view('backup.index', compact('backups', 'stats'));
    }

    // ── Crear backup ─────────────────────────────────────────────────────────
    public function crear()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $sql    = $this->generarSQL();
            $nombre = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $ruta   = $this->backupDir . '/' . $nombre;
            file_put_contents($ruta, $sql);

            return back()->with('success', "Backup <strong>{$nombre}</strong> creado correctamente (" . $this->formatBytes(filesize($ruta)) . ").");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al crear el backup: ' . $e->getMessage());
        }
    }

    // ── Descargar archivo ────────────────────────────────────────────────────
    public function descargar(string $nombre)
    {
        $ruta = $this->backupDir . '/' . basename($nombre);

        abort_unless(file_exists($ruta) && str_ends_with($nombre, '.sql'), 404, 'Archivo no encontrado.');

        return response()->download($ruta);
    }

    // ── Eliminar archivo ─────────────────────────────────────────────────────
    public function eliminar(string $nombre)
    {
        $ruta = $this->backupDir . '/' . basename($nombre);

        if (file_exists($ruta) && str_ends_with($nombre, '.sql')) {
            unlink($ruta);
            return back()->with('success', "Backup <strong>{$nombre}</strong> eliminado.");
        }

        return back()->with('error', 'Archivo no encontrado.');
    }

    // ── Restaurar desde archivo ───────────────────────────────────────────────
    public function restaurar(Request $request)
    {
        $request->validate([
            'archivo_sql' => 'required|file|max:51200|mimes:sql,txt',
        ], [
            'archivo_sql.required' => 'Debes seleccionar un archivo .sql',
            'archivo_sql.max'      => 'El archivo no puede superar 100 MB.',
            'archivo_sql.mimes'    => 'El archivo debe ser de tipo .sql',
        ]);

        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $driver = DB::connection()->getDriverName();

        try {
            // Backup automático de seguridad antes de restaurar
            $autoNombre = 'pre_restore_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($this->backupDir . '/' . $autoNombre, $this->generarSQL());

            $contenido = file_get_contents($request->file('archivo_sql')->getRealPath());

            // Validar que el archivo contenga al menos una sentencia de backup esperada
            $sentenciasPermitidas = [
                'INSERT INTO', 'CREATE TABLE', 'SET NAMES', 'SET FOREIGN_KEY_CHECKS',
                'SET session_replication_role', 'DROP TABLE IF EXISTS', 'ALTER TABLE',
                'UPDATE ', 'DELETE FROM'
            ];

            $esBackupValido = false;
            foreach ($sentenciasPermitidas as $permitida) {
                if (stripos($contenido, $permitida) !== false) {
                    $esBackupValido = true;
                    break;
                }
            }

            if (!$esBackupValido) {
                throw new \RuntimeException('El archivo no contiene sentencias SQL válidas de backup.');
            }

            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = replica');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            // Dividir en sentencias individuales y validar cada una
            $statements = preg_split('/;\s*[\r\n]+/', $contenido);

            $comandosBloqueados = ['DROP DATABASE', 'DROP USER', 'GRANT', 'REVOKE', 'ALTER USER', 'CREATE USER'];

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt) || preg_match('/^--/', $stmt) || preg_match('/^\/\*/', $stmt)) {
                    continue;
                }

                // Bloquear comandos peligrosos
                $stmtUpper = strtoupper($stmt);
                $comandoBloqueado = false;
                foreach ($comandosBloqueados as $comando) {
                    if (strpos($stmtUpper, $comando) !== false) {
                        $comandoBloqueado = true;
                        break;
                    }
                }

                if ($comandoBloqueado) {
                    continue;
                }

                try {
                    DB::unprepared($stmt);
                } catch (\Throwable $e) {
                    // Continuar con las siguientes sentencias
                }
            }

            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            return back()->with('success', 'Base de datos restaurada correctamente. Se guardó un backup automático previo (<strong>' . $autoNombre . '</strong>).');
        } catch (\Throwable $e) {
            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    // ── Resetear sistema ─────────────────────────────────────────────────────
    public function resetear(Request $request)
    {
        $request->validate([
            'tipo_reset'   => 'required|in:ventas,datos,total',
            'confirmacion' => 'required|in:RESETEAR',
        ], [
            'confirmacion.in' => 'Debes escribir exactamente "RESETEAR" para confirmar.',
        ]);

        $driver = DB::connection()->getDriverName();

        // Backup automático antes de resetear
        try {
            $autoNombre = 'pre_reset_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($this->backupDir . '/' . $autoNombre, $this->generarSQL());
        } catch (\Throwable) { /* Continúa aunque falle el backup */ }

        // Tablas hijas que referencian a las tablas padre (orden de eliminación correcto)
        $tablasHijas = [
            'reparacion_fotos',
            'detalle_ventas',
            'detalle_ordenes_compra',
            'ordenes_compra',
            'movimientos_stock',
            'comisiones_pagos',
            'gastos_fijos',
        ];

        try {
            // Desactivar verificaciones de FK según driver
            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = replica');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            switch ($request->tipo_reset) {
                case 'ventas':
                    // Tablas hijas
                    foreach ($tablasHijas as $tabla) {
                        if (Schema::hasTable($tabla)) {
                            DB::table($tabla)->delete();
                        }
                    }
                    DB::table('ventas')->delete();
                    DB::table('reparaciones')->delete();
                    $msg = 'Ventas y reparaciones eliminadas. Clientes, productos y usuarios conservados.';
                    break;

                case 'datos':
                    // Tablas hijas
                    foreach ($tablasHijas as $tabla) {
                        if (Schema::hasTable($tabla)) {
                            DB::table($tabla)->delete();
                        }
                    }
                    DB::table('ventas')->delete();
                    DB::table('reparaciones')->delete();
                    DB::table('clientes')->delete();
                    DB::table('productos')->delete();
                    $msg = 'Datos comerciales eliminados. Usuarios, categorías y marcas conservados.';
                    break;

                case 'total':
                    // Tablas hijas
                    foreach ($tablasHijas as $tabla) {
                        if (Schema::hasTable($tabla)) {
                            DB::table($tabla)->delete();
                        }
                    }
                    DB::table('ventas')->delete();
                    DB::table('reparaciones')->delete();
                    DB::table('clientes')->delete();
                    DB::table('productos')->delete();
                    DB::table('users')->where('rol', '!=', 'admin')->delete();
                    $msg = 'Sistema reseteado a estado de fábrica. Solo el administrador fue conservado.';
                    break;
            }

            // Reactivar verificaciones de FK
            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        } catch (\Throwable $e) {
            // Asegurar reactivación de FK en caso de error
            try {
                if ($driver === 'pgsql') {
                    DB::statement('SET session_replication_role = DEFAULT');
                } else {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            } catch (\Throwable) {}

            return back()->with('error', 'Error durante el reset: ' . $e->getMessage());
        }

        return back()->with('success', $msg . ' Se generó un backup automático previo.');
    }

    // ── Generador SQL puro PHP ───────────────────────────────────────────────
    private function generarSQL(): string
    {
        $driver = DB::connection()->getDriverName();
        $pdo    = DB::connection()->getPdo();
        $dbName = config('database.connections.' . $driver . '.database') ?: 'database';
        $ahora  = now()->format('d/m/Y H:i:s');

        $sql  = "-- ==============================================================\n";
        $sql .= "--  CRM Tienda Celulares — Backup Completo\n";
        $sql .= "--  Generado  : {$ahora}\n";
        $sql .= "--  Base datos: {$dbName}\n";
        $sql .= "--  Motor     : {$driver}\n";
        $sql .= "-- ==============================================================\n\n";

        if ($driver === 'pgsql') {
            $sql .= "SET session_replication_role = replica;\n\n";
        } else {
            $sql .= "SET NAMES utf8mb4;\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        }

        // PostgreSQL no tiene la función DATABASE() (es de MySQL)
        $queryTablas = $driver === 'pgsql'
            ? "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
            : "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()";

        $filas  = $pdo->query($queryTablas)->fetchAll(PDO::FETCH_NUM);
        $tablas = array_map(fn($fila) => $fila[0], $filas);

        foreach ($tablas as $tabla) {
            if (in_array($tabla, ['migrations', 'personal_access_tokens'])) continue;

            $sql .= "-- --------------------------------------------------------------\n";
            $sql .= "-- Tabla: {$tabla}\n";
            $sql .= "-- --------------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS {$tabla};\n";

            if ($driver === 'pgsql') {
                $create = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = '{$tabla}' ORDER BY ordinal_position")->fetchAll(PDO::FETCH_ASSOC);
                $sql .= "CREATE TABLE {$tabla} (\n";
                $cols = [];
                foreach ($create as $col) {
                    $cols[] = "  {$col['column_name']} " . strtoupper($col['data_type']) .
                        ($col['is_nullable'] === 'NO' ? ' NOT NULL' : '') .
                        ($col['column_default'] ? ' DEFAULT ' . $col['column_default'] : '');
                }
                $sql .= implode(",\n", $cols) . "\n);\n\n";
            } else {
                $create = $pdo->query("SHOW CREATE TABLE `{$tabla}`")->fetch(PDO::FETCH_ASSOC);
                $sql .= $create['Create Table'] . ";\n\n";
            }

            $rows = $pdo->query("SELECT * FROM {$tabla}")->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                foreach (array_chunk($rows, 500) as $chunk) {
                    $sql .= "INSERT INTO {$tabla} VALUES\n";
                    $lines = [];
                    foreach ($chunk as $row) {
                        $vals = array_map(
                            fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v),
                            array_values($row)
                        );
                        $lines[] = '(' . implode(', ', $vals) . ')';
                    }
                    $sql .= implode(",\n", $lines) . ";\n";
                }
                $sql .= "\n";
            }
        }

        if ($driver === 'pgsql') {
            $sql .= "SET session_replication_role = DEFAULT;\n";
        } else {
            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        }

        return $sql;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 2) . ' MB';
        if ($bytes >= 1_024)     return round($bytes / 1_024,     2) . ' KB';
        return $bytes . ' B';
    }
}