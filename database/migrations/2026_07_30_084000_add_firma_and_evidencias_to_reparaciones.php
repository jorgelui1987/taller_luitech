<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            // Firma digital del cliente (Base64)
            $table->longText('firma_cliente')->nullable()->after('notas');
            $table->dateTime('fecha_firma')->nullable()->after('firma_cliente');

            // Evidencias fotográficas (JSON con rutas de imágenes)
            $table->json('evidencias')->nullable()->after('fecha_firma');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn(['firma_cliente', 'fecha_firma', 'evidencias']);
        });
    }
};