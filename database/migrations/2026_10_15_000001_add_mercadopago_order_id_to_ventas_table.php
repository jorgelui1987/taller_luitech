<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // ID de la orden MP Point (Orders API v1/orders) para poder
            // consultar el estado del cobro aunque el webhook no llegue
            // (ej. entorno local sin URL pública) y así confirmar el pago
            // e imprimir la boleta automáticamente.
            $table->string('mercadopago_order_id')->nullable()->after('referencia_pago');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('mercadopago_order_id');
        });
    }
};
