<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Usar DB::statement para compatibilidad con MariaDB
        // (el método ->change() puede fallar en MariaDB con columnas foreign key)
        DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NOT NULL');
    }
};