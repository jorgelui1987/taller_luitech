@extends('layouts.app')
@section('title', 'Configuración')

@section('breadcrumb')
    <li class="breadcrumb-item active">Configuración</li>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- ── Header ── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e1b4b;">Configuración del Sistema</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Gestión de la empresa, usuarios y parámetros generales</p>
    </div>
</div>

<div class="row g-4">

    <!-- ══════════ COLUMNA IZQUIERDA ══════════ -->
    <div class="col-lg-4">

        <!-- ── Datos de la Empresa ── -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-store me-2" style="color:#a855f7;"></i>Datos de la Empresa</h6>

                <form action="{{ route('configuracion.updateEmpresa') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Logo actual -->
                    <div class="text-center mb-3">
                        @if($empresa && $empresa->logo)
                            <img src="{{ asset($empresa->logo) }}" alt="Logo"
                                 style="max-width:120px; max-height:80px; border-radius:8px; object-fit:contain;">
                        @else
                            <div style="width:80px;height:80px;background:linear-gradient(135deg,#a855f7,#ec4899);
                                        border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                <i class="fas fa-store" style="color:#fff;font-size:32px;"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Subir logo -->
                    <div class="mb-3">
                        <label class="form-label">Logo de la empresa</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <div class="form-text">PNG, JPG, WEBP. Máx 2MB. Se actualizará al guardar.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre de tienda <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_tienda" class="form-control"
                               value="{{ old('nombre_tienda', $empresa->nombre_tienda ?? 'CRM Celulares') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RUC</label>
                        <input type="text" name="ruc" class="form-control"
                               value="{{ old('ruc', $empresa->ruc ?? '') }}" maxlength="20">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               value="{{ old('direccion', $empresa->direccion ?? '') }}" maxlength="500">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                   value="{{ old('telefono', $empresa->telefono ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control"
                                   value="{{ old('whatsapp', $empresa->whatsapp ?? '') }}"
                                   placeholder="+CODIGO_PAIS NÚMERO (ej: +51 999 999 999)">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $empresa->email ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">IGV (%)</label>
                            <input type="number" name="igv" class="form-control" step="0.01" min="0" max="100"
                                   value="{{ old('igv', $empresa->igv ?? 18) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Comisión Técnicos (%)</label>
                            <div class="input-group">
                                <input type="number" name="comision_global_tecnicos" class="form-control" step="0.01" min="0" max="100"
                                       value="{{ old('comision_global_tecnicos', $empresa->comision_global_tecnicos ?? 0) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">% global de comisión por reparación entregada</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Moneda</label>
                            <select name="moneda" class="form-select" required>
                                <option value="">Seleccionar...</option>
                {{-- Las monedas vienen del controlador como $monedas --}}
                                @foreach($monedas as $cod => $datos)
                                    <option value="{{ $cod }}" {{ (old('moneda', $empresa->moneda ?? 'PEN') == $cod) ? 'selected' : '' }}
                                            data-simbolo="{{ $datos['simbolo'] }}">
                                        {{ $cod }} - {{ $datos['pais'] }} ({{ $datos['simbolo'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Símbolo</label>
                            <input type="text" name="simbolo_moneda" id="simbolo_moneda" class="form-control"
                                   value="{{ old('simbolo_moneda', $empresa->simbolo_moneda ?? 'S/.') }}" required readonly
                                   style="background:#f9fafb;cursor:default;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Términos de garantía</label>
                        <textarea name="terminos_garantia" class="form-control" rows="2" maxlength="1000"
                                  placeholder="Ej: 30 días de garantía...">{{ old('terminos_garantia', $empresa->terminos_garantia ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Guardar Datos
                    </button>
                </form>
            </div>
        </div>

        <!-- ═══════ Plan y Suscripción ═══════ -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-star-fill me-2" style="color:#f59e0b;"></i>Mi Plan</h6>
                @php
                    $tenant = auth()->user()->tenant;
                @endphp
                @if($tenant)
                    <div class="text-center mb-3">
                        <span class="badge bg-{{ $badgeColor[$tenant->plan] ?? 'secondary' }}" style="font-size:1rem; padding:8px 20px;">
                            {{ $planes[$tenant->plan] ?? ucfirst($tenant->plan) }}
                        </span>
                    </div>
                    <div style="font-size:13px;">
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                            <span class="text-muted">Estado</span>
                            <span class="badge bg-{{ $tenant->estado === 'activo' ? 'success' : 'danger' }}">{{ $tenant->estado }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                            <span class="text-muted">Usuarios</span>
                            <span class="fw-500">{{ $usuarios->count() }} / {{ $tenant->max_usuarios }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                            <span class="text-muted">Productos</span>
                            <span class="fw-500">{{ $tenant->productos_count ?? \App\Models\Producto::where('tenant_id', $tenant->id)->count() }} / {{ $tenant->max_productos }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                            <span class="text-muted">Vencimiento</span>
                            <span class="fw-500">{{ $tenant->fecha_expiracion ? $tenant->fecha_expiracion->format('d/m/Y') : 'Sin fecha' }}</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('planes') }}#precios" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-arrow-up-circle me-1"></i>Mejorar Plan
                        </a>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No disponible</p>
                @endif
            </div>
        </div>

        <!-- Info del sistema -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Información del Sistema</h6>
                <div style="font-size:13px;" class="mb-3">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Versión</span>
                        <span class="fw-500">1.0.0</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Framework</span>
                        <span class="fw-500">Laravel 10</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Base de datos</span>
                        <span class="fw-500">MySQL</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f3f4f6;">
                        <span class="text-muted">Zona horaria</span>
                        <span class="fw-500">America/Lima</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Moneda</span>
                        <span class="fw-500">{{ $empresa->simbolo_moneda ?? 'S/.' }} ({{ $empresa->moneda ?? 'PEN' }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Estadísticas del Sistema</h6>
                {{-- Las estadísticas vienen del controlador como $stats --}}
                @foreach($stats as $s)
                <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #f3f4f6; font-size:13px;">
                    <div style="width:32px;height:32px;background:{{ $s['color'] }}18;border-radius:8px;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-{{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:13px;"></i>
                    </div>
                    <span class="text-muted flex-grow-1">{{ $s['label'] }}</span>
                    <strong>{{ $s['value'] }}</strong>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Accesos Rápidos</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary btn-sm text-start" style="border-radius:8px;">
                        <i class="fas fa-box me-2 text-muted"></i>Gestionar Inventario
                    </a>
                    <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary btn-sm text-start" style="border-radius:8px;">
                        <i class="fas fa-chart-bar me-2 text-muted"></i>Ver Reportes
                    </a>
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm text-start" style="border-radius:8px;">
                        <i class="fas fa-users me-2 text-muted"></i>Ver Clientes
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm text-start" style="border-radius:8px;">
                        <i class="fas fa-th-large me-2 text-muted"></i>Ir al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ COLUMNA DERECHA: Gestión de usuarios ══════════ -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="fw-bold mb-0">Gestión de Usuarios</h6>
                    <span style="background:#ede9fe;color:#7c3aed;border-radius:20px;padding:3px 12px;font-size:12px;">
                        {{ $usuarios->count() }} usuarios
                    </span>
                </div>

                <!-- Leyenda de roles -->
                <div class="d-flex gap-3 mb-4" style="font-size:12px;">
                    <span><span style="display:inline-block;width:10px;height:10px;background:#a855f7;border-radius:50%;margin-right:4px;"></span>Admin</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:#06b6d4;border-radius:50%;margin-right:4px;"></span>Vendedor</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:#f59e0b;border-radius:50%;margin-right:4px;"></span>Técnico</span>
                </div>

                <div class="row g-3">
                    @foreach($usuarios as $usuario)
                    @php
                        $rolColor = ['admin'=>'#a855f7','vendedor'=>'#06b6d4','tecnico'=>'#f59e0b'][$usuario->rol] ?? '#9ca3af';
                        $rolBg    = ['admin'=>'#ede9fe','vendedor'=>'#e0f2fe','tecnico'=>'#fef3c7'][$usuario->rol] ?? '#f3f4f6';
                        $rolTxt   = ['admin'=>'#7c3aed','vendedor'=>'#0369a1','tecnico'=>'#92400e'][$usuario->rol] ?? '#374151';
                        $inicial  = strtoupper(substr($usuario->name, 0, 1));
                    @endphp
                    <div class="col-12">
                        <div class="p-3 rounded-3 d-flex align-items-center gap-3"
                             style="background:#f9fafb;border:1px solid #f3f4f6;transition:all .2s;"
                             onmouseenter="this.style.borderColor='#e9d5ff'"
                             onmouseleave="this.style.borderColor='#f3f4f6'">

                            <!-- Avatar -->
                            <div style="width:44px;height:44px;background:{{ $rolColor }};border-radius:12px;
                                        display:flex;align-items:center;justify-content:center;
                                        color:#fff;font-weight:700;font-size:16px;flex-shrink:0;">
                                {{ $inicial }}
                            </div>

                            <!-- Info -->
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-600" style="font-size:14px;font-weight:600;">{{ $usuario->name }}</span>
                                    <span style="background:{{ $rolBg }};color:{{ $rolTxt }};
                                                 border-radius:20px;padding:2px 8px;font-size:11px;">
                                        {{ ucfirst($usuario->rol) }}
                                    </span>
                                    @if($usuario->id === auth()->id())
                                        <span style="background:#d1fae5;color:#065f46;border-radius:20px;padding:2px 8px;font-size:11px;">
                                            Tú
                                        </span>
                                    @endif
                                    @if(!$usuario->activo)
                                        <span style="background:#fee2e2;color:#991b1b;border-radius:20px;padding:2px 8px;font-size:11px;">
                                            Inactivo
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                                    <i class="fas fa-envelope me-1"></i>{{ $usuario->email }}
                                    @if($usuario->telefono)
                                        &nbsp;·&nbsp;<i class="fas fa-phone me-1"></i>{{ $usuario->telefono }}
                                    @endif
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <!-- Editar -->
                                <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;padding:4px 10px;"
                                        title="Editar usuario"
                                        onclick="abrirModalEditar({{ $usuario->id }}, '{{ addslashes($usuario->name) }}', '{{ $usuario->email }}', '{{ $usuario->rol }}', '{{ $usuario->telefono }}', '{{ $usuario->comision_porcentaje }}')">
                                    <i class="fas fa-edit" style="font-size:12px;"></i>
                                </button>

                                @if($usuario->id !== auth()->id())
                                <!-- Toggle activo -->
                                <form action="{{ route('configuracion.toggleUsuario', $usuario) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $usuario->activo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            style="border-radius:8px;padding:4px 10px;"
                                            title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }} usuario">
                                        <i class="fas fa-{{ $usuario->activo ? 'ban' : 'check' }}" style="font-size:12px;"></i>
                                    </button>
                                </form>

                                <!-- Eliminar -->
                                <form action="{{ route('configuracion.destroyUsuario', $usuario) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ addslashes($usuario->name) }}? Esta acción no se puede deshacer.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            style="border-radius:8px;padding:4px 10px;"
                                            title="Eliminar usuario">
                                        <i class="fas fa-trash" style="font-size:12px;"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Información de seguridad -->
        <div class="card mt-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-shield-alt me-2" style="color:#a855f7;"></i>Políticas de Acceso</h6>
                <div class="row g-3" style="font-size:13px;">
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background:#f8f5ff;border-left:3px solid #a855f7;">
                            <div class="fw-600 mb-1" style="font-weight:600;color:#7c3aed;">Admin</div>
                            <div class="text-muted" style="font-size:12px;">Acceso completo a todos los módulos, configuración y reportes.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background:#e0f7fa;border-left:3px solid #06b6d4;">
                            <div class="fw-600 mb-1" style="font-weight:600;color:#0369a1;">Vendedor</div>
                            <div class="text-muted" style="font-size:12px;">Clientes, inventario, ventas y consulta de reportes.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background:#fffbeb;border-left:3px solid #f59e0b;">
                            <div class="fw-600 mb-1" style="font-weight:600;color:#92400e;">Técnico</div>
                            <div class="text-muted" style="font-size:12px;">Gestión de reparaciones y consulta de inventario.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ MODAL: Nuevo Usuario ══════════ -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-user-plus me-2" style="color:#a855f7;"></i>Nuevo Usuario
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('configuracion.storeUsuario') }}" method="POST">
                @csrf
                <div class="modal-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger" style="border-radius:10px;font-size:13px;">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                   placeholder="Ej: María García" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                   placeholder="usuario@tienda.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="nuevaPassword" class="form-control" required minlength="8">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('nuevaPassword','eyeNueva')">
                                    <i class="fas fa-eye" id="eyeNueva" style="font-size:13px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="confirmPassword" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('confirmPassword','eyeConfirm')">
                                    <i class="fas fa-eye" id="eyeConfirm" style="font-size:13px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select" required>
                                <option value="">Seleccionar rol...</option>
                                <option value="admin"    {{ old('rol')=='admin'?'selected':'' }}>👑 Administrador</option>
                                <option value="vendedor" {{ old('rol')=='vendedor'?'selected':'' }}>🛒 Vendedor</option>
                                <option value="tecnico"  {{ old('rol')=='tecnico'?'selected':'' }}>🔧 Técnico</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}"
                                   placeholder="+51 999 999 999">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════ MODAL: Editar Usuario ══════════ -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-user-edit me-2" style="color:#a855f7;"></i>Editar Usuario
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarUsuario" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editNombre" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="editPassword" class="form-control" minlength="8"
                                       placeholder="Dejar vacío para no cambiar">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('editPassword','eyeEdit')">
                                    <i class="fas fa-eye" id="eyeEdit" style="font-size:13px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="Repetir nueva contraseña">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select name="rol" id="editRol" class="form-select" required>
                                <option value="admin">👑 Administrador</option>
                                <option value="vendedor">🛒 Vendedor</option>
                                <option value="tecnico">🔧 Técnico</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="editTelefono" class="form-control">
                        </div>
                        <div class="col-12" id="comisionSection" style="display:none;">
                            <label class="form-label">Comisión (%) <span class="text-muted">(dejar vacío para usar el % global)</span></label>
                            <div class="input-group">
                                <input type="number" name="comision_porcentaje" id="editComision" class="form-control" step="0.01" min="0" max="100" placeholder="Ej: 30">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Se calcula automáticamente al entregar una reparación: Costo × % / 100</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Botón flotante para crear usuario -->
<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        style="bottom:28px;right:28px;width:56px;height:56px;z-index:1050;"
        data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
    <i class="fas fa-user-plus"></i>
</button>

@endsection

@push('scripts')
<script>
// Auto-actualizar símbolo cuando cambia la moneda
document.addEventListener('DOMContentLoaded', function() {
    var monedaSelect = document.querySelector('select[name="moneda"]');
    var simboloInput = document.getElementById('simbolo_moneda');
    
    if (monedaSelect && simboloInput) {
        monedaSelect.addEventListener('change', function() {
            var selected = monedaSelect.options[monedaSelect.selectedIndex];
            var simbolo = selected.getAttribute('data-simbolo');
            if (simbolo) {
                simboloInput.value = simbolo;
            }
        });
    }
});

function abrirModalEditar(id, nombre, email, rol, telefono, comision) {
    document.getElementById('editNombre').value   = nombre;
    document.getElementById('editEmail').value    = email;
    document.getElementById('editRol').value      = rol;
    document.getElementById('editTelefono').value = telefono || '';
    document.getElementById('editComision').value = comision || '';
    document.getElementById('comisionSection').style.display = rol === 'tecnico' ? 'block' : 'none';
    document.getElementById('formEditarUsuario').action = '/configuracion/usuarios/' + id;
    var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
    modal.show();
}

function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Auto-open modal si hay errores de validación (al crear)
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalNuevoUsuario')).show();
    });
@endif
</script>
@endpush