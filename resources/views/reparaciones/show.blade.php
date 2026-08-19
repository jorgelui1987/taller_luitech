@extends('layouts.app')
@section('title', 'Orden '.$reparacion->numero_orden)

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#a855f7;">Reparaciones</a></li></ul>
    <ul><li class="breadcrumb-item active">{{ $reparacion->numero_orden }}</li></ul>
@endsection

@push('styles')
<style>
/* Timeline minimal */
.timeline-bar { display:flex; align-items:center; gap:4px; margin-top:8px; }
.timeline-step { flex:1; height:6px; border-radius:3px; background:#e5e7eb; position:relative; }
.timeline-step.active { background:linear-gradient(90deg, #a855f7, #7c3aed); }
.timeline-step.current { background:#7c3aed; box-shadow:0 0 0 2px #ede9fe; }
.timeline-step.done { background:#10b981; }
.timeline-step .dot { position:absolute; top:-4px; right:-2px; width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #e5e7eb; }
.timeline-step.done .dot { background:#10b981; border-color:#10b981; }
.timeline-step.current .dot { background:#7c3aed; border-color:#7c3aed; }

@media print {
    .sidebar,.topbar,.breadcrumb,.btn-acciones { display:none!important; }
    .main-wrapper { margin-left:0!important; }
    .page-content { padding:0!important; }
}
/* Signature pad */
.signature-pad-wrapper {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    background: #fff;
    position: relative;
    cursor: crosshair;
}
.signature-pad-wrapper canvas {
    display: block;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    touch-action: none;
}
.signature-pad-wrapper .placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #9ca3af;
    font-size: 13px;
    pointer-events: none;
}
/* Photo gallery */
.foto-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 1;
    background: #f3f4f6;
}
.foto-item .foto-ver {
    display: block;
    width: 100%;
    height: 100%;
    padding: 0;
    border: none;
    background: none;
    cursor: pointer;
}
.foto-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.foto-item .foto-tipo {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    font-size: 9px;
    padding: 2px 7px;
    border-radius: 8px;
}
.foto-item .foto-delete {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(239,68,68,0.9);
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 11px;
    transition: all .2s;
}
.foto-item .foto-delete:hover {
    background: #dc2626;
    transform: scale(1.1);
}
.foto-upload-box {
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: #f9fafb;
    transition: all .2s;
    color: #9ca3af;
}
.foto-upload-box:hover {
    border-color: #a855f7;
    background: #f8f5ff;
    color: #a855f7;
}
.foto-upload-box input[type="file"] {
    display: none;
}
/* Lightbox */
.lightbox-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.9);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.lightbox-overlay.show {
    display: flex;
}
.lightbox-overlay img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
}
.lightbox-overlay .close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: #fff;
    font-size: 30px;
    cursor: pointer;
    background: none;
    border: none;
}
/* Info badges */
.info-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:500;
}
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 btn-acciones">
    <div>
        <h4 class="mb-1 fw-bold">{{ $reparacion->numero_orden }}</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            <i class="far fa-calendar-alt me-1"></i>Recibido {{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }} ·
            <i class="fas fa-user-cog me-1"></i>Técnico: <strong>{{ $reparacion->tecnico->name ?? '—' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('reparaciones.ticket', $reparacion) }}" target="_blank" class="btn btn-outline-primary px-4">
            <i class="fas fa-receipt me-2"></i>Sticker 80mm
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary px-4">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>
        @if($reparacion->cliente && ($reparacion->cliente->telefono || $reparacion->cliente->celular))
        <a href="{{ route('reparaciones.whatsapp', $reparacion) }}" target="_blank" class="btn px-4"
           style="background:#25D366; color:#fff; border-radius:8px;">
            <i class="fab fa-whatsapp me-2"></i>Enviar por WhatsApp
        </a>
        @endif
        <a href="{{ route('reparaciones.edit', $reparacion) }}" class="btn btn-primary px-4">
            <i class="fas fa-edit me-2"></i>Actualizar Estado
        </a>
        @if(Auth::user()->rol === 'admin')
        <form action="{{ route('reparaciones.destroy', $reparacion) }}" method="POST"
              onsubmit="return confirm('¿Estás seguro de eliminar la orden {{ $reparacion->numero_orden }}? Esta acción no se puede deshacer.');"
              style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger px-4">
                <i class="fas fa-trash me-2"></i>Eliminar
            </button>
        </form>
        @endif
    </div>
</div>

@php
    $stColors = ['recibido'=>['#ede9fe','#6d28d9'],'en_diagnostico'=>['#e0f2fe','#0369a1'],'esperando_repuesto'=>['#fef9c3','#92400e'],'en_reparacion'=>['#dbeafe','#1d4ed8'],'listo'=>['#d1fae5','#065f46'],'entregado'=>['#f3f4f6','#374151'],'no_reparable'=>['#fee2e2','#991b1b']];
    $priCol = ['urgente'=>['#fee2e2','#991b1b','🔴'],'alta'=>['#ffedd5','#9a3412','🟠'],'media'=>['#fef9c3','#713f12','🟡'],'baja'=>['#d1fae5','#065f46','🟢']];
    $pr = $priCol[$reparacion->prioridad] ?? ['#f3f4f6','#374151','⚪'];
    $tipos = ['celular'=>'📱 Celular','tablet'=>'📟 Tablet','portatil'=>'💻 Portátil','otros'=>'🔧 Otros'];
@endphp

{{-- Tarjeta de Resumen (datos clave de un vistazo) --}}
<div class="card mb-4">
    <div class="card-header p-3" style="background:linear-gradient(135deg,#faf5ff,#fdf4ff); border-bottom:1px solid #f3e8ff;">
        <div class="row g-3 align-items-center">
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Estado</div>
                <span style="background:{{ $stColors[$reparacion->estado][0] }}; color:{{ $stColors[$reparacion->estado][1] }}; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; display:inline-block;">
                    {{ str_replace('_',' ',ucfirst($reparacion->estado)) }}
                </span>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Equipo</div>
                <div style="font-weight:600; font-size:14px;">{{ $reparacion->dispositivo ?: ($reparacion->marca ?: '—') }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Prioridad</div>
                <span class="info-badge" style="background:{{ $pr[0] }}; color:{{ $pr[1] }};">
                    {{ $pr[2] }} {{ ucfirst($reparacion->prioridad) }}
                </span>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Total</div>
                <div style="font-weight:700; font-size:16px; color:#7c3aed;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($reparacion->total, 2) }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
                <div style="font-weight:600; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $reparacion->cliente->nombre_completo ?? '—' }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Recibido</div>
                <div style="font-weight:600; font-size:13px;">{{ optional($reparacion->fecha_recepcion)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Cobro con Mercado Pago (Opción B: venta pendiente, completar al confirmar pago) --}}
@if($reparacion->estado === 'entregado' && $reparacion->total > 0 && (!$ventaReparacion || $ventaReparacion->estado === 'pendiente'))
<div class="card mb-4" style="border:1px solid #00b1ea;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-1" style="color:#00b1ea; font-size:14px;">
                    <i class="fab fa-mercadopago me-1"></i>Mercado Pago
                </h6>
                <p class="text-muted mb-0" style="font-size:12px;">
                    Cobra la reparación con Mercado Pago. La venta se creará al confirmarse el pago.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form action="{{ route('reparaciones.point', $reparacion) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:#00b1ea; color:#fff; border-radius:8px;">
                        <i class="fas fa-credit-card me-1"></i>Cobrar con Point
                    </button>
                </form>
                <form action="{{ route('reparaciones.mercadopago', $reparacion) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                        <i class="fas fa-qrcode me-1"></i>Generar QR de Pago
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Mensaje de QR generado --}}
@if(session('mercadopago_reparacion'))
<div class="card mb-4" style="border:1px solid #00b1ea;">
    <div class="card-body p-3 text-center">
        <h6 class="fw-bold mb-2" style="color:#00b1ea; font-size:13px;">
            <i class="fab fa-mercadopago me-1"></i>Pago con Mercado Pago
        </h6>
        @if(session('mercadopago_reparacion.init_point'))
        <div class="mb-2">
            <a href="{{ session('mercadopago_reparacion.init_point') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-external-link-alt me-1"></i>Abrir enlace de pago
            </a>
        </div>
        @endif
        <div style="font-size:11px; color:#6b7280;">
            La venta se creará automáticamente al confirmarse el pago.
        </div>
    </div>
</div>
@endif

{{-- Mensaje de cobro Point enviado --}}
@if(session('point_reparacion'))
<div class="card mb-4" style="border:1px solid #00b1ea;">
    <div class="card-body p-3 text-center">
        <h6 class="fw-bold mb-2" style="color:#00b1ea; font-size:13px;">
            <i class="fab fa-mercadopago me-1"></i>Cobro enviado al Point
        </h6>
        <div class="mb-2" style="font-size:12px; color:#6b7280;">
            {{ session('point_reparacion.mensaje') ?? 'Esperando confirmación del dispositivo...' }}
        </div>
        <div style="font-size:11px; color:#6b7280;">
            La venta se creará automáticamente al confirmarse el pago.
        </div>
    </div>
</div>
@endif

{{-- Pestañas de secciones --}}
<div class="card mb-4">
    <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
        <ul class="nav nav-tabs card-header-tabs" id="reparacionTabs" role="tablist" style="border-bottom:none; padding:0 8px;">
            <li class="nav-item">
                <button class="nav-link active" id="tab-equipo-tab" data-bs-toggle="tab" data-bs-target="#tab-equipo" type="button" role="tab" aria-controls="tab-equipo" aria-selected="true" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-mobile-alt me-1" style="color:#a855f7;"></i>📱 Equipo
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-diagnostico-tab" data-bs-toggle="tab" data-bs-target="#tab-diagnostico" type="button" role="tab" aria-controls="tab-diagnostico" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-stethoscope me-1" style="color:#a855f7;"></i>🔍 Diagnóstico
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-costos-tab" data-bs-toggle="tab" data-bs-target="#tab-costos" type="button" role="tab" aria-controls="tab-costos" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-dollar-sign me-1" style="color:#a855f7;"></i>💰 Costos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-fotos-tab" data-bs-toggle="tab" data-bs-target="#tab-fotos" type="button" role="tab" aria-controls="tab-fotos" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-camera me-1" style="color:#a855f7;"></i>📷 Fotos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-firmas-tab" data-bs-toggle="tab" data-bs-target="#tab-firmas" type="button" role="tab" aria-controls="tab-firmas" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-pen me-1" style="color:#a855f7;"></i>✍️ Firmas
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="reparacionTabsContent">

            {{-- Pestaña: Equipo --}}
            <div class="tab-pane fade show active" id="tab-equipo" role="tabpanel" aria-labelledby="tab-equipo-tab">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">TIPO</div>
                            <div style="font-weight:600; font-size:13px;">{{ $tipos[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?: '—' }}</div>
                        </div>
                    </div>
                    @if($reparacion->dispositivo)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">DISPOSITIVO</div>
                            <div style="font-weight:600; font-size:13px;">{{ $reparacion->dispositivo }}</div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->marca || $reparacion->modelo)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">MARCA / MODELO</div>
                            <div style="font-weight:600; font-size:13px;">{{ $reparacion->marca ?? '' }}@if($reparacion->modelo) / {{ $reparacion->modelo }}@endif</div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->color)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">COLOR</div>
                            <div style="font-weight:600; font-size:13px;">{{ $reparacion->color }}</div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->imei)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">IMEI / SERIE</div>
                            <div style="font-weight:600; font-size:12px; word-break:break-all;">{{ $reparacion->imei }}</div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->tipo_codigo === 'pin' && $reparacion->codigo_equipo)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">PIN</div>
                            <div style="font-weight:600; font-size:13px;">{{ $reparacion->codigo_equipo }}</div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->tipo_codigo === 'patron' && $reparacion->patron_secuencia)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">PATRÓN</div>
                            <div style="font-weight:600; font-size:12px;">{{ $reparacion->patron_secuencia }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Pestaña: Diagnóstico --}}
            <div class="tab-pane fade" id="tab-diagnostico" role="tabpanel" aria-labelledby="tab-diagnostico-tab">
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-size:11px; color:#9ca3af;">FALLA REPORTADA</span>
                        <span class="info-badge" style="background:#fef3c7; color:#92400e;">Cliente</span>
                    </div>
                    <div class="p-3 rounded-3" style="background:#fef3c7; font-size:13.5px; border-left:3px solid #f59e0b;">
                        {{ $reparacion->falla_reportada }}
                    </div>
                </div>
                @if($reparacion->diagnostico)
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-size:11px; color:#9ca3af;">DIAGNÓSTICO TÉCNICO</span>
                        <span class="info-badge" style="background:#e0f2fe; color:#0369a1;">Técnico</span>
                    </div>
                    <div class="p-3 rounded-3" style="background:#e0f2fe; font-size:13.5px; border-left:3px solid #0ea5e9;">
                        {{ $reparacion->diagnostico }}
                    </div>
                </div>
                @endif
                @if($reparacion->solucion)
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-size:11px; color:#9ca3af;">SOLUCIÓN APLICADA</span>
                        <span class="info-badge" style="background:#d1fae5; color:#065f46;">Técnico</span>
                    </div>
                    <div class="p-3 rounded-3" style="background:#d1fae5; font-size:13.5px; border-left:3px solid #10b981;">
                        {{ $reparacion->solucion }}
                    </div>
                </div>
                @endif
                @if($reparacion->notas)
                <div class="p-3 rounded-3 d-flex align-items-start gap-2" style="background:#f9fafb; font-size:13px; color:#6b7280;">
                    <i class="fas fa-sticky-note mt-1" style="color:#a855f7;"></i>
                    <div>{{ $reparacion->notas }}</div>
                </div>
                @endif
            </div>

            {{-- Pestaña: Costos --}}
            <div class="tab-pane fade" id="tab-costos" role="tabpanel" aria-labelledby="tab-costos-tab">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">PRESUPUESTO</div>
                            <div style="font-weight:700; font-size:16px; color:#7c3aed;">{{ $empresa->simbolo_moneda ?? '$' }} {{ $reparacion->presupuesto ? number_format($reparacion->presupuesto, 2) : '0.00' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#d1fae5;">
                            <div style="font-size:10px; color:#065f46;">COSTO FINAL</div>
                            <div style="font-weight:700; font-size:16px; color:#059669;">{{ $empresa->simbolo_moneda ?? '$' }} {{ $reparacion->costo_final ? number_format($reparacion->costo_final, 2) : '0.00' }}</div>
                        </div>
                    </div>
                    @if($reparacion->costo_repuesto > 0)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f3f4f6;">
                            <div style="font-size:10px; color:#6b7280;">REPUESTOS</div>
                            <div style="font-weight:600; font-size:14px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($reparacion->costo_repuesto, 2) }}</div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->abono > 0)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#fef3c7;">
                            <div style="font-size:10px; color:#92400e;">ABONO</div>
                            <div style="font-weight:600; font-size:14px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($reparacion->abono, 2) }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#fef3c7; border:2px solid #f59e0b;">
                            <div style="font-size:10px; color:#92400e; font-weight:600;">SALDO</div>
                            <div style="font-weight:700; font-size:17px; color:#92400e;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($reparacion->total, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pestaña: Fotos --}}
            <div class="tab-pane fade" id="tab-fotos" role="tabpanel" aria-labelledby="tab-fotos-tab">
                <div class="row g-2" id="fotosGallery">
                    @forelse($reparacion->fotos as $foto)
                    <div class="col-3" data-foto-id="{{ $foto->id }}">
                        <div class="foto-item">
                            <button type="button" class="foto-ver" onclick="abrirLightbox('{{ asset('storage/'.$foto->ruta) }}')" aria-label="Ver foto {{ $foto->tipo }}">
                                <img src="{{ asset('storage/'.$foto->ruta) }}" alt="Foto {{ $foto->tipo }}" style="cursor:pointer;">
                            </button>
                            <span class="foto-tipo">{{ ucfirst($foto->tipo) }}</span>
                            <button class="foto-delete" onclick="eliminarFoto({{ $foto->id }})" title="Eliminar foto">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-3">
                            <i class="fas fa-image" style="font-size:28px; color:#d1d5db; display:block; margin-bottom:6px;"></i>
                            <p class="text-muted mb-0" style="font-size:12px;">No hay fotos de evidencia aún.</p>
                        </div>
                    </div>
                    @endforelse
                    <div class="col-3">
                        <label for="fotoInput" class="foto-upload-box" style="display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer;">
                            <i class="fas fa-plus" style="font-size:20px;"></i>
                            <span style="font-size:10px; margin-top:3px;">Agregar</span>
                        </label>
                        <form id="fotoUploadForm" enctype="multipart/form-data" style="display:none;">
                            @csrf
                            <input type="file" id="fotoInput" name="foto" accept="image/*" capture="environment"
                                   onchange="subirFoto(this)">
                            <label for="fotoTipo" class="visually-hidden">Tipo de foto</label>
                            <select id="fotoTipo" name="tipo">
                                <option value="frontal">Frontal</option>
                                <option value="trasero">Trasero</option>
                                <option value="detalle">Detalle</option>
                                <option value="imei">IMEI</option>
                                <option value="general">General</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div id="fotoUploadProgress" style="display:none; margin-top:8px;">
                    <div class="progress" style="height:5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%; background:#a855f7;"></div>
                    </div>
                    <p class="text-muted mt-1" style="font-size:11px;">Subiendo foto...</p>
                </div>
            </div>

            {{-- Pestaña: Firmas --}}
            <div class="tab-pane fade" id="tab-firmas" role="tabpanel" aria-labelledby="tab-firmas-tab">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2" style="font-size:13px;">Firma de Recepción</h6>
                        @if($reparacion->firma_recepcion)
                            <div class="text-center">
                                <img src="{{ asset('storage/'.$reparacion->firma_recepcion) }}" alt="Firma de recepción"
                                     style="max-width:100%; max-height:120px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                                <p class="text-muted mt-1 mb-0" style="font-size:11px;">✓ Firma registrada al recibir el equipo</p>
                            </div>
                        @else
                            <p class="text-muted" style="font-size:12px;">No hay firma de recepción registrada.</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2" style="font-size:13px;">Firma de Entrega</h6>
                        @if($reparacion->firma_entrega)
                            <div class="text-center">
                                <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma de entrega"
                                     style="max-width:100%; max-height:120px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                                <p class="text-muted mt-1 mb-0" style="font-size:11px;">✓ Firma registrada al entregar el equipo</p>
                            </div>
                        @else
                            <p class="text-muted" style="font-size:12px;">No hay firma de entrega registrada.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Lightbox --}}
