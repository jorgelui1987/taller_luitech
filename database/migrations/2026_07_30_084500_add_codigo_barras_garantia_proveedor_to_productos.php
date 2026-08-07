<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Agregar columnas faltantes que estaban en add_producto_mejoras.sql
            if (!Schema::hasColumn('productos', 'codigo_barras')) {
                $table->string('codigo_barras', 100)->nullable()->after('codigo');
            }
            if (!Schema::hasColumn('productos', 'garantia_dias')) {
                $table->integer('garantia_dias')->nullable()->default(0)->after('descripcion');
            }
            if (!Schema::hasColumn('productos', 'proveedor_id')) {
                $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete()->after('marca_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'codigo_barras')) {
                $table->dropColumn('codigo_barras');
            }
            if (Schema::hasColumn('productos', 'garantia_dias')) {
                $table->dropColumn('garantia_dias');
            }
            if (Schema::hasColumn('productos', 'proveedor_id')) {
                $table->dropForeign(['proveedor_id']);
                $table->dropColumn('proveedor_id');
            }
        });
    }
};
