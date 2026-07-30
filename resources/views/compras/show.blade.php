@extends('layouts.app')
@section('title', $ordenCompra->numero_orden)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('compras.index') }}" style="color:#a855f7;">Órdenes de Compra</a></li>
    <li class="breadcrumb-item active">{{ $ordenCompra->numero_orden }}</li>
@endsection
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">{{ $ordenCompra->numero_orden }}</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Proveedor: <a href="{{ route('proveedores.show', $ordenCompra->proveedor) }}" style="color:#7c3aed;">{{ $ordenCompra->proveedor->nombre }}</a>
            · Fecha: {{ $ordenCompra->fecha_orden->format('d/m/Y') }}
            · Creado por: {{ $ordenCompra->user->name ?? '—' }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array($ordenCompra->estado, ['pendiente','aprobada','enviada','recibida_parcial']))
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-edit me-1"></i>Cambiar Estado
            </button>
            <ul class="dropdown-menu shadow-sm border-0" style="border-radius:12px;font-size:13px;">
                @if($ordenCompra->estado === 'pendiente')
                <li><form action="{{ route('compras.estado', $ordenCompra) }}" method="POST">@csrf<input type="hidden" name="estado" value="aprobada"><button class="dropdown-item" type="submit"><span style="display:inline-block;width:10px;height:10px;background:#06b6d4;border-radius:50%;margin-right:8px;"></span>Aprobar</button></form></li>
                @endif
                @if(in_array($ordenCompra->estado, ['pendiente','aprobada']))
                <li><form action="{{ route('compras.estado', $ordenCompra) }}" method="POST">@csrf<input type="hidden" name="estado" value="cancelada"><button class="dropdown-item text-danger" type="submit"><span style="display:inline-block;width:10px;height:10px;background:#dc2626;border-radius:50%;margin-right:8px;"></span>Cancelar</button></form></li>
                @endif
                @if(in_array($ordenCompra->estado, ['aprobada','enviada','recibida_parcial']))
                <li><form action="{{ route('compras.estado', $ordenCompra) }}" method="POST">@csrf<input type="hidden" name="estado" value="completada"><button class="dropdown-item" type="submit"><span style="display:inline-block;width:10px;height:10px;background:#10b981;border-radius:50%;margin-right:8px;"></span>Marcar como Completada (recibir todo)</button></form></li>
                @endif
            </ul>
        </div>
        @endif
        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Detalle de la Orden</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                        <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-center">Recibido</th><th class="text-center">Pendiente</th><th class="text-end">P. Unitario</th><th class="text-end">Dto.</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach($ordenCompra->detalles as $det)
                            <tr>
                                <td>
                                    <div style="font-weight:500;">{{ $det->producto->nombre ?? '—' }}</div>
                                    <div style="font-size:11px;color:#9ca3af;">{{ $det->producto->codigo ?? '' }}</div>
                                </td>
                                <td class="text-center">{{ $det->cantidad_ordenada }}</td>
                                <td class="text-center">{{ $det->cantidad_recibida }}</td>
                                <td class="text-center">{{ $det->pendiente_recibir }}</td>
                                <td class="text-end">S/ {{ number_format($det->precio_unitario, 2) }}</td>
                                <td class="text-end">{{ $det->descuento > 0 ? 'S/ '.number_format($det->descuento, 2) : '—' }}</td>
                                <td class="text-end" style="font-weight:600;">S/ {{ number_format($det->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Resumen</h6>
                <div style="font-size:13px;">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Estado</span>
                        <span style="background:{{ $ordenCompra->estado_bg }};color:{{ $ordenCompra->estado_color }};border-radius:20px;padding:3px 10px;font-size:11px;">{{ ucfirst(str_replace('_',' ',$ordenCompra->estado)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Subtotal</span>
                        <span>S/ {{ number_format($ordenCompra->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Descuento</span>
                        <span style="color:#dc2626;">- S/ {{ number_format($ordenCompra->descuento, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Impuesto (18%)</span>
                        <span>S/ {{ number_format($ordenCompra->impuesto, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="fw-bold">Total</span>
                        <span style="font-weight:700;font-size:18px;color:#7c3aed;">S/ {{ number_format($ordenCompra->total, 2) }}</span>
                    </div>
                </div>
                @if($ordenCompra->fecha_estimada)
                <hr>
                <div style="font-size:12px;color:#6b7280;">
                    <i class="fas fa-calendar me-1"></i>Fecha estimada: {{ $ordenCompra->fecha_estimada->format('d/m/Y') }}
                </div>
                @endif
                @if($ordenCompra->fecha_recibida)
                <div style="font-size:12px;color:#10b981;">
                    <i class="fas fa-check-circle me-1"></i>Recibida: {{ $ordenCompra->fecha_recibida->format('d/m/Y') }}
                </div>
                @endif
                @if($ordenCompra->notas)
                <hr>
                <div><strong style="font-size:12px;">Notas:</strong><p style="font-size:12px;color:#374151;margin-top:4px;">{{ $ordenCompra->notas }}</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection