<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "cupones: " . (Schema::hasTable('cupones') ? 'SI' : 'NO') . PHP_EOL;
echo "resenas: " . (Schema::hasTable('resenas') ? 'SI' : 'NO') . PHP_EOL;
echo "recordatorios_retiro: " . (Schema::hasTable('recordatorios_retiro') ? 'SI' : 'NO') . PHP_EOL;
echo "pagina_publica_activa: " . (Schema::hasColumn('configuracion', 'pagina_publica_activa') ? 'SI' : 'NO') . PHP_EOL;
echo "slug_publico: " . (Schema::hasColumn('tenants', 'slug_publico') ? 'SI' : 'NO') . PHP_EOL;
echo "instagram: " . (Schema::hasColumn('configuracion', 'instagram') ? 'SI' : 'NO') . PHP_EOL;
echo "facebook: " . (Schema::hasColumn('configuracion', 'facebook') ? 'SI' : 'NO') . PHP_EOL;
echo "tiktok: " . (Schema::hasColumn('configuracion', 'tiktok') ? 'SI' : 'NO') . PHP_EOL;
echo "horario_atencion: " . (Schema::hasColumn('configuracion', 'horario_atencion') ? 'SI' : 'NO') . PHP_EOL;
echo "descripcion_corta: " . (Schema::hasColumn('configuracion', 'descripcion_corta') ? 'SI' : 'NO') . PHP_EOL;
echo "cupon_automatico_al_entregar: " . (Schema::hasColumn('configuracion', 'cupon_automatico_al_entregar') ? 'SI' : 'NO') . PHP_EOL;
echo "cupon_descuento_porcentaje: " . (Schema::hasColumn('configuracion', 'cupon_descuento_porcentaje') ? 'SI' : 'NO') . PHP_EOL;
echo "cupon_dias_validez: " . (Schema::hasColumn('configuracion', 'cupon_dias_validez') ? 'SI' : 'NO') . PHP_EOL;