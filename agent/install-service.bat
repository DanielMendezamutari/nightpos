@echo off
setlocal
cd /d "%~dp0"

echo NightPOS Print Agent - Instalacion de servicio Windows
echo.
echo Requiere ejecutar como Administrador.
echo.

if not exist "NightPOSPrintAgent.exe" (
  echo ERROR: No se encuentra NightPOSPrintAgent.exe en esta carpeta.
  echo Compile primero con: powershell -ExecutionPolicy Bypass -File build.ps1
  exit /b 1
)

NightPOSPrintAgent.exe --install
if errorlevel 1 exit /b 1

echo.
echo Servicio instalado. Edite la configuracion:
echo   %ProgramData%\NightPOS\PrintAgent\config.json
echo.
echo Luego reinicie:
echo   restart-service.bat
echo.
pause
