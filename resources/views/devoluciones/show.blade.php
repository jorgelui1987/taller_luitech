@extends('layouts.app')
@section('title', 'Devolución ' . $devolucion->numero_devolucion)

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('devoluciones.index') }}">Devoluciones</a></li></ul>
    <ul><li class="breadcrumb-item active">{{ $devolucion->numero_devolucion }}</li></ul>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $motivoBadge = [
        'garantia'        => ['🛡️ Garantía', '#e0f2fe', '#0369a1'],
        'defecto'         => ['⚠️ Defecto', '#fee2e2', '#dc2626'],
        'cambio_opinion'  => ['💭 Cambio de opinión', '#fef3c7', '#92400e'],
        'error_venta'     => ['❌ Error de venta', '#f3e8ff', '#7c3aed'],
        'otro'            => ['📦 Otro', '#f3f4f6', '#374151'],
    ];
    $mot = $motivoBadge[$devolucion->motivo] ?? ['📦 Otro', '#f3f4f6', '#374151'];
    $esAnulada = $devolucion->estado === 'anulada';
@endphp

<!-- ── Header ── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e1b4b;">
            <i class="fas fa-undo-alt me-2" style="color:#a855f7;"></i>{{ $devolucion->numero_devolucion }}
        </h4>
        <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge" style="background:{{ $mot[1] }};color:{{ $mot[2] }};font-size:11px;">{{ $mot[0] }}</span>
            @if($esAnulada)
                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px;">Anulada</span>
            @else
                <span class="badge" style="background:#d1fae5;color:#065f46;font-size:11px;">Completada</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
        @if(!$esAnulada)
            <form action="{{ route('devoluciones.anular', $devolucion) }}" method="POST"
                  onsubmit="return confirm('¿Anular esta devolución? Se restará el stock devuelto y el historial quedará como anulado.')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-ban me-1"></i> Anular Devolución
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- ════════ COLUMNA IZQUIERDA: Información ════════ -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2" style="color:#a855f7;"></i>Detalle de la Devolución</h6>
                <div style="font-size:13px;">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">N° Devolución</span>
                        <strong>{{ $devolucion->numero_devolucion }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Venta original</span>
                        <a href="{{ route('ventas.show', $devolucion->venta) }}" class="text-decoration-none">{{ $devolucion->venta->numero_venta ?? '—' }}</a>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Cliente</span>
                        <span>{{ $devolucion->cliente->nombre_completo ?? 'Venta general' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Fecha</span>
                        <span>{{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Registrado por</span>
                        <span>{{ $devolucion->usuario->name ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Motivo</span>
                        <span>{{ $mot[0] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Reembolso</span>
                        <span>
                            @php
                                $reembolsos = ['efectivo'=>'💵 Efectivo','tarjeta'=>'💳 Tarjeta','transferencia'=>'🏦 Transferencia','nota_credito'=>'📄 Nota de crédito'];
                            @endphp
                            {{ $devolucion->tipo_reembolso ? ($reembolsos[$devolucion->tipo_reembolso] ?? $devolucion->tipo_reembolso) : '—' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Estado</span>
                        @if($esAnulada)
                            <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px;">Anulada</span>
                        @else
                            <span class="badge" style="background:#d1fae5;color:#065f46;font-size:11px;">Completada</span>
                        @endif
                    </div>
                </div>

                @if($devolucion->observacion)
                    <hr>
                    <div class="mb-1" style="font-weight:600;font-size:12px;">Nota / Observación</div>
                    <p class="text-muted mb-0" style="font-size:13px;">{{ $devolucion->observacion }}</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-money-bill-wave me-2" style="color:#10b981;"></i>Resumen de la Devolución</h6>
                <div style="font-size:13px;">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Subtotal</span>
                        <strong>{{ number_format($devolucion->subtotal, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Descuento</span>
                        <strong>-{{ number_format($devolucion->descuento, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">IGV</span>
                        <strong>{{ number_format($devolucion->impuesto, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-top:2px solid #e9d5ff;margin-top:4px;">
                        <span style="font-weight:600;">Total devuelto</span>
                        <strong style="color:#7c3aed;font-size:16px;">{{ number_format($devolucion->total, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════ COLUMNA DERECHA: Productos devueltos ════════ -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-box me-2" style="color:#a855f7;"></i>Productos Devueltos</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Condición</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devolucion->detalles as $det)
                                @php
                                    $condiciones = ['nuevo'=>'🆕 Nuevo','usado'=>'👌 Usado','dañado'=>'💥 Dañado','incompleto'=>'📦 Incompleto'];
                                    $condicion = $condiciones[$det->condicion] ?? $det->condicion;
                                @endphp
                                <tr>
                                    <td style="font-weight:500;">{{ $det->producto->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="text-center"><strong>{{ $det->cantidad }}</strong></td>
                                    <td class="text-end">{{ number_format($det->precio_unitario, 2) }}</td>
                                    <td class="text-end">{{ $det->descuento > 0 ? '-'.number_format($det->descuento, 2) : '—' }}</td>
                                    <td class="text-end"><strong>{{ number_format($det->subtotal, 2) }}</strong></td>
                                    <td class="text-center" style="font-size:12px;">{{ $condicion }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection