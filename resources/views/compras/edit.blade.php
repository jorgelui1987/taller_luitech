@extends('layouts.app')
@section('title', 'Editar ' . $ordenCompra->numero_orden)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('compras.index') }}" style="color:#a855f7;">Órdenes de Compra</a></li>
    <li class="breadcrumb-item active">Editar {{ $ordenCompra->numero_orden }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4 text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3" style="color:#06b6d4;"></i>
                <h5 class="fw-bold">Edición de Órdenes de Compra</h5>
                <p class="text-muted mb-4">La edición detallada de órdenes de compra estará disponible próximamente. Por ahora puedes cambiar el estado desde la vista de la orden.</p>
                <a href="{{ route('compras.show', $ordenCompra) }}" class="btn btn-primary"><i class="fas fa-eye me-2"></i>Ver Orden</a>
                <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            </div>
        </div>
    </div>
</div>
@endsection