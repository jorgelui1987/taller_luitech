<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $pdo = DB::connection()->getPdo();
    
    // Check current max_allowed_packet
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'Current max_allowed_packet: ' . $row['Value'] . ' bytes (' . round($row['Value']/1024/1024, 2) . ' MB)' . PHP_EOL;
    
    // Increase max_allowed_packet to 64MB
    $pdo->exec("SET GLOBAL max_allowed_packet = 67108864");
    echo 'max_allowed_packet increased to 64MB!' . PHP_EOL;
    
    // Verify
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'New max_allowed_packet: ' . $row['Value'] . ' bytes (' . round($row['Value']/1024/1024, 2) . ' MB)' . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}