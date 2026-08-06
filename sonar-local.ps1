# Script para análisis local con SonarQube
# Requisitos: SonarQube corriendo en http://localhost:9000 y sonar-scanner instalado
# Uso: .\sonar-local.ps1 -Token "tu_token"

param(
    [Parameter(Mandatory=$true)]
    [string]$Token
)

$ProjectKey = "taller_luitech-main"
$HostUrl = "http://localhost:9000"

Write-Host "=== Análisis SonarQube Local ===" -ForegroundColor Cyan
Write-Host "Proyecto: $ProjectKey"
Write-Host "Host: $HostUrl"
Write-Host ""

# Verificar que sonar-scanner esté disponible
$scanner = Get-Command sonar-scanner -ErrorAction SilentlyContinue
if (-not $scanner) {
    Write-Host "ERROR: sonar-scanner no encontrado en PATH." -ForegroundColor Red
    Write-Host "Instálalo desde: https://docs.sonarqube.org/latest/analysis/scan/sonarscanner/" -ForegroundColor Yellow
    exit 1
}

Write-Host "sonar-scanner encontrado: $($scanner.Source)" -ForegroundColor Green
Write-Host ""

# Ejecutar el análisis (usa sonar-project.properties para fuentes y exclusiones)
Write-Host "Ejecutando análisis..." -ForegroundColor Cyan
sonar-scanner.bat `
    -D"sonar.projectKey=$ProjectKey" `
    -D"sonar.host.url=$HostUrl" `
    -D"sonar.token=$Token"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "=== Análisis completado exitosamente ===" -ForegroundColor Green
    Write-Host "Revisa los resultados en: $HostUrl/dashboard?id=$ProjectKey" -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "=== El análisis falló con código de salida: $LASTEXITCODE ===" -ForegroundColor Red
    exit $LASTEXITCODE
}
</｜DSML｜tool>
<task_progress>
- [x] Revisar el workflow de SonarCloud
- [x] Revisar sonar-project.properties
- [x] Identificar la causa del error del escáner
- [x] Corregir la configuración
- [x] Verificar la estructura del repositorio
- [x] Crear script de análisis local
- [ ] Verificar estado final
</task_progress>
</｜DSML｜tool>