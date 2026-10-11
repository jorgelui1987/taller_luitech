<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices de rendimiento para producción con alto volumen.
     * Sin estos, los listados con LIKE + filtros por tenant/estado se
     * vuelven lentos al crecer a miles de registros.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->index(['tenant_id', 'estado', 'fecha_venta'], 'idx_ventas_tenant_estado_fecha');
            $table->index('numero_venta', 'idx_ventas_numero');
        });
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->index(['tenant_id', 'estado'], 'idx_rep_tenant_estado');
            $table->index('numero_orden', 'idx_rep_numero');
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->index(['tenant_id', 'activo'], 'idx_prod_tenant_activo');
            $table->index('codigo_barras', 'idx_prod_codbarras');
        });
        Schema::table('clientes', function (Blueprint $table) {
            $table->index(['tenant_id', 'activo'], 'idx_cli_tenant_activo');
        });
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->index('venta_id', 'idx_detventa_venta');
            $table->index('producto_id', 'idx_detventa_producto');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_tenant_estado_fecha');
            $table->dropIndex('idx_ventas_numero');
        });
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropIndex('idx_rep_tenant_estado');
            $table->dropIndex('idx_rep_numero');
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('idx_prod_tenant_activo');
            $table->dropIndex('idx_prod_codbarras');
        });
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_cli_tenant_activo');
        });
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropIndex('idx_detventa_venta');
            $table->dropIndex('idx_detventa_producto');
        });
    }
};
