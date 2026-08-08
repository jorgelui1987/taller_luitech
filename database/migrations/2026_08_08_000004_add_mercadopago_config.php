<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->boolean('mercadopago_activo')->default(false)->after('certificado_password');
            $table->string('mercadopago_public_key')->nullable()->after('mercadopago_activo');
            $table->string('mercadopago_access_token')->nullable()->after('mercadopago_public_key');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn(['mercadopago_activo', 'mercadopago_public_key', 'mercadopago_access_token']);
        });
    }
};