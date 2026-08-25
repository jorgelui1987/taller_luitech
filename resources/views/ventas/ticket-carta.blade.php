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

    $nombreImpuesto = ($empresa?->pais ?? '') === 'CL' ? 'IVA' : 'IGV';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Boleta {{ $venta->numero_venta }} — Media carta vertical</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#525659;padding:20px;display:flex;flex-direction:column;align-items:center;gap:14px}
.aviso{background:#ffd54f;color:#333;padding:10px 18px;border-radius:8px;font-size:13px;text-align:center;max-width:700px}

/* ═══ BOLETA MEDIA CARTA VERTICAL: 10.795 cm ancho × 27.94 cm alto ═══
   Es la mitad de una hoja carta cortada a lo largo.
   Una hoja carta completa rinde para 2 boletas → ahorras papel.
   Se deja 0.2 mm de holgura para evitar desbordes al imprimir. */
.boleta{width:107.95mm;height:279.2mm;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.45);padding:7mm 6mm;display:flex;flex-direction:column}

/* ═══ ENCABEZADO ═══ */
.encabezado{text-align:center;border-bottom:2.5px solid #1a1a1a;padding-bottom:4mm;margin-bottom:3mm}
.logo{max-width:60mm;max-height:16mm;object-fit:contain;margin:0 auto 2.5mm;display:block}
.tienda-nombre{font-size:15px;font-weight:800;color:#111}
.fiscal-linea{font-size:8px;color:#444;margin-top:.8mm;line-height:1.4}
.doc-tipo{display:inline-block;margin-top:2mm;background:#1a1a1a;color:#fff;font-size:9px;font-weight:700;letter-spacing:2px;padding:1.2mm 4mm;border-radius:3px}
.doc-numero{font-size:17px;font-weight:800;margin-top:1mm;letter-spacing:.5px}
.cancelada{font-size:11px;font-weight:800;border:2px solid #000;padding:1mm 3mm;margin-top:1.5mm;display:inline-block;letter-spacing:1px}

/* ═══ DATOS ═══ */
.datos-grid{font-size:9px;color:#333;margin:3mm 0;padding:2mm 2.5mm;background:#f5f6f8;border-radius:4px}
.datos-grid div{margin-bottom:1mm}
.datos-grid div:last-child{margin-bottom:0}
.datos-grid b{display:inline-block;font-size:7.5px;text-transform:uppercase;color:#888;letter-spacing:.5px;width:22mm}

/* ═══ SECCIONES ═══ */
.seccion{font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#1e3a5f;margin:3mm 0 1.2mm;border-bottom:1px solid #dde1e6;padding-bottom:1mm}

table{width:100%;border-collapse:collapse;font-size:9px}
th{background:#1e3a5f;color:#fff;text-align:left;padding:1.5mm 1.8mm;font-size:7.5px;text-transform:uppercase;letter-spacing:.5px}
th.c,td.c{text-align:center} th.p,td.p{text-align:right}
td{padding:1.5mm 1.8mm;border-bottom:.5px solid #e8eaed;vertical-align:top}
tr:nth-child(even) td{background:#fafbfc}
.sub{color:#888;font-size:7.5px}

/* ═══ TOTALES ═══ */
.totales{margin-top:auto}
.tot-fila{display:flex;justify-content:space-between;font-size:9.5px;padding:1.2mm 2mm;color:#333}
.tot-final{display:flex;justify-content:space-between;background:#1e3a5f;color:#fff;font-size:14px;font-weight:800;padding:2.2mm 3mm;border-radius:4px;margin-top:1.5mm}

.garantia-box{font-size:7.5px;color:#555;line-height:1.45;background:#fdf9ee;border:1px solid #eadfb8;border-radius:4px;padding:2mm 2.2mm;margin-top:3mm}
.notas{font-size:8px;color:#555;margin-top:2mm;line-height:1.4}

/* ═══ MINI WEB ═══ */
.miniweb-box{text-align:center;border:1.5px dashed #1e3a5f;border-radius:4px;padding:2mm;margin-top:3mm;font-size:8.5px;font-weight:700}
.miniweb-box .url{color:#0000EE;word-break:break-all}

/* ═══ PIE ═══ */
.pie{text-align:center;margin-top:3mm;padding-top:2.5mm;border-top:1px solid #dde1e6;font-size:8px;color:#666;line-height:1.5}

/* ═══ FIRMAS ═══ */
.firma-zona{display:flex;justify-content:center;gap:8mm;margin-top:6mm}
.firma{text-align:center;width:40mm}
.firma .linea{border-top:1px solid #333;margin-top:9mm}
.firma .lbl{font-size:7px;color:#777;margin-top:1mm;text-transform:uppercase;letter-spacing:.5px}

.btn-print{position:fixed;bottom:24px;right:24px;background:#0070e0;color:#fff;border:none;border-radius:30px;padding:14px 28px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(0,112,224,.4)}
@media print{
 body{display:block;background:#fff;padding:0;margin:0}
 .aviso,.btn-print{display:none!important}
 .boleta{box-shadow:none}
 /* Página = media carta vertical (mitad izquierda de una hoja carta).
    La otra mitad queda libre para la siguiente boleta. */
 @page{size:107.95mm 279.4mm;margin:0}
}
</style>
</head>
<body>

<div class="aviso">📄 Formato <b>MEDIA CARTA VERTICAL</b> (10.795 × 27.94 cm) · <b>1 sola boleta por impresión</b>, orientación vertical. Una hoja carta cortada a lo largo rinde para <b>2 boletas</b>. El botón azul no se imprime.</div>

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
        <div class="doc-tipo">BOLETA DE VENTA</div>
        <div class="doc-numero">N° {{ $venta->numero_venta }}</div>
        @if($venta->estado === 'cancelada')<span class="cancelada">*** CANCELADA ***</span>@endif
    </div>

    <!-- ═══ DATOS ═══ -->
    <div class="datos-grid">
        <div><b>Cliente:</b> {{ $venta->cliente?->nombre_completo ?? 'VENTA GENERAL' }}</div>
        <div><b>Fecha:</b> {{ $venta->fecha_venta?->format('d/m/Y H:i') }}</div>
        <div><b>Pago:</b> {{ ucfirst($venta->metodo_pago) }}</div>
        <div><b>Vendedor:</b> {{ $venta->vendedor->name ?? '—' }}</div>
    </div>

    <!-- ═══ PRODUCTOS ═══ -->
    <div class="seccion">Detalle de productos</div>
    <table>
        <thead><tr><th>Producto</th><th class="c">Cant.</th><th class="p">Subtotal</th></tr></thead>
        <tbody>
            @forelse($venta->detalles as $det)
            <tr>
                <td>
                    {{ $det->producto->nombre ?? '—' }}
                    @if($det->producto && $det->producto->marca)<br><span class="sub">{{ $det->producto->marca->nombre }}</span>@endif
                    @if($det->imei_vendido)<br><span class="sub">IMEI: {{ $det->imei_vendido }}</span>@endif
                </td>
                <td class="c">{{ $det->cantidad }}</td>
                <td class="p">{{ number_format($det->subtotal, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#999">Sin productos</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($venta->notas)
    <div class="notas"><b>Notas:</b> {{ $venta->notas }}</div>
    @endif

    <!-- ═══ TOTALES ═══ -->
    <div class="totales">
        <div class="tot-fila"><span>{{ ($empresa?->pais ?? '') === 'CL' ? 'Neto' : 'Subtotal' }}</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($venta->subtotal - $venta->impuesto, 2) }}</span></div>
        @if($venta->descuento > 0)
        <div class="tot-fila"><span>Descuento</span><span>-{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($venta->descuento, 2) }}</span></div>
        @endif
        <div class="tot-fila"><span>{{ $nombreImpuesto }} ({{ $empresa?->igv ?? 18 }}%)</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($venta->impuesto, 2) }}</span></div>
        @if(!is_null($venta->monto_recibido))
        <div class="tot-fila"><span>Efectivo recibido</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($venta->monto_recibido, 2) }}</span></div>
        @endif
        @if(!is_null($venta->vuelto))
        <div class="tot-fila" style="font-weight:800"><span>VUELTO</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($venta->vuelto, 2) }}</span></div>
        @endif
        <div class="tot-final"><span>TOTAL</span><span>{{ $empresa?->simbolo_moneda ?? '$' }} {{ number_format($venta->total, 2) }}</span></div>
    </div>

    <!-- ═══ GARANTÍA ═══ -->
    @if($empresa?->terminos_garantia)
    <div class="garantia-box">
        <b>🛡️ GARANTÍA:</b> {{ $empresa->terminos_garantia }}
    </div>
    @endif

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
        @if($empresa?->horario_atencion)<br>🕘 {{ $empresa->horario_atencion }}@endif
        <br><b style="font-size:9.5px">¡Gracias por su preferencia!</b>
    </div>

    <!-- ═══ FIRMAS ═══ -->
    <div class="firma-zona">
        <div class="firma">
            <div class="linea"></div>
            <div class="lbl">Firma cliente</div>
        </div>
        <div class="firma">
            <div class="linea"></div>
            <div class="lbl">Sello / Vendedor</div>
        </div>
    </div>

</div>

<button class="btn-print" onclick="window.print()">🖨️ Imprimir boleta (media carta vertical)</button>

<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>