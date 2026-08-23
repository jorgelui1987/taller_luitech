@extends('layouts.app')
@section('title', 'Garantía ' . $garantia->numero_garantia)

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('garantias.index') }}">Garantías</a></li></ul>
    <ul><li class="breadcrumb-item active">{{ $garantia->numero_garantia }}</li></ul>
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
    $esAnulada = $garantia->estado === 'anulada';
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e1b4b;">
            <i class="fas fa-shield-alt me-2" style="color:#10b981;"></i>{{ $garantia->numero_garantia }}
        </h4>
        <div class="d-flex align-items-center gap-2 mt-1">
            @if($esAnulada)
                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px;">Anulada</span>
            @else
                <span class="badge" style="background:#d1fae5;color:#065f46;font-size:11px;">Completada</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('garantias.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
        @if(!$esAnulada)
            <form action="{{ route('garantias.anular', $garantia) }}" method="POST"
                  onsubmit="return confirm('¿Anular esta garantía? Se restará el stock reintegrado.')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-ban me-1"></i> Anular Garantía
                </button>
            </form>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header p-3" style="background:linear-gradient(135deg,#ecfdf5,#f0fdf4); border-bottom:1px solid #d1fae5;">
        <div class="row g-3 align-items-center">
            <div class="col-md-3 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Garantía</div>
                <div style="font-weight:600; font-size:14px;">{{ $garantia->numero_garantia }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Venta</div>
                <div style="font-weight:600; font-size:13px;">{{ $garantia->venta->numero_venta ?? '—' }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
                <div style="font-weight:600; font-size:13px;">{{ $garantia->cliente->nombre_completo ?? 'Venta general' }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Fecha</div>
                <div style="font-weight:600; font-size:13px;">{{ $garantia->fecha_garantia->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2" style="color:#10b981;"></i>Detalle de la Garantía</h6>
                <div style="font-size:13px;">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">N° Garantía</span>
                        <strong>{{ $garantia->numero_garantia }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Venta original</span>
                        <a href="{{ route('ventas.show', $garantia->venta) }}" class="text-decoration-none">{{ $garantia->venta->numero_venta ?? '—' }}</a>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Cliente</span>
                        <span>{{ $garantia->cliente->nombre_completo ?? 'Venta general' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Fecha</span>
                        <span>{{ $garantia->fecha_garantia->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Registrado por</span>
                        <span>{{ $garantia->usuario->name ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Motivo</span>
                        <span>🛡️ Garantía</span>
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

                @if($garantia->observacion)
                    <hr>
                    <div class="mb-1" style="font-weight:600;font-size:12px;">Nota / Observación</div>
                    <p class="text-muted mb-0" style="font-size:13px;">{{ $garantia->observacion }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-box me-2" style="color:#10b981;"></i>Productos en Garantía</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Condición</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($garantia->detalles as $det)
                            @php
                                $condiciones = [
                                    'nuevo'      => ['🆕 Nuevo', '#d1fae5', '#065f46'],
                                    'usado'      => ['👌 Usado', '#fef3c7', '#92400e'],
                                    'dañado'     => ['💥 Dañado', '#fee2e2', '#b91c1c'],
                                    'incompleto' => ['📦 Incompleto', '#fee2e2', '#b91c1c'],
                                ];
                                $cInfo = $condiciones[$det->condicion] ?? ['📦 Otro', '#f3f4f6', '#374151'];
                            @endphp
                            <tr>
                                <td style="font-weight:500;">{{ $det->producto->nombre ?? 'Producto eliminado' }}</td>
                                <td class="text-center"><strong>{{ $det->cantidad }}</strong></td>
                                <td class="text-center">
                                    <span style="background:{{ $cInfo[1] }}; color:{{ $cInfo[2] }}; border-radius:20px; padding:4px 10px; font-size:11px; font-weight:600; display:inline-block;">
                                        {{ $cInfo[0] }}
                                    </span>
                                </td>
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