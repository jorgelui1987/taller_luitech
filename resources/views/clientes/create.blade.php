@extends('layouts.app')
@section('title', 'Nuevo Cliente')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('clientes.index') }}" style="color:#a855f7;">Clientes</a></li></ul>
    <ul><li class="breadcrumb-item active">Nuevo Cliente</li></ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Registrar Nuevo Cliente</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Completa los datos del cliente</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<ul><li style="font-size:13px;">{{ $e }}</li></ul>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf

                    {{-- Tipo de cliente --}}
                    <div class="mb-4">
                        <span class="form-label d-block">Tipo de Cliente <span class="text-danger">*</span></span>
                        <div class="d-flex gap-3">
                            <label for="tipo_particular" class="d-flex align-items-center gap-2 cursor-pointer"
                                   style="padding:10px 20px; border:1.5px solid #e5e7eb; border-radius:10px; cursor:pointer; flex:1; transition:.2s;">
                                <input type="radio" name="tipo" id="tipo_particular" value="particular"
                                       {{ old('tipo','particular')=='particular'?'checked':'' }}
                                       style="accent-color:#a855f7;">
                                <span style="font-size:13.5px;">
                                    <i class="fas fa-user me-1 text-muted"></i> Particular
                                </span>
                            </label>
                            <label id="tipo_empresa" for="tipo_empresa_radio" class="d-flex align-items-center gap-2"
                                   style="padding:10px 20px; border:1.5px solid #e5e7eb; border-radius:10px; cursor:pointer; flex:1; transition:.2s;">
                                <input type="radio" name="tipo" id="tipo_empresa_radio" value="empresa"
                                       {{ old('tipo')=='empresa'?'checked':'' }}
                                       style="accent-color:#a855f7;">
                                <span style="font-size:13.5px;">
                                    <i class="fas fa-building me-1 text-muted"></i> Empresa
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Datos personales --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                   name="nombre" id="nombre" value="{{ old('nombre') }}" placeholder="Nombre">
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('apellido') is-invalid @enderror"
                                   name="apellido" id="apellido" value="{{ old('apellido') }}" placeholder="Apellido">
                            @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <small class="text-muted">(opcional)</small></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" id="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                   name="telefono" id="telefono" value="{{ old('telefono') }}" placeholder="999 999 999">
                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" class="form-control" name="celular" id="celular"
                                   value="{{ old('celular') }}" placeholder="999 999 999">
                        </div>
                        <div class="col-md-4">
                            <label for="dni" class="form-label">DNI / Documento</label>
                            <input type="text" class="form-control @error('dni') is-invalid @enderror"
                                   name="dni" id="dni" value="{{ old('dni') }}" placeholder="12345678">
                            @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento"
                                   value="{{ old('fecha_nacimiento') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="ciudad" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="ciudad" id="ciudad"
                                   value="{{ old('ciudad') }}" placeholder="serena">
                        </div>
                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="direccion"
                                   value="{{ old('direccion') }}" placeholder="Av. Ejemplo 123">
                        </div>
                    </div>

                    {{-- Datos empresa (condicional) --}}
                    <div id="datos-empresa" style="display:{{ old('tipo')=='empresa'?'block':'none' }};">
                        <hr>
                        <h6 class="fw-600 mb-3" style="font-weight:600;">Datos de Empresa</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label for="empresa" class="form-label">Razón Social</label>
                                <input type="text" class="form-control" name="empresa" id="empresa"
                                       value="{{ old('empresa') }}" placeholder="Empresa SAC">
                            </div>
                            <div class="col-md-4">
                                <label for="ruc" class="form-label">RUC</label>
                                <input type="text" class="form-control" name="ruc" id="ruc"
                                       value="{{ old('ruc') }}" placeholder="20123456789">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="mb-3">
                        <label for="notas" class="form-label">Notas internas</label>
                        <textarea class="form-control" name="notas" id="notas" rows="3"
                                  placeholder="Observaciones, preferencias del cliente...">{{ old('notas') }}</textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const radios = document.querySelectorAll('input[name="tipo"]');
    const datosEmpresa = document.getElementById('datos-empresa');

    radios.forEach(r => r.addEventListener('change', function() {
        datosEmpresa.style.display = this.value === 'empresa' ? 'block' : 'none';
    }));
</script>
@endpush
