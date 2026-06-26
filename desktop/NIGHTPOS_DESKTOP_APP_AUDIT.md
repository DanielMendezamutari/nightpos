# NIGHTPOS DESKTOP APP — AUDITORÍA Y PROPUESTA V1

**Fecha:** 2026-06-25  
**Estado:** Auditoría — sin implementación  
**Alcance:** App de escritorio Windows para PC principal de sucursal (caja/admin)

---

## 1. ¿Es posible?

**Sí.** NightPOS ya es una SPA Vue desplegada en hosting. Una app de escritorio no requiere duplicar lógica de negocio: solo debe **envolver** la misma URL con mejor UX (ventana propia, acceso directo, configuración local, opcional integración con el agente).

Arquitectura actual compatible:

| Capa | Estado |
|------|--------|
| Backend único Laravel (hosting) | ✅ SaaS multi-tenant |
| Frontend Vue (misma build web) | ✅ Login PIN + contexto empresa/sucursal |
| Agente impresión Windows (Go) | ✅ Servicio separado, poll al backend |
| Persistencia contexto login | ✅ Cookies 30 días (`tenantSlug`, `branchCode`, nombres, `lastOperatorName`) |
| Token sesión | ✅ Cookie `accessToken` 14 días + refresh JWT |

**Limitación actual:** el frontend asume API en el **mismo origen** (`VITE_API_BASE_URL` o `/api/v1`). Un wrapper de escritorio puede cargar `https://cliente.com` y funcionar sin cambios si frontend y API comparten dominio (caso hosting actual).

---

## 2. Opciones analizadas

### Opción A — Electron

| | |
|---|---|
| **Ventajas** | Ventana dedicada, ícono en escritorio/barra, autoarranque Windows, leer `status.json` del agente, `config.json` local, pantalla offline/conexión, versión embebida, modo kiosk futuro |
| **Desventajas** | ~120–180 MB instalado, Chromium embebido, mantenimiento build/signing, actualizaciones manuales en V1 |
| **Esfuerzo V1 MVP** | Medio — ~3–5 días wrapper + instalador NSIS |

### Opción B — Tauri

| | |
|---|---|
| **Ventajas** | Binario liviano (~5–15 MB), bajo RAM, WebView2 nativo Windows |
| **Desventajas** | Toolchain Rust, curva aprendizaje, WebView2 debe existir en PC, más complejidad CI |
| **Esfuerzo V1 MVP** | Medio-alto — ~5–8 días |

### Opción C — PWA instalada en Windows (Edge/Chrome)

| | |
|---|---|
| **Ventajas** | Casi cero código nuevo, misma build, instalación desde navegador, standalone |
| **Desventajas** | Sin acceso a `status.json` del agente, URL fijada al origen de instalación, menos control autoarranque, iconos/SW incompletos hoy |
| **Esfuerzo V1 MVP** | Bajo — completar PWA frontend (~1–2 días) + guía instalación |

### Opción D — Acceso directo (.url / .lnk)

| | |
|---|---|
| **Ventajas** | Inmediato, cero desarrollo |
| **Desventajas** | No es “sistema instalado”, usuario ve barra del navegador, sin config local |
| **Esfuerzo** | Minutos |

---

## 3. Recomendación

### V1 — **PWA en Windows + acceso directo como fallback**

Motivos:

1. El login **ya implementa** el flujo deseado (empresa/sucursal guardadas → solo PIN → botones Cambiar empresa/sucursal).
2. La PC de caja suele usar **un dominio fijo** del cliente (`https://pos.cliente.com`) — no requiere editar URL en V1 si se instala desde ese dominio.
3. Menor riesgo de romper el frontend web.
4. El agente **debe seguir separado** en V1 (ya estable como servicio Windows).

**Cuándo usar Electron en V1:** solo si un cliente exige **configurar URL en la app** sin reinstalar PWA, o **mostrar estado del agente** dentro de NightPOS antes de V1.1.

### V1.1 — **Electron liviano + instalador unificado opcional**

