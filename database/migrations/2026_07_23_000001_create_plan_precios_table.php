<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plan_precios', function (Blueprint $table) {
            $table->id();
            $table->string('plan_key', 50)->unique(); // gratis, basico, profesional, empresarial
            $table->string('nombre', 100);
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->string('moneda', 10)->default('PEN');
            $table->string('simbolo', 10)->default('S/');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar precios por defecto
        DB::table('plan_precios')->insert([
            [
                'plan_key' => 'gratis',
                'nombre' => 'Gratis',
                'precio_mensual' => 0,
                'moneda' => 'PEN',
                'simbolo' => 'S/',
                'descripcion' => 'Para empezar',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan_key' => 'basico',
                'nombre' => 'Básico',
                'precio_mensual' => 49,
                'moneda' => 'PEN',
                'simbolo' => 'S/',
                'descripcion' => 'Para negocios pequeños',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan_key' => 'profesional',
                'nombre' => 'Profesional',
                'precio_mensual' => 99,
                'moneda' => 'PEN',
                'simbolo' => 'S/',
                'descripcion' => 'Para negocios en crecimiento',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan_key' => 'empresarial',
                'nombre' => 'Empresarial',
                'precio_mensual' => 199,
                'moneda' => 'PEN',
                'simbolo' => 'S/',
                'descripcion' => 'Para grandes tiendas',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('plan_precios');
    }
};