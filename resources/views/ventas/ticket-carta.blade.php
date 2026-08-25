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
<title>Boleta {{ $venta->numero_venta }} — Media Carta Horizontal</title>
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
.cancelada{font-size:11px;font-weight:800;border:2px solid #000;padding:1mm 3mm;margin-top:1mm;display:inline-block;letter-spacing:1px}

/* ═══ CUERPO EN 2 COLUMNAS ═══ */
.cuerpo{display:flex;gap:5mm;flex:1;margin-top:2.5mm}
.col-productos{flex:1.6}
.col-totales{flex:1;display:flex;flex-direction:column}

.datos-grid{font-size:8px;color:#333;margin-bottom:1.5mm;line-height:1.45}
.datos-grid b{font-size:7px;text-transform:uppercase;color:#888;letter-spacing:.5px}

.seccion{font-size:7.5px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#1e3a5f;margin-bottom:1mm;border-bottom:1px solid #dde1e6;padding-bottom:.8mm}

table{width:100%;border-collapse:collapse;font-size:8.5px}
th{background:#1e3a5f;color:#fff;text-align:left;padding:1.2mm 1.5mm;font-size:7px;text-transform:uppercase;letter-spacing:.5px}
th.c,td.c{text-align:center} th.p,td.p{text-align:right}
td{padding:1.2mm 1.5mm;border-bottom:.5px solid #e8eaed;vertical-align:top}
tr:nth-child(even) td{background:#fafbfc}
.sub{color:#888;font-size:7px}

.tot-fila{display:flex;justify-content:space-between;font-size:9px;padding:.9mm 2mm;color:#333}
.tot-final{display:flex;justify-content:space-between;background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:1.8mm 3mm;border-radius:4px;margin-top:1mm}

.garantia-box{font-size:7px;color:#555;line-height:1.4;background:#fdf9ee;border:1px solid #eadfb8;border-radius:4px;padding:1.5mm 2mm;margin-top:auto}
.notas{font-size:7.5px;color:#555;margin-top:1.5mm}

/* ═══ PIE HORIZONTAL ═══ */
.pie{display:flex;justify-content:space-between;align-items:center;margin-top:2.5mm;padding-top:2mm;border-top:1px solid #dde1e6;font-size:7.5px;color:#666}
.miniweb{font-weight:700;color:#0000EE;word-break:break-all;font-size:8px}
.firma-zona{display:flex;gap:10mm;margin-right:4mm}
.firma{text-align:center;width:32mm}
.firma .linea{border-top:1px solid #333;margin-top:5mm}
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
                    @if($empresa?->direccion) · {{ $empresa->direccion }} @if($empresa?->comuna_ciudad){{ ', '.$empresa->comuna_ciudad }} @endif @endif
                </div>
            </div>
        </div>
        <div class="doc-bloque">
            <span class="doc-tipo">BOLETA DE VENTA</span>
            <div class="doc-numero">N° {{ $venta->numero_venta }}</div>
            @if($venta->estado === 'cancelada')<span class="cancelada">*** CANCELADA ***</span>@endif
        </div>
    </div>

    <!-- ═══ CUERPO EN 2 COLUMNAS ═══ -->
    <div class="cuerpo">

        <div class="col-productos">
            <div class="datos-grid">
                <b>CLIENTE:</b> {{ $venta->cliente?->nombre_completo ?? 'VENTA GENERAL' }} &nbsp;&nbsp;
                <b>FECHA:</b> {{ $venta->fecha_venta?->format('d/m/Y H:i') }} &nbsp;&nbsp;
                <b>PAGO:</b> {{ ucfirst($venta->metodo_pago) }} &nbsp;&nbsp;
                <b>VENDEDOR:</b> {{ $venta->vendedor->name ?? '—' }}
            </div>

            <div class="seccion">Detalle de productos</div>
            <table>
                <thead><tr><th>Producto</th><th class="c">Cant.</th><th class="p">P. Unit.</th><th class="p">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($venta->detalles as $det)
                    <tr>
                        <td>
                            {{ $det->producto->nombre ?? '—' }}
                            @if($det->producto && $det->producto->marca)<span class="sub">({{ $det->producto->marca->nombre }})</span>@endif
                            @if($det->imei_vendido)<br><span class="sub">IMEI: {{ $det->imei_vendido }}</span>@endif
                        </td>
                        <td class="c">{{ $det->cantidad }}</td>
                        <td class="p">{{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="p">{{ number_format($det->subtotal, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:#999">Sin productos</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if($venta->notas)
            <div class="notas"><b>Notas:</b> {{ $venta->notas }}</div>
            @endif
        </div>

        <div class="col-totales">
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

            @if($empresa?->terminos_garantia)
            <div class="garantia-box">
                <b>🛡️ GARANTÍA:</b> {{ $empresa->terminos_garantia }}
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
            @if($empresa?->telefono)📞 {{ $empresa->telefono }}@endif
            @if($empresa?->instagram) · 📷 {{ $empresa->instagram }}@endif
            @if($empresa?->horario_atencion)<br>🕘 {{ $empresa->horario_atencion }}@endif
        </div>
        <div class="firma-zona">
            <div class="firma"><div class="linea"></div><div class="lbl">Firma cliente</div></div>
            <div class="firma"><div class="linea"></div><div class="lbl">Sello / Vendedor</div></div>
        </div>
    </div>

</div>

<button class="btn-print" onclick="window.print()">🖨️ Imprimir formato carta</button>

<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>