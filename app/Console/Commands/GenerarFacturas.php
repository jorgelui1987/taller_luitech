<?php

namespace App\Console\Commands;

use App\Models\Factura;
use App\Models\PlanPrecio;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerarFacturas extends Command
{
    protected $signature = 'facturas:generar
                            {--mes= : Mes para facturar (Y-m), por defecto el mes actual}';

    protected $description = 'Genera facturas mensuales para todos los tenants activos con plan de pago';

    public function handle(): int
    {
        $this->info('=== Generación de Facturas ===');

        $periodo = $this->option('mes') ?: now()->format('Y-m');
        $fechaInicio = \Carbon\Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $this->info("Período: {$periodo}");

        try {
            // Obtener precios de planes
            $planes = PlanPrecio::getPlanesActivos();

            // Tenants activos con plan de pago
            $tenants = Tenant::where('estado', 'activo')
                ->where('plan', '!=', 'gratis')
                ->get();

            if ($tenants->isEmpty()) {
                $this->info('No hay tenants con plan de pago.');
                return Command::SUCCESS;
            }

            $generadas = 0;
            foreach ($tenants as $tenant) {
                // Verificar si ya se facturó este período
                $yaFacturado = Factura::where('tenant_id', $tenant->id)
                    ->whereBetween('fecha_emision', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
                    ->exists();

                if ($yaFacturado) {
                    continue;
                }

                $planData = $planes->get($tenant->plan);
                if (!$planData) {
                    $this->warn("✗ Tenant {$tenant->empresa}: plan '{$tenant->plan}' no encontrado");
                    continue;
                }

                $monto = (float) $planData->precio_mensual;

                Factura::create([
                    'tenant_id' => $tenant->id,
                    'numero_factura' => Factura::generarNumero(),
                    'plan' => $tenant->plan,
                    'monto' => $monto,
                    'moneda' => $planData->moneda ?? 'PEN',
                    'estado' => 'pendiente',
                    'fecha_emision' => $fechaInicio->toDateString(),
                    'fecha_vencimiento' => $fechaInicio->copy()->addDays(5)->toDateString(),
                    'notas' => "Factura mensual del plan {$tenant->plan}",
                ]);

                $this->info("✓ Factura generada para {$tenant->empresa} (Plan: {$tenant->plan}, Monto: {$planData->moneda}{$monto})");
                $generadas++;
            }

            $this->info("=== Se generaron {$generadas} facturas ===");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("✗ Error al generar facturas: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
