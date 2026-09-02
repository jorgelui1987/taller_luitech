<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-rol: permite que un usuario tenga varios roles
     * (ej. ['tecnico','vendedor'] para talleres pequeños).
     * Siembra la columna con el rol único existente (retrocompatibilidad).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('rol');
        });

        $users = DB::table('users')->select('id', 'rol')->get();

        foreach ($users as $user) {
            $roles = !empty($user->rol) ? [$user->rol] : [];

            DB::table('users')
                ->where('id', $user->id)
                ->update(['roles' => json_encode(array_values($roles))]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