- `config.json` en `%ProgramData%\NightPOS\Desktop\`
- Lectura de `C:\ProgramData\NightPOS\PrintAgent\status.json`
- Instalador que ofrece: Desktop + Print Agent + servicio (checkboxes)
- Autoarranque al iniciar Windows

### V2 — Auto-update

- Electron: `electron-updater` + releases GitHub/CDN
- Agente: `--update` o instalador incremental
- PWA: service worker con estrategia de cache versionada

---

## 4. Requisitos desktop vs estado actual

| Requisito | Estado actual | Gap V1 |
|-----------|---------------|--------|
| Abrir sin escribir URL | PWA/acceso directo al dominio del cliente | Guía operativa; PWA `start_url` hoy apunta a garzón — **crear manifest desktop** con `start_url: /login` o `/nightpos/cashier` |
| Recordar empresa/sucursal/usuario | ✅ Cookies + `login.vue` | Ninguno |
| Solo PIN al reabrir | ✅ | Ninguno |
| Cambiar empresa / sucursal | ✅ Botones en login | Ninguno |
| Cambiar dominio | ❌ No hay UI “editar URL” | PWA: reinstalar desde nuevo dominio; Electron V1.1: `config.json` |
| Pantalla sin conexión | ❌ | Página `/offline` + detección `navigator.onLine` (frontend) o ventana Electron |
| Modo caja/admin | ✅ Routing post-login (`resolveHomeRoute`) | Opcional: query `?mode=cashier` en `start_url` |
| Estado agente impresión | ❌ Solo docs en settings/printers | V1: systray agente; V1.1: leer `status.json` |
| Versión instalada | ❌ | Electron: semver en About; PWA: mostrar `import.meta.env` build hash en footer |
| Config local `config.json` | ❌ (solo agente tiene config) | V1.1 Electron |

### Ejemplo config propuesto (V1.1 Electron)

```json
{
  "app_url": "https://pos.casa22.com",
  "tenant_slug": "casa22",
  "branch_code": "CENTRO",
  "default_route": "/login",
  "remember_context": true,
  "print_agent_status_path": "C:\\ProgramData\\NightPOS\\PrintAgent\\status.json",
  "auto_launch": true,
  "kiosk": false
}
```

En V1 PWA, el equivalente parcial son las **cookies existentes** + origen de instalación; no hace falta `config.json` si el dominio es estable.

---

## 5. MVP propuesto (V1 — sin Electron)

### Entrega mínima

1. **Manifest desktop** (`manifest-desktop.webmanifest` o parametrizar build):
   - `name`: NightPOS Caja
   - `start_url`: `/login` o ruta cajera por defecto
   - `display`: `standalone`
   - Iconos 192/512 PNG
2. **Service worker básico** (`vite-plugin-pwa`):
   - Precache shell (index, assets)
   - Offline fallback HTML
   - **No** cachear API
3. **Pantalla conexión** en frontend cuando API falla / offline
4. **Guía** `desktop/INSTALL_WINDOWS_PWA.md`: instalar desde Edge, anclar barra, modo pantalla completa
5. **Acceso directo** `.url` opcional para sucursales sin PWA

### Lo que NO incluye V1 desktop MVP

- Instalador MSI
- Autoarranque Windows (se puede con acceso directo en Startup manual)
- Integración visual agente dentro de la app
- Multi-URL por config

---

## 6. Integración con agente de impresión

### Arquitectura actual (confirmada en código)

```
Frontend (browser) ──HTTPS──► Backend Laravel (hosting)
                                    ▲
                                    │ poll print-jobs
Print Agent (Windows service) ──────┘
     │
     └──► Impresora USB ESC/POS