<div class="lightbox-overlay" id="lightbox" role="dialog" aria-modal="true" aria-label="Visor de fotos" tabindex="-1">
    <button type="button" class="close" aria-label="Cerrar">&times;</button>
    <img id="lightboxImg" src="" alt="Foto">
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" integrity="sha384-dPowQo9uxJU703klzvnG+vzLHQDNmO/zREXw6BhCMupB54CE70wj6SWOGVPySK3s" crossorigin="anonymous"></script>
<script>
// ── CONFIGURACIÓN ──
const reparacionId = {{ $reparacion->id }};

// ── FIRMA: Inicializar pads ──
let sigPadRecepcion = null;
let sigPadEntrega = null;

function initSignaturePads() {
    const canvasR = document.getElementById('sigCanvasRecepcion');
    if (canvasR) {
        const wrapperR = document.getElementById('sigPadRecepcionWrapper');
        canvasR.width = wrapperR.clientWidth || 300;
        canvasR.height = 140;
        sigPadRecepcion = new SignaturePad(canvasR, { backgroundColor: 'rgb(255,255,255)' });
        sigPadRecepcion.addEventListener('beginStroke', () => {
            wrapperR.querySelector('.placeholder')?.style?.setProperty('display', 'none');
        });
    }

    const canvasE = document.getElementById('sigCanvasEntrega');
    if (canvasE) {
        const wrapperE = document.getElementById('sigPadEntregaWrapper');
        canvasE.width = wrapperE.clientWidth || 300;
        canvasE.height = 140;
        sigPadEntrega = new SignaturePad(canvasE, { backgroundColor: 'rgb(255,255,255)' });
        sigPadEntrega.addEventListener('beginStroke', () => {
            wrapperE.querySelector('.placeholder')?.style?.setProperty('display', 'none');
        });
    }
}

