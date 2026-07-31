<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            // Porcentaje de comisión aplicado (se guarda al momento de entregar)
            $table->decimal('comision_porcentaje', 5, 2)->nullable()->after('total');
            // Monto de comisión generado
            $table->decimal('comision_monto', 10, 2)->nullable()->after('comision_porcentaje');
            // Si la comisión ya fue pagada al técnico
            $table->boolean('comision_pagada')->default(false)->after('comision_monto');
            // Fecha de pago de comisión
            $table->dateTime('comision_fecha_pago')->nullable()->after('comision_pagada');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn(['comision_porcentaje', 'comision_monto', 'comision_pagada', 'comision_fecha_pago']);
        });
    }
};