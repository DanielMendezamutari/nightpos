# NightPOS Print Agent (Windows)

Agente local Node.js para imprimir comandas en impresora USB conectada a la PC del local.

## Requisitos

- Windows 10/11
- Node.js 18+
- Impresora térmica instalada en Windows (nombre exacto del spooler)
- `device_key` generada en NightPOS → Configuración → Impresoras

## Instalación

1. Copie la carpeta `agent/` a la PC del local (ej. `C:\NightPOS\agent`).
2. Copie `config.example.json` → `config.json`.
3. Edite `config.json`:

```json
{
  "backend_url": "https://su-dominio.com/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500,
  "dry_run": false
}
```

4. Inicie el agente:

```powershell
cd C:\NightPOS\agent
node src/index.js
```

## Modo dry-run (sin impresora)

Escribe tickets en `./dry-run-output/`:

```powershell
node src/index.js --dry-run
```

## Flujo

1. Garzón envía comanda a barra desde celular.
2. Backend encola `print_job` ORDER_COMMAND.
3. Agente consulta `GET /print-jobs/pending` cada 1,5 s.
4. Reclama job → imprime → confirma `PRINTED` o `FAILED`.

## Seguridad

- Una `device_key` por sucursal/dispositivo.
- El agente solo accede a jobs de su tenant + branch.
- No usa credenciales de usuario.

## Arranque automático (opcional)

Use **Programador de tareas de Windows** para ejecutar al iniciar sesión:

- Programa: `node.exe`
- Argumentos: `C:\NightPOS\agent\src\index.js`
- Inicio en: `C:\NightPOS\agent`

## Solución de problemas

| Problema | Acción |
|----------|--------|
| 401 Unauthorized | Verifique `device_key` y URL backend |
| Impresora no imprime | Confirme `printer_name` en Panel de control → Dispositivos e impresoras |
| Jobs PENDING acumulados | Verifique internet y que el agente esté corriendo |
| FAILED sin papel | Reponga papel y use Reimprimir en NightPOS |
