<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de cierres de caja.
     */
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');               // Cajero que abre/cierra
            $table->unsignedBigInteger('tenant_id')->nullable(); // Tenant al que pertenece
            $table->decimal('monto_inicial', 10, 2)->default(0); // Dinero en caja al abrir
            $table->timestamp('fecha_apertura')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            // Totales calculados por método de pago (ventas y reparaciones del turno)
            $table->decimal('ventas_efectivo', 10, 2)->default(0);
            $table->decimal('ventas_tarjeta', 10, 2)->default(0);
            $table->decimal('ventas_transferencia', 10, 2)->default(0);
            $table->decimal('ventas_otros', 10, 2)->default(0);  // yape, plin, mercadopago, etc.
            $table->decimal('total_ingresos', 10, 2)->default(0);

            // Egresos (devoluciones en efectivo del turno)
            $table->decimal('total_egresos', 10, 2)->default(0);

            // Cuadre de caja
            $table->decimal('total_esperado', 10, 2)->default(0); // monto_inicial + efectivo - egresos
            $table->decimal('total_contado', 10, 2)->nullable();  // Lo que el cajero cuenta físicamente
            $table->decimal('diferencia', 10, 2)->default(0);     // contado - esperado (sobrante/faltante)

            $table->string('estado', 20)->default('abierta');     // abierta | cerrada
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['tenant_id', 'estado']);
            $table->index('user_id');
            $table->index('fecha_apertura');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};