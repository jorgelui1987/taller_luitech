<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Compatible con MySQL/MariaDB y PostgreSQL
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ventas ALTER COLUMN cliente_id DROP NOT NULL');
        } else {
            // MySQL / MariaDB
            DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ventas ALTER COLUMN cliente_id SET NOT NULL');
        } else {
            // MySQL / MariaDB
            DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
