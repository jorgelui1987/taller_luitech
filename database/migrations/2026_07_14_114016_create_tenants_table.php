<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('empresa')->unique();
            $table->string('subdominio')->unique();
            $table->string('dominio')->nullable()->unique();
            $table->string('email_contacto');
            $table->string('telefono_contacto')->nullable();
            $table->string('plan')->default('gratis'); // gratis, basico, profesional, empresarial
            $table->enum('estado', ['activo', 'suspendido', 'cancelado'])->default('activo');
            $table->string('logo')->nullable();
            $table->string('pais')->nullable();
            $table->string('moneda')->default('PEN');
            $table->string('simbolo_moneda')->default('S/');
            $table->decimal('impuesto', 5, 2)->default(18.00);
            $table->integer('max_usuarios')->default(5);
            $table->integer('max_productos')->default(100);
            $table->timestamp('fecha_expiracion')->nullable();
            $table->json('configuracion_extra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};