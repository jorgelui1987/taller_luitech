<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Tienda' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8f9fa; }
        .hero { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #3730a3 100%); color: #fff; padding: 60px 0; }
        .hero .logo { max-height: 80px; max-width: 200px; border-radius: 12px; }
        .card { border-radius: 16px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .btn-whatsapp { background: #25D366; color: #fff; border-radius: 50px; padding: 10px 24px; font-weight: 600; }
        .btn-whatsapp:hover { background: #1da851; color: #fff; }
        .star { color: #f59e0b; font-size: 20px; }
        .star-empty { color: #d1d5db; font-size: 20px; }
        .resena-card { border-left: 4px solid #a855f7; }
        .cupon-card { border: 2px dashed #10b981; background: #f0fdf4; }
        .social-btn { width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; margin: 0 4px; }
        .social-btn.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
        .social-btn.facebook { background: #1877f2; }
        .social-btn.tiktok { background: #000; }
        .social-btn.whatsapp { background: #25D366; }
    </style>
</head>
<body>
    <div class="hero text-center">
        <div class="container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo" class="logo mb-3">
            @else
                <div style="font-size:60px;">📱</div>
            @endif
            <h1 class="display-5 fw-bold">{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Mi Tienda' }}</h1>
            @if($config->descripcion_corta)
                <p class="lead mb-3">{{ $config->descripcion_corta }}</p>
            @endif
            @if($promedio)
                <div class="mb-3">
                    @for($i=1; $i<=5; $i++)
                        <span class="{{ $i <= round($promedio) ? 'star' : 'star-empty' }}">★</span>
                    @endfor
                    <span class="ms-2">{{ number_format($promedio, 1) }}/5</span>
                </div>
            @endif
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @if($config->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $config->whatsapp) }}" target="_blank" class="btn btn-whatsapp">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a>
                @endif
                @if($config->instagram)
                    <a href="{{ $config->instagram }}" target="_blank" class="social-btn instagram"><i class="bi bi-instagram"></i></a>
                @endif
                @if($config->facebook)
                    <a href="{{ $config->facebook }}" target="_blank" class="social-btn facebook"><i class="bi bi-facebook"></i></a>
                @endif
                @if($config->tiktok)
                    <a href="{{ $config->tiktok }}" target="_blank" class="social-btn tiktok"><i class="bi bi-tiktok"></i></a>
                @endif
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card p-4 mb-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h4>
                    <div class="row g-3">
                        @if($config->direccion)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt text-danger fs-4"></i>
                                <div>
                                    <div class="text-muted small">Dirección</div>
                                    <strong>{{ $config->direccion }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($config->telefono)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-telephone text-success fs-4"></i>
                                <div>
                                    <div class="text-muted small">Teléfono</div>
                                    <strong>{{ $config->telefono }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($config->horario_atencion)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock text-warning fs-4"></i>
                                <div>
                                    <div class="text-muted small">Horario</div>
                                    <strong>{{ $config->horario_atencion }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($config->email)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-envelope text-primary fs-4"></i>
                                <div>
                                    <div class="text-muted small">Email</div>
                                    <strong>{{ $config->email }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($resenas->isNotEmpty())
                <div class="card p-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-star me-2 text-warning"></i>Reseñas de Clientes</h4>
                    @foreach($resenas as $resena)
                    <div class="resena-card p-3 mb-3" style="background:#faf5ff;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $resena->nombre_publico ?? 'Cliente' }}</strong>
                                <div class="mt-1">
                                    @for($i=1; $i<=5; $i++)
                                        <span class="{{ $i <= $resena->calificacion ? 'star' : 'star-empty' }}" style="font-size:16px;">★</span>
                                    @endfor
                                </div>
                            </div>
                            <small class="text-muted">{{ $resena->created_at->format('d/m/Y') }}</small>
                        </div>
                        @if($resena->comentario)
                            <p class="mb-0 mt-2" style="font-size:14px;">{{ $resena->comentario }}</p>
                        @endif
                        @if($resena->respuesta_admin)
                            <div class="mt-2 p-2" style="background:#e0f2fe;border-radius:8px;font-size:13px;">
                                <strong class="text-primary">Respuesta:</strong> {{ $resena->respuesta_admin }}
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                @if($cupones->isNotEmpty())
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-ticket-perforated me-2 text-success"></i>Cupones Activos</h5>
                    @foreach($cupones as $cupon)
                    <div class="cupon-card p-3 mb-3 text-center">
                        <div class="text-muted small">Código</div>
                        <div class="fw-bold fs-5" style="letter-spacing:2px;">{{ $cupon->codigo }}</div>
                        <div class="text-success fw-bold mt-1">{{ $cupon->valor }}% de descuento</div>
                        <div class="text-muted small mt-1">{{ $cupon->descripcion }}</div>
                        @if($cupon->fecha_expiracion)
                            <div class="text-danger small mt-1">Vence: {{ $cupon->fecha_expiracion->format('d/m/Y') }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-truck me-2 text-primary"></i>Seguimiento de Reparación</h5>
                    <p class="text-muted" style="font-size:14px;">Ingresa tu número de orden para ver el estado de tu equipo.</p>
                    <form action="{{ route('reparaciones.public-status') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="numero_orden" class="form-control" 
                               placeholder="N° de orden (ej: R-0001)" 
                               value="{{ request('numero_orden') }}" required>
                        <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                    </form>
                </div>

                <div class="card p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-patch-check me-2 text-primary"></i>¿Reparaste con nosotros?</h5>
                    <p class="text-muted" style="font-size:14px;">Cuéntanos tu experiencia y ayuda a otros clientes.</p>
                    <a href="{{ route('public.resena.form', $tenant->slug_publico) }}" class="btn btn-primary w-100">
                        <i class="bi bi-star me-2"></i>Dejar una reseña
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} {{ $config->nombre_tienda ?? $tenant->empresa ?? 'Mi Tienda' }}</p>
            @if($config->direccion)
                <p class="small text-muted mt-1 mb-0">{{ $config->direccion }}</p>
            @endif
        </div>
    </footer>
</body>
</html>