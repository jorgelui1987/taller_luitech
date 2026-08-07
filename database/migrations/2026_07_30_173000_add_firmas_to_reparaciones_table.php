<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->string('firma_recepcion')->nullable()->after('notas');
            $table->string('firma_entrega')->nullable()->after('firma_recepcion');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn(['firma_recepcion', 'firma_entrega']);
        });
    }
};
