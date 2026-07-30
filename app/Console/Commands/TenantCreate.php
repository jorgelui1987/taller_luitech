<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create
                            {--empresa= : Nombre de la empresa}
                            {--subdominio= : Subdominio único (ej: mitienda)}
                            {--email= : Email del administrador}
                            {--password= : Contraseña del admin}
                            {--plan=gratis : Plan (gratis, basico, profesional, empresarial)}
                            {--max-usuarios=3 : Máximo de usuarios permitidos}
                            {--max-productos=50 : Máximo de productos permitidos}';

    protected $description = 'Crea un nuevo tenant (cliente SaaS) con su administrador y configuración inicial';

    public function handle(): int
    {
        $data = [
            'empresa'       => $this->option('empresa') ?? $this->ask('Nombre de la empresa'),
            'subdominio'    => $this->option('subdominio'),
            'email'         => $this->option('email') ?? $this->ask('Email del administrador'),
            'password'      => $this->option('password') ?? $this->secret('Contraseña del admin (mín 8 caracteres)'),
            'plan'          => $this->option('plan'),
            'max_usuarios'  => (int) $this->option('max-usuarios'),
            'max_productos' => (int) $this->option('max-productos'),
        ];

        // Auto-generar subdominio si no se proporcionó
        if (!$data['subdominio']) {
            $data['subdominio'] = \Illuminate\Support\Str::slug($data['empresa'], '-');
            $data['subdominio'] = preg_replace('/[^a-z0-9-]/', '', strtolower($data['subdominio']));
            $data['subdominio'] = substr($data['subdominio'], 0, 50);
            $original = $data['subdominio'];
            $counter = 1;
            while (\App\Models\Tenant::where('subdominio', $data['subdominio'])->exists()) {
                $data['subdominio'] = $original . '-' . $counter;
                $counter++;
            }
            $this->line("  Subdominio auto-generado: {$data['subdominio']}");
        }

        $validator = Validator::make($data, [
            'empresa'       => 'required|string|max:255|unique:tenants,empresa',
            'subdominio'    => 'required|string|max:50|unique:tenants,subdominio|regex:/^[a-z0-9-]+$/',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8',
            'plan'          => 'required|in:gratis,basico,profesional,empresarial',
            'max_usuarios'  => 'required|integer|min:1',
            'max_productos' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error("✗ {$error}");
            }
            return Command::FAILURE;
        }

        try {
            DB::transaction(function () use ($data) {
                $tenant = Tenant::create([
                    'empresa'          => $data['empresa'],
                    'subdominio'       => $data['subdominio'],
                    'email_contacto'   => $data['email'],
                    'plan'             => $data['plan'],
                    'estado'           => 'activo',
                    'max_usuarios'     => $data['max_usuarios'],
                    'max_productos'    => $data['max_productos'],
                ]);

                // Admin del tenant
                $user = User::create([
                    'name'      => 'Administrador',
                    'email'     => $data['email'],
                    'password'  => Hash::make($data['password']),
                    'rol'       => 'admin',
                    'activo'    => true,
                    'tenant_id' => $tenant->id,
                ]);

                // Configuración inicial
                Configuracion::create([
                    'nombre_tienda'  => $data['empresa'],
                    'igv'            => 18.00,
                    'moneda'         => 'PEN',
                    'simbolo_moneda' => 'S/',
                    'tenant_id'      => $tenant->id,
                ]);

                $this->info("✓ Tenant '{$tenant->empresa}' creado exitosamente!");
                $this->line("  Subdominio: {$tenant->subdominio}");
                $this->line("  Admin email: {$user->email}");
                $this->line("  Plan: {$tenant->plan}");
                $this->line("  URL: http://{$tenant->subdominio}.tudominio.com");
            });

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("✗ Error al crear tenant: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}