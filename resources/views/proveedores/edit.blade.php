@extends('layouts.app')
@section('title', 'Editar Proveedor')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}" style="color:#a855f7;">Proveedores</a></li>
    <li class="breadcrumb-item active">Editar: {{ $proveedor->nombre }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Editar Proveedor</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Modifica los datos del proveedor</p>
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required>
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contacto</label>
                            <input type="text" class="form-control" name="contacto" value="{{ old('contacto', $proveedor->contacto) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $proveedor->email) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RUC</label>
                            <input type="text" class="form-control" name="ruc" value="{{ old('ruc', $proveedor->ruc) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" value="{{ old('direccion', $proveedor->direccion) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" rows="3">{{ old('notas', $proveedor->notas) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="activo" value="1" id="activo" {{ old('activo', $proveedor->activo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo">Proveedor activo</label>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection