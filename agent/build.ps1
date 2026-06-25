# Build NightPOSPrintAgent.exe (Windows)
# Requiere Go 1.22+ — https://go.dev/dl/
#
# Si PowerShell bloquea scripts, use una de estas opciones:
#   build.bat
#   powershell -ExecutionPolicy Bypass -File .\build.ps1

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

if (-not (Get-Command go -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: Go no esta instalado o no esta en el PATH." -ForegroundColor Red
    Write-Host ""
    Write-Host "Instale Go 1.22+ desde: https://go.dev/dl/"
    Write-Host "Luego cierre y abra la terminal e intente de nuevo."
    Write-Host ""
    Write-Host "Alternativa sin politica de scripts: .\build.bat"
    exit 1
}

Write-Host "Downloading modules..."
go mod tidy

Write-Host "Building NightPOSPrintAgent.exe..."
go build -ldflags "-s -w" -o NightPOSPrintAgent.exe .

if ($LASTEXITCODE -ne 0) { exit 1 }

Write-Host ""
Write-Host "OK: NightPOSPrintAgent.exe" -ForegroundColor Green
Write-Host ""
Write-Host "Instalar (Administrador):"
Write-Host "  .\install-service.bat"
Write-Host ""
Write-Host "Config:"
Write-Host "  $env:ProgramData\NightPOS\PrintAgent\config.json"
