@extends('layouts.app')
@section('title', 'Orden '.$reparacion->numero_orden)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#a855f7;">Reparaciones</a></li>
    <li class="breadcrumb-item active">{{ $reparacion->numero_orden }}</li>
@endsection

@push('styles')
<style>
.timeline-item { position:relative; padding-left:28px; margin-bottom:20px; }
.timeline-item::before { content:''; position:absolute; left:8px; top:20px; bottom:-20px; width:2px; background:#e5e7eb; }
.timeline-item:last-child::before { display:none; }
.timeline-dot { position:absolute; left:0; top:6px; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
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
    height: 160px;
    border-radius: 12px;
    touch-action: none;
}
.signature-pad-wrapper .placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #9ca3af;
    font-size: 14px;
    pointer-events: none;
}
/* Photo gallery */
.foto-item {
    position: relative;
    border-radius: 12px;
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
    top: 6px;
    left: 6px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 10px;
}
.foto-item .foto-delete {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(239,68,68,0.9);
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: all .2s;
}
.foto-item .foto-delete:hover {
    background: #dc2626;
    transform: scale(1.1);
}
.foto-upload-box {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
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
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 btn-acciones">
    <div>
        <h4 class="mb-1 fw-bold">{{ $reparacion->numero_orden }}</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Recibido el {{ optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') }} ·
            Técnico: <strong>{{ $reparacion->tecnico->name ?? '—' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reparaciones.ticket', $reparacion) }}" target="_blank" class="btn btn-outline-primary px-4">
            <i class="fas fa-receipt me-2"></i>Sticker 80mm
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary px-4">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>
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
    {{-- Detalle principal --}}
    <div class="col-lg-8">
        {{-- Dispositivo --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-mobile-alt me-2" style="color:#a855f7;"></i>Datos del Equipo</h6>
                <div class="row g-3" style="font-size:13.5px;">
                    <div class="col-md-3">
                        <span class="text-muted d-block" style="font-size:11px;">TIPO</span>
                        <strong>@php
                            $tipos = ['celular'=>'📱 Celular','tablet'=>'📟 Tablet','portatil'=>'💻 Portátil','otros'=>'🔧 Otros'];
                        @endphp
                        {{ $tipos[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?: '—' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block" style="font-size:11px;">DISPOSITIVO</span>
                        <strong>{{ $reparacion->dispositivo }}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block" style="font-size:11px;">🔐 PATRÓN / PIN</span>
                        <strong>@php
                            $tiposCodigo = ['patron'=>'🔓 Patrón','pin'=>'🔢 PIN'];
                            $tipoMostrar = $tiposCodigo[$reparacion->tipo_codigo] ?? '';
                        @endphp
                        @if($reparacion->tipo_codigo === 'patron' && $reparacion->patron_secuencia)
                            {{ $tipoMostrar }}
                            <div style="display:flex; gap:2px; flex-wrap:wrap; max-width:130px; margin-top:4px;">
                                @for($i=1;$i<=9;$i++)
                                    @php
                                        $nums = explode('-', $reparacion->patron_secuencia);
                                        $pos = array_search($i, $nums);
                                        $esSeleccionado = $pos !== false;
                                    @endphp
                                    <div style="width:30px; height:30px; border-radius:50%;
                                        {{ $esSeleccionado ? 'background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;' : 'background:#f8f5ff;color:#a855f7;border:2px solid #a855f7;' }}
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:11px; font-weight:600;">
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
                    <div class="col-md-3">
                        <span class="text-muted d-block" style="font-size:11px;">MARCA / MODELO</span>
                        <strong>{{ $reparacion->marca ?: '—' }} {{ $reparacion->modelo ? '/ '.$reparacion->modelo : '' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block" style="font-size:11px;">COLOR</span>
                        <strong>{{ $reparacion->color ?: '—' }}</strong>
                    </div>
                    @if($reparacion->imei)
                    <div class="col-md-3">
                        <span class="text-muted d-block" style="font-size:11px;">IMEI / SERIE</span>
                        <strong>{{ $reparacion->imei }}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Falla / Diagnóstico / Solución --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-stethoscope me-2" style="color:#a855f7;"></i>Diagnóstico</h6>
                <div class="mb-3">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">FALLA REPORTADA POR EL CLIENTE</div>
                    <div class="p-3 rounded-3" style="background:#fef3c7; font-size:13.5px;">
                        {{ $reparacion->falla_reportada }}
                    </div>
                </div>
                @if($reparacion->diagnostico)
                <div class="mb-3">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">DIAGNÓSTICO TÉCNICO</div>
                    <div class="p-3 rounded-3" style="background:#e0f2fe; font-size:13.5px;">
                        {{ $reparacion->diagnostico }}
                    </div>
                </div>
                @endif
                @if($reparacion->solucion)
                <div>
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">SOLUCIÓN APLICADA</div>
                    <div class="p-3 rounded-3" style="background:#d1fae5; font-size:13.5px;">
                        {{ $reparacion->solucion }}
                    </div>
                </div>
                @endif
                @if($reparacion->notas)
                <div class="mt-3 p-3 rounded-3" style="background:#f9fafb; font-size:13px; color:#6b7280;">
                    <i class="fas fa-sticky-note me-1"></i>{{ $reparacion->notas }}
                </div>
                @endif
            </div>
        </div>

        {{-- Costos --}}
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-dollar-sign me-2" style="color:#a855f7;"></i>Costos</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8f5ff; text-align:center;">
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">PRESUPUESTO</div>
                            <div style="font-size:24px; font-weight:700; color:#7c3aed;">
                                S/ {{ $reparacion->presupuesto ? number_format($reparacion->presupuesto, 2) : '0.00' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#d1fae5; text-align:center;">
                            <div style="font-size:11px; color:#065f46; margin-bottom:4px;">COSTO FINAL</div>
                            <div style="font-size:24px; font-weight:700; color:#059669;">
                                S/ {{ $reparacion->costo_final ? number_format($reparacion->costo_final, 2) : '0.00' }}
                            </div>
                        </div>
                    </div>
                    @if($reparacion->abono > 0)
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fef3c7; text-align:center;">
                            <div style="font-size:11px; color:#92400e; margin-bottom:4px;">ABONO RECIBIDO</div>
                            <div style="font-size:20px; font-weight:700; color:#92400e;">
                                S/ {{ number_format($reparacion->abono, 2) }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->total > 0)
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fef3c7; text-align:center;">
                            <div style="font-size:11px; color:#92400e; margin-bottom:4px;">TOTAL</div>
                            <div style="font-size:20px; font-weight:700; color:#92400e;">
                                S/ {{ number_format($reparacion->total, 2) }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reparacion->garantia)
                    <div class="col-12">
                        <div class="p-3 rounded-3 d-flex align-items-center gap-3" style="background:#e0f2fe;">
                            <i class="fas fa-shield-alt" style="color:#0369a1; font-size:20px;"></i>
                            <div>
                                <div style="font-weight:600; color:#0369a1;">Garantía incluida</div>
                                <div style="font-size:12px; color:#0369a1;">{{ $reparacion->dias_garantia }} días de garantía</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div class="col-lg-4">
        {{-- Estado y cliente --}}
        <div class="card mb-3">
            <div class="card-body p-4">
                {{-- Estado actual --}}
                @php
                    $stColors = ['recibido'=>['#ede9fe','#6d28d9'],'en_diagnostico'=>['#e0f2fe','#0369a1'],'esperando_repuesto'=>['#fef9c3','#92400e'],'en_reparacion'=>['#dbeafe','#1d4ed8'],'listo'=>['#d1fae5','#065f46'],'entregado'=>['#f3f4f6','#374151'],'no_reparable'=>['#fee2e2','#991b1b']];
                    $sc = $stColors[$reparacion->estado] ?? ['#f3f4f6','#374151'];
                    $priCol = ['urgente'=>['#fee2e2','#991b1b','🔴'],'alta'=>['#ffedd5','#9a3412','🟠'],'media'=>['#fef9c3','#713f12','🟡'],'baja'=>['#d1fae5','#065f46','🟢']];
                    $pr = $priCol[$reparacion->prioridad] ?? ['#f3f4f6','#374151','⚪'];
                @endphp

                <div class="text-center mb-3">
                    <span style="background:{{ $sc[0] }}; color:{{ $sc[1] }}; border-radius:20px; padding:8px 20px; font-size:13px; font-weight:600; display:inline-block;">
                        {{ str_replace('_',' ',ucfirst($reparacion->estado)) }}
                    </span>
                </div>

                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                    <span class="text-muted">Prioridad</span>
                    <span style="background:{{ $pr[0] }}; color:{{ $pr[1] }}; border-radius:20px; padding:2px 10px; font-size:12px;">
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

                <hr>
                <h6 class="fw-bold mb-2" style="font-size:13px;">Cliente</h6>
                <div style="font-weight:600; font-size:13.5px;">{{ $reparacion->cliente->nombre_completo ?? '—' }}</div>
                <div style="font-size:12px; color:#9ca3af;">{{ $reparacion->cliente->telefono ?? '' }}</div>
                @if($reparacion->cliente->email)
                    <div style="font-size:12px; color:#9ca3af;">{{ $reparacion->cliente->email }}</div>
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

        {{-- 📸 FOTOS DE EVIDENCIA --}}
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-camera me-2" style="color:#a855f7;"></i>Fotos de Evidencia</h6>
                <div class="row g-2" id="fotosGallery">
                    @forelse($reparacion->fotos as $foto)
                    <div class="col-4" data-foto-id="{{ $foto->id }}">
                        <div class="foto-item">
                            <img src="{{ asset('storage/'.$foto->ruta) }}" alt="Foto {{ $foto->tipo }}"
                                 onclick="abrirLightbox(this.src)" style="cursor:pointer;">
                            <span class="foto-tipo">{{ ucfirst($foto->tipo) }}</span>
                            <button class="foto-delete" onclick="eliminarFoto({{ $foto->id }})" title="Eliminar foto">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-muted mb-0" style="font-size:12px;">No hay fotos de evidencia aún.</p>
                    </div>
                    @endforelse
                    {{-- Botón de subir foto --}}
                    <div class="col-4">
                        <div class="foto-upload-box" onclick="document.getElementById('fotoInput').click()">
                            <i class="fas fa-plus" style="font-size:24px;"></i>
                            <span style="font-size:11px; margin-top:4px;">Agregar</span>
                        </div>
                        <form id="fotoUploadForm" enctype="multipart/form-data" style="display:none;">
                            @csrf
                            <input type="file" id="fotoInput" name="foto" accept="image/*" capture="environment"
                                   onchange="subirFoto(this)">
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
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%; background:#a855f7;"></div>
                    </div>
                    <p class="text-muted mt-1" style="font-size:11px;">Subiendo foto...</p>
                </div>
            </div>
        </div>

        {{-- ✍️ FIRMA DE RECEPCIÓN --}}
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-pen me-2" style="color:#a855f7;"></i>Firma de Recepción</h6>
                @if($reparacion->firma_recepcion)
                    {{-- Ya firmado --}}
                    <div class="text-center">
                        <img src="{{ asset('storage/'.$reparacion->firma_recepcion) }}" alt="Firma de recepción"
                             style="max-width:100%; max-height:120px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                        <p class="text-muted mt-2" style="font-size:11px;">✓ Firma registrada al recibir el equipo</p>
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
                    {{-- No firmado aún --}}
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
                @endif
            </div>
        </div>

        {{-- ✍️ FIRMA DE ENTREGA --}}
        @if(in_array($reparacion->estado, ['listo', 'entregado']))
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-pen me-2" style="color:#a855f7;"></i>Firma de Entrega</h6>
                @if($reparacion->firma_entrega)
                    <div class="text-center">
                        <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma de entrega"
                             style="max-width:100%; max-height:120px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                        <p class="text-muted mt-2" style="font-size:11px;">✓ Firma registrada al entregar el equipo</p>
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
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Lightbox --}}
<div class="lightbox-overlay" id="lightbox" onclick="cerrarLightbox()">
    <button class="close">&times;</button>
    <img id="lightboxImg" src="" alt="Foto">
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
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
        canvasR.height = 160;
        sigPadRecepcion = new SignaturePad(canvasR, { backgroundColor: 'rgb(255,255,255)' });
        sigPadRecepcion.addEventListener('beginStroke', () => {
            wrapperR.querySelector('.placeholder')?.style?.setProperty('display', 'none');
        });
    }

    const canvasE = document.getElementById('sigCanvasEntrega');
    if (canvasE) {
        const wrapperE = document.getElementById('sigPadEntregaWrapper');
        canvasE.width = wrapperE.clientWidth || 300;
        canvasE.height = 160;
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
            col.className = 'col-4';
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
            const uploadBox = gallery.querySelector('.foto-upload-box').closest('.col-4');
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