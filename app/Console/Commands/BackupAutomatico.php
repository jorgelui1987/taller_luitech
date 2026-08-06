<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupAutomatico extends Command
{
    protected $signature = 'backup:automatico
                            {--retencion=7 : Días a conservar los backups}';

    protected $description = 'Genera un backup automático de la base de datos y limpia backups antiguos';

    public function handle(): int
    {
        $this->info('=== Backup Automático ===');

        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $driver = DB::connection()->getDriverName();
            $pdo = DB::connection()->getPdo();
            $dbName = config('database.connections.' . $driver . '.database') ?: 'database';
            $ahora = now()->format('Y-m-d_H-i-s');

            $sql = "-- ==============================================================\n";
            $sql .= "--  CRM Tienda Celulares — Backup Automático\n";
            $sql .= "--  Generado  : " . now()->format('d/m/Y H:i:s') . "\n";
            $sql .= "--  Base datos: {$dbName}\n";
            $sql .= "--  Motor     : {$driver}\n";
            $sql .= "-- ==============================================================\n\n";

            if ($driver === 'pgsql') {
                $sql .= "SET session_replication_role = replica;\n\n";
            } else {
                $sql .= "SET NAMES utf8mb4;\n";
                $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            }

            $queryTablas = $driver === 'pgsql'
                ? "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
                : "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()";

            $filas = $pdo->query($queryTablas)->fetchAll(\PDO::FETCH_NUM);
            $tablas = array_map(fn($fila) => $fila[0], $filas);

            foreach ($tablas as $tabla) {
                if (in_array($tabla, ['migrations', 'personal_access_tokens'])) continue;

                $sql .= "-- --------------------------------------------------------------\n";
                $sql .= "-- Tabla: {$tabla}\n";
                $sql .= "-- --------------------------------------------------------------\n";
                $sql .= "DROP TABLE IF EXISTS {$tabla};\n";

                if ($driver === 'pgsql') {
                    $create = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = '{$tabla}' ORDER BY ordinal_position")->fetchAll(\PDO::FETCH_ASSOC);
                    $sql .= "CREATE TABLE {$tabla} (\n";
                    $cols = [];
                    foreach ($create as $col) {
                        $cols[] = "  {$col['column_name']} " . strtoupper($col['data_type']) .
                            ($col['is_nullable'] === 'NO' ? ' NOT NULL' : '') .
                            ($col['column_default'] ? ' DEFAULT ' . $col['column_default'] : '');
                    }
                    $sql .= implode(",\n", $cols) . "\n);\n\n";
                } else {
                    $create = $pdo->query("SHOW CREATE TABLE `{$tabla}`")->fetch(\PDO::FETCH_ASSOC);
                    $sql .= $create['Create Table'] . ";\n\n";
                }

                $rows = $pdo->query("SELECT * FROM {$tabla}")->fetchAll(\PDO::FETCH_ASSOC);

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

            $nombre = 'backup_auto_' . $ahora . '.sql';
            file_put_contents($backupDir . '/' . $nombre, $sql);

            $this->info("✓ Backup creado: {$nombre} (" . $this->formatBytes(filesize($backupDir . '/' . $nombre)) . ")");

            // Limpiar backups antiguos
            $retencion = (int) $this->option('retencion');
            $archivos = glob($backupDir . '/backup_auto_*.sql') ?: [];
            $corte = Carbon::now()->subDays($retencion);

            $eliminados = 0;
            foreach ($archivos as $archivo) {
                $fecha = Carbon::createFromTimestamp(filemtime($archivo));
                if ($fecha->lt($corte)) {
                    unlink($archivo);
                    $eliminados++;
                }
            }

            if ($eliminados > 0) {
                $this->info("✓ Eliminados {$eliminados} backups antiguos (retención: {$retencion} días)");
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("✗ Error al generar backup: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 2) . ' MB';
        if ($bytes >= 1_024)     return round($bytes / 1_024, 2) . ' KB';
        return $bytes . ' B';
    }
}