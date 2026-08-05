<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('numero_factura', 50)->unique();
            $table->string('plan', 50);
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 10)->default('PEN');
            $table->string('estado', 20)->default('pendiente'); // pendiente, pagada, vencida, cancelada
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->string('metodo_pago', 50)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};