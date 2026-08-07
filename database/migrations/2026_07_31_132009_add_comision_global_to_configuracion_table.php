<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            // Comisión global para técnicos (por defecto 0%)
            $table->decimal('comision_global_tecnicos', 5, 2)->default(0)->after('igv');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn('comision_global_tecnicos');
        });
    }
};