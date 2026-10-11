<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('reparaciones', 'accesorios_entregados')) {
                $table->string('accesorios_entregados', 255)->nullable()->after('notas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            if (Schema::hasColumn('reparaciones', 'accesorios_entregados')) {
                $table->dropColumn('accesorios_entregados');
            }
        });
    }
};