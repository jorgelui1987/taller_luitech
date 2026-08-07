<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reparaciones ALTER COLUMN dispositivo DROP NOT NULL');
        } else {
            Schema::table('reparaciones', function (Blueprint $table) {
                $table->string('dispositivo', 150)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reparaciones ALTER COLUMN dispositivo SET NOT NULL');
        } else {
            Schema::table('reparaciones', function (Blueprint $table) {
                $table->string('dispositivo', 150)->nullable(false)->change();
            });
        }
    }
};