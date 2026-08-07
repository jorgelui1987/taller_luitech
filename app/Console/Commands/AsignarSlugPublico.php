<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AsignarSlugPublico extends Command
{
    protected $signature = 'tenant:asignar-slugs';

    protected $description = 'Asigna slug_publico a todos los tenants que no lo tienen';

    public function handle(): int
    {
        $tenants = Tenant::whereNull('slug_publico')->get();

        if ($tenants->isEmpty()) {
            $this->info('Todos los tenants ya tienen slug_publico.');
            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $base = Str::slug($tenant->empresa ?? $tenant->subdominio ?? 'tienda');
            $slug = $base;
            $contador = 1;

            // Asegurar unicidad
            while (Tenant::where('slug_publico', $slug)->exists()) {
                $slug = $base . '-' . $contador;
                $contador++;
            }

            $tenant->update(['slug_publico' => $slug]);
            $this->info("✓ {$tenant->empresa} → slug: {$slug}");
        }

        $this->info('Slugs asignados correctamente.');
        return self::SUCCESS;
    }
}
