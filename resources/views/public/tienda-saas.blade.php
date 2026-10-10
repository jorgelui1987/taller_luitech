<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Tienda' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/tienda-saas.css') }}?v=20261010a" rel="stylesheet">
</head>
<body>
<header class="top"><div class="wrap top-in">
<div class="brand"><div class="logo">@if(!empty($logoSrc))<img src="{{ $logoSrc }}">@else<i class="fa-solid fa-store"></i>@endif</div>
<div><div class="bname">{{ $config->nombre_tienda ?? $tenant->empresa }}</div><div class="bsub">luitech.fun/t/{{ $tenant->slug_publico }}</div></div></div>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
@if(isset($promedio) && $promedio)<span class="chip"><i class="fa-solid fa-star"></i> {{ number_format($promedio,1) }}</span>@endif
@if($config->horario_atencion)<span class="chip"><span style="width:8px;height:8px;border-radius:50%;background:var(--emerald);display:inline-block"></span> {{ $config->horario_atencion }}</span>@endif
</div></div></header>
<section class="hero"><div class="wrap">
<span class="chip"><i class="fa-solid fa-microchip"></i> Servicio técnico · Ventas · Accesorios</span>
<h1 class="h1">Tu celular como nuevo, <span>el mismo día</span>.</h1>
<p class="lead">@if($config->descripcion_corta){{ $config->descripcion_corta }}<br>@endif@if($config->direccion){{ $config->direccion }} · @endif@if(isset($promedio) && $promedio) {{ number_format($promedio,1) }} estrellas ({{ $resenas->count() }} reseñas)@endif</p>
<div class="cta">
@if(!empty($whatsappUrl))<a class="btn btn-wa" href="{{ $whatsappUrl }}" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp {{ $config->whatsapp ?? $config->telefono ?? '' }}</a>@endif
<a class="btn btn-p" href="#seguimiento"><i class="fa-solid fa-magnifying-glass"></i> Consultar mi reparación</a>
@if(!empty($instagramUrl))<a class="btn btn-g" href="{{ $instagramUrl }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>@endif
@if(!empty($facebookUrl))<a class="btn btn-g" href="{{ $facebookUrl }}" target="_blank"><i class="fa-brands fa-facebook"></i></a>@endif
@if(!empty($tiktokUrl))<a class="btn btn-g" href="{{ $tiktokUrl }}" target="_blank"><i class="fa-brands fa-tiktok"></i></a>@endif
</div></div></section>
<section class="sec"><div class="wrap"><div class="card">
<h3 style="margin:0 0 4px"><i class="fa-solid fa-screwdriver-wrench" style="color:var(--cyan)"></i> Nuestros servicios</h3>
<p style="color:var(--muted);font-size:13px;margin:0 0 16px">Reparación profesional con garantía</p>
<div class="grid4">
<div class="srv"><div class="ic">📱</div><b>Cambio de pantalla</b><br><small>Todas las marcas · mismo día</small></div>
<div class="srv"><div class="ic">🔋</div><b>Batería</b><br><small>Originales con garantía</small></div>
<div class="srv"><div class="ic">💦</div><b>Placa / Agua</b><br><small>Diagnóstico gratis</small></div>
<div class="srv"><div class="ic">🔓</div><b>Liberación</b><br><small>Todas las operadoras</small></div>
</div>
@if(!empty($whatsappUrl))<a class="btn btn-p" style="width:100%;justify-content:center;margin-top:16px" href="{{ $whatsappUrl }}" target="_blank"><i class="fa-brands fa-whatsapp"></i> Cotizar mi reparación por WhatsApp</a>@endif
</div></div></section>
@if(!empty($productos) && $productos->count() > 0)
<section class="sec" style="padding-top:0"><div class="wrap"><div class="card">
<h3 style="margin:0 0 4px"><i class="fa-solid fa-bag-shopping" style="color:var(--cyan)"></i> Catálogo</h3>
<p style="color:var(--muted);font-size:13px;margin:0 0 16px">Stock real de la tienda · pide por WhatsApp</p>
<div class="grid4">
@foreach($productos as $prod)
<div class="prod"><b style="font-size:14px">{{ $prod->nombre }}</b>
<span class="price">{{ $config->simbolo_moneda ?? 'S/' }} {{ number_format($prod->precio_venta, 2) }}</span>
<span class="stock">● En stock ({{ $prod->stock }})</span>
@if(!empty($whatsappUrl))<a class="btn btn-wa btn-sm" style="justify-content:center;margin-top:6px;font-size:13px" target="_blank" href="https://wa.me/{{ preg_replace('/\D/', '', (string)($config->whatsapp ?? $config->telefono ?? '')) }}?text={{ urlencode('Hola, me interesa: ' . $prod->nombre . ' ¿Sigue disponible?') }}"><i class="fa-brands fa-whatsapp"></i> Pedir</a>@endif
</div>
@endforeach
</div></div></div></section>
@endif
<section class="sec" id="seguimiento" style="padding-top:0"><div class="wrap"><div class="card" style="text-align:center;background:linear-gradient(135deg,rgba(6,182,212,.12),rgba(59,130,246,.10)),var(--card)">
<h3 style="margin:0"><i class="fa-solid fa-magnifying-glass" style="color:var(--cyan)"></i> ¿Dejaste tu equipo en reparación?</h3>
<p style="color:var(--muted);font-size:13.5px">Ingresa tu código de boleta · ej: RPT-000002</p>
<form method="GET" action="{{ route('buscar.orden') }}" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
<input class="in" type="text" name="codigo" placeholder="RPT-000002" required>
<button class="btn btn-p" type="submit">Consultar estado</button>
</form>
<p style="color:var(--muted);font-size:12px">O escanea el QR de tu boleta 📷</p>
</div></div></section>
<section class="sec" style="padding-top:0"><div class="wrap"><div class="grid2">
<div>@if($cupones->isNotEmpty())@foreach($cupones as $cupon)<div class="cupon" style="margin-bottom:12px"><small style="color:var(--muted)">CUPÓN ACTIVO</small><h3 style="margin:4px 0">{{ $cupon->codigo }}</h3><b style="color:var(--emerald)">{{ $cupon->valor }}% de descuento</b><br><small style="color:var(--muted)">{{ $cupon->descripcion }}</small></div>@endforeach@endif</div>
<div class="card"><b><span class="stars">★★★★★</span> @if(isset($promedio) && $promedio){{ number_format($promedio,1) }} · @endifLo que dicen los clientes</b>
@foreach($resenas->take(4) as $r)<p style="font-size:13.5px;color:#e2e8f0;margin:10px 0"><span class="stars">{{ str_repeat('★', (int)$r->calificacion) }}</span> <b>{{ $r->nombre_cliente }}</b><br><span style="color:var(--muted)">{{ $r->comentario }}</span></p>@endforeach
<a class="btn btn-g" style="width:100%;justify-content:center" href="{{ route('public.resena.form', $tenant->slug_publico) }}"><i class="fa-solid fa-star"></i> Dejar mi reseña</a>
</div></div></div></section>
<section class="sec" style="padding-top:0"><div class="wrap"><div class="card" style="text-align:center">
<h3 style="margin:0"><i class="fa-solid fa-location-dot" style="color:var(--cyan)"></i> Visítanos</h3>
<p style="color:var(--muted);font-size:13.5px">@if($config->direccion){{ $config->direccion }} · @endif @if($config->horario_atencion){{ $config->horario_atencion }} · @endif @if($config->telefono){{ $config->telefono }}@endif</p>
@if($config->mapa_url)<div style="position:relative"><iframe src="{{ $config->mapa_url }}" width="100%" height="230" style="border:0;border-radius:12px" loading="lazy"></iframe><a class="btn btn-p btn-sm" style="position:absolute;top:12px;right:12px" target="_blank" href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($config->direccion ?? '') . ' ' . ($config->nombre_tienda ?? '')) }}"><i class="fa-solid fa-route"></i> Cómo llegar</a></div>@endif
</div></div></section>
<footer class="foot"><div class="wrap">© {{ date('Y') }} {{ $config->nombre_tienda ?? $tenant->empresa }} · Potenciado por LUITECH<br><small>luitech.fun/t/{{ $tenant->slug_publico }}</small></div></footer>
@if(!empty($whatsappUrl))<a class="wa-float" href="{{ $whatsappUrl }}" target="_blank">💬</a>@endif
</body></html>
