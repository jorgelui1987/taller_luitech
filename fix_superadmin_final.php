<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('rol', 'superadmin')->first();
if (!$user) {
    echo "No superadmin found. Creating one...\n";
    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'camila1987chile@gmail.com',
        'password' => Hash::make('Castro16@'),
        'rol' => 'superadmin',
        'activo' => true,
    ]);
}

$user->email = 'camila1987chile@gmail.com';
$user->password = Hash::make('Castro16@');
$user->save();

echo "Email: " . $user->email . "\n";
echo "Password set to: Castro16@\n";
echo "Hash: " . $user->password . "\n";
echo "DONE\n";