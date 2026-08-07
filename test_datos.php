<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== CLIENTES ===\n";
    $clientes = DB::table('clientes')->select('id', 'nombre', 'apellido', 'email', 'telefono', 'tenant_id')->get();
    foreach ($clientes as $c) {
        echo "  ID: {$c->id} | {$c->nombre} {$c->apellido} | {$c->email} | {$c->telefono} | tenant: {$c->tenant_id}\n";
    }
    echo "Total: " . count($clientes) . "\n\n";

    echo "=== PROVEEDORES ===\n";
    $proveedores = DB::table('proveedores')->select('id', 'nombre', 'contacto', 'email', 'telefono', 'tenant_id')->get();
    foreach ($proveedores as $p) {
        echo "  ID: {$p->id} | {$p->nombre} | {$p->contacto} | {$p->email} | {$p->telefono} | tenant: {$p->tenant_id}\n";
    }
    echo "Total: " . count($proveedores) . "\n\n";

    echo "=== USERS ===\n";
    $users = DB::table('users')->select('id', 'name', 'email', 'tenant_id', 'rol')->get();
    foreach ($users as $u) {
        echo "  ID: {$u->id} | {$u->name} | {$u->email} | tenant: {$u->tenant_id} | rol: {$u->rol}\n";
    }
    echo "Total: " . count($users) . "\n\n";

    echo "=== TENANTS ===\n";
    $tenants = DB::table('tenants')->select('id', 'nombre', 'slug')->get();
    foreach ($tenants as $t) {
        echo "  ID: {$t->id} | {$t->nombre} | {$t->slug}\n";
    }
    echo "Total: " . count($tenants) . "\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo 'Archivo: ' . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
