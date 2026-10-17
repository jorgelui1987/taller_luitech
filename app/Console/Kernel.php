<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Backup automático diario a las 2:00 AM (hora del servidor).
        // Sin ->withoutOverlapping: si el VPS se reinicia a las 2AM, el
        // mutex viejo podía bloquear el backup del día siguiente.
        // El log va a storage/app/schedule-backup.log (DENTRO del volumen
        // persistente) para poder diagnosticar fallos del cron en Dokploy.
        $schedule->command('backup:automatico --retencion=7')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('app/schedule-backup.log'));

        // Generar facturas mensuales el primer día de cada mes a las 5:00 AM
        $schedule->command('facturas:generar')->monthlyOn(1, '05:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
