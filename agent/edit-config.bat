@echo off
setlocal

set CONFIG_DIR=%ProgramData%\NightPOS\PrintAgent
set CONFIG=%CONFIG_DIR%\config.json

if not exist "%CONFIG_DIR%" mkdir "%CONFIG_DIR%" >nul 2>&1

if not exist "%CONFIG%" (
  echo { > "%CONFIG%"
  echo   "backend_url": "https://tu-dominio-nightpos.com/api/v1", >> "%CONFIG%"
  echo   "device_key": "npd_live_REEMPLAZAR", >> "%CONFIG%"
  echo   "printer_name": "CAJA", >> "%CONFIG%"
  echo   "poll_interval_ms": 1500, >> "%CONFIG%"
  echo   "dry_run": false, >> "%CONFIG%"
  echo   "dry_run_dir": "%ProgramData%\\NightPOS\\PrintAgent\\dry-run-output", >> "%CONFIG%"
  echo   "log_level": "info" >> "%CONFIG%"
  echo } >> "%CONFIG%"
)

start "" notepad.exe "%CONFIG%"
