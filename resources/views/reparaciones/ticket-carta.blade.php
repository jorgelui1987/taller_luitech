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
<title>Orden {{ $reparacion->numero_orden }} — Formato Carta</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#525659;padding:16px;display:flex;flex-direction:column;align-items:center;gap:14px}
.aviso{background:#ffd54f;color:#333;padding:8px 16px;border-radius:8px;font-size:13px;text-align:center;max-width:760px}

/* ═══ HOJA CARTA ═══ */
.hoja{width:216mm;height:279mm;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.45);padding:8mm 10mm;display:flex;flex-direction:column}

/* ═══ TICKET = MEDIA HOJA SUPERIOR ═══ */
.ticket{height:139mm;border:1.5px solid #c9c9c9;border-radius:6px;padding:5mm 7mm;display:flex;flex-direction:column;background:#fff}
.zona-vacia{flex:1;display:flex;align-items:center;justify-content:center;color:#bbb;font-size:12px;letter-spacing:1px}

/* ═══ CONTENIDO PROFESIONAL ═══ */
.encabezado{text-align:center;border-bottom:2.5px solid #1a1a1a;padding-bottom:3mm}
.logo{width:52mm;max-height:18mm;object-fit:contain;margin:0 auto 2mm;display:block}
.tienda-nombre{font-size:15px;font-weight:800;color:#111}
.fiscal-linea{font-size:8.5px;color:#444;margin-top:.8mm}
.doc-tipo{display:inline-block;margin-top:1.5mm;background:#1a1a1a;color:#fff;font-size:9px;font-weight:700;letter-spacing:2px;padding:1.2mm 5mm;border-radius:3px}
.doc-numero{font-size:16px;font-weight:800;margin-top:1mm;letter-spacing:.5px}

.datos-grid{display:flex;justify-content:space-between;font-size:9px;color:#333;margin:2.5mm 0;padding:1.8mm 2.5mm;background:#f5f6f8;border-radius:4px}
.datos-grid div b{display:block;font-size:7.5px;text-transform:uppercase;color:#888;letter-spacing:.5px}

.seccion{font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#1e3a5f;margin:2.5mm 0 1.2mm;border-bottom:1px solid #dde1e6;padding-bottom:1mm}

table{width:100%;border-collapse:collapse;font-size:9.5px}
th{background:#1e3a5f;color:#fff;text-align:left;padding:1.5mm 2mm;font-size:8px;text-transform:uppercase;letter-spacing:.5px}
td{padding:1.4mm 2mm;border-bottom:.5px solid #e8eaed;vertical-align:top}
td.lbl{font-size:8px;font-weight:700;text-transform:uppercase;color:#888;width:22%}
td.val{font-weight:600}

.bx{font-size:9.5px;line-height:1.45;color:#222;word-break:break-word}

.totales{margin-top:auto}
.tot-fila{display:flex;justify-content:space-between;font-size:9.5px;padding:1mm 2mm;color:#333}
.tot-final{display:flex;justify-content:space-between;background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2mm 3mm;border-radius:4px;margin-top:1.2mm}

.pie{display:flex;justify-content:space-between;align-items:center;margin-top:2.5mm;padding-top:2mm;border-top:1px solid #dde1e6;font-size:8px;color:#666}
.qr{width:15mm;height:15mm}
.garantia-box{font-size:8px;color:#555;line-height:1.4;background:#fdf9ee;border:1px solid #eadfb8;border-radius:4px;padding:1.8mm 2.2mm;margin-top:2mm}
.miniweb-box{text-align:center;border:1.5px dashed #1e3a5f;border-radius:4px;padding:1.8mm;margin-top:2mm;font-size:9px;font-weight:700}
.miniweb-box .url{color:#0000EE;word-break:break-all}
.firma-zona{display:flex;gap:8mm;margin-top:2.5mm}
.firma{flex:1;text-align:center}
.firma .linea{border-top:1px solid #333;margin-top:7mm}
.firma .lbl{font-size:7.5px;color:#777;margin-top:1mm;text-transform:uppercase;letter-spacing:.5px}

.btn-print{position:fixed;bottom:24px;right:24px;background:#0070e0;color:#fff;border:none;border-radius:30px;padding:14px 28px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(0,112,224,.4)}
@media print{
 body{background:#fff;padding:0}
 .aviso,.btn-print{display:none!important}
 .hoja{box-shadow:none;width:auto;height:auto;padding:8mm}
}
</style>
</head>
<body>

<div class="aviso">🖨️ Formato <b>CARTA — media hoja</b>. Usa tu impresora normal (no la térmica). El botón azul no se imprime.</div>

<div class="hoja">

    <!-- ═══ ORDEN DE SERVICIO — MEDIA HOJA SUPERIOR ═══ -->
    <div class="ticket">
        <div class="encabezado">
            @if($logoSrc)<img src="{{ $logoSrc }}" alt="" class="logo">@endif
            <div class="tienda-nombre">{{ $empresa?->nombre_tienda ?? 'CRM Celulares' }}</div>
            <div class="fiscal-linea">
                @if($empresa?->rut_emisor ?? $empresa?->ruc){{ ($empresa?->pais ?? '') === 'CL' ? 'R.U.T.' : 'R.U.C.' }}: {{ $empresa->rut_emisor ?? $empresa->ruc }}@endif
                @if($empresa?->giro) · {{ $empresa->giro }}@endif
            </div>
            <div class="doc-tipo">ORDEN DE SERVICIO</div>
            <div class="doc-numero">N° {{ $reparacion->numero_orden }}</div>
        </div>

        <div class="datos-grid">
            <div><b>Cliente</b>{{ $reparacion->cliente?->nombre_completo ?? '—' }}</div>
            <div><b>Teléfono</b>{{ $reparacion->cliente?->telefono ?? '—' }}</div>
            <div><b>Recepción</b>{{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }}</div>
            <div><b>Estado</b>{{ $estadoLabel }}</div>
            <div><b>Técnico</b>{{ $reparacion->tecnico->name ?? '—' }}</div>
        </div>

        <div class="seccion">Equipo recibido</div>
        <table>
            <tbody>
                <tr><td class="lbl">Tipo</td><td class="val">{{ $tipoDispositivoLabel }}</td></tr>
                <tr><td class="lbl">Marca / Modelo</td><td class="val">{{ trim(($reparacion->marca ?: '—').' '.($reparacion->modelo ?: '')) }}</td></tr>
                @if($reparacion->imei)<tr><td class="lbl">IMEI</td><td class="val">{{ $reparacion->imei }}</td></tr>@endif
                @if($reparacion->color)<tr><td class="lbl">Color</td><td class="val">{{ $reparacion->color }}</td></tr>@endif
                @if($reparacion->fecha_estimada)<tr><td class="lbl">Entrega estimada</td><td class="val">{{ $reparacion->fecha_estimada->format('d/m/Y') }}</td></tr>@endif
                @if($reparacion->fecha_entrega)<tr><td class="lbl">Entregado</td><td class="val">{{ $reparacion->fecha_entrega->format('d/m/Y') }}</td></tr>@endif
            </tbody>
        </table>

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

        @if($reparacion->presupuesto > 0 || $reparacion->costo_final > 0 || $reparacion->abono > 0 || $reparacion->total > 0)
        <div class="totales">
            @if($reparacion->presupuesto > 0)
            <div class="tot-fila"><span>Presupuesto</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($reparacion->presupuesto, 2) }}</span></div>
            @endif
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

        @if($reparacion->garantia)
        <div class="garantia-box">
            <b>🛡️ GARANTÍA:</b> {{ $reparacion->dias_garantia }} días.
            @if($empresa?->terminos_garantia){{ $empresa->terminos_garantia }}@endif
        </div>
        @endif

        @if($reparacion->notas)
        <div style="font-size:8.5px;color:#555;margin-top:2mm"><b>Notas:</b> {{ $reparacion->notas }}</div>
        @endif

        <div class="pie">
            <div style="max-width:62%">
                @if($empresa?->direccion)📍 {{ $empresa->direccion }}@if($empresa?->comuna_ciudad){{ ', '.$empresa->comuna_ciudad }}@endif<br>@endif
                @if($empresa?->telefono)📞 {{ $empresa->telefono }}@endif
                @if($empresa?->instagram) · 📷 {{ $empresa->instagram }}@endif
            </div>
            <div style="text-align:center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qrUrl) }}" alt="QR" class="qr">
                <div style="font-size:7px;font-weight:700;margin-top:.5mm">Escanea para ver estado</div>
            </div>
            <div style="text-align:right;font-weight:700;font-size:11px">¡Gracias por su<br>preferencia!</div>
        </div>

        @if($urlMiniWeb)
        <div class="miniweb-box">
            🌐 Visita nuestra tienda online:<br>
            <span class="url">{{ $urlMiniWeb }}</span>
        </div>
        @endif

        <div class="firma-zona">
            <div class="firma">
                @if($firmaMostrar)
                <img src="{{ asset('storage/'.$firmaMostrar) }}" alt="Firma" style="max-width:100%;max-height:12mm;object-fit:contain">
                @else
                <div class="linea"></div>
                @endif
                <div class="lbl">Firma cliente</div>
            </div>
            <div class="firma"><div class="linea"></div><div class="lbl">Sello / Recepción</div></div>
        </div>
    </div>

    <!-- ═══ MITAD INFERIOR LIBRE ═══ -->
    <div class="zona-vacia">— mitad inferior de la hoja queda libre —</div>

</div>

<button class="btn-print" onclick="window.print()">🖨️ Imprimir formato carta</button>

<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>