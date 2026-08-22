<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->decimal('impuesto', 10, 2)->default(0)->after('costo_repuesto');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn('impuesto');
        });
    }
};