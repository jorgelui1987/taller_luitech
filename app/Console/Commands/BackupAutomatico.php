<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class BackupAutomatico extends Command
{
    protected $signature = 'backup:automatico
                            {--retencion=7 : Días a conservar los backups}';

    protected $description = 'Genera un backup automático de la base de datos y limpia backups antiguos';

    public function handle(BackupService $backupService): int
    {
        $this->info('=== Backup Automático ===');

        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $sql = $backupService->generarSQL();

            $nombre = 'backup_auto_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($backupDir . '/' . $nombre, $sql);

            $this->info("✓ Backup creado: {$nombre} (" . $this->formatBytes(filesize($backupDir . '/' . $nombre)) . ")");

            $this->limpiarBackupsAntiguos($backupDir);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("✗ Error al generar backup: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function limpiarBackupsAntiguos(string $backupDir): void
    {
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
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }
        if ($bytes >= 1_024) {
            return round($bytes / 1_024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}