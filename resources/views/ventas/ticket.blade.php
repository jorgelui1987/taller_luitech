<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket {{ $venta->numero_venta }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Lucida Console','Courier New',monospace;font-size:11px;line-height:1.3;color:#000;width:72mm;
    -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-rendering:optimizeLegibility}
@page{size:72mm auto;margin:2mm}
.hdr{text-align:center}
.hdr .tienda{font-size:13px;font-weight:700;letter-spacing:-0.3px}
.hdr .inf{font-size:9px;letter-spacing:-0.2px}
.hdr .nro{font-size:14px;font-weight:700;letter-spacing:0.5px}
.det{font-size:11px}
.det .etq{font-size:8px;font-weight:700}
.prod{width:100%;border-collapse:collapse;font-size:10px;table-layout:fixed}
.prod th{font-size:8px;text-align:left;font-weight:700;border-bottom:1.5px solid #000;text-transform:uppercase}
.prod th.c,.prod td.c{text-align:center}
.prod th.p,.prod td.p{text-align:right}
.prod td{padding:1px 0;vertical-align:top;overflow:hidden}
.prod td.n{font-weight:700;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
.prod td.d{font-size:8px;color:#555;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.prod th:nth-child(1){width:55%}
.prod th:nth-child(2){width:12%}
.prod th:nth-child(3){width:16%}
.prod th:nth-child(4){width:17%}
.tot .l{display:flex;justify-content:space-between;font-size:10px;white-space:nowrap}
.tot .lt{display:flex;justify-content:space-between;font-weight:700;font-size:13px;border-top:1.5px solid #000;border-bottom:1.5px solid #000;padding:2px 0;white-space:nowrap}
.not{font-size:9px}
.section{font-weight:700;margin-top:2px;font-size:10px}
.ftr{text-align:center;margin-top:2px;font-size:9px}
.ftr .gr{font-size:10px;font-weight:700}
hr{border:none;border-top:1.5px solid #000;margin:1px 0}
hr.dotted{border-top:1px dashed #000}
/* Mini web - más visible para el cliente */
.miniweb{text-align:center;margin:4px 0;padding:4px 2px;border:2px solid #000;border-radius:4px;background:#fff}
.miniweb .lbl{font-size:8px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase}
.miniweb .url{font-size:11px;font-weight:700;word-break:break-all;line-height:1.4}

@media print{
    body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>
</head>
<body>
<div class="hdr">
@if($empresa && $empresa->logo)<img src="{{ asset($empresa->logo) }}" alt="" style="max-height:60px;max-width:100px">@endif
<div class="tienda">{{ $empresa->nombre_tienda ?? 'CRM Celulares' }}</div>
<div class="inf">{{ $empresa->ruc ?? '' }}{{ ($empresa->ruc??'') && ($empresa->direccion??'') ? ' | ' : '' }}{{ $empresa->direccion ?? '' }}</div>
<div class="inf">{{ $empresa->telefono ?? '' }}{{ ($empresa->telefono??'') && ($empresa->email??'') ? ' | ' : '' }}{{ $empresa->email ?? '' }}</div>
<div class="nro">{{ $venta->numero_venta }}</div>
<div>{{ ucfirst($venta->estado) }} | {{ $venta->fecha_venta->format('d/m/Y H:i') }}</div>
@if($venta->estado === 'cancelada')
<div style="font-size:16px;font-weight:700;color:#000; border:2px solid #000; padding:4px 0; margin-top:4px;">*** VENTA CANCELADA ***</div>
@endif
</div>
<hr>
<div class="det"><span class="etq">CLIENTE </span>{{ $venta->cliente?->nombre_completo ?? 'VENTA GENERAL' }}</div>
@if($venta->cliente?->telefono || $venta->cliente?->email)
<div class="det" style="font-size:9px;">
    @if($venta->cliente?->telefono)<span class="etq">TEL </span>{{ $venta->cliente->telefono }}@endif
    @if($venta->cliente?->telefono && $venta->cliente?->email) | @endif
    @if($venta->cliente?->email)<span class="etq">EMAIL </span>{{ $venta->cliente->email }}@endif
</div>
@endif
<div class="det"><span class="etq">PAGO </span>{{ ucfirst($venta->metodo_pago) }} | {{ $venta->vendedor->name ?? '—' }}</div>
<hr>
<table class="prod">
<thead><tr><th>Producto</th><th class="c">Cant</th><th class="p">P.U.</th><th class="p">Subt.</th></tr></thead>
<tbody>
@foreach($venta->detalles as $det)
<tr>
<td>
<span class="n">{{ $det->producto->nombre ?? '—' }}</span>
@if($det->producto && $det->producto->marca)<span class="d"> {{ $det->producto->marca->nombre }}</span>@endif
@if($det->imei_vendido)<span class="d"> IMEI:{{ $det->imei_vendido }}</span>@endif
@if($det->descuento > 0)<span class="d"> Desc:-{{ $empresa->simbolo_moneda ?? '$' }}{{ number_format($det->descuento,2) }}</span>@endif
</td>
<td class="c">{{ $det->cantidad }}</td>
<td class="p">{{ number_format($det->precio_unitario,2) }}</td>
<td class="p">{{ number_format($det->subtotal,2) }}</td>
</tr>
@endforeach
</tbody>
</table>
<div class="tot">
<div class="l"><span>Subtotal</span><span>{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->subtotal,2) }}</span></div>
@if($venta->descuento > 0)<div class="l"><span>Descuento</span><span>-{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->descuento,2) }}</span></div>@endif
<div class="l"><span>{{ $empresa->pais == 'CL' ? 'IVA' : 'IGV' }} ({{ $empresa->igv ?? 18 }}%)</span><span>{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->impuesto,2) }}</span></div>
<div class="lt"><span>TOTAL</span><span>{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->total,2) }}</span></div>
</div>
@if($venta->notas)<div class="not">Notas: {{ $venta->notas }}</div>@endif

@if($urlMiniWeb)
<hr class="dotted">
<div class="miniweb">
    <div class="lbl">🌐 Visita nuestra tienda online</div>
    <div class="url">{{ $urlMiniWeb }}</div>
</div>
@endif

<div class="ftr">
<div class="gr">Gracias por su preferencia!</div>
<div>{{ $venta->created_at->format('d/m/Y H:i') }}</div>
</div>
<div id="btPrintBtn" style="display:none; text-align:center; margin:10px 0; padding:10px;">
    <button onclick="BTPrintTicket.imprimir()" style="background:#0070e0; color:#fff; border:none; border-radius:8px; padding:12px 20px; font-size:14px; cursor:pointer; font-family:Arial, sans-serif;">
        <span class="bt-print-status">📡 Imprimir por Bluetooth</span>
    </button>
    <div id="btPrintMsg" style="font-size:11px; font-family:Arial, sans-serif; color:#555; margin-top:6px;"></div>
</div>

<script src="{{ asset('js/bluetooth-print.js') }}"></script>
<script>
// Si el navegador soporta Web Bluetooth, mostrar botón de impresión Bluetooth
if (navigator.bluetooth) {
    document.getElementById('btPrintBtn').style.display = 'block';
}

window.BTPrintTicket = (function() {
    const ventaData = {
        tienda: @json($empresa->nombre_tienda ?? 'CRM Celulares'),
        direccion: @json($empresa->direccion ?? ''),
        telefono: @json($empresa->telefono ?? ''),
        numero_venta: @json($venta->numero_venta),
        cliente: @json($venta->cliente?->nombre_completo ?? 'VENTA GENERAL'),
        fecha: @json($venta->fecha_venta->format('d/m/Y H:i')),
        metodo_pago: @json(ucfirst($venta->metodo_pago)),
        vendedor: @json($venta->vendedor->name ?? '—'),
        subtotal: {{ $venta->subtotal }},
        descuento: {{ $venta->descuento }},
        impuesto: {{ $venta->impuesto }},
        total: {{ $venta->total }},
        productos: @json($venta->detalles->map(function($det) {
            return [
                'nombre' => $det->producto?->nombre ?? '—',
                'cantidad' => $det->cantidad,
                'precio_unitario' => number_format($det->precio_unitario, 2),
                'subtotal' => number_format($det->subtotal, 2),
            ];
        }))
    };

    async function imprimir() {
        const btn = document.querySelector('#btPrintBtn button');
        const msg = document.getElementById('btPrintMsg');
        const status = document.querySelector('.bt-print-status');

        try {
            status.textContent = '🔍 Buscando impresora...';
            msg.innerHTML = 'Asegúrate de que la impresora Bluetooth esté encendida.';
            await BTPrint.conectar();

            status.textContent = '🖨️ Imprimiendo...';
            msg.innerHTML = 'Enviando ticket a la impresora...';
            const datos = BTPrint.generarTicketVenta(ventaData);
            await BTPrint.enviarDatos(datos);

            status.textContent = '✅ ¡Ticket impreso!';
            msg.innerHTML = 'La impresión se completó correctamente.';
            setTimeout(() => { status.textContent = '📡 Imprimir por Bluetooth'; }, 3000);
        } catch (err) {
            status.textContent = '❌ Error';
            msg.innerHTML = '<span style="color:#dc2626;">' + (err.message || 'No se pudo imprimir') + '</span>';
            setTimeout(() => { status.textContent = '📡 Imprimir por Bluetooth'; }, 4000);
        }
    }

    return { imprimir: imprimir };
})();
</script>
<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>
