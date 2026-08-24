<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Compatible con PostgreSQL, MySQL/MariaDB y SQLite
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ventas ALTER COLUMN cliente_id DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            Schema::table('ventas', function (Blueprint $table) {
                $table->unsignedBigInteger('cliente_id')->nullable()->change();
            });
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
        } elseif ($driver === 'sqlite') {
            Schema::table('ventas', function (Blueprint $table) {
                $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
            });
        } else {
            // MySQL / MariaDB
            DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
