<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla principal de devoluciones
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero_devolucion')->unique();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_devolucion');
            $table->string('motivo'); // garantia, defecto, cambio_opinion, error_venta, otro
            $table->string('tipo'); // devolucion, garantia
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('tipo_reembolso')->nullable(); // efectivo, tarjeta, transferencia, nota_credito
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('numero_devolucion');
            $table->index('fecha_devolucion');
            $table->index('tenant_id');
        });

        // Detalles de la devolución (productos devueltos)
        Schema::create('devolucion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_id')->constrained('devoluciones')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('detalle_venta_id')->nullable()->constrained('detalle_ventas')->nullOnDelete();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->string('condicion')->nullable(); // nuevo, usado, dañado, incompleto
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devolucion_detalles');
        Schema::dropIfExists('devoluciones');
    }
};