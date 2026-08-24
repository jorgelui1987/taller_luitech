<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            // 'superadmin' incluido desde el inicio para que instalaciones
            // nuevas (y los tests) coincidan con la migración
            // 2026_07_14_114613_add_superadmin_role_to_users.
            $table->enum('rol', ['admin', 'vendedor', 'tecnico', 'superadmin'])->default('vendedor');
            $table->string('telefono')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
