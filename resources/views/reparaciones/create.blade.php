@extends('layouts.app')
@section('title', 'Nueva Reparación')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#a855f7;">Reparaciones</a></li>
    <li class="breadcrumb-item active">Nueva Orden</li>
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
                            @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reparaciones.store') }}" method="POST" enctype="multipart/form-data" id="orderCreateForm">
                    @csrf

                    <div class="row g-4">

                        {{-- ============================================================ --}}
                        {{-- COLUMNA PRINCIPAL (ASIGNACIÓN + EQUIPO + FALLA) --}}
                        {{-- ============================================================ --}}
                        <div class="col-lg-8">

                            {{-- Acordeón móvil: Asignación --}}
                            <div class="accordion-mobile">
                                <div class="accordion-item mb-4">
                                    <div class="accordion-header active">
                                        <span><i class="fas fa-users me-2" style="color:#a855f7;"></i>Asignación</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="accordion-body show">
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label">Cliente <span class="text-danger">*</span></label>
                                                <select name="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
                                                    <option value="">— Seleccionar cliente —</option>
                                                    @foreach($clientes as $c)
                                                        <option value="{{ $c->id }}"
                                                                {{ (old('cliente_id', request('cliente')) == $c->id) ? 'selected' : '' }}>
                                                            {{ $c->nombre_completo }} — {{ $c->telefono }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Técnico Asignado <span class="text-danger">*</span></label>
                                                <select name="tecnico_id" class="form-select @error('tecnico_id') is-invalid @enderror" required>
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
                                                <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                                                <select name="prioridad" class="form-select" required>
                                                    <option value="baja" {{ old('prioridad')=='baja'?'selected':'' }}>🟢 Baja</option>
                                                    <option value="media" {{ old('prioridad','media')=='media'?'selected':'' }}>🟡 Media</option>
                                                    <option value="alta" {{ old('prioridad')=='alta'?'selected':'' }}>🟠 Alta</option>
                                                    <option value="urgente" {{ old('prioridad')=='urgente'?'selected':'' }}>🔴 Urgente</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Acordeón móvil: Datos del Equipo --}}
                            <div class="accordion-mobile">
                                <div class="accordion-item mb-4">
                                    <div class="accordion-header active">
                                        <span><i class="fas fa-mobile-alt me-2" style="color:#a855f7;"></i>Datos del Equipo</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="accordion-body show">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">📱 Tipo <span class="text-danger">*</span></label>
                                                <select name="tipo_dispositivo" class="form-select @error('tipo_dispositivo') is-invalid @enderror" required>
                                                    <option value="">— Seleccionar —</option>
                                                    <option value="celular" {{ old('tipo_dispositivo')=='celular'?'selected':'' }}>📱 Celular / Smartphone</option>
                                                    <option value="tablet" {{ old('tipo_dispositivo')=='tablet'?'selected':'' }}>📟 Tablet / iPad</option>
                                                    <option value="portatil" {{ old('tipo_dispositivo')=='portatil'?'selected':'' }}>💻 Portátil / Laptop</option>
                                                    <option value="otros" {{ old('tipo_dispositivo')=='otros'?'selected':'' }}>🔧 Otros</option>
                                                </select>
                                                @error('tipo_dispositivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">🏷️ Marca</label>
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
                                                <select name="marca_select" class="form-select marca-select" onchange="toggleMarcaInput(this)">
                                                    <option value="">— Seleccionar —</option>
                                                    @foreach($marcasPrecargadas as $m)
                                                        <option value="{{ $m }}" {{ $marcaSeleccionada==$m?'selected':'' }}>{{ $m }}</option>
                                                    @endforeach
                                                    <option value="__otra__" {{ $marcaSeleccionada && !in_array($marcaSeleccionada, $marcasPrecargadas) ? 'selected' : '' }}>✏️ Otra</option>
                                                </select>
                                                <input type="text" class="form-control marca-input mt-1 @error('marca') is-invalid @enderror"
                                                       name="marca" value="{{ old('marca') }}"
                                                       placeholder="Escribir marca..."
                                                       style="{{ $marcaSeleccionada && !in_array($marcaSeleccionada, $marcasPrecargadas) ? 'display:block;' : 'display:none;' }}">
                                                @error('marca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">📦 Modelo</label>
                                                <input type="text" class="form-control" name="modelo"
                                                       value="{{ old('modelo') }}" placeholder="Galaxy, iPhone...">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">🔢 IMEI / Serie</label>
                                                <input type="text" class="form-control" name="imei"
                                                       value="{{ old('imei') }}" placeholder="123456789012345">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">🎨 Color</label>
                                                <input type="text" class="form-control" name="color"
                                                       value="{{ old('color') }}" placeholder="Negro, Blanco...">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">📅 Fecha Estimada</label>
                                                <input type="date" class="form-control" name="fecha_estimada"
                                                       value="{{ old('fecha_estimada') }}" min="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">🔐 Patrón / PIN</label>
                                                <div class="d-flex gap-2">
                                                    <select name="tipo_codigo" class="form-select" style="flex:0 0 110px;" onchange="togglePatronInput(this)">
                                                        <option value="">—</option>
                                                        <option value="pin" {{ old('tipo_codigo')=='pin'?'selected':'' }}>🔢 PIN</option>
                                                        <option value="patron" {{ old('tipo_codigo')=='patron'?'selected':'' }}>🔓 Patrón</option>
                                                    </select>
                                                    <input type="text" class="form-control patron-valor" name="codigo_equipo"
                                                           value="{{ old('codigo_equipo') }}"
                                                           placeholder="PIN numérico" style="display:block;">
                                                </div>
                                                <div class="patron-dibujo mt-2" style="display:none;">
                                                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">Toca los puntos en orden:</div>
                                                    <div style="display:flex; gap:2px; flex-wrap:wrap; max-width:130px;">
                                                        @for($i=1;$i<=9;$i++)
                                                        <div class="patron-punto" data-pos="{{ $i }}"
                                                             style="width:36px; height:36px; border-radius:50%; border:2px solid #a855f7;
                                                                    display:flex; align-items:center; justify-content:center;
                                                                    font-size:12px; color:#a855f7; cursor:pointer; background:#f8f5ff;
                                                                    transition:all .2s; user-select:none;"
                                                             onclick="togglePunto(this)">
                                                            {{ $i }}
                                                        </div>
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
                                </div>
                            </div>

                            {{-- Acordeón móvil: Falla Reportada --}}
                            <div class="accordion-mobile">
                                <div class="accordion-item mb-4">
                                    <div class="accordion-header active">
                                        <span><i class="fas fa-exclamation-triangle me-2" style="color:#a855f7;"></i>Falla Reportada <span class="info-badge ms-2" style="background:#fef3c7; color:#92400e;">Cliente</span></span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="accordion-body show">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <textarea class="form-control @error('falla_reportada') is-invalid @enderror"
                                                          name="falla_reportada" rows="3"
                                                          placeholder="Describe exactamente qué problema reporta el cliente...">{{ old('falla_reportada') }}</textarea>
                                                @error('falla_reportada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Notas Adicionales</label>
                                                <textarea class="form-control" name="notas" rows="2"
                                                          placeholder="Accesorios recibidos, observaciones al recibir el equipo...">{{ old('notas') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ============================================================ --}}
                        {{-- COLUMNA LATERAL (COSTOS + FOTOS + FIRMA) --}}
                        {{-- ============================================================ --}}
                        <div class="col-lg-4">

                            {{-- Acordeón móvil: Costos --}}
                            <div class="accordion-mobile">
                                <div class="accordion-item mb-3">
                                    <div class="accordion-header active">
                                        <span><i class="fas fa-dollar-sign me-2" style="color:#a855f7;"></i>Costos</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="accordion-body show">
                                        <div class="d-flex flex-column gap-2">
                                            <div>
                                                <label class="form-label" style="font-size:12px;">Presupuesto Estimado (S/)</label>
                                                <input type="number" class="form-control" name="presupuesto"
                                                       value="{{ old('presupuesto', 0) }}" min="0" step="0.01">
                                                <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Dejar en 0 si aún no se determinó</div>
                                            </div>
                                            <div>
                                                <label class="form-label" style="font-size:12px;">Abono (S/)</label>
                                                <input type="number" class="form-control" name="abono"
                                                       value="{{ old('abono', 0) }}" min="0" step="0.01">
                                                <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Monto pagado por adelantado</div>
                                            </div>
                                            <div>
                                                <label class="form-label" style="font-size:12px;">Costo de Repuesto(s) (S/)</label>
                                                <input type="number" class="form-control" name="costo_repuesto"
                                                       value="{{ old('costo_repuesto', 0) }}" min="0" step="0.01">
                                                <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Opcional. Se resta para calcular la ganancia del técnico</div>
                                            </div>
                                            <div class="p-3 rounded-3" style="background:#fef3c7; border:2px solid #f59e0b;">
                                                <div style="font-size:11px; color:#92400e; font-weight:600;">SALDO PENDIENTE</div>
                                                <input type="number" class="form-control total-auto mt-1" name="total"
                                                       value="{{ old('total', 0) }}" min="0" step="0.01" readonly
                                                       style="background:#fff3cd; font-weight:700; font-size:18px; border:1px solid #f59e0b;">
                                                <div style="font-size:11px; color:#92400e; margin-top:2px;">Presupuesto - Abono</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Acordeón móvil: Fotos de Evidencia --}}
                            <div class="accordion-mobile">
                                <div class="accordion-item mb-3">
                                    <div class="accordion-header active">
                                        <span><i class="fas fa-camera me-2" style="color:#a855f7;"></i>Fotos de Evidencia</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="accordion-body show">
                                        <p class="text-muted mb-2" style="font-size:11px;">Captura el estado actual del equipo al recibirlo.</p>
                                        <div class="mb-2">
                                            <select id="selectTipoFoto" class="form-select form-select-sm">
                                                <option value="frontal">📱 Pantalla / Frontal</option>
                                                <option value="trasero">🔍 Tapa / Trasero</option>
                                                <option value="detalle">⚠️ Detalle / Rayón</option>
                                                <option value="imei">🏷️ IMEI / Serie</option>
                                                <option value="general">📷 Vista General</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <input type="file" id="inputFotoCamara" class="form-control form-control-sm" accept="image/*" capture="environment" onchange="agregarEvidenciaFoto(this)">
                                        </div>
                                        <div class="row g-1" id="galeriaEvidenciasPrevias"></div>
                                        <div id="contenedorInputsFotosHidden"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Acordeón móvil: Firma del Cliente --}}
                            <div class="accordion-mobile">
                                <div class="accordion-item mb-3">
                                    <div class="accordion-header active">
                                        <span><i class="fas fa-pen me-2" style="color:#a855f7;"></i>Firma del Cliente</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="accordion-body show">
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
// ── FIRMA DIGITAL EN CREACIÓN ──
let signaturePadCreate = null;

function initSignaturePadCreate() {
    const canvas = document.getElementById('canvasFirmaRecepcionCreate');
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const width = rect.width || (canvas.parentElement ? canvas.parentElement.clientWidth : 500);

    canvas.width = width * ratio;
    canvas.height = 140 * ratio;

    const ctx = canvas.getContext('2d');
    ctx.scale(ratio, ratio);

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

function agregarEvidenciaFoto(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const tipoSelect = document.getElementById('selectTipoFoto');
    const tipo = tipoSelect ? tipoSelect.value : 'general';
    const tipoLabel = tipoSelect ? tipoSelect.options[tipoSelect.selectedIndex].text : tipo;

    const reader = new FileReader();
    reader.onload = function(e) {
        const fotoObj = {
            id: Date.now() + Math.random(),
            file: file,
            tipo: tipo,
            tipoLabel: tipoLabel,
            dataUrl: e.target.result
        };
        listaFotosEvidencia.push(fotoObj);
        renderizarFotosPrevias();
        input.value = '';
    };
    reader.readAsDataURL(file);
}

function eliminarFotoPrevia(id) {
    listaFotosEvidencia = listaFotosEvidencia.filter(f => f.id !== id);
    renderizarFotosPrevias();
}

function renderizarFotosPrevias() {
    const galeria = document.getElementById('galeriaEvidenciasPrevias');
    const hiddenContainer = document.getElementById('contenedorInputsFotosHidden');
    galeria.innerHTML = '';
    hiddenContainer.innerHTML = '';

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
        hiddenContainer.appendChild(hiddenTipo);

        dt.items.add(foto.file);
    });

    if (listaFotosEvidencia.length > 0) {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.name = 'fotos[]';
        fileInput.multiple = true;
        fileInput.style.display = 'none';
        fileInput.files = dt.files;
        hiddenContainer.appendChild(fileInput);
    }
}

// Al enviar el formulario, guardar la firma
document.addEventListener('DOMContentLoaded', function() {
    initSignaturePadCreate();

    const form = document.getElementById('orderCreateForm');
    if (form) {
        form.addEventListener('submit', function() {
            if (signaturePadCreate && !signaturePadCreate.isEmpty()) {
                document.getElementById('firmaRecepcionDataInput').value = signaturePadCreate.toDataURL('image/png');
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
        el.style.background = 'linear-gradient(135deg, #a855f7, #ec4899)';
        el.style.color = '#fff';
        el.style.borderColor = 'transparent';
        el.style.transform = 'scale(1.1)';
        el.textContent = patronPuntos.length;
    } else {
        patronPuntos.splice(idx, 1);
        el.style.background = '#f8f5ff';
        el.style.color = '#a855f7';
        el.style.borderColor = '#a855f7';
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
        el.style.color = '#a855f7';
        el.style.borderColor = '#a855f7';
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
</script>
@endpush