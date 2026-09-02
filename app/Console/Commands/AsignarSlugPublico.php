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
        // Listado completo: permite diagnosticar slugs faltantes o tenants suspendidos
        $tenants = Tenant::withoutGlobalScopes()->orderBy('id')
            ->get(['id', 'empresa', 'subdominio', 'slug_publico', 'estado']);

        $this->info('=== Estado actual de los tenants ===');
        $this->table(
            ['ID', 'Empresa', 'Subdominio', 'Slug público', 'Estado'],
            $tenants->map(fn ($t) => [
                $t->id,
                $t->empresa,
                $t->subdominio ?? '—',
                $t->slug_publico ?? 'NULL',
                $t->estado,
            ])->all()
        );

        $pendientes = $tenants->filter(fn ($t) => empty($t->slug_publico));

        if ($pendientes->isEmpty()) {
            $this->info('Todos los tenants ya tienen slug_publico.');
            return self::SUCCESS;
        }

        foreach ($pendientes as $tenant) {
            // Prioridad del slug: subdominio (ya normalizado y único) → empresa
            $base = $tenant->subdominio ?: $tenant->empresa ?: 'tienda';
            $slug = Tenant::generarSlugUnico($base);

            $tenant->update(['slug_publico' => $slug]);
            $this->info("✓ {$tenant->empresa} → slug: {$slug}");
        }

        $this->info('Slugs asignados correctamente. Verifica /pantalla/{slug}.');
        return self::SUCCESS;
    }
}
