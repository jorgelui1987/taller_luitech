@extends('layouts.app')
@section('title', 'Actualizar Reparación')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#0891b2;">Reparaciones</a></li></ul>
    <ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.show', $reparacion) }}" style="color:#0891b2;">{{ $reparacion->numero_orden }}</a></li></ul>
    <ul><li class="breadcrumb-item active">Editar</li></ul>
@endsection

@push('styles')
<style>
.ot-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;}
.ot-sec{background:#f8fafc;border:1px solid #eef2f7;border-radius:12px;padding:12px;margin-bottom:10px;}
.ot-sec-title{font-size:12.5px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.ot-sec-title i{color:var(--accent1);}
.ot-sec .form-label{font-size:12px;margin-bottom:2px;}
.ot-sec .form-control,.ot-sec .form-select{font-size:13px;padding:6px 10px;}
.ot-sec textarea.form-control{rows:1;}
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
    height: 160px;
    border-radius: 12px;
    touch-action: none;
}
.signature-pad-wrapper .placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #9ca3af;
    font-size: 14px;
    pointer-events: none;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-1">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Actualizar Orden: {{ $reparacion->numero_orden }}</h5>
                        <p class="text-muted mb-0" style="font-size:13px;">
                            {{ $reparacion->dispositivo }} — {{ $reparacion->cliente->nombre_completo ?? '' }}
                        </p>
                    </div>
                    <a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-eye me-1"></i>Ver Detalle
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<ul><li style="font-size:13px;">{{ $e }}</li></ul>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reparaciones.update', $reparacion) }}" method="POST">
                    @csrf @method('PUT')

                    {{-- Formulario horizontal 3 columnas parejas --}}
                    <div class="row g-2">
                        {{-- Columna 1: estado + equipo --}}
                        <div class="col-xxl-4 col-lg-4 col-md-6">
                            <div class="ot-sec mb-2">
                                <div class="ot-sec-title"><i class="fas fa-tasks"></i>Estado y asignación</div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label for="estado" class="form-label">Estado *</label>
                                            <select name="estado" id="estado" class="form-select" required>
                                                @php $estados = ['recibido'=>'Recibido','en_diagnostico'=>'En Diagnóstico','esperando_repuesto'=>'Esperando Repuesto','en_reparacion'=>'En Reparación','listo'=>'Listo para Entregar','entregado'=>'Entregado','no_reparable'=>'No Reparable']; @endphp
                                                @foreach($estados as $val => $lbl)
                                                    <option value="{{ $val }}" {{ old('estado',$reparacion->estado)==$val?'selected':'' }}>
                                                        {{ $lbl }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label for="prioridad" class="form-label">Prioridad</label>
                                            <select name="prioridad" id="prioridad" class="form-select">
                                                <option value="baja" {{ old('prioridad',$reparacion->prioridad)=='baja'?'selected':'' }}>Baja</option>
                                                <option value="media" {{ old('prioridad',$reparacion->prioridad)=='media'?'selected':'' }}>Media</option>
                                                <option value="alta" {{ old('prioridad',$reparacion->prioridad)=='alta'?'selected':'' }}>Alta</option>
                                                <option value="urgente" {{ old('prioridad',$reparacion->prioridad)=='urgente'?'selected':'' }}>Urgente</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label for="tecnico_id" class="form-label">Técnico Asignado</label>
                                            <select name="tecnico_id" id="tecnico_id" class="form-select">
                                                @foreach($tecnicos as $t)
                                                    <option value="{{ $t->id }}" {{ old('tecnico_id',$reparacion->tecnico_id)==$t->id?'selected':'' }}>
                                                        {{ $t->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                            </div>
                            <div class="ot-sec mb-2">
                                <div class="ot-sec-title"><i class="fas fa-mobile-alt"></i>Equipo</div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label for="tipo_dispositivo" class="form-label">Tipo *</label>
                                            <select name="tipo_dispositivo" id="tipo_dispositivo" class="form-select" required>
                                                <option value="">— Seleccionar tipo —</option>
                                                <option value="celular" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='celular'?'selected':'' }}>Celular / Smartphone</option>
                                                <option value="tablet" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='tablet'?'selected':'' }}>Tablet / iPad</option>
                                                <option value="portatil" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='portatil'?'selected':'' }}>Portátil / Laptop</option>
                                                <option value="otros" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='otros'?'selected':'' }}>Otros</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label for="marca_select" class="form-label">Marca</label>
                                            @php
                                                $marcasPrecargadas = [
                                                    'Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Motorola',
                                                    'LG', 'Sony', 'Nokia', 'Alcatel', 'Honor',
                                                    'Realme', 'Oppo', 'Vivo', 'OnePlus', 'Google',
                                                    'HP', 'Dell', 'Lenovo', 'Acer', 'Asus',
                                                    'Toshiba', 'Microsoft', 'HTC', 'ZTE', 'BlackBerry',
                                                ];
                                                $marcaActual = old('marca', $reparacion->marca ?? '');
                                                $esOtra = $marcaActual && !in_array($marcaActual, $marcasPrecargadas);
                                            @endphp
                                            <select name="marca_select" id="marca_select" class="form-select marca-select" onchange="toggleMarcaInputEdit(this)">
                                                <option value="">— Seleccionar o escribir —</option>
                                                @foreach($marcasPrecargadas as $m)
                                                    <option value="{{ $m }}" {{ $marcaActual==$m?'selected':'' }}>{{ $m }}</option>
                                                @endforeach
                                                <option value="__otra__" {{ $esOtra?'selected':'' }}>Otra (escribir manualmente)</option>
                                            </select>
                                            <label for="marca" class="form-label mt-1">Otra marca</label>
                                            <input type="text" class="form-control marca-input" name="marca" id="marca"
                                                   value="{{ old('marca',$reparacion->marca) }}"
                                                   placeholder="Escribir marca manualmente..."
                                                   style="{{ $esOtra ? 'display:block;' : 'display:none;' }}">
                                        </div>
                                        <div class="col-4">
                                            <label for="modelo" class="form-label">Modelo</label>
                                            <input type="text" class="form-control form-control-sm" name="modelo" id="modelo"
                                                   value="{{ old('modelo',$reparacion->modelo) }}">
                                        </div>
                                        <div class="col-4">
                                            <label for="imei" class="form-label">IMEI / Serie</label>
                                            <input type="text" class="form-control form-control-sm" name="imei" id="imei"
                                                   value="{{ old('imei',$reparacion->imei) }}">
                                        </div>
                                        <div class="col-4">
                                            <label for="color" class="form-label">Color</label>
                                            <input type="text" class="form-control form-control-sm" name="color" id="color"
                                                   value="{{ old('color',$reparacion->color) }}">
                                        </div>
                                        <div class="col-6">
                                            <label for="fecha_estimada" class="form-label">Fecha Estimada</label>
                                            <input type="date" class="form-control form-control-sm" name="fecha_estimada" id="fecha_estimada"
                                                   value="{{ old('fecha_estimada', optional($reparacion->fecha_estimada)->format('Y-m-d')) }}">
                                        </div>
                                        <div class="col-6">
                                            <label for="tipo_codigo" class="form-label">Patrón / PIN</label>
                                            <select name="tipo_codigo" id="tipo_codigo" class="form-select form-select-sm" onchange="togglePatronInputEdit(this)">
                                                <option value="">—</option>
                                                <option value="pin" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='pin'?'selected':'' }}>PIN</option>
                                                <option value="patron" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'selected':'' }}>Patrón</option>
                                            </select>
                                        </div>
                                        <div class="col-12" id="pinWrapperEdit" style="display:{{ old('tipo_codigo',$reparacion->tipo_codigo)=='pin'?'block':'none' }};">
                                            <input type="text" class="form-control form-control-sm patron-valor" name="codigo_equipo" id="codigo_equipo"
                                                   value="{{ old('codigo_equipo',$reparacion->codigo_equipo) }}" placeholder="PIN numérico">
                                        </div>
                                    </div>
                            </div>
                        {{-- Columna 3: diagnóstico + resumen --}}
                        <div class="col-xxl-3 col-lg-3 col-md-12">
                            <div class="ot-sec mb-2">
                                <div class="ot-sec-title"><i class="fas fa-stethoscope"></i>Diagnóstico y solución</div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label for="falla_reportada" class="form-label">Falla Reportada <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="falla_reportada" id="falla_reportada" rows="2" required>{{ old('falla_reportada',$reparacion->falla_reportada) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="diagnostico" class="form-label">Diagnóstico del Técnico</label>
                                            <textarea class="form-control" name="diagnostico" id="diagnostico" rows="2"
                                                      placeholder="Describe el diagnóstico técnico del equipo...">{{ old('diagnostico',$reparacion->diagnostico) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="solucion" class="form-label">Solución Aplicada</label>
                                            <textarea class="form-control" name="solucion" id="solucion" rows="2"
                                                      placeholder="Describe qué se hizo para solucionar la falla...">{{ old('solucion',$reparacion->solucion) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="notas" class="form-label">Notas adicionales</label>
                                            <textarea class="form-control" name="notas" id="notas" rows="2">{{ old('notas',$reparacion->notas) }}</textarea>
                                        </div>
                                    </div>
                            </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label for="presupuesto" class="form-label">Presupuesto ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="presupuesto" id="presupuesto"
                                                   value="{{ old('presupuesto',$reparacion->presupuesto) }}" min="0" step="0.01">
                                        </div>
                                        <div class="col-6">
                                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                                            <select name="metodo_pago" id="metodo_pago" class="form-select">
                                                <option value="mercadopago" {{ old('metodo_pago', $reparacion->metodo_pago ?? '')=='mercadopago'?'selected':'' }}>Mercado Pago</option>
                                                <option value="efectivo" {{ old('metodo_pago', $reparacion->metodo_pago ?? 'efectivo')=='efectivo'?'selected':'' }}>Efectivo</option>
                                                <option value="transferencia" {{ old('metodo_pago', $reparacion->metodo_pago ?? '')=='transferencia'?'selected':'' }}>Transferencia</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="costo_final" class="form-label">Costo Final ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="costo_final" id="costo_final"
                                                   value="{{ old('costo_final',$reparacion->costo_final) }}" min="0" step="0.01">
                                        </div>
                                        <div class="col-6">
                                            <label for="costo_repuesto" class="form-label">Costo Repuesto(s) ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="costo_repuesto" id="costo_repuesto"
                                                   value="{{ old('costo_repuesto',$reparacion->costo_repuesto) }}" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="col-6">
                                            <label for="comisionPorcentajeEdit" class="form-label">% Comisión Técnico</label>
                                            <input type="number" class="form-control" name="comision_porcentaje" id="comisionPorcentajeEdit" value="@php echo old('comision_porcentaje', $reparacion->comision_porcentaje ?? $reparacion->tecnico->comision_porcentaje ?? ''); @endphp" min="0" max="100" step="0.01" placeholder="%">
                                        </div>
                                        <div class="col-6">
                                            <label for="abono" class="form-label">Abono ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control" name="abono" id="abono"
                                                   value="{{ old('abono',$reparacion->abono) }}" min="0" step="0.01">
                                        </div>
                                        <div class="col-6">
                                            <label for="total" class="form-label">Total ({{ $empresa->simbolo_moneda ?? '$' }})</label>
                                            <input type="number" class="form-control total-auto" name="total" id="total"
                                                   value="{{ old('total',$reparacion->total) }}" min="0" step="0.01" readonly
                                                   style="background:#f3f4f6; font-weight:700;">
                                        </div>
                                        <div class="col-6">
                                            <label for="garantia" class="form-label">Garantía</label>
                                            <select name="garantia" id="garantia" class="form-select">
                                                <option value="0" {{ !$reparacion->garantia?'selected':'' }}>No</option>
                                                <option value="1" {{ $reparacion->garantia?'selected':'' }}>Sí</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="dias_garantia" class="form-label">Días Garantía</label>
                                            <input type="number" class="form-control" name="dias_garantia" id="dias_garantia"
                                                   value="{{ old('dias_garantia',$reparacion->dias_garantia) }}" min="0">
                                        </div>
                                        <div class="col-12">
                                            <div class="p-2 rounded-3" style="background:#fff7ed; border:1px solid #f59e0b;" id="comisionPreviewBox">
                                                <div style="font-weight:700; color:#9a3412; font-size:12px;">
                                                    <i class="fas fa-coins me-1"></i>Comisión: <span id="comisionPreviewMonto">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span>
                                                    <span class="text-muted fw-normal">(<span id="comisionPreviewPct">0%</span> de <span id="comisionPreviewBase">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span>)</span>
                                                </div>
                                                <span id="comisionPreviewCobrado" class="d-none">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span>
                                                <span id="comisionPreviewRepuesto" class="d-none">- {{ $empresa->simbolo_moneda ?? '$' }} 0.00</span>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        {{-- Columna 3: entrega + resumen --}}
                        <div class="col-xxl-4 col-lg-4 col-md-12">
                            <div class="ot-sec mb-2">
                                <div class="ot-sec-title"><i class="fas fa-truck"></i>Entrega y WhatsApp</div>
                                    @php
                                        use App\Helpers\WhatsAppHelper;
                                        $cliente = $reparacion->cliente;
                                        $telefonoCliente = WhatsAppHelper::limpiarNumero($cliente->telefono ?? $cliente->celular);
                                        $urlRecibido = WhatsAppHelper::generarUrl(
                                            $telefonoCliente,
                                            WhatsAppHelper::mensajeRecibido($reparacion, $empresa->nombre_tienda ?? 'CRM Celulares')
                                        );
                                        $urlListo = WhatsAppHelper::generarUrl(
                                            $telefonoCliente,
                                            WhatsAppHelper::mensajeListo($reparacion, $empresa->nombre_tienda ?? 'CRM Celulares')
                                        );
                                    @endphp
                                    @if($urlRecibido)
                                    <div class="mb-3">
                                        <div class="form-label fw-600" style="font-size:13px; color:#0f172a;">
                                            <i class="fab fa-whatsapp me-1" style="color:#25D366;"></i>Notificar al Cliente por WhatsApp
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ $urlRecibido }}" target="_blank"
                                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                                <i class="fab fa-whatsapp me-1"></i>Notificar Recibido
                                            </a>
                                            <a href="{{ $urlListo }}" target="_blank"
                                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                                <i class="fab fa-whatsapp me-1"></i>Notificar Listo/Entregado
                                            </a>
                                        </div>
                                        <div style="font-size:11px; color:#9ca3af; margin-top:4px;">
                                            <i class="fas fa-phone me-1"></i>{{ $reparacion->cliente->telefono ?? '—' }}
                                        </div>
                                    </div>
                                    @endif

                                    {{-- ✍️ FIRMA DE ENTREGA (solo si se marca como entregado) --}}
                                    <div class="mb-3" id="firmaEntregaSection" style="display:none;">
                                        <div class="form-label fw-600" style="font-size:13px; color:#0f172a;">
                                            <i class="fas fa-pen me-1" style="color:#0891b2;"></i>Firma de Entrega
                                        </div>
                                        <p class="text-muted" style="font-size:12px;">Haz que el cliente firme al entregar el equipo.</p>
                                        @if($reparacion->firma_entrega)
                                            <div class="text-center mb-2" id="firmaEntregaExistente">
                                                <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma de entrega"
                                                     style="max-width:100%; max-height:100px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                                                <p class="text-muted mt-1" style="font-size:11px;">Firma de entrega ya registrada</p>
                                            </div>
                                        @endif
                                        <div class="signature-pad-wrapper" id="sigPadEntregaWrapperEdit" style="border:2px dashed #d1d5db; border-radius:12px; background:#fff; position:relative; cursor:crosshair;">
                                            <canvas id="sigCanvasEntregaEdit" style="display:block; width:100%; height:160px; border-radius:12px; touch-action:none;"></canvas>
                                            <div class="placeholder" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:#9ca3af; font-size:14px; pointer-events:none;">Firma aquí</div>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirmaEdit()">
                                                <i class="fas fa-eraser me-1"></i>Limpiar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="guardarFirmaEdit()">
                                                <i class="fas fa-check me-1"></i>Guardar Firma
                                            </button>
                                        </div>
                                        <input type="hidden" name="firma_entrega_data" id="firmaEntregaData" value="">
                                    </div>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-lg-4 col-md-12">
                            <div class="ot-sec h-100">
                                <div class="ot-sec-title"><i class="fas fa-clipboard-check"></i>Resumen y guardar</div>
                                <div class="p-2 rounded-3 mb-3" style="background:#f8fafc;font-size:13px;line-height:2;">
                                    <div><b>{{ $reparacion->numero_orden }}</b> · {{ $reparacion->dispositivo }}</div>
                                    <div>{{ $reparacion->cliente->nombre_completo ?? '' }}</div>
                                    <div>Total: <b>{{ ($empresa->simbolo_moneda ?? '$') . ' ' . number_format($reparacion->total ?? 0, 0) }}</b> · {{ ucfirst($reparacion->estado) }}</div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn ui-btn-brand btn-lg">
                                        <i class="fas fa-save me-2"></i>Guardar Cambios
                                    </button>
                                    <a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary">Cancelar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" integrity="sha384-dPowQo9uxJU703klzvnG+vzLHQDNmO/zREXw6BhCMupB54CE70wj6SWOGVPySK3s" crossorigin="anonymous"></script>
<script>
// ── FIRMA DE ENTREGA ──
let sigPadEntregaEdit = null;

function initFirmaEntregaEdit() {
    const canvas = document.getElementById('sigCanvasEntregaEdit');
    if (!canvas) return;
    const wrapper = document.getElementById('sigPadEntregaWrapperEdit');
    const ratio = Math.max(window.devicePixelRatio || 1, 1);

    // Ancho seguro: si el wrapper está oculto o sin layout, usar un mínimo
    let width = wrapper && wrapper.clientWidth > 100 ? wrapper.clientWidth : 0;
    if (width < 100) {
        width = 300;
    }

    canvas.width = width * ratio;
    canvas.height = 160 * ratio;

    const ctx = canvas.getContext('2d');
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

    sigPadEntregaEdit = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
    sigPadEntregaEdit.addEventListener('beginStroke', () => {
        const placeholder = wrapper.querySelector('.placeholder');
        if (placeholder) placeholder.style.display = 'none';
    });
}

function mostrarFirmaEntrega() {
    const section = document.getElementById('firmaEntregaSection');
    if (section) section.style.display = 'block';
    setTimeout(() => initFirmaEntregaEdit(), 100);
}

function ocultarFirmaEntrega() {
    const section = document.getElementById('firmaEntregaSection');
    if (section) section.style.display = 'none';
}

function limpiarFirmaEdit() {
    if (sigPadEntregaEdit) {
        sigPadEntregaEdit.clear();
        const wrapper = document.getElementById('sigPadEntregaWrapperEdit');
        const placeholder = wrapper?.querySelector('.placeholder');
        if (placeholder) placeholder.style.display = '';
    }
    document.getElementById('firmaEntregaData').value = '';
}

function guardarFirmaEdit() {
    if (!sigPadEntregaEdit) return;
    if (sigPadEntregaEdit.isEmpty()) {
        alert('Por favor, dibuja la firma antes de guardar.');
        return;
    }
    const dataUrl = sigPadEntregaEdit.toDataURL('image/png');
    document.getElementById('firmaEntregaData').value = dataUrl;
    const wrapper = document.getElementById('sigPadEntregaWrapperEdit');
    const existing = document.getElementById('firmaEntregaExistente');
    if (existing) {
        existing.querySelector('img').src = dataUrl;
        existing.querySelector('p').textContent = '✓ Nueva firma registrada';
    }
    wrapper.style.display = 'none';
    alert('Firma capturada correctamente. Guarda los cambios para confirmar.');
}

// Mostrar/ocultar firma según el estado seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.querySelector('select[name="estado"]');
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            if (this.value === 'entregado') {
                mostrarFirmaEntrega();
            } else {
                ocultarFirmaEntrega();
            }
        });
        if (estadoSelect.value === 'entregado') {
            mostrarFirmaEntrega();
        }
    }
});

// ── Toggle Marca (precargada / otra) ──
function toggleMarcaInputEdit(select) {
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
let patronPuntosEdit = [];

function togglePatronInputEdit(select) {
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
        limpiarPatronEdit();
    } else {
        dibujo.style.display = 'none';
        valorInput.style.display = 'block';
        valorInput.placeholder = 'Valor del PIN o patrón';
        limpiarPatronEdit();
    }
}

function togglePuntoEdit(el) {
    const container = el.closest('.col-md-6');
    const pos = parseInt(el.dataset.pos);
    const idx = patronPuntosEdit.indexOf(pos);

    if (idx === -1) {
        patronPuntosEdit.push(pos);
        el.style.background = 'linear-gradient(135deg, #0891b2, #3b82f6)';
        el.style.color = '#fff';
        el.style.borderColor = 'transparent';
        el.style.transform = 'scale(1.1)';
        el.textContent = patronPuntosEdit.length;
    } else {
        patronPuntosEdit.splice(idx, 1);
        el.style.background = '#f8f5ff';
        el.style.color = '#0891b2';
        el.style.borderColor = '#0891b2';
        el.style.transform = 'scale(1)';
        patronPuntosEdit.forEach((p, i) => {
            const punto = container.querySelector(`.patron-punto[data-pos="${p}"]`);
            if (punto) punto.textContent = i + 1;
        });
    }

    actualizarPatronTextoEdit(container);
}