function limpiarFirma(tipo) {
    const pad = tipo === 'recepcion' ? sigPadRecepcion : sigPadEntrega;
    if (pad) {
        pad.clear();
        const wrapper = document.getElementById(tipo === 'recepcion' ? 'sigPadRecepcionWrapper' : 'sigPadEntregaWrapper');
        const placeholder = wrapper?.querySelector('.placeholder');
        if (placeholder) placeholder.style.display = '';
    }
}

function guardarFirma(tipo) {
    const pad = tipo === 'recepcion' ? sigPadRecepcion : sigPadEntrega;
    if (!pad) return;

    if (pad.isEmpty()) {
        alert('Por favor, dibuja la firma antes de guardar.');
        return;
    }

    const dataUrl = pad.toDataURL('image/png');

    fetch('{{ route("reparaciones.firma", $reparacion) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            firma: dataUrl,
            tipo: tipo,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo guardar la firma.'));
        }
    })
    .catch(err => {
        alert('Error de conexión al guardar la firma.');
        console.error(err);
    });
}

function cargarFirmaRecepcion() {
    document.querySelector('#signaturePadRecepcion').style.display = 'block';
    const wrapper = document.getElementById('sigPadRecepcionWrapper');
    if (sigPadRecepcion) {
        sigPadRecepcion.clear();
        const placeholder = wrapper?.querySelector('.placeholder');
        if (placeholder) placeholder.style.display = '';
    }
}

