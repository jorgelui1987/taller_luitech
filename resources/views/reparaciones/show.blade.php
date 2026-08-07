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

<div class="row g-4">
    {{-- ============================================================ --}}
    {{-- COLUMNA PRINCIPAL (EQUIPO + DIAGNÓSTICO + FOTOS) --}}
    {{-- ============================================================ --}}
    <div class="col-lg-8">

        {{-- Acordeón móvil: Datos del Equipo --}}
        <div class="accordion-mobile">
            <div class="accordion-item mb-4">
                <div class="accordion-header active">
                    <span><i class="fas fa-mobile-alt me-2" style="color:#a855f7;"></i>Datos del Equipo</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body show">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        @php
                            $tipos = ['celular'=>'📱 Celular','tablet'=>'📟 Tablet','portatil'=>'💻 Portátil','otros'=>'🔧 Otros'];
                        @endphp
                        <span class="info-badge" style="background:#f3f4f6; color:#374151;">
                            {{ $tipos[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?: '—' }}
                        </span>
                    </div>
                    <div class="row g-3" style="font-size:13.5px;">
                        <div class="col-md-4 col-6">
                            <span class="text-muted d-block" style="font-size:11px;">DISPOSITIVO</span>
                            <strong>{{ $reparacion->dispositivo ?: '—' }}</strong>
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="text-muted d-block" style="font-size:11px;">MARCA / MODELO</span>
                            <strong>{{ $reparacion->marca ?: '—' }}@if($reparacion->modelo) / {{ $reparacion->modelo }}@endif</strong>
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="text-muted d-block" style="font-size:11px;">🎨 COLOR</span>
                            <strong>{{ $reparacion->color ?: '—' }}</strong>
                        </div>
                        @if($reparacion->imei)
                        <div class="col-md-4 col-6">
                            <span class="text-muted d-block" style="font-size:11px;">🔢 IMEI / SERIE</span>
                            <strong style="font-size:12px; word-break:break-all;">{{ $reparacion->imei }}</strong>
                        </div>
                        @endif
                        <div class="col-md-4 col-6">
                            <span class="text-muted d-block" style="font-size:11px;">🔐 PATRÓN / PIN</span>
                            <strong>
                            @php
                                $tiposCodigo = ['patron'=>'🔓 Patrón','pin'=>'🔢 PIN'];
                                $tipoMostrar = $tiposCodigo[$reparacion->tipo_codigo] ?? '';
                            @endphp
                            @if($reparacion->tipo_codigo === 'patron' && $reparacion->patron_secuencia)
                                {{ $tipoMostrar }}
                                <div style="display:flex; gap:2px; flex-wrap:wrap; max-width:110px; margin-top:3px;">
                                    @for($i=1;$i<=9;$i++)
                                        @php
                                            $nums = explode('-', $reparacion->patron_secuencia);
                                            $pos = array_search($i, $nums);
                                            $esSeleccionado = $pos !== false;
                                        @endphp
                                        <div style="width:26px; height:26px; border-radius:50%;
                                            {{ $esSeleccionado ? 'background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;' : 'background:#f8f5ff;color:#a855f7;border:2px solid #a855f7;' }}
                                            display:flex; align-items:center; justify-content:center;
                                            font-size:10px; font-weight:600;">
                                            {{ $esSeleccionado ? $pos + 1 : $i }}
                                        </div>
                                    @endfor
                                </div>
                                <div style="font-size:10px; color:#6b7280; margin-top:2px;">Secuencia: {{ $reparacion->patron_secuencia }}</div>
                            @elseif($reparacion->tipo_codigo === 'pin')
                                {{ $tipoMostrar }}: {{ $reparacion->codigo_equipo ?: '—' }}
                            @else
                                —
                            @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acordeón móvil: Diagnóstico --}}
        <div class="accordion-mobile">
            <div class="accordion-item mb-4">
                <div class="accordion-header active">
                    <span><i class="fas fa-stethoscope me-2" style="color:#a855f7;"></i>Diagnóstico</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body show">
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
                    <div class="mt-3 p-3 rounded-3 d-flex align-items-start gap-2" style="background:#f9fafb; font-size:13px; color:#6b7280;">
                        <i class="fas fa-sticky-note mt-1" style="color:#a855f7;"></i>
                        <div>{{ $reparacion->notas }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Acordeón móvil: Fotos de Evidencia --}}
        <div class="accordion-mobile">
            <div class="accordion-item mb-4">
                <div class="accordion-header active">
                    <span><i class="fas fa-camera me-2" style="color:#a855f7;"></i>Fotos de Evidencia @if($reparacion->fotos->count() > 0)<span class="info-badge ms-2" style="background:#f3f4f6; color:#374151;">{{ $reparacion->fotos->count() }}</span>@endif</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body show">
                    <div class="row g-2" id="fotosGallery">
                        @forelse($reparacion->fotos as $foto)
                        <div class="col-3" data-foto-id="{{ $foto->id }}">
                            <div class="foto-item">
                                <img src="{{ asset('storage/'.$foto->ruta) }}" alt="Foto {{ $foto->tipo }}"
                                     onclick="abrirLightbox(this.src)" style="cursor:pointer;"
                                     onkeypress="if(event.key==='Enter'||event.key===' '){abrirLightbox(this.src);}" tabindex="0">
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
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- COLUMNA LATERAL (ESTADO + COSTOS + FIRMAS) --}}
    {{-- ============================================================ --}}
    <div class="col-lg-4">

        {{-- Acordeón móvil: Estado y Cliente --}}
        <div class="accordion-mobile">
            <div class="accordion-item mb-3">
                <div class="accordion-header active">
                    <span><i class="fas fa-info-circle me-2" style="color:#a855f7;"></i>Estado y Cliente</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body show">
                    @php
                        $stColors = ['recibido'=>['#ede9fe','#6d28d9'],'en_diagnostico'=>['#e0f2fe','#0369a1'],'esperando_repuesto'=>['#fef9c3','#92400e'],'en_reparacion'=>['#dbeafe','#1d4ed8'],'listo'=>['#d1fae5','#065f46'],'entregado'=>['#f3f4f6','#374151'],'no_reparable'=>['#fee2e2','#991b1b']];
                        $sc = $stColors[$reparacion->estado] ?? ['#f3f4f6','#374151'];
                        $priCol = ['urgente'=>['#fee2e2','#991b1b','🔴'],'alta'=>['#ffedd5','#9a3412','🟠'],'media'=>['#fef9c3','#713f12','🟡'],'baja'=>['#d1fae5','#065f46','🟢']];
                        $pr = $priCol[$reparacion->prioridad] ?? ['#f3f4f6','#374151','⚪'];
                        $diasTranscurridos = $reparacion->fecha_recepcion ? now()->diffInDays($reparacion->fecha_recepcion) : 0;
                    @endphp

                    <div class="text-center mb-3">
                        <span style="background:{{ $sc[0] }}; color:{{ $sc[1] }}; border-radius:20px; padding:8px 20px; font-size:13px; font-weight:600; display:inline-block;">
                            {{ str_replace('_',' ',ucfirst($reparacion->estado)) }}
                        </span>
                    </div>

                    <div class="text-center mb-3">
                        <span class="info-badge" style="background:#f3f4f6; color:#6b7280;">
                            <i class="far fa-clock me-1"></i>{{ $diasTranscurridos }} día(s) desde recepción
                        </span>
                    </div>

                    @php
                        $ordenEstados = ['recibido','en_diagnostico','esperando_repuesto','en_reparacion','listo','entregado'];
                        $idxActual = array_search($reparacion->estado, $ordenEstados);
                        $etiquetasCortas = ['recibido'=>'Rec','en_diagnostico'=>'Diag','esperando_repuesto'=>'Rep','en_reparacion'=>'Rep','listo'=>'Listo','entregado'=>'Ent'];
                    @endphp
                    <div class="d-flex justify-content-between mb-1" style="font-size:9px; color:#9ca3af; padding:0 2px;">
                        @foreach($ordenEstados as $i => $est)
                            <span>{{ $etiquetasCortas[$est] }}</span>
                        @endforeach
                    </div>
                    <div class="timeline-bar">
                        @foreach($ordenEstados as $i => $est)
                            @php
                                $clase = '';
                                if ($i < $idxActual) $clase = 'done';
                                elseif ($i == $idxActual) $clase = 'current';
                                if ($i <= $idxActual) $clase .= ' active';
                            @endphp
                            <div class="timeline-step {{ $clase }}">
                                @if($i <= $idxActual)
                                    <div class="dot"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                        <span class="text-muted">Prioridad</span>
                        <span class="info-badge" style="background:{{ $pr[0] }}; color:{{ $pr[1] }};">
                            {{ $pr[2] }} {{ ucfirst($reparacion->prioridad) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                        <span class="text-muted">Recibido</span>
                        <span>{{ optional($reparacion->fecha_recepcion)->format('d/m/Y') }}</span>
                    </div>
                    @if($reparacion->fecha_estimada)
                    <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                        <span class="text-muted">Fecha estimada</span>
                        <span>{{ $reparacion->fecha_estimada->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($reparacion->fecha_entrega)
                    <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                        <span class="text-muted">Entregado</span>
                        <span style="color:#059669; font-weight:600;">{{ $reparacion->fecha_entrega->format('d/m/Y') }}</span>
                    </div>
                    @endif

                    <hr class="my-3">

                    <h6 class="fw-bold mb-2" style="font-size:13px;">
                        <i class="fas fa-user me-1" style="color:#a855f7;"></i> Cliente
                    </h6>
                    <div style="font-weight:600; font-size:13.5px;">{{ $reparacion->cliente->nombre_completo ?? '—' }}</div>
                    <div style="font-size:12px; color:#9ca3af;">
                        <i class="fas fa-phone me-1"></i>{{ $reparacion->cliente->telefono ?? '' }}
                    </div>
                    @if($reparacion->cliente->email)
                        <div style="font-size:12px; color:#9ca3af;">
                            <i class="fas fa-envelope me-1"></i>{{ $reparacion->cliente->email }}
                        </div>
                    @endif

                    <div class="mt-3 d-grid gap-2">
                        @php
                            use App\Helpers\WhatsAppHelper;
                            $cliente = $reparacion->cliente;
                            $telefonoCliente = WhatsAppHelper::limpiarNumero($cliente->telefono ?? $cliente->celular);
                            $urlRecibido = WhatsAppHelper::generarUrl(
                                $telefonoCliente,
                                WhatsAppHelper::mensajeRecibido($reparacion, $empresa->nombre_tienda ?? 'CRM Celulares')
                            );
                            $urlListo = WhatsAppHelper::generarUrl(
                                $telefonoCliente,
                                WhatsAppHelper::mensajeListo($reparacion, $empresa->nombre_tienda ?? 'CRM Celulares')
                            );
                        @endphp
                        @if($urlRecibido)
                        <div class="d-grid gap-1">
                            <a href="{{ $urlRecibido }}" target="_blank"
                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                <i class="fab fa-whatsapp me-1"></i>📩 Notificar Recibido
                            </a>
                            <a href="{{ $urlListo }}" target="_blank"
                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                <i class="fab fa-whatsapp me-1"></i>📩 Notificar Listo/Entregado
                            </a>
                        </div>
                        @endif
                        <a href="{{ route('reparaciones.edit', $reparacion) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Actualizar Estado
                        </a>
                        <a href="{{ route('clientes.show', $reparacion->cliente_id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user me-1"></i>Ver Cliente
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acordeón móvil: Costos --}}
        <div class="accordion-mobile">
            <div class="accordion-item mb-3">
                <div class="accordion-header active">
                    <span><i class="fas fa-dollar-sign me-2" style="color:#a855f7;"></i>Costos</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body show">
                    <div class="d-flex flex-column gap-2">
                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#f8f5ff;">
                            <span style="font-size:11px; color:#9ca3af;">PRESUPUESTO</span>
                            <span style="font-size:18px; font-weight:700; color:#7c3aed;">
                                S/ {{ $reparacion->presupuesto ? number_format($reparacion->presupuesto, 2) : '0.00' }}
                            </span>
                        </div>
                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#d1fae5;">
                            <span style="font-size:11px; color:#065f46;">COSTO FINAL</span>
                            <span style="font-size:18px; font-weight:700; color:#059669;">
                                S/ {{ $reparacion->costo_final ? number_format($reparacion->costo_final, 2) : '0.00' }}
                            </span>
                        </div>
                        @if($reparacion->costo_repuesto > 0)
                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#f3f4f6;">
                            <span style="font-size:11px; color:#6b7280;">COSTO DE REPUESTO(S)</span>
                            <span style="font-size:16px; font-weight:700; color:#6b7280;">
                                S/ {{ number_format($reparacion->costo_repuesto, 2) }}
                            </span>
                        </div>
                        @endif
                        @if($reparacion->abono > 0)
                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#fef3c7;">
                            <span style="font-size:11px; color:#92400e;">ABONO</span>
                            <span style="font-size:16px; font-weight:700; color:#92400e;">
                                S/ {{ number_format($reparacion->abono, 2) }}
                            </span>
                        </div>
                        @endif
                        @if($reparacion->total > 0)
                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#fef3c7; border:2px solid #f59e0b;">
                            <span style="font-size:11px; color:#92400e; font-weight:600;">SALDO PENDIENTE</span>
                            <span style="font-size:18px; font-weight:700; color:#92400e;">
                                S/ {{ number_format($reparacion->total, 2) }}
                            </span>
                        </div>
                        @endif
                        @if($reparacion->garantia)
                        <div class="p-3 rounded-3 d-flex align-items-center gap-3" style="background:#e0f2fe;">
                            <i class="fas fa-shield-alt" style="color:#0369a1; font-size:18px;"></i>
                            <div>
                                <div style="font-weight:600; color:#0369a1; font-size:13px;">Garantía incluida</div>
                                <div style="font-size:11px; color:#0369a1;">{{ $reparacion->dias_garantia }} días de garantía</div>
                            </div>
                        </div>
                        @endif

                        {{-- Comision del Tecnico: (Presupuesto - Repuesto) x % --}}
                        @if($reparacion->estado === 'entregado' && $reparacion->presupuesto > 0)
                        @php
                            $baseCom = $reparacion->baseComision();
                            $pctCom = $reparacion->comision_porcentaje ?? $reparacion->tecnico?->comision_porcentaje ?? 0;
                            $montoCom = $reparacion->comision_monto ?? round($baseCom * ($pctCom / 100), 2);
                        @endphp
                        <div class="p-3 rounded-3" style="background:#fff7ed; border:2px solid #f59e0b;">
                            <div style="font-weight:700; color:#9a3412; font-size:13px; margin-bottom:8px;">
                                <i class="fas fa-coins me-1"></i>Comision del Tecnico ({{ $reparacion->tecnico->name ?? 'N/A' }})
                            </div>
                            <div style="font-size:12px; display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="color:#6b7280;">Presupuesto</span>
                                <span>S/ {{ number_format($reparacion->presupuesto, 2) }}</span>
                            </div>
                            <div style="font-size:12px; display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="color:#6b7280;">Costo repuesto(s)</span>
                                <span style="color:#dc2626;">- S/ {{ number_format((float)($reparacion->costo_repuesto ?? 0), 2) }}</span>
                            </div>
                            <div style="font-size:12px; display:flex; justify-content:space-between; margin-bottom:6px; font-weight:600;">
                                <span style="color:#6b7280;">Base para comision</span>
                                <span style="color:#9a3412;">S/ {{ number_format($baseCom, 2) }}</span>
                            </div>
                            <hr style="margin:4px 0;">
                            <div style="font-size:12px; display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="color:#6b7280;">% del tecnico</span>
                                <span>{{ $pctCom }}%</span>
                            </div>
                            <div style="font-size:14px; display:flex; justify-content:space-between; font-weight:700;">
                                <span style="color:#9a3412;">Comision del tecnico</span>
                                <span style="color:#f59e0b;">S/ {{ number_format($montoCom, 2) }}</span>
                            </div>
                            @if($reparacion->comision_pagada)
                            <div style="font-size:11px; margin-top:6px; background:#d1fae5; color:#065f46; border-radius:8px; padding:4px 8px; text-align:center;">
                                <i class="fas fa-check-circle me-1"></i>Comision pagada el {{ optional($reparacion->comision_fecha_pago)->format('d/m/Y') }}
                            </div>
                            @else
                            <div style="font-size:11px; margin-top:6px; background:#fee2e2; color:#991b1b; border-radius:8px; padding:4px 8px; text-align:center;">
                                <i class="fas fa-clock me-1"></i>Comision pendiente de pago
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Acordeón móvil: Firmas --}}
        <div class="accordion-mobile">
            <div class="accordion-item mb-3">
                <div class="accordion-header active">
                    <span><i class="fas fa-pen me-2" style="color:#a855f7;"></i>Firmas</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body show">
                    {{-- Firma Recepción --}}
                    <h6 class="fw-bold mb-2" style="font-size:12px;">Firma de Recepción</h6>
                    @if($reparacion->firma_recepcion)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/'.$reparacion->firma_recepcion) }}" alt="Firma de recepción"
                                 style="max-width:100%; max-height:80px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                            <p class="text-muted mt-1 mb-1" style="font-size:11px;">✓ Firma registrada al recibir el equipo</p>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="cargarFirmaRecepcion()">
                                <i class="fas fa-redo me-1"></i>Volver a firmar
                            </button>
                        </div>
                        <div id="signaturePadRecepcion" style="display:none; margin-top:10px;">
                            <div class="signature-pad-wrapper" id="sigPadRecepcionWrapper">
                                <canvas id="sigCanvasRecepcion"></canvas>
                                <div class="placeholder">Firma aquí</div>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirma('recepcion')">
                                    <i class="fas fa-eraser me-1"></i>Limpiar
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="guardarFirma('recepcion')">
                                    <i class="fas fa-check me-1"></i>Guardar Firma
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="signature-pad-wrapper mb-3" id="sigPadRecepcionWrapper">
                            <canvas id="sigCanvasRecepcion"></canvas>
                            <div class="placeholder">Firma aquí</div>
                        </div>
                        <div class="d-flex gap-2 mt-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirma('recepcion')">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="guardarFirma('recepcion')">
                                <i class="fas fa-check me-1"></i>Guardar Firma
                            </button>
                        </div>
                    @endif

                    <hr>

                    {{-- Firma Entrega --}}
                    @if(in_array($reparacion->estado, ['listo', 'entregado']))
                        <h6 class="fw-bold mb-2" style="font-size:12px;">Firma de Entrega</h6>
                        @if($reparacion->firma_entrega)
                            <div class="text-center">
                                <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma de entrega"
                                     style="max-width:100%; max-height:80px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                                <p class="text-muted mt-1" style="font-size:11px;">✓ Firma registrada al entregar el equipo</p>
                            </div>
                        @else
                            <p class="text-muted" style="font-size:12px;">Haz que el cliente firme al entregar el equipo.</p>
                            <div class="signature-pad-wrapper" id="sigPadEntregaWrapper">
                                <canvas id="sigCanvasEntrega"></canvas>
                                <div class="placeholder">Firma aquí</div>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirma('entrega')">
                                    <i class="fas fa-eraser me-1"></i>Limpiar
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="guardarFirma('entrega')">
                                    <i class="fas fa-check me-1"></i>Guardar Firma
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Lightbox --}}
<div class="lightbox-overlay" id="lightbox" role="button" tabindex="0" onkeypress="if(event.key==='Enter'||event.key===' '){this.click();}" onclick="cerrarLightbox()">
    <button class="close">&times;</button>
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
                    <img src="${data.url}" alt="Foto" onclick="abrirLightbox(this.src)" style="cursor:pointer;">
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

// ── Inicializar al cargar ──
document.addEventListener('DOMContentLoaded', function() {
    initSignaturePads();
});
</script>
@endpush