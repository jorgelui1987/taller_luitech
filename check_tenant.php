<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;

$tenant = Tenant::where('slug_publico', 'luitech')->first();
if ($tenant) {
    echo "Tenant encontrado: {$tenant->empresa} (ID: {$tenant->id}, Estado: {$tenant->estado})" . PHP_EOL;
} else {
    echo "Tenant NO encontrado" . PHP_EOL;
}

// Verificar configuración
$config = \App\Models\Configuracion::withoutGlobalScopes()->where('tenant_id', $tenant?->id)->first();
if ($config) {
    echo "Config encontrada: pagina_publica_activa = " . ($config->pagina_publica_activa ? 'SI' : 'NO') . PHP_EOL;
} else {
    echo "Config NO encontrada" . PHP_EOL;
}