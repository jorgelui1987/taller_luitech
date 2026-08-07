<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Asignar tenant_id = 1 a los registros existentes que no lo tienen
        // Esto se ejecuta automáticamente en producción con php artisan migrate --force

        $primerTenant = DB::table('tenants')->select('id')->first();

        if (!$primerTenant) {
            return; // Si no hay tenants, no hacer nada
        }

        $tenantId = $primerTenant->id;

        // Asignar tenant_id a usuarios (excepto superadmin)
        DB::table('users')
            ->whereNull('tenant_id')
            ->where('rol', '!=', 'superadmin')
            ->update(['tenant_id' => $tenantId]);

        // Asignar tenant_id a clientes
        DB::table('clientes')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        // Asignar tenant_id a proveedores
        DB::table('proveedores')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        // Asignar tenant_id a productos
        DB::table('productos')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        // Asignar tenant_id a ventas
        if (DB::getSchemaBuilder()->hasColumn('ventas', 'tenant_id')) {
            DB::table('ventas')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        // Asignar tenant_id a detalle_ventas
        if (DB::getSchemaBuilder()->hasColumn('detalle_ventas', 'tenant_id')) {
            DB::table('detalle_ventas')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        // Asignar tenant_id a reparaciones
        if (DB::getSchemaBuilder()->hasColumn('reparaciones', 'tenant_id')) {
            DB::table('reparaciones')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        // Asignar tenant_id a ordenes_compra
        if (DB::getSchemaBuilder()->hasColumn('ordenes_compra', 'tenant_id')) {
            DB::table('ordenes_compra')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        // Asignar tenant_id a detalle_ordenes_compra
        if (DB::getSchemaBuilder()->hasColumn('detalle_ordenes_compra', 'tenant_id')) {
            DB::table('detalle_ordenes_compra')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        // Asignar tenant_id a movimientos_stock
        if (DB::getSchemaBuilder()->hasColumn('movimientos_stock', 'tenant_id')) {
            DB::table('movimientos_stock')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        // Asignar tenant_id a configuracion
        if (DB::getSchemaBuilder()->hasColumn('configuracion', 'tenant_id')) {
            DB::table('configuracion')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }
    }

    public function down(): void
    {
        // No revertir los tenant_id asignados
    }
};
