<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Celulares - Gestión para tu tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" integrity="sha384-4LISF5TTJX/fLmGSxO53rV4miRxdg84mZsxmO8Rx5jGtp/LbrixFETvWa5a6sESd" crossorigin="anonymous">
    <style>
        .pricing-card { transition: transform 0.3s, box-shadow 0.3s; border-radius: 16px; }
        .pricing-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .pricing-card.popular { border: 2px solid #0d6efd; }
        .pricing-card .card-body { padding: 2rem; }
        .price { font-size: 2.5rem; font-weight: 800; color: #1e1b4b; }
        .price small { font-size: 1rem; font-weight: 400; color: #6b7280; }
        .feature-list { list-style: none; padding: 0; }
        .feature-list li { padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
        .feature-list li:last-child { border-bottom: none; }
        .feature-list .feature-yes { color: #10b981; margin-right: 8px; }
        .feature-list .feature-no { color: #dc2626; margin-right: 8px; }
        .hero-section { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #3730a3 100%); }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: #1e1b4b;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#" style="text-decoration:none;">
                <span style="width:40px;height:40px;background:linear-gradient(135deg,#a855f7,#ec4899);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 4px 12px rgba(168,85,247,0.4);">📱</span>
                <span style="font-size:22px;font-weight:800;background:linear-gradient(135deg,#a855f7,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:1px;">LUITECH</span>
            </a>
            <div class="ms-auto">
                <a href="{{ route('registro.tenant') }}" class="btn btn-light me-2">Comenzar Gratis</a>
                <a href="{{ route('login') }}" class="btn btn-outline-light">Iniciar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="hero-section text-white text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Gestiona tu tienda de celulares como nunca antes</h1>
            <div class="row justify-content-center g-3">
                <div class="col-md-3">
                    <div class="p-3" style="background:rgba(255,255,255,0.15);border-radius:12px;border:1px solid rgba(255,255,255,0.25);">
                        <div style="font-size:2.5rem;">🛒</div>
                        <h5 class="mt-2 fw-bold">Ventas</h5>
                        <p class="small mb-0" style="color:rgba(255,255,255,0.8);">Carrito, múltiples pagos, tickets</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background:rgba(255,255,255,0.15);border-radius:12px;border:1px solid rgba(255,255,255,0.25);">
                        <div style="font-size:2.5rem;">🔧</div>
                        <h5 class="mt-2 fw-bold">Reparaciones</h5>
                        <p class="small mb-0" style="color:rgba(255,255,255,0.8);">Órdenes, estados, WhatsApp</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background:rgba(255,255,255,0.15);border-radius:12px;border:1px solid rgba(255,255,255,0.25);">
                        <div style="font-size:2.5rem;">📦</div>
                        <h5 class="mt-2 fw-bold">Inventario</h5>
                        <p class="small mb-0" style="color:rgba(255,255,255,0.8);">Stock, IMEI, alertas</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background:rgba(255,255,255,0.15);border-radius:12px;border:1px solid rgba(255,255,255,0.25);">
                        <div style="font-size:2.5rem;">📈</div>
                        <h5 class="mt-2 fw-bold">Reportes</h5>
                        <p class="small mb-0" style="color:rgba(255,255,255,0.8);">Dashboard, gráficos, PDF</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('registro.tenant') }}" class="btn btn-light btn-lg mt-4">Comenzar gratis →</a>
        </div>
    </header>

    <!-- ═══════════ PLANES Y PRECIOS ═══════════ -->
    @php
        $planesPrecios = \App\Models\PlanPrecio::getPlanesActivos();
        $planGratis = $planesPrecios['gratis'] ?? null;
        $planBasico = $planesPrecios['basico'] ?? null;
        $planProfesional = $planesPrecios['profesional'] ?? null;
        $planEmpresarial = $planesPrecios['empresarial'] ?? null;

        // Helper para formatear precio incluso si es objeto de respaldo
        $mostrarPrecio = function($plan, $default) {
            if (!$plan) return $default;
            if (is_object($plan) && method_exists($plan, 'precioFormateado')) {
                return $plan->precioFormateado();
            }
            if (isset($plan->simbolo) && isset($plan->precio_mensual)) {
                $p = $plan->precio_mensual;
                return $plan->simbolo . ($p == 0 ? '0' : ($p == floor($p) ? number_format($p, 0) : number_format($p, 2)));
            }
            return $default;
        };
    @endphp
    <section class="py-5" id="precios">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="color:#1e1b4b;">Planes y Precios</h2>
                <p class="text-muted">Elige el plan que mejor se adapte a tu negocio</p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Plan GRATIS -->
                <div class="col-lg-3 col-md-6">
                    <div class="card pricing-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">🌱 {{ $planGratis->nombre ?? 'Gratis' }}</h5>
                            <p class="text-muted small">{{ $planGratis->descripcion ?? 'Para empezar' }}</p>
                            <div class="price">{{ $mostrarPrecio($planGratis, 'S/0') }} <small>/mes</small></div>
                            <ul class="feature-list text-start mt-3">
                                <li><span class="feature-yes">✅</span> Hasta 3 usuarios</li>
                                <li><span class="feature-yes">✅</span> Hasta 50 productos</li>
                                <li><span class="feature-yes">✅</span> Ventas básicas</li>
                                <li><span class="feature-yes">✅</span> Reparaciones básicas</li>
                                <li><span class="feature-yes">✅</span> Reportes básicos</li>
                                <li><span class="feature-no">❌</span> <span class="text-muted">Exportar a Excel</span></li>
                                <li><span class="feature-no">❌</span> <span class="text-muted">Notificaciones WhatsApp</span></li>
                                <li><span class="feature-no">❌</span> <span class="text-muted">Soporte prioritario</span></li>
                            </ul>
                            <a href="{{ route('registro.tenant') }}" class="btn btn-outline-primary w-100 mt-3">Comenzar Gratis</a>
                        </div>
                    </div>
                </div>

                <!-- Plan BÁSICO -->
                <div class="col-lg-3 col-md-6">
                    <div class="card pricing-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">🚀 {{ $planBasico->nombre ?? 'Básico' }}</h5>
                            <p class="text-muted small">{{ $planBasico->descripcion ?? 'Para negocios pequeños' }}</p>
                            <div class="price">{{ $mostrarPrecio($planBasico, 'S/49') }} <small>/mes</small></div>
                            <ul class="feature-list text-start mt-3">
                                <li><span class="feature-yes">✅</span> Hasta 5 usuarios</li>
                                <li><span class="feature-yes">✅</span> Hasta 200 productos</li>
                                <li><span class="feature-yes">✅</span> Ventas completas</li>
                                <li><span class="feature-yes">✅</span> Reparaciones completas</li>
                                <li><span class="feature-yes">✅</span> Reportes avanzados</li>
                                <li><span class="feature-yes">✅</span> Exportar a Excel</li>
                                <li><span class="feature-yes">✅</span> Notificaciones WhatsApp</li>
                                <li><span class="feature-no">❌</span> <span class="text-muted">Soporte prioritario</span></li>
                            </ul>
                            <a href="{{ route('registro.tenant') }}" class="btn btn-outline-primary w-100 mt-3">Lo quiero</a>
                        </div>
                    </div>
                </div>

                <!-- Plan PROFESIONAL (DESTACADO) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card pricing-card h-100 shadow popular" style="border-color: #0d6efd;">
                        <div class="card-body text-center position-relative">
                            <span class="badge bg-primary position-absolute" style="top: -12px; right: 20px; padding: 6px 16px;">MÁS POPULAR</span>
                            <h5 class="fw-bold">⭐ {{ $planProfesional->nombre ?? 'Profesional' }}</h5>
                            <p class="text-muted small">{{ $planProfesional->descripcion ?? 'Para negocios en crecimiento' }}</p>
                            <div class="price">{{ $mostrarPrecio($planProfesional, 'S/99') }} <small>/mes</small></div>
                            <ul class="feature-list text-start mt-3">
                                <li><span class="feature-yes">✅</span> Hasta 15 usuarios</li>
                                <li><span class="feature-yes">✅</span> Hasta 1,000 productos</li>
                                <li><span class="feature-yes">✅</span> Ventas completas</li>
                                <li><span class="feature-yes">✅</span> Reparaciones completas</li>
                                <li><span class="feature-yes">✅</span> Reportes avanzados</li>
                                <li><span class="feature-yes">✅</span> Exportar a Excel</li>
                                <li><span class="feature-yes">✅</span> Notificaciones WhatsApp</li>
                                <li><span class="feature-yes">✅</span> Soporte prioritario</li>
                            </ul>
                            <a href="{{ route('registro.tenant') }}" class="btn btn-primary w-100 mt-3">Lo quiero</a>
                        </div>
                    </div>
                </div>

                <!-- Plan EMPRESARIAL -->
                <div class="col-lg-3 col-md-6">
                    <div class="card pricing-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">🏢 {{ $planEmpresarial->nombre ?? 'Empresarial' }}</h5>
                            <p class="text-muted small">{{ $planEmpresarial->descripcion ?? 'Para grandes tiendas' }}</p>
                            <div class="price">{{ $mostrarPrecio($planEmpresarial, 'S/199') }} <small>/mes</small></div>
                            <ul class="feature-list text-start mt-3">
                                <li><span class="feature-yes">✅</span> Usuarios ilimitados</li>
                                <li><span class="feature-yes">✅</span> Productos ilimitados</li>
                                <li><span class="feature-yes">✅</span> Todas las funcionalidades</li>
                                <li><span class="feature-yes">✅</span> Múltiples sucursales</li>
                                <li><span class="feature-yes">✅</span> API personalizada</li>
                                <li><span class="feature-yes">✅</span> Soporte 24/7</li>
                                <li><span class="feature-yes">✅</span> Capacitación del equipo</li>
                                <li><span class="feature-yes">✅</span> Dominio personalizado</li>
                            </ul>
                            <a href="{{ route('registro.tenant') }}" class="btn btn-outline-primary w-100 mt-3">Contactar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nota: al registrarse obtienen plan Gratis -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    ℹ️
                    Al registrarse obtienes el plan <strong>Gratis</strong> automáticamente.
                    Puedes upgradear cuando quieras contactándonos.
                </p>
                <p class="mt-2">
                    💬
                    Contáctanos por WhatsApp:
                    <a href="https://wa.me/56982209690?text=Quiero%20información%20sobre%20los%20planes" target="_blank" class="text-success fw-bold">
                        +56982209690
                    </a>
                    &nbsp;·&nbsp;
                    ✉️
                    <a href="mailto:luitechserena@gmail.com" class="text-primary fw-bold">luitechserena@gmail.com</a>
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} CRM Celulares. Todos los derechos reservados.</p>
            <p class="small text-muted mt-1">
                <a href="#precios" class="text-muted">Planes</a> ·
                <a href="{{ route('registro.tenant') }}" class="text-muted">Registro</a> ·
                <a href="{{ route('login') }}" class="text-muted">Iniciar Sesión</a>
            </p>
        </div>
    </footer>
</body>
</html>
