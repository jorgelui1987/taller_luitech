<?php
/**
 * Smoke test del portal público (consola): php check_portal.php
 * Verifica que las vistas del portal público rendericen sin errores.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\PublicReparacionController;
use App\Models\Reparacion;

function renderView($r): string
{
    return $r instanceof \Illuminate\View\View ? $r->render() : (string) $r;
}

$c = app(PublicReparacionController::class);

// 1) Búsqueda sin código → página Consulta Express
$html1 = renderView($c->status(null));
echo '[1] /estado (busqueda) render: ' . strlen($html1) . ' chars '
    . (strpos($html1, 'Consulta Express') !== false ? 'OK' : 'FALLO') . PHP_EOL;

// 2) Estado de una orden real
$rep = Reparacion::withoutGlobalScopes()->orderBy('id')->first();
if ($rep) {
    $html2 = renderView($c->status($rep->numero_orden));
    echo '[2] /r/' . $rep->numero_orden . ' render: ' . strlen($html2) . ' chars '
        . (strpos($html2, 'lp-timeline') !== false ? 'OK (timeline presente)' : 'FALLO (sin timeline)') . PHP_EOL;
} else {
    echo "[2] Sin reparaciones en BD (skip)" . PHP_EOL;
}

// 3) Código inexistente → mensaje amigable (sin 404)
$html3 = renderView($c->status('RPT-999999'));
echo '[3] Codigo inexistente: '
    . (strpos($html3, 'no fue encontrada') !== false ? 'OK' : 'FALLO') . PHP_EOL;

// 4) Normalización de códigos ("1024" -> "RPT-001024")
$metodo = new ReflectionMethod(PublicReparacionController::class, 'normalizarNumeroOrden');
$metodo->setAccessible(true);
$norm = $metodo->invoke($c, '1024');
echo '[4] Normalizar "1024": ' . $norm . ($norm === 'RPT-001024' ? ' OK' : ' FALLO') . PHP_EOL;

// 5) JSON del modo TV
$d = $c->pantallaData(\Illuminate\Http\Request::create('/pantalla/data'))->getData(true);
echo '[5] pantallaData: listos=' . count($d['listos']) . ' proceso=' . count($d['proceso'])
    . ' tienda=' . (($d['tienda']['nombre'] ?? '') ?: 'n/d') . PHP_EOL;

// 6) Vista TV
$html5 = renderView($c->pantalla(\Illuminate\Http\Request::create('/pantalla')));
echo '[6] /pantalla render: ' . strlen($html5) . ' chars '
    . (strpos($html5, 'tv-app') !== false ? 'OK' : 'FALLO') . PHP_EOL;

// 7) Landing pública (con y sin empresa para probar fallbacks)
$html6 = renderView(view('landing', ['empresa' => null]));
echo '[7] landing render (sin empresa): ' . strlen($html6) . ' chars '
    . (strpos($html6, 'lp-hero') !== false ? 'OK' : 'FALLO') . PHP_EOL;

echo 'SMOKE TEST COMPLETO' . PHP_EOL;
