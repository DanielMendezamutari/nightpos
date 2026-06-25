@echo off
setlocal
cd /d "%~dp0"

echo NightPOS Print Agent - Desinstalar servicio
echo.

if exist "NightPOSPrintAgent.exe" (
  NightPOSPrintAgent.exe --uninstall
) else if exist "%ProgramFiles%\NightPOS\PrintAgent\NightPOSPrintAgent.exe" (
  "%ProgramFiles%\NightPOS\PrintAgent\NightPOSPrintAgent.exe" --uninstall
) else (
  echo ERROR: No se encuentra NightPOSPrintAgent.exe
  exit /b 1
)

echo Datos conservados en %ProgramData%\NightPOS\PrintAgent\
pause
