@echo off
setlocal
cd /d "%~dp0"

where go >nul 2>&1
if errorlevel 1 (
  echo ERROR: Go no esta instalado o no esta en el PATH.
  echo.
  echo Instale Go 1.22+ desde: https://go.dev/dl/
  echo Luego cierre y abra PowerShell/CMD e intente de nuevo.
  echo.
  pause
  exit /b 1
)

echo Downloading modules...
go mod tidy
if errorlevel 1 exit /b 1

echo Building NightPOSPrintAgent.exe...
go build -ldflags "-s -w" -o NightPOSPrintAgent.exe .
if errorlevel 1 exit /b 1

echo.
echo OK: NightPOSPrintAgent.exe
echo.
echo Instalar ^(Administrador^):
echo   install-service.bat
echo.
echo Config:
echo   %ProgramData%\NightPOS\PrintAgent\config.json
echo.
pause
