@echo off
title Iniciar RiberResto POS - Modo Impresion Directa Silenciosa (Kiosk Printing)
echo ======================================================================
echo           RIBERSOFT BOLIVIA - RIBERRESTO POS
echo    Iniciando Punto de Venta con Impresion Termica Directa
echo ======================================================================
echo.
echo Desactivando vista previa de impresion para salida directa a la termica...
echo.

REM Buscar Google Chrome
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk-printing --app=http://localhost:5173
    exit
)

if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" --kiosk-printing --app=http://localhost:5173
    exit
)

REM Buscar Microsoft Edge si Chrome no est?? instalado
if exist "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" (
    start "" "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" --kiosk-printing --app=http://localhost:5173
    exit
)

echo No se encontro Chrome o Edge en las rutas estandar.
echo Abriendo en el navegador predeterminado...
start http://localhost:5173
