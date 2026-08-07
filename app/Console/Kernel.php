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
        // Backup automático diario a las 2:00 AM
        $schedule->command('backup:automatico --retencion=7')->dailyAt('02:00');

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