@extends('layouts.app')

@section('title', 'Cerrar Caja')
@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('caja.index') }}">Cierre de Caja</a></li></ul>
    <ul><li class="breadcrumb-item active" aria-current="page">Cerrar Caja</li></ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3" style="font-weight:600;">
                    <i class="fas fa-lock me-2" style="color:var(--accent1);"></i>
                    Cerrar Caja
                </h5>

                {{-- Resumen de información de la caja abierta --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div style="font-size:11px; color:var(--text-muted);">Cajero</div>
                                <div style="font-size:14px; font-weight:600;">{{ $caja->usuario->name ?? Auth::user()->name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div style="font-size:11px; color:var(--text-muted);">Apertura</div>
                                <div style="font-size:14px; font-weight:600;">{{ $caja->fecha_apertura?->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div style="font-size:11px; color:var(--text-muted);">Monto Inicial</div>
                                <div style="font-size:14px; font-weight:600;">{{ $simbolo }} {{ number_format($caja->monto_inicial, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Totales del turno --}}
                <h6 class="mb-3" style="font-size:14px; font-weight:600; color:var(--text-dark);">
                    <i class="fas fa-chart-pie me-1" style="color:var(--accent1);"></i>
                    Totales del Día (Ventas + Reparaciones)
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered bg-white">
                        <tbody>
                            <tr>
                                <td style="width:50%; font-weight:500;">💵 Efectivo</td>
                                <td class="text-end" style="font-weight:600;">{{ $simbolo }} {{ number_format($ventasEfectivo, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:500;">💳 Tarjeta</td>
                                <td class="text-end" style="font-weight:600;">{{ $simbolo }} {{ number_format($ventasTarjeta, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:500;">🏦 Transferencia</td>
                                <td class="text-end" style="font-weight:600;">{{ $simbolo }} {{ number_format($ventasTransferencia, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:500;">📱 Otros (Mercado Pago, etc.)</td>
                                <td class="text-end" style="font-weight:600;">{{ $simbolo }} {{ number_format($ventasOtros, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--text-dark);">
                                <td style="font-weight:700;">💰 Total Ingresos</td>
                                <td class="text-end" style="font-weight:700;">{{ $simbolo }} {{ number_format($totalIngresos, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:500;">↩️ Egresos (Devoluciones)</td>
                                <td class="text-end text-danger" style="font-weight:600;">-{{ $simbolo }} {{ number_format($totalEgresos, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info py-2 px-3" style="font-size:13px;">
                    <i class="fas fa-calculator me-1"></i>
                    <strong>Total esperado en efectivo:</strong> {{ $simbolo }} {{ number_format($totalEsperado, 2) }}
                    <br><small>Monto inicial + Ventas en efectivo − Devoluciones</small>
                </div>
            </div>
        </div>

        {{-- Formulario de arqueo --}}
        <div class="card">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-size:14px; font-weight:600; color:var(--text-dark);">
                    <i class="fas fa-hand-holding-usd me-1" style="color:var(--accent1);"></i>
                    Arqueo Físico de Caja
                </h6>

                <form method="POST" action="{{ route('caja.guardarCierre') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Total contado (físico) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $simbolo }}</span>
                            <input type="number" step="0.01" min="0" name="total_contado" id="totalContado"
                                   class="form-control form-control-lg @error('total_contado') is-invalid @enderror"
                                   placeholder="0.00" required>
                            @error('total_contado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Cuenta el dinero físico en la caja y registra el total.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="form-control"
                                  placeholder="Diferencias, notas, faltantes, etc."></textarea>
                    </div>

                    {{-- Diferencia en tiempo real --}}
                    <div class="card bg-light mb-4">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted);">Diferencia calculada</div>
                                    <div id="diferenciaPreview" style="font-size:20px; font-weight:700; color:var(--text-muted);">
                                        — 
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-size:12px; color:var(--text-muted);">Esperado</div>
                                    <div style="font-size:18px; font-weight:700;">{{ $simbolo }} {{ number_format($totalEsperado, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger flex-fill">
                            <i class="fas fa-lock me-1"></i>Confirmar Cierre
                        </button>
                        <a href="{{ route('caja.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalEsperado = parseFloat('{{ $totalEsperado }}');
    const inputContado = document.getElementById('totalContado');
    const preview = document.getElementById('diferenciaPreview');

    if (inputContado && preview) {
        inputContado.addEventListener('input', function() {
            const contado = parseFloat(this.value) || 0;
            const diferencia = contado - totalEsperado;

            if (this.value === '') {
                preview.textContent = '—';
                preview.style.color = 'var(--text-muted)';
                return;
            }

            if (Math.abs(diferencia) < 0.01) {
                preview.textContent = '✔ CUADRADO';
                preview.style.color = '#10b981';
            } else if (diferencia > 0) {
                preview.textContent = '+ {{ $simbolo }} ' + diferencia.toFixed(2) + ' (SOBRANTE)';
                preview.style.color = '#f97316';
            } else {
                preview.textContent = '- {{ $simbolo }} ' + Math.abs(diferencia).toFixed(2) + ' (FALTANTE)';
                preview.style.color = '#ef4444';
            }
        });
    }
});
</script>
@endpush