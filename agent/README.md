# NightPOS Print Agent (Windows EXE)

**Ribersoft — NightPOS V1**  
Agente de impresión local: un solo ejecutable Go, servicio Windows nativo, impresión térmica USB ESC/POS.

---

## Documentación oficial (técnicos Ribersoft)

| Documento | Para qué sirve |
|-----------|----------------|
| **[INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md)** | Instalación paso a paso (desarrollo, producción, sucursales) |
| **[TROUBLESHOOTING_GUIDE.md](./TROUBLESHOOTING_GUIDE.md)** | Solución de problemas + FAQ |
| **[DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md)** | Checklist de despliegue en nueva sucursal |
| [README_WINDOWS.md](./README_WINDOWS.md) | Resumen operativo rápido |
| [WINDOWS_SERVICE_INSTALLATION_REPORT.md](./WINDOWS_SERVICE_INSTALLATION_REPORT.md) | Notas técnicas del servicio |

> **Empezar aquí:** [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md)

---

## Resumen del componente

- Servicio Windows (`NightPOSPrintAgent`) — inicio automático, sin consola
- Bandeja del sistema (🟢 conectado / 🟡 sin red / 🔴 error impresora)
- Polling backend ~1,5 s → heartbeat → cola `print_jobs` → impresión RAW
- Config y logs en `C:\ProgramData\NightPOS\PrintAgent\`
- **No requiere** Node.js, .NET ni NSSM

---

## Compilar (solo desarrollo / nueva versión)

Requisito: Go 1.22+ en PATH.

```powershell
cd C:\xampp\htdocs\nightpos\agent
.\build.bat
```

Alternativa: `go build -ldflags "-s -w" -o NightPOSPrintAgent.exe .`

Genera `NightPOSPrintAgent.exe` en esta carpeta.

---

## Instalar en sucursal (resumen)

1. **Administrador:** `.\install-service.bat` o `.\NightPOSPrintAgent.exe --install`
2. Editar `C:\ProgramData\NightPOS\PrintAgent\config.json`
3. Registrar dispositivo en NightPOS → copiar `device_key`
4. `.\restart-service.bat`
5. Verificar Online + Probar impresión

Detalle completo: [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md)

---

## Comandos CLI

| Comando | Descripción |
|---------|-------------|
| `--install` | Instala servicio + bandeja |
| `--uninstall` | Desinstala servicio |
| `--start` / `--stop` / `--restart` | Control del servicio |
| `--status` | Estado RUNNING/STOPPED + rutas |
| `--run` | Modo consola (debug) |
| `--dry-run` | Consola sin imprimir (archivos en dry-run-output) |

---

## Rutas importantes

| Qué | Ruta |
|-----|------|
| EXE instalado | `C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe` |
| **Config activa** | `C:\ProgramData\NightPOS\PrintAgent\config.json` |
| Logs | `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log` |

---

## Scripts de mantenimiento

| Script | Acción |
|--------|--------|
| `build.bat` | Compilar EXE |
| `install-service.bat` | Instalar servicio |
| `restart-service.bat` | Reiniciar tras cambiar config |
| `uninstall-service.bat` | Desinstalar servicio |

---

## Legacy

La versión Node.js en `agent/src/` está **obsoleta**. Usar solo `NightPOSPrintAgent.exe` (Go).

---

**Ribersoft © NightPOS V1**
