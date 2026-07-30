<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modificar el ENUM de la columna 'rol' para incluir 'superadmin'
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'vendedor', 'tecnico', 'superadmin') NOT NULL DEFAULT 'admin'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'vendedor', 'tecnico') NOT NULL DEFAULT 'admin'");
    }
};