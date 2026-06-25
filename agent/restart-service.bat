@echo off
setlocal
cd /d "%~dp0"

if exist "NightPOSPrintAgent.exe" (
  NightPOSPrintAgent.exe --restart
) else if exist "%ProgramFiles%\NightPOS\PrintAgent\NightPOSPrintAgent.exe" (
  "%ProgramFiles%\NightPOS\PrintAgent\NightPOSPrintAgent.exe" --restart
) else (
  echo ERROR: No se encuentra NightPOSPrintAgent.exe
  exit /b 1
)

echo Servicio reiniciado.
pause