// ── FOTOS: Subir foto ──
function subirFoto(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const tipo = document.getElementById('fotoTipo').value;
    const formData = new FormData();
    formData.append('foto', file);
    formData.append('tipo', tipo);

    const progress = document.getElementById('fotoUploadProgress');
    progress.style.display = 'block';

    fetch('{{ route("reparaciones.fotos.subir", $reparacion) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        progress.style.display = 'none';
        if (data.success) {
            // Agregar la foto a la galería
            const gallery = document.getElementById('fotosGallery');
            const col = document.createElement('div');
            col.className = 'col-3';
            col.dataset.fotoId = data.id;
            col.innerHTML = `
                <div class="foto-item">
                    <button type="button" class="foto-ver" onclick="abrirLightbox('${data.url}')" aria-label="Ver foto ${tipo}">
                        <img src="${data.url}" alt="Foto ${tipo}" style="cursor:pointer;">
                    </button>
                    <span class="foto-tipo">${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</span>
                    <button class="foto-delete" onclick="eliminarFoto(${data.id})" title="Eliminar foto">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            // Insertar antes del botón de agregar
            const uploadBox = gallery.querySelector('.foto-upload-box').closest('.col-3');
            gallery.insertBefore(col, uploadBox);
            input.value = '';
        } else {
            alert('Error: ' + (data.message || 'No se pudo subir la foto.'));
        }
    })
    .catch(err => {
        progress.style.display = 'none';
        alert('Error de conexión al subir la foto.');
        console.error(err);
    });
}

function eliminarFoto(fotoId) {
    if (!confirm('¿Eliminar esta foto?')) return;

    fetch(`/reparaciones/fotos/${fotoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const el = document.querySelector(`[data-foto-id="${fotoId}"]`);
            if (el) el.remove();
        } else {
            alert('Error: ' + (data.message || 'No se pudo eliminar la foto.'));
        }
    })
    .catch(err => {
        alert('Error de conexión al eliminar la foto.');
        console.error(err);
    });
}

