@extends('layouts.app')
@section('title', 'Detalle Venta '.$venta->numero_venta)

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('ventas.index') }}" style="color:#0891b2;">Ventas</a></li></ul>
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
.info-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:500;
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
        <a href="{{ route('ventas.ticket-carta', $venta) }}" target="_blank" class="btn btn-outline-secondary px-4">
            <i class="fas fa-file-invoice me-2"></i>Formato carta (media hoja)
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

@php
    $cfg = ['completada'=>['#d1fae5','#065f46'],'cancelada'=>['#fee2e2','#991b1b'],'pendiente'=>['#fef3c7','#92400e'],'devuelta'=>['#f3f4f6','#374151']];
    $c = $cfg[$venta->estado] ?? ['#f3f4f6','#374151'];
    $iconos = ['efectivo'=>'💵','tarjeta'=>'💳','transferencia'=>'🏦','cuotas'=>'📅','yape'=>'📱','plin'=>'📲','mercadopago'=>'🟦'];
@endphp

{{-- Tarjeta de Resumen --}}
<div class="card mb-4">
    <div class="card-header p-3" style="background:linear-gradient(135deg,#ecfeff,#fdf4ff); border-bottom:1px solid #cffafe;">
        <div class="row g-3 align-items-center">
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Estado</div>
                <span style="background:{{ $c[0] }}; color:{{ $c[1] }}; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; display:inline-block;">
                    {{ ucfirst($venta->estado) }}
                </span>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
                <div style="font-weight:600; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $venta->cliente?->nombre_completo ?? 'VENTA GENERAL' }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Pago</div>
                <div style="font-weight:600; font-size:14px;">{{ $iconos[$venta->metodo_pago] ?? '' }} {{ ucfirst($venta->metodo_pago) }}</div>
                @if($venta->referencia_pago)
                <div style="font-size:11px; color:#6b7280; font-family:monospace;">Ref: {{ $venta->referencia_pago }}</div>
                @endif
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Total</div>
                <div style="font-weight:700; font-size:16px; color:#0e7490;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->total, 2) }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Vendedor</div>
                <div style="font-weight:600; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $venta->vendedor->name ?? '—' }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Fecha</div>
                <div style="font-weight:600; font-size:13px;">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Panel Mercado Pago (si aplica) --}}
@if($venta->estado === 'pendiente')
<div class="card mb-4" style="border:1px solid #00b1ea;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-1" style="color:#00b1ea; font-size:14px;">
                    <i class="fab fa-mercadopago me-1"></i>Mercado Pago — cobro manual en Point
                </h6>
                <p class="text-muted mb-0" style="font-size:12px;">
                    Total a cobrar en el equipo:
                    <strong>{{ ($empresa->simbolo_moneda ?? '$') . ' ' . number_format($venta->total, 0, ',', '.') }}</strong>
                    · Cobra con el menú normal (Tarjetas / Efectivo con boleta) y márcalo aquí.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                {{-- Verificación MP: si el Point ya cobró pero la venta quedó en Pendiente, consulta orden + respaldo y al aprobar imprime solo. --}}
                <form action="{{ route('ventas.point-verificar', $venta) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" style="border-radius:8px; font-weight:700;"
                            title="Consulta a Mercado Pago si el Point ya cobró y, si aprobó, marca pagada e imprime la boleta.">
                        <i class="fas fa-check-double me-1"></i>Ya cobré, verificar e imprimir
                    </button>
                </form>
                <form action="{{ route('ventas.point', $venta) }}" method="POST"
                      onsubmit="return confirm('¿Enviar el total al Point? En el equipo eliges Tarjeta o Efectivo con boleta.');">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:#00b1ea; color:#fff; border-radius:8px;"
                            title="Manda solo el precio al Point: en la máquina sale el menú normal (Tarjetas / Efectivo con boleta)">
                        <i class="fas fa-credit-card me-1"></i>Cobrar con Mercado Pago
                    </button>
                </form>
                <form action="{{ route('ventas.point-tarjeta', $venta) }}" method="POST"
                      onsubmit="return confirm('¿Cobraste con tarjeta manual en el Point? Se marcará pagada con tarjeta.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius:8px;"
                            title="Si ya cobraste manual en el equipo con tarjeta, márcalo aquí">
                        <i class="fas fa-check me-1"></i>Pagó con tarjeta
                    </button>
                </form>
                <form action="{{ route('ventas.mercadopago', $venta) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                        <i class="fas fa-qrcode me-1"></i>Generar QR de Pago
                    </button>
                </form>
                <form action="{{ route('ventas.point-efectivo', $venta) }}" method="POST" class="d-flex gap-1 align-items-center"
                      onsubmit="return confirm('¿Pagaron en efectivo manual en el Point? Se registrará en caja con vuelto.');">
                    @csrf
                    <input type="number" name="monto_recibido" class="form-control form-control-sm" style="width:130px;"
                           placeholder="Monto recibido" min="{{ $venta->total }}" step="1" value="{{ number_format($venta->total, 0, '.', '') }}">
                    <button type="submit" class="btn btn-sm btn-success" style="border-radius:8px;"
                            title="Si el cliente pagó en efectivo en el equipo, regístralo aquí con vuelto">
                        <i class="fas fa-money-bill-wave me-1"></i>Pagó en efectivo
                    </button>
                </form>
            </div>
            <small class="text-muted d-block mt-2" style="font-size:11px;">
                Cobra manual en el equipo con su menú normal (Tarjetas / Efectivo con boleta) por el total
                <strong>{{ ($empresa->simbolo_moneda ?? '$') . ' ' . number_format($venta->total, 0, ',', '.') }}</strong>
                y luego márcalo aquí. El sistema solo guarda el precio, no manda orden al Point.
            </small>
        </div>
    </div>
</div>
@endif

@if(session('point'))
<div class="card mb-4" style="border:1px solid #00b1ea;">
    <div class="card-body p-3 text-center">
        <h6 class="fw-bold mb-2" style="color:#00b1ea; font-size:13px;">
            <i class="fab fa-mercadopago me-1"></i>Cobro enviado al Point
        </h6>
        <div class="mb-2" style="font-size:12px; color:#6b7280;">{{ session('point.mensaje') ?? 'Esperando confirmación del dispositivo...' }}</div>
        <div style="font-size:11px; color:#6b7280;">El estado de la venta se actualizará automáticamente al confirmarse el pago.</div>
    </div>
</div>
@endif

@if(session('mercadopago'))
<div class="card mb-4" style="border:1px solid #00b1ea;">
    <div class="card-body p-3 text-center">
        <h6 class="fw-bold mb-2" style="color:#00b1ea; font-size:13px;">
            <i class="fab fa-mercadopago me-1"></i>Pago con Mercado Pago
        </h6>
        @if(session('mercadopago.init_point'))
        <div class="mb-2">
            <a href="{{ session('mercadopago.init_point') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-external-link-alt me-1"></i>Abrir enlace de pago
            </a>
        </div>
        @endif
        <div style="font-size:11px; color:#6b7280;">El estado de la venta se actualizará automáticamente al confirmarse el pago.</div>
    </div>
</div>
@endif

{{-- Pestañas de secciones --}}
<div class="card mb-4">
    <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
        <ul class="nav nav-tabs card-header-tabs" id="ventaTabs" role="tablist" style="border-bottom:none; padding:0 8px;">
            <li class="nav-item">
                <button class="nav-link active" id="tab-productos-tab" data-bs-toggle="tab" data-bs-target="#tab-productos" type="button" role="tab" aria-controls="tab-productos" aria-selected="true" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-box-open me-1" style="color:#0891b2;"></i>📦 Productos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-totales-tab" data-bs-toggle="tab" data-bs-target="#tab-totales" type="button" role="tab" aria-controls="tab-totales" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-dollar-sign me-1" style="color:#0891b2;"></i>💰 Totales
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-resumen-tab" data-bs-toggle="tab" data-bs-target="#tab-resumen" type="button" role="tab" aria-controls="tab-resumen" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="fas fa-info-circle me-1" style="color:#0891b2;"></i>ℹ️ Resumen
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="ventaTabsContent">

            {{-- Pestaña: Productos --}}
            <div class="tab-pane fade show active" id="tab-productos" role="tabpanel" aria-labelledby="tab-productos-tab">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:13.5px;">
                        <thead>
                            <tr style="border-bottom:2px solid #a5f3fc;">
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
            </div>

            {{-- Pestaña: Totales --}}
            <div class="tab-pane fade" id="tab-totales" role="tabpanel" aria-labelledby="tab-totales-tab">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">SUBTOTAL</div>
                            <div style="font-weight:700; font-size:16px; color:#0e7490;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->subtotal, 2) }}</div>
                        </div>
                    </div>
                    @if($venta->descuento > 0)
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#fee2e2;">
                            <div style="font-size:10px; color:#991b1b;">DESCUENTO</div>
                            <div style="font-weight:700; font-size:16px; color:#dc2626;">— {{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->descuento, 2) }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#e0f2fe;">
                            <div style="font-size:10px; color:#0369a1;">{{ $empresa->pais == 'CL' ? 'IVA' : 'IGV' }} ({{ $empresa->igv ?? 18 }}%)</div>
                            <div style="font-weight:700; font-size:16px; color:#0369a1;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->impuesto, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:linear-gradient(135deg,#0891b2,#3b82f6);">
                            <div style="font-size:10px; color:#fff;">TOTAL</div>
                            <div style="font-weight:700; font-size:18px; color:#fff;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($venta->total, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pestaña: Resumen --}}
            <div class="tab-pane fade" id="tab-resumen" role="tabpanel" aria-labelledby="tab-resumen-tab">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">PRODUCTOS</div>
                            <div style="font-weight:600; font-size:14px;">{{ $venta->detalles->count() }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">UNIDADES</div>
                            <div style="font-weight:600; font-size:14px;">{{ $venta->detalles->sum('cantidad') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">CLIENTE</div>
                            <div style="font-weight:600; font-size:13px;">{{ $venta->cliente?->nombre_completo ?? 'VENTA GENERAL' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                            <div style="font-size:10px; color:#9ca3af;">PAGO</div>
                            <div style="font-weight:600; font-size:13px;">{{ $iconos[$venta->metodo_pago] ?? '' }} {{ ucfirst($venta->metodo_pago) }}</div>
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
{{-- Se activa cuando: pago manual recién confirmado, venta MP pendiente (esperando
     Point) O venta MP recién pagada vía Point (session point) aunque ya esté completada. --}}
@if(session('imprimir_ticket') || session('point') || ($venta->estado === 'pendiente' && $venta->metodo_pago === 'mercadopago') || ($venta->estado === 'completada' && $venta->metodo_pago === 'mercadopago' && session('success')))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlTicket = "{{ route('ventas.ticket', $venta) }}";

    // ── Impresión directa: se intenta con ventana nueva (window.print del
    // ticket) y como respaldo con iframe oculto. El aviso dice "aprobado"
    // solo cuando la venta ya quedó completada, así no miente. ──
    function imprimirDirecto() {
        // 1) Ventana nueva: el ticket trae window.print() al cargar.
        // Funciona porque el polling ya confirmó el pago (no es popup spam).
        try {
            const win = window.open(urlTicket + (urlTicket.includes('?') ? '&' : '?') + 'auto=' + Date.now(), '_blank');
            if (win) {
                // Si la ventana abrió, el ticket se imprime solo; el iframe
                // queda como respaldo por si el navegador la bloqueó.
                setTimeout(function () { imprimirIframe(); }, 2500);
                return;
            }
        } catch (e) {}
        // 2) Respaldo: iframe oculto (el CSP ya permite frame-src 'self').
        imprimirIframe();
    }

    function imprimirIframe() {
        let frame = document.getElementById('frameTicketAuto');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'frameTicketAuto';
            frame.style.cssText = 'position:fixed;width:0;height:0;border:0;right:0;bottom:0;visibility:hidden;';
            document.body.appendChild(frame);
        }
        // El ticket ya trae window.print() al cargar, con esto basta.
        // Se agrega timestamp para evitar caché y forzar recarga.
        frame.src = urlTicket + (urlTicket.includes('?') ? '&' : '?') + 'auto=' + Date.now();
    }

    function mostrarAviso(texto, ok) {
        let aviso = document.getElementById('avisoMpAuto');
        if (!aviso) {
            aviso = document.createElement('div');
            aviso.id = 'avisoMpAuto';
            aviso.style.cssText = 'position:fixed;bottom:18px;right:18px;z-index:9999;max-width:320px;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.2);';
            document.body.appendChild(aviso);
        }
        aviso.style.background = ok ? '#d1fae5' : '#fef9c3';
        aviso.style.color = ok ? '#065f46' : '#92400e';
        aviso.style.border = ok ? '1px solid #6ee7b7' : '1px solid #fde68a';
        aviso.innerHTML = texto;
    }

    @if(session('imprimir_ticket'))
    // Pago manual recién confirmado (tarjeta o efectivo): imprimir de una vez.
    imprimirDirecto();
    @else
    // Pago por Point: consultar el estado de la orden MP.
    // Funciona por webhook (el servidor ya la marcó pagada) o por polling
    // directo a la orden (local sin URL pública). Al aprobarse, imprime sola.
    const urlEstadoVenta = "{{ route('ventas.estado', $venta) }}";
    const urlEstadoPoint = "{{ route('ventas.point-estado', $venta) }}";
    let impreso = false;
    let intentos = 0;
    const maxIntentos = 150; // ~5 min a 2s

    mostrarAviso('⏳ Esperando pago en el Point...<br><small style="font-weight:400;">Al aprobarse se imprime la boleta sola.</small>', false);

    async function verificarPago() {
        if (impreso) return;
        intentos++;
        if (intentos > maxIntentos) {
            clearInterval(intervalo);
            mostrarAviso('⚠️ No se detectó el pago. <a href="' + urlTicket + '" target="_blank" style="color:#92400e;text-decoration:underline;">Imprimir manual aquí</a>', false);
            return;
        }
        try {
            // 1) ¿La venta ya quedó completada? (webhook o polling previo)
            const resVenta = await fetch(urlEstadoVenta, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const dataVenta = await resVenta.json();

            let pagado = dataVenta.estado === 'completada';

            // 2) Si aún no, preguntar directo por la orden Point (marca pagada si aprobó)
            if (!pagado) {
                const resPoint = await fetch(urlEstadoPoint, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const dataPoint = await resPoint.json();
                pagado = !!dataPoint.pagado;
            }

            if (pagado && !impreso) {
                impreso = true;
                clearInterval(intervalo);
                mostrarAviso('✅ ¡Pago aprobado! Imprimiendo boleta...<br><small style="font-weight:400;">Si no sale el diálogo, <a href="' + urlTicket + '" target="_blank" style="color:#065f46;font-weight:700;text-decoration:underline;">clic aquí para abrir la boleta</a></small>', true);
                imprimirDirecto();
                // Por si el iframe tarda, reintentar una vez a los 3s
                setTimeout(function () { imprimirIframe(); }, 3000);
            }
        } catch (e) {}
    }

    // Primera verificación inmediata (por si el webhook ya la marcó
    // pagada antes de cargar la página) y luego cada 2s.
    verificarPago();
    const intervalo = setInterval(verificarPago, 2000);
    @endif
});
</script>
@endpush
@endif
@endsection