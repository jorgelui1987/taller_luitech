@extends('layouts.app')
@section('title', $proveedor->nombre)
@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}" style="color:#a855f7;">Proveedores</a></li></ul>
    <ul><li class="breadcrumb-item active">{{ $proveedor->nombre }}</li></ul>
@endsection
@section('content')

@php
    $totalOrdenes = $proveedor->ordenesCompra->count();
    $totalCompras = $proveedor->ordenesCompra->sum('total');
@endphp

{{-- Tarjeta de Resumen --}}
<div class="card mb-4">
    <div class="card-header p-3" style="background:linear-gradient(135deg,#faf5ff,#fdf4ff); border-bottom:1px solid #f3e8ff;">
        <div class="row g-3 align-items-center">
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Proveedor</div>
                <div style="font-weight:600; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $proveedor->nombre }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Estado</div>
                <span style="background:{{ $proveedor->activo ? '#d1fae5' : '#fee2e2' }};color:{{ $proveedor->activo ? '#065f46' : '#dc2626' }};border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;display:inline-block;">
                    {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Contacto</div>
                <div style="font-weight:600; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $proveedor->contacto ?: '—' }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Teléfono</div>
                <div style="font-weight:600; font-size:13px;">{{ $proveedor->telefono ?: '—' }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Órdenes</div>
                <div style="font-weight:700; font-size:16px; color:#7c3aed;">{{ $totalOrdenes }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Total compras</div>
                <div style="font-weight:700; font-size:16px; color:#059669;">S/ {{ number_format($totalCompras, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Perfil --}}
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
                        <span class="text-muted">{{ $empresa->pais == 'CL' ? 'RUT' : 'RUC' }}</span>
                        <strong>{{ $proveedor->ruc ?: '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Órdenes</span>
                        <strong>{{ $totalOrdenes }}</strong>
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

    {{-- Historial --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
                <ul class="nav nav-tabs card-header-tabs" id="proveedorTabs" role="tablist" style="border-bottom:none; padding:0 8px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-ordenes-tab" data-bs-toggle="tab" data-bs-target="#tab-ordenes" type="button" role="tab" aria-controls="tab-ordenes" aria-selected="true" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                            <i class="fas fa-clipboard-list me-1" style="color:#a855f7;"></i>📋 Órdenes de Compra
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-notas-tab" data-bs-toggle="tab" data-bs-target="#tab-notas" type="button" role="tab" aria-controls="tab-notas" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                            <i class="fas fa-sticky-note me-1" style="color:#a855f7;"></i>📝 Notas
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="proveedorTabsContent">

                    {{-- Pestaña: Órdenes de Compra --}}
                    <div class="tab-pane fade show active" id="tab-ordenes" role="tabpanel" aria-labelledby="tab-ordenes-tab">
                        @if($proveedor->ordenesCompra->count())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                                <thead><tr><th>N° Orden</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                                <tbody>
                                    @foreach($proveedor->ordenesCompra as $oc)
                                    <tr>
                                        <td><a href="{{ route('compras.show', $oc) }}" style="color:#a855f7;font-weight:500;">{{ $oc->numero_orden ?? '—' }}</a></td>
                                        <td style="color:#6b7280;">{{ $oc->fecha_orden ? \Carbon\Carbon::parse($oc->fecha_orden)->format('d/m/Y') : '—' }}</td>
                                        <td style="font-weight:600;">S/ {{ number_format($oc->total ?? 0, 2) }}</td>
                                        <td><span style="background:{{ $oc->estado_bg ?? '#f3f4f6' }};color:{{ $oc->estado_color ?? '#6b7280' }};border-radius:20px;padding:3px 10px;font-size:11px;">{{ ucfirst(str_replace('_', ' ', $oc->estado ?? 'pendiente')) }}</span></td>
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

                    {{-- Pestaña: Notas --}}
                    <div class="tab-pane fade" id="tab-notas" role="tabpanel" aria-labelledby="tab-notas-tab">
                        @if($proveedor->notas)
                        <div class="p-3 rounded-3" style="background:#f8f5ff; font-size:13px; color:#374151;">
                            <i class="fas fa-sticky-note me-1" style="color:#a855f7;"></i>{{ $proveedor->notas }}
                        </div>
                        @else
                        <div class="text-center py-4 text-muted" style="font-size:13px;">
                            <i class="fas fa-sticky-note fa-2x mb-2 d-block opacity-40"></i>
                            No hay notas para este proveedor
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection