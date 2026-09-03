<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Throwable;

class DiagnosticoLanding extends Command
{
    protected $signature = 'diagnostico:landing {ruta=/ : Ruta pública a diagnosticar}';

    protected $description = 'Diagnostica un error 500 en una ruta pública: ejecuta la petición, muestra la excepción completa, migraciones y el final del log';

    public function handle(): int
    {
        $ruta = $this->argument('ruta');

        // ── Migraciones pendientes ────────────────────────────────────────
        $this->info('=== Migraciones (pendientes = problema) ===');
        try {
            \Artisan::call('migrate:status');
            $salida = trim(\Artisan::output());
            $lineas = explode("\n", $salida);
            foreach (array_slice($lineas, -8) as $linea) {
                $this->line($linea);
            }
            if (str_contains($salida, 'Pending')) {
                $this->warn('>>> HAY MIGRACIONES PENDIENTES. Ejecuta: php artisan migrate --force');
            }
        } catch (Throwable $e) {
            $this->error('No se pudo consultar migraciones: ' . $e->getMessage());
        }

        // ── Ejecutar la ruta y capturar la excepción real ─────────────────
        $this->info("=== Petición a {$ruta} ===");
        try {
            $kernel = $this->laravel->make(Kernel::class);
            $response = $kernel->handle(Request::create($ruta, 'GET'));

            $this->line('STATUS: ' . $response->getStatusCode());

            if ($response->getStatusCode() >= 400 && isset($response->exception)) {
                $e = $response->exception;
                $this->error(get_class($e) . ': ' . $e->getMessage());
                $this->line('Archivo: ' . $e->getFile() . ':' . $e->getLine());
                $this->info('--- Trace (12) ---');
                foreach (array_slice($e->getTrace(), 0, 12) as $i => $t) {
                    $this->line('#' . $i . ' ' . ($t['file'] ?? '[internal]') . ':' . ($t['line'] ?? '-') . '  ' . ($t['class'] ?? '') . ($t['class'] ?? '' ? '::' : '') . ($t['function'] ?? ''));
                }
            }
        } catch (Throwable $e) {
            $this->error(get_class($e) . ': ' . $e->getMessage());
            $this->line('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            $this->info('--- Trace (12) ---');
            foreach (array_slice($e->getTrace(), 0, 12) as $i => $t) {
                $this->line('#' . $i . ' ' . ($t['file'] ?? '[internal]') . ':' . ($t['line'] ?? '-') . '  ' . ($t['class'] ?? '') . ($t['class'] ?? '' ? '::' : '') . ($t['function'] ?? ''));
            }
        }

        // ── Final del log de Laravel ──────────────────────────────────────
        $this->info('=== Últimas 25 líneas de storage/logs/laravel.log ===');
        $logPath = storage_path('logs/laravel.log');
        if (is_file($logPath)) {
            $lineas = file($logPath, FILE_IGNORE_NEW_LINES);
            foreach (array_slice($lineas, -25) as $linea) {
                $this->line($linea);
            }
        } else {
            $this->line('(sin archivo de log)');
        }

        return self::SUCCESS;
    }
}
