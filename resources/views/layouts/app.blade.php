<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM') — Tienda Celulares</title>

    <!-- ── PWA ─────────────────────────────────────────────────── -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    <meta name="theme-color" content="#1a0a3e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Taller CRM">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg:    #1a0a3e;
            --sidebar-width: 260px;
            --accent1:       #a855f7;
            --accent2:       #ec4899;
            --accent3:       #06b6d4;
            --gradient:      linear-gradient(135deg, var(--accent1), var(--accent2));
            --card-bg:       #ffffff;
            --page-bg:       #f4f0fb;
            --text-dark:     #1e1b4b;
            --text-muted:    #6b7280;
            --sidebar-text:  rgba(255,255,255,0.75);
            --sidebar-active:#ffffff;
            --nav-hover-bg:  rgba(168,85,247,0.2);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
            margin: 0;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ───────────────────────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1050;
            transition: transform .3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-brand .brand-logo {
            width: 42px; height: 42px;
            background: var(--gradient);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #fff;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-name {
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
        }

        .sidebar-brand .brand-sub {
            color: var(--accent1);
            font-size: 11px;
            font-weight: 400;
        }

        .user-profile {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .user-avatar {
            width: 40px; height: 40px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600; font-size: 16px;
            flex-shrink: 0;
        }

        .user-name { color: #fff; font-size: 13px; font-weight: 600; }
        .user-role { color: var(--accent1); font-size: 11px; }

        .sidebar-nav { padding: 12px 0; flex: 1; }

        .nav-section-title {
            color: rgba(255,255,255,.35);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 12px 20px 6px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 400;
            border-radius: 0;
            transition: all .2s;
            position: relative;
        }

        .sidebar-nav .nav-link:hover {
            background: var(--nav-hover-bg);
            color: #fff;
        }

        .sidebar-nav .nav-link.active {
            background: var(--gradient);
            color: var(--sidebar-active);
            font-weight: 600;
        }

        .sidebar-nav .nav-link .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link .badge-count {
            margin-left: auto;
            background: var(--accent2);
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        /* ── SIDEBAR OVERLAY (mobile) ──────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 1049;
        }
        .sidebar-overlay.show {
            display: block;
        }

        /* ── MAIN CONTENT ────────────────────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOPBAR ──────────────────────────────────────────────── */
        .topbar {
            background: #fff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar .search-box {
            flex: 1;
            max-width: 420px;
            position: relative;
        }

        .topbar .search-box input {
            width: 100%;
            padding: 8px 16px 8px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 24px;
            font-size: 13px;
            font-family: inherit;
            background: #f9fafb;
            transition: border-color .2s;
            outline: none;
        }

        .topbar .search-box input:focus {
            border-color: var(--accent1);
            background: #fff;
        }

        .topbar .search-box .search-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 13px;
        }

        .topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 10px; }

        .topbar-btn {
            width: 38px; height: 38px;
            border-radius: 50%;
            border: none;
            background: #f3f4f6;
            color: var(--text-muted);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all .2s;
            position: relative;
            text-decoration: none;
        }

        .topbar-btn:hover { background: var(--accent1); color: #fff; }

        .topbar-btn .notif-dot {
            position: absolute;
            top: 7px; right: 8px;
            width: 7px; height: 7px;
            background: var(--accent2);
            border-radius: 50%;
            border: 1.5px solid #fff;
        }

        .topbar .page-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        /* ── PAGE CONTENT ────────────────────────────────────────── */
        .page-content { padding: 24px 28px; flex: 1; }

        /* ── CARDS ───────────────────────────────────────────────── */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
        }

        .kpi-card {
            border-radius: 16px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::after {
            content: '';
            position: absolute;
            right: -20px; top: -20px;
            width: 100px; height: 100px;
            background: rgba(255,255,255,.1);
            border-radius: 50%;
        }

        .kpi-card .kpi-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .kpi-card .kpi-value {
            font-size: 26px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }

        .kpi-card .kpi-label {
            font-size: 12px;
            opacity: .85;
            margin-bottom: 8px;
        }

        .kpi-card .kpi-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,.2);
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 11px;
        }

        .bg-grad-purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }
        .bg-grad-pink   { background: linear-gradient(135deg, #ec4899, #db2777); }
        .bg-grad-cyan   { background: linear-gradient(135deg, #06b6d4, #0284c7); }
        .bg-grad-green  { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-grad-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }

        /* ── TABLE STYLES ────────────────────────────────────────── */
        .table { font-size: 13.5px; }
        .table thead th {
            background: #f8f5ff;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid #e9d5ff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .table tbody tr:hover { background: #fdf4ff; }

        /* ── BADGES ──────────────────────────────────────────────── */
        .badge-estado {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        /* ── BUTTONS ─────────────────────────────────────────────── */
        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 8px;
        }
        .btn-primary:hover { opacity: .9; filter: brightness(1.05); }

        .btn-outline-primary {
            border-color: var(--accent1);
            color: var(--accent1);
            border-radius: 8px;
        }
        .btn-outline-primary:hover {
            background: var(--accent1);
            color: #fff;
        }

        /* ── FORMS ───────────────────────────────────────────────── */
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #e5e7eb;
            font-size: 13.5px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent1);
            box-shadow: 0 0 0 3px rgba(168,85,247,.15);
        }

        .form-label { font-size: 13px; font-weight: 500; color: var(--text-dark); }

        /* ── ALERTS ──────────────────────────────────────────────── */
        .alert { border-radius: 10px; font-size: 13.5px; }

        /* ── PAGINATION ──────────────────────────────────────────── */
        .pagination .page-link {
            border-radius: 8px !important;
            margin: 0 2px;
            color: var(--accent1);
        }
        .pagination .page-item.active .page-link {
            background: var(--gradient);
            border-color: transparent;
        }

        /* ── SCROLLBAR ───────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }

        /* ═══════════════════════════════════════════════════════════════
           RESPONSIVE — Mobile First
           ═══════════════════════════════════════════════════════════════ */

        /* Tablets y menores */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .topbar {
                padding: 10px 12px;
                gap: 8px;
            }
            .page-content {
                padding: 16px 12px;
            }
            .topbar .search-box {
                max-width: 200px;
            }
            .topbar .search-box input {
                padding: 6px 12px 6px 32px;
                font-size: 12px;
            }
            .topbar .search-box .search-icon {
                left: 10px;
                font-size: 11px;
            }
            /* Tablas scroll horizontal en móvil */
            .table-responsive-custom {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .table-responsive-custom table {
                min-width: 600px;
            }
            /* Botones más grandes para touch */
            .btn, .btn-sm {
                padding: 8px 16px !important;
                font-size: 14px !important;
            }
            .topbar-btn {
                width: 42px;
                height: 42px;
            }
            /* Stats cards más compactas */
            .kpi-card {
                padding: 14px;
            }
            .kpi-card .kpi-value {
                font-size: 20px;
            }
            /* Ocultar búsqueda en móvil muy pequeño */
            .topbar .search-box {
                display: none;
            }
        }

        /* Teléfonos (< 576px) */
        @media (max-width: 575.98px) {
            .topbar {
                padding: 8px 10px;
                gap: 6px;
            }
            .page-content {
                padding: 12px 8px;
            }
            .topbar .page-title {
                font-size: 14px;
            }
            .topbar-actions .btn-primary span {
                display: none;
            }
            /* Cards full width */
            .card-body {
                padding: 14px !important;
            }
            /* KPIs en grid 2x2 más compacto */
            .kpi-card {
                padding: 12px;
            }
            .kpi-card .kpi-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
                margin-bottom: 8px;
            }
            .kpi-card .kpi-value {
                font-size: 18px;
            }
            .kpi-card .kpi-label {
                font-size: 11px;
            }
            /* Formularios: etiquetas arriba, inputs abajo */
            .row.g-3 > .col-md-3,
            .row.g-3 > .col-md-4,
            .row.g-3 > .col-md-6 {
                width: 100%;
            }
            /* Alertas más compactas */
            .alert {
                font-size: 12px;
                padding: 10px 12px;
            }
            /* Breadcrumb más pequeño */
            .breadcrumb {
                font-size: 12px !important;
            }
            /* Títulos de página más pequeños */
            h4.fw-bold {
                font-size: 16px !important;
            }
            /* Botones de acción en tabla: más pequeños y compactos */
            .table .btn-sm {
                padding: 4px 8px !important;
                font-size: 12px !important;
            }
            /* Paginación compacta */
            .pagination .page-link {
                padding: 4px 10px;
                font-size: 12px;
            }
            /* Tarjetas de estadísticas en 2 columnas */
            .col-6.col-md-3 {
                width: 50%;
            }
        }

        /* Sidebar overlay visible en móvil */
        @media (max-width: 991.98px) {
            .sidebar-overlay {
                display: none;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Utilidad para ocultar en móvil */
        @media (max-width: 575.98px) {
            .hide-mobile {
                display: none !important;
            }
        }

        /* Utilidad para mostrar solo en móvil */
        @media (min-width: 576px) {
            .show-mobile-only {
                display: none !important;
            }
        }

        /* ═══════════════════════════════════════════════════════════════
           🆕 MEJORAS RESPONSIVE ADICIONALES (Opción 5)
           ═══════════════════════════════════════════════════════════════ */

        /* ── Touch targets mejorados ── */
        @media (max-width: 575.98px) {
            .btn, 
            .btn-sm,
            .btn-xs,
            button,
            .form-control,
            .form-select,
            .nav-link,
            a {
                min-height: 44px;
            }
            input, select, textarea {
                font-size: 16px !important; /* Evita zoom en iOS */
            }
            .card-body {
                padding: 12px !important;
            }
            .gap-2 { gap: 8px !important; }
            .gap-3 { gap: 10px !important; }
            .gap-4 { gap: 12px !important; }
        }

        /* ── Filtros colapsables en móvil ── */
        .filtros-collapse {
            transition: max-height .3s ease;
        }
        @media (max-width: 575.98px) {
            .filtros-collapse {
                max-height: 0;
                overflow: hidden;
            }
            .filtros-collapse.show {
                max-height: 800px;
            }
            .filtros-toggle {
                cursor: pointer;
                user-select: none;
            }
        }

        /* ── Tarjetas para vista mobile (Index) ── */
        .mobile-card-rep {
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            transition: all .2s;
            border-left: 4px solid #e5e7eb;
        }
        .mobile-card-rep:active {
            transform: scale(0.98);
        }
        .mobile-card-rep .card-header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .mobile-card-rep .card-body-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mobile-card-rep .card-footer-actions {
            display: flex;
            gap: 6px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f3f4f6;
        }

        /* ── FAB (Floating Action Button) para móvil ── */
        .fab-mobile {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--gradient);
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(168,85,247,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            z-index: 999;
            transition: all .2s;
            text-decoration: none;
        }
        .fab-mobile:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(168,85,247,0.5);
            color: #fff;
        }
        @media (min-width: 576px) {
            .fab-mobile {
                display: none !important;
            }
        }

        /* ── Acordeón personalizado para móvil ── */
        .accordion-mobile .accordion-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px !important;
            margin-bottom: 8px;
            overflow: hidden;
        }
        .accordion-mobile .accordion-header {
            background: #f8f5ff;
            padding: 10px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            font-size: 13px;
            user-select: none;
        }
        .accordion-mobile .accordion-header i {
            transition: transform .2s;
        }
        .accordion-mobile .accordion-header.active i {
            transform: rotate(180deg);
        }
        .accordion-mobile .accordion-body {
            padding: 12px 14px;
            display: none;
        }
        .accordion-mobile .accordion-body.show {
            display: block;
        }
@media (min-width: 576px) {
            .accordion-mobile .accordion-body {
                display: block !important;
            }
            .accordion-mobile .accordion-header i {
                display: none !important;
            }
        }

        /* ── Botón de instalación PWA ── */
        .pwa-install-btn {
            position: fixed;
            bottom: 24px;
            right: 20px;
            z-index: 1000;
            background: var(--gradient);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            box-shadow: 0 4px 15px rgba(168,85,247,0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all .2s;
            min-height: 44px;
        }
        .pwa-install-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(168,85,247,0.5);
        }
        .pwa-install-btn.d-none {
            display: none !important;
        }
        /* En desktop también se muestra, posición arriba del FAB si existe */
        @media (min-width: 576px) {
            .pwa-install-btn {
                bottom: 24px;
                right: 20px;
            }
        }
        /* Icono del botón PWA */
        .pwa-install-btn i {
            font-size: 18px;
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- ══════════ SIDEBAR OVERLAY (mobile) ══════════ -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand d-flex align-items-center gap-3">
        @if(isset($empresa) && $empresa && $empresa->logo)
            <img src="{{ $empresa->logo_url ?? asset($empresa->logo) }}" alt="Logo" style="width:42px;height:42px;border-radius:12px;object-fit:contain;">
        @else
            <div class="brand-logo"><i class="fas fa-mobile-alt"></i></div>
        @endif
        <div>
            <div class="brand-name">{{ $empresa->nombre_tienda ?? 'CRM Celulares' }}</div>
            <div class="brand-sub">{{ $empresa->ruc ? 'RUC: '.$empresa->ruc : 'Panel de gestión' }}</div>
        </div>
    </div>

    <!-- User -->
    <div class="user-profile d-flex align-items-center gap-3">
        <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        <div>
            <div class="user-name">{{ Auth::user()->name }}</div>
            <div class="user-role">{{ ucfirst(Auth::user()->rol) }}</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-th-large"></i></span>
            Dashboard
        </a>

        <div class="nav-section-title">Gestión</div>

        <a href="{{ route('clientes.index') }}"
           class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-users"></i></span>
            Clientes
        </a>

        <a href="{{ route('proveedores.index') }}"
           class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-truck"></i></span>
            Proveedores
        </a>

        @if(Auth::user()->esAdmin())
        <a href="{{ route('compras.index') }}"
           class="nav-link {{ request()->routeIs('compras.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
            Órdenes de Compra
        </a>
        @endif

        <a href="{{ route('productos.index') }}"
           class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-box"></i></span>
            Inventario
        </a>

        @if(Auth::user()->esAdmin())
        <a href="{{ route('stock.bajo') }}"
           class="nav-link {{ request()->routeIs('stock.bajo') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-exclamation-triangle"></i></span>
            Alertas de Stock
            @php $stockBajoCount = \App\Models\Producto::whereColumn('stock','<=','stock_minimo')->count(); @endphp
            @if($stockBajoCount > 0)
                <span class="badge-count">{{ $stockBajoCount }}</span>
            @endif
        </a>

        <a href="{{ route('stock.movimientos') }}"
           class="nav-link {{ request()->routeIs('stock.movimientos') || request()->routeIs('stock.ajuste') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-exchange-alt"></i></span>
            Movimientos Stock
        </a>
        @endif

        @if(in_array(Auth::user()->rol, ['admin', 'vendedor']))
        <a href="{{ route('ventas.index') }}"
           class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-shopping-cart"></i></span>
            Ventas
        </a>
        @endif

        @if(in_array(Auth::user()->rol, ['admin', 'tecnico']))
        <a href="{{ route('reparaciones.index') }}"
           class="nav-link {{ request()->routeIs('reparaciones.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-tools"></i></span>
            Reparaciones
            @php $pendRep = \App\Models\Reparacion::where('estado','listo')->count(); @endphp
            @if($pendRep > 0)
                <span class="badge-count">{{ $pendRep }}</span>
            @endif
        </a>
        @endif

        @if(Auth::user()->esAdmin())
        <div class="nav-section-title">Reportes</div>

        <a href="{{ route('reportes.index') }}"
           class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
            Reportes
        </a>

        <a href="{{ route('comisiones.index') }}"
           class="nav-link {{ request()->routeIs('comisiones.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-money-bill-wave"></i></span>
            <span class="nav-text">Comisiones</span>
        </a>
        <a href="{{ route('gastos.index') }}"
           class="nav-link {{ request()->routeIs('gastos.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-receipt"></i></span>
            <span class="nav-text">Gastos Fijos</span>
        </a>

        <a href="{{ route('financiero.index') }}"
           class="nav-link {{ request()->routeIs('financiero.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            Estado Financiero
        </a>
        @endif

        <div class="nav-section-title">Sistema</div>

        @if(Auth::user()->esAdmin())
        <a href="{{ route('configuracion.index') }}"
           class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-cog"></i></span>
            Configuración
        </a>

        <a href="{{ route('backup.index') }}"
           class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}" onclick="closeSidebarMobile()">
            <span class="nav-icon"><i class="fas fa-database"></i></span>
            Backup & Restore
        </a>
        @endif

    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link w-100 border-0 bg-transparent"
                    style="color: var(--sidebar-text); text-align:left;">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>

<!-- ══════════ MAIN WRAPPER ══════════ -->
<div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
        <button class="topbar-btn d-md-none" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="search-box d-none d-md-block">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Buscar...">
        </div>

        <div class="topbar-actions">
            <a href="{{ route('ventas.create') }}" class="btn btn-sm btn-primary px-3" style="border-radius:20px;">
                <i class="fas fa-plus me-1"></i><span> Nueva Venta</span>
            </a>

            <button class="topbar-btn">
                <i class="fas fa-bell"></i>
                @php $stockBajoCount = \App\Models\Producto::whereColumn('stock','<=','stock_minimo')->count(); @endphp
                @if($stockBajoCount > 0)<span class="notif-dot"></span>@endif
            </button>

            <div class="dropdown">
                <button class="topbar-btn" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:12px; font-size:13px;">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2 text-muted"></i>Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Salir
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        {{-- Breadcrumb --}}
        @hasSection('breadcrumb')
        <nav class="mb-3" style="font-size:13px;">
            <ol class="breadcrumb mb-0" style="background:transparent; padding:0;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:var(--accent1);">Inicio</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
        @endif

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-check-circle"></i>
                <span class="flex-fill">{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('whatsapp_url'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fab fa-whatsapp" style="font-size:20px; color:#25D366;"></i>
                <span class="flex-fill">Notificación generada para enviar por WhatsApp al cliente.</span>
                <a href="{{ session('whatsapp_url') }}" target="_blank"
                   class="btn btn-sm ms-auto"
                   style="background:#25D366; color:#fff; border-radius:20px; white-space:nowrap;">
                    <i class="fab fa-whatsapp me-1"></i> Enviar
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span class="flex-fill">{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

{{-- FAB (Floating Action Button) para móvil - Nueva Orden --}}
@if(request()->routeIs('reparaciones.index'))
<a href="{{ route('reparaciones.create') }}" class="fab-mobile">
    <i class="fas fa-plus"></i>
</a>
@endif

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // ── Sidebar toggle para móvil ──────────────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    function closeSidebarMobile() {
        if (window.innerWidth < 992) {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
    }

    // Cerrar sidebar al hacer click fuera
    document.addEventListener('click', function(e) {
        var sidebar = document.getElementById('sidebar');
        var toggleBtn = document.querySelector('.topbar-btn.d-md-none');
        if (window.innerWidth < 992 && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && toggleBtn && !toggleBtn.contains(e.target)) {
                closeSidebarMobile();
            }
        }
    });

    // ── Acordeón para móvil ──
    document.addEventListener('click', function(e) {
        var header = e.target.closest('.accordion-mobile .accordion-header');
        if (header && window.innerWidth < 576) {
            var body = header.nextElementSibling;
            if (body) {
                body.classList.toggle('show');
                header.classList.toggle('active');
            }
        }
    });

    // ── Filtros colapsables en móvil ──
    document.addEventListener('click', function(e) {
        var toggle = e.target.closest('.filtros-toggle');
        if (toggle && window.innerWidth < 576) {
            var target = document.querySelector(toggle.dataset.target);
            if (target) {
                target.classList.toggle('show');
            }
        }
    });
</script>

@stack('scripts')

{{-- Botón de instalación PWA --}}
<button id="pwaInstallBtn" class="pwa-install-btn d-none">
    <i class="fas fa-download"></i>
    <span>Instalar App</span>
</button>

<script>
    // ── PWA: Registrar Service Worker ──────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('✅ Service Worker registrado:', registration.scope);
                })
                .catch(function(error) {
                    console.log('❌ Error al registrar Service Worker:', error);
                });
        });
    }

    // ── PWA: Botón de instalación ─────────────────────────────────
    let deferredPrompt = null;
    const pwaBtn = document.getElementById('pwaInstallBtn');

    // Detectar si ya está instalada como app (standalone)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                         window.navigator.standalone === true;

    // Función para mostrar el botón de instalación
    function showPwaInstallBtn() {
        if (pwaBtn && !isStandalone) {
            pwaBtn.classList.remove('d-none');
        }
    }

    // Función para ocultar el botón de instalación
    function hidePwaInstallBtn() {
        if (pwaBtn) pwaBtn.classList.add('d-none');
    }

    // En móvil (Android/Chrome), mostrar el botón siempre que sea instalable.
    // En iOS Safari no existe beforeinstallprompt, pero se muestra el botón
    // con instrucciones para "Agregar a pantalla de inicio".
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    // Si la app ya está instalada, ocultar el botón
    if (isStandalone) {
        hidePwaInstallBtn();
    } else if (isMobile) {
        // En móvil, siempre mostrar el botón (el SW ya está registrado)
        // El evento beforeinstallprompt puede tardar en dispararse
        setTimeout(showPwaInstallBtn, 3000);
    }

    // Evento beforeinstallprompt (Chrome, Edge, Android)
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevenir el mini-infobar automático del navegador
        e.preventDefault();
        // Guardar el evento para usarlo después
        deferredPrompt = e;
        // Mostrar el botón de instalación
        showPwaInstallBtn();
    });

    // Click en el botón de instalación
    if (pwaBtn) {
        pwaBtn.addEventListener('click', function() {
            if (deferredPrompt) {
                // Chrome/Android: mostrar diálogo nativo de instalación
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('✅ App instalada');
                        hidePwaInstallBtn();
                    } else {
                        console.log('❌ Instalación cancelada');
                    }
                    deferredPrompt = null;
                });
            } else {
                // No hay evento beforeinstallprompt disponible.
                // En Chrome Android, la instalación se hace desde el menú ⋮
                const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
                const isAndroidChrome = /Android/i.test(navigator.userAgent) && /Chrome|CriOS/i.test(navigator.userAgent);

                if (isIOS) {
                    alert('📱 Para instalar esta app:\n\n1. Toca el botón Compartir (⬆️)\n2. Desplázate y toca "Añadir a pantalla de inicio"\n3. Toca "Añadir"');
                } else if (isAndroidChrome) {
                    alert('📲 Para instalar esta app en Chrome:\n\n1. Toca el menú ⋮ (arriba a la derecha)\n2. Busca y toca "Instalar app" o "Agregar a pantalla de inicio"\n3. Confirma la instalación\n\nSi no aparece esa opción, asegúrate de:\n• Estar conectado por HTTPS (candado 🔒)\n• Haber visitado la página más de una vez\n• Borrar caché si ya lo intentaste antes');
                } else {
                    alert('📲 Para instalar esta app:\n\nUsa la opción "Instalar app" o "Agregar a pantalla de inicio" del menú del navegador.\n\nEn Chrome: Menú ⋮ → "Instalar app" o "Agregar a pantalla de inicio".');
                }
            }
        });
    }

    // Ocultar botón cuando la app ya esté instalada (display-mode: standalone)
    window.addEventListener('appinstalled', () => {
        hidePwaInstallBtn();
        deferredPrompt = null;
    });
</script>

</body>
</html>
