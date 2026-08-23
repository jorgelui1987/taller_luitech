@extends('layouts.app')
@section('title', 'Nueva Garantía')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('garantias.index') }}">Garantías</a></li></ul>
    <ul><li class="breadcrumb-item active">Nueva Garantía</li></ul>
@endsection

@section('content')

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <ul><li>{{ $error }}</li></ul>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e1b4b;"><i class="fas fa-shield-alt me-2" style="color:#10b981;"></i>Registrar Garantía</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Registra un producto que ingresa por garantía (sin reembolso de dinero)</p>
    </div>
    <a href="{{ route('garantias.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<form id="formGarantia" method="POST" action="{{ route('garantias.store') }}">
    @csrf

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-shopping-cart me-2" style="color:#10b981;"></i>Venta Original (Opcional)</h6>

                    <div class="mb-3">
                        <label for="ventaSelect" class="form-label">Seleccionar venta (opcional)</label>
                        <select name="venta_id" id="ventaSelect" class="form-select @error('venta_id') is-invalid @enderror">
                            <option value="">Sin venta asociada...</option>
                            @foreach($ventas as $v)
                                <option value="{{ $v->id }}" {{ old('venta_id', $ventaSeleccionada->id ?? '') == $v->id ? 'selected' : '' }}>
                                    {{ $v->numero_venta }} - {{ $v->fecha_venta->format('d/m/Y') }} - {{ $v->cliente->nombre_completo ?? 'Venta general' }}
                                </option>
                            @endforeach
                        </select>
                        @error('venta_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="ventaInfo" class="mb-3 p-3 rounded-3" style="background:#ecfdf5;display:none;">
                        <div style="font-size:12px;color:#059669;font-weight:600;">Datos de la venta</div>
                        <div id="ventaInfoContenido" style="font-size:12px;margin-top:6px;"></div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3"><i class="fas fa-tag me-2" style="color:#10b981;"></i>Información de la Garantía</h6>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                        <select name="motivo" id="motivo" class="form-select @error('motivo') is-invalid @enderror" required>
                            <option value="">Seleccionar motivo...</option>
                            <option value="garantia" {{ old('motivo')=='garantia'?'selected':'' }}>🛡️ Garantía</option>
                            <option value="defecto" {{ old('motivo')=='defecto'?'selected':'' }}>⚠️ Producto defectuoso</option>
                            <option value="otro" {{ old('motivo')=='otro'?'selected':'' }}>📦 Otro</option>
                        </select>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea name="observacion" id="observacion" class="form-control" rows="3" maxlength="1000"
                                  placeholder="Ej: El cliente trae el equipo con falla en la pantalla...">{{ old('observacion') }}</textarea>
                    </div>

                    <div class="alert alert-info py-2" style="font-size:12px;">
                        <i class="fas fa-info-circle me-1"></i>
                        La garantía <strong>no genera reembolso</strong>. Solo registra el ingreso del producto y lo reintegra al stock.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-box me-2" style="color:#10b981;"></i>Productos en Garantía</h6>
                        <span class="badge" style="background:#ecfdf5;color:#059669;font-size:11px;">Agregue productos manualmente o seleccione una venta</span>
                    </div>

                    <!-- Agregar producto manualmente -->
                    <div class="row g-2 mb-3 p-3 rounded-3" style="background:#f8fafc;border:1px dashed #cbd5e1;">
                        <div class="col-md-5">
                            <label class="form-label" style="font-size:12px;">Producto</label>
                            <select id="productoManualSelect" class="form-select form-select-sm">
                                <option value="">Seleccionar producto...</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}">{{ $p->nombre }} (Stock: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:12px;">Cantidad</label>
                            <input type="number" id="productoManualCantidad" class="form-control form-control-sm" min="1" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Condición</label>
                            <select id="productoManualCondicion" class="form-select form-select-sm">
                                <option value="nuevo">🆕 Nuevo</option>
                                <option value="usado">👌 Usado</option>
                                <option value="dañado">💥 Dañado</option>
                                <option value="incompleto">📦 Incompleto</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="btnAgregarProducto">
                                <i class="fas fa-plus me-1"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <div id="sinVentaMsg" class="text-center py-5" style="display:none;">
                        <i class="fas fa-shopping-cart" style="font-size:48px;color:#e5e7eb;"></i>
                        <p class="text-muted mt-3 mb-0">Selecciona una venta para cargar sus productos</p>
                    </div>

                    <div id="productosContainer">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="productosTable">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"><label for="checkAll" class="visually-hidden">Seleccionar todos</label><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                        <th>Producto</th>
                                        <th class="text-center" style="width:90px;">Vendido</th>
                                        <th class="text-center" style="width:110px;">Cantidad</th>
                                        <th style="width:130px;">Condición</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="productosTbody"></tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg" id="btnSubmit" disabled>
                            <i class="fas fa-check-circle me-2"></i>Registrar Garantía
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ventaSelect = document.getElementById('ventaSelect');
    const ventaInfo = document.getElementById('ventaInfo');
    const ventaInfoContenido = document.getElementById('ventaInfoContenido');
    const sinVentaMsg = document.getElementById('sinVentaMsg');
    const productosContainer = document.getElementById('productosContainer');
    const productosTbody = document.getElementById('productosTbody');
    const checkAll = document.getElementById('checkAll');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnAgregarProducto = document.getElementById('btnAgregarProducto');
    const productoManualSelect = document.getElementById('productoManualSelect');
    const productoManualCantidad = document.getElementById('productoManualCantidad');
    const productoManualCondicion = document.getElementById('productoManualCondicion');

    let itemsManuales = [];

    const ventaPreseleccionada = '{{ old('venta_id', $ventaSeleccionada->id ?? '') }}';
    if (ventaPreseleccionada) {
        cargarVenta(ventaPreseleccionada);
    }

    ventaSelect.addEventListener('change', function() {
        if (this.value) {
            cargarVenta(this.value);
        } else {
            ventaInfo.style.display = 'none';
            sinVentaMsg.style.display = 'none';
            productosTbody.innerHTML = '';
            checkAll.checked = false;
            checkAll.disabled = true;
            actualizarBtn();
        }
    });

    function cargarVenta(ventaId) {
        fetch('{{ route('garantias.api.venta', ['ventaId' => '__ID__']) }}'.replace('__ID__', ventaId))
            .then(res => res.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }

                ventaInfo.style.display = 'block';
                ventaInfoContenido.innerHTML =
                    '<div class="d-flex justify-content-between"><span class="text-muted">N° Venta</span><strong>' + data.venta.numero_venta + '</strong></div>' +
                    '<div class="d-flex justify-content-between mt-1"><span class="text-muted">Fecha</span><span>' + data.venta.fecha_venta + '</span></div>' +
                    '<div class="d-flex justify-content-between mt-1"><span class="text-muted">Cliente</span><span>' + data.venta.cliente + '</span></div>' +
                    '<div class="d-flex justify-content-between mt-1"><span class="text-muted">Total venta</span><strong>{{ $empresa->simbolo_moneda ?? '$' }} ' + data.venta.total.toFixed(2) + '</strong></div>';

                sinVentaMsg.style.display = 'none';
                productosContainer.style.display = 'block';
                productosTbody.innerHTML = '';

                data.detalles.forEach(function(det) {
                    const tr = document.createElement('tr');
                    tr.dataset.detalleVentaId = det.detalle_venta_id;
                    tr.dataset.productoId = det.producto_id;

                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input check-item" data-detalle="${det.detalle_venta_id}"></td>
                        <td>
                            <div style="font-weight:500;">${det.producto_nombre}</div>
                            ${det.imei ? `<div style="font-size:11px;color:#9ca3af;">IMEI: ${det.imei}</div>` : ''}
                        </td>
                        <td class="text-center">${det.cantidad_vendida}</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center cant-item"
                                   min="1" max="${det.cantidad_vendida}" value="${det.cantidad_vendida === 1 ? 1 : ''}"
                                   data-detalle="${det.detalle_venta_id}" disabled>
                        </td>
                        <td>
                            <select class="form-select form-select-sm cond-item" data-detalle="${det.detalle_venta_id}" disabled>
                                <option value="nuevo">🆕 Nuevo</option>
                                <option value="usado">👌 Usado</option>
                                <option value="dañado">💥 Dañado</option>
                                <option value="incompleto">📦 Incompleto</option>
                            </select>
                        </td>
                        <td></td>
                    `;
                    productosTbody.appendChild(tr);
                });

                checkAll.disabled = false;
                checkAll.checked = false;

                document.querySelectorAll('.check-item').forEach(chk => {
                    chk.addEventListener('change', function() {
                        const fila = this.closest('tr');
                        const cantInput = fila.querySelector('.cant-item');
                        const condSelect = fila.querySelector('.cond-item');

                        cantInput.disabled = !this.checked;
                        condSelect.disabled = !this.checked;

                        if (!this.checked) {
                            cantInput.value = '';
                        } else {
                            const max = parseInt(cantInput.max);
                            cantInput.value = max;
                            cantInput.focus();
                        }

                        actualizarCheckAll();
                        actualizarBtn();
                    });
                });

                document.querySelectorAll('.cant-item').forEach(inp => {
                    inp.addEventListener('input', actualizarBtn);
                });

                actualizarBtn();
            })
            .catch(err => {
                console.error('Error al cargar venta:', err);
                alert('Error al cargar los datos de la venta.');
            });
    }

    checkAll.addEventListener('change', function() {
        document.querySelectorAll('.check-item').forEach(chk => {
            chk.checked = this.checked;
            const fila = chk.closest('tr');
            const cantInput = fila.querySelector('.cant-item');
            const condSelect = fila.querySelector('.cond-item');
            cantInput.disabled = !this.checked;
            condSelect.disabled = !this.checked;
            if (this.checked) {
                const max = parseInt(cantInput.max);
                cantInput.value = max;
            } else {
                cantInput.value = '';
            }
        });
        actualizarBtn();
    });

    // Agregar producto manualmente
    btnAgregarProducto.addEventListener('click', function() {
        const productoId = productoManualSelect.value;
        const nombre = productoManualSelect.options[productoManualSelect.selectedIndex]?.dataset.nombre || '';
        const cantidad = parseInt(productoManualCantidad.value) || 1;
        const condicion = productoManualCondicion.value;

        if (!productoId) {
            alert('Selecciona un producto.');
            return;
        }

        // Verificar si ya existe
        const existe = itemsManuales.find(i => i.producto_id === parseInt(productoId));
        if (existe) {
            alert('Ese producto ya fue agregado.');
            return;
        }

        itemsManuales.push({
            producto_id: parseInt(productoId),
            nombre: nombre,
            cantidad: cantidad,
            condicion: condicion
        });

        renderItemsManuales();
        productoManualSelect.value = '';
        productoManualCantidad.value = 1;
        productoManualCondicion.value = 'nuevo';
        actualizarBtn();
    });

    function renderItemsManuales() {
        // Eliminar filas manuales existentes
        document.querySelectorAll('.fila-manual').forEach(el => el.remove());

        itemsManuales.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'fila-manual';
            tr.dataset.productoId = item.producto_id;
            tr.dataset.detalleVentaId = 0;

            tr.innerHTML = `
                <td><input type="checkbox" class="form-check-input check-item" data-detalle="0" checked></td>
                <td>
                    <div style="font-weight:500;">${item.nombre}</div>
                    <div style="font-size:11px;color:#9ca3af;">Agregado manualmente</div>
                </td>
                <td class="text-center">-</td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center cant-item"
                           min="1" value="${item.cantidad}" data-detalle="0">
                </td>
                <td>
                    <select class="form-select form-select-sm cond-item" data-detalle="0">
                        <option value="nuevo" ${item.condicion === 'nuevo' ? 'selected' : ''}>🆕 Nuevo</option>
                        <option value="usado" ${item.condicion === 'usado' ? 'selected' : ''}>👌 Usado</option>
                        <option value="dañado" ${item.condicion === 'dañado' ? 'selected' : ''}>💥 Dañado</option>
                        <option value="incompleto" ${item.condicion === 'incompleto' ? 'selected' : ''}>📦 Incompleto</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-manual" data-idx="${idx}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            productosTbody.appendChild(tr);
        });

        // Eventos para filas manuales
        document.querySelectorAll('.fila-manual .check-item').forEach(chk => {
            chk.addEventListener('change', function() {
                const fila = this.closest('tr');
                const cantInput = fila.querySelector('.cant-item');
                const condSelect = fila.querySelector('.cond-item');
                cantInput.disabled = !this.checked;
                condSelect.disabled = !this.checked;
                actualizarCheckAll();
                actualizarBtn();
            });
        });

        document.querySelectorAll('.fila-manual .cant-item').forEach(inp => {
            inp.addEventListener('input', actualizarBtn);
        });

        document.querySelectorAll('.btn-eliminar-manual').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                itemsManuales.splice(idx, 1);
                renderItemsManuales();
                actualizarBtn();
            });
        });

        actualizarCheckAll();
    }

    function actualizarCheckAll() {
        const checks = document.querySelectorAll('.check-item');
        if (checks.length === 0) return;
        checkAll.checked = Array.from(checks).every(c => c.checked);
    }

    function actualizarBtn() {
        // Habilitar el botón si hay al menos un producto seleccionado
        const itemsSeleccionados = document.querySelectorAll('.check-item:checked');
        const hayItems = itemsSeleccionados.length > 0;
        btnSubmit.disabled = !hayItems;
        btnSubmit.style.opacity = hayItems ? '1' : '.5';
    }

    document.getElementById('formGarantia').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.check-item:checked');
        if (items.length === 0) {
            e.preventDefault();
            alert('Selecciona al menos un producto para la garantía.');
            return;
        }

        document.querySelectorAll('.input-garantia-hidden').forEach(el => el.remove());

        let index = 0;
        items.forEach(chk => {
            const fila = chk.closest('tr');
            const cantidad = parseInt(fila.querySelector('.cant-item').value) || 0;
            if (cantidad <= 0) return;

            const inputs = [
                ['productos[' + index + '][detalle_venta_id]', fila.dataset.detalleVentaId],
                ['productos[' + index + '][producto_id]', fila.dataset.productoId],
                ['productos[' + index + '][cantidad]', cantidad],
                ['productos[' + index + '][condicion]', fila.querySelector('.cond-item').value]
            ];

            inputs.forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                input.className = 'input-garantia-hidden';
                this.appendChild(input);
            });

            index++;
        });

        if (index === 0) {
            e.preventDefault();
            alert('La cantidad debe ser mayor a 0.');
        }
    });
});
</script>
@endpush