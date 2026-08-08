@extends('layouts.app')

@section('title', 'Manual de Ayuda')

@section('breadcrumb')
    <ul><li class="breadcrumb-item active">Manual de Ayuda</li></ul>
@endsection

@section('styles')
<style>
    /* ── Estilos del Manual de Ayuda ─────────────────────────────── */
    .ayuda-wrapper {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    /* ── Índice lateral ── */
    .ayuda-indice {
        width: 280px;
        flex-shrink: 0;
        position: sticky;
        top: 90px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        padding: 16px;
    }

    .ayuda-indice .indice-titulo {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        padding: 8px 12px;
        border-bottom: 2px solid #f3f0ff;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ayuda-indice .indice-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        transition: all .2s;
        cursor: pointer;
    }

    .ayuda-indice .indice-item:hover {
        background: #f8f5ff;
        color: var(--accent1);
    }

    .ayuda-indice .indice-item.active {
        background: var(--gradient);
        color: #fff;
        font-weight: 500;
    }

    .ayuda-indice .indice-item i {
        width: 18px;
        text-align: center;
        font-size: 14px;
    }

    /* ── Contenido principal ── */
    .ayuda-contenido {
        flex: 1;
        min-width: 0;
    }

    .ayuda-seccion {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        padding: 28px;
        margin-bottom: 24px;
        scroll-margin-top: 90px;
    }

    .ayuda-seccion .seccion-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f0ff;
    }

    .ayuda-seccion .seccion-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
        flex-shrink: 0;
    }

    .ayuda-seccion .seccion-titulo {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .ayuda-seccion .seccion-desc {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
    }

    /* ── Pasos ── */
    .paso {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        position: relative;
    }

    .paso:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 19px;
        top: 44px;
        bottom: -20px;
        width: 2px;
        background: #e9d5ff;
    }

    .paso-numero {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--gradient);
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(168,85,247,.3);
        z-index: 1;
    }

    .paso-contenido {
        flex: 1;
        padding-top: 4px;
    }

    .paso-contenido .paso-titulo {
        font-size: 14.5px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .paso-contenido .paso-texto {
        font-size: 13.5px;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 8px;
    }

    .paso-contenido .paso-texto strong {
        color: var(--text-dark);
    }

    /* ── Captura de pantalla placeholder ── */
    .captura-placeholder {
        background: linear-gradient(135deg, #f8f5ff, #fdf4ff);
        border: 2px dashed #d8b4fe;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        color: var(--accent1);
        font-size: 13px;
        margin: 12px 0;
        cursor: pointer;
        transition: all .2s;
    }

    .captura-placeholder:hover {
        background: linear-gradient(135deg, #f3e8ff, #fce7f3);
        border-color: var(--accent1);
    }

    .captura-placeholder i {
        font-size: 28px;
        margin-bottom: 8px;
        display: block;
    }

    .captura-placeholder .captura-texto {
        font-weight: 500;
    }

    .captura-placeholder .captura-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* ── Notas y consejos ── */
    .nota {
        background: #f0f9ff;
        border-left: 4px solid #0ea5e9;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #0c4a6e;
        margin: 12px 0;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .nota i { margin-top: 2px; }

    .consejo {
        background: #f0fdf4;
        border-left: 4px solid #22c55e;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #14532d;
        margin: 12px 0;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .consejo i { margin-top: 2px; }

    .advertencia {
        background: #fefce8;
        border-left: 4px solid #eab308;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #713f12;
        margin: 12px 0;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .advertencia i { margin-top: 2px; }

    /* ── Tabla de roles ── */
    .tabla-roles {
        width: 100%;
        font-size: 13px;
        border-collapse: separate;
        border-spacing: 0;
        margin: 12px 0;
    }

    .tabla-roles th {
        background: #f8f5ff;
        padding: 10px 14px;
        font-weight: 600;
        color: var(--text-dark);
        border-bottom: 2px solid #e9d5ff;
        text-align: left;
    }

    .tabla-roles td {
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        color: var(--text-muted);
    }

    .tabla-roles tr:last-child td { border-bottom: none; }

    /* ── Botón de volver arriba ── */
    .btn-volver-arriba {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--gradient);
        color: #fff;
        border: none;
        box-shadow: 0 4px 15px rgba(168,85,247,.4);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all .3s;
    }

    .btn-volver-arriba.visible {
        opacity: 1;
        visibility: visible;
    }

    /* ── Responsive ── */
    @media (max-width: 991.98px) {
        .ayuda-wrapper {
            flex-direction: column;
        }
        .ayuda-indice {
            width: 100%;
            position: static;
            max-height: none;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 12px;
        }
        .ayuda-indice .indice-titulo {
            width: 100%;
        }
        .ayuda-indice .indice-item {
            font-size: 12px;
            padding: 6px 10px;
        }
        .ayuda-seccion {
            padding: 20px;
        }
    }

    @media (max-width: 575.98px) {
        .ayuda-seccion {
            padding: 16px;
        }
        .ayuda-seccion .seccion-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .ayuda-seccion .seccion-titulo {
            font-size: 17px;
        }
        .paso {
            gap: 12px;
        }
        .paso-numero {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }
        .paso:not(:last-child)::after {
            left: 16px;
        }
    }
</style>
@endsection

@section('content')
<div class="ayuda-wrapper">

    {{-- ══════════ ÍNDICE LATERAL ══════════ --}}
    <aside class="ayuda-indice">
        <div class="indice-titulo">
            <i class="fas fa-book-open"></i>
            Índice del Manual
        </div>

        <a class="indice-item" data-target="inicio">
            <i class="fas fa-home"></i> Inicio
        </a>
        <a class="indice-item" data-target="dashboard">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a class="indice-item" data-target="clientes">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a class="indice-item" data-target="proveedores">
            <i class="fas fa-truck"></i> Proveedores
        </a>
        <a class="indice-item" data-target="compras">
            <i class="fas fa-clipboard-list"></i> Órdenes de Compra
        </a>
        <a class="indice-item" data-target="inventario">
            <i class="fas fa-box"></i> Inventario
        </a>
        <a class="indice-item" data-target="stock">
            <i class="fas fa-exchange-alt"></i> Movimientos de Stock
        </a>
        <a class="indice-item" data-target="ventas">
            <i class="fas fa-shopping-cart"></i> Ventas
        </a>
        <a class="indice-item" data-target="devoluciones">
            <i class="fas fa-undo-alt"></i> Devoluciones
        </a>
        <a class="indice-item" data-target="reparaciones">
            <i class="fas fa-tools"></i> Reparaciones
        </a>
        <a class="indice-item" data-target="reportes">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
        <a class="indice-item" data-target="financiero">
            <i class="fas fa-chart-line"></i> Estado Financiero
        </a>
        <a class="indice-item" data-target="comisiones">
            <i class="fas fa-money-bill-wave"></i> Comisiones
        </a>
        <a class="indice-item" data-target="gastos">
            <i class="fas fa-receipt"></i> Gastos Fijos
        </a>
        <a class="indice-item" data-target="configuracion">
            <i class="fas fa-cog"></i> Configuración
        </a>
        <a class="indice-item" data-target="facturacion-electronica">
            <i class="fas fa-file-invoice"></i> Facturación Electrónica
        </a>
        <a class="indice-item" data-target="mercadopago">
            <i class="fab fa-mercadopago"></i> Mercado Pago
        </a>
        <a class="indice-item" data-target="pagina-publica">
            <i class="fas fa-globe"></i> Página Pública (Mini Web)
        </a>
        <a class="indice-item" data-target="backup">
            <i class="fas fa-database"></i> Backup & Restore
        </a>
        <a class="indice-item" data-target="pwa">
            <i class="fas fa-mobile-alt"></i> Instalar App
        </a>
    </aside>

    {{-- ══════════ CONTENIDO PRINCIPAL ══════════ --}}
    <div class="ayuda-contenido">

        {{-- ── INICIO ── --}}
        <section class="ayuda-seccion" id="inicio">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:var(--gradient);">
                    <i class="fas fa-home"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Bienvenido al Manual de Ayuda</h2>
                    <p class="seccion-desc">Guía completa para el uso de tu sistema de gestión</p>
                </div>
            </div>

            <p class="paso-texto" style="font-size:14px; line-height:1.7; color:var(--text-muted);">
                Este manual te guiará paso a paso por cada módulo de la aplicación.
                Utiliza el <strong>índice lateral</strong> para navegar rápidamente entre las secciones.
            </p>

            <div class="consejo">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Consejo:</strong> Si eres nuevo en la aplicación, te recomendamos seguir el orden de las secciones:
                    primero <strong>Configuración</strong> para personalizar tu empresa, luego <strong>Clientes</strong>,
                    <strong>Inventario</strong>, y finalmente <strong>Ventas</strong> y <strong>Reparaciones</strong>.
                </div>
            </div>

            <h5 class="mt-4 mb-3" style="font-weight:600; color:var(--text-dark);">Roles y permisos</h5>
            <table class="tabla-roles">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Acceso principal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Administrador</strong></td>
                        <td>Todos los módulos: ventas, reparaciones, inventario, reportes, configuración, backup, etc.</td>
                    </tr>
                    <tr>
                        <td><strong>Vendedor</strong></td>
                        <td>Clientes, ventas, devoluciones y consulta de inventario.</td>
                    </tr>
                    <tr>
                        <td><strong>Técnico</strong></td>
                        <td>Reparaciones, clientes y consulta de inventario.</td>
                    </tr>
                </tbody>
            </table>
        </section>

        {{-- ── DASHBOARD ── --}}
        <section class="ayuda-seccion" id="dashboard">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#a855f7,#7c3aed);">
                    <i class="fas fa-th-large"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Dashboard</h2>
                    <p class="seccion-desc">Resumen general de tu negocio</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Accede al Dashboard</div>
                    <p class="paso-texto">
                        Al iniciar sesión, verás el <strong>Dashboard</strong> con indicadores clave:
                        ventas del día, reparaciones pendientes, productos con stock bajo y más.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura del Dashboard')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura del Dashboard</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Interpreta los indicadores</div>
                    <p class="paso-texto">
                        Cada tarjeta muestra una métrica importante:
                        <strong>Ventas del día</strong>, <strong>Reparaciones en curso</strong>,
                        <strong>Productos con stock bajo</strong> y <strong>Clientes registrados</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Gráfico de ventas semanales</div>
                    <p class="paso-texto">
                        El gráfico muestra el <strong>total de ventas de los últimos 7 días</strong>.
                        Pasa el cursor sobre las barras para ver los valores exactos.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>El Dashboard se actualiza automáticamente con cada venta, reparación o cambio de stock.</div>
            </div>
        </section>

        {{-- ── CLIENTES ── --}}
        <section class="ayuda-seccion" id="clientes">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#06b6d4,#0284c7);">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Clientes</h2>
                    <p class="seccion-desc">Gestión de tu cartera de clientes</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ir a Clientes</div>
                    <p class="paso-texto">
                        En el menú lateral, haz clic en <strong>Clientes</strong> para ver la lista completa.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura de Clientes')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura de la lista de Clientes</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Registrar un nuevo cliente</div>
                    <p class="paso-texto">
                        Haz clic en el botón <strong>"Nuevo Cliente"</strong> (esquina superior derecha).
                        Completa los campos: <strong>nombre, teléfono, email, dirección</strong> y otros datos.
                        Luego presiona <strong>"Guardar"</strong>.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura del formulario de Cliente')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura del formulario de Cliente</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Buscar y editar clientes</div>
                    <p class="paso-texto">
                        Usa el <strong>buscador</strong> para encontrar clientes por nombre o teléfono.
                        Haz clic en el icono <i class="fas fa-eye text-primary"></i> para ver detalles,
                        o <i class="fas fa-edit text-warning"></i> para editar la información.
                    </p>
                </div>
            </div>

            <div class="consejo">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Consejo:</strong> Registra el teléfono con el formato correcto para poder
                    enviar notificaciones por <strong>WhatsApp</strong> desde las ventas y reparaciones.
                </div>
            </div>
        </section>

        {{-- ── PROVEEDORES ── --}}
        <section class="ayuda-seccion" id="proveedores">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Proveedores</h2>
                    <p class="seccion-desc">Gestiona tus proveedores de productos</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ir a Proveedores</div>
                    <p class="paso-texto">
                        En el menú lateral, haz clic en <strong>Proveedores</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Registrar un proveedor</div>
                    <p class="paso-texto">
                        Haz clic en <strong>"Nuevo Proveedor"</strong>. Completa los datos:
                        <strong>nombre, RUC, teléfono, email, dirección</strong> y estado (activo/inactivo).
                        Presiona <strong>"Guardar"</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Activar o desactivar proveedores</div>
                    <p class="paso-texto">
                        Usa el botón de <strong>activar/desactivar</strong> para habilitar o inhabilitar
                        un proveedor sin eliminarlo. Los proveedores inactivos no aparecerán en las
                        órdenes de compra.
                    </p>
                </div>
            </div>
        </section>

        {{-- ── ÓRDENES DE COMPRA ── --}}
        <section class="ayuda-seccion" id="compras">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Órdenes de Compra</h2>
                    <p class="seccion-desc">Registra compras a proveedores y actualiza tu inventario</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Crear una orden de compra</div>
                    <p class="paso-texto">
                        Ve a <strong>Órdenes de Compra</strong> y haz clic en <strong>"Nueva Compra"</strong>.
                        Selecciona el <strong>proveedor</strong> y la <strong>fecha</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Agregar productos</div>
                    <p class="paso-texto">
                        Busca y selecciona los <strong>productos</strong> que estás comprando.
                        Indica la <strong>cantidad</strong> y el <strong>precio de compra</strong> de cada uno.
                        El sistema calculará el <strong>total automáticamente</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Confirmar la compra</div>
                    <p class="paso-texto">
                        Revisa los datos y presiona <strong>"Guardar"</strong>.
                        Al confirmar, el <strong>stock de los productos se actualizará automáticamente</strong>.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>Las órdenes de compra solo están disponibles para el rol <strong>Administrador</strong>.</div>
            </div>
        </section>

        {{-- ── INVENTARIO ── --}}
        <section class="ayuda-seccion" id="inventario">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Inventario</h2>
                    <p class="seccion-desc">Gestión de productos, marcas y categorías</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ver el inventario</div>
                    <p class="paso-texto">
                        En el menú, haz clic en <strong>Inventario</strong> para ver todos tus productos
                        con su <strong>stock, precio de venta y estado</strong>.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura del Inventario')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura del Inventario</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Registrar un nuevo producto</div>
                    <p class="paso-texto">
                        Haz clic en <strong>"Nuevo Producto"</strong>. Completa:
                        <strong>nombre, código, marca, categoría, precio de compra, precio de venta,
                        stock inicial y stock mínimo</strong>. Presiona <strong>"Guardar"</strong>.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura del formulario de Producto')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura del formulario de Producto</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Crear marcas y categorías</div>
                    <p class="paso-texto">
                        Al registrar un producto, puedes <strong>crear una nueva marca o categoría</strong>
                        directamente desde el formulario si no existe en la lista.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">4</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Importar y exportar productos</div>
                    <p class="paso-texto">
                        Usa <strong>Exportar</strong> para descargar tu inventario en <strong>Excel (CSV)</strong> o <strong>PDF</strong>.
                        Usa <strong>Importar</strong> para cargar productos desde un archivo CSV
                        (descarga primero la <strong>plantilla</strong> para ver el formato correcto).
                    </p>
                </div>
            </div>

            <div class="advertencia">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Importante:</strong> El <strong>stock mínimo</strong> es el nivel de alerta.
                    Cuando el stock de un producto baje de ese nivel, aparecerá en <strong>Alertas de Stock</strong>.
                </div>
            </div>
        </section>

        {{-- ── MOVIMIENTOS DE STOCK ── --}}
        <section class="ayuda-seccion" id="stock">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#0ea5e9,#0369a1);">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Movimientos de Stock</h2>
                    <p class="seccion-desc">Controla entradas, salidas y ajustes de inventario</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ver movimientos</div>
                    <p class="paso-texto">
                        Ve a <strong>Movimientos Stock</strong> para ver el historial completo de
                        <strong>entradas, salidas y ajustes</strong> de cada producto.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Realizar un ajuste de stock</div>
                    <p class="paso-texto">
                        Haz clic en <strong>"Ajuste de Stock"</strong>. Selecciona el <strong>producto</strong>,
                        el <strong>tipo de ajuste</strong> (entrada o salida), la <strong>cantidad</strong>
                        y el <strong>motivo</strong>. Presiona <strong>"Guardar"</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Alertas de stock bajo</div>
                    <p class="paso-texto">
                        En <strong>Alertas de Stock</strong> verás los productos que están por debajo
                        de su <strong>stock mínimo</strong>. Puedes enviar una <strong>notificación por WhatsApp</strong>
                        al proveedor para reponer el inventario.
                    </p>
                </div>
            </div>
        </section>

        {{-- ── VENTAS ── --}}
        <section class="ayuda-seccion" id="ventas">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#ec4899,#db2777);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Ventas</h2>
                    <p class="seccion-desc">Registra ventas, genera tickets y envía por WhatsApp</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Crear una nueva venta</div>
                    <p class="paso-texto">
                        Haz clic en <strong>"Nueva Venta"</strong> (botón en la barra superior)
                        o ve a <strong>Ventas → Nueva Venta</strong>.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura del formulario de Venta')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura del formulario de Venta</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Seleccionar cliente y productos</div>
                    <p class="paso-texto">
                        Selecciona el <strong>cliente</strong> (o crea uno nuevo).
                        Busca los <strong>productos</strong> por nombre o código y agrégalos al carrito.
                        El sistema calculará el <strong>total automáticamente</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Completar la venta</div>
                    <p class="paso-texto">
                        Revisa el total, selecciona el <strong>método de pago</strong> y presiona
                        <strong>"Registrar Venta"</strong>. El stock se descontará automáticamente.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">4</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ticket y WhatsApp</div>
                    <p class="paso-texto">
                        Después de registrar la venta, puedes:
                        <br>• <i class="fas fa-print text-primary"></i> <strong>Imprimir ticket</strong> para entregar al cliente.
                        <br>• <i class="fab fa-whatsapp text-success"></i> <strong>Enviar por WhatsApp</strong> el comprobante de venta.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">5</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Cancelar una venta</div>
                    <p class="paso-texto">
                        Si necesitas cancelar una venta, ve a la lista de <strong>Ventas</strong>,
                        busca la venta y haz clic en <i class="fas fa-times-circle text-danger"></i>
                        <strong>Cancelar</strong>. El stock se repondrá automáticamente.
                    </p>
                </div>
            </div>

            <div class="advertencia">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Importante:</strong> Solo el <strong>Administrador</strong> puede cancelar ventas.
                    Los vendedores pueden registrar ventas pero no cancelarlas.
                </div>
            </div>
        </section>

        {{-- ── DEVOLUCIONES ── --}}
        <section class="ayuda-seccion" id="devoluciones">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#f43f5e,#e11d48);">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Devoluciones</h2>
                    <p class="seccion-desc">Gestiona devoluciones de productos vendidos</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ir a Devoluciones</div>
                    <p class="paso-texto">
                        En el menú lateral, haz clic en <strong>Devoluciones</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Registrar una devolución</div>
                    <p class="paso-texto">
                        Haz clic en <strong>"Nueva Devolución"</strong>.
                        Selecciona la <strong>venta original</strong> y el sistema cargará automáticamente
                        los productos vendidos. Indica qué productos se devuelven y el <strong>motivo</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Confirmar la devolución</div>
                    <p class="paso-texto">
                        Revisa los datos y presiona <strong>"Guardar"</strong>.
                        El <strong>stock se repondrá automáticamente</strong> con los productos devueltos.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>Las devoluciones solo se pueden registrar sobre <strong>ventas completadas</strong>.</div>
            </div>
        </section>

        {{-- ── REPARACIONES ── --}}
        <section class="ayuda-seccion" id="reparaciones">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Reparaciones</h2>
                    <p class="seccion-desc">Gestiona órdenes de reparación de principio a fin</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Crear una orden de reparación</div>
                    <p class="paso-texto">
                        Ve a <strong>Reparaciones</strong> y haz clic en <strong>"Nueva Reparación"</strong>.
                        Selecciona el <strong>cliente</strong>, el <strong>técnico</strong> asignado,
                        el <strong>equipo</strong> (marca, modelo, IMEI) y describe el <strong>problema reportado</strong>.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura del formulario de Reparación')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura del formulario de Reparación</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Agregar fotos y firma</div>
                    <p class="paso-texto">
                        En la orden de reparación puedes:
                        <br>• <i class="fas fa-camera text-primary"></i> <strong>Subir fotos</strong> del equipo (estado, daños).
                        <br>• <i class="fas fa-pen text-success"></i> <strong>Capturar la firma</strong> del cliente como autorización.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Avanzar el estado de la reparación</div>
                    <p class="paso-texto">
                        A medida que avanza el trabajo, actualiza el estado:
                        <strong>Recibido → En Proceso → Listo → Entregado</strong>.
                        También puedes registrar el <strong>costo de la reparación</strong> y los <strong>repuestos usados</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">4</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ticket y WhatsApp</div>
                    <p class="paso-texto">
                        Cuando la reparación esté <strong>lista</strong>, puedes:
                        <br>• <i class="fas fa-print text-primary"></i> <strong>Imprimir ticket</strong> de entrega.
                        <br>• <i class="fab fa-whatsapp text-success"></i> <strong>Enviar por WhatsApp</strong> la notificación al cliente.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">5</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">QR de seguimiento</div>
                    <p class="paso-texto">
                        Cada orden de reparación genera un <strong>código QR</strong> que el cliente puede
                        escanear para ver el <strong>estado de su reparación</strong> sin necesidad de iniciar sesión.
                    </p>
                </div>
            </div>

            <div class="consejo">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Consejo:</strong> Usa las <strong>fotos</strong> para documentar el estado del equipo
                    al recibirlo. Esto protege tu negocio ante reclamos de daños preexistentes.
                </div>
            </div>
        </section>

        {{-- ── REPORTES ── --}}
        <section class="ayuda-seccion" id="reportes">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#14b8a6,#0d9488);">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Reportes</h2>
                    <p class="seccion-desc">Analiza el rendimiento de tu negocio</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Acceder a Reportes</div>
                    <p class="paso-texto">
                        En el menú, haz clic en <strong>Reportes</strong> (solo Administrador).
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Tipos de reportes disponibles</div>
                    <p class="paso-texto">
                        <strong>Ventas por período:</strong> total de ventas entre fechas.
                        <br><strong>Productos más vendidos:</strong> ranking de productos.
                        <br><strong>Reparaciones:</strong> cantidad y estado de reparaciones.
                        <br><strong>Clientes frecuentes:</strong> clientes con más compras.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Filtrar y exportar</div>
                    <p class="paso-texto">
                        Usa los <strong>filtros de fecha</strong> para acotar los resultados.
                        Puedes <strong>exportar</strong> los reportes a <strong>Excel o PDF</strong>
                        para compartirlos o archivarlos.
                    </p>
                </div>
            </div>
        </section>

        {{-- ── ESTADO FINANCIERO ── --}}
        <section class="ayuda-seccion" id="financiero">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Estado Financiero</h2>
                    <p class="seccion-desc">Controla la salud financiera de tu negocio</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Acceder al módulo financiero</div>
                    <p class="paso-texto">
                        En el menú, haz clic en <strong>Estado Financiero</strong> (solo Administrador).
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Reportes financieros disponibles</div>
                    <p class="paso-texto">
                        <strong>Estado de Resultados:</strong> ingresos, costos y utilidad del período.
                        <br><strong>Balance General:</strong> activos, pasivos y patrimonio.
                        <br><strong>Flujo de Caja:</strong> entradas y salidas de efectivo.
                        <br><strong>Indicadores:</strong> márgenes, rentabilidad y eficiencia.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Interpretar los datos</div>
                    <p class="paso-texto">
                        Cada reporte se genera automáticamente con los datos de tus
                        <strong>ventas, compras, gastos y comisiones</strong>.
                        Usa los <strong>filtros de fecha</strong> para analizar períodos específicos.
                    </p>
                </div>
            </div>
        </section>

        {{-- ── COMISIONES ── --}}
        <section class="ayuda-seccion" id="comisiones">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#22c55e,#15803d);">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Comisiones de Técnicos</h2>
                    <p class="seccion-desc">Gestiona las comisiones por reparaciones</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ver comisiones generadas</div>
                    <p class="paso-texto">
                        Ve a <strong>Comisiones</strong> para ver las comisiones generadas por cada
                        <strong>técnico</strong> según las reparaciones completadas.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Registrar pago de comisión</div>
                    <p class="paso-texto">
                        Cuando pagues una comisión a un técnico, haz clic en
                        <strong>"Pagar"</strong> junto a la reparación, o
                        <strong>"Pagar Todo"</strong> para liquidar todas las comisiones pendientes de un técnico.
                    </p>
                </div>
            </div>
        </section>

        {{-- ── GASTOS FIJOS ── --}}
        <section class="ayuda-seccion" id="gastos">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Gastos Fijos</h2>
                    <p class="seccion-desc">Registra y controla tus gastos recurrentes</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Registrar un gasto fijo</div>
                    <p class="paso-texto">
                        Ve a <strong>Gastos Fijos</strong> y haz clic en <strong>"Nuevo Gasto"</strong>.
                        Completa: <strong>descripción, monto, categoría</strong> y frecuencia (mensual, semanal, etc.).
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Activar o desactivar gastos</div>
                    <p class="paso-texto">
                        Usa el botón de <strong>activar/desactivar</strong> para incluir o excluir
                        un gasto de los cálculos financieros sin eliminarlo.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>Los gastos fijos se incluyen automáticamente en el <strong>Estado de Resultados</strong> y el <strong>Flujo de Caja</strong>.</div>
            </div>
        </section>

        {{-- ── CONFIGURACIÓN ── --}}
        <section class="ayuda-seccion" id="configuracion">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#64748b,#475569);">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Configuración</h2>
                    <p class="seccion-desc">Personaliza tu empresa y gestiona usuarios</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Datos de la empresa</div>
                    <p class="paso-texto">
                        Ve a <strong>Configuración</strong> y completa los datos de tu empresa:
                        <strong>nombre, RUC, teléfono, dirección, logo</strong> y otros datos.
                        Estos datos aparecerán en los <strong>tickets y reportes</strong>.
                    </p>
                    <button type="button" class="captura-placeholder" onclick="alert('Aquí se insertará la captura de Configuración')">
                        <i class="fas fa-camera"></i>
                        <div class="captura-texto">📸 Captura de Configuración</div>
                        <div class="captura-sub">Haz clic para reemplazar con tu captura</div>
                    </button>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Zona horaria</div>
                    <p class="paso-texto">
                        Selecciona tu <strong>zona horaria</strong> para que las fechas y horas
                        de ventas, reparaciones y reportes se muestren correctamente.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Gestionar usuarios</div>
                    <p class="paso-texto">
                        En la sección de <strong>Usuarios</strong> puedes:
                        <br>• <i class="fas fa-user-plus text-primary"></i> <strong>Crear usuarios</strong> con su rol (admin, vendedor, técnico).
                        <br>• <i class="fas fa-edit text-warning"></i> <strong>Editar</strong> nombre, email o rol.
                        <br>• <i class="fas fa-toggle-on text-success"></i> <strong>Activar/desactivar</strong> el acceso de un usuario.
                        <br>• <i class="fas fa-key text-danger"></i> <strong>Cambiar contraseña</strong>.
                    </p>
                </div>
            </div>

            <div class="advertencia">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Importante:</strong> Solo el <strong>Administrador</strong> puede acceder a Configuración.
                    Asigna roles con cuidado: los <strong>vendedores</strong> no pueden ver reportes financieros
                    ni configuración.
                </div>
            </div>
        </section>

        {{-- ── FACTURACIÓN ELECTRÓNICA ── --}}
        <section class="ayuda-seccion" id="facturacion-electronica">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Facturación Electrónica</h2>
                    <p class="seccion-desc">Emite DTE (Chile) o Facturas DIAN (Colombia) automáticamente</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Activar la facturación electrónica</div>
                    <p class="paso-texto">
                        Ve a <strong>Configuración → Empresa</strong> y activa el interruptor
                        <strong>"Facturación Electrónica"</strong>. Al activarlo, se mostrarán los campos
                        necesarios para configurar tu empresa.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Completar los datos de la empresa</div>
                    <p class="paso-texto">
                        Completa los siguientes campos:
                        <br>• <strong>RUT Emisor</strong> - tu RUT de empresa (ej: 76.123.456-7).
                        <br>• <strong>Razón Social</strong> - el nombre legal de tu empresa.
                        <br>• <strong>Giro</strong> - tu giro comercial (ej: "Venta de celulares").
                        <br>• <strong>Comuna / Ciudad</strong> - tu comuna (ej: "Santiago").
                        <br>• <strong>Proveedor DTE</strong> - Acepta, Fove o Tundra.
                        <br>• <strong>Certificado Digital (.pfx)</strong> - el archivo que te emitió el SII.
                        <br>• <strong>Contraseña del certificado</strong> - la clave que protege tu archivo .pfx.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Obtener el certificado digital</div>
                    <p class="paso-texto">
                        El certificado digital <strong>no se obtiene dentro del sistema</strong>. Debes obtenerlo:
                        <br>• <strong>Chile:</strong> En el SII (siiclave.cl) o en proveedores como Certisur o Acepta.
                        <br>• <strong>Colombia:</strong> En la DIAN o en entidades de certificación como Certicámara.
                        <br><br>El certificado es un archivo <strong>.pfx</strong> o <strong>.p12</strong> que funciona
                        como la firma digital de tu empresa.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">4</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Contratar un proveedor DTE (Chile)</div>
                    <p class="paso-texto">
                        Para Chile, necesitas un <strong>proveedor DTE autorizado</strong> que envíe tus facturas al SII:
                        <br>• <strong>Acepta</strong> (acepta.cl) - recomendado para PyMEs.
                        <br>• <strong>Fove</strong> (fove.cl).
                        <br>• <strong>Tundra</strong> (tundra.cl).
                        <br><br>Estos proveedores se encargan de firmar el XML, enviarlo al SII y generar el PDF con sello.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">5</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Cómo funciona al registrar ventas</div>
                    <p class="paso-texto">
                        Cuando la facturación electrónica está <strong>activada</strong>, cada venta registrada
                        intentará emitir el documento electrónico automáticamente:
                        <br>• <strong>Chile:</strong> Factura (tipo 33) si hay cliente, o Boleta (tipo 39) si es venta general.
                        <br>• <strong>Colombia:</strong> Factura Electrónica de Venta (FEV) con CUFE.
                        <br><br>Si la facturación está <strong>desactivada</strong>, las ventas se registran normalmente
                        sin emitir documentos electrónicos.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>
                    Cada empresa puede <strong>activar o desactivar</strong> la facturación electrónica cuando quiera.
                    Las empresas que no la activan no necesitan certificado digital ni proveedor DTE.
                </div>
            </div>
        </section>

        {{-- ── MERCADO PAGO ── --}}
        <section class="ayuda-seccion" id="mercadopago">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#00b1ea,#0090c0);">
                    <i class="fab fa-mercadopago"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Mercado Pago</h2>
                    <p class="seccion-desc">Cobra con QR y la boleta se envía al SII automáticamente</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Activar Mercado Pago</div>
                    <p class="paso-texto">
                        Ve a <strong>Configuración → Empresa</strong> y activa el interruptor
                        <strong>"Mercado Pago"</strong>. Al activarlo, se mostrarán los campos
                        para ingresar tus credenciales.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Obtener tus credenciales</div>
                    <p class="paso-texto">
                        Para obtener tus credenciales:
                        <br>1. Crea una cuenta en <strong>Mercado Pago Developers</strong> (developers.mercadopago.com).
                        <br>2. Crea una aplicación.
                        <br>3. Copia tu <strong>Public Key</strong> y tu <strong>Access Token</strong>.
                        <br><br>Pégalos en los campos correspondientes en Configuración.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Cobrar con QR</div>
                    <p class="paso-texto">
                        Cuando Mercado Pago está <strong>activado</strong>, en la vista de cada venta
                        aparecerá un botón <strong>"Pagar con Mercado Pago"</strong>.
                        Al hacer clic, se genera un <strong>código QR</strong> que el cliente puede escanear
                        con su celular para pagar.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">4</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Boleta al SII automática</div>
                    <p class="paso-texto">
                        Cuando el cliente paga con Mercado Pago, la <strong>boleta se envía al SII automáticamente</strong>
                        (si tu plan de Mercado Pago lo incluye). El sistema recibe la confirmación del pago
                        y marca la venta como <strong>"pagada"</strong>.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>
                    La boleta que emite Mercado Pago sale con el <strong>RUT de tu empresa</strong> que configuraste
                    en Mercado Pago. No necesitas certificado digital ni proveedor DTE para estas boletas.
                </div>
            </div>
        </section>

        {{-- ── PÁGINA PÚBLICA (MINI WEB) ── --}}
        <section class="ayuda-seccion" id="pagina-publica">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#4f46e5,#3730a3);">
                    <i class="fas fa-globe"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Página Pública (Mini Web)</h2>
                    <p class="seccion-desc">Tu tienda en línea para mostrar información, reseñas y cupones</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Activar tu página pública</div>
                    <p class="paso-texto">
                        Ve a <strong>Configuración → Publicidad y Página Pública</strong>.
                        Activa la opción <strong>"Activar página pública"</strong> y guarda los cambios.
                        Tu página estará disponible en una URL tipo: <strong>https://tudominio.com/t/tu-tienda</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Personalizar tu página</div>
                    <p class="paso-texto">
                        En la misma sección puedes configurar:
                        <br>• <strong>Descripción corta</strong> - texto que aparece debajo del nombre de tu tienda.
                        <br>• <strong>Horario de atención</strong> - ej: "Lun-Vie 9am-7pm".
                        <br>• <strong>Redes sociales</strong> - Instagram, Facebook, TikTok.
                        <br>• <strong>Ubicación en Google Maps</strong> - pega el link de tu ubicación.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Agregar tu ubicación en el mapa</div>
                    <p class="paso-texto">
                        Para que tu página muestre un <strong>mapa interactivo</strong>:
                        <br>1. Abre <strong>Google Maps</strong> y busca tu dirección.
                        <br>2. Haz clic en <strong>"Compartir"</strong> → <strong>"Copiar enlace"</strong>.
                        <br>3. Pega el enlace en el campo <strong>"Ubicación en Google Maps"</strong> en Configuración.
                        <br>4. Tu página mostrará el mapa con un botón <strong>"Cómo llegar"</strong>.
                        <br><br>💡 <strong>Alternativa:</strong> Si solo tienes la dirección configurada, el mapa se genera automáticamente.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">4</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Ver tu página pública</div>
                    <p class="paso-texto">
                        En Configuración verás un botón <strong>"Ver mi página pública"</strong>.
                        Desde ahí puedes ver tu página y compartir el enlace con tus clientes.
                        Tu página muestra:
                        <br>• <strong>Información</strong> - dirección, teléfono, horario, email.
                        <br>• <strong>Mapa</strong> - tu ubicación con botón "Cómo llegar".
                        <br>• <strong>Reseñas</strong> - opiniones de tus clientes.
                        <br>• <strong>Seguimiento</strong> - los clientes pueden ver el estado de su reparación.
                        <br>• <strong>Cupones activos</strong> - descuentos disponibles.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">5</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Cupones de descuento automáticos</div>
                    <p class="paso-texto">
                        En <strong>Configuración → Publicidad</strong> puedes activar:
                        <br>• <strong>Generar cupón al entregar</strong> - se crea un cupón automáticamente cuando entregas una reparación.
                        <br>• <strong>Descuento (%)</strong> - el porcentaje del cupón (ej: 10%).
                        <br>• <strong>Días de validez</strong> - cuántos días es válido el cupón.
                        <br><br>Los cupones se muestran en tu <strong>página pública</strong> y en los <strong>tickets impresos</strong>.
                        El cliente puede usar el cupón en su próxima <strong>venta</strong> o <strong>reparación</strong>.
                    </p>
                </div>
            </div>

            <div class="consejo">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Consejo:</strong> Comparte el link de tu página pública en tus <strong>redes sociales</strong>
                    y en los <strong>tickets de venta y reparación</strong>. La URL también se incluye automáticamente
                    en los <strong>mensajes de WhatsApp</strong> que envías a tus clientes.
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>
                    Cuando actualizas tu página pública, el <strong>logo</strong> que subas debe estar en formato
                    <strong>PNG, JPG o WEBP</strong> (máx 2MB). Asegúrate de <strong>subirlo nuevamente</strong> si
                    actualizas el sistema, ya que los archivos se guardan localmente.
                </div>
            </div>
        </section>

        {{-- ── BACKUP & RESTORE ── --}}
        <section class="ayuda-seccion" id="backup">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Backup & Restore</h2>
                    <p class="seccion-desc">Protege la información de tu negocio</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Crear un backup</div>
                    <p class="paso-texto">
                        Ve a <strong>Backup & Restore</strong> y haz clic en <strong>"Crear Backup"</strong>.
                        El sistema generará una copia completa de tu base de datos.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Descargar el backup</div>
                    <p class="paso-texto">
                        En la lista de backups, haz clic en <i class="fas fa-download text-primary"></i>
                        <strong>Descargar</strong> para guardar la copia en tu computadora.
                        <strong>Guarda los backups en un lugar seguro</strong> (disco externo, nube, etc.).
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Restaurar un backup</div>
                    <p class="paso-texto">
                        Si necesitas recuperar datos, sube el archivo de backup y haz clic en
                        <strong>"Restaurar"</strong>. El sistema reemplazará los datos actuales
                        con los del backup.
                    </p>
                </div>
            </div>

            <div class="advertencia">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Advertencia:</strong> Restaurar un backup <strong>reemplaza todos los datos actuales</strong>.
                    Asegúrate de tener un backup reciente antes de restaurar.
                </div>
            </div>

            <div class="consejo">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Recomendación:</strong> Crea un backup <strong>al menos una vez por semana</strong>
                    o después de cambios importantes en tu inventario o precios.
                </div>
            </div>
        </section>

        {{-- ── INSTALAR APP (PWA) ── --}}
        <section class="ayuda-seccion" id="pwa">
            <div class="seccion-header">
                <div class="seccion-icon" style="background:linear-gradient(135deg,#a855f7,#ec4899);">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div>
                    <h2 class="seccion-titulo">Instalar la App en tu Celular</h2>
                    <p class="seccion-desc">Accede al sistema como una aplicación nativa</p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">En Android (Chrome)</div>
                    <p class="paso-texto">
                        Abre el sistema en <strong>Chrome</strong> y toca el menú
                        <strong>⋮ (tres puntos)</strong> en la esquina superior derecha.
                        Busca y toca <strong>"Instalar app"</strong> o <strong>"Agregar a pantalla de inicio"</strong>.
                        Confirma tocando <strong>"Instalar"</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">En iPhone (Safari)</div>
                    <p class="paso-texto">
                        Abre el sistema en <strong>Safari</strong> y toca el botón
                        <strong>Compartir</strong> (cuadrado con flecha hacia arriba).
                        Desliza hacia abajo y toca <strong>"Agregar a pantalla de inicio"</strong>.
                        Confirma tocando <strong>"Agregar"</strong>.
                    </p>
                </div>
            </div>

            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <div class="paso-titulo">Beneficios de instalar la app</div>
                    <p class="paso-texto">
                        <i class="fas fa-bolt text-warning"></i> <strong>Acceso rápido</strong> desde el icono en tu pantalla de inicio.
                        <br><i class="fas fa-wifi text-primary"></i> <strong>Funciona sin conexión</strong> parcialmente.
                        <br><i class="fas fa-bell text-danger"></i> <strong>Notificaciones</strong> de alertas de stock y reparaciones.
                    </p>
                </div>
            </div>

            <div class="nota">
                <i class="fas fa-info-circle"></i>
                <div>
                    Si no aparece la opción de instalar, visita el sitio <strong>2 veces</strong> con al menos
                    <strong>5 minutos</strong> de diferencia y verifica que la conexión sea <strong>HTTPS</strong> (candado 🔒).
                </div>
            </div>
        </section>

    </div>
</div>

{{-- Botón volver arriba --}}
<button class="btn-volver-arriba" id="btnVolverArriba" title="Volver arriba">
    <i class="fas fa-arrow-up"></i>
</button>
@endsection

@section('scripts')
<script>
    // ── Navegación del índice ──────────────────────────────────────
    document.querySelectorAll('.ayuda-indice .indice-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.getElementById(this.dataset.target);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── Resaltar sección activa en el índice ───────────────────────
    var secciones = document.querySelectorAll('.ayuda-seccion');
    var indiceItems = document.querySelectorAll('.ayuda-indice .indice-item');

    function actualizarIndiceActivo() {
        var scrollPos = window.scrollY + 120;
        var actual = null;

        secciones.forEach(function(seccion) {
            if (seccion.offsetTop <= scrollPos) {
                actual = seccion;
            }
        });

        if (actual) {
            indiceItems.forEach(function(item) {
                item.classList.remove('active');
                if (item.dataset.target === actual.id) {
                    item.classList.add('active');
                }
            });
        }
    }

    window.addEventListener('scroll', actualizarIndiceActivo);
    actualizarIndiceActivo();

    // ── Botón volver arriba ────────────────────────────────────────
    var btnVolverArriba = document.getElementById('btnVolverArriba');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            btnVolverArriba.classList.add('visible');
        } else {
            btnVolverArriba.classList.remove('visible');
        }
    });

    btnVolverArriba.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
@endsection