// ── LIGHTBOX ──
function abrirLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('show');
}

function cerrarLightbox() {
    document.getElementById('lightbox').classList.remove('show');
}

// ── IMPRESIÓN AUTOMÁTICA DEL TICKET DE REPARACIÓN AL CONFIRMAR PAGO ──
@if($reparacion->estado === 'entregado' && $reparacion->total > 0 && (!$ventaReparacion || $ventaReparacion->estado === 'pendiente'))
document.addEventListener('DOMContentLoaded', function() {
    const urlEstadoPago = "{{ route('reparaciones.estado-pago', $reparacion) }}";
    const urlTicket = "{{ route('reparaciones.ticket', $reparacion) }}";
    let impreso = false;

    // Polling cada 5 segundos para detectar el pago confirmado
    const intervalo = setInterval(async () => {
        try {
            const res = await fetch(urlEstadoPago, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            // Cuando el pago se confirma (se crea la venta automática), imprimir el ticket de REPARACIÓN
            if (data.pagado && !impreso) {
                impreso = true;
                clearInterval(intervalo);

                // Abrir el ticket de reparación en una ventana nueva y disparar la impresión
                const win = window.open(urlTicket, '_blank');
                if (win) {
                    win.onload = function () {
                        win.focus();
                        win.print();
                    };
                }
            }
        } catch (e) {
            // Ignorar errores de red temporales
        }
    }, 5000);
});
@endif

// ── Inicializar al cargar ──
document.addEventListener('DOMContentLoaded', function() {
    initSignaturePads();

    // Lightbox: cerrar al hacer clic fuera de la imagen o en el botón cerrar
    const lightbox = document.getElementById('lightbox');
    const closeBtn = lightbox ? lightbox.querySelector('.close') : null;
    if (closeBtn) {
        closeBtn.addEventListener('click', cerrarLightbox);
    }
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                cerrarLightbox();
            }
        });
        // Soporte de teclado: Escape para cerrar
        lightbox.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarLightbox();
            }
        });
    }
});
</script>
@endpush