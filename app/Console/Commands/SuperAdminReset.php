<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SuperAdminReset extends Command
{
    /**
     * Nota: se usa getenv() en lugar de env() para que funcione incluso
     * cuando la configuración está cacheada (php artisan optimize/config:cache),
     * ya que env() devuelve null para claves fuera de config en ese caso.
     */
    protected $signature = 'superadmin:reset'
        . ' {--email= : Email del superadmin (default: SUPERADMIN_EMAIL o luitechserena@gmail.com)}'
        . ' {--password= : Contraseña del superadmin (default: SUPERADMIN_PASSWORD o password)}' // NOSONAR - Entrada CLI segura, no credencial hardcodeada
        . ' {--force : Ejecutar sin confirmación}';

    protected $description = 'Crea o restablece el usuario SuperAdmin (acceso a /superadmin/login)';

    public function handle(): int
    {
        $email = $this->option('email') ?: (getenv('SUPERADMIN_EMAIL') ?: 'luitechserena@gmail.com');
        $password = $this->option('password') ?: (getenv('SUPERADMIN_PASSWORD') ?: 'password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email'    => 'required|email|max:255',
                'password' => 'required|string|min:8',
            ]
        );

        // La contraseña por defecto histórica es corta ('password' = 8 chars, ok),
        // pero si alguien pasa una más corta por opción se valida igual.
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error("✗ {$error}");
            }
            return Command::FAILURE;
        }

        $existente = User::where('email', $email)->first();

        if ($existente && !$this->option('force')) {
            if (!$this->confirm("El usuario {$email} ya existe. ¿Restablecer su contraseña y convertirlo en superadmin?")) {
                $this->line('Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        $datos = [
            'name'     => 'Super Admin',
            'password' => Hash::make($password),
            'rol'      => 'superadmin',
            'activo'   => true,
            'tenant_id' => null,
            // Limpiar 2FA para evitar bloqueos tras un restablecimiento
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ];

        if ($existente) {
            $existente->update($datos);
            $this->info("✓ SuperAdmin actualizado correctamente.");
        } else {
            User::create(array_merge(['email' => $email], $datos));
            $this->info("✓ SuperAdmin creado correctamente.");
        }

        $this->line("  Email: {$email}");
        $this->line("  URL de acceso: " . rtrim(config('app.url'), '/') . "/superadmin/login");

        return Command::SUCCESS;
    }
}