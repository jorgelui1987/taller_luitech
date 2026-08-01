<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('configuracion', 'decimales')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->integer('decimales')->default(0)->after('simbolo_moneda');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('configuracion', 'decimales')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->dropColumn('decimales');
            });
        }
    }
};