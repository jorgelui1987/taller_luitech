@extends('layouts.app')
@section('title', 'Nueva Reparación')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#0891b2;">Reparaciones</a></li></ul>
    <ul><li class="breadcrumb-item active">Nueva Orden</li></ul>
@endsection

@push('styles')
<style>
.info-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:500;
}
/* Signature pad */
.signature-pad-wrapper {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    background: #fff;
    position: relative;
    cursor: crosshair;
}
.signature-pad-wrapper canvas {
    display: block;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    touch-action: none;
}
.signature-pad-wrapper .placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #9ca3af;
    font-size: 13px;
    pointer-events: none;
}
/* Photo preview */
.foto-preview-item {
    border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#f9fafb; position:relative;
}
.foto-preview-item img {
    width:100%; height:90px; object-fit:cover;
}
.foto-preview-item .badge {
    position:absolute; bottom:4px; left:4px; font-size:9px; opacity:0.85;
}
.foto-preview-item .btn-remove {
    position:absolute; top:4px; right:4px;
    width:24px; height:24px; border-radius:50%;
    background:rgba(239,68,68,0.9); color:#fff;
    border:none; display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:10px; padding:0;
}
.foto-preview-item .btn-remove:hover { background:#dc2626; transform:scale(1.1); }
/* Buscador de clientes: permitir salto de línea en los resultados */
#clienteResultados .dropdown-item {
    white-space: normal;
    word-break: break-word;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Nueva Orden de Reparación</h5>
                        <p class="text-muted mb-0" style="font-size:13px;">Registra un nuevo equipo para servicio técnico</p>
                    </div>
                    <a href="{{ route('reparaciones.index') }}" class="btn btn-outline-secondary">
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

                <form action="{{ route('reparaciones.store') }}" method="POST" enctype="multipart/form-data" id="orderCreateForm">
                    @csrf

                    {{-- Pestañas del formulario --}}
                    <div class="card mb-4">
                        <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
                            <ul class="nav nav-tabs card-header-tabs" id="createReparacionTabs" role="tablist" style="border-bottom:none; padding:0 8px;">
                                <li class="nav-item">
                                    <button class="nav-link active" id="tab-asignacion-tab" data-bs-toggle="tab" data-bs-target="#tab-asignacion" type="button" role="tab" aria-controls="tab-asignacion" aria-selected="true" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-users me-1" style="color:#0891b2;"></i>👥 Asignación
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-equipo-tab" data-bs-toggle="tab" data-bs-target="#tab-equipo" type="button" role="tab" aria-controls="tab-equipo" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-mobile-alt me-1" style="color:#0891b2;"></i>📱 Equipo
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-falla-tab" data-bs-toggle="tab" data-bs-target="#tab-falla" type="button" role="tab" aria-controls="tab-falla" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-exclamation-triangle me-1" style="color:#0891b2;"></i>⚠️ Falla
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-costos-tab" data-bs-toggle="tab" data-bs-target="#tab-costos" type="button" role="tab" aria-controls="tab-costos" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-dollar-sign me-1" style="color:#0891b2;"></i>💰 Costos
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-fotos-tab" data-bs-toggle="tab" data-bs-target="#tab-fotos" type="button" role="tab" aria-controls="tab-fotos" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-camera me-1" style="color:#0891b2;"></i>📷 Fotos
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-firma-tab" data-bs-toggle="tab" data-bs-target="#tab-firma" type="button" role="tab" aria-controls="tab-firma" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                                        <i class="fas fa-pen me-1" style="color:#0891b2;"></i>✍️ Firma
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="createReparacionTabsContent">

                                {{-- Pestaña: Asignación --}}
                                <div class="tab-pane fade show active" id="tab-asignacion" role="tabpanel" aria-labelledby="tab-asignacion-tab">
                                    <div class="row g-4">
                                        <div class="col-md-5">
                                            <label for="clienteBuscarInput" class="form-label">Cliente <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-2">
                                                <div class="cliente-buscador flex-grow-1" style="position:relative;">
                                                    <input
                                                           type="text"
                                                           id="clienteBuscarInput"
                                                           class="form-control @error('cliente_id') is-invalid @enderror"
                                                           placeholder="🔍 Escribe el nombre del cliente..."
                                                           autocomplete="off"
                                                           value="{{ old('cliente_id', request('cliente')) ? optional($clientes->firstWhere('id', old('cliente_id', request('cliente'))))->nombre_completo : '' }}">
                                                    <input type="hidden" name="cliente_id" id="clienteIdHidden"
                                                           value="{{ old('cliente_id', request('cliente')) }}">
                                                    <div id="clienteResultados" class="dropdown-menu"
                                                         style="display:none; width:100%; max-height:250px; overflow-y:auto;"></div>
                                                </div>
                                                <button type="button" class="btn btn-outline-success" style="white-space:nowrap; flex-shrink:0;" onclick="abrirModalClienteRapido()" title="Registrar nuevo cliente">
                                                    <i class="fas fa-user-plus me-1"></i>Nuevo
                                                </button>
                                            </div>
                                            <div id="clienteSeleccionado" style="font-size:11px; color:#10b981; font-weight:600; min-height:16px;"></div>
                                            @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="tecnico_id" class="form-label">Técnico Asignado <span class="text-danger">*</span></label>
                                            <select name="tecnico_id" id="tecnico_id" class="form-select @error('tecnico_id') is-invalid @enderror" required>
                                                <option value="">— Seleccionar técnico —</option>
                                                @foreach($tecnicos as $t)
                                                    <option value="{{ $t->id }}" {{ old('tecnico_id')==$t->id?'selected':'' }}>
                                                        {{ $t->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tecnico_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label for="prioridad" class="form-label">Prioridad <span class="text-danger">*</span></label>
                                            <select name="prioridad" id="prioridad" class="form-select" required>
                                                <option value="baja" {{ old('prioridad')=='baja'?'selected':'' }}>🟢 Baja</option>
                                                <option value="media" {{ old('prioridad','media')=='media'?'selected':'' }}>🟡 Media</option>
                                                <option value="alta" {{ old('prioridad')=='alta'?'selected':'' }}>🟠 Alta</option>
                                                <option value="urgente" {{ old('prioridad')=='urgente'?'selected':'' }}>🔴 Urgente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Pestaña: Equipo --}}
                                <div class="tab-pane fade" id="tab-equipo" role="tabpanel" aria-labelledby="tab-equipo-tab">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="tipo_dispositivo" class="form-label">📱 Tipo <span class="text-danger">*</span></label>
                                            <select name="tipo_dispositivo" id="tipo_dispositivo" class="form-select @error('tipo_dispositivo') is-invalid @enderror" required>
                                                <option value="">— Seleccionar —</option>
                                                <option value="celular" {{ old('tipo_dispositivo')=='celular'?'selected':'' }}>📱 Celular / Smartphone</option>
                                                <option value="tablet" {{ old('tipo_dispositivo')=='tablet'?'selected':'' }}>📟 Tablet / iPad</option>
                                                <option value="portatil" {{ old('tipo_dispositivo')=='portatil'?'selected':'' }}>💻 Portátil / Laptop</option>
                                                <option value="otros" {{ old('tipo_dispositivo')=='otros'?'selected':'' }}>🔧 Otros</option>
                                            </select>
                                            @error('tipo_dispositivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="marca_select" class="form-label">🏷️ Marca</label>
                                            @php
                                                $marcasPrecargadas = [
                                                    'Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Motorola',
                                                    'LG', 'Sony', 'Nokia', 'Alcatel', 'Honor',
                                                    'Realme', 'Oppo', 'Vivo', 'OnePlus', 'Google',
                                                    'HP', 'Dell', 'Lenovo', 'Acer', 'Asus',
                                                    'Toshiba', 'Microsoft', 'HTC', 'ZTE', 'BlackBerry',
                                                ];
                                                $marcaSeleccionada = old('marca');
                                            @endphp
                                            <select name="marca_select" id="marca_select" class="form-select marca-select" onchange="toggleMarcaInput(this)">
                                                <option value="">— Seleccionar —</option>
                                                @foreach($marcasPrecargadas as $m)
                                                    <option value="{{ $m }}" {{ $marcaSeleccionada==$m?'selected':'' }}>{{ $m }}</option>
                                                @endforeach
                                                <option value="__otra__" {{ $marcaSeleccionada && !in_array($marcaSeleccionada, $marcasPrecargadas) ? 'selected' : '' }}>✏️ Otra</option>
                                            </select>
                                            <label for="marca" class="form-label mt-1">Otra marca</label>
                                            <input type="text" class="form-control marca-input @error('marca') is-invalid @enderror"
                                                   name="marca" id="marca" value="{{ old('marca') }}"
                                                   placeholder="Escribir marca..."
                                                   style="{{ $marcaSeleccionada && !in_array($marcaSeleccionada, $marcasPrecargadas) ? 'display:block;' : 'display:none;' }}">
                                            @error('marca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="modelo" class="form-label">📦 Modelo</label>
                                            <input type="text" class="form-control" name="modelo" id="modelo"
                                                   value="{{ old('modelo') }}" placeholder="Galaxy, iPhone...">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="imei" class="form-label">🔢 IMEI / Serie</label>
                                            <input type="text" class="form-control" name="imei" id="imei"
                                                   value="{{ old('imei') }}" placeholder="123456789012345">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="color" class="form-label">🎨 Color</label>
                                            <input type="text" class="form-control" name="color" id="color"
                                                   value="{{ old('color') }}" placeholder="Negro, Blanco...">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fecha_estimada" class="form-label">📅 Fecha Estimada</label>
                                            <input type="date" class="form-control" name="fecha_estimada" id="fecha_estimada"
                                                   value="{{ old('fecha_estimada') }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tipo_codigo" class="form-label">🔐 Patrón / PIN</label>
                                            <div class="d-flex gap-2">
                                                <select name="tipo_codigo" id="tipo_codigo" class="form-select" style="flex:0 0 110px;" onchange="togglePatronInput(this)">
                                                    <option value="">—</option>
                                                    <option value="pin" {{ old('tipo_codigo')=='pin'?'selected':'' }}>🔢 PIN</option>
                                                    <option value="patron" {{ old('tipo_codigo')=='patron'?'selected':'' }}>🔓 Patrón</option>
                                                </select>
                                                <label for="codigo_equipo" class="visually-hidden">PIN numérico</label>
                                                <input type="text" class="form-control patron-valor" name="codigo_equipo" id="codigo_equipo"
                                                       value="{{ old('codigo_equipo') }}"
                                                       placeholder="PIN numérico" style="display:block;">
                                            </div>
                                            <div class="patron-dibujo mt-2" style="display:none;">
                                                <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">Toca los puntos en orden:</div>
                                                <div style="display:flex; gap:2px; flex-wrap:wrap; max-width:130px;">
                                                    @for($i=1;$i<=9;$i++)
                                                    <button type="button" class="patron-punto" data-pos="{{ $i }}"
                                                            style="width:36px; height:36px; border-radius:50%; border:2px solid #0891b2;
                                                                   display:flex; align-items:center; justify-content:center;
                                                                   font-size:12px; color:#0891b2; cursor:pointer; background:#f8f5ff;
                                                                   transition:all .2s; user-select:none; padding:0;"
                                                            onclick="togglePunto(this)" aria-label="Punto {{ $i }} del patrón">
                                                        {{ $i }}
                                                    </button>
                                                    @endfor
                                                </div>
                                                <input type="hidden" name="patron_secuencia" class="patron-secuencia" value="{{ old('patron_secuencia') }}">
                                                <div style="display:flex; gap:4px; margin-top:4px;">
                                                    <span style="font-size:11px; color:#9ca3af;" class="patron-texto">Ningún punto seleccionado</span>
                                                    <button type="button" onclick="limpiarPatron()" style="font-size:11px; border:none; background:transparent; color:#dc2626; cursor:pointer; padding:0;">✕ Limpiar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Pestaña: Falla --}}
                                <div class="tab-pane fade" id="tab-falla" role="tabpanel" aria-labelledby="tab-falla-tab">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="falla_reportada" class="form-label">Falla Reportada <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('falla_reportada') is-invalid @enderror"
                                                      name="falla_reportada" id="falla_reportada" rows="3"
                                                      placeholder="Describe exactamente qué problema reporta el cliente...">{{ old('falla_reportada') }}</textarea>
                                            @error('falla_reportada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="notas" class="form-label">Notas Adicionales</label>
                                            <textarea class="form-control" name="notas" id="notas" rows="2"
                                                      placeholder="Accesorios recibidos, observaciones al recibir el equipo...">{{ old('notas') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- Pestaña: Costos --}}
                                <div class="tab-pane fade" id="tab-costos" role="tabpanel" aria-labelledby="tab-costos-tab">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="presupuesto" class="form-label">Presupuesto Estimado ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="presupuesto" id="presupuesto"
                                                   value="{{ old('presupuesto', 0) }}" min="0" step="0.01">
                                            <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Dejar en 0 si aún no se determinó</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="abono" class="form-label">Abono ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="abono" id="abono"
                                                   value="{{ old('abono', 0) }}" min="0" step="0.01">
                                            <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Monto pagado por adelantado</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="costo_repuesto" class="form-label">Costo de Repuesto(s) ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="costo_repuesto" id="costo_repuesto"
                                                   value="{{ old('costo_repuesto', 0) }}" min="0" step="0.01">
                                            <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Opcional. Se resta para calcular la ganancia del técnico</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cuponCodigoInput" class="form-label">🎟️ Cupón de Descuento</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="cupon_codigo" id="cuponCodigoInput"
                                                       value="{{ old('cupon_codigo', session('cupon_codigo')) }}" placeholder="Código del cupón (ej: CUP-XXXXXX-XXX)">
                                                <button type="button" class="btn btn-outline-primary" id="validarCuponBtn" onclick="validarCuponReparacion()">
                                                    <i class="fas fa-check me-1"></i>Validar
                                                </button>
                                            </div>
                                            <div id="cuponInfo" class="mt-2" style="font-size:12px;"></div>
                                            <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                                                Si el cliente tiene un cupón de una venta anterior, ingrésalo aquí para aplicar el descuento.
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3" style="background:#fef3c7; border:2px solid #f59e0b;">
                                                <div style="font-size:11px; color:#92400e; font-weight:600;">SALDO PENDIENTE</div>
                                                <label for="total" class="visually-hidden">Saldo pendiente</label>
                                                <input type="number" class="form-control total-auto mt-1" name="total" id="total"
                                                       value="{{ old('total', 0) }}" min="0" step="0.01" readonly
                                                       style="background:#fff3cd; font-weight:700; font-size:18px; border:1px solid #f59e0b;">
                                                <div style="font-size:11px; color:#92400e; margin-top:2px;">Presupuesto - Abono</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="garantia" class="form-label">¿Incluye Garantía?</label>
                                            <select name="garantia" id="garantia" class="form-select">
                                                <option value="0" {{ old('garantia', 0) == 0 ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ old('garantia') == 1 ? 'selected' : '' }}>Sí</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dias_garantia" class="form-label">Días de Garantía</label>
                                            <input type="number" class="form-control" name="dias_garantia" id="dias_garantia"
                                                   value="{{ old('dias_garantia', 0) }}" min="0">
                                        </div>
                                    </div>
                                </div>

                                {{-- Pestaña: Fotos --}}
                                <div class="tab-pane fade" id="tab-fotos" role="tabpanel" aria-labelledby="tab-fotos-tab">
                                    <p class="text-muted mb-2" style="font-size:11px;">Captura el estado actual del equipo al recibirlo.</p>
                                    <div class="mb-2">
                                        <label for="selectTipoFoto" class="form-label">Tipo de foto</label>
                                        <select id="selectTipoFoto" class="form-select form-select-sm">
                                            <option value="frontal">📱 Pantalla / Frontal</option>
                                            <option value="trasero">🔍 Tapa / Trasero</option>
                                            <option value="detalle">⚠️ Detalle / Rayón</option>
                                            <option value="imei">🏷️ IMEI / Serie</option>
                                            <option value="general">📷 Vista General</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="inputFotoCamara" class="form-label">Capturar foto</label>
                                        <input type="file" id="inputFotoCamara" class="form-control form-control-sm" accept="image/*" capture="environment" onchange="agregarEvidenciaFoto(this)">
                                    </div>
                                    <div class="row g-1" id="galeriaEvidenciasPrevias"></div>
                                    <label for="inputFotosEvidencia" class="visually-hidden">Fotos de evidencia seleccionadas</label>
                                    <input type="file" id="inputFotosEvidencia" name="fotos[]" multiple hidden>
                                    <div id="contenedorInputsFotosHidden">
                                        <div id="contenedorTiposFotos"></div>
                                    </div>
                                </div>

                                {{-- Pestaña: Firma --}}
                                <div class="tab-pane fade" id="tab-firma" role="tabpanel" aria-labelledby="tab-firma-tab">
                                    <p class="text-muted mb-2" style="font-size:11px;">Firma aceptando la recepción del equipo.</p>
                                    <div class="signature-pad-wrapper" style="max-width:100%;">
                                        <canvas id="canvasFirmaRecepcionCreate" style="display:block; width:100%; height:140px; border-radius:12px; touch-action:none;"></canvas>
                                        <div id="placeholderFirmaCreate" class="placeholder">
                                            <i class="fas fa-signature me-1"></i>Firme aquí
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirmaCreate()">
                                            <i class="fas fa-eraser me-1"></i>Limpiar
                                        </button>
                                    </div>
                                    <input type="hidden" name="firma_recepcion_data" id="firmaRecepcionDataInput" value="">
                                </div>

                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('reparaciones.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Registrar Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Registro rápido de cliente --}}
<div class="modal fade" id="modalClienteRapido" tabindex="-1" aria-labelledby="modalClienteRapidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#f8f5ff; border-bottom:1px solid #e5e7eb;">
                <h6 class="modal-title fw-bold" id="modalClienteRapidoLabel" style="color:#0891b2;">
                    <i class="fas fa-user-plus me-1"></i>Registrar Nuevo Cliente
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="clienteRapidoMsg" class="mb-3" style="display:none;"></div>
                <form id="formClienteRapido" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="clienteRapidoNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="clienteRapidoNombre" name="nombre" required maxlength="100" placeholder="Ej: Juan">
                    </div>
                    <div class="col-md-6">
                        <label for="clienteRapidoApellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="clienteRapidoApellido" name="apellido" required maxlength="100" placeholder="Ej: Pérez">
                    </div>
                    <div class="col-md-6">
                        <label for="clienteRapidoTelefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="clienteRapidoTelefono" name="telefono" required maxlength="20" placeholder="Ej: 987654321">
                    </div>
                    <div class="col-md-6">
                        <label for="clienteRapidoCelular" class="form-label">Celular</label>
                        <input type="text" class="form-control form-control-sm" id="clienteRapidoCelular" name="celular" maxlength="20" placeholder="Opcional">
                    </div>
                    <div class="col-md-6">
                        <label for="clienteRapidoEmail" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-sm" id="clienteRapidoEmail" name="email" maxlength="150" placeholder="Opcional">
                    </div>
                    <div class="col-md-6">
                        <label for="clienteRapidoDni" class="form-label">DNI / Cédula</label>
                        <input type="text" class="form-control form-control-sm" id="clienteRapidoDni" name="dni" maxlength="15" placeholder="Opcional">
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btnGuardarClienteRapido" onclick="guardarClienteRapido()">
                    <i class="fas fa-check me-1"></i>Guardar Cliente
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" integrity="sha384-dPowQo9uxJU703klzvnG+vzLHQDNmO/zREXw6BhCMupB54CE70wj6SWOGVPySK3s" crossorigin="anonymous"></script>
<script>
// ── FIRMA DIGITAL EN CREACIÓN ──
let signaturePadCreate = null;

function initSignaturePadCreate() {
    const canvas = document.getElementById('canvasFirmaRecepcionCreate');
    if (!canvas) return;

    const wrapper = canvas.parentElement;
    const ratio = Math.max(window.devicePixelRatio || 1, 1);

    // Si el canvas está en una pestaña oculta, usar el ancho del wrapper o un mínimo seguro
    let width = wrapper && wrapper.clientWidth > 100 ? wrapper.clientWidth : 0;
    if (width < 100) {
        const activePane = document.querySelector('.tab-pane.active');
        width = activePane && activePane.clientWidth > 100 ? activePane.clientWidth : 300;
    }

    canvas.width = width * ratio;
    canvas.height = 140 * ratio;

    const ctx = canvas.getContext('2d');
    // Reset + scale (evita acumular transformaciones al reinicializar)
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

    signaturePadCreate = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(15, 23, 42)'
    });

    const placeholder = document.getElementById('placeholderFirmaCreate');
    signaturePadCreate.addEventListener('beginStroke', () => {
        if (placeholder) placeholder.style.display = 'none';
    });
}

function limpiarFirmaCreate() {
    if (signaturePadCreate) {
        signaturePadCreate.clear();
        const placeholder = document.getElementById('placeholderFirmaCreate');
        if (placeholder) placeholder.style.display = 'block';
    }
    const input = document.getElementById('firmaRecepcionDataInput');
    if (input) input.value = '';
}

// ── MANEJO DE FOTOS DE EVIDENCIA ──
let listaFotosEvidencia = [];
let fotosProcesando = 0;

function optimizarFoto(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const image = new Image();
            image.onload = function() {
                const maxDimension = 1600;
                const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(image.width * scale));
                canvas.height = Math.max(1, Math.round(image.height * scale));
                canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function(blob) {
                    resolve(blob ? new File([blob], 'foto_' + Date.now() + '.jpg', { type: 'image/jpeg' }) : file);
                }, 'image/jpeg', 0.82);
            };
            image.onerror = function() { resolve(file); };
            image.src = e.target.result;
        };
        reader.onerror = function() { resolve(file); };
        reader.readAsDataURL(file);
    });
}

async function agregarEvidenciaFoto(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const tipoSelect = document.getElementById('selectTipoFoto');
    const tipo = tipoSelect ? tipoSelect.value : 'general';
    const tipoLabel = tipoSelect ? tipoSelect.options[tipoSelect.selectedIndex].text : tipo;

    fotosProcesando++;
    const fotoOptimizada = await optimizarFoto(file);
    const previewReader = new FileReader();
    previewReader.onload = function(e) {
        listaFotosEvidencia.push({
            id: Date.now() + Math.random(),
            file: fotoOptimizada,
            tipo: tipo,
            tipoLabel: tipoLabel,
            dataUrl: e.target.result
        });
        renderizarFotosPrevias();
        input.value = '';
        fotosProcesando--;
    };
    previewReader.onerror = function() {
        input.value = '';
        fotosProcesando--;
        alert('No se pudo leer la foto capturada.');
    };
    previewReader.readAsDataURL(fotoOptimizada);
}

function eliminarFotoPrevia(id) {
    listaFotosEvidencia = listaFotosEvidencia.filter(f => f.id !== id);
    renderizarFotosPrevias();
}

function renderizarFotosPrevias() {
    const galeria = document.getElementById('galeriaEvidenciasPrevias');
    const fileInput = document.getElementById('inputFotosEvidencia');
    const tiposContainer = document.getElementById('contenedorTiposFotos');
    galeria.innerHTML = '';
    tiposContainer.innerHTML = '';

    const dt = new DataTransfer();

    listaFotosEvidencia.forEach((foto, index) => {
        const col = document.createElement('div');
        col.className = 'col-4';
        col.innerHTML = `
            <div class="foto-preview-item">
                <img src="${foto.dataUrl}" alt="Foto">
                <span class="badge bg-dark">${foto.tipoLabel}</span>
                <button type="button" onclick="eliminarFotoPrevia(${foto.id})" class="btn-remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        galeria.appendChild(col);

        const hiddenTipo = document.createElement('input');
        hiddenTipo.type = 'hidden';
        hiddenTipo.name = 'fotos_tipos[]';
        hiddenTipo.value = foto.tipo;
        tiposContainer.appendChild(hiddenTipo);

        dt.items.add(foto.file);
    });

    fileInput.files = dt.files;
}

// ── Mostrar errores de validación del formulario de forma visible ──
function mostrarErrorFormulario(mensaje) {
    let alerta = document.getElementById('alertaErroresForm');
    if (!alerta) {
        alerta = document.createElement('div');
        alerta.id = 'alertaErroresForm';
        alerta.className = 'alert alert-danger';
        const form = document.getElementById('orderCreateForm');
        form.prepend(alerta);
    }
    alerta.innerHTML = '<ul class="mb-0 ps-3"><li style="font-size:13px;">' + mensaje + '</li></ul>';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Envío AJAX con fotos adjuntadas manualmente ──
// Evita el fallo silencioso de DataTransfer.files en algunos navegadores
// móviles/webviews, donde el input oculto queda vacío y las fotos nunca llegan.
async function enviarFormularioConFotos(form) {
    const btnSubmit = form.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit ? btnSubmit.innerHTML : '';
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando orden...';
    }

    try {
        // Construir FormData con todos los campos del formulario (incluye _token)
        const formData = new FormData(form);

        // Descartar lo que el navegador haya puesto en 'fotos' (puede venir vacío/roto)
        formData.delete('fotos');
        formData.delete('fotos[]');

        // Adjuntar las fotos directamente desde la lista en memoria
        listaFotosEvidencia.forEach((foto, index) => {
            formData.append('fotos[]', foto.file, foto.file.name || ('foto_' + index + '.jpg'));
            formData.append('fotos_tipos[]', foto.tipo);
        });

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (response.ok) {
            const data = await response.json();
            window.location.href = data.redirect;
            return;
        }

        if (response.status === 422) {
            const data = await response.json();
            const errores = Object.values(data.errors || {}).flat().join('<br>');
            mostrarErrorFormulario(errores || data.message || 'Datos inválidos.');
            return;
        }

        mostrarErrorFormulario('Error del servidor (' + response.status + '). Intenta nuevamente.');
    } catch (err) {
        console.error('Error al enviar la orden:', err);
        mostrarErrorFormulario('Error de conexión al guardar la orden. Verifica tu conexión e intenta de nuevo.');
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = textoOriginal;
        }
    }
}

// Al enviar el formulario, guardar la firma
document.addEventListener('DOMContentLoaded', function() {
    initSignaturePadCreate();

    // Reinicializar cuando la pestaña "Firma" se hace visible
    const firmaTab = document.getElementById('tab-firma-tab');
    if (firmaTab) {
        firmaTab.addEventListener('shown.bs.tab', function() {
            setTimeout(initSignaturePadCreate, 100);
        });
    }

    const form = document.getElementById('orderCreateForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (fotosProcesando > 0) {
                event.preventDefault();
                alert('Espera un momento mientras se preparan las fotos.');
                return;
            }
            renderizarFotosPrevias();
            if (signaturePadCreate && !signaturePadCreate.isEmpty()) {
                document.getElementById('firmaRecepcionDataInput').value = signaturePadCreate.toDataURL('image/png');
            }

            // Si hay fotos capturadas, enviar por AJAX adjuntándolas manualmente
            if (listaFotosEvidencia.length > 0) {
                event.preventDefault();
                enviarFormularioConFotos(form);
            }
        });
    }
});

// ── Toggle Marca ──
function toggleMarcaInput(select) {
    const input = select.closest('.col-md-4').querySelector('.marca-input');
    const marcaSelect = select.value;
    if (marcaSelect === '__otra__') {
        input.style.display = 'block';
        input.value = '';
        input.focus();
    } else {
        input.style.display = 'none';
        input.value = marcaSelect;
    }
}

// ── Dibujo de Patrón 3x3 ──
let patronPuntos = [];

function togglePatronInput(select) {
    const container = select.closest('.col-md-6');
    const dibujo = container.querySelector('.patron-dibujo');
    const valorInput = container.querySelector('.patron-valor');
    const tipo = select.value;

    if (tipo === 'patron') {
        dibujo.style.display = 'block';
        valorInput.style.display = 'none';
        valorInput.value = '';
    } else if (tipo === 'pin') {
        dibujo.style.display = 'none';
        valorInput.style.display = 'block';
        valorInput.placeholder = 'PIN numérico (ej: 1234)';
        limpiarPatron();
    } else {
        dibujo.style.display = 'none';
        valorInput.style.display = 'block';
        valorInput.placeholder = 'Valor del PIN o patrón';
        limpiarPatron();
    }
}

function togglePunto(el) {
    const container = el.closest('.col-md-6');
    const pos = parseInt(el.dataset.pos);
    const idx = patronPuntos.indexOf(pos);

    if (idx === -1) {
        patronPuntos.push(pos);
        el.style.background = 'linear-gradient(135deg, #0891b2, #3b82f6)';
        el.style.color = '#fff';
        el.style.borderColor = 'transparent';
        el.style.transform = 'scale(1.1)';
        el.textContent = patronPuntos.length;
    } else {
        patronPuntos.splice(idx, 1);
        el.style.background = '#f8f5ff';
        el.style.color = '#0891b2';
        el.style.borderColor = '#0891b2';
        el.style.transform = 'scale(1)';
        patronPuntos.forEach((p, i) => {
            const punto = container.querySelector(`.patron-punto[data-pos="${p}"]`);
            if (punto) punto.textContent = i + 1;
        });
    }

    actualizarPatronTexto(container);
}

function limpiarPatron() {
    patronPuntos = [];
    document.querySelectorAll('.patron-punto').forEach(el => {
        el.style.background = '#f8f5ff';
        el.style.color = '#0891b2';
        el.style.borderColor = '#0891b2';
        el.style.transform = 'scale(1)';
        el.textContent = el.dataset.pos;
    });
    document.querySelectorAll('.patron-texto').forEach(el => el.textContent = 'Ningún punto seleccionado');
    document.querySelectorAll('.patron-secuencia').forEach(el => el.value = '');
}

function actualizarPatronTexto(container) {
    const texto = container.querySelector('.patron-texto');
    const hidden = container.querySelector('.patron-secuencia');
    if (patronPuntos.length === 0) {
        texto.textContent = 'Ningún punto seleccionado';
        hidden.value = '';
    } else {
        const secuencia = patronPuntos.join('-');
        texto.textContent = `Secuencia: ${secuencia}`;
        hidden.value = secuencia;
    }
}

// ── Auto-calcular Total = Presupuesto - Abono ──
document.addEventListener('input', function(e) {
    if (e.target.name === 'presupuesto' || e.target.name === 'abono') {
        const presupuesto = parseFloat(document.querySelector('input[name="presupuesto"]').value) || 0;
        const abono = parseFloat(document.querySelector('input[name="abono"]').value) || 0;
        const totalInput = document.querySelector('input[name="total"]');
        if (totalInput) totalInput.value = Math.max(0, presupuesto - abono).toFixed(2);
    }
});

// ── BUSCADOR DE CLIENTES (AUTOCOMPLETADO) ──
let clienteTimer = null;

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('clienteBuscarInput');
    const resultados = document.getElementById('clienteResultados');
    const hidden = document.getElementById('clienteIdHidden');
    const seleccionado = document.getElementById('clienteSeleccionado');

    if (!input) return;

    // Mostrar cliente seleccionado si ya hay uno (por old())
    if (hidden.value) {
        const nombre = input.value;
        if (nombre) {
            seleccionado.innerHTML = '✅ ' + nombre;
        }
    }

    input.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(clienteTimer);

        if (q.length < 1) {
            resultados.style.display = 'none';
            hidden.value = '';
            seleccionado.innerHTML = '';
            return;
        }

        clienteTimer = setTimeout(function() {
            fetch('{{ route("api.clientes.buscar") }}?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(clientes => {
                if (clientes.length === 0) {
                    resultados.innerHTML = '<div class="dropdown-item text-muted" style="font-size:12px;">No se encontraron clientes con "' + q + '"</div>';
                    resultados.style.display = 'block';
                    return;
                }

                resultados.innerHTML = clientes.map(c => {
                    const nombre = (c.nombre || '') + ' ' + (c.apellido || '');
                    const tel = c.telefono || c.celular || '';
                    const dni = c.dni ? ' | DNI: ' + c.dni : '';
                    return '<a href="#" class="dropdown-item" data-id="' + c.id + '" data-nombre="' + nombre.replace(/"/g, '"') + '" style="font-size:13px; padding:6px 10px; white-space:normal; word-break:break-word;">' +
                        '<div style="font-weight:600;">' + nombre + '</div>' +
                        '<div style="font-size:11px; color:#9ca3af;">' + (tel ? '📞 ' + tel : '') + dni + '</div>' +
                    '</a>';
                }).join('');

                resultados.style.display = 'block';

                // Click en un resultado
                resultados.querySelectorAll('.dropdown-item[data-id]').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const nombre = this.dataset.nombre;
                        input.value = nombre;
                        hidden.value = id;
                        seleccionado.innerHTML = '✅ ' + nombre;
                        resultados.style.display = 'none';
                    });
                });
            })
            .catch(err => {
                console.error('Error buscando clientes:', err);
            });
        }, 300);
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.cliente-buscador')) {
            resultados.style.display = 'none';
        }
    });

    // Limpiar selección si el usuario borra el texto
    input.addEventListener('blur', function() {
        setTimeout(function() {
            if (!hidden.value) {
                seleccionado.innerHTML = '';
            }
        }, 200);
    });
});

// ── REGISTRO RÁPIDO DE CLIENTE ──
function abrirModalClienteRapido() {
    // Limpiar campos y mensajes al abrir
    document.getElementById('formClienteRapido').reset();
    document.getElementById('clienteRapidoMsg').style.display = 'none';
    document.getElementById('clienteRapidoMsg').innerHTML = '';
    // Eventualmente precargar el nombre escrito si existe
    const buscador = document.getElementById('clienteBuscarInput');
    if (buscador && buscador.value.trim() && !document.getElementById('clienteIdHidden').value) {
        // Si escribió algo que no encontró, precargar en nombre (poner en nombre solo)
        const texto = buscador.value.trim();
        const partes = texto.split(' ');
        document.getElementById('clienteRapidoNombre').value = partes.shift() || '';
        document.getElementById('clienteRapidoApellido').value = partes.join(' ');
    }
    // Mostrar modal
    const modal = document.getElementById('modalClienteRapido');
    const modalBootstrap = bootstrap.Modal.getOrCreateInstance(modal);
    modalBootstrap.show();
}

function guardarClienteRapido() {
    const form = document.getElementById('formClienteRapido');
    const msg = document.getElementById('clienteRapidoMsg');
    const btn = document.getElementById('btnGuardarClienteRapido');

    // Validar campos requeridos
    if (!form.nombre.value.trim() || !form.apellido.value.trim() || !form.telefono.value.trim()) {
        msg.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size:12px;"><i class="fas fa-exclamation-triangle me-1"></i>Nombre, apellido y teléfono son obligatorios.</div>';
        msg.style.display = 'block';
        return;
    }

    // Deshabilitar botón
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
    msg.style.display = 'none';

    const data = new FormData(form);

    fetch('{{ route("reparaciones.cliente-rapido") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: data,
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Seleccionar el cliente recién creado en el buscador
            const input = document.getElementById('clienteBuscarInput');
            const hidden = document.getElementById('clienteIdHidden');
            const seleccionado = document.getElementById('clienteSeleccionado');

            input.value = data.cliente.nombre_completo;
            hidden.value = data.cliente.id;
            seleccionado.innerHTML = '✅ ' + data.cliente.nombre_completo;

            // Cerrar modal
            const modal = document.getElementById('modalClienteRapido');
            const modalBootstrap = bootstrap.Modal.getInstance(modal);
            if (modalBootstrap) modalBootstrap.hide();

            // Mostrar mensaje de éxito temporal
            msg.innerHTML = '<div class="alert alert-success py-2 px-3 mb-0" style="font-size:12px;"><i class="fas fa-check-circle me-1"></i>' + data.message + '</div>';
            msg.style.display = 'block';
        } else {
            msg.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size:12px;"><i class="fas fa-times-circle me-1"></i>' + (data.message || 'Error al guardar el cliente.') + '</div>';
            msg.style.display = 'block';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        msg.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size:12px;"><i class="fas fa-times-circle me-1"></i>Error de conexión al guardar el cliente.</div>';
        msg.style.display = 'block';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Guardar Cliente';
    });
}

// ── VALIDAR CUPÓN DE DESCUENTO ──
function validarCuponReparacion() {
    const codigo = document.getElementById('cuponCodigoInput').value.trim();
    const infoDiv = document.getElementById('cuponInfo');

    if (!codigo) {
        infoDiv.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Ingresa un código de cupón.</span>';
        return;
    }

    fetch('{{ route("api.cupon.validar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ codigo: codigo }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const cupon = data.cupon;
            const valorTexto = cupon.tipo === 'porcentaje' ? cupon.valor + '%' : '{{ $empresa->simbolo_moneda ?? '$' }} ' + cupon.valor;
            infoDiv.innerHTML = `
                <div class="alert alert-success py-2 px-3 mb-0" style="font-size:12px;">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>Cupón válido:</strong> ${valorTexto} de descuento
                    ${cupon.descripcion ? '<br><small class="text-muted">' + cupon.descripcion + '</small>' : ''}
                </div>
            `;
            document.getElementById('validarCuponBtn').classList.add('btn-success');
            document.getElementById('validarCuponBtn').classList.remove('btn-outline-primary');
        } else {
            infoDiv.innerHTML = `
                <div class="alert alert-danger py-2 px-3 mb-0" style="font-size:12px;">
                    <i class="fas fa-times-circle me-1"></i>${data.message || 'Cupón no válido.'}
                </div>
            `;
            document.getElementById('validarCuponBtn').classList.remove('btn-success');
            document.getElementById('validarCuponBtn').classList.add('btn-outline-primary');
        }
    })
    .catch(err => {
        infoDiv.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size:12px;"><i class="fas fa-times-circle me-1"></i>Error de conexión al validar el cupón.</div>';
        console.error(err);
    });
}
</script>
@endpush