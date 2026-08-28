@extends('layouts.public')

@section('title', 'Orden ' . $reparacion->numero_orden)

@php
    $labels = [
        'recibido' => 'Recibido',
        'en_diagnostico' => 'En diagnóstico',
        'esperando_repuesto' => 'Esperando repuesto',
        'en_reparacion' => 'En reparación',
        'listo' => 'Listo para retiro',
        'entregado' => 'Entregado',
        'no_reparable' => 'No reparable',
    ];
    $badgeClasses = [
        'recibido' => 'lp-badge-cyan', 'en_diagnostico' => 'lp-badge-amber',
        'esperando_repuesto' => 'lp-badge-amber', 'en_reparacion' => 'lp-badge-cyan',
        'listo' => 'lp-badge-emerald', 'entregado' => 'lp-badge-slate',
        'no_reparable' => 'lp-badge-red',
    ];
    $pasosMap = [
        'recibido' => 1, 'en_diagnostico' => 2, 'esperando_repuesto' => 3,
        'en_reparacion' => 3, 'listo' => 4, 'entregado' => 5, 'no_reparable' => 3,
    ];
    $avancesMap = [
        'recibido' => 10, 'en_diagnostico' => 30, 'esperando_repuesto' => 45,
        'en_reparacion' => 65, 'listo' => 85, 'entregado' => 100, 'no_reparable' => 65,
    ];
    $estadoKey   = $reparacion->estado;
    $estadoLabel = $labels[$estadoKey] ?? ucfirst($estadoKey);
    $badgeClass  = $badgeClasses[$estadoKey] ?? 'lp-badge-slate';
    $pasoActual  = $pasosMap[$estadoKey] ?? 1;
    $avance      = $avancesMap[$estadoKey] ?? 50;
    $esEspera    = $estadoKey === 'esperando_repuesto';
    $esFallado   = $estadoKey === 'no_reparable';
    $pasos = [
        1 => ['Recepción', 'fa-receipt', false],
        2 => ['Diagnóstico', 'fa-stethoscope', false],
        3 => ['Reparación', 'fa-screwdriver-wrench', false],
        4 => ['Listo para retiro', 'fa-box-open', true],
        5 => ['Entregado', 'fa-circle-check', true],
    ];
    $equipoNombre = trim((($reparacion->marca ?: '') . ' ' . ($reparacion->modelo ?: ''))) ?: ($reparacion->dispositivo ?: 'Equipo');
    $tipos = ['celular' => 'Celular', 'tablet' => 'Tablet', 'portatil' => 'Portátil', 'otros' => 'Equipo'];
    $tipoLabel = $tipos[$reparacion->tipo_dispositivo] ?? ($reparacion->tipo_dispositivo ?: 'Equipo');
    $moneda = $empresa->simbolo_moneda ?? '$';
    $waDigits = preg_replace('/\D/', '', (string) ($empresa->telefono ?? ''));
    $waNumber = ($waDigits !== '') ? (str_starts_with($waDigits, '56') ? $waDigits : '56' . $waDigits) : null;
@endphp

