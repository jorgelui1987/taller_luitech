<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantaneously load the entire route map into the router.
|
*/

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/health-check' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.healthCheck',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/execute-solution' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.executeSolution',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/update-config' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.updateConfig',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ZBUJ6mKQTHByZP5X',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/superadmin/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.login.post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/superadmin/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/superadmin/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/superadmin/tenants' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/superadmin/tenants/crear' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/superadmin/planes-precios' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.planes-precios',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'landing',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/planes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planes',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/registro' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'registro.tenant',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'registro.tenant.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'login.post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/register' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'register',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'register.post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/clientes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/clientes/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/productos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'productos.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/productos/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/proveedores' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/proveedores/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/compras' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'compras.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'compras.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/compras/crear' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'compras.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/stock/movimientos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.movimientos',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/stock/ajuste' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.ajuste',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'stock.ajuste.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/stock/bajo' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.bajo',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/stock/bajo/contador' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.stock.bajo',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/exportar/productos/csv' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.exportar.csv',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/exportar/productos/html' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.exportar.html',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/exportar/productos/plantilla' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.plantilla',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/importar/productos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.importar',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'productos.importar.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ventas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ventas.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'ventas.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ventas/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ventas.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reparaciones' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reparaciones/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reportes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reportes.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/financiero' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'financiero.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/financiero/estado-resultados' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'financiero.estado-resultados',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/financiero/balance-general' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'financiero.balance-general',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/financiero/flujo-caja' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'financiero.flujo-caja',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/financiero/indicadores' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'financiero.indicadores',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/configuracion' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'configuracion.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/configuracion/empresa' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'configuracion.updateEmpresa',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/configuracion/usuarios' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'configuracion.storeUsuario',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backup' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backup/crear' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.crear',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backup/restaurar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.restaurar',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backup/resetear' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.resetear',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/productos/buscar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.productos.buscar',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/dashboard/ventas-semana' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.dashboard.ventas',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/s(?|uperadmin/(?|tenants/([^/]++)(?|/(?|editar(*:54)|toggle(*:67)|login\\-as(*:83)|usuarios(*:98))|(*:106))|usuarios/([^/]++)/cambiar\\-password(*:150)|planes\\-precios/([^/]++)(*:182))|to(?|rage/(.*)(*:205)|ck/(?|producto/([^/]++)(*:236)|bajo/([^/]++)/whatsapp(*:266))))|/r(?|/([^/]++)(*:291)|eparaciones/([^/]++)(?|(*:322)|/(?|edit(*:338)|ticket(*:352))|(*:361)))|/c(?|lientes/([^/]++)(?|(*:395)|/edit(*:408)|(*:416))|o(?|mpras/([^/]++)(?|(*:446)|/e(?|ditar(*:464)|stado(*:477))|(*:486))|nfiguracion/usuarios/([^/]++)(?|/toggle(*:534)|(*:542))))|/pro(?|ductos/(?|([^/]++)(?|(*:581)|/edit(*:594)|(*:602))|marca\\-ajax(*:622)|categoria\\-ajax(*:645))|veedores/([^/]++)(?|(*:674)|/(?|edit(*:690)|toggle(*:704))|(*:713)))|/ventas/([^/]++)(?|(*:742)|/(?|cancelar(*:762)|ticket(*:776)))|/backup/(?|descargar/([^/]++)(*:815)|eliminar/([^/]++)(*:840)))/?$}sDu',
    ),
    3 => 
    array (
      54 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.edit',
          ),
          1 => 
          array (
            0 => 'tenant',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      67 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.toggle',
          ),
          1 => 
          array (
            0 => 'tenant',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      83 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.login-as',
          ),
          1 => 
          array (
            0 => 'tenant',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      98 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.users',
          ),
          1 => 
          array (
            0 => 'tenant',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      106 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.update',
          ),
          1 => 
          array (
            0 => 'tenant',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.tenants.destroy',
          ),
          1 => 
          array (
            0 => 'tenant',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      150 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.usuarios.change-password',
          ),
          1 => 
          array (
            0 => 'usuario',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      182 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'superadmin.planes-precios.update',
          ),
          1 => 
          array (
            0 => 'planPrecio',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      205 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.serve',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      236 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.producto',
          ),
          1 => 
          array (
            0 => 'producto',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      266 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.bajo.whatsapp',
          ),
          1 => 
          array (
            0 => 'producto',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      291 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.public-status',
          ),
          1 => 
          array (
            0 => 'numero_orden',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      322 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.show',
          ),
          1 => 
          array (
            0 => 'reparacion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      338 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.edit',
          ),
          1 => 
          array (
            0 => 'reparacion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      352 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.ticket',
          ),
          1 => 
          array (
            0 => 'reparacion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      361 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reparaciones.update',
          ),
          1 => 
          array (
            0 => 'reparacion',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      395 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.show',
          ),
          1 => 
          array (
            0 => 'cliente',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      408 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.edit',
          ),
          1 => 
          array (
            0 => 'cliente',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      416 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.update',
          ),
          1 => 
          array (
            0 => 'cliente',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'clientes.destroy',
          ),
          1 => 
          array (
            0 => 'cliente',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      446 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'compras.show',
          ),
          1 => 
          array (
            0 => 'ordenCompra',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      464 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'compras.edit',
          ),
          1 => 
          array (
            0 => 'ordenCompra',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      477 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'compras.estado',
          ),
          1 => 
          array (
            0 => 'ordenCompra',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      486 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'compras.update',
          ),
          1 => 
          array (
            0 => 'ordenCompra',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'compras.destroy',
          ),
          1 => 
          array (
            0 => 'ordenCompra',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      534 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'configuracion.toggleUsuario',
          ),
          1 => 
          array (
            0 => 'usuario',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      542 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'configuracion.updateUsuario',
          ),
          1 => 
          array (
            0 => 'usuario',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'configuracion.destroyUsuario',
          ),
          1 => 
          array (
            0 => 'usuario',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      581 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.show',
          ),
          1 => 
          array (
            0 => 'producto',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      594 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.edit',
          ),
          1 => 
          array (
            0 => 'producto',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      602 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.update',
          ),
          1 => 
          array (
            0 => 'producto',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'productos.destroy',
          ),
          1 => 
          array (
            0 => 'producto',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      622 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.marca-ajax',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      645 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'productos.categoria-ajax',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      674 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.show',
          ),
          1 => 
          array (
            0 => 'proveedore',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      690 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.edit',
          ),
          1 => 
          array (
            0 => 'proveedore',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      704 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.toggle',
          ),
          1 => 
          array (
            0 => 'proveedor',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      713 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.update',
          ),
          1 => 
          array (
            0 => 'proveedore',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'proveedores.destroy',
          ),
          1 => 
          array (
            0 => 'proveedore',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      742 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ventas.show',
          ),
          1 => 
          array (
            0 => 'venta',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      762 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ventas.cancelar',
          ),
          1 => 
          array (
            0 => 'venta',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      776 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ventas.ticket',
          ),
          1 => 
          array (
            0 => 'venta',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      815 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.descargar',
          ),
          1 => 
          array (
            0 => 'nombre',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      840 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.eliminar',
          ),
          1 => 
          array (
            0 => 'nombre',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.healthCheck' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_ignition/health-check',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController',
        'as' => 'ignition.healthCheck',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.executeSolution' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/execute-solution',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController',
        'as' => 'ignition.executeSolution',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.updateConfig' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/update-config',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController',
        'as' => 'ignition.updateConfig',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ZBUJ6mKQTHByZP5X' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:79:"function (\\Illuminate\\Http\\Request $request) {
    return $request->user();
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000092a0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ZBUJ6mKQTHByZP5X',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@showLoginForm',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@showLoginForm',
        'as' => 'superadmin.login',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.login.post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'superadmin/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@login',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@login',
        'as' => 'superadmin.login.post',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'superadmin/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@logout',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@logout',
        'as' => 'superadmin.logout',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@dashboard',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@dashboard',
        'as' => 'superadmin.dashboard',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/tenants',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@tenants',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@tenants',
        'as' => 'superadmin.tenants',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/tenants/crear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@createTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@createTenant',
        'as' => 'superadmin.tenants.create',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'superadmin/tenants',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@storeTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@storeTenant',
        'as' => 'superadmin.tenants.store',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/tenants/{tenant}/editar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@editTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@editTenant',
        'as' => 'superadmin.tenants.edit',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'superadmin/tenants/{tenant}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@updateTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@updateTenant',
        'as' => 'superadmin.tenants.update',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'superadmin/tenants/{tenant}/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@toggleTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@toggleTenant',
        'as' => 'superadmin.tenants.toggle',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'superadmin/tenants/{tenant}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@destroyTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@destroyTenant',
        'as' => 'superadmin.tenants.destroy',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.login-as' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/tenants/{tenant}/login-as',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@loginAsTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@loginAsTenant',
        'as' => 'superadmin.tenants.login-as',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.tenants.users' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/tenants/{tenant}/usuarios',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@tenantUsers',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@tenantUsers',
        'as' => 'superadmin.tenants.users',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.usuarios.change-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'superadmin/usuarios/{usuario}/cambiar-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@changeUserPassword',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@changeUserPassword',
        'as' => 'superadmin.usuarios.change-password',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.planes-precios' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'superadmin/planes-precios',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@planPreciosIndex',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@planPreciosIndex',
        'as' => 'superadmin.planes-precios',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'superadmin.planes-precios.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'superadmin/planes-precios/{planPrecio}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@planPreciosUpdate',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@planPreciosUpdate',
        'as' => 'superadmin.planes-precios.update',
        'namespace' => NULL,
        'prefix' => '/superadmin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.public-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'r/{numero_orden}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PublicReparacionController@status',
        'controller' => 'App\\Http\\Controllers\\PublicReparacionController@status',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reparaciones.public-status',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.serve' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:184:"function ($path) {
    $fullPath = \\storage_path(\'app/public/\' . $path);
    if (!\\file_exists($fullPath)) {
        \\abort(404);
    }
    return \\response()->file($fullPath);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000092d0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'storage.serve',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'landing' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:131:"function () {
    if (\\auth()->check()) {
        return \\redirect()->route(\'dashboard\');
    }
    return \\view(\'landing\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009320000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'landing',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'planes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:46:"function () {
    return \\view(\'landing\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009410000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planes',
        'excluded_middleware' => 
        array (
          0 => 'App\\Http\\Middleware\\CheckTenantStatus',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'registro.tenant' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'registro',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@showRegistroTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@showRegistroTenant',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'registro.tenant',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'registro.tenant.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'registro',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SuperAdminController@registrarTenant',
        'controller' => 'App\\Http\\Controllers\\SuperAdminController@registrarTenant',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'registro.tenant.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@showLoginForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@showLoginForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login.post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login.post',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'register' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\RegisterController@showRegistrationForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\RegisterController@showRegistrationForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'register',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'register.post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\RegisterController@register',
        'controller' => 'App\\Http\\Controllers\\Auth\\RegisterController@register',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'register.post',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\DashboardController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'clientes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.index',
        'uses' => 'App\\Http\\Controllers\\ClienteController@index',
        'controller' => 'App\\Http\\Controllers\\ClienteController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'clientes/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.create',
        'uses' => 'App\\Http\\Controllers\\ClienteController@create',
        'controller' => 'App\\Http\\Controllers\\ClienteController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'clientes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.store',
        'uses' => 'App\\Http\\Controllers\\ClienteController@store',
        'controller' => 'App\\Http\\Controllers\\ClienteController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'clientes/{cliente}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.show',
        'uses' => 'App\\Http\\Controllers\\ClienteController@show',
        'controller' => 'App\\Http\\Controllers\\ClienteController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'clientes/{cliente}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.edit',
        'uses' => 'App\\Http\\Controllers\\ClienteController@edit',
        'controller' => 'App\\Http\\Controllers\\ClienteController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'clientes/{cliente}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.update',
        'uses' => 'App\\Http\\Controllers\\ClienteController@update',
        'controller' => 'App\\Http\\Controllers\\ClienteController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientes.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'clientes/{cliente}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'clientes.destroy',
        'uses' => 'App\\Http\\Controllers\\ClienteController@destroy',
        'controller' => 'App\\Http\\Controllers\\ClienteController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'productos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.index',
        'uses' => 'App\\Http\\Controllers\\ProductoController@index',
        'controller' => 'App\\Http\\Controllers\\ProductoController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'productos/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.create',
        'uses' => 'App\\Http\\Controllers\\ProductoController@create',
        'controller' => 'App\\Http\\Controllers\\ProductoController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'productos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.store',
        'uses' => 'App\\Http\\Controllers\\ProductoController@store',
        'controller' => 'App\\Http\\Controllers\\ProductoController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'productos/{producto}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.show',
        'uses' => 'App\\Http\\Controllers\\ProductoController@show',
        'controller' => 'App\\Http\\Controllers\\ProductoController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'productos/{producto}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.edit',
        'uses' => 'App\\Http\\Controllers\\ProductoController@edit',
        'controller' => 'App\\Http\\Controllers\\ProductoController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'productos/{producto}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.update',
        'uses' => 'App\\Http\\Controllers\\ProductoController@update',
        'controller' => 'App\\Http\\Controllers\\ProductoController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'productos/{producto}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'productos.destroy',
        'uses' => 'App\\Http\\Controllers\\ProductoController@destroy',
        'controller' => 'App\\Http\\Controllers\\ProductoController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.marca-ajax' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'productos/marca-ajax',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\ProductoController@storeMarcaAjax',
        'controller' => 'App\\Http\\Controllers\\ProductoController@storeMarcaAjax',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.marca-ajax',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.categoria-ajax' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'productos/categoria-ajax',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\ProductoController@storeCategoriaAjax',
        'controller' => 'App\\Http\\Controllers\\ProductoController@storeCategoriaAjax',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.categoria-ajax',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'proveedores',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.index',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@index',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'proveedores/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.create',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@create',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'proveedores',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.store',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@store',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'proveedores/{proveedore}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.show',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@show',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'proveedores/{proveedore}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.edit',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@edit',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'proveedores/{proveedore}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.update',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@update',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'proveedores/{proveedore}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'as' => 'proveedores.destroy',
        'uses' => 'App\\Http\\Controllers\\ProveedorController@destroy',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'proveedores.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'proveedores/{proveedor}/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'uses' => 'App\\Http\\Controllers\\ProveedorController@toggle',
        'controller' => 'App\\Http\\Controllers\\ProveedorController@toggle',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'proveedores.toggle',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'compras',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@index',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'compras/crear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@create',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'compras',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@store',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'compras/{ordenCompra}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@show',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'compras/{ordenCompra}/editar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@edit',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'compras/{ordenCompra}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@update',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'compras/{ordenCompra}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@destroy',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'compras.estado' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'compras/{ordenCompra}/estado',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\OrdenCompraController@cambiarEstado',
        'controller' => 'App\\Http\\Controllers\\OrdenCompraController@cambiarEstado',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'compras.estado',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.movimientos' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'stock/movimientos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovimientoController@index',
        'controller' => 'App\\Http\\Controllers\\StockMovimientoController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.movimientos',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.ajuste' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'stock/ajuste',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovimientoController@ajusteForm',
        'controller' => 'App\\Http\\Controllers\\StockMovimientoController@ajusteForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.ajuste',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.ajuste.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'stock/ajuste',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovimientoController@ajusteStore',
        'controller' => 'App\\Http\\Controllers\\StockMovimientoController@ajusteStore',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.ajuste.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.producto' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'stock/producto/{producto}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovimientoController@porProducto',
        'controller' => 'App\\Http\\Controllers\\StockMovimientoController@porProducto',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.producto',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.bajo' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'stock/bajo',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockBajoController@index',
        'controller' => 'App\\Http\\Controllers\\StockBajoController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.bajo',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.bajo.whatsapp' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'stock/bajo/{producto}/whatsapp',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockBajoController@notificacionWhatsApp',
        'controller' => 'App\\Http\\Controllers\\StockBajoController@notificacionWhatsApp',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.bajo.whatsapp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.stock.bajo' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/stock/bajo/contador',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\StockBajoController@apiContador',
        'controller' => 'App\\Http\\Controllers\\StockBajoController@apiContador',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.stock.bajo',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.exportar.csv' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'exportar/productos/csv',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ExportacionController@productosExcel',
        'controller' => 'App\\Http\\Controllers\\ExportacionController@productosExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.exportar.csv',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.exportar.html' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'exportar/productos/html',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ExportacionController@productosPDF',
        'controller' => 'App\\Http\\Controllers\\ExportacionController@productosPDF',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.exportar.html',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.plantilla' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'exportar/productos/plantilla',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ExportacionController@productosPlantillaImportacion',
        'controller' => 'App\\Http\\Controllers\\ExportacionController@productosPlantillaImportacion',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.plantilla',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.importar' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'importar/productos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ImportacionController@importarForm',
        'controller' => 'App\\Http\\Controllers\\ImportacionController@importarForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.importar',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'productos.importar.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'importar/productos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ImportacionController@importarStore',
        'controller' => 'App\\Http\\Controllers\\ImportacionController@importarStore',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'productos.importar.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ventas.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ventas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.ventas',
        ),
        'as' => 'ventas.index',
        'uses' => 'App\\Http\\Controllers\\VentaController@index',
        'controller' => 'App\\Http\\Controllers\\VentaController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ventas.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ventas/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.ventas',
        ),
        'as' => 'ventas.create',
        'uses' => 'App\\Http\\Controllers\\VentaController@create',
        'controller' => 'App\\Http\\Controllers\\VentaController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ventas.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'ventas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.ventas',
        ),
        'as' => 'ventas.store',
        'uses' => 'App\\Http\\Controllers\\VentaController@store',
        'controller' => 'App\\Http\\Controllers\\VentaController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ventas.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ventas/{venta}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.ventas',
        ),
        'as' => 'ventas.show',
        'uses' => 'App\\Http\\Controllers\\VentaController@show',
        'controller' => 'App\\Http\\Controllers\\VentaController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ventas.cancelar' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'ventas/{venta}/cancelar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.ventas',
        ),
        'uses' => 'App\\Http\\Controllers\\VentaController@cancelar',
        'controller' => 'App\\Http\\Controllers\\VentaController@cancelar',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'ventas.cancelar',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ventas.ticket' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ventas/{venta}/ticket',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.ventas',
        ),
        'uses' => 'App\\Http\\Controllers\\VentaController@printTicket',
        'controller' => 'App\\Http\\Controllers\\VentaController@printTicket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'ventas.ticket',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reparaciones',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'as' => 'reparaciones.index',
        'uses' => 'App\\Http\\Controllers\\ReparacionController@index',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reparaciones/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'as' => 'reparaciones.create',
        'uses' => 'App\\Http\\Controllers\\ReparacionController@create',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reparaciones',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'as' => 'reparaciones.store',
        'uses' => 'App\\Http\\Controllers\\ReparacionController@store',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reparaciones/{reparacion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'as' => 'reparaciones.show',
        'uses' => 'App\\Http\\Controllers\\ReparacionController@show',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reparaciones/{reparacion}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'as' => 'reparaciones.edit',
        'uses' => 'App\\Http\\Controllers\\ReparacionController@edit',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'reparaciones/{reparacion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'as' => 'reparaciones.update',
        'uses' => 'App\\Http\\Controllers\\ReparacionController@update',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reparaciones.ticket' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reparaciones/{reparacion}/ticket',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.reparaciones',
        ),
        'uses' => 'App\\Http\\Controllers\\ReparacionController@printTicket',
        'controller' => 'App\\Http\\Controllers\\ReparacionController@printTicket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reparaciones.ticket',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reportes.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reportes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ReporteController@index',
        'controller' => 'App\\Http\\Controllers\\ReporteController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reportes.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'financiero.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'financiero',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\FinancieroController@index',
        'controller' => 'App\\Http\\Controllers\\FinancieroController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'financiero.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'financiero.estado-resultados' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'financiero/estado-resultados',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\FinancieroController@estadoResultados',
        'controller' => 'App\\Http\\Controllers\\FinancieroController@estadoResultados',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'financiero.estado-resultados',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'financiero.balance-general' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'financiero/balance-general',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\FinancieroController@balanceGeneral',
        'controller' => 'App\\Http\\Controllers\\FinancieroController@balanceGeneral',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'financiero.balance-general',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'financiero.flujo-caja' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'financiero/flujo-caja',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\FinancieroController@flujoCaja',
        'controller' => 'App\\Http\\Controllers\\FinancieroController@flujoCaja',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'financiero.flujo-caja',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'financiero.indicadores' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'financiero/indicadores',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\FinancieroController@indicadores',
        'controller' => 'App\\Http\\Controllers\\FinancieroController@indicadores',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'financiero.indicadores',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'configuracion.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'configuracion',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ConfiguracionController@index',
        'controller' => 'App\\Http\\Controllers\\ConfiguracionController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'configuracion.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'configuracion.updateEmpresa' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'configuracion/empresa',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ConfiguracionController@updateEmpresa',
        'controller' => 'App\\Http\\Controllers\\ConfiguracionController@updateEmpresa',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'configuracion.updateEmpresa',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'configuracion.storeUsuario' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'configuracion/usuarios',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ConfiguracionController@storeUsuario',
        'controller' => 'App\\Http\\Controllers\\ConfiguracionController@storeUsuario',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'configuracion.storeUsuario',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'configuracion.toggleUsuario' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'configuracion/usuarios/{usuario}/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ConfiguracionController@toggleUsuario',
        'controller' => 'App\\Http\\Controllers\\ConfiguracionController@toggleUsuario',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'configuracion.toggleUsuario',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'configuracion.updateUsuario' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'configuracion/usuarios/{usuario}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ConfiguracionController@updateUsuario',
        'controller' => 'App\\Http\\Controllers\\ConfiguracionController@updateUsuario',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'configuracion.updateUsuario',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'configuracion.destroyUsuario' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'configuracion/usuarios/{usuario}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ConfiguracionController@destroyUsuario',
        'controller' => 'App\\Http\\Controllers\\ConfiguracionController@destroyUsuario',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'configuracion.destroyUsuario',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'backup.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'backup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\BackupController@index',
        'controller' => 'App\\Http\\Controllers\\BackupController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'backup.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'backup.crear' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'backup/crear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\BackupController@crear',
        'controller' => 'App\\Http\\Controllers\\BackupController@crear',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'backup.crear',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'backup.descargar' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'backup/descargar/{nombre}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\BackupController@descargar',
        'controller' => 'App\\Http\\Controllers\\BackupController@descargar',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'backup.descargar',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'backup.eliminar' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'backup/eliminar/{nombre}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\BackupController@eliminar',
        'controller' => 'App\\Http\\Controllers\\BackupController@eliminar',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'backup.eliminar',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'backup.restaurar' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'backup/restaurar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\BackupController@restaurar',
        'controller' => 'App\\Http\\Controllers\\BackupController@restaurar',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'backup.restaurar',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'backup.resetear' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'backup/resetear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
          4 => 'check.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\BackupController@resetear',
        'controller' => 'App\\Http\\Controllers\\BackupController@resetear',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'backup.resetear',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.productos.buscar' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/productos/buscar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:545:"function () {
            $productos = \\App\\Models\\Producto::with([\'marca\'])
                ->where(\'activo\', true)
                ->where(\'stock\', \'>\', 0)
                ->when(\\request(\'q\'), fn($q, $buscar) =>
                    $q->where(\'nombre\', \'like\', "%$buscar%")
                      ->orWhere(\'codigo\', \'like\', "%$buscar%")
                )
                ->limit(10)
                ->get([\'id\', \'nombre\', \'codigo\', \'precio_venta\', \'stock\', \'marca_id\']);

            return \\response()->json($productos);
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009510000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.productos.buscar',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.dashboard.ventas' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/dashboard/ventas-semana',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'tenant',
          2 => 'auth',
          3 => 'check.tenant',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:551:"function () {
            $datos = \\App\\Models\\Venta::select(
                    \\Illuminate\\Support\\Facades\\DB::raw(\'DATE(fecha_venta) as fecha\'),
                    \\Illuminate\\Support\\Facades\\DB::raw(\'SUM(total) as total\')
                )
                ->where(\'estado\', \'completada\')
                ->where(\'fecha_venta\', \'>=\', \\Carbon\\Carbon::now()->subDays(6)->startOfDay())
                ->groupBy(\'fecha\')
                ->orderBy(\'fecha\')
                ->get();

            return \\response()->json($datos);
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000099c0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.dashboard.ventas',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
