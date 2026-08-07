<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('reparaciones', 'costo_repuesto')) {
            Schema::table('reparaciones', function (Blueprint $table) {
                $table->decimal('costo_repuesto', 10, 2)->nullable()->default(null)->after('costo_final');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reparaciones', 'costo_repuesto')) {
            Schema::table('reparaciones', function (Blueprint $table) {
                $table->dropColumn('costo_repuesto');
            });
        }
    }
};