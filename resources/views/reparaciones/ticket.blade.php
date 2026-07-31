@php
    $estadoLabel = str_replace('_',' ',ucfirst($reparacion->estado));
    $prioridadIcon = ['urgente'=>'!!!','alta'=>'!!','media'=>'!','baja'=>''];
    $tipoDispositivo = ['celular'=>'Celular','tablet'=>'Tablet','portatil'=>'Portatil','otros'=>'Otros'];
    $tiposCodigo = ['patron'=>'Patron','pin'=>'PIN'];
    $tipoCodigoMostrar = $tiposCodigo[$reparacion->tipo_codigo] ?? '';

    // Obtener URL correcta del logo
    $logoSrc = null;
    if ($empresa && $empresa->logo) {
        $logoPath = str_replace('storage/', '', $empresa->logo);
        $fullPath = storage_path('app/public/' . $logoPath);
        if (file_exists($fullPath)) {
            $logoSrc = route('storage.serve', ['path' => $logoPath]);
        } else {
            $logoSrc = asset($empresa->logo);
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sticker {{ $reparacion->numero_orden }}</title>
<style>
*{margin:0;padding:0}
body{font-family:'Arial','Helvetica',sans-serif;font-size:13px;line-height:1.3;color:#000;width:72mm}
@page{size:80mm auto;margin:0}
.hdr{text-align:center;padding:2px 0}
.hdr .logo{max-height:35px;max-width:120px;margin:2px auto}
.hdr .tienda{font-size:15px;font-weight:700}
.hdr .inf{font-size:10px}
.hdr .nro{font-size:18px;font-weight:700;letter-spacing:1px;margin:2px 0}
.section{font-weight:700;margin-top:2px;font-size:12px}
.det{font-size:13px}
.g2{width:100%}
.g2 .row{display:flex;flex-wrap:wrap}
.g2 .col{width:50%}
.g2 .l{font-size:9px;font-weight:700;color:#555}
.g2 .v{font-size:12px;font-weight:600}
.g2 .full{width:100%}
.bx{font-size:12px;word-break:break-word;overflow-wrap:break-word}
.gar{font-size:11px;text-align:center}
.cr{display:flex;gap:4px;justify-content:center}
.ci{text-align:center;font-size:12px;font-weight:700}
.ci .l{font-size:9px;font-weight:700;color:#555}
.ftr{text-align:center;margin-top:2px;font-size:10px}
.ftr .gr{font-size:12px;font-weight:700}
.firma-img{max-width:100%;max-height:80px;background:#fff;border:1px solid #ddd;border-radius:2px}
.firma-label{font-size:10px;color:#555;margin-top:1px}
hr{border:none;border-top:2px solid #000;margin:2px 0}
</style>
</head>
<body>
<div class="hdr">
@if($logoSrc)<img src="{{ $logoSrc }}" alt="" class="logo">@endif
<div class="tienda">{{ $empresa->nombre_tienda ?? 'CRM Celulares' }}</div>
<div class="inf">RUC: {{ $empresa->ruc ?? '' }}@if($empresa->ruc && $empresa->direccion) | @endif{{ $empresa->direccion ?? '' }}</div>
<div class="nro">{{ $reparacion->numero_orden }}</div>
<div>{{ $estadoLabel }} @if($reparacion->prioridad!='baja'){{ $prioridadIcon[$reparacion->prioridad]??'' }}@endif | Téc: {{ $reparacion->tecnico->name ?? '—' }}</div>
</div>
<hr>
<div class="section">CLIENTE</div>
<div class="det">{{ $reparacion->cliente->nombre_completo ?? '—' }}{{ $reparacion->cliente->telefono ? ' T:'.$reparacion->cliente->telefono : '' }}</div>
<hr>
<div class="section">EQUIPO</div>
<div class="g2">
<div class="row">
<div class="col"><span class="l">TIPO </span><span class="v">{{ $tipoDispositivo[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?? '—' }}</span></div>
<div class="col"><span class="l">MARCA </span><span class="v">{{ $reparacion->marca ?: '—' }}</span></div>
<div class="col"><span class="l">MODELO </span><span class="v">{{ $reparacion->modelo ?: '—' }}</span></div>
<div class="col"><span class="l">IMEI </span><span class="v">{{ $reparacion->imei ?: '—' }}</span></div>
@if($reparacion->tipo_codigo)
<div class="full">
<span class="l">{{ $tipoCodigoMostrar }} </span>
<span class="v">
@if($reparacion->tipo_codigo==='patron' && $reparacion->patron_secuencia)
@php $nums = explode('-', $reparacion->patron_secuencia); $p = ''; foreach(range(1,9) as $i) { $p .= in_array($i,$nums) ? '#' : 'O'; if($i%3==0&&$i<9) $p.=' '; } @endphp
{{ $p }} {{ $reparacion->patron_secuencia }}
@elseif($reparacion->tipo_codigo==='pin')
{{ $reparacion->codigo_equipo ?: '—' }}
@endif
</span>
</div>
@endif
<div class="col"><span class="l">COLOR </span><span class="v">{{ $reparacion->color ?: '—' }}</span></div>
<div class="col"><span class="l">RECIBIDO </span><span class="v">{{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }}</span></div>
@if($reparacion->fecha_estimada)<div class="col"><span class="l">EST.ENTREGA </span><span class="v">{{ $reparacion->fecha_estimada->format('d/m/Y') }}</span></div>@endif
@if($reparacion->fecha_entrega)<div class="col"><span class="l">ENTREGADO </span><span class="v">{{ $reparacion->fecha_entrega->format('d/m/Y') }}</span></div>@endif
</div>
</div>
<hr>
<div class="section">FALLA</div>
<div class="bx">{{ $reparacion->falla_reportada }}</div>
@if($reparacion->diagnostico)<div class="section">DIAGNÓSTICO</div><div class="bx">{{ $reparacion->diagnostico }}</div>@endif
@if($reparacion->solucion)<div class="section">SOLUCIÓN</div><div class="bx">{{ $reparacion->solucion }}</div>@endif
@if($reparacion->presupuesto>0||$reparacion->costo_final>0||$reparacion->abono>0||$reparacion->total>0)
<div class="cr">
@if($reparacion->presupuesto>0)<div class="ci"><div class="l">PRESUPUESTO</div>S/{{ number_format($reparacion->presupuesto,2) }}</div>@endif
@if($reparacion->costo_final>0)<div class="ci"><div class="l">COSTO FINAL</div>S/{{ number_format($reparacion->costo_final,2) }}</div>@endif
@if($reparacion->abono>0)<div class="ci"><div class="l">ABONO</div>S/{{ number_format($reparacion->abono,2) }}</div>@endif
@if($reparacion->total>0)<div class="ci"><div class="l">TOTAL</div>S/{{ number_format($reparacion->total,2) }}</div>@endif
</div>
@endif
@if($reparacion->garantia)<div class="gar">Garantía: {{ $reparacion->dias_garantia }} días</div>@endif
@if($reparacion->notas)<div class="section">NOTAS</div><div class="bx">{{ $reparacion->notas }}</div>@endif
@if($empresa && $empresa->terminos_garantia)
<hr>
<div class="section">GARANTÍA</div>
<div style="font-size:11px;font-weight:700;text-align:justify;">{{ $empresa->terminos_garantia }}</div>
@endif
@if($reparacion->firma_recepcion)
<hr>
<div class="section">FIRMA RECEPCIÓN</div>
<div style="text-align:center; margin:2px 0;">
    <img src="{{ asset('storage/'.$reparacion->firma_recepcion) }}" alt="Firma recepción"
         class="firma-img">
    <div class="firma-label">Cliente: {{ $reparacion->cliente->nombre_completo ?? '' }}</div>
</div>
@endif
@if($reparacion->firma_entrega)
<hr>
<div class="section">FIRMA ENTREGA</div>
<div style="text-align:center; margin:2px 0;">
    <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma entrega"
         class="firma-img">
    <div class="firma-label">Cliente: {{ $reparacion->cliente->nombre_completo ?? '' }}</div>
</div>
@endif
<div class="ftr">
@php
    $qrUrl = route('reparaciones.public-status', $reparacion->numero_orden);
@endphp
<div style="margin:4px auto;text-align:center;">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qrUrl) }}" alt="QR" style="width:80px;height:80px;display:inline-block;">
    <div style="font-size:9px;color:#666;">Escanea para ver estado y garantía</div>
</div>
<div class="gr">¡Gracias por su preferencia!</div>
<div>{{ $reparacion->created_at->format('d/m/Y H:i') }} | {{ $reparacion->numero_orden }}</div>
</div>
<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>