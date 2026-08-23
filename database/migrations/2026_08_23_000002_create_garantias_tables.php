<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            $table->string('numero_garantia')->unique();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_garantia');
            $table->string('motivo')->default('garantia');
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->text('observacion')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();
            $table->index(['venta_id', 'estado']);
            $table->index('tenant_id');
        });

        Schema::create('garantia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garantia_id')->constrained('garantias')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('detalle_venta_id')->constrained('detalle_ventas')->cascadeOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('condicion')->default('nuevo');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();
            $table->index(['garantia_id', 'producto_id']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garantia_detalles');
        Schema::dropIfExists('garantias');
    }
};