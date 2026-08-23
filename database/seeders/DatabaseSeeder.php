<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Cliente;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── SuperAdmin (accede a /superadmin/login) ──────────────────────
        // ⚠️ La contraseña NUNCA debe estar hardcodeada en el código.
        // Se define vía variable de entorno SUPERADMIN_PASSWORD.
        // En producción, configura SUPERADMIN_PASSWORD en Dokploy → Variables.
        $superAdminEmail = env('SUPERADMIN_EMAIL', 'luitechserena@gmail.com');
        $superAdminPass  = env('SUPERADMIN_PASSWORD', 'password');

        User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make($superAdminPass),
                'rol'      => 'superadmin',
                'activo'   => true,
            ]
        );

        // ── Usuarios demo (para el tenant por defecto) ────────────────────
        User::firstOrCreate(
            ['email' => 'admin@tienda.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('password'),
                'rol'      => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'vendedor@tienda.com'],
            [
                'name'     => 'Juan Vendedor',
                'password' => Hash::make('password'),
                'rol'      => 'vendedor',
            ]
        );

        User::firstOrCreate(
            ['email' => 'tecnico@tienda.com'],
            [
                'name'     => 'Carlos Técnico',
                'password' => Hash::make('password'),
                'rol'      => 'tecnico',
            ]
        );

        // ── Categorías ───────────────────────────────────────────────────
        $tenantId = \App\Models\Tenant::value('id');
        $cats = ['Smartphones', 'Tablets', 'Accesorios', 'Audífonos', 'Cargadores', 'Cases y Fundas', 'Repuestos'];
        foreach ($cats as $cat) {
            Categoria::firstOrCreate(
                ['nombre' => $cat],
                ['tenant_id' => $tenantId]
            );
        }

        // ── Marcas ───────────────────────────────────────────────────────
        $marcas = ['Samsung', 'Apple', 'Xiaomi', 'Motorola', 'Huawei', 'OPPO', 'Realme', 'OnePlus'];
        foreach ($marcas as $marca) {
            Marca::firstOrCreate(
                ['nombre' => $marca],
                ['tenant_id' => $tenantId]
            );
        }

        // ── Productos y clientes de ejemplo SOLO en desarrollo ───────────
        if (!app()->environment('production')) {
            // ── Productos de ejemplo ─────────────────────────────────────
            $productos = [
                ['codigo'=>'SAM-A54-128', 'nombre'=>'Samsung Galaxy A54', 'categoria'=>1, 'marca'=>1, 'modelo'=>'A54', 'almacenamiento'=>'128GB', 'ram'=>'8GB', 'precio_compra'=>650, 'precio_venta'=>899, 'stock'=>15],
                ['codigo'=>'SAM-S24-256', 'nombre'=>'Samsung Galaxy S24', 'categoria'=>1, 'marca'=>1, 'modelo'=>'S24', 'almacenamiento'=>'256GB', 'ram'=>'12GB', 'precio_compra'=>950, 'precio_venta'=>1299, 'stock'=>8],
                ['codigo'=>'APP-IPH15-128','nombre'=>'iPhone 15', 'categoria'=>1, 'marca'=>2, 'modelo'=>'15', 'almacenamiento'=>'128GB', 'ram'=>'6GB', 'precio_compra'=>2500, 'precio_venta'=>3499, 'stock'=>5],
                ['codigo'=>'XIA-13T-256', 'nombre'=>'Xiaomi 13T', 'categoria'=>1, 'marca'=>3, 'modelo'=>'13T', 'almacenamiento'=>'256GB', 'ram'=>'12GB', 'precio_compra'=>700, 'precio_venta'=>999, 'stock'=>12],
                ['codigo'=>'MOT-G84-256', 'nombre'=>'Motorola Moto G84', 'categoria'=>1, 'marca'=>4, 'modelo'=>'G84', 'almacenamiento'=>'256GB', 'ram'=>'12GB', 'precio_compra'=>480, 'precio_venta'=>699, 'stock'=>10],
                ['codigo'=>'AUD-SAM-TW', 'nombre'=>'Samsung Galaxy Buds2', 'categoria'=>4, 'marca'=>1, 'modelo'=>'Buds2', 'precio_compra'=>120, 'precio_venta'=>199, 'stock'=>20],
                ['codigo'=>'CAR-USB-C-65', 'nombre'=>'Cargador USB-C 65W', 'categoria'=>5, 'marca'=>3, 'precio_compra'=>18, 'precio_venta'=>35, 'stock'=>50],
                ['codigo'=>'CASE-IPH15', 'nombre'=>'Case iPhone 15 Pro', 'categoria'=>6, 'marca'=>2, 'precio_compra'=>8, 'precio_venta'=>25, 'stock'=>30],
            ];

            foreach ($productos as $p) {
                Producto::firstOrCreate(
                    ['codigo' => $p['codigo']],
                    [
                        'nombre'        => $p['nombre'],
                        'categoria_id'  => $p['categoria'] ?? 3,
                        'marca_id'      => $p['marca'],
                        'modelo'        => $p['modelo'] ?? null,
                        'almacenamiento'=> $p['almacenamiento'] ?? null,
                        'ram'           => $p['ram'] ?? null,
                        'precio_compra' => $p['precio_compra'],
                        'precio_venta'  => $p['precio_venta'],
                        'stock'         => $p['stock'],
                        'stock_minimo'  => 3,
                        'condicion'     => 'nuevo',
                    ]
                );
            }

            // ── Clientes de ejemplo (multi-país: Perú y Chile) ───────────
            $clientes = [
                ['nombre'=>'María',   'apellido'=>'García',   'email'=>'maria.garcia@gmail.com',   'telefono'=>'987654321', 'dni'=>'45123456', 'rut'=>'12345678', 'rut_dv'=>'5', 'ciudad'=>'Lima'],
                ['nombre'=>'Carlos',  'apellido'=>'López',    'email'=>'carlos.lopez@gmail.com',    'telefono'=>'965432187', 'dni'=>'32145678', 'rut'=>'87654321', 'rut_dv'=>'0', 'ciudad'=>'Lima'],
                ['nombre'=>'Ana',     'apellido'=>'Martínez', 'email'=>'ana.martinez@hotmail.com',  'telefono'=>'974561230', 'dni'=>'56789012', 'rut'=>'11111111', 'rut_dv'=>'1', 'ciudad'=>'Santiago'],
                ['nombre'=>'Pedro',   'apellido'=>'Sánchez',  'email'=>'pedro.sanchez@outlook.com', 'telefono'=>'912345678', 'dni'=>'78901234', 'rut'=>'22222222', 'rut_dv'=>'2', 'ciudad'=>'Santiago'],
                ['nombre'=>'Lucía',   'apellido'=>'Torres',   'email'=>null,                         'telefono'=>'998765432', 'dni'=>'89012345', 'rut'=>'33333333', 'rut_dv'=>'3', 'ciudad'=>'Valparaíso'],
            ];

            foreach ($clientes as $c) {
                Cliente::firstOrCreate(
                    ['dni' => $c['dni']],
                    array_merge($c, ['tipo'=>'particular'])
                );
            }

            // Datos demo: ventas, reparaciones (solo desarrollo)
            $this->call(DemoDataSeeder::class);
        }
    }
}
