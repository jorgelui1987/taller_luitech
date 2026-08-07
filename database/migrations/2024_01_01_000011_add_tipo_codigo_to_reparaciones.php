<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            // tipo_dispositivo: celular, tablet, portatil, otros
            $table->string('tipo_dispositivo', 30)->nullable()->after('imei');
            // codigo_equipo: código o patrón del equipo (ej: SM-A546B, A309, etc.)
            $table->string('codigo_equipo', 80)->nullable()->after('tipo_dispositivo');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn(['tipo_dispositivo', 'codigo_equipo']);
        });
    }
};