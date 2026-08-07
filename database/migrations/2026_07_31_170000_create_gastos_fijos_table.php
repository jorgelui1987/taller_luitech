<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gastos_fijos')) {
            Schema::create('gastos_fijos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->decimal('monto', 10, 2);
                $table->string('categoria')->nullable();
                $table->text('descripcion')->nullable();
                $table->date('fecha')->nullable();
                $table->boolean('activo')->default(true);
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos_fijos');
    }
};