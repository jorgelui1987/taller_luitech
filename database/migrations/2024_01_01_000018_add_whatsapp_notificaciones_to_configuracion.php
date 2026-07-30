<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('whatsapp_notificaciones')->nullable()->after('whatsapp')
                  ->comment('Número para recibir notificaciones de stock bajo vía WhatsApp');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn('whatsapp_notificaciones');
        });
    }
};