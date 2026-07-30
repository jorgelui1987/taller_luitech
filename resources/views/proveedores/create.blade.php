@extends('layouts.app')
@section('title', 'Nuevo Proveedor')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}" style="color:#a855f7;">Proveedores</a></li>
    <li class="breadcrumb-item active">Nuevo Proveedor</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Registrar Nuevo Proveedor</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Completa los datos del proveedor</p>
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('proveedores.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre del proveedor" required>
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contacto</label>
                            <input type="text" class="form-control" name="contacto" value="{{ old('contacto') }}" placeholder="Persona de contacto">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" value="{{ old('telefono') }}" placeholder="+51 999 999 999">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="proveedor@email.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RUC</label>
                            <input type="text" class="form-control" name="ruc" value="{{ old('ruc') }}" placeholder="12345678901">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" value="{{ old('direccion') }}" placeholder="Dirección del proveedor">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" rows="3" placeholder="Notas adicionales...">{{ old('notas') }}</textarea>
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar Proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection