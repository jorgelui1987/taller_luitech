<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$count = User::where('rol', 'superadmin')->count();
echo "Superadmins existentes: " . $count . PHP_EOL;

if ($count == 0) {
    User::create([
        'name' => 'Super Admin',
        'email' => 'camila1987chile@gmail.com',
        'password' => bcrypt('Castro16@'),
        'rol' => 'superadmin',
        'activo' => true,
    ]);
    echo "Superadmin creado exitosamente!" . PHP_EOL;
    echo "Email: camila1987chile@gmail.com" . PHP_EOL;
    echo "Password: Castro16@" . PHP_EOL;
} else {
    echo "El superadmin ya existe." . PHP_EOL;
}