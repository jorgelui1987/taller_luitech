# =============================================================
#  SMOKE TEST DE PRODUCCIÓN — Taller Luitech
# =============================================================
#  Verifica que las páginas clave respondan correctamente
#  después de cada despliegue en Dokploy.
#
#  USO:
#    .\smoke-test-produccion.ps1 -Url https://tu-dominio.com
# =============================================================

param(
    [Parameter(Mandatory = $true)]
    [string]$Url
)

# Quitar barra final
$Url = $Url.TrimEnd('/')

$pass = 0
$fail = 0
$resultados = @()

function Probar-Ruta {
    param([string]$Nombre, [string]$Ruta, [int[]]$CodigosOk, [int]$TimeoutSec = 20)

    $full = if ($Ruta -eq '/') { $Url } else { "$Url$Ruta" }
    try {
        $sw = [System.Diagnostics.Stopwatch]::StartNew()
        $resp = Invoke-WebRequest -Uri $full -Method GET -MaximumRedirection 5 `
            -TimeoutSec $TimeoutSec -UseBasicParsing -ErrorAction Stop
        $sw.Stop()
        $ms = $sw.ElapsedMilliseconds

        if ($CodigosOk -contains $resp.StatusCode) {
            $script:pass++
            $resultados += [PSCustomObject]@{ Estado = "OK  "; Ruta = $Ruta; Codigo = $resp.StatusCode; Tiempo = "$ms ms" }
        } else {
            $script:fail++
            $resultados += [PSCustomObject]@{ Estado = "FAIL"; Ruta = $Ruta; Codigo = $resp.StatusCode; Tiempo = "$ms ms" }
        }
    } catch {
        $codigo = try { $_.Exception.Response.StatusCode.value__ } catch { 'ERR' }
        if ($CodigosOk -contains $codigo) {
            $script:pass++
            $resultados += [PSCustomObject]@{ Estado = "OK  "; Ruta = $Ruta; Codigo = $codigo; Tiempo = "-" }
        } else {
            $script:fail++
            $resultados += [PSCustomObject]@{ Estado = "FAIL"; Ruta = $Ruta; Codigo = $codigo; Tiempo = "-" }
        }
    }
}

Write-Host ""
Write-Host "=== SMOKE TEST — $Url ===" -ForegroundColor Cyan
Write-Host ""

# ── Páginas públicas ──
Probar-Ruta "Landing / inicio"          "/"                      @(200, 302)
Probar-Ruta "Login principal"           "/login"                 @(200)
Probar-Ruta "Login superadmin"          "/superadmin/login"      @(200)

# ── Protección: sin sesión deben redirigir (302) o bloquear ──
Probar-Ruta "Dashboard protegido"       "/dashboard"             @(302, 401, 403)
Probar-Ruta "Ventas protegido"          "/ventas"                @(302, 401, 403)
Probar-Ruta "Backup protegido"          "/backup"                @(302, 401, 403)
Probar-Ruta "Configuración protegida"   "/configuracion"         @(302, 401, 403)

# ── Recuperación de superadmin DESACTIVADA por defecto ──
Probar-Ruta "Recuperación SA desactivada" "/recuperar-superadmin/x" @(404, 302)

# ── Webhook Mercado Pago (debe existir; sin firma puede dar 200 o 401) ──
Probar-Ruta "Webhook Mercado Pago"      "/webhooks/mercadopago"  @(200, 401, 405, 419)

# ── Ruta inexistente debe dar 404 (manejo de errores OK) ──
Probar-Ruta "404 en ruta inexistente"   "/esta-ruta-no-existe"   @(404)

# ── Resultados ──
Write-Host ""
$resultados | Format-Table -AutoSize

Write-Host ""
if ($fail -eq 0) {
    Write-Host "✅ TODO OK: $pass verificaciones pasaron. El despliegue responde correctamente." -ForegroundColor Green
} else {
    Write-Host "❌ ATENCIÓN: $fail de $($pass + $fail) verificaciones FALLARON. Revisar logs en Dokploy:" -ForegroundColor Red
    Write-Host "   tail -50 /var/www/html/storage/logs/laravel.log" -ForegroundColor Yellow
}
Write-Host ""