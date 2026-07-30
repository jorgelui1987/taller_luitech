<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== DIAGNÓSTICO SUPERADMIN ===" . PHP_EOL;

// Buscar superadmin
$user = User::where('email', 'superadmin@admin.com')->first();

if (!$user) {
    echo "Superadmin NO EXISTE. Creándolo..." . PHP_EOL;
    
    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@admin.com',
        'password' => Hash::make('admin123'),
        'rol' => 'superadmin',
        'activo' => true,
    ]);
    
    echo "Superadmin creado exitosamente!" . PHP_EOL;
}

echo "ID: " . $user->id . PHP_EOL;
echo "Email: " . $user->email . PHP_EOL;
echo "Rol: '" . $user->rol . "'" . PHP_EOL;
echo "Activo: " . ($user->activo ? 'SI' : 'NO') . PHP_EOL;
echo "Password hash: " . $user->password . PHP_EOL;

// Verificar si el hash es válido
$testPass = 'admin123';
if (Hash::check($testPass, $user->password)) {
    echo "✓ La contraseña 'admin123' COINCIDE con el hash" . PHP_EOL;
} else {
    echo "✗ La contraseña 'admin123' NO COINCIDE. Reasignando..." . PHP_EOL;
    $user->password = Hash::make('admin123');
    $user->save();
    echo "  Contraseña reasignada a 'admin123'" . PHP_EOL;
}

// Verificar login manualmente
$credentials = ['email' => 'superadmin@admin.com', 'password' => 'admin123'];
if (auth()->attempt($credentials)) {
    echo "✓ Login EXITOSO con superadmin@admin.com / admin123" . PHP_EOL;
    auth()->logout();
} else {
