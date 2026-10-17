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
        // Los backups pueden crecer con 4+ tiendas: mismo límite que el
        // backup manual (BackupController::crear usa 512M/300s).
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $this->info('=== Backup Automático ===');

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $globalOk = false;
        $empresaOk = false;

        // ── 1. Backup GLOBAL (todas las empresas juntas) ─────────────
        // Va en su propio try/catch: si el global falla (p. ej. OOM),
        // NO debe impedir los respaldos por empresa (causa del aviso
        // "Sin respaldo reciente + 4 tiendas sin respaldo").
        try {
            $sql = $backupService->generarSQL();

            $nombre = 'backup_auto_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($backupDir . '/' . $nombre, $sql);

            $this->info("✓ Backup global creado: {$nombre} (" . $this->formatBytes(filesize($backupDir . '/' . $nombre)) . ")");
            $globalOk = true;
        } catch (\Throwable $e) {
            $this->error('✗ Falló el backup global: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('[backup:automatico] falló backup global: ' . $e->getMessage());
        }

        // ── 2. Backup INDIVIDUAL por cada empresa activa ─────────────
        // Siempre se intenta, aunque el global haya fallado.
        try {
            $empresaOk = $this->generarBackupsPorEmpresa($backupService, $backupDir);
        } catch (\Throwable $e) {
            $this->error('✗ Falló la fase por empresa: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('[backup:automatico] falló fase por empresa: ' . $e->getMessage());
        }

        // ── 3. Limpieza según retención (nunca debe tumbar el comando) ─
        try {
            $this->limpiarBackupsAntiguos($backupDir);
            $this->limpiarBackupsEmpresaAntiguos($backupDir);
        } catch (\Throwable $e) {
            $this->warn('⚠ Limpieza incompleta: ' . $e->getMessage());
        }

        if ($globalOk || $empresaOk) {
            return Command::SUCCESS;
        }

        $this->error('✗ No se generó ningún backup (global ni por empresa). Revisa storage/logs/laravel.log y el volumen taller_storage.');
        return Command::FAILURE;
    }

    /**
     * Genera un archivo de backup independiente para cada empresa activa.
     * Si una empresa falla, se continúa con las demás (no aborta el proceso).
     * Devuelve true si al menos una empresa se respaldó.
     */
    private function generarBackupsPorEmpresa(BackupService $backupService, string $backupDir): bool
    {
        $tenants = Tenant::where('estado', 'activo')->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->line('ℹ No hay empresas activas: se omite backup por empresa.');
            return false;
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
                \Illuminate\Support\Facades\Log::error("[backup:automatico] falló backup de '{$tenant->empresa}': " . $e->getMessage());
            }
        }

        $this->info("✓ Backups por empresa generados: {$generados}/{$tenants->count()}");

        return $generados > 0;
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