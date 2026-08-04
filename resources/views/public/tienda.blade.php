<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Tienda' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --light: #f8fafc;
            --border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }
        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4f46e5 100%);
            color: #fff;
            padding: 80px 0 100px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16,185,129,.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero .container { position: relative; z-index: 1; }
        .hero .logo {
            max-height: 90px;
            max-width: 220px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,.3);
            background: #fff;
            padding: 8px;
        }
        .hero h1 {
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 20px rgba(0,0,0,.2);
        }
        .hero .lead {
            font-size: 1.1rem;
            opacity: .9;
            max-width: 600px;
            margin: 0 auto;
        }
        .hero .rating-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: .9rem;
        }
        .hero .social-row { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
        .btn-whatsapp {
            background: #25D366;
            color: #fff;
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 600;
            border: none;
            transition: all .3s;
            box-shadow: 0 4px 15px rgba(37,211,102,.3);
        }
        .btn-whatsapp:hover { background: #1da851; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,.4); }
        .social-btn {
            width: 48px; height: 48px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            transition: all .3s;
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
        }
        .social-btn:hover { transform: translateY(-3px) scale(1.05); color: #fff; }
        .social-btn.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
        .social-btn.facebook { background: #1877f2; }
        .social-btn.tiktok { background: #000; }
        .social-btn.whatsapp { background: #25D366; }

        /* ── Cards ── */
        .card-modern {
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 20px rgba(0,0,0,.05);
            transition: all .3s;
            overflow: hidden;
        }
        .card-modern:hover { box-shadow: 0 8px 30px rgba(0,0,0,.1); transform: translateY(-2px); }
        .card-header-modern {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-header-modern .icon-circle {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .card-body-modern { padding: 24px; }

        /* ── Info items ── */
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            background: var(--light);
            border: 1px solid var(--border);
            transition: all .3s;
            height: 100%;
        }
        .info-item:hover { border-color: var(--primary); background: #eef2ff; }
        .info-item .icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .info-item .label { font-size: .75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .info-item .value { font-weight: 600; font-size: .95rem; color: var(--dark); }

        /* ── Reseñas ── */
        .resena-card {
            background: #faf5ff;
            border-left: 4px solid #a855f7;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .star { color: var(--warning); font-size: 18px; }
        .star-empty { color: #d1d5db; font-size: 18px; }

        /* ── Cupones ── */
        .cupon-card {
            border: 2px dashed var(--success);
            background: #f0fdf4;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 12px;
        }
        .cupon-card .codigo {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 3px;
            color: var(--dark);
            background: #fff;
            border: 1px dashed var(--success);
            border-radius: 8px;
            padding: 6px 12px;
            display: inline-block;
        }

        /* ── Mapa ── */
        .mapa-container {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        .mapa-container iframe {
            width: 100%;
            height: 300px;
            border: none;
            display: block;
        }
        .btn-como-llegar {
            background: var(--primary);
            color: #fff;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            border: none;
            transition: all .3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-como-llegar:hover { background: var(--primary-dark); color: #fff; transform: translateY(-2px); }

        /* ── Seguimiento ── */
        .tracking-form {
            background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border);
        }

        /* ── Footer ── */
        .footer {
            background: var(--dark);
            color: #94a3b8;
            padding: 30px 0;
            text-align: center;
        }
        .footer .brand { color: #fff; font-weight: 700; font-size: 1.1rem; }

        /* ── Animaciones ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade { animation: fadeInUp .6s ease-out both; }
        .animate-delay-1 { animation-delay: .1s; }
        .animate-delay-2 { animation-delay: .2s; }
        .animate-delay-3 { animation-delay: .3s; }

        @media (max-width: 768px) {
            .hero { padding: 50px 0 70px; }
            .hero h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <!-- ══════════ HERO ══════════ -->
    <div class="hero text-center">
        <div class="container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo" class="logo mb-4">
            @else
                <div style="font-size:70px;" class="mb-3">📱</div>
            @endif
            <h1 class="fw-bold mb-2">{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Mi Tienda' }}</h1>
            @if($config->descripcion_corta)
                <p class="lead mb-4">{{ $config->descripcion_corta }}</p>
            @endif
            @if($promedio)
                <div class="rating-badge mb-4">
                    <span>
                        @for($i=1; $i<=5; $i++)
                            <span class="{{ $i <= round($promedio) ? 'star' : 'star-empty' }}" style="font-size:16px;">★</span>
                        @endfor
                    </span>
                    <strong>{{ number_format($promedio, 1) }}/5</strong>
                    <span style="opacity:.7;">· {{ $resenas->count() }} reseñas</span>
                </div>
            @endif
            <div class="social-row">
                @if($config->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $config->whatsapp) }}" target="_blank" class="btn btn-whatsapp">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a>
                @endif
                @if($config->instagram)
                    <a href="{{ $config->instagram }}" target="_blank" class="social-btn instagram" title="Instagram"><i class="bi bi-instagram"></i></a>
                @endif
                @if($config->facebook)
                    <a href="{{ $config->facebook }}" target="_blank" class="social-btn facebook" title="Facebook"><i class="bi bi-facebook"></i></a>
                @endif
                @if($config->tiktok)
                    <a href="{{ $config->tiktok }}" target="_blank" class="social-btn tiktok" title="TikTok"><i class="bi bi-tiktok"></i></a>
                @endif
            </div>
        </div>
    </div>

    <!-- ══════════ CONTENIDO ══════════ -->
    <div class="container py-5" style="margin-top:-40px;">
        <div class="row g-4">
            <!-- ── Columna principal ── -->
            <div class="col-lg-8">
                <!-- Información -->
                <div class="card-modern mb-4 animate-fade">
                    <div class="card-header-modern">
                        <div class="icon-circle" style="background:#eef2ff;color:var(--primary);">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Información</h5>
                    </div>
                    <div class="card-body-modern">
                        <div class="row g-3">
                            @if($config->direccion)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="icon" style="background:#fee2e2;color:var(--danger);"><i class="bi bi-geo-alt"></i></div>
                                    <div>
                                        <div class="label">Dirección</div>
                                        <div class="value">{{ $config->direccion }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($config->telefono)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="icon" style="background:#d1fae5;color:var(--success);"><i class="bi bi-telephone"></i></div>
                                    <div>
                                        <div class="label">Teléfono</div>
                                        <div class="value">{{ $config->telefono }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($config->horario_atencion)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="icon" style="background:#fef3c7;color:var(--warning);"><i class="bi bi-clock"></i></div>
                                    <div>
                                        <div class="label">Horario</div>
                                        <div class="value">{{ $config->horario_atencion }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($config->email)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="icon" style="background:#e0f2fe;color:var(--accent);"><i class="bi bi-envelope"></i></div>
                                    <div>
                                        <div class="label">Email</div>
                                        <div class="value">{{ $config->email }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Mapa / Ubicación -->
                @if($config->mapa_url)
                <div class="card-modern mb-4 animate-fade animate-delay-1">
                    <div class="card-header-modern">
                        <div class="icon-circle" style="background:#fee2e2;color:var(--danger);">
                            <i class="bi bi-map"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Ubicación</h5>
                    </div>
                    <div class="card-body-modern">
                        <div class="mapa-container mb-3">
                            <iframe
                                src="{{ $config->mapa_url }}"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Mapa de {{ $config->nombre_tienda ?? 'la tienda' }}">
                            </iframe>
                        </div>
                        <div class="text-center">
                            <a href="{{ $config->mapa_url }}" target="_blank" class="btn-como-llegar">
                                <i class="bi bi-sign-turn-right"></i> Cómo llegar
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Reseñas -->
                @if($resenas->isNotEmpty())
                <div class="card-modern animate-fade animate-delay-2">
                    <div class="card-header-modern">
                        <div class="icon-circle" style="background:#fef3c7;color:var(--warning);">
                            <i class="bi bi-star"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Reseñas de Clientes</h5>
                    </div>
                    <div class="card-body-modern">
                        @foreach($resenas as $resena)
                        <div class="resena-card">
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
                </div>
                @endif
            </div>

            <!-- ── Columna lateral ── -->
            <div class="col-lg-4">
                <!-- Cupones -->
                @if($cupones->isNotEmpty())
                <div class="card-modern mb-4 animate-fade animate-delay-1">
                    <div class="card-header-modern">
                        <div class="icon-circle" style="background:#d1fae5;color:var(--success);">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Cupones Activos</h5>
                    </div>
                    <div class="card-body-modern">
                        @foreach($cupones as $cupon)
                        <div class="cupon-card">
                            <div class="text-muted small mb-1">Código</div>
                            <div class="codigo mb-2">{{ $cupon->codigo }}</div>
                            <div class="text-success fw-bold">{{ $cupon->valor }}% de descuento</div>
                            <div class="text-muted small mt-1">{{ $cupon->descripcion }}</div>
                            @if($cupon->fecha_expiracion)
                                <div class="text-danger small mt-1">Vence: {{ $cupon->fecha_expiracion->format('d/m/Y') }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Seguimiento -->
                <div class="card-modern mb-4 animate-fade animate-delay-2">
                    <div class="card-header-modern">
                        <div class="icon-circle" style="background:#e0f2fe;color:var(--accent);">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Seguimiento de Reparación</h5>
                    </div>
                    <div class="card-body-modern">
                        <p class="text-muted" style="font-size:14px;">Ingresa tu número de orden para ver el estado de tu equipo.</p>
                        <div class="tracking-form">
                            <form action="{{ route('reparaciones.public-status.search') }}" method="GET" class="d-flex gap-2">
                                <input type="text" name="numero_orden" class="form-control"
                                       placeholder="N° de orden (ej: R-0001)"
                                       value="{{ request('numero_orden') }}" required>
                                <button type="submit" class="btn btn-primary" style="white-space:nowrap;border-radius:10px;">
                                    <i class="bi bi-search me-1"></i>Buscar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reseña -->
                <div class="card-modern animate-fade animate-delay-3">
                    <div class="card-header-modern">
                        <div class="icon-circle" style="background:#f3e8ff;color:#a855f7;">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <h5 class="fw-bold mb-0">¿Reparaste con nosotros?</h5>
                    </div>
                    <div class="card-body-modern">
                        <p class="text-muted" style="font-size:14px;">Cuéntanos tu experiencia y ayuda a otros clientes.</p>
                        <a href="{{ route('public.resena.form', $tenant->slug_publico) }}" class="btn btn-primary w-100" style="border-radius:10px;">
                            <i class="bi bi-star me-2"></i>Dejar una reseña
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ FOOTER ══════════ -->
    <footer class="footer">
        <div class="container">
            <div class="brand mb-2">{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Mi Tienda' }}</div>
            @if($config->direccion)
                <p class="small mb-1"><i class="bi bi-geo-alt me-1"></i>{{ $config->direccion }}</p>
            @endif
            @if($config->telefono)
                <p class="small mb-1"><i class="bi bi-telephone me-1"></i>{{ $config->telefono }}</p>
            @endif
            <p class="small mb-0 mt-3">© {{ date('Y') }} {{ $config->nombre_tienda ?? $tenant->empresa ?? 'Mi Tienda' }}. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>