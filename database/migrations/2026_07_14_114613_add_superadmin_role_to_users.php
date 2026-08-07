<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Compatible con MySQL/MariaDB y PostgreSQL
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_rol_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_rol_check CHECK (rol IN ('admin', 'vendedor', 'tecnico', 'superadmin'))");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'vendedor', 'tecnico', 'superadmin') NOT NULL DEFAULT 'admin'");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_rol_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_rol_check CHECK (rol IN ('admin', 'vendedor', 'tecnico'))");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'vendedor', 'tecnico') NOT NULL DEFAULT 'admin'");
        }
    }
};