```

- El frontend **no habla** con el agente.
- El agente usa `config.json`: `backend_url`, `device_key`, `printer_name`.
- Estado local en `C:\ProgramData\NightPOS\PrintAgent\status.json` (systray + CLI `--status`).

### Opción 1 — Agente separado (recomendada V1)

| Pros | Contras |
|------|---------|
| Ya funciona en producción | Usuario instala dos cosas |
| Reinicio independiente | Desktop no muestra estado en app |
| Menor acoplamiento | |

**Desktop V1:** documentar instalación dual; estado vía bandeja del agente.

### Opción 2 — Instalador único (V1.1)

Un setup.exe que:

1. Copia `NightPOSPrintAgent.exe` → `Program Files\NightPOS\PrintAgent\`
2. Ejecuta `--install` (servicio + tray)
3. Instala Electron app o crea acceso PWA
4. Escribe configs en `ProgramData`

---

## 7. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| PWA desktop limitada vs Electron | Medio | Evaluar por cliente; Electron solo si lo exigen |
| Cookies no httpOnly (`accessToken`) | Medio | Ya así en web; Electron no empeora; no guardar PIN |
| Cambio dominio hosting | Alto operativo | Pantalla “Configuración” V1.1; comunicar migración |
| Permisos congelados en sesión | Medio | Logout tras cambios rol (documentado) |
| WebView2 / Chromium desactualizado | Bajo | PWA usa navegador del SO |
| Confundir manifest garzón vs caja | Medio | Dos manifests o query `?app=desktop` |
| Autoarranque sin supervisión | Bajo | Política local del boliche |

---

## 8. Qué instalar en cada sucursal

| Componente | Obligatorio | Notas |
|------------|-------------|-------|
| PC Windows 10/11 | ✅ | Caja/barra |
| Navegador Edge/Chrome actualizado | ✅ | PWA V1 |
| NightPOS Print Agent + servicio | ✅ si hay impresión térmica | `NightPOSPrintAgent.exe` |
| Impresora térmica USB + driver | ✅ si hay tickets/comandas auto |
| `device_key` registrada en admin | ✅ | Config → Impresoras |
| Internet estable | ✅ | No offline V1 |
| Acceso HTTPS al hosting | ✅ | Mixed content si API HTTP |

Opcional V1.1: app Electron, autoarranque, instalador único.

---

## 9. Estrategia de actualización

| Capa | V1 | V1.1 | V2 |
|------|----|----|-----|
| **Frontend/API** | Deploy en hosting; usuarios recargan o SW update | Igual | CI/CD + cache bust |
| **PWA** | SW `skipWaiting` + prompt “Nueva versión” | Igual | Automático |
| **Electron** | — | Instalador manual semver | `electron-updater` |
| **Print Agent** | Reemplazar EXE + `restart-service.bat` | Incluido en setup | Version check opcional |

**Regla:** la app instalada **no duplica releases** — siempre consume la build web del hosting (excepto wrapper Electron que solo cambia shell).

---

## 10. Qué NO prometer en V1

- Offline completo (comandar/cobrar sin red)
- Push notifications desktop
- Auto-update silencioso Electron
- Instalador único Desktop+Agent (V1.1)
- Control total impresora desde la app web (sin agente)
- APK Windows nativo (solo PWA/Electron)
- Múltiples ventanas POS sincronizadas localmente
- Kiosk lockdown certificado

---

## 11. Respuestas directas (checklist)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Es posible? | **Sí** — wrapper sobre SPA existente |
| 2 | ¿Opción V1? | **PWA Windows** (+ guía) o acceso directo; Electron solo si hace falta config URL |
| 3 | ¿Opción V1.1? | **Electron simple** + lectura `status.json` + instalador opcional con agente |
| 4 | ¿Qué es rápido? | Acceso directo hoy; completar PWA 1–2 días; Electron wrapper ~1 semana |
| 5 | ¿Riesgos? | URL/dominio, sesión cacheada, dos instalables (PWA+agente), iconos/SW incompletos |
| 6 | ¿Qué instalar por sucursal? | Browser/PWA + Print Agent + impresora + device_key |
| 7 | ¿Cómo actualizar? | Hosting deploy; agente manual; PWA vía SW |
| 8 | ¿URL/contexto? | Hoy: cookies + origen; V1.1: `config.json` Electron |
| 9 | ¿Agente? | **Separado V1**; poll backend; status en systray/`status.json` |
| 10 | ¿Qué no prometer? | Offline real, push, auto-update, un solo instalador V1 |

---

## 12. Archivos de referencia (codebase)

| Archivo | Relevancia |
|---------|------------|
| `frontend/src/pages/login.vue` | Flujo PIN + contexto guardado |
| `frontend/src/stores/context.js` | Persistencia empresa/sucursal |
| `frontend/src/stores/auth.js` | Token, refresh, cookies |
| `frontend/public/manifest.webmanifest` | PWA parcial (orientado garzón) |
| `frontend/vite.config.js` | Sin `vite-plugin-pwa` hoy |
| `agent/README_WINDOWS.md` | Instalación agente |
| `agent/internal/paths/paths.go` | Rutas `config.json`, `status.json` |

---

## 13. Próximos pasos sugeridos (cuando se apruebe implementación)

1. Aprobar estrategia V1 = PWA desktop + agente separado
2. Implementar PWA completa (ver `frontend/PWA_WAITER_APP_AUDIT.md` — compartir SW/icons)
3. Crear manifest variant desktop (`start_url: /login`)
4. Pantalla offline/conexión en frontend
5. Guía operativa sucursal (`desktop/INSTALL_WINDOWS_PWA.md`)
6. Backlog V1.1: repo `desktop/electron` mínimo

**Principio rector:** la app instalada es un **contenedor de acceso** al mismo NightPOS SaaS — sin fork de lógica.
