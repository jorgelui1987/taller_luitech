<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste'])->default('ajuste');
            $table->string('motivo'); // venta, compra, ajuste, devolucion, perdida, daño, sobrante
            $table->integer('cantidad'); // positiva para entradas, negativa para salidas
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->text('observacion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
    }
};