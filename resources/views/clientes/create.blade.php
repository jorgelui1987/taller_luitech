@extends('layouts.app')
@section('title', 'Nuevo Cliente')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('clientes.index') }}" style="color:#a855f7;">Clientes</a></li></ul>
    <ul><li class="breadcrumb-item active">Nuevo Cliente</li></ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Registrar Nuevo Cliente</h5>
                        <p class="text-muted mb-0" style="font-size:13px;">Completa los datos del cliente</p>
                    </div>
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<ul><li style="font-size:13px;">{{ $e }}</li></ul>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf

                    {{-- Pestañas del formulario --}}
                    <div class="card mb-4">
                        <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
                            <ul class="nav nav-tabs card-header-tabs" id="clienteCreateTabs" role="tablist" style="border-bottom:none; padding:0 8px;">
                                <li class="nav-item">
                                    <button class="nav-link active" id="tab-datos-tab" data-bs-toggle="tab" data-bs-target="#tab-datos" type="button" role="tab" aria-controls="tab-datos" aria-selected="true" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-user me-1" style="color:#a855f7;"></i>👤 Datos Personales
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-empresa-tab" data-bs-toggle="tab" data-bs-target="#tab-empresa" type="button" role="tab" aria-controls="tab-empresa" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-building me-1" style="color:#a855f7;"></i>🏢 Empresa
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-notas-tab" data-bs-toggle="tab" data-bs-target="#tab-notas" type="button" role="tab" aria-controls="tab-notas" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-sticky-note me-1" style="color:#a855f7;"></i>📝 Notas
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="clienteCreateTabsContent">

                                {{-- Pestaña: Datos Personales --}}
                                <div class="tab-pane fade show active" id="tab-datos" role="tabpanel" aria-labelledby="tab-datos-tab">
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

                                    <div class="row g-3">
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
                                            <label for="dni" class="form-label">{{ $empresa->pais == 'CL' ? 'RUT' : 'DNI / Documento' }}</label>
                                            <input type="text" class="form-control @error('dni') is-invalid @enderror"
                                                   name="dni" id="dni" value="{{ old('dni') }}" placeholder="{{ $empresa->pais == 'CL' ? '12345678' : '12345678' }}">
                                            @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        @if($empresa->pais == 'CL')
                                        <div class="col-md-2">
                                            <label for="rut_dv" class="form-label">DV</label>
                                            <input type="text" class="form-control" name="rut_dv" id="rut_dv" maxlength="1"
                                                   value="{{ old('rut_dv') }}" placeholder="K">
                                        </div>
                                        @endif
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
                                </div>

                                {{-- Pestaña: Empresa --}}
                                <div class="tab-pane fade" id="tab-empresa" role="tabpanel" aria-labelledby="tab-empresa-tab">
                                    <div class="alert alert-info py-2 px-3" style="font-size:12px; border-radius:8px;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Esta sección se habilita cuando el tipo de cliente es <strong>Empresa</strong>.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label for="empresa" class="form-label">Razón Social</label>
                                            <input type="text" class="form-control" name="empresa" id="empresa"
                                                   value="{{ old('empresa') }}" placeholder="Empresa SAC">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ruc" class="form-label">{{ $empresa->pais == 'CL' ? 'RUT Empresa' : 'RUC' }}</label>
                                            <input type="text" class="form-control" name="ruc" id="ruc"
                                                   value="{{ old('ruc') }}" placeholder="{{ $empresa->pais == 'CL' ? '76543210' : '20123456789' }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- Pestaña: Notas --}}
                                <div class="tab-pane fade" id="tab-notas" role="tabpanel" aria-labelledby="tab-notas-tab">
                                    <label for="notas" class="form-label">Notas internas</label>
                                    <textarea class="form-control" name="notas" id="notas" rows="4"
                                              placeholder="Observaciones, preferencias del cliente...">{{ old('notas') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
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
    const datosEmpresa = document.getElementById('tab-empresa');

    radios.forEach(r => r.addEventListener('change', function() {
        // Si es empresa, activar la pestaña Empresa
        if (this.value === 'empresa') {
            const tabEmpresa = document.getElementById('tab-empresa-tab');
            if (tabEmpresa) tabEmpresa.click();
        }
    }));
</script>
@endpush