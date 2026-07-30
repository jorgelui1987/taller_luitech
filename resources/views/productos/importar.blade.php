@extends('layouts.app')
@section('title', 'Importar Productos')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li>
    <li class="breadcrumb-item active">Importar Productos</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Importar Productos desde CSV</h5>
                <p class="text-muted mb-4" style="font-size:13px;">
                    Sube un archivo CSV para crear o actualizar productos en lote
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-4 rounded-3 text-center" style="background:#f8f5ff; border:2px dashed #d1d5db;">
                            <i class="fas fa-file-csv fa-4x mb-3" style="color:#a855f7;"></i>
                            <h6 class="fw-bold">Subir archivo CSV</h6>
                            <p style="font-size:12px; color:#6b7280;">
                                El archivo debe tener cabeceras en la primera fila
                            </p>

                            <form action="{{ route('productos.importar.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" name="archivo" class="form-control" accept=".csv,.txt" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-upload me-2"></i>Importar Productos
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-4 rounded-3" style="background:#f9fafb;">
                            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-muted"></i>Instrucciones</h6>
                            <ol style="font-size:13px; color:#374151; padding-left:18px;">
                                <li class="mb-2">Descarga la <strong>plantilla CSV</strong> de ejemplo</li>
                                <li class="mb-2">Completa los datos respetando los nombres de columna</li>
                                <li class="mb-2">Guarda el archivo en formato <strong>CSV UTF-8</strong></li>
                                <li class="mb-2">Súbelo usando el formulario de la izquierda</li>
                            </ol>

                            <div class="mt-3">
                                <strong style="font-size:13px;">Columnas obligatorias:</strong>
                                <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                    <code>codigo</code>, <code>nombre</code>, <code>precio_compra</code>, <code>precio_venta</code>
                                </div>
                            </div>

                            <div class="mt-3">
                                <strong style="font-size:13px;">Columnas opcionales:</strong>
                                <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                    <code>categoria_id</code>, <code>marca_id</code>, <code>modelo</code>, <code>color</code>,
                                    <code>almacenamiento</code>, <code>ram</code>, <code>condicion</code>, <code>stock</code>,
                                    <code>stock_minimo</code>, <code>imei</code>, <code>descripcion</code>
                                </div>
                            </div>

                            <div class="mt-3 p-3 rounded-3" style="background:#fffbeb; font-size:12px; color:#92400e;">
                                <i class="fas fa-lightbulb me-1"></i>
                                Si el <code>código</code> ya existe, el producto <strong>se actualizará</strong>.
                                Si no existe, se <strong>creará uno nuevo</strong>.
                            </div>

                            <a href="{{ route('productos.plantilla') }}" class="btn btn-outline-primary w-100 mt-3">
                                <i class="fas fa-download me-2"></i>Descargar Plantilla CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection