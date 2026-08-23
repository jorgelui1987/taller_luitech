<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna stock_daniado a la tabla productos.
     * Esta columna guarda la cantidad de unidades que volvieron
     * dañadas o incompletas de devoluciones y NO deben estar
     * en el stock vendible.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('stock_daniado')->default(0)->after('stock');
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock_daniado');
        });
    }
};