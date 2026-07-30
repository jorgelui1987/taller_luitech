@extends('layouts.app')
@section('title', 'Actualizar Reparación')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#a855f7;">Reparaciones</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reparaciones.show', $reparacion) }}" style="color:#a855f7;">{{ $reparacion->numero_orden }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Actualizar Orden: {{ $reparacion->numero_orden }}</h5>
                        <p class="text-muted mb-0" style="font-size:13px;">
                            {{ $reparacion->dispositivo }} — {{ $reparacion->cliente->nombre_completo ?? '' }}
                        </p>
                    </div>
                    <a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>Ver Detalle
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reparaciones.update', $reparacion) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row g-4">
                        {{-- Estado y prioridad --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-tasks me-2" style="color:#a855f7;"></i>Estado de la Orden
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">Estado Actual <span class="text-danger">*</span></label>
                                    <select name="estado" class="form-select" required>
                                        @php $estados = ['recibido'=>'📥 Recibido','en_diagnostico'=>'🔍 En Diagnóstico','esperando_repuesto'=>'⏳ Esperando Repuesto','en_reparacion'=>'🔧 En Reparación','listo'=>'✅ Listo para Entregar','entregado'=>'📦 Entregado','no_reparable'=>'❌ No Reparable']; @endphp
                                        @foreach($estados as $val => $lbl)
                                            <option value="{{ $val }}" {{ old('estado',$reparacion->estado)==$val?'selected':'' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prioridad</label>
                                    <select name="prioridad" class="form-select">
                                        <option value="baja" {{ old('prioridad',$reparacion->prioridad)=='baja'?'selected':'' }}>🟢 Baja</option>
                                        <option value="media" {{ old('prioridad',$reparacion->prioridad)=='media'?'selected':'' }}>🟡 Media</option>
                                        <option value="alta" {{ old('prioridad',$reparacion->prioridad)=='alta'?'selected':'' }}>🟠 Alta</option>
                                        <option value="urgente" {{ old('prioridad',$reparacion->prioridad)=='urgente'?'selected':'' }}>🔴 Urgente</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Técnico Asignado</label>
                                    <select name="tecnico_id" class="form-select">
                                        @foreach($tecnicos as $t)
                                            <option value="{{ $t->id }}" {{ old('tecnico_id',$reparacion->tecnico_id)==$t->id?'selected':'' }}>
                                                {{ $t->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Equipo --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-mobile-alt me-2" style="color:#a855f7;"></i>Equipo
                            </h6>
                            <div class="row g-3">
                                {{-- 1. Tipo de Dispositivo --}}
                                <div class="col-md-4">
                                    <label class="form-label">📱 Tipo de Dispositivo <span class="text-danger">*</span></label>
                                    <select name="tipo_dispositivo" class="form-select" required>
                                        <option value="">— Seleccionar tipo —</option>
                                        <option value="celular" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='celular'?'selected':'' }}>📱 Celular / Smartphone</option>
                                        <option value="tablet" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='tablet'?'selected':'' }}>📟 Tablet / iPad</option>
                                        <option value="portatil" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='portatil'?'selected':'' }}>💻 Portátil / Laptop</option>
                                        <option value="otros" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='otros'?'selected':'' }}>🔧 Otros</option>
                                    </select>
                                </div>
                                {{-- 2. Marca --}}
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
                                        $marcaActual = old('marca', $reparacion->marca ?? '');
                                        $esOtra = $marcaActual && !in_array($marcaActual, $marcasPrecargadas);
                                    @endphp
                                    <select name="marca_select" class="form-select marca-select" onchange="toggleMarcaInputEdit(this)">
                                        <option value="">— Seleccionar o escribir —</option>
                                        @foreach($marcasPrecargadas as $m)
                                            <option value="{{ $m }}" {{ $marcaActual==$m?'selected':'' }}>{{ $m }}</option>
                                        @endforeach
                                        <option value="__otra__" {{ $esOtra?'selected':'' }}>✏️ Otra (escribir manualmente)</option>
                                    </select>
                                    <input type="text" class="form-control marca-input mt-1" name="marca"
                                           value="{{ old('marca',$reparacion->marca) }}"
                                           placeholder="Escribir marca manualmente..."
                                           style="{{ $esOtra ? 'display:block;' : 'display:none;' }}">
                                </div>
                                {{-- 3. Modelo --}}
                                <div class="col-md-4">
                                    <label class="form-label">📦 Modelo</label>
                                    <input type="text" class="form-control" name="modelo"
                                           value="{{ old('modelo',$reparacion->modelo) }}">
                                </div>
                                {{-- 4. IMEI --}}
                                <div class="col-md-4">
                                    <label class="form-label">🔢 IMEI / Serie</label>
                                    <input type="text" class="form-control" name="imei"
                                           value="{{ old('imei',$reparacion->imei) }}">
                                </div>
                                {{-- 5. Tipo (Patrón/PIN) --}}
                                <div class="col-md-4">
                                    <div class="d-flex gap-2 align-items-start">
                                        <div style="flex:0 0 100px;">
                                            <label class="form-label">🔐 Tipo</label>
                                            <select name="tipo_codigo" class="form-select" onchange="togglePatronInputEdit(this)">
                                                <option value="">—</option>
                                                <option value="pin" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='pin'?'selected':'' }}>🔢 PIN</option>
                                                <option value="patron" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'selected':'' }}>🔓 Patrón</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;">
                                            <label class="form-label">Valor</label>
                                            <input type="text" class="form-control patron-valor" name="codigo_equipo"
                                                   value="{{ old('codigo_equipo',$reparacion->codigo_equipo) }}"
                                                   placeholder="PIN numérico"
                                                   style="display:{{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'none':'block'}};">
                                        </div>
                                    </div>
                                    {{-- Dibujo de patrón 3x3 --}}
                                    <div class="patron-dibujo mt-2" style="display:{{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'block':'none'}};">
                                        <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">Dibuja el patrón (toca los puntos en orden):</div>
                                        <div style="display:flex; gap:2px; flex-wrap:wrap; max-width:140px; margin:0 auto;">
                                            @for($i=1;$i<=9;$i++)
                                            <div class="patron-punto" data-pos="{{ $i }}"
                                                 style="width:40px; height:40px; border-radius:50%; border:2px solid #a855f7;
                                                        display:flex; align-items:center; justify-content:center;
                                                        font-size:13px; color:#a855f7; cursor:pointer; background:#f8f5ff;
                                                        transition:all .2s; user-select:none;"
                                                 onclick="togglePuntoEdit(this)">
                                                {{ $i }}
                                            </div>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="patron_secuencia" class="patron-secuencia" value="{{ old('patron_secuencia') }}">
                                        <div style="display:flex; gap:4px; margin-top:4px;">
                                            <span style="font-size:11px; color:#9ca3af;" class="patron-texto">Ningún punto seleccionado</span>
                                            <button type="button" onclick="limpiarPatronEdit()" style="font-size:11px; border:none; background:transparent; color:#dc2626; cursor:pointer; padding:0;">✕ Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- 6. Color --}}
                                <div class="col-md-4">
                                    <label class="form-label">🎨 Color</label>
                                    <input type="text" class="form-control" name="color"
                                           value="{{ old('color',$reparacion->color) }}">
                                </div>
                                {{-- 7. Fecha Estimada --}}
                                <div class="col-md-4">
                                    <label class="form-label">📅 Fecha Estimada de Entrega</label>
                                    <input type="date" class="form-control" name="fecha_estimada"
                                           value="{{ old('fecha_estimada', optional($reparacion->fecha_estimada)->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Diagnóstico --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-stethoscope me-2" style="color:#a855f7;"></i>Diagnóstico Técnico
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Falla Reportada <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="falla_reportada" rows="3" required>{{ old('falla_reportada',$reparacion->falla_reportada) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Diagnóstico del Técnico</label>
                                    <textarea class="form-control" name="diagnostico" rows="4"
                                              placeholder="Describe el diagnóstico técnico del equipo...">{{ old('diagnostico',$reparacion->diagnostico) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Solución Aplicada</label>
                                    <textarea class="form-control" name="solucion" rows="4"
                                              placeholder="Describe qué se hizo para solucionar la falla...">{{ old('solucion',$reparacion->solucion) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Costos y garantía --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-dollar-sign me-2" style="color:#a855f7;"></i>Costos y Garantía
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Presupuesto (S/)</label>
                                    <input type="number" class="form-control" name="presupuesto"
                                           value="{{ old('presupuesto',$reparacion->presupuesto) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Costo Final (S/)</label>
                                    <input type="number" class="form-control" name="costo_final"
                                           value="{{ old('costo_final',$reparacion->costo_final) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Abono (S/)</label>
                                    <input type="number" class="form-control" name="abono"
                                           value="{{ old('abono',$reparacion->abono) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Total (S/)</label>
                                    <input type="number" class="form-control total-auto" name="total"
                                           value="{{ old('total',$reparacion->total) }}" min="0" step="0.01" readonly
                                           style="background:#f3f4f6; font-weight:700;">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">¿Incluye Garantía?</label>
                                    <select name="garantia" class="form-select">
                                        <option value="0" {{ !$reparacion->garantia?'selected':'' }}>No</option>
                                        <option value="1" {{ $reparacion->garantia?'selected':'' }}>Sí</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Días de Garantía</label>
                                    <input type="number" class="form-control" name="dias_garantia"
                                           value="{{ old('dias_garantia',$reparacion->dias_garantia) }}" min="0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notas adicionales</label>
                                    <textarea class="form-control" name="notas" rows="2">{{ old('notas',$reparacion->notas) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
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
                        <label class="form-label fw-600" style="font-size:13px; color:#1e1b4b;">
                            <i class="fab fa-whatsapp me-1" style="color:#25D366;"></i>Notificar al Cliente por WhatsApp
                        </label>
                        <div class="d-flex gap-2">
                            <a href="{{ $urlRecibido }}" target="_blank"
                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                <i class="fab fa-whatsapp me-1"></i>📩 Notificar Recibido
                            </a>
                            <a href="{{ $urlListo }}" target="_blank"
                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                <i class="fab fa-whatsapp me-1"></i>📩 Notificar Listo/Entregado
                            </a>
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:4px;">
                            📞 {{ $reparacion->cliente->telefono ?? '—' }}
                        </div>
                    </div>
                    @endif
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
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
    const container = select.closest('.col-md-4');
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
    const container = el.closest('.col-md-4');
    const pos = parseInt(el.dataset.pos);
    const idx = patronPuntosEdit.indexOf(pos);

    if (idx === -1) {
        patronPuntosEdit.push(pos);
        el.style.background = 'linear-gradient(135deg, #a855f7, #ec4899)';
        el.style.color = '#fff';
        el.style.borderColor = 'transparent';
        el.style.transform = 'scale(1.1)';
        el.textContent = patronPuntosEdit.length;
    } else {
        patronPuntosEdit.splice(idx, 1);
        el.style.background = '#f8f5ff';
        el.style.color = '#a855f7';
        el.style.borderColor = '#a855f7';
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
        el.style.color = '#a855f7';
        el.style.borderColor = '#a855f7';
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
