@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li>
    <li class="breadcrumb-item active">Nuevo Producto</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Registrar Nuevo Producto</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Completa los datos del producto</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        {{-- Columna izquierda --}}
                        <div class="col-lg-8">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">Información General</h6>
                            <div class="row g-3">

                                {{-- TIPO DE PRODUCTO --}}
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Producto <span class="text-danger">*</span></label>
                                    <select name="tipo" id="tipoProducto" class="form-select @error('tipo') is-invalid @enderror" required>
                                        <option value="celular" {{ old('tipo', 'celular')=='celular'?'selected':'' }}>📱 Celular</option>
                                        <option value="accesorio" {{ old('tipo')=='accesorio'?'selected':'' }}>🔌 Accesorio</option>
                                        <option value="otro" {{ old('tipo')=='otro'?'selected':'' }}>📦 Otro</option>
                                    </select>
                                    @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Código SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('codigo') is-invalid @enderror"
                                           name="codigo" value="{{ old('codigo') }}" placeholder="SAM-A54-128">
                                    @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Código de Barras</label>
                                    <input type="text" class="form-control @error('codigo_barras') is-invalid @enderror"
                                           name="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="1234567890123">
                                    @error('codigo_barras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Garantía (días)</label>
                                    <input type="number" class="form-control" name="garantia_dias"
                                           value="{{ old('garantia_dias', 0) }}" min="0" placeholder="30, 90, 365...">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                           name="nombre" value="{{ old('nombre') }}" placeholder="Samsung Galaxy A54 128GB">
                                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- CATEGORÍA --}}
                                <div class="col-md-6">
                                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="categoria_id" id="categoriaSelect" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach($categorias as $cat)
                                                <option value="{{ $cat->id }}" {{ old('categoria_id')==$cat->id?'selected':'' }}>
                                                    {{ $cat->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="abrirModalCategoria()" title="Nueva categoría">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- MARCA --}}
                                <div class="col-md-6">
                                    <label class="form-label">Marca <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="marca_id" id="marcaSelect" class="form-select @error('marca_id') is-invalid @enderror" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach($marcas as $m)
                                                <option value="{{ $m->id }}" {{ old('marca_id')==$m->id?'selected':'' }}>
                                                    {{ $m->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="abrirModalMarca()" title="Nueva marca">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    @error('marca_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Proveedor</label>
                                    <select name="proveedor_id" class="form-select @error('proveedor_id') is-invalid @enderror">
                                        <option value="">— Sin proveedor —</option>
                                        @foreach(\App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get() as $prov)
                                            <option value="{{ $prov->id }}" {{ old('proveedor_id')==$prov->id?'selected':'' }}>
                                                {{ $prov->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('proveedor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- CAMPOS PARA CELULAR (se ocultan si no es celular) --}}
                                <div id="camposCelular">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Modelo</label>
                                            <input type="text" class="form-control" name="modelo"
                                                   value="{{ old('modelo') }}" placeholder="A54, iPhone 15...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Color</label>
                                            <input type="text" class="form-control" name="color"
                                                   value="{{ old('color') }}" placeholder="Negro, Blanco...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Condición <span class="text-danger" id="condReq">*</span></label>
                                            <select name="condicion" id="condicionSelect" class="form-select">
                                                <option value="nuevo" {{ old('condicion','nuevo')=='nuevo'?'selected':'' }}>Nuevo</option>
                                                <option value="reacondicionado" {{ old('condicion')=='reacondicionado'?'selected':'' }}>Reacondicionado</option>
                                                <option value="usado" {{ old('condicion')=='usado'?'selected':'' }}>Usado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Almacenamiento</label>
                                            <select name="almacenamiento" class="form-select">
                                                <option value="">— Sin especificar —</option>
                                                @foreach(['32GB','64GB','128GB','256GB','512GB','1TB'] as $alm)
                                                    <option value="{{ $alm }}" {{ old('almacenamiento')==$alm?'selected':'' }}>{{ $alm }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">RAM</label>
                                            <select name="ram" class="form-select">
                                                <option value="">— Sin especificar —</option>
                                                @foreach(['2GB','3GB','4GB','6GB','8GB','12GB','16GB'] as $ram)
                                                    <option value="{{ $ram }}" {{ old('ram')==$ram?'selected':'' }}>{{ $ram }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">IMEI</label>
                                            <input type="text" class="form-control" name="imei"
                                                   value="{{ old('imei') }}" placeholder="123456789012345">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="3"
                                              placeholder="Características, detalles del producto...">{{ old('descripcion') }}</textarea>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">Precios y Stock</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Precio de Compra (S/) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('precio_compra') is-invalid @enderror"
                                           name="precio_compra" value="{{ old('precio_compra',0) }}"
                                           min="0" step="0.01" oninput="calcularMargen()">
                                    @error('precio_compra')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Precio de Venta (S/) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('precio_venta') is-invalid @enderror"
                                           name="precio_venta" value="{{ old('precio_venta',0) }}"
                                           min="0" step="0.01" oninput="calcularMargen()">
                                    @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Margen de Ganancia</label>
                                    <div class="form-control d-flex align-items-center" style="background:#f9fafb;">
                                        <span id="margenValor" style="font-weight:600; color:#10b981;">0.0%</span>
                                        <span id="margenMonto" class="ms-2 text-muted" style="font-size:12px;"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Inicial <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                           name="stock" value="{{ old('stock',0) }}" min="0">
                                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Mínimo <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle text-muted fa-xs" title="Alerta cuando baje de este número"></i>
                                    </label>
                                    <input type="number" class="form-control" name="stock_minimo"
                                           value="{{ old('stock_minimo',5) }}" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Columna derecha - imagen --}}
                        <div class="col-lg-4">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">Imagen del Producto</h6>
                            <div id="dropZone" onclick="document.getElementById('imagenInput').click()"
                                 style="border:2px dashed #d1d5db; border-radius:16px; padding:32px 20px;
                                        text-align:center; cursor:pointer; background:#fafafa; transition:.2s;"
                                 ondragover="event.preventDefault(); this.style.borderColor='#a855f7';"
                                 ondragleave="this.style.borderColor='#d1d5db';"
                                 ondrop="handleDrop(event)">
                                <div id="dropContent">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color:#d1d5db;"></i>
                                    <p class="mb-1" style="font-size:13px; color:#6b7280;">Arrastra la imagen aquí</p>
                                    <p class="mb-0" style="font-size:12px; color:#9ca3af;">o haz clic para seleccionar</p>
                                    <p class="mb-0 mt-2" style="font-size:11px; color:#d1d5db;">JPG, PNG, WebP · Máx 2MB</p>
                                </div>
                                <img id="previewImg" src="" style="display:none; width:100%; border-radius:10px; max-height:200px; object-fit:cover;">
                            </div>
                            <input type="file" id="imagenInput" name="imagen" accept="image/*"
                                   style="display:none;" onchange="previewImage(this)">

                            @error('imagen')
                                <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                            @enderror

                            <div class="mt-4 p-3 rounded-3" style="background:#f8f5ff;">
                                <h6 style="font-size:13px; font-weight:600; margin-bottom:12px;">Resumen de Precio</h6>
                                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                                    <span class="text-muted">Precio compra</span>
                                    <span id="resCompra">S/ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                                    <span class="text-muted">Precio venta</span>
                                    <span id="resVenta" style="font-weight:600;">S/ 0.00</span>
                                </div>
                                <hr style="margin:8px 0;">
                                <div class="d-flex justify-content-between" style="font-size:13px;">
                                    <span class="text-muted">Ganancia unitaria</span>
                                    <span id="resGanancia" style="color:#10b981; font-weight:600;">S/ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL NUEVA MARCA --}}
<div class="modal fade" id="modalMarca" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:linear-gradient(135deg,#a855f7,#7c3aed); color:#fff; border-radius:16px 16px 0 0;">
                <h6 class="modal-title fw-bold"><i class="fas fa-tag me-2"></i>Nueva Marca</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre de la Marca</label>
                    <input type="text" id="nuevaMarcaInput" class="form-control" placeholder="Ej: Spigen, Anker, Ugreen..."
                           onkeypress="if(event.key==='Enter'){event.preventDefault();guardarMarca();}">
                    <div id="marcaError" class="text-danger mt-1" style="font-size:12px; display:none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarMarca()">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL NUEVA CATEGORÍA --}}
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:linear-gradient(135deg,#06b6d4,#0284c7); color:#fff; border-radius:16px 16px 0 0;">
                <h6 class="modal-title fw-bold"><i class="fas fa-folder me-2"></i>Nueva Categoría</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre de la Categoría</label>
                    <input type="text" id="nuevaCategoriaInput" class="form-control" placeholder="Ej: Fundas, Cargadores, Audífonos..."
                           onkeypress="if(event.key==='Enter'){event.preventDefault();guardarCategoria();}">
                    <div id="categoriaError" class="text-danger mt-1" style="font-size:12px; display:none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCategoria()">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── TIPO DE PRODUCTO: mostrar/ocultar campos de celular ──────────
document.getElementById('tipoProducto')?.addEventListener('change', function() {
    const esCelular = this.value === 'celular';
    document.getElementById('camposCelular').style.display = esCelular ? 'block' : 'none';
    // Hacer required/no required la condición
    const condSelect = document.getElementById('condicionSelect');
    if (esCelular) {
        condSelect.setAttribute('required', '');
        document.getElementById('condReq').style.display = 'inline';
    } else {
        condSelect.removeAttribute('required');
        document.getElementById('condReq').style.display = 'none';
    }
});
// Disparar al cargar para aplicar estado inicial
document.addEventListener('DOMContentLoaded', function() {
    const evento = new Event('change');
    document.getElementById('tipoProducto')?.dispatchEvent(evento);
});

// ── IMAGEN ─────────────────────────────────────────────────────
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewImg').style.display = 'block';
            document.getElementById('dropContent').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('imagenInput').files = dt.files;
        previewImage(document.getElementById('imagenInput'));
    }
}

// ── MARGEN ─────────────────────────────────────────────────────
function calcularMargen() {
    const compra = parseFloat(document.querySelector('[name=precio_compra]').value) || 0;
    const venta  = parseFloat(document.querySelector('[name=precio_venta]').value) || 0;
    const margen = compra > 0 ? ((venta - compra) / compra * 100) : 0;
    const ganancia = venta - compra;

    document.getElementById('margenValor').textContent = margen.toFixed(1) + '%';
    document.getElementById('margenValor').style.color = margen >= 0 ? '#10b981' : '#dc2626';
    document.getElementById('margenMonto').textContent = '(S/ ' + ganancia.toFixed(2) + ')';
    document.getElementById('resCompra').textContent  = 'S/ ' + compra.toFixed(2);
    document.getElementById('resVenta').textContent   = 'S/ ' + venta.toFixed(2);
    document.getElementById('resGanancia').textContent = 'S/ ' + ganancia.toFixed(2);
    document.getElementById('resGanancia').style.color = ganancia >= 0 ? '#10b981' : '#dc2626';
}

// ── MODAL MARCA ────────────────────────────────────────────────
function abrirModalMarca() {
    document.getElementById('nuevaMarcaInput').value = '';
    document.getElementById('marcaError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalMarca')).show();
    setTimeout(() => document.getElementById('nuevaMarcaInput').focus(), 300);
}

function guardarMarca() {
    const input = document.getElementById('nuevaMarcaInput');
    const nombre = input.value.trim();
    const errorDiv = document.getElementById('marcaError');

    if (!nombre) {
        errorDiv.textContent = 'Ingresa el nombre de la marca';
        errorDiv.style.display = 'block';
        return;
    }

    // Usar FormData para mejor compatibilidad con Laravel CSRF
    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route('productos.marca-ajax') }}', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor' })))
    .then(data => {
        if (data.success) {
            const select = document.getElementById('marcaSelect');
            const opt = document.createElement('option');
            opt.value = data.id;
            opt.textContent = data.nombre;
            opt.selected = true;
            select.appendChild(opt);
            bootstrap.Modal.getInstance(document.getElementById('modalMarca')).hide();
        } else {
            errorDiv.textContent = data.message || 'Error al guardar';
            errorDiv.style.display = 'block';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        errorDiv.textContent = 'Error de conexión con el servidor. Intenta de nuevo.';
        errorDiv.style.display = 'block';
    });
}

// ── MODAL CATEGORÍA ────────────────────────────────────────────
function abrirModalCategoria() {
    document.getElementById('nuevaCategoriaInput').value = '';
    document.getElementById('categoriaError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalCategoria')).show();
    setTimeout(() => document.getElementById('nuevaCategoriaInput').focus(), 300);
}

function guardarCategoria() {
    const input = document.getElementById('nuevaCategoriaInput');
    const nombre = input.value.trim();
    const errorDiv = document.getElementById('categoriaError');

    if (!nombre) {
        errorDiv.textContent = 'Ingresa el nombre de la categoría';
        errorDiv.style.display = 'block';
        return;
    }

    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route('productos.categoria-ajax') }}', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor' })))
    .then(data => {
        if (data.success) {
            const select = document.getElementById('categoriaSelect');
            const opt = document.createElement('option');
            opt.value = data.id;
            opt.textContent = data.nombre;
            opt.selected = true;
            select.appendChild(opt);
            bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
        } else {
            errorDiv.textContent = data.message || 'Error al guardar';
            errorDiv.style.display = 'block';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        errorDiv.textContent = 'Error de conexión con el servidor. Intenta de nuevo.';
        errorDiv.style.display = 'block';
    });
}
</script>
@endpush