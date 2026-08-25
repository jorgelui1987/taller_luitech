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
<title>Orden {{ $reparacion->numero_orden }} — Media Carta Horizontal</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#525659;padding:16px;display:flex;flex-direction:column;align-items:center;gap:14px}
.aviso{background:#ffd54f;color:#333;padding:8px 16px;border-radius:8px;font-size:13px;text-align:center;max-width:800px}

/* ═══ BOLETA: 27.94 cm ancho × 10.795 cm alto ═══ */
.boleta{width:279.4mm;height:107.95mm;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.45);padding:5mm 6mm;display:flex;flex-direction:column}

/* ═══ ENCABEZADO HORIZONTAL ═══ */
.encabezado{display:flex;align-items:center;justify-content:space-between;border-bottom:2.5px solid #1a1a1a;padding-bottom:2.5mm}
.logo{width:42mm;max-height:13mm;object-fit:contain}
.tienda-bloque{text-align:left;margin-left:4mm}
.tienda-nombre{font-size:14px;font-weight:800;color:#111}
.fiscal-linea{font-size:7.5px;color:#444;line-height:1.35}
.doc-bloque{text-align:right}
.doc-tipo{display:inline-block;background:#1a1a1a;color:#fff;font-size:8px;font-weight:700;letter-spacing:2px;padding:1mm 4mm;border-radius:3px}
.doc-numero{font-size:15px;font-weight:800;margin-top:.5mm;letter-spacing:.5px}

/* ═══ CUERPO EN 2 COLUMNAS ═══ */
.cuerpo{display:flex;gap:5mm;flex:1;margin-top:2.5mm}
.col-izq{flex:1.4}
.col-der{flex:1;display:flex;flex-direction:column}

.datos-grid{font-size:8px;color:#333;margin-bottom:1.5mm;line-height:1.45}
.datos-grid b{font-size:7px;text-transform:uppercase;color:#888;letter-spacing:.5px}

.seccion{font-size:7.5px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#1e3a5f;margin-bottom:1mm;border-bottom:1px solid #dde1e6;padding-bottom:.8mm}

table{width:100%;border-collapse:collapse;font-size:8.5px}
th{background:#1e3a5f;color:#fff;text-align:left;padding:1.2mm 1.5mm;font-size:7px;text-transform:uppercase;letter-spacing:.5px}
td{padding:1.2mm 1.5mm;border-bottom:.5px solid #e8eaed;vertical-align:top}
td.lbl{font-size:7px;font-weight:700;text-transform:uppercase;color:#888;width:30%}
td.val{font-weight:600}

.bx{font-size:8.5px;line-height:1.45;color:#222;word-break:break-word}

.tot-fila{display:flex;justify-content:space-between;font-size:9px;padding:.9mm 2mm;color:#333}
.tot-final{display:flex;justify-content:space-between;background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:1.8mm 3mm;border-radius:4px;margin-top:1mm}

.garantia-box{font-size:7px;color:#555;line-height:1.4;background:#fdf9ee;border:1px solid #eadfb8;border-radius:4px;padding:1.5mm 2mm;margin-top:auto}
.notas{font-size:7.5px;color:#555;margin-top:1.5mm}

/* ═══ PIE HORIZONTAL ═══ */
.pie{display:flex;justify-content:space-between;align-items:center;margin-top:2.5mm;padding-top:2mm;border-top:1px solid #dde1e6;font-size:7.5px;color:#666}
.qr{width:11mm;height:11mm}
.miniweb{font-weight:700;color:#0000EE;word-break:break-all;font-size:8px}
.firma-zona{display:flex;gap:10mm;margin-right:4mm}
.firma{text-align:center;width:32mm}
.firma .linea{border-top:1px solid #333;margin-top:5mm}
.firma img{max-width:100%;max-height:10mm;object-fit:contain}
.firma .lbl{font-size:6.5px;color:#777;margin-top:.8mm;text-transform:uppercase;letter-spacing:.5px}

.btn-print{position:fixed;bottom:24px;right:24px;background:#0070e0;color:#fff;border:none;border-radius:30px;padding:14px 28px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(0,112,224,.4)}
@media print{
 body{background:#fff;padding:0}
 .aviso,.btn-print{display:none!important}
 .boleta{box-shadow:none;width:auto;height:auto}
 @page{size:279.4mm 107.95mm;margin:0}
}
</style>
</head>
<body>

<div class="aviso">🖨️ Formato <b>MEDIA CARTA HORIZONTAL</b> (27.94 × 10.795 cm). Usa tu impresora normal. El botón azul no se imprime.</div>

<div class="boleta">

    <!-- ═══ ENCABEZADO HORIZONTAL ═══ -->
    <div class="encabezado">
        <div style="display:flex;align-items:center">
            @if($logoSrc)<img src="{{ $logoSrc }}" alt="" class="logo">@endif
            <div class="tienda-bloque">
                <div class="tienda-nombre">{{ $empresa?->nombre_tienda ?? 'CRM Celulares' }}</div>
                <div class="fiscal-linea">
                    @if($empresa?->rut_emisor ?? $empresa?->ruc){{ ($empresa?->pais ?? '') === 'CL' ? 'R.U.T.' : 'R.U.C.' }}: {{ $empresa->rut_emisor ?? $empresa->ruc }}@endif
                    @if($empresa?->giro) · {{ $empresa->giro }}@endif
                    @if($empresa?->direccion) · {{ $empresa->direccion }}@if($empresa?->comuna_ciudad){{ ', '.$empresa->comuna_ciudad }}@endif@endif
                </div>
            </div>
        </div>
        <div class="doc-bloque">
            <span class="doc-tipo">ORDEN DE SERVICIO</span>
            <div class="doc-numero">N° {{ $reparacion->numero_orden }}</div>
        </div>
    </div>

    <!-- ═══ CUERPO EN 2 COLUMNAS ═══ -->
    <div class="cuerpo">

        <div class="col-izq">
            <div class="datos-grid">
                <b>CLIENTE:</b> {{ $reparacion->cliente?->nombre_completo ?? '—' }}
                @if($reparacion->cliente?->telefono)&nbsp;&nbsp;<b>TEL:</b> {{ $reparacion->cliente->telefono }}@endif
                &nbsp;&nbsp;<b>RECEPCIÓN:</b> {{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }}
                &nbsp;&nbsp;<b>TÉCNICO:</b> {{ $reparacion->tecnico->name ?? '—' }}
            </div>

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

            <div class="seccion" style="margin-top:2mm">Falla reportada</div>
            <div class="bx">{{ $reparacion->falla_reportada ?: '—' }}</div>

            @if($reparacion->diagnostico)
            <div class="seccion" style="margin-top:2mm">Diagnóstico</div>
            <div class="bx">{{ $reparacion->diagnostico }}</div>
            @endif

            @if($reparacion->solucion)
            <div class="seccion" style="margin-top:2mm">Solución</div>
            <div class="bx">{{ $reparacion->solucion }}</div>
            @endif

            @if($reparacion->notas)
            <div class="notas"><b>Notas:</b> {{ $reparacion->notas }}</div>
            @endif
        </div>

        <div class="col-der">
            <div class="datos-grid" style="text-align:right"><b>ESTADO:</b> {{ $estadoLabel }}</div>

            @if($reparacion->presupuesto > 0 || $reparacion->costo_final > 0 || $reparacion->abono > 0 || $reparacion->total > 0)
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
            @endif

            @if($reparacion->garantia)
            <div class="garantia-box">
                <b>🛡️ GARANTÍA:</b> {{ $reparacion->dias_garantia }} días.
                @if($empresa?->terminos_garantia){{ $empresa->terminos_garantia }}@endif
            </div>
            @endif
        </div>
    </div>

    <!-- ═══ PIE HORIZONTAL ═══ -->
    <div class="pie">
        @if($urlMiniWeb)
        <div>🌐 Visita nuestra tienda online:<br><span class="miniweb">{{ $urlMiniWeb }}</span></div>
        @else
        <div></div>
        @endif
        <div style="text-align:center">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qrUrl) }}" alt="QR" class="qr">
            <div style="font-size:6.5px;font-weight:700;">Escanea para ver estado</div>
        </div>
        <div style="text-align:center">
            @if($empresa?->telefono)📞 {{ $empresa->telefono }}@endif
            @if($empresa?->instagram) · 📷 {{ $empresa->instagram }}@endif
        </div>
        <div class="firma-zona">
            <div class="firma">
                @if($firmaMostrar)
                <img src="{{ asset('storage/'.$firmaMostrar) }}" alt="Firma">
                @else
                <div class="linea"></div>
                @endif
                <div class="lbl">Firma cliente</div>
            </div>
            <div class="firma"><div class="linea"></div><div class="lbl">Sello / Recepción</div></div>
        </div>
    </div>

</div>

<button class="btn-print" onclick="window.print()">🖨️ Imprimir formato carta</button>

<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>