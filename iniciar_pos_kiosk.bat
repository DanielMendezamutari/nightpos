@echo off
title Iniciar RiberResto POS - Modo Impresion Directa Silenciosa (Kiosk Printing)
echo ======================================================================
echo           RIBERSOFT BOLIVIA - RIBERRESTO POS
echo    Iniciando Punto de Venta con Impresion Termica Directa
echo ======================================================================
echo.
echo Desactivando vista previa de impresion para salida directa a la termica...
echo Usando perfil POS dedicado para aislar el proceso de impresion directa...
echo.

set POS_PROFILE=%LOCALAPPDATA%\RiberResto_POS_Profile
if not exist "%POS_PROFILE%" mkdir "%POS_PROFILE%"

REM Buscar Google Chrome
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk-printing --user-data-dir="%POS_PROFILE%" --app=http://localhost:5173
    exit
)

if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" --kiosk-printing --user-data-dir="%POS_PROFILE%" --app=http://localhost:5173
    exit
)

REM Buscar Microsoft Edge si Chrome no est?? instalado
if exist "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" (
    start "" "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" --kiosk-printing --user-data-dir="%POS_PROFILE%" --app=http://localhost:5173
    exit
)

start http://localhost:5173