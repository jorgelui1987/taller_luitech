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
    </div>
</div>
@endsection