function limpiarPatronEdit() {
    patronPuntosEdit = [];
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

function actualizarPatronTextoEdit(container) {
    const texto = container.querySelector('.patron-texto');
    const hidden = container.querySelector('.patron-secuencia');
    if (patronPuntosEdit.length === 0) {
        texto.textContent = 'Ningún punto seleccionado';
        hidden.value = '';
    } else {
        const secuencia = patronPuntosEdit.join('-');
        texto.textContent = `Secuencia: ${secuencia}`;
        hidden.value = secuencia;
    }
}

// ── Auto-calcular Total = Costo Final / Presupuesto - Abono ──
document.addEventListener('input', function(e) {
    if (['presupuesto', 'abono', 'costo_final'].includes(e.target.name)) {
        const presupuesto = parseFloat(document.querySelector('input[name="presupuesto"]').value) || 0;
        const costoFinal = parseFloat(document.querySelector('input[name="costo_final"]')?.value) || 0;
        const abono = parseFloat(document.querySelector('input[name="abono"]').value) || 0;
        const totalInput = document.querySelector('input[name="total"]');
        const base = costoFinal > 0 ? costoFinal : presupuesto;
        if (totalInput) totalInput.value = Math.max(0, base - abono).toFixed(2);
    }
});

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
                <div class="alert alert-success py-2 px-3 mb-0" style="font-size:13px;">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>Cupón válido:</strong> ${valorTexto} de descuento
                    ${cupon.descripcion ? '<br><small class="text-muted">' + cupon.descripcion + '</small>' : ''}
                </div>
            `;
            document.getElementById('validarCuponBtn').classList.add('btn-success');
            document.getElementById('validarCuponBtn').classList.remove('btn-outline-primary');
        } else {
            infoDiv.innerHTML = `
                <div class="alert alert-danger py-2 px-3 mb-0" style="font-size:13px;">
                    <i class="fas fa-times-circle me-1"></i>${data.message || 'Cupón no válido.'}
                </div>
            `;
            document.getElementById('validarCuponBtn').classList.remove('btn-success');
            document.getElementById('validarCuponBtn').classList.add('btn-outline-primary');
        }
    })
    .catch(err => {
        infoDiv.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size:13px;"><i class="fas fa-times-circle me-1"></i>Error de conexión al validar el cupón.</div>';
        console.error(err);
    });
}
</script>
@endpush