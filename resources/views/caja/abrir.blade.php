@extends('layouts.app')

@section('title', 'Abrir Caja')
@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('caja.index') }}">Cierre de Caja</a></li></ul>
    <ul><li class="breadcrumb-item active" aria-current="page">Abrir Caja</li></ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-4" style="font-weight:600;">
                    <i class="fas fa-unlock me-2" style="color:var(--accent1);"></i>
                    Abrir Caja
                </h5>

                <form method="POST" action="{{ route('caja.guardar') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Fecha de Apertura</label>
                        <input type="text" class="form-control" value="{{ now()->format('d/m/Y H:i') }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cajero</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Monto Inicial <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $simbolo }}</span>
                            <input type="number" step="0.01" min="0" name="monto_inicial"
                                   class="form-control @error('monto_inicial') is-invalid @enderror"
                                   placeholder="0.00" value="{{ old('monto_inicial', 0) }}" required>
                            @error('monto_inicial')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Dinero en efectivo con el que se inicia la caja (fondo fijo).</div>
                    </div>

                    {{-- Resumen de ventas del día (informativo) --}}
                    <div class="card bg-light mb-4">
                        <div class="card-body py-3">
                            <h6 class="mb-3" style="font-size:13px; font-weight:600; color:var(--text-dark);">
                                <i class="fas fa-info-circle me-1" style="color:var(--accent1);"></i>
                                Ventas registradas hoy (antes de abrir)
                            </h6>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div style="font-size:11px; color:var(--text-muted);">Ventas</div>
                                    <div style="font-size:15px; font-weight:700;">{{ $ventasHoy['num_ventas'] ?? 0 }}</div>
                                </div>
                                <div class="col-4">
                                    <div style="font-size:11px; color:var(--text-muted);">Reparaciones</div>
                                    <div style="font-size:15px; font-weight:700;">{{ $ventasHoy['num_reparaciones'] ?? 0 }}</div>
                                </div>
                                <div class="col-4">
                                    <div style="font-size:11px; color:var(--text-muted);">Total</div>
                                    <div style="font-size:15px; font-weight:700; color:var(--accent1);">
                                        {{ $simbolo }} {{ number_format(($ventasHoy['total_ingresos'] ?? 0), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-check me-1"></i>Abrir Caja
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