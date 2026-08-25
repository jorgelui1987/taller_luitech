@php
    $empresa = \App\Models\Configuracion::empresa();

    // URL correcta del logo
    $logoSrc = null;
    if ($empresa && $empresa->logo) {
        $logoPath = str_replace('storage/', '', $empresa->logo);
        if (file_exists(storage_path('app/public/' . $logoPath))) {
            $logoSrc = route('storage.serve', ['path' => $logoPath]);
        } else {
            $logoSrc = asset($empresa->logo);
        }
    }

    $estadoLabel = str_replace('_', ' ', ucfirst($reparacion->estado));
    $tipoDispositivo = ['celular'=>'Celular','tablet'=>'Tablet','portatil'=>'Portátil','otros'=>'Otros'];
    $tipoDispositivoLabel = $tipoDispositivo[$reparacion->tipo_dispositivo] ?? ($reparacion->tipo_dispositivo ?? '—');
    $nombreImpuesto = ($empresa?->pais ?? '') === 'CL' ? 'IVA' : 'IGV';
    $qrUrl = route('reparaciones.public-status', $reparacion->numero_orden);

    // Una sola firma: priorizar la de entrega
    $firmaMostrar = $reparacion->firma_entrega ?: $reparacion->firma_recepcion;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden {{ $reparacion->numero_orden }} — Media carta vertical</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#525659;padding:20px;display:flex;flex-direction:column;align-items:center;gap:14px}
.aviso{background:#ffd54f;color:#333;padding:10px 18px;border-radius:8px;font-size:13px;text-align:center;max-width:700px}

/* ═══ ORDEN MEDIA CARTA VERTICAL: 10.795 cm ancho × 27.94 cm alto ═══
   Es la mitad de una hoja carta cortada a lo largo.
   Una hoja carta completa rinde para 2 órdenes → ahorras papel.
   Se deja 0.2 mm de holgura para evitar desbordes al imprimir. */
.boleta{width:107.95mm;height:279.2mm;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.45);padding:7mm 6mm;display:flex;flex-direction:column}

/* ═══ ENCABEZADO ═══ */
.encabezado{text-align:center;border-bottom:2.5px solid #1a1a1a;padding-bottom:4mm;margin-bottom:3mm}
.logo{max-width:60mm;max-height:16mm;object-fit:contain;margin:0 auto 2.5mm;display:block}
.tienda-nombre{font-size:15px;font-weight:800;color:#111}
.fiscal-linea{font-size:8px;color:#444;margin-top:.8mm;line-height:1.4}
.doc-tipo{display:inline-block;margin-top:2mm;background:#1a1a1a;color:#fff;font-size:9px;font-weight:700;letter-spacing:2px;padding:1.2mm 4mm;border-radius:3px}
.doc-numero{font-size:17px;font-weight:800;margin-top:1mm;letter-spacing:.5px}

/* ═══ DATOS ═══ */
.datos-grid{font-size:9px;color:#333;margin:3mm 0;padding:2mm 2.5mm;background:#f5f6f8;border-radius:4px}
.datos-grid div{margin-bottom:1mm}
.datos-grid div:last-child{margin-bottom:0}
.datos-grid b{display:inline-block;font-size:7.5px;text-transform:uppercase;color:#888;letter-spacing:.5px;width:24mm}

/* ═══ SECCIONES ═══ */
.seccion{font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#1e3a5f;margin:3mm 0 1.2mm;border-bottom:1px solid #dde1e6;padding-bottom:1mm}

table{width:100%;border-collapse:collapse;font-size:9px}
th{background:#1e3a5f;color:#fff;text-align:left;padding:1.5mm 1.8mm;font-size:7.5px;text-transform:uppercase;letter-spacing:.5px}
td{padding:1.5mm 1.8mm;border-bottom:.5px solid #e8eaed;vertical-align:top}
td.lbl{font-size:7.5px;font-weight:700;text-transform:uppercase;color:#888;width:34%}
td.val{font-weight:600}

.bx{font-size:9px;line-height:1.45;color:#222;word-break:break-word}

/* ═══ TOTALES ═══ */
.totales{margin-top:auto}
.tot-fila{display:flex;justify-content:space-between;font-size:9.5px;padding:1.2mm 2mm;color:#333}
.tot-final{display:flex;justify-content:space-between;background:#1e3a5f;color:#fff;font-size:14px;font-weight:800;padding:2.2mm 3mm;border-radius:4px;margin-top:1.5mm}

.garantia-box{font-size:7.5px;color:#555;line-height:1.45;background:#fdf9ee;border:1px solid #eadfb8;border-radius:4px;padding:2mm 2.2mm;margin-top:3mm}
.notas{font-size:8px;color:#555;margin-top:2mm;line-height:1.4}

/* ═══ MINI WEB ═══ */
.miniweb-box{text-align:center;border:1.5px dashed #1e3a5f;border-radius:4px;padding:2mm;margin-top:3mm;font-size:8.5px;font-weight:700}
.miniweb-box .url{color:#0000EE;word-break:break-all}

/* ═══ QR ═══ */
.qr-zona{text-align:center;margin-top:3mm}
.qr{width:16mm;height:16mm}

/* ═══ PIE ═══ */
.pie{text-align:center;margin-top:3mm;padding-top:2.5mm;border-top:1px solid #dde1e6;font-size:8px;color:#666;line-height:1.5}

/* ═══ FIRMAS ═══ */
.firma-zona{display:flex;justify-content:center;gap:8mm;margin-top:6mm}
.firma{text-align:center;width:40mm}
.firma .linea{border-top:1px solid #333;margin-top:9mm}
.firma img{max-width:100%;max-height:10mm;object-fit:contain}
.firma .lbl{font-size:7px;color:#777;margin-top:1mm;text-transform:uppercase;letter-spacing:.5px}

.btn-print{position:fixed;bottom:24px;right:24px;background:#0070e0;color:#fff;border:none;border-radius:30px;padding:14px 28px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(0,112,224,.4)}
@media print{
 body{display:block;background:#fff;padding:0;margin:0}
 .aviso,.btn-print{display:none!important}
 .boleta{box-shadow:none}
 /* Página = media carta vertical (mitad izquierda de una hoja carta).
    La otra mitad queda libre para la siguiente orden. */
 @page{size:107.95mm 279.4mm;margin:0}
}
</style>
</head>
<body>

<div class="aviso">📄 Formato <b>MEDIA CARTA VERTICAL</b> (10.795 × 27.94 cm) · <b>1 sola orden por impresión</b>, orientación vertical. Una hoja carta cortada a lo largo rinde para <b>2 órdenes</b>. El botón azul no se imprime.</div>

<div class="boleta">

    <!-- ═══ ENCABEZADO ═══ -->
    <div class="encabezado">
        @if($logoSrc)<img src="{{ $logoSrc }}" alt="" class="logo">@endif
        <div class="tienda-nombre">{{ $empresa?->nombre_tienda ?? 'CRM Celulares' }}</div>
        <div class="fiscal-linea">
            @if($empresa?->rut_emisor ?? $empresa?->ruc){{ ($empresa?->pais ?? '') === 'CL' ? 'R.U.T.' : 'R.U.C.' }}: {{ $empresa->rut_emisor ?? $empresa->ruc }}@endif
            @if($empresa?->giro)<br>{{ $empresa->giro }}@endif
            @if($empresa?->direccion)<br>{{ $empresa->direccion }} @if($empresa?->comuna_ciudad){{ ', '.$empresa->comuna_ciudad }}@endif @endif
        </div>
        <div class="doc-tipo">ORDEN DE SERVICIO</div>
        <div class="doc-numero">N° {{ $reparacion->numero_orden }}</div>
    </div>

    <!-- ═══ DATOS ═══ -->
    <div class="datos-grid">
        <div><b>Cliente:</b> {{ $reparacion->cliente?->nombre_completo ?? '—' }}</div>
        @if($reparacion->cliente?->telefono)<div><b>Teléfono:</b> {{ $reparacion->cliente->telefono }}</div>@endif
        <div><b>Recepción:</b> {{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }}</div>
        <div><b>Técnico:</b> {{ $reparacion->tecnico->name ?? '—' }}</div>
        <div><b>Estado:</b> {{ $estadoLabel }}</div>
    </div>

    <!-- ═══ EQUIPO ═══ -->
    <div class="seccion">Equipo recibido</div>
    <table>
        <tbody>
            <tr><td class="lbl">Tipo</td><td>{{ $tipoDispositivoLabel }}</td></tr>
            <tr><td class="lbl">Marca / Modelo</td><td>{{ trim(($reparacion->marca ?: '—').' '.($reparacion->modelo ?: '')) }}</td></tr>
            @if($reparacion->imei)<tr><td class="lbl">IMEI</td><td>{{ $reparacion->imei }}</td></tr>@endif
            @if($reparacion->color)<tr><td class="lbl">Color</td><td>{{ $reparacion->color }}</td></tr>@endif
            @if($reparacion->fecha_estimada)<tr><td class="lbl">Entrega estimada</td><td>{{ $reparacion->fecha_estimada->format('d/m/Y') }}</td></tr>@endif
            @if($reparacion->fecha_entrega)<tr><td class="lbl">Entregado</td><td>{{ $reparacion->fecha_entrega->format('d/m/Y') }}</td></tr>@endif
        </tbody>
    </table>

    <!-- ═══ FALLA / DIAGNÓSTICO / SOLUCIÓN ═══ -->
    <div class="seccion">Falla reportada</div>
    <div class="bx">{{ $reparacion->falla_reportada ?: '—' }}</div>

    @if($reparacion->diagnostico)
    <div class="seccion">Diagnóstico</div>
    <div class="bx">{{ $reparacion->diagnostico }}</div>
    @endif

    @if($reparacion->solucion)
    <div class="seccion">Solución</div>
    <div class="bx">{{ $reparacion->solucion }}</div>
    @endif

    @if($reparacion->notas)
    <div class="notas"><b>Notas:</b> {{ $reparacion->notas }}</div>
    @endif

    <!-- ═══ TOTALES ═══ -->
    @if($reparacion->presupuesto > 0 || $reparacion->costo_final > 0 || $reparacion->abono > 0 || $reparacion->total > 0)
    <div class="totales">
        <div class="tot-fila"><span>Presupuesto</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($reparacion->presupuesto, 2) }}</span></div>
        @if($reparacion->costo_final > 0)
        <div class="tot-fila"><span>Costo final</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($reparacion->costo_final, 2) }}</span></div>
        @endif
        @if($reparacion->abono > 0)
        <div class="tot-fila"><span>Abono</span><span>-{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($reparacion->abono, 2) }}</span></div>
        @endif
        @if($reparacion->impuesto > 0)
        <div class="tot-fila"><span>{{ $nombreImpuesto }} ({{ $empresa?->igv ?? 18 }}%)</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($reparacion->impuesto, 2) }}</span></div>
        @endif
        @if($reparacion->total > 0)
        <div class="tot-final"><span>TOTAL A PAGAR</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($reparacion->total, 2) }}</span></div>
        @endif
    </div>
    @endif

    <!-- ═══ GARANTÍA ═══ -->
    @if($reparacion->garantia)
    <div class="garantia-box">
        <b>🛡️ GARANTÍA:</b> {{ $reparacion->dias_garantia }} días.
        @if($empresa?->terminos_garantia){{ $empresa->terminos_garantia }}@endif
    </div>
    @endif

    <!-- ═══ QR ESTADO ═══ -->
    <div class="qr-zona">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qrUrl) }}" alt="QR" class="qr">
        <div style="font-size:7.5px;font-weight:700;">Escanea para ver el estado de tu reparación</div>
    </div>

    <!-- ═══ MINI WEB ═══ -->
    @if($urlMiniWeb)
    <div class="miniweb-box">
        🌐 Visita nuestra tienda online:<br>
        <span class="url">{{ $urlMiniWeb }}</span>
    </div>
    @endif

    <!-- ═══ PIE ═══ -->
    <div class="pie">
        @if($empresa?->telefono)📞 {{ $empresa->telefono }}@endif
        @if($empresa?->instagram) · 📷 {{ $empresa->instagram }}@endif
        <br><b style="font-size:9.5px">¡Gracias por su preferencia!</b>
    </div>

    <!-- ═══ FIRMAS ═══ -->
    <div class="firma-zona">
        <div class="firma">
            @if($firmaMostrar)
            <img src="{{ asset('storage/'.$firmaMostrar) }}" alt="Firma">
            @else
            <div class="linea"></div>
            @endif
            <div class="lbl">Firma cliente</div>
        </div>
        <div class="firma">
            <div class="linea"></div>
            <div class="lbl">Sello / Recepción</div>
        </div>
    </div>

</div>

<button class="btn-print" onclick="window.print()">🖨️ Imprimir orden (media carta vertical)</button>

<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>