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

        // ⚠️ La contraseña NUNCA debe estar hardcodeada en el código.
        // Se define vía variable de entorno SUPERADMIN_PASSWORD.
        // En producción, configura SUPERADMIN_PASSWORD en Dokploy → Variables.
        $superAdminEmail = env('SUPERADMIN_EMAIL', 'luitechserena@gmail.com');
        $superAdminPass  = env('SUPERADMIN_PASSWORD', 'password');

        // Crear o actualizar el nuevo superadmin
        $exists = DB::table('users')->where('email', $superAdminEmail)->exists();

        if ($exists) {
            DB::table('users')
                ->where('email', $superAdminEmail)
                ->update([
                    'name' => 'Super Admin',
                    'password' => Hash::make($superAdminPass),
                    'rol' => 'superadmin',
                    'activo' => true,
                ]);
        } else {
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => $superAdminEmail,
                'password' => Hash::make($superAdminPass),
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
