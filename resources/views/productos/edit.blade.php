@extends('layouts.app')
@section('title', 'Editar Producto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Editar Producto</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Modifica los datos del producto</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">Información General</h6>
                            <div class="row g-3">

                                {{-- TIPO DE PRODUCTO --}}
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Producto <span class="text-danger">*</span></label>
                                    <select name="tipo" id="tipoProducto" class="form-select @error('tipo') is-invalid @enderror" required>
                                        <option value="celular" {{ old('tipo', $producto->tipo ?? 'celular')=='celular'?'selected':'' }}>📱 Celular</option>
                                        <option value="accesorio" {{ old('tipo', $producto->tipo)=='accesorio'?'selected':'' }}>🔌 Accesorio</option>
                                        <option value="otro" {{ old('tipo', $producto->tipo)=='otro'?'selected':'' }}>📦 Otro</option>
                                    </select>
                                    @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Código SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('codigo') is-invalid @enderror"
                                           name="codigo" value="{{ old('codigo', $producto->codigo) }}">
                                    @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Código de Barras</label>
                                    <input type="text" class="form-control @error('codigo_barras') is-invalid @enderror"
                                           name="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}">
                                    @error('codigo_barras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Garantía (días)</label>
                                    <input type="number" class="form-control" name="garantia_dias"
                                           value="{{ old('garantia_dias', $producto->garantia_dias ?? 0) }}" min="0">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                           name="nombre" value="{{ old('nombre', $producto->nombre) }}">
                                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- CATEGORÍA --}}
                                <div class="col-md-6">
                                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="categoria_id" id="categoriaSelect" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach($categorias as $cat)
                                                <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id)==$cat->id?'selected':'' }}>{{ $cat->nombre }}</option>
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
                                                <option value="{{ $m->id }}" {{ old('marca_id', $producto->marca_id)==$m->id?'selected':'' }}>{{ $m->nombre }}</option>
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
                                    <select name="proveedor_id" class="form-select">
                                        <option value="">— Sin proveedor —</option>
                                        @foreach(\App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get() as $prov)
                                            <option value="{{ $prov->id }}" {{ old('proveedor_id', $producto->proveedor_id)==$prov->id?'selected':'' }}>
                                                {{ $prov->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- CAMPOS PARA CELULAR --}}
                                <div id="camposCelular">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Modelo</label>
                                            <input type="text" class="form-control" name="modelo"
                                                   value="{{ old('modelo', $producto->modelo) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Color</label>
                                            <input type="text" class="form-control" name="color"
                                                   value="{{ old('color', $producto->color) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Condición <span class="text-danger" id="condReq">*</span></label>
                                            <select name="condicion" id="condicionSelect" class="form-select">
                                                @foreach(['nuevo','reacondicionado','usado'] as $c)
                                                    <option value="{{ $c }}" {{ old('condicion', $producto->condicion ?? 'nuevo')==$c?'selected':'' }}>{{ ucfirst($c) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Almacenamiento</label>
                                            <select name="almacenamiento" class="form-select">
                                                <option value="">— Sin especificar —</option>
                                                @foreach(['32GB','64GB','128GB','256GB','512GB','1TB'] as $alm)
                                                    <option value="{{ $alm }}" {{ old('almacenamiento', $producto->almacenamiento)==$alm?'selected':'' }}>{{ $alm }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">RAM</label>
                                            <select name="ram" class="form-select">
                                                <option value="">— Sin especificar —</option>
                                                @foreach(['2GB','3GB','4GB','6GB','8GB','12GB','16GB'] as $ram)
                                                    <option value="{{ $ram }}" {{ old('ram', $producto->ram)==$ram?'selected':'' }}>{{ $ram }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">IMEI</label>
                                            <input type="text" class="form-control" name="imei"
                                                   value="{{ old('imei', $producto->imei) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="3">{{ old('descripcion', $producto->descripcion) }}</textarea>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">Precios y Stock</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Precio Compra (S/) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('precio_compra') is-invalid @enderror"
                                           name="precio_compra" value="{{ old('precio_compra', $producto->precio_compra) }}"
                                           min="0" step="0.01" oninput="calcularMargen()">
                                    @error('precio_compra')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Precio Venta (S/) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('precio_venta') is-invalid @enderror"
                                           name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}"
                                           min="0" step="0.01" oninput="calcularMargen()">
                                    @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Margen de Ganancia</label>
                                    <div class="form-control d-flex align-items-center" style="background:#f9fafb;">
                                        <span id="margenValor" style="font-weight:600; color:#10b981;">
                                            {{ number_format($producto->margen, 1) }}%
                                        </span>
                                        <span id="margenMonto" class="ms-2 text-muted" style="font-size:12px;">
                                            (S/ {{ number_format($producto->precio_venta - $producto->precio_compra, 2) }})
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Actual <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                           name="stock" value="{{ old('stock', $producto->stock) }}" min="0">
                                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Mínimo <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="stock_minimo"
                                           value="{{ old('stock_minimo', $producto->stock_minimo) }}" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select name="activo" class="form-select">
                                        <option value="1" {{ $producto->activo?'selected':'' }}>Activo</option>
                                        <option value="0" {{ !$producto->activo?'selected':'' }}>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">Imagen del Producto</h6>
                            @if($producto->imagen)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/'.$producto->imagen) }}" id="previewImg"
                                         style="width:100%; border-radius:12px; max-height:220px; object-fit:cover;">
                                    <p class="text-muted mt-1" style="font-size:12px;">Imagen actual</p>
                                </div>
                            @else
                                <img id="previewImg" src="" style="display:none; width:100%; border-radius:12px; max-height:220px; object-fit:cover; margin-bottom:8px;">
                            @endif

                            <div onclick="document.getElementById('imagenInput').click()"
                                 style="border:2px dashed #d1d5db; border-radius:12px; padding:20px;
                                        text-align:center; cursor:pointer; background:#fafafa;">
                                <i class="fas fa-camera text-muted mb-2 d-block"></i>
                                <span style="font-size:12px; color:#6b7280;">
                                    {{ $producto->imagen ? 'Cambiar imagen' : 'Subir imagen' }}
                                </span>
                            </div>
                            <input type="file" id="imagenInput" name="imagen" accept="image/*"
                                   style="display:none;" onchange="previewImage(this)">

                            <div class="mt-4 p-3 rounded-3" style="background:#f8f5ff;">
                                <h6 style="font-size:13px; font-weight:600; margin-bottom:12px;">Resumen de Precio</h6>
                                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                                    <span class="text-muted">Precio compra</span>
                                    <span id="resCompra">S/ {{ number_format($producto->precio_compra, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                                    <span class="text-muted">Precio venta</span>
                                    <span id="resVenta" style="font-weight:600;">S/ {{ number_format($producto->precio_venta, 2) }}</span>
                                </div>
                                <hr style="margin:8px 0;">
                                <div class="d-flex justify-content-between" style="font-size:13px;">
                                    <span class="text-muted">Ganancia unitaria</span>
                                    <span id="resGanancia" style="color:#10b981; font-weight:600;">
                                        S/ {{ number_format($producto->precio_venta - $producto->precio_compra, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Actualizar Producto
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
    const condSelect = document.getElementById('condicionSelect');
    if (esCelular) {
        condSelect.setAttribute('required', '');
        document.getElementById('condReq').style.display = 'inline';
    } else {
        condSelect.removeAttribute('required');
        document.getElementById('condReq').style.display = 'none';
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const evento = new Event('change');
    document.getElementById('tipoProducto')?.dispatchEvent(evento);
});

// ── IMAGEN ─────────────────────────────────────────────────────
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('previewImg');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
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