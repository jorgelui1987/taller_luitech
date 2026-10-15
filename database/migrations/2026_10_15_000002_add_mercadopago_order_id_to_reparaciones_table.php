<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            // Orden MP Point para cobro directo de reparación (igual que
            // ventas.mercadopago_order_id): permite consultar el estado por
            // polling y crear la venta + imprimir al aprobarse el pago.
            $table->string('mercadopago_order_id')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn('mercadopago_order_id');
        });
    }
};
