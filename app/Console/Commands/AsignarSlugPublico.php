<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class AsignarSlugPublico extends Command
{
    protected $signature = 'tenant:asignar-slugs';

    protected $description = 'Asigna slug_publico a todos los tenants que no lo tienen';

    public function handle(): int
    {
        $tenants = Tenant::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNull('slug_publico')->orWhere('slug_publico', '');
            })
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('Todos los tenants ya tienen slug_publico.');
            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            // Prioridad del slug: subdominio (ya normalizado y único) → empresa
            $base = $tenant->subdominio ?: $tenant->empresa ?: 'tienda';
            $slug = Tenant::generarSlugUnico($base);

            $tenant->update(['slug_publico' => $slug]);
            $this->info("✓ {$tenant->empresa} → slug: {$slug}");
        }

        $this->info('Slugs asignados correctamente.');
        return self::SUCCESS;
    }
}
