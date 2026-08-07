<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas', 'comision_monto')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->decimal('comision_monto', 10, 2)->nullable()->default(0)->after('total');
            });
        }
        if (!Schema::hasColumn('ventas', 'comision_pagada')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->boolean('comision_pagada')->default(false)->after('comision_monto');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $columns = ['comision_monto', 'comision_pagada'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('ventas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