@section('content')
<div class="lp-status-wrap">
    <div class="lp-order-card">

        <!-- Encabezado de la orden -->
        <div class="lp-order-head">
            <div>
                <span class="lp-order-kicker">Orden de trabajo</span>
                <h1 class="lp-order-code">{{ $reparacion->numero_orden }}</h1>
                <p class="lp-order-sub">
                    {{ $equipoNombre }} · {{ $tipoLabel }}@if($reparacion->cliente) · {{ $reparacion->cliente->nombre_completo ?? '' }}@endif
                </p>
            </div>
            <div class="lp-order-side">
                <div class="lp-meta-chip">
                    <small>Técnico asignado</small>
                    <span>{{ $reparacion->tecnico->name ?? 'Por asignar' }}</span>
                </div>
                <div class="lp-badge {{ $badgeClass }}">
                    <span class="lp-pulse"></span> {{ $estadoLabel }}
                </div>
            </div>
        </div>

        <!-- Timeline de avance (5 pasos según el estado real de la orden) -->
        <div class="lp-timeline">
            <div class="lp-timeline-outer">
                <div class="lp-track"><div class="lp-fill" style="width: {{ $avance }}%;"></div></div>
                <div class="lp-steps">
                    @foreach($pasos as $i => $paso)
                        @php
                            $clases = ['lp-step'];
                            if ($i < $pasoActual) { $clases[] = 'is-done'; }
                            if ($i === $pasoActual) { $clases[] = 'is-active'; }
                            if ($i === $pasoActual && $esEspera) { $clases[] = 'is-waiting'; }
                            if ($i === $pasoActual && $esFallado) { $clases[] = 'is-failed'; }
                            if ($paso[2]) { $clases[] = 'is-final'; }
                        @endphp
                        <div class="{{ implode(' ', $clases) }}">
                            <div class="lp-step-dot"><i class="fa-solid {{ $paso[1] }}"></i></div>
                            <span class="lp-step-label">{{ $paso[0] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lp-order-body">
            <!-- Detalles clave -->
            <div class="lp-grid-2">
                <div class="lp-box">
                    <small>Fecha de ingreso</small>
                    <span>{{ optional($reparacion->fecha_recepcion)->format('d/m/Y') ?? '—' }}</span>
                </div>
                @if($reparacion->fecha_entrega)
                    <div class="lp-box"><small>Fecha de entrega</small><span>{{ $reparacion->fecha_entrega->format('d/m/Y') }}</span></div>
                @elseif($reparacion->fecha_estimada)
                    <div class="lp-box"><small>Fecha estimada</small><span>{{ $reparacion->fecha_estimada->format('d/m/Y') }}</span></div>
                @else
                    <div class="lp-box"><small>Fecha estimada</small><span>Por confirmar</span></div>
                @endif
                <div class="lp-box">
                    <small>Equipo</small>
                    <span>{{ $equipoNombre }}{{ $reparacion->color ? ' · ' . $reparacion->color : '' }}</span>
                </div>
                <div class="lp-box"><small>IMEI / Serie</small><span>{{ $reparacion->imei ?: '—' }}</span></div>
            </div>

            @if($reparacion->falla_reportada)
                <div class="lp-callout lp-callout-falla">
                    <small><i class="fa-solid fa-triangle-exclamation"></i> Falla reportada</small>
                    {{ $reparacion->falla_reportada }}
                </div>
            @endif

            @if($reparacion->diagnostico)
                <div class="lp-callout lp-callout-diag">
                    <small><i class="fa-solid fa-stethoscope"></i> Diagnóstico técnico</small>
                    {{ $reparacion->diagnostico }}
                </div>
            @endif

            @if($reparacion->solucion)
                <div class="lp-callout lp-callout-sol">
                    <small><i class="fa-solid fa-circle-check"></i> Solución aplicada</small>
                    {{ $reparacion->solucion }}
                </div>
            @endif

            @if($esFallado)
                <div class="lp-callout lp-callout-warn">
                    <small><i class="fa-solid fa-circle-xmark"></i> Equipo no reparable</small>
                    Contáctanos para coordinar la devolución de tu equipo.
                </div>
            @endif

            @if($reparacion->presupuesto > 0 || $reparacion->costo_final > 0 || $reparacion->abono > 0 || $reparacion->total > 0)
                <div class="lp-costs">
                    @if($reparacion->presupuesto > 0)
                        <div class="lp-cost lp-cost-presupuesto"><b>{{ $moneda }} {{ number_format($reparacion->presupuesto, 2) }}</b><small>Presupuesto</small></div>
                    @endif
                    @if($reparacion->costo_final > 0)
                        <div class="lp-cost lp-cost-final"><b>{{ $moneda }} {{ number_format($reparacion->costo_final, 2) }}</b><small>Costo final</small></div>
                    @endif
                    @if($reparacion->abono > 0)
                        <div class="lp-cost lp-cost-abono"><b>{{ $moneda }} {{ number_format($reparacion->abono, 2) }}</b><small>Abono</small></div>
                    @endif
                    @if($reparacion->total > 0)
                        <div class="lp-cost lp-cost-total"><b>{{ $moneda }} {{ number_format($reparacion->total, 2) }}</b><small>Saldo total</small></div>
                    @endif
                </div>
            @endif

            @if($reparacion->garantia && $reparacion->dias_garantia > 0)
                @php $garantia = $reparacion->estadoGarantia(); @endphp
                <div class="lp-callout lp-callout-sol">
                    <small><i class="fa-solid fa-shield-halved"></i> Garantía {{ $garantia['estado'] === 'vencida' ? 'vencida' : 'vigente' }}</small>
                    {{ $reparacion->dias_garantia }} días de garantía
                    @if(!empty($garantia['fecha_vencimiento'])) · vence el {{ $garantia['fecha_vencimiento']->format('d/m/Y') }}@endif
                </div>
            @endif

            @if(!empty($empresa->terminos_garantia))
                <div class="lp-callout lp-callout-plain">
                    <small><i class="fa-solid fa-file-contract"></i> Condiciones de garantía</small>
                    {{ $empresa->terminos_garantia }}
                </div>
            @endif

            @if($reparacion->notas)
                <div class="lp-callout lp-callout-plain">
                    <small><i class="fa-solid fa-note-sticky"></i> Notas</small>
                    {{ $reparacion->notas }}
                </div>
            @endif

            @if($reparacion->cliente)
                <div class="lp-callout lp-callout-plain">
                    <small><i class="fa-solid fa-user"></i> Cliente</small>
                    <strong>{{ $reparacion->cliente->nombre_completo ?? '—' }}</strong>
                    @if($reparacion->cliente->telefono) · {{ $reparacion->cliente->telefono }}@endif
                </div>
            @endif

            @if($waNumber)
                <a class="lp-wa-cta" href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Hola, consulto por mi orden ' . $reparacion->numero_orden) }}" target="_blank" rel="noopener">
                    <i class="fa-brands fa-whatsapp"></i>
                    {{ $estadoKey === 'listo' ? 'Coordinar retiro por WhatsApp' : '¿Dudas? Escríbenos por WhatsApp' }}
                </a>
            @endif
        </div>

        <!-- QR para volver a esta consulta -->
        <div class="lp-qr">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(url()->current()) }}" alt="QR de la orden">
            <p>
                <strong>Escanea para volver aquí</strong><br>
                Guarda este enlace o escanea el código para consultar el estado de tu orden
                <strong>{{ $reparacion->numero_orden }}</strong> en cualquier momento.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if(request()->filled('numero_orden'))
        document.addEventListener('DOMContentLoaded', () => lpToast('¡Orden encontrada! Estado actualizado en tiempo real.', 'success'));
    @endif
@endpush