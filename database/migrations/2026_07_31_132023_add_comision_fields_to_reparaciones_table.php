<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('reparaciones', 'comision_porcentaje')) {
                // Porcentaje de comisión aplicado (se guarda al momento de entregar)
                $table->decimal('comision_porcentaje', 5, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('reparaciones', 'comision_monto')) {
                // Monto de comisión generado
                $table->decimal('comision_monto', 10, 2)->nullable()->after('comision_porcentaje');
            }
            if (!Schema::hasColumn('reparaciones', 'comision_pagada')) {
                // Si la comisión ya fue pagada al técnico
                $table->boolean('comision_pagada')->default(false)->after('comision_monto');
            }
            if (!Schema::hasColumn('reparaciones', 'comision_fecha_pago')) {
                // Fecha de pago de comisión
                $table->dateTime('comision_fecha_pago')->nullable()->after('comision_pagada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $columns = ['comision_porcentaje', 'comision_monto', 'comision_pagada', 'comision_fecha_pago'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('reparaciones', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};