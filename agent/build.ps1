# Build NightPOSPrintAgent.exe (Windows)
# Requiere Go 1.22+ — https://go.dev/dl/

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "Downloading modules..."
go mod tidy

Write-Host "Building CLI exe (install/status)..."
go build -ldflags "-s -w" -o NightPOSPrintAgent.exe .

if ($LASTEXITCODE -ne 0) { exit 1 }

Write-Host "OK: NightPOSPrintAgent.exe"
Write-Host ""
Write-Host "Instalar (Administrador):"
Write-Host "  .\NightPOSPrintAgent.exe --install"
Write-Host ""
Write-Host "Config:"
Write-Host "  $env:ProgramData\NightPOS\PrintAgent\config.json"
