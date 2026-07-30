@extends('layouts.app')
@section('title', $proveedor->nombre)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}" style="color:#a855f7;">Proveedores</a></li>
    <li class="breadcrumb-item active">{{ $proveedor->nombre }}</li>
@endsection
@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div style="width:64px;height:64px;background:linear-gradient(135deg,#a855f7,#ec4899);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="fas fa-truck" style="font-size:28px;color:#fff;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $proveedor->nombre }}</h5>
                    <span style="background:{{ $proveedor->activo ? '#d1fae5' : '#fee2e2' }};color:{{ $proveedor->activo ? '#065f46' : '#dc2626' }};border-radius:20px;padding:3px 10px;font-size:11px;">
                        {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <hr>
                <div style="font-size:13px;">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Contacto</span>
                        <strong>{{ $proveedor->contacto ?: '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Teléfono</span>
                        <strong>{{ $proveedor->telefono ?: '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Email</span>
                        <strong>{{ $proveedor->email ?: '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">RUC</span>
                        <strong>{{ $proveedor->ruc ?: '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Órdenes</span>
                        <strong>{{ $proveedor->ordenesCompra->count() }}</strong>
                    </div>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Editar Proveedor</a>
                    <a href="{{ route('compras.create') }}?proveedor_id={{ $proveedor->id }}" class="btn btn-outline-primary"><i class="fas fa-plus me-2"></i>Nueva Orden de Compra</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Órdenes de Compra</h6>
                @if($proveedor->ordenesCompra->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                        <thead><tr><th>N° Orden</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach($proveedor->ordenesCompra->sortByDesc('created_at') as $oc)
                            <tr>
                                <td><a href="{{ route('compras.show', $oc) }}" style="color:#a855f7;font-weight:500;">{{ $oc->numero_orden }}</a></td>
                                <td style="color:#6b7280;">{{ $oc->fecha_orden->format('d/m/Y') }}</td>
                                <td style="font-weight:600;">S/ {{ number_format($oc->total, 2) }}</td>
                                <td><span style="background:{{ $oc->estado_bg }};color:{{ $oc->estado_color }};border-radius:20px;padding:3px 10px;font-size:11px;">{{ ucfirst(str_replace('_', ' ', $oc->estado)) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted" style="font-size:13px;">
                    <i class="fas fa-clipboard-list fa-2x mb-2 d-block opacity-40"></i>
                    No hay órdenes de compra para este proveedor
                </div>
                @endif
            </div>
        </div>
        @if($proveedor->notas)
        <div class="card mt-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-2">Notas</h6>
                <p style="font-size:13px;color:#374151;">{{ $proveedor->notas }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection