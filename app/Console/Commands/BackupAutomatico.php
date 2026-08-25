<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BackupAutomatico extends Command
{
    protected $signature = 'backup:automatico
                            {--retencion=7 : Días a conservar los backups globales}
                            {--retencion-empresa=30 : Días a conservar los backups por empresa}';

    protected $description = 'Genera backup global + uno por cada empresa, y limpia los antiguos';

    public function handle(BackupService $backupService): int
    {
        $this->info('=== Backup Automático ===');

        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // ── 1. Backup GLOBAL (todas las empresas juntas) ─────────────
            $sql = $backupService->generarSQL();

            $nombre = 'backup_auto_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($backupDir . '/' . $nombre, $sql);

            $this->info("✓ Backup global creado: {$nombre} (" . $this->formatBytes(filesize($backupDir . '/' . $nombre)) . ")");

            // ── 2. Backup INDIVIDUAL por cada empresa activa ─────────────
            $this->generarBackupsPorEmpresa($backupService, $backupDir);

            // ── 3. Limpieza según retención ──────────────────────────────
            $this->limpiarBackupsAntiguos($backupDir);
            $this->limpiarBackupsEmpresaAntiguos($backupDir);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("✗ Error al generar backup: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Genera un archivo de backup independiente para cada empresa activa.
     * Si una empresa falla, se continúa con las demás (no aborta el proceso).
     */
    private function generarBackupsPorEmpresa(BackupService $backupService, string $backupDir): void
    {
        $tenants = Tenant::where('estado', 'activo')->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->line('ℹ No hay empresas activas: se omite backup por empresa.');
            return;
        }

        $generados = 0;
        foreach ($tenants as $tenant) {
            try {
                $sql = $backupService->generarSQLPorTenant($tenant->id);
                $slug = Str::slug($tenant->empresa) ?: ('tenant-' . $tenant->id);
                $nombre = 'backup_empresa-' . $slug . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
                file_put_contents($backupDir . '/' . $nombre, $sql);
                $generados++;
                $this->line("  ✓ {$nombre} (" . $this->formatBytes(filesize($backupDir . '/' . $nombre)) . ")");
            } catch (\Throwable $e) {
                $this->warn("  ⚠ Falló backup de '{$tenant->empresa}': " . $e->getMessage());
            }
        }

        $this->info("✓ Backups por empresa generados: {$generados}/{$tenants->count()}");
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
            $this->info("✓ Eliminados {$eliminados} backups globales antiguos (retención: {$retencion} días)");
        }
    }

    /**
     * Limpia los backups individuales por empresa según su retención propia.
     */
    private function limpiarBackupsEmpresaAntiguos(string $backupDir): void
    {
        $retencion = (int) $this->option('retencion-empresa');
        $archivos = glob($backupDir . '/backup_empresa-*.sql') ?: [];
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
            $this->info("✓ Eliminados {$eliminados} backups por empresa antiguos (retención: {$retencion} días)");
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