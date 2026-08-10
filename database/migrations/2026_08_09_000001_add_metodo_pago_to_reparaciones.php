<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna metodo_pago a la tabla reparaciones.
     */
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('reparaciones', 'metodo_pago')) {
                $table->string('metodo_pago', 30)->nullable()->after('total');
            }
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            if (Schema::hasColumn('reparaciones', 'metodo_pago')) {
                $table->dropColumn('metodo_pago');
            }
        });
    }
};