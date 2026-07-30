@extends('layouts.app')
@section('title', 'Movimientos - ' . $producto->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li>
    <li class="breadcrumb-item"><a href="{{ route('productos.show', $producto) }}" style="color:#a855f7;">{{ $producto->nombre }}</a></li>
    <li class="breadcrumb-item active">Movimientos de Stock</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Movimientos: {{ $producto->nombre }}</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Código: {{ $producto->codigo }} · Stock actual: <strong>{{ $producto->stock }}</strong>
        </p>
    </div>
    <a href="{{ route('stock.movimientos') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Movimientos
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Fecha / Hora</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th class="text-center">Stock Anterior</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Stock Nuevo</th>
                        <th>Usuario</th>
                        <th class="pe-4">Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                    <tr>
                        <td class="ps-4" style="font-size:12px; color:#6b7280;">
                            {{ $mov->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if($mov->tipo === 'entrada')
                                <span style="background:#d1fae5; color:#065f46; border-radius:20px; padding:3px 10px; font-size:11px;">
                                    <i class="fas fa-arrow-down fa-xs me-1"></i>Entrada
                                </span>
                            @elseif($mov->tipo === 'salida')
                                <span style="background:#fee2e2; color:#dc2626; border-radius:20px; padding:3px 10px; font-size:11px;">
                                    <i class="fas fa-arrow-up fa-xs me-1"></i>Salida
                                </span>
                            @else
                                <span style="background:#fef3c7; color:#92400e; border-radius:20px; padding:3px 10px; font-size:11px;">
                                    <i class="fas fa-adjust fa-xs me-1"></i>Ajuste
                                </span>
                            @endif
                        </td>
                        <td style="font-size:13px;">{{ $mov->motivo_label }}</td>
                        <td class="text-center" style="font-size:13px;">{{ $mov->stock_anterior }}</td>
                        <td class="text-center">
                            <span style="font-weight:600; font-size:14px;
                                {{ $mov->cantidad > 0 ? 'color:#059669;' : 'color:#dc2626;' }}">
                                {{ $mov->cantidad > 0 ? '+'.$mov->cantidad : $mov->cantidad }}
                            </span>
                        </td>
                        <td class="text-center" style="font-size:13px; font-weight:600;">{{ $mov->stock_nuevo }}</td>
                        <td style="font-size:12px;">{{ $mov->user->name ?? '—' }}</td>
                        <td class="pe-4" style="font-size:12px; color:#6b7280; max-width:200px;">
                            {{ $mov->observacion ? Str::limit($mov->observacion, 80) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x mb-3 d-block" style="color:#d1d5db;"></i>
                            <p class="text-muted mb-0">Este producto no tiene movimientos registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
        <div class="p-3 border-top">
            {{ $movimientos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection