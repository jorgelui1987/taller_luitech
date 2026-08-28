@extends('layouts.public')

@section('title', ($empresa->nombre_tienda ?? 'LUITECH') . ' · Servicio Técnico de Celulares y Computadores')

@php
    $waDigits = preg_replace('/\D/', '', (string) ($empresa->telefono ?? ''));
    $waNumber = $waDigits !== '' ? (str_starts_with($waDigits, '56') ? $waDigits : '56' . $waDigits) : null;
    // Coordenadas del local (ajustar según la ubicación real del taller)
    $mapCoords = [-29.9024, -71.2482];
@endphp

@section('content')
<section class="lp-hero">
    <div class="lp-container lp-hero-grid">
        <div>
            <span class="lp-hero-chip"><i class="fa-solid fa-location-dot"></i> {{ $empresa->direccion ?? 'Atención personalizada' }}</span>
            <h1 class="lp-hero-title">Tu tecnología en manos de <span class="lp-hero-grad">expertos</span>.</h1>
            <p class="lp-hero-text">Reparación de celulares, tablets, notebooks y PC con repuestos de calidad, garantía escrita y seguimiento en línea de tu orden, paso a paso.</p>
            <div class="lp-hero-actions">
                <a href="#consulta" class="lp-btn lp-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Consultar mi orden</a>
                <a href="#agendar" class="lp-btn lp-btn-ghost"><i class="fa-solid fa-calendar-check"></i> Agendar cita</a>
            </div>
        </div>

        <div class="lp-card" id="consulta">
            <span class="lp-card-corner">Rápido</span>
            <h3><i class="fa-solid fa-route"></i> Consulta Express</h3>
            <p>¿Dejaste tu equipo con nosotros? Ingresa el código de tu boleta y mira el avance actual de tu reparación.</p>
            <form method="GET" action="{{ route('reparaciones.public-status.search') }}">
                <label class="lp-label" for="express-code">Código de reparación</label>
                <div class="lp-input-group">
                    <span class="lp-input-prefix">RPT-</span>
                    <input class="lp-input" type="text" id="express-code" name="numero_orden" placeholder="001024" inputmode="numeric" autocomplete="off" required>
                </div>
                <button type="submit" class="lp-btn lp-btn-primary lp-btn-block"><i class="fa-solid fa-search"></i> Consultar estado</button>
                <p class="lp-hint">El código completo se ve así: <strong>RPT-001024</strong>. También puedes pegarlo completo.</p>
            </form>
        </div>
    </div>
</section>
<section id="servicios" class="lp-section">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">¿Qué ofrecemos?</span>
            <h2>Nuestras especialidades</h2>
            <p>Diagnóstico honesto, repuestos de calidad y garantía escrita en cada trabajo.</p>
        </div>
        <div class="lp-features">
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <h4>Pantallas y cristales</h4>
                <p>Reemplazo de pantallas OLED/IPS, vidrios templados e hidrogel para smartphones y tablets de todas las marcas.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-battery-full"></i></div>
                <h4>Baterías y carga</h4>
                <p>Cambio de baterías, puertos de carga, fallas de energía y accesorios certificados.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-microchip"></i></div>
                <h4>Placas y software</h4>
                <p>Reparación de placa a nivel componente, recuperación de software, sistemas y datos.</p>
            </div>
        </div>
    </div>
</section>

<section id="como-funciona" class="lp-section lp-section-alt">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Transparencia total</span>
            <h2>¿Cómo funciona?</h2>
            <p>Sabes qué pasa con tu equipo en todo momento, sin llamadas ni incertidumbre.</p>
        </div>
        <div class="lp-features">
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-receipt"></i></div>
                <span class="lp-feature-n">PASO 1</span>
                <h4>Deja tu equipo y guarda tu código</h4>
                <p>Al recibir tu equipo entregamos una boleta con el código de la orden de reparación.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-stethoscope"></i></div>
                <span class="lp-feature-n">PASO 2</span>
                <h4>Diagnóstico y presupuesto</h4>
                <p>Registramos la falla, el diagnóstico técnico y el presupuesto antes de tocar un solo tornillo.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-bell"></i></div>
                <span class="lp-feature-n">PASO 3</span>
                <h4>Aviso de retiro</h4>
                <p>Te notificamos cuando esté listo. Presenta tu boleta o el código en el mesón para retirarlo.</p>
            </div>
        </div>
    </div>
</section>
<section id="laboratorio" class="lp-section">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Laboratorio Digital</span>
            <h2>Herramientas inteligentes de diagnóstico</h2>
            <p>Cotiza tu reparación, evalúa la salud de tu equipo o sigue el avance de tu orden en tiempo real.</p>
        </div>

        <div class="lp-features">
            <a class="lp-feature" href="#cotizador">
                <div class="lp-feature-ico"><i class="fa-solid fa-calculator"></i></div>
                <h4>Cotizador Online</h4>
                <p>Obtén un presupuesto estimado para tu servicio técnico en segundos.</p>
            </a>
            <a class="lp-feature" href="#test-salud">
                <div class="lp-feature-ico"><i class="fa-solid fa-heart-pulse"></i></div>
                <h4>Test de Salud de Equipos</h4>
                <p>Responde 5 preguntas y descubre la condición real de tu equipo.</p>
            </a>
            <a class="lp-feature" href="{{ route('reparaciones.public-status.search') }}">
                <div class="lp-feature-ico"><i class="fa-solid fa-magnifying-glass"></i></div>
                <h4>Estado de mi Reparación</h4>
                <p>Sigue el avance de tu equipo en tiempo real con tu código de orden.</p>
            </a>
        </div>

        <div class="lp-card" id="cotizador" style="max-width:760px;margin:34px auto 0;">
            <h3><i class="fa-solid fa-bolt"></i> Cotizador de Reparaciones al Instante</h3>
            <p>Obtén un presupuesto estimado para tu servicio técnico en segundos.</p>
            <form id="cotizador-form" novalidate>
                <div class="lp-form-grid">
                    <div>
                        <label class="lp-label" for="cot-tipo">Tipo de Dispositivo</label>
                        <select class="lp-input" id="cot-tipo">
                            <option value="celular" selected>Celular / Smartphone</option>
                            <option value="tablet">Tablet</option>
                            <option value="notebook">Notebook</option>
                            <option value="pc">PC de Escritorio</option>
                            <option value="consola">Consola</option>
                        </select>
                    </div>
                    <div>
                        <label class="lp-label" for="cot-modelo">Modelo</label>
                        <input class="lp-input" type="text" id="cot-modelo" placeholder="Ej: iPhone 13" autocomplete="off">
                    </div>
                    <div class="full">
                        <label class="lp-label" for="cot-servicio">Falla o Servicio</label>
                        <select class="lp-input" id="cot-servicio">
                            <option value="pantalla" selected>Cambio de Pantalla</option>
                            <option value="bateria">Cambio de Batería</option>
                            <option value="puerto">Puerto de Carga</option>
                            <option value="mantenimiento">Mantenimiento / Limpieza</option>
                            <option value="software">Software / Sistema</option>
                            <option value="placa">Reparación de Placa</option>
                            <option value="camara">Cámara / Audio</option>
                            <option value="datos">Recuperación de Datos</option>
                            <option value="otro">Otro / No sé</option>
                        </select>
                    </div>
                </div>
                <div class="lp-quote-result">
                    <div>
                        <div class="lbl">Presupuesto estimado</div>
                        <div class="precio" id="cot-resultado-precio">—</div>
                    </div>
                    <div class="lbl" id="cot-resultado-nota">Selecciona dispositivo y servicio</div>
                </div>
                <p class="lp-quote-disclaimer"><i class="fa-solid fa-circle-info"></i> Precios referenciales en pesos chilenos. El presupuesto final se confirma tras el diagnóstico técnico, que no tiene costo.</p>
                <button type="submit" class="lp-btn lp-btn-primary lp-btn-block" style="margin-top:14px;">
                    <i class="fa-brands fa-whatsapp"></i> Agendar esta cotización por WhatsApp
                </button>
            </form>
        </div>

        <div class="lp-card" id="test-salud" style="max-width:760px;margin:34px auto 0;">
            <h3><i class="fa-solid fa-heart-pulse"></i> Test de Salud de Equipos</h3>
            <p>Responde 5 preguntas rápidas y descubre la condición real de tu equipo.</p>
            <form id="salud-form" novalidate>
                <div class="lp-form-grid">
                    <div>
                        <label class="lp-label" for="sal-tipo">Tipo de equipo *</label>
                        <select class="lp-input" id="sal-tipo">
                            <option value="" disabled selected>Selecciona…</option>
                            <option value="celular">Celular</option>
                            <option value="tablet">Tablet</option>
                            <option value="notebook">Notebook</option>
                        </select>
                    </div>
                    <div>
                        <label class="lp-label" for="sal-edad">¿Qué año lo compraste? *</label>
                        <select class="lp-input" id="sal-edad">
                            <option value="" disabled selected>Selecciona…</option>
                            <option value="menos1">Menos de 1 año</option>
                            <option value="a12">Entre 1 y 2 años</option>
                            <option value="a34">Entre 3 y 4 años</option>
                            <option value="a5mas">5 años o más</option>
                        </select>
                    </div>
                    <div>
                        <label class="lp-label" for="sal-bateria">¿Cuánto aguanta la batería? *</label>
                        <select class="lp-input" id="sal-bateria">
                            <option value="" disabled selected>Selecciona…</option>
                            <option value="mas1dia">Más de un día</option>
                            <option value="mediadia">Medio día</option>
                            <option value="menos3h">Menos de 3 horas</option>
                            <option value="apaga">Se apaga solo</option>
                        </select>
                    </div>
                    <div>
                        <label class="lp-label" for="sal-fisico">Estado físico *</label>
                        <select class="lp-input" id="sal-fisico">
                            <option value="" disabled selected>Selecciona…</option>
                            <option value="nuevo">Como nuevo</option>
                            <option value="rayones">Rayones leves</option>
                            <option value="trizado">Pantalla trizada o golpes</option>
                        </select>
                    </div>
                    <div class="full">
                        <label class="lp-label" for="sal-problemas">¿Presenta algún problema? *</label>
                        <select class="lp-input" id="sal-problemas">
                            <option value="" disabled selected>Selecciona…</option>
                            <option value="ninguno">Ninguno</option>
                            <option value="calienta">Se calienta</option>
                            <option value="lento">Funciona lento</option>
                            <option value="ambos">Ambos</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="lp-btn lp-btn-primary lp-btn-block" style="margin-top:14px;">
                    <i class="fa-solid fa-heart-pulse"></i> Evaluar mi equipo
                </button>
            </form>
            <div class="lp-salud-resultado" id="salud-resultado">
                <div class="lp-salud-score-box">
                    <div class="lbl" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);">Condición</div>
                    <div class="lp-salud-score" id="salud-score">—</div>
                    <div class="lp-salud-nivel" id="salud-nivel"></div>
                </div>
                <div class="lp-salud-detalle">
                    <p id="salud-mensaje"></p>
                    <button type="button" id="salud-wa" class="lp-btn lp-btn-primary"><i class="fa-brands fa-whatsapp"></i> Enviar mi resultado a un técnico</button>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="agendar" class="lp-section">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Sin esperas</span>
            <h2>Agenda tu reparación</h2>
            <p>Cuéntanos qué le pasa a tu equipo y coordinamos tu visita. Te respondemos por WhatsApp.</p>
        </div>
        @if($waNumber)
            <div class="lp-card" style="max-width:720px;margin:0 auto;">
                <form id="agendar-form" novalidate>
                    <div class="lp-form-grid">
                        <div>
                            <label class="lp-label" for="ag-nombre">Tu nombre *</label>
                            <input class="lp-input" type="text" id="ag-nombre" placeholder="Juan Pérez" autocomplete="name">
                        </div>
                        <div>
                            <label class="lp-label" for="ag-telefono">Tu teléfono *</label>
                            <input class="lp-input" type="tel" id="ag-telefono" placeholder="+56 9 1234 5678" autocomplete="tel">
                        </div>
                        <div>
                            <label class="lp-label" for="ag-equipo">Equipo *</label>
                            <select class="lp-input" id="ag-equipo">
                                <option value="">Selecciona…</option>
                                <option>Celular</option>
                                <option>Tablet</option>
                                <option>Notebook</option>
                                <option>PC de escritorio</option>
                                <option>Consola / Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="lp-label" for="ag-fecha">Fecha preferida (opcional)</label>
                            <input class="lp-input" type="date" id="ag-fecha">
                        </div>
                        <div class="full">
                            <label class="lp-label" for="ag-problema">¿Qué le pasa a tu equipo? *</label>
                            <textarea class="lp-input lp-textarea" id="ag-problema" placeholder="Ej: iPhone 13 no carga, se apaga solo…"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="lp-btn lp-btn-primary lp-btn-block" style="margin-top:14px;">
                        <i class="fa-brands fa-whatsapp"></i> Enviar solicitud por WhatsApp
                    </button>
                    <p class="lp-hint">Al enviar se abrirá WhatsApp con tu solicitud lista para enviarnos. Sin registros ni contraseñas.</p>
                </form>
            </div>
        @else
            <div class="lp-callout lp-callout-plain" style="max-width:720px;margin:0 auto;">
                <small><i class="fa-solid fa-phone"></i> Agendamiento</small>
                Configura un teléfono de contacto en la configuración de la tienda para habilitar el agendamiento en línea. Mientras tanto, contáctanos directamente.
            </div>
        @endif
    </div>
</section>

<section id="planes" class="lp-section lp-section-alt {{ empty($abrirPlanes) ? 'lp-collapsed' : 'lp-open' }}">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Planes y precios</span>
            <h2>Elige tu plan</h2>
            <p>Empieza gratis y crece a tu ritmo. Sin contratos forzosos, cancela cuando quieras.</p>
        </div>
        @php
            $planesPrecios = \App\Models\PlanPrecio::getPlanesActivos();
            $planGratis = $planesPrecios['gratis'] ?? null;
            $planBasico = $planesPrecios['basico'] ?? null;
            $planProfesional = $planesPrecios['profesional'] ?? null;
            $planEmpresarial = $planesPrecios['empresarial'] ?? null;
            $mostrarPrecio = function ($plan, $default) {
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
        <div class="lp-pricing">
            {{-- GRATIS --}}
            <div class="lp-plan">
                <h4><i class="fa-solid fa-seedling" style="color:#34d399;"></i> {{ $planGratis->nombre ?? 'Gratis' }}</h4>
                <p class="lp-plan-desc">{{ $planGratis->descripcion ?? 'Para empezar' }}</p>
                <div class="lp-plan-price">{{ $mostrarPrecio($planGratis, 'S/0') }} <small>/mes</small></div>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Hasta 3 usuarios</li>
                    <li><i class="fa-solid fa-check"></i> Hasta 50 productos</li>
                    <li><i class="fa-solid fa-check"></i> Ventas básicas</li>
                    <li><i class="fa-solid fa-check"></i> Reparaciones básicas</li>
                    <li><i class="fa-solid fa-check"></i> Reportes básicos</li>
                    <li class="no"><i class="fa-solid fa-xmark"></i> Exportar a Excel</li>
                    <li class="no"><i class="fa-solid fa-xmark"></i> Notificaciones WhatsApp</li>
                    <li class="no"><i class="fa-solid fa-xmark"></i> Soporte prioritario</li>
                </ul>
                <a href="{{ route('registro.tenant') }}" class="lp-btn lp-btn-ghost lp-btn-block">Comenzar gratis</a>
            </div>
            {{-- BÁSICO --}}
            <div class="lp-plan">
                <h4><i class="fa-solid fa-rocket" style="color:#22d3ee;"></i> {{ $planBasico->nombre ?? 'Básico' }}</h4>
                <p class="lp-plan-desc">{{ $planBasico->descripcion ?? 'Para negocios pequeños' }}</p>
                <div class="lp-plan-price">{{ $mostrarPrecio($planBasico, 'S/49') }} <small>/mes</small></div>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Hasta 5 usuarios</li>
                    <li><i class="fa-solid fa-check"></i> Hasta 200 productos</li>
                    <li><i class="fa-solid fa-check"></i> Ventas completas</li>
                    <li><i class="fa-solid fa-check"></i> Reparaciones completas</li>
                    <li><i class="fa-solid fa-check"></i> Reportes avanzados</li>
                    <li><i class="fa-solid fa-check"></i> Exportar a Excel</li>
                    <li><i class="fa-solid fa-check"></i> Notificaciones WhatsApp</li>
                    <li class="no"><i class="fa-solid fa-xmark"></i> Soporte prioritario</li>
                </ul>
                <a href="{{ route('registro.tenant') }}" class="lp-btn lp-btn-ghost lp-btn-block">Lo quiero</a>
            </div>
            {{-- PROFESIONAL (destacado) --}}
            <div class="lp-plan is-popular">
                <span class="lp-plan-badge">Más popular</span>
                <h4><i class="fa-solid fa-star" style="color:#22d3ee;"></i> {{ $planProfesional->nombre ?? 'Profesional' }}</h4>
                <p class="lp-plan-desc">{{ $planProfesional->descripcion ?? 'Para negocios en crecimiento' }}</p>
                <div class="lp-plan-price">{{ $mostrarPrecio($planProfesional, 'S/99') }} <small>/mes</small></div>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Hasta 15 usuarios</li>
                    <li><i class="fa-solid fa-check"></i> Hasta 1,000 productos</li>
                    <li><i class="fa-solid fa-check"></i> Ventas completas</li>
                    <li><i class="fa-solid fa-check"></i> Reparaciones completas</li>
                    <li><i class="fa-solid fa-check"></i> Reportes avanzados</li>
                    <li><i class="fa-solid fa-check"></i> Exportar a Excel</li>
                    <li><i class="fa-solid fa-check"></i> Notificaciones WhatsApp</li>
                    <li><i class="fa-solid fa-check"></i> Soporte prioritario</li>
                </ul>
                <a href="{{ route('registro.tenant') }}" class="lp-btn lp-btn-primary lp-btn-block">Lo quiero</a>
            </div>
            {{-- EMPRESARIAL --}}
            <div class="lp-plan">
                <h4><i class="fa-solid fa-building" style="color:#3b82f6;"></i> {{ $planEmpresarial->nombre ?? 'Empresarial' }}</h4>
                <p class="lp-plan-desc">{{ $planEmpresarial->descripcion ?? 'Para grandes tiendas' }}</p>
                <div class="lp-plan-price">{{ $mostrarPrecio($planEmpresarial, 'S/199') }} <small>/mes</small></div>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Usuarios ilimitados</li>
                    <li><i class="fa-solid fa-check"></i> Productos ilimitados</li>
                    <li><i class="fa-solid fa-check"></i> Todas las funcionalidades</li>
                    <li><i class="fa-solid fa-check"></i> Múltiples sucursales</li>
                    <li><i class="fa-solid fa-check"></i> API personalizada</li>
                    <li><i class="fa-solid fa-check"></i> Soporte 24/7</li>
                    <li><i class="fa-solid fa-check"></i> Capacitación del equipo</li>
                    <li><i class="fa-solid fa-check"></i> Dominio personalizado</li>
                </ul>
                <a href="{{ route('registro.tenant') }}" class="lp-btn lp-btn-ghost lp-btn-block">Contactar</a>
            </div>
        </div>
    </div>
</section>

<section class="lp-section">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Para emprendedores</span>
            <h2>¿Tienes una tienda de celulares?</h2>
            <p>Este mismo sistema gestiona ventas, inventario, reparaciones, comisiones y reportes. Pruébalo gratis.</p>
        </div>
        <div class="lp-hero-actions" style="justify-content:center;">
            <a href="{{ route('registro.tenant') }}" class="lp-btn lp-btn-primary"><i class="fa-solid fa-store"></i> Registra tu tienda gratis</a>
            <button type="button" id="btn-planes" class="lp-btn lp-btn-ghost" aria-expanded="false" aria-controls="planes"><i class="fa-solid fa-tags"></i> <span>Ver planes</span></button>
            <a href="{{ route('login') }}" class="lp-btn lp-btn-ghost"><i class="fa-solid fa-right-to-bracket"></i> Acceso del personal</a>
        </div>
    </div>
</section>

<section id="contacto" class="lp-section lp-section-alt">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Visítanos</span>
            <h2>Contacto</h2>
            <p>Estamos para ayudarte con tu equipo. Escríbenos o ven al local.</p>
        </div>
        <div class="lp-features">
            @if(!empty($empresa->direccion))
                <div class="lp-feature">
                    <div class="lp-feature-ico"><i class="fa-solid fa-location-dot"></i></div>
                    <h4>Dirección</h4>
                    <p>{{ $empresa->direccion }}</p>
                </div>
            @endif
            @if(!empty($empresa->telefono))
                @php
                    $waDigits = preg_replace('/\D/', '', (string) $empresa->telefono);
                    $waNumber = $waDigits !== '' ? (str_starts_with($waDigits, '56') ? $waDigits : '56' . $waDigits) : null;
                @endphp
                <div class="lp-feature">
                    <div class="lp-feature-ico"><i class="fa-brands fa-whatsapp"></i></div>
                    <h4>WhatsApp</h4>
                    <p><a class="lp-wa" href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Hola, tengo una consulta sobre mi equipo') }}" target="_blank" rel="noopener">+{{ $waNumber }}</a></p>
                </div>
            @endif
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-tv"></i></div>
                <h4>Sala de espera</h4>
                <p>Sigue los turnos en vivo en la pantalla del local o desde <a href="{{ route('public.pantalla') }}" target="_blank" rel="noopener">tu celular</a>.</p>
            </div>
            </div>

            <div class="lp-map" id="map" style="margin-top:26px;"></div>
        </div>
    </div>
</section>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('external-scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@push('scripts')
    // Solo caracteres válidos en el código express
    const lpCode = document.getElementById('express-code');
    if (lpCode) {
        lpCode.addEventListener('input', () => {
            lpCode.value = lpCode.value.replace(/[^0-9A-Za-z-]/g, '').toUpperCase();
        });
    }

    // Mapa oscuro de la ubicación del local
    const mapEl = document.getElementById('map');
    if (mapEl && typeof L !== 'undefined') {
        const coords = @json($mapCoords);
        const tiendaNombre = @json($empresa->nombre_tienda ?? 'LUITECH');
        const tiendaDireccion = @json($empresa->direccion ?? '');
        const mapa = L.map('map', { center: coords, zoom: 17, scrollWheelZoom: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);
        mapEl.classList.add('dark-map');
        L.marker(coords).addTo(mapa)
            .bindPopup('<b style="color:#22d3ee">' + tiendaNombre + '</b><br><span style="font-size:12px">' + tiendaDireccion + '</span>')
            .openPopup();
    }

    // Acordeón de planes: plegado por defecto, se despliega al hacer clic
    const btnPlanes = document.getElementById('btn-planes');
    const secPlanes = document.getElementById('planes');
    if (btnPlanes && secPlanes) {
        const setPlanes = (abrir) => {
            secPlanes.classList.toggle('lp-collapsed', !abrir);
            secPlanes.classList.toggle('lp-open', abrir);
            btnPlanes.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            btnPlanes.querySelector('span').textContent = abrir ? 'Ocultar planes' : 'Ver planes';
            btnPlanes.querySelector('i').className = abrir ? 'fa-solid fa-eye-slash' : 'fa-solid fa-tags';
            if (abrir) setTimeout(() => secPlanes.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
        };
        btnPlanes.addEventListener('click', () => setPlanes(secPlanes.classList.contains('lp-collapsed')));
        @if(!empty($abrirPlanes))
            setPlanes(true);
        @endif
    }

    // Test de Salud de Equipos: cuestionario interactivo con puntaje
    const SALUD_PENAL = {
        edad:      { menos1: 0, a12: -10, a34: -25, a5mas: -40 },
        bateria:   { mas1dia: 0, mediadia: -15, menos3h: -30, apaga: -45 },
        fisico:    { nuevo: 0, rayones: -10, trizado: -25 },
        problemas: { ninguno: 0, calienta: -10, lento: -10, ambos: -20 }
    };
    const salForm = document.getElementById('salud-form');
    const salBox = document.getElementById('salud-resultado');
    let saludData = null;
    if (salForm) {
        const salVal = id => document.getElementById(id).value;
        const salTxt = id => document.getElementById(id).options[document.getElementById(id).selectedIndex].text;
        salForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const ids = ['sal-tipo', 'sal-edad', 'sal-bateria', 'sal-fisico', 'sal-problemas'];
            for (const id of ids) {
                if (!salVal(id)) {
                    lpToast('Responde todas las preguntas para evaluar tu equipo.', 'error');
                    return;
                }
            }
            let score = 100;
            score += SALUD_PENAL.edad[salVal('sal-edad')] || 0;
            score += SALUD_PENAL.bateria[salVal('sal-bateria')] || 0;
            score += SALUD_PENAL.fisico[salVal('sal-fisico')] || 0;
            score += SALUD_PENAL.problemas[salVal('sal-problemas')] || 0;
            score = Math.max(0, Math.min(100, score));
            let nivel, color, mensaje;
            if (score >= 80) {
                nivel = 'Excelente estado'; color = '#34d399';
                mensaje = 'Tu equipo está saludable. Sigue con las buenas prácticas de carga y limpieza.';
            } else if (score >= 55) {
                nivel = 'Estado regular'; color = '#fbbf24';
                mensaje = 'Te recomendamos un mantenimiento preventivo para alargar su vida útil.';
            } else {
                nivel = 'Estado crítico'; color = '#f87171';
                mensaje = 'Tu equipo necesita revisión urgente — probablemente batería agotada u otro desgaste interno.';
            }
            document.getElementById('salud-score').textContent = score + '/100';
            document.getElementById('salud-score').style.color = color;
            document.getElementById('salud-nivel').textContent = nivel;
            document.getElementById('salud-nivel').style.color = color;
            document.getElementById('salud-mensaje').textContent = mensaje;
            salBox.style.display = 'flex';
            saludData = {
                tipo: salTxt('sal-tipo'), edad: salTxt('sal-edad'), bateria: salTxt('sal-bateria'),
                fisico: salTxt('sal-fisico'), problemas: salTxt('sal-problemas'), score, nivel
            };
            salBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        const salWa = document.getElementById('salud-wa');
        salWa.addEventListener('click', () => {
            const WA_SAL = @json($waNumber);
            if (!saludData) {
                lpToast('Primero evalúa tu equipo.', 'error');
                return;
            }
            if (!WA_SAL) {
                lpToast('No hay teléfono de contacto configurado.', 'error');
                return;
            }
            let msg = 'Hola, hice el TEST DE SALUD en la web:\n';
            msg += '• Equipo: ' + saludData.tipo + '\n';
            msg += '• Año de compra: ' + saludData.edad + '\n';
            msg += '• Batería: ' + saludData.bateria + '\n';
            msg += '• Estado físico: ' + saludData.fisico + '\n';
            msg += '• Problemas: ' + saludData.problemas + '\n';
            msg += '• Condición: ' + saludData.nivel + ' (' + saludData.score + '/100)';
            window.open('https://wa.me/' + WA_SAL + '?text=' + encodeURIComponent(msg), '_blank');
        });
    }

    // Cotizador de reparaciones al instante (precios referenciales CLP)
    const COT_SERVICIOS = {
        pantalla: { precios: { celular:[45000,90000], tablet:[60000,120000], notebook:[90000,180000], pc:[70000,150000], consola:[90000,180000] } },
        bateria:  { precios: { celular:[25000,45000], tablet:[35000,60000], notebook:[45000,90000], consola:[40000,70000] } },
        puerto:   { precios: { celular:[20000,40000], tablet:[25000,45000], notebook:[30000,60000], pc:[15000,35000], consola:[30000,60000] } },
        mantenimiento: { precios: { celular:[15000,25000], tablet:[18000,30000], notebook:[25000,45000], pc:[20000,40000], consola:[25000,40000] } },
        software: { precios: { celular:[15000,30000], tablet:[15000,30000], notebook:[20000,40000], pc:[20000,40000], consola:[20000,35000] } },
        placa:    { precios: { celular:[60000,150000], tablet:[70000,160000], notebook:[80000,180000], pc:[60000,150000], consola:[80000,200000] } },
        camara:   { precios: { celular:[20000,50000], tablet:[25000,55000], notebook:[30000,60000], pc:[20000,45000] } },
        datos:    { precios: { celular:[30000,80000], tablet:[35000,90000], notebook:[40000,120000], pc:[30000,90000] } },
        otro:     { precios: {} }
    };
    const clp = n => '$' + n.toLocaleString('es-CL');
    const cotTipo = document.getElementById('cot-tipo');
    const cotServicio = document.getElementById('cot-servicio');
    const cotModelo = document.getElementById('cot-modelo');
    const cotPrecio = document.getElementById('cot-resultado-precio');
    const cotNota = document.getElementById('cot-resultado-nota');
    const cotActualizar = () => {
        const serv = COT_SERVICIOS[cotServicio.value];
        const rango = serv ? serv.precios[cotTipo.value] : null;
        if (rango) {
            cotPrecio.textContent = clp(rango[0]) + ' - ' + clp(rango[1]);
            cotNota.textContent = 'Precio referencial · el presupuesto final se confirma tras el diagnóstico (sin costo)';
        } else {
            cotPrecio.textContent = 'A medida';
            cotNota.textContent = 'Este servicio se cotiza personalizado — escríbenos por WhatsApp';
        }
    };
    if (cotTipo && cotServicio) {
        cotTipo.addEventListener('change', cotActualizar);
        cotServicio.addEventListener('change', cotActualizar);
        cotActualizar();
    }
    const cotForm = document.getElementById('cotizador-form');
    if (cotForm) {
        const WA_COT = @json($waNumber);
        cotForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!WA_COT) {
                lpToast('No hay teléfono de contacto configurado para cotizar.', 'error');
                return;
            }
            const equipoTexto = cotTipo.options[cotTipo.selectedIndex].text + (cotModelo.value.trim() ? ' (' + cotModelo.value.trim() + ')' : '');
            const servicioTexto = cotServicio.options[cotServicio.selectedIndex].text;
            const rango = (COT_SERVICIOS[cotServicio.value] || { precios: {} }).precios[cotTipo.value];
            const estimado = rango ? clp(rango[0]) + ' - ' + clp(rango[1]) : 'por evaluar';
            let msg = 'Hola, quiero cotizar una reparación:\n';
            msg += '• Equipo: ' + equipoTexto + '\n';
            msg += '• Servicio: ' + servicioTexto + '\n';
            msg += '• Estimado web: ' + estimado;
            window.open('https://wa.me/' + WA_COT + '?text=' + encodeURIComponent(msg), '_blank');
            lpToast('¡Cotización lista! Se abrió WhatsApp para enviarla.', 'success');
        });
    }

    // Formulario de agendamiento → abre WhatsApp con la solicitud armada
    const agForm = document.getElementById('agendar-form');
    if (agForm) {
        const WA_NUM = @json($waNumber);
        agForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!WA_NUM) {
                lpToast('No hay teléfono de contacto configurado para agendar.', 'error');
                return;
            }
            const nombre = document.getElementById('ag-nombre').value.trim();
            const telefono = document.getElementById('ag-telefono').value.trim();
            const equipo = document.getElementById('ag-equipo').value;
            const problema = document.getElementById('ag-problema').value.trim();
            const fecha = document.getElementById('ag-fecha').value;
            if (!nombre || !telefono || !equipo || !problema) {
                lpToast('Completa los campos marcados con *.', 'error');
                return;
            }
            let msg = 'Hola, quiero agendar una reparación:\n';
            msg += '• Nombre: ' + nombre + '\n';
            msg += '• Teléfono: ' + telefono + '\n';
            msg += '• Equipo: ' + equipo + '\n';
            msg += '• Problema: ' + problema;
            if (fecha) msg += '\n• Fecha preferida: ' + fecha;
            window.open('https://wa.me/' + WA_NUM + '?text=' + encodeURIComponent(msg), '_blank');
            lpToast('¡Solicitud lista! Se abrió WhatsApp para enviarla.', 'success');
            agForm.reset();
        });
    }
@endpush