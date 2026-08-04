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
body{font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#000;width:72mm}
@page{size:80mm auto;margin:0;padding:2mm}
.hdr{text-align:center;padding:3px 0}
.hdr .logo{max-height:40px;max-width:140px;margin:2px auto}
.hdr .tienda{font-size:16px;font-weight:700}
.hdr .inf{font-size:10px;color:#000}
.hdr .nro{font-size:20px;font-weight:700;letter-spacing:1px;margin:4px 0}
.section{font-weight:700;font-size:13px;margin:3px 0 1px 0}
.det{font-size:14px;font-weight:600}
.eq-table{width:100%;border-collapse:collapse;margin:2px 0}
.eq-table td{padding:1px 2px;font-size:13px;vertical-align:top}
.eq-table .lbl{font-size:10px;font-weight:700;color:#000;width:30%}
.eq-table .val{font-weight:700;font-size:13px;width:70%}
.bx{font-size:13px;font-weight:600;word-break:break-word;overflow-wrap:break-word}
.gar{font-size:12px;text-align:center;font-weight:700}
prices{display:flex;flex-wrap:wrap;gap:2px;justify-content:center}
.price-box{text-align:center;font-size:13px;font-weight:700;padding:2px 6px;border:1px solid #000;border-radius:2px;margin:1px}
.price-box .lbl{font-size:9px;font-weight:700}
.ftr{text-align:center;margin-top:3px;font-size:10px}
.ftr .gr{font-size:13px;font-weight:700}
.firma-img{max-width:100%;max-height:90px;background:#fff;border:1px solid #000}
.firma-label{font-size:11px;font-weight:700;margin-top:2px;text-align:center}
hr{border:none;border-top:2px solid #000;margin:3px 0}
</style>
</head>
<body>
<div class="hdr">
@if($logoSrc)<img src="{{ $logoSrc }}" alt="" class="logo">@endif
<div class="tienda">{{ $empresa->nombre_tienda ?? 'CRM Celulares' }}</div>
<div class="inf">RUC: {{ $empresa->ruc ?? '' }}@if($empresa->ruc && $empresa->direccion) | @endif{{ $empresa->direccion ?? '' }}</div>
<div class="nro">{{ $reparacion->numero_orden }}</div>
<div class="det">{{ $estadoLabel }} @if($reparacion->prioridad!='baja'){{ $prioridadIcon[$reparacion->prioridad]??'' }}@endif | Téc: {{ $reparacion->tecnico->name ?? '—' }}</div>
</div>
<hr>
<div class="section">CLIENTE</div>
<div class="det">{{ $reparacion->cliente->nombre_completo ?? '—' }}{{ $reparacion->cliente->telefono ? ' | T:'.$reparacion->cliente->telefono : '' }}</div>
<hr>
<div class="section">EQUIPO</div>
<table class="eq-table">
<tr><td class="lbl">TIPO</td><td class="val">{{ $tipoDispositivo[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?? '—' }}</td></tr>
<tr><td class="lbl">MARCA</td><td class="val">{{ $reparacion->marca ?: '—' }}</td></tr>
<tr><td class="lbl">MODELO</td><td class="val">{{ $reparacion->modelo ?: '—' }}</td></tr>
<tr><td class="lbl">IMEI</td><td class="val">{{ $reparacion->imei ?: '—' }}</td></tr>
<tr><td class="lbl">COLOR</td><td class="val">{{ $reparacion->color ?: '—' }}</td></tr>
<tr><td class="lbl">RECIBIDO</td><td class="val">{{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }}</td></tr>
@if($reparacion->fecha_estimada)<tr><td class="lbl">EST.ENTREGA</td><td class="val">{{ $reparacion->fecha_estimada->format('d/m/Y') }}</td></tr>@endif
@if($reparacion->fecha_entrega)<tr><td class="lbl">ENTREGADO</td><td class="val">{{ $reparacion->fecha_entrega->format('d/m/Y') }}</td></tr>@endif
@if($reparacion->tipo_codigo)
<tr><td class="lbl">{{ $tipoCodigoMostrar }}</td><td class="val">
@if($reparacion->tipo_codigo==='patron' && $reparacion->patron_secuencia)
@php $nums = explode('-', $reparacion->patron_secuencia); $p = ''; foreach(range(1,9) as $i) { $p .= in_array($i,$nums) ? '#' : 'O'; if($i%3==0&&$i<9) $p.=' '; } @endphp
{{ $p }} {{ $reparacion->patron_secuencia }}
@elseif($reparacion->tipo_codigo==='pin')
{{ $reparacion->codigo_equipo ?: '—' }}
@endif
</td></tr>
@endif
</table>
<hr>
<div class="section">FALLA REPORTADA</div>
<div class="bx">{{ $reparacion->falla_reportada }}</div>
@if($reparacion->diagnostico)<div class="section">DIAGNÓSTICO</div><div class="bx">{{ $reparacion->diagnostico }}</div>@endif
@if($reparacion->solucion)<div class="section">SOLUCIÓN</div><div class="bx">{{ $reparacion->solucion }}</div>@endif
@if($reparacion->presupuesto>0||$reparacion->costo_final>0||$reparacion->abono>0||$reparacion->total>0)
<div class="prices">
@if($reparacion->presupuesto>0)<div class="price-box"><div class="lbl">PRESUPUESTO</div>S/{{ number_format($reparacion->presupuesto,2) }}</div>@endif
@if($reparacion->costo_final>0)<div class="price-box"><div class="lbl">COSTO FINAL</div>S/{{ number_format($reparacion->costo_final,2) }}</div>@endif
@if($reparacion->abono>0)<div class="price-box"><div class="lbl">ABONO</div>S/{{ number_format($reparacion->abono,2) }}</div>@endif
@if($reparacion->total>0)<div class="price-box"><div class="lbl">TOTAL</div>S/{{ number_format($reparacion->total,2) }}</div>@endif
</div>
@endif
@if($reparacion->garantia)<div class="gar">Garantía: {{ $reparacion->dias_garantia }} días</div>@endif
@if($reparacion->notas)<div class="section">NOTAS</div><div class="bx">{{ $reparacion->notas }}</div>@endif
@if($empresa && $empresa->terminos_garantia)
<hr>
<div class="section">GARANTÍA</div>
<div style="font-size:12px;font-weight:700;text-align:justify;">{{ $empresa->terminos_garantia }}</div>
@endif
@if($reparacion->firma_recepcion)
<hr>
<div class="section">FIRMA RECEPCIÓN</div>
<div style="text-align:center;margin:3px 0;">
    <img src="{{ asset('storage/'.$reparacion->firma_recepcion) }}" alt="Firma" class="firma-img">
    <div class="firma-label">Cliente: {{ $reparacion->cliente->nombre_completo ?? '' }}</div>
</div>
@endif
@if($reparacion->firma_entrega)
<hr>
<div class="section">FIRMA ENTREGA</div>
<div style="text-align:center;margin:3px 0;">
    <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma" class="firma-img">
    <div class="firma-label">Cliente: {{ $reparacion->cliente->nombre_completo ?? '' }}</div>
</div>
@endif

@if($cupon)
<hr>
<div class="section" style="text-align:center;font-size:14px;">🎟️ CUPÓN DE DESCUENTO</div>
<div style="text-align:center;border:2px dashed #000;padding:6px;margin:3px 0;">
    <div style="font-size:10px;">Código</div>
    <div style="font-size:18px;font-weight:700;letter-spacing:2px;">{{ $cupon->codigo }}</div>
    <div style="font-size:14px;font-weight:700;margin-top:2px;">{{ $cupon->valor }}% DE DESCUENTO</div>
    <div style="font-size:10px;">{{ $cupon->descripcion }}</div>
    @if($cupon->fecha_expiracion)
    <div style="font-size:10px;font-weight:700;margin-top:2px;">Vence: {{ $cupon->fecha_expiracion->format('d/m/Y') }}</div>
    @endif
</div>
@endif

@if($urlMiniWeb)
<hr>
<div class="section" style="text-align:center;font-size:13px;">🌐 VISÍTANOS EN LÍNEA</div>
<div style="margin:4px auto;text-align:center;">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($urlMiniWeb) }}" alt="QR Mini Web" style="width:80px;height:80px">
    <div style="font-size:10px;font-weight:700;">Escanea para ver nuestra tienda</div>
    <div style="font-size:9px;word-break:break-all;">{{ $urlMiniWeb }}</div>
</div>
@endif

<div class="ftr">
@php $qrUrl = route('reparaciones.public-status', $reparacion->numero_orden); @endphp
<div style="margin:4px auto;text-align:center;">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qrUrl) }}" alt="QR" style="width:80px;height:80px">
    <div style="font-size:10px;font-weight:700;">Escanea para ver estado</div>
</div>
<div class="gr">¡Gracias por su preferencia!</div>
<div style="font-size:10px;">{{ $reparacion->created_at->format('d/m/Y H:i') }} | {{ $reparacion->numero_orden }}</div>
</div>
<script>window.onload=function(){window.print()};window.onafterprint=function(){window.close()};</script>
</body>
</html>