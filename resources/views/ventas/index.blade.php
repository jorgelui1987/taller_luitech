@extends('layouts.app')
@section('title', 'Ventas')

@section('breadcrumb')
    <ul><li class="breadcrumb-item active">Ventas</li></ul>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Ventas</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Total del mes: <strong style="color:#a855f7;">{{ formatoMoneda($totalMes) }}</strong>
        </p>
    </div>
    <a href="{{ route('ventas.create') }}" class="btn btn-primary px-4">
        <i class="fas fa-plus me-2"></i>Nueva Venta
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="buscar" class="visually-hidden">Buscar venta o cliente</label>
                <input type="text" class="form-control" name="buscar" id="buscar"
                       placeholder="N° venta o cliente..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label for="estado" class="visually-hidden">Estado</label>
                <select class="form-select" name="estado" id="estado">
                    <option value="">Todos los estados</option>
                    <option value="completada" {{ request('estado')=='completada'?'selected':'' }}>Completada</option>
                    <option value="pendiente"  {{ request('estado')=='pendiente'?'selected':'' }}>Pendiente</option>
                    <option value="cancelada"  {{ request('estado')=='cancelada'?'selected':'' }}>Cancelada</option>
                    <option value="devuelta"   {{ request('estado')=='devuelta'?'selected':'' }}>Devuelta</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="fecha_desde" class="visually-hidden">Fecha desde</label>
                <input type="date" class="form-control" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <label for="fecha_hasta" class="visually-hidden">Fecha hasta</label>
                <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">N° Venta</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Fecha</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr>
                        <td class="ps-4">
                            <span style="font-weight:600; color:#a855f7;">{{ $venta->numero_venta }}</span>
                        </td>
                        <td style="font-size:13px;">
                            <div>{{ $venta->cliente?->nombre_completo ?? '—' }}</div>
                            <div style="font-size:11px; color:#9ca3af;">{{ $venta->cliente?->telefono ?? '' }}</div>
                        </td>
                        <td style="font-size:13px; color:#6b7280;">{{ $venta->vendedor->name ?? '—' }}</td>
                        <td style="font-size:12px;">
                            <div>{{ $venta->fecha_venta->format('d/m/Y') }}</div>
                            <div style="color:#9ca3af;">{{ $venta->fecha_venta->format('H:i') }}</div>
                        </td>
                        <td>
                            <span style="font-size:12px;">
                                @php
                                    $iconos = ['efectivo'=>'💵','tarjeta'=>'💳','transferencia'=>'🏦','cuotas'=>'📅','mercadopago'=>'🟦'];
                                @endphp
                                {{ $iconos[$venta->metodo_pago] ?? '' }} {{ ucfirst($venta->metodo_pago) }}
                            </span>
                        </td>
                        <td style="font-weight:700; color:#1e1b4b;">
                            {{ formatoMoneda($venta->total) }}
                        </td>
                        <td>
                            @php
                                $cfg = [
                                    'completada' => ['bg'=>'#d1fae5','color'=>'#065f46'],
                                    'pendiente'  => ['bg'=>'#fef3c7','color'=>'#92400e'],
                                    'cancelada'  => ['bg'=>'#fee2e2','color'=>'#991b1b'],
                                    'devuelta'   => ['bg'=>'#e5e7eb','color'=>'#374151'],
                                ];
                                $c = $cfg[$venta->estado] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                            @endphp
                            <span style="background:{{ $c['bg'] }}; color:{{ $c['color'] }};
                                border-radius:20px; padding:4px 10px; font-size:11px; font-weight:500;">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('ventas.show', $venta) }}"
                                   class="btn btn-sm" style="background:#ede9fe; color:#7c3aed; border-radius:8px; padding:5px 10px;">
                                    <i class="fas fa-eye fa-sm"></i>
                                </a>
                                @if(in_array($venta->estado, ['completada', 'pendiente']))
                                <button type="button" class="btn btn-sm btn-cancelar-venta"
                                        style="background:#fee2e2; color:#dc2626; border-radius:8px; padding:5px 10px;"
                                        data-numero="{{ $venta->numero_venta }}"
                                        data-url="{{ route('ventas.cancelar', $venta) }}"
                                        data-bs-toggle="modal" data-bs-target="#modalCancelarVentaIndex">
                                    <i class="fas fa-ban fa-sm"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x mb-3 d-block" style="color:#d1d5db;"></i>
                            <p class="text-muted mb-2">No hay ventas registradas</p>
                            <a href="{{ route('ventas.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Registrar primera venta
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ventas->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted" style="font-size:13px;">
                Mostrando {{ $ventas->firstItem() }}–{{ $ventas->lastItem() }} de {{ $ventas->total() }} ventas
            </span>
            {{ $ventas->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Cancelar Venta (desde listado) --}}
<div class="modal fade" id="modalCancelarVentaIndex" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                <h6 class="modal-title fw-bold" style="color:#dc2626;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Cancelar Venta <span id="lblNumeroVenta"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCancelarVentaIndex" action="" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Se restaurará el stock de los productos y la venta quedará marcada como cancelada.
                    </p>
                    <label for="motivo_cancelacion_index" class="form-label">
                        Motivo de cancelación <span class="text-danger">*</span>
                    </label>
                    <textarea name="motivo_cancelacion" id="motivo_cancelacion_index" class="form-control" rows="3"
                              required maxlength="500" placeholder="Ej: Error al registrar, devolución, cliente no pagó..."></textarea>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-ban me-2"></i>Confirmar Cancelación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-cancelar-venta').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var numero = this.dataset.numero;
            var url = this.dataset.url;
            document.getElementById('lblNumeroVenta').textContent = '— ' + numero;
            document.getElementById('formCancelarVentaIndex').action = url;
            document.getElementById('motivo_cancelacion_index').value = '';
        });
    });
});
</script>
@endpush
@endsection
