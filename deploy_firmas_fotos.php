<?php
/**
 * Script de actualización - Firmas y Fotos de Evidencia
 * 
 * INSTRUCCIONES:
 * 1. Sube este archivo a la raíz de tu proyecto en el servidor (donde está artisan)
 * 2. Ejecútalo desde el navegador: https://tudominio.com/deploy_firmas_fotos.php
 * 3. Borra este archivo después de ejecutarlo por seguridad
 */

echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Deploy - Firmas y Fotos</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#1a1a2e;color:#fff;}.ok{color:#10b981;}.err{color:#ef4444;}.cmd{background:#16213e;padding:10px;border-radius:8px;margin:5px 0;font-family:monospace;}</style></head><body>";
echo "<h1>🚀 Actualizando Firmas y Fotos de Evidencia</h1>";
echo "<hr>";

function ejecutar($comando) {
    echo "<div class='cmd'>📌 <strong>\$ {$comando}</strong></div>";
    ob_flush(); flush();
    $output = [];
    $returnVar = 0;
    exec($comando . " 2>&1", $output, $returnVar);
    foreach ($output as $linea) {
        echo "<div>" . htmlspecialchars($linea) . "</div>";
    }
    if ($returnVar === 0) {
        echo "<div class='ok'>✅ Comando ejecutado correctamente</div>";
    } else {
        echo "<div class='err'>❌ Error (código: {$returnVar})</div>";
    }
    echo "<br>";
    ob_flush(); flush();
    return $returnVar === 0;
}

// 1. Git pull
echo "<h2>📥 1. Bajando cambios de GitHub...</h2>";
ejecutar("git pull origin main 2>&1");

// 2. Migraciones
echo "<h2>🗄️ 2. Ejecutando migraciones...</h2>";
ejecutar("php artisan migrate --force 2>&1");

// 3. Storage link
echo "<h2>🔗 3. Creando enlace de storage...</h2>";
ejecutar("php artisan storage:link 2>&1");

// 4. Crear directorios
echo "<h2>📁 4. Creando directorios para firmas y fotos...</h2>";
ejecutar("mkdir -p storage/app/public/firmas 2>&1");
ejecutar("mkdir -p storage/app/public/reparaciones/fotos 2>&1");

// 5. Limpiar cachés
echo "<h2>🧹 5. Limpiando cachés...</h2>";
ejecutar("php artisan route:clear 2>&1");
ejecutar("php artisan config:clear 2>&1");
ejecutar("php artisan cache:clear 2>&1");
ejecutar("php artisan view:clear 2>&1");

echo "<hr>";
echo "<h2 class='ok'>✅ ¡Actualización completada!</h2>";
echo "<p>Ya deberían funcionar las firmas digitales y las fotos de evidencia.</p>";
echo "<p><strong>⚠️ IMPORTANTE: Borra este archivo del servidor por seguridad.</strong></p>";
echo "</body></html>";