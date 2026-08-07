<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Desactivar el superadmin antiguo
        DB::table('users')
            ->where('email', 'camila1987chile@gmail.com')
            ->where('rol', 'superadmin')
            ->update(['activo' => false]);

        // Crear o actualizar el nuevo superadmin
        $exists = DB::table('users')->where('email', 'luitechserena@gmail.com')->exists();

        if ($exists) {
            DB::table('users')
                ->where('email', 'luitechserena@gmail.com')
                ->update([
                    'name' => 'Super Admin',
                    'password' => Hash::make('Castro161219@'),
                    'rol' => 'superadmin',
                    'activo' => true,
                ]);
        } else {
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => 'luitechserena@gmail.com',
                'password' => Hash::make('Castro161219@'),
                'rol' => 'superadmin',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Revertir: reactivar el antiguo superadmin
        DB::table('users')
            ->where('email', 'camila1987chile@gmail.com')
            ->where('rol', 'superadmin')
            ->update(['activo' => true]);

        // Opcionalmente eliminar el nuevo si fue creado
        DB::table('users')
            ->where('email', 'luitechserena@gmail.com')
            ->where('rol', 'superadmin')
            ->update(['activo' => false]);
    }
};