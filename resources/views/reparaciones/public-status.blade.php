@php
    $tiposDispositivo = ['celular'=>'📱 Celular','tablet'=>'📟 Tablet','portatil'=>'💻 Portátil','otros'=>'🔧 Otros'];
    $estados = [
        'recibido'=>['label'=>'Recibido','icon'=>'📥','color'=>'#6d28d9','bg'=>'#ede9fe'],
        'en_diagnostico'=>['label'=>'En diagnóstico','icon'=>'🔍','color'=>'#0369a1','bg'=>'#e0f2fe'],
        'esperando_repuesto'=>['label'=>'Esperando repuesto','icon'=>'⏳','color'=>'#92400e','bg'=>'#fef9c3'],
        'en_reparacion'=>['label'=>'En reparación','icon'=>'🔧','color'=>'#1d4ed8','bg'=>'#dbeafe'],
        'listo'=>['label'=>'Listo para entregar','icon'=>'✅','color'=>'#065f46','bg'=>'#d1fae5'],
        'entregado'=>['label'=>'Entregado','icon'=>'📦','color'=>'#374151','bg'=>'#f3f4f6'],
        'no_reparable'=>['label'=>'No reparable','icon'=>'❌','color'=>'#991b1b','bg'=>'#fee2e2'],
    ];
    $st = $estados[$reparacion->estado] ?? ['label'=>ucfirst($reparacion->estado),'icon'=>'❓','color'=>'#374151','bg'=>'#f3f4f6'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Estado de Reparación - {{ $reparacion->numero_orden }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:480px;width:100%;overflow:hidden;position:relative}
.card-header{background:linear-gradient(135deg,#667eea,#764ba2);padding:30px 24px;text-align:center;color:#fff}
.card-header .tienda{font-size:14px;opacity:0.9;margin-bottom:4px}
.card-header .nro-orden{font-size:22px;font-weight:800;letter-spacing:1px;word-break:break-word}
.card-header .nro-orden small{font-size:12px;font-weight:400;opacity:0.7}
.card-body{padding:24px}
.estado-badge{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:50px;font-weight:700;font-size:16px;margin-bottom:20px;width:100%;justify-content:center}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.info-item{padding:12px;border-radius:12px;background:#f8fafc}
.info-item .label{font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px}
.info-item .value{font-size:14px;font-weight:600;color:#1e293b}
.info-item.full{grid-column:1/-1}
.section-title{font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin:16px 0 8px;padding-bottom:4px;border-bottom:2px solid #e2e8f0}
.falla-box{background:#fef3c7;border-radius:12px;padding:14px;font-size:14px;color:#92400e;line-height:1.5;margin-bottom:12px}
.diag-box{background:#e0f2fe;border-radius:12px;padding:14px;font-size:14px;color:#0369a1;line-height:1.5;margin-bottom:12px}
.sol-box{background:#d1fae5;border-radius:12px;padding:14px;font-size:14px;color:#065f46;line-height:1.5;margin-bottom:12px}
.costos{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.costo-item{flex:1;min-width:100px;text-align:center;padding:12px;border-radius:12px;background:#f8fafc}
.costo-item .monto{font-size:20px;font-weight:800;color:#1e293b}
.costo-item .monto.presupuesto{color:#7c3aed}
.costo-item .monto.final{color:#059669}
.costo-item .monto.abono{color:#d97706}
.costo-item .monto.total{color:#dc2626}
.costo-item .lbl{font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase}
.garantia-box{display:flex;align-items:center;gap:12px;background:#e0f2fe;border-radius:12px;padding:14px;margin-bottom:16px}
.garantia-box .icon{font-size:28px}
.garantia-box .text{font-size:14px;font-weight:600;color:#0369a1}
.garantia-box .text small{font-weight:400;display:block;font-size:12px;color:#64748b}
.terminos-box{background:#f0fdf4;border-radius:12px;padding:14px;font-size:13px;color:#166534;line-height:1.6;margin-bottom:16px}
.terminos-box strong{display:block;font-size:13px;margin-bottom:6px;color:#15803d}
.cliente-info{font-size:14px;color:#475569;margin-bottom:16px;padding:12px;background:#f8fafc;border-radius:12px}
.cliente-info strong{color:#1e293b}
.footer{text-align:center;padding:20px 24px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8}
.footer .qr-section{margin-bottom:12px}
.footer .qr-section img{border-radius:8px;display:block;margin:0 auto}
.footer .qr-label{font-size:11px;color:#94a3b8;margin-top:4px}
.footer .qr-label strong{color:#64748b}
@media(max-width:480px){.info-grid{grid-template-columns:1fr}.costos{flex-direction:column}}
</style>
</head>
<body>
@php
    $nombreTienda = $empresa->nombre_tienda ?? 'CRM Celulares';
    $direccion = $empresa->direccion ?? '';
@endphp
<div class="card">
    <div class="card-header">
        <div class="tienda">{{ $nombreTienda }}</div>
        <div class="nro-orden">{{ $reparacion->numero_orden }}<br><small>{{ $tiposDispositivo[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?? 'Dispositivo' }}</small></div>
    </div>
    <div class="card-body">
        {{-- Estado actual --}}
        <div class="estado-badge" style="background:{{ $st['bg'] }};color:{{ $st['color'] }}">
            <span style="font-size:24px">{{ $st['icon'] }}</span>
            {{ $st['label'] }}
        </div>

        {{-- Info del equipo --}}
        <div class="section-title">📱 Equipo</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Marca</div>
                <div class="value">{{ $reparacion->marca ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Modelo</div>
                <div class="value">{{ $reparacion->modelo ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Color</div>
                <div class="value">{{ $reparacion->color ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="label">IMEI</div>
                <div class="value">{{ $reparacion->imei ?: '—' }}</div>
            </div>
            @if($reparacion->fecha_estimada)
            <div class="info-item">
                <div class="label">Fecha estimada</div>
                <div class="value">{{ $reparacion->fecha_estimada->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($reparacion->fecha_entrega)
            <div class="info-item">
                <div class="label">Entregado</div>
                <div class="value">{{ $reparacion->fecha_entrega->format('d/m/Y') }}</div>
            </div>
            @endif
        </div>

        {{-- Falla / Diagnóstico / Solución --}}
        @if($reparacion->falla_reportada)
        <div class="section-title">⚠️ Falla reportada</div>
        <div class="falla-box">{{ $reparacion->falla_reportada }}</div>
        @endif
        @if($reparacion->diagnostico)
        <div class="section-title">🔍 Diagnóstico técnico</div>
        <div class="diag-box">{{ $reparacion->diagnostico }}</div>
        @endif
        @if($reparacion->solucion)
        <div class="section-title">✅ Solución aplicada</div>
        <div class="sol-box">{{ $reparacion->solucion }}</div>
        @endif

        {{-- Costos --}}
        @if($reparacion->presupuesto>0 || $reparacion->costo_final>0 || $reparacion->abono>0 || $reparacion->total>0)
        <div class="section-title">💰 Costos</div>
        <div class="costos">
            @if($reparacion->presupuesto>0)
            <div class="costo-item">
                <div class="monto presupuesto">S/ {{ number_format($reparacion->presupuesto,2) }}</div>
                <div class="lbl">Presupuesto</div>
            </div>
            @endif
            @if($reparacion->costo_final>0)
            <div class="costo-item">
                <div class="monto final">S/ {{ number_format($reparacion->costo_final,2) }}</div>
                <div class="lbl">Costo final</div>
            </div>
            @endif
            @if($reparacion->abono>0)
            <div class="costo-item">
                <div class="monto abono">S/ {{ number_format($reparacion->abono,2) }}</div>
                <div class="lbl">Abono</div>
            </div>
            @endif
            @if($reparacion->total>0)
            <div class="costo-item">
                <div class="monto total">S/ {{ number_format($reparacion->total,2) }}</div>
                <div class="lbl">Total</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Garantía --}}
        @if($reparacion->garantia)
        <div class="section-title">🛡️ Garantía</div>
        <div class="garantia-box">
            <div class="icon">🛡️</div>
            <div class="text">
                Este equipo tiene garantía
                <small>{{ $reparacion->dias_garantia }} días desde la entrega</small>
            </div>
        </div>
        @endif

        {{-- Términos de garantía de la empresa --}}
        @if($empresa && $empresa->terminos_garantia)
        <div class="section-title">📋 Condiciones de Garantía</div>
        <div class="terminos-box">
            <strong>Condiciones y términos de garantía:</strong>
            {{ $empresa->terminos_garantia }}
        </div>
        @endif

        {{-- Notas adicionales --}}
        @if($reparacion->notas)
        <div class="section-title">📝 Notas</div>
        <div class="info-item full" style="margin-bottom:12px">
            <div class="value">{{ $reparacion->notas }}</div>
        </div>
        @endif

        {{-- Cliente --}}
        <div class="section-title">👤 Cliente</div>
        <div class="cliente-info">
            <strong>{{ $reparacion->cliente->nombre_completo ?? '—' }}</strong>
            @if($reparacion->cliente->telefono)<br>📞 {{ $reparacion->cliente->telefono }}@endif
        </div>
    </div>
    <div class="footer">
        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(url()->current()) }}" alt="QR" style="width:100px;height:100px">
            <div class="qr-label">Escanea para ver el estado de tu reparación</div>
        </div>
        <div><strong>{{ $empresa->nombre_tienda ?? 'CRM Celulares' }}</strong></div>
        @if($empresa && $empresa->direccion)
        <div>{{ $empresa->direccion }}</div>
        @endif
        <div style="margin-top:4px">{{ $reparacion->created_at->format('d/m/Y H:i') }} · N° {{ $reparacion->numero_orden }}</div>
    </div>
</div>
</body>
</html>