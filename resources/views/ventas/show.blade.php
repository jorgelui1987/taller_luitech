@extends('layouts.app')
@section('title', 'Detalle Venta '.$venta->numero_venta)

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('ventas.index') }}" style="color:#a855f7;">Ventas</a></li></ul>
    <ul><li class="breadcrumb-item active">{{ $venta->numero_venta }}</li></ul>
@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .breadcrumb, .btn-acciones, .page-content > .d-flex { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
    .page-content { padding: 0 !important; }
    .ticket { box-shadow: none !important; border: 1px solid #ddd; }
}
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 btn-acciones">
    <div>
        <h4 class="mb-1 fw-bold">{{ $venta->numero_venta }}</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            {{ $venta->fecha_venta->format('d/m/Y H:i') }} ·
            Atendido por <strong>{{ $venta->vendedor->name ?? '—' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('ventas.ticket', $venta) }}" target="_blank" class="btn btn-outline-primary px-4">
            <i class="fas fa-receipt me-2"></i>Ticket 80mm
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary px-4">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>
        @if($venta->cliente && ($venta->cliente->telefono || $venta->cliente->celular))
        <a href="{{ route('ventas.whatsapp', $venta) }}" target="_blank" class="btn px-4"
           style="background:#25D366; color:#fff; border-radius:8px;">
            <i class="fab fa-whatsapp me-2"></i>Enviar por WhatsApp
        </a>
        @endif
        @if(in_array($venta->estado, ['completada', 'pendiente']))
        <button type="button" class="btn btn-outline-danger px-4" data-bs-toggle="modal" data-bs-target="#modalCancelarVenta">
            <i class="fas fa-ban me-2"></i>Cancelar Venta
        </button>
        @endif
    </div>
</div>

<div class="row g-4">
    {{-- Comprobante --}}
    <div class="col-lg-8">
        <div class="card ticket">
            <div class="card-body p-4">
                {{-- Cabecera del ticket --}}
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    @if(isset($empresa) && $empresa && $empresa->logo)
                        <img src="{{ asset($empresa->logo) }}" alt="Logo" style="width:42px;height:42px;border-radius:10px;object-fit:contain;">
                    @else
                        <div style="width:42px; height:42px; background:linear-gradient(135deg,#a855f7,#ec4899);
                            border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-mobile-alt" style="color:#fff;"></i>
                        </div>
                    @endif
                    <div>
                        <div style="font-weight:700; font-size:16px;">{{ $empresa->nombre_tienda ?? 'CRM Tienda Celulares' }}</div>
                        @if($empresa->ruc)<div style="font-size:11px; color:#9ca3af;">{{ $empresa->pais == 'CL' ? 'RUT' : 'RUC' }}: {{ $empresa->ruc }}</div>@endif
                        <div style="font-size:12px; color:#9ca3af;">Comprobante de Venta</div>
                    </div>
                </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:20px; font-weight:700; color:#a855f7;">{{ $venta->numero_venta }}</div>
                        <div style="font-size:12px; color:#9ca3af;">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</div>
                        @php $cfg=['completada'=>['#d1fae5','#065f46'],'cancelada'=>['#fee2e2','#991b1b'],'pendiente'=>['#fef3c7','#92400e'],'devuelta'=>['#f3f4f6','#374151']]; $c=$cfg[$venta->estado]??['#f3f4f6','#374151']; @endphp
                        <span style="background:{{ $c[0] }}; color:{{ $c[1] }}; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; display:inline-block; margin-top:4px;">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </div>
                </div>

                {{-- Datos del cliente --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                            <div class="p-3 rounded-3" style="background:#f8f5ff;">
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">CLIENTE</div>
                            <div style="font-weight:600;">{{ $venta->cliente?->nombre_completo ?? 'VENTA GENERAL' }}</div>
                            <div style="font-size:12px; color:#6b7280;">{{ $venta->cliente?->telefono ?? '' }}</div>
                            @if($venta->cliente && $venta->cliente->email)
                            <div style="font-size:12px; color:#6b7280;">{{ $venta->cliente->email }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f8f5ff;">
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">PAGO</div>
                            @php $iconos=['efectivo'=>'💵','tarjeta'=>'💳','transferencia'=>'🏦','cuotas'=>'📅','yape'=>'📱','plin'=>'📲','mercadopago'=>'🟦']; @endphp
                            <div style="font-weight:600;">{{ $iconos[$venta->metodo_pago] ?? '' }} {{ ucfirst($venta->metodo_pago) }}</div>
                            <div style="font-size:12px; color:#6b7280;">Vendedor: {{ $venta->vendedor->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Detalle de productos --}}
                <div class="table-responsive mb-4">
                    <table class="table mb-0" style="font-size:13.5px;">
                        <thead>
                            <tr style="border-bottom:2px solid #e9d5ff;">
                                <th style="padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase;">Producto</th>
                                <th style="padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase; text-align:center;">Cant.</th>
                                <th style="padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase; text-align:right;">P. Unit.</th>
                                <th style="padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase; text-align:right;">Descto.</th>
                                <th style="padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase; text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($venta->detalles as $det)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:10px 0;">
                                    <div style="font-weight:500;">{{ $det->producto->nombre ?? '—' }}</div>
                                    @if($det->producto && $det->producto->marca)
                                        <div style="font-size:11px; color:#9ca3af;">{{ $det->producto->marca->nombre }}</div>
                                    @endif
                                    @if($det->imei_vendido)
                                        <div style="font-size:11px; color:#9ca3af;">IMEI: {{ $det->imei_vendido }}</div>
                                    @endif
                                </td>
                                <td style="padding:10px 0; text-align:center;">{{ $det->cantidad }}</td>
                                <td style="padding:10px 0; text-align:right;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($det->precio_unitario, 2) }}</td>
                                <td style="padding:10px 0; text-align:right; color:#dc2626;">
                                    {{ $det->descuento > 0 ? '— '.($empresa->simbolo_moneda ?? '$').' '.number_format($det->descuento,2) : '—' }}
                                </td>
                                <td style="padding:10px 0; text-align:right; font-weight:600;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($det->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totales --}}
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between mb-2" style="font-size:13.5px;">
                            <span class="text-muted">Subtotal</span>
                            <span>{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->subtotal, 2) }}</span>
                        </div>
                        @if($venta->descuento > 0)
                        <div class="d-flex justify-content-between mb-2" style="font-size:13.5px;">
                            <span class="text-muted">Descuento</span>
                            <span class="text-danger">— {{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->descuento, 2) }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between mb-3" style="font-size:13.5px;">
                            <span class="text-muted">{{ $empresa->pais == 'CL' ? 'IVA' : 'IGV' }} ({{ $empresa->igv ?? 18 }}%)</span>
                            <span>{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->impuesto, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between p-3 rounded-3"
                             style="background:linear-gradient(135deg,#a855f7,#ec4899);">
                            <span style="color:#fff; font-weight:700; font-size:16px;">TOTAL</span>
                            <span style="color:#fff; font-weight:700; font-size:20px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($venta->notas)
                <div class="mt-3 p-3 rounded-3" style="background:#f9fafb; font-size:13px; color:#6b7280;">
                    <i class="fas fa-sticky-note me-1"></i><strong>Notas:</strong> {{ $venta->notas }}
                </div>
                @endif

                @if($venta->estado === 'cancelada' && $venta->motivo_cancelacion)
                <div class="mt-3 p-3 rounded-3" style="background:#fee2e2; font-size:13px; color:#991b1b; border:1px solid #fecaca;">
                    <i class="fas fa-ban me-1"></i><strong>Motivo de cancelación:</strong> {{ $venta->motivo_cancelacion }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Acciones Rápidas</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('ventas.ticket', $venta) }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-receipt me-2"></i>Ticket 80mm
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="fas fa-print me-2"></i>Imprimir A4
                    </button>
                    @if($venta->cliente_id)
                    <a href="{{ route('clientes.show', $venta->cliente_id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>Ver Perfil del Cliente
                    </a>
                    @endif
                    <a href="{{ route('ventas.create') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-plus me-2"></i>Nueva Venta
                    </a>
                </div>
            </div>
        </div>

        @if(($empresa->mercadopago_activo ?? false) && in_array($venta->estado, ['completada', 'pendiente']))
        <div class="card mb-3" style="border:1px solid #00b1ea;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#00b1ea;">
                    <i class="fab fa-mercadopago me-2"></i>Mercado Pago
                </h6>
                <p class="text-muted mb-3" style="font-size:12px;">
                    Cobra esta venta con tu terminal Point o genera un QR de pago.
                </p>

                {{-- Botón Cobrar con Point --}}
                <form action="{{ route('ventas.point', $venta) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn w-100" style="background:#00b1ea; color:#fff;">
                        <i class="fas fa-credit-card me-2"></i>Cobrar con Point
                    </button>
                </form>

                {{-- Botón Generar QR (solo si la venta está pendiente de pago MP) --}}
                @if($venta->metodo_pago === 'mercadopago' && $venta->estado === 'pendiente')
                <form action="{{ route('ventas.mercadopago', $venta) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-qrcode me-2"></i>Generar QR de Pago
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        @if(session('point'))
        <div class="card mb-3" style="border:1px solid #00b1ea;">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold mb-3" style="color:#00b1ea;">
                    <i class="fab fa-mercadopago me-2"></i>Cobro enviado al Point
                </h6>
                <div class="mb-2" style="font-size:12px; color:#6b7280;">
                    {{ session('point.mensaje') ?? 'Esperando confirmación del dispositivo...' }}
                </div>
                <div style="font-size:12px; color:#6b7280;">
                    El estado de la venta se actualizará automáticamente al confirmarse el pago.
                </div>
            </div>
        </div>
        @endif

        @if(session('mercadopago'))
        <div class="card mb-3" style="border:1px solid #00b1ea;">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold mb-3" style="color:#00b1ea;">
                    <i class="fab fa-mercadopago me-2"></i>Pago con Mercado Pago
                </h6>
                @if(session('mercadopago.init_point'))
                <div class="mb-3">
                    <div style="font-size:12px; color:#6b7280; margin-bottom:8px;">Escanea el QR o haz clic para pagar:</div>
                    <a href="{{ session('mercadopago.init_point') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Abrir enlace de pago
                    </a>
                </div>
                @endif
                <div style="font-size:12px; color:#6b7280;">
                    El estado de la venta se actualizará automáticamente al confirmarse el pago.
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Resumen</h6>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                    <span class="text-muted">Productos</span>
                    <span class="fw-500">{{ $venta->detalles->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                    <span class="text-muted">Unidades</span>
                    <span class="fw-500">{{ $venta->detalles->sum('cantidad') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                    <span class="text-muted">Método de Pago</span>
                    <span class="fw-500">{{ ucfirst($venta->metodo_pago) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                    <span class="text-muted">Fecha</span>
                    <span class="fw-500">{{ $venta->fecha_venta->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:13px;">
                    <span class="text-muted">Hora</span>
                    <span class="fw-500">{{ $venta->fecha_venta->format('H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cancelar Venta --}}
@if(in_array($venta->estado, ['completada', 'pendiente']))
<div class="modal fade" id="modalCancelarVenta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                <h6 class="modal-title fw-bold" style="color:#dc2626;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Cancelar Venta
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ventas.cancelar', $venta) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Se restaurará el stock de los productos y la venta quedará marcada como cancelada.
                    </p>
                    <label for="motivo_cancelacion" class="form-label">
                        Motivo de cancelación <span class="text-danger">*</span>
                    </label>
                    <textarea name="motivo_cancelacion" id="motivo_cancelacion" class="form-control" rows="3"
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
@endif

{{-- Impresión automática al confirmarse el pago con Mercado Pago --}}
@if($venta->estado === 'pendiente' && $venta->metodo_pago === 'mercadopago')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ventaId = {{ $venta->id }};
    const urlEstado = "{{ route('ventas.estado', $venta) }}";
    const urlTicket = "{{ route('ventas.ticket', $venta) }}";
    let impreso = false;

    // Polling cada 5 segundos para detectar el pago confirmado
    const intervalo = setInterval(async () => {
        try {
            const res = await fetch(urlEstado, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            // Cuando la venta pasa a completada, imprimir el ticket automáticamente
            if (data.estado === 'completada' && !impreso) {
                impreso = true;
                clearInterval(intervalo);

                // Abrir el ticket en una ventana nueva y disparar la impresión
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
</script>
@endpush
@endif
@endsection
