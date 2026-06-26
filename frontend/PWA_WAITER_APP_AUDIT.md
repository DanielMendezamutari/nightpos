# PWA / APP GARZÓN — AUDITORÍA Y PROPUESTA V1

**Fecha:** 2026-06-25  
**Estado:** Auditoría — sin implementación  
**Alcance:** App instalable móvil para garzones (comandas, mesas, servicios)

---

## 1. ¿Es posible?

**Sí.** El modo garzón ya existe como rutas Vue móviles:

| Ruta | Función |
|------|---------|
| `/nightpos/waiter` | Mis mesas |
| `/nightpos/waiter/orders` | Comandas |
| `/nightpos/waiter/orders/new` | Nueva comanda |
| `/nightpos/waiter/orders/[id]` | Detalle |

Layout móvil: `WaiterMobileHeader`, `WaiterBottomNav`, SSE banner (`NightPosSseBanner`).

Login PIN con contexto guardado **ya cubre** el flujo “solo ingresar PIN” descrito en el requerimiento.

---

## 2. Auditoría PWA actual

### Inventario

| Elemento | Estado | Detalle |
|----------|--------|---------|
| `manifest.webmanifest` | ⚠️ Parcial | Existe en `frontend/public/` |
| Service worker producción | ❌ No | Solo `mockServiceWorker.js` (MSW dev) |
| `vite-plugin-pwa` | ❌ No | No está en `vite.config.js` |
| Iconos PWA | ❌ Insuficiente | Solo `favicon.ico` 48×48 |
| `theme_color` | ✅ | `#666CFF` en manifest + meta |
| `display: standalone` | ✅ | En manifest |
| `start_url` | ✅ | `/nightpos/waiter` |
| `orientation` | ✅ | `portrait` |
| Meta iOS/Android | ⚠️ Parcial | `apple-mobile-web-app-capable` en `index.html` |
| Offline fallback | ❌ No | |
| Install prompt / A2HS | ❌ No implementado | Depende del navegador |
| Cache estrategia API | ❌ N/A | Correcto no cachear API en V1 |

### Contenido actual del manifest

```json
{
  "name": "NightPOS Garzón",
  "short_name": "NightPOS",
  "start_url": "/nightpos/waiter",
  "display": "standalone",
  "theme_color": "#666CFF",
  "icons": [{ "src": "/favicon.ico", "sizes": "48x48" }]
}
```

**Conclusión:** hay base PWA “declarativa”, pero **no es instalable de forma confiable** en Android/iOS hasta agregar iconos maskable, service worker y HTTPS.

---

## 3. Opciones analizadas

### Opción A — PWA móvil (recomendada V1)

| | |
|---|---|
| **Ventajas** | Reutiliza 100% frontend, instalación Chrome “Agregar a pantalla principal”, bajo mantenimiento, alineado con login/contexto actual |
| **Desventajas** | Push limitado (iOS restricciones), sin APK en Play Store, instalación menos guiada |
| **Esfuerzo** | ~2–4 días (SW + icons + UX install + offline shell) |

### Opción B — Capacitor (Android APK)

| | |
|---|---|
| **Ventajas** | APK real, distribución manual sideload, storage nativo, push futuro |
| **Desventajas** | Pipeline Android, firma APK, otra capa de release |
| **Esfuerzo** | ~1–2 semanas V1 |

### Opción C — WebView Android simple

| | |
|---|---|
| **Ventajas** | APK mínimo |
| **Desventajas** | Duplica wrapper, menos robusto, seguridad si URL editable sin validación |
| **Esfuerzo** | ~3–5 días — **no recomendado** vs Capacitor si se quiere APK |

---

## 4. Recomendación

| Fase | Opción |
|------|--------|
| **V1** | **PWA instalada** (Chrome Android + Safari iOS Add to Home) |
| **V1.1** | **Capacitor** si el operador exige APK o notificaciones push fiables |
| **V2** | Push + background sync evaluado caso por caso |

---

## 5. Requisitos garzón vs estado actual

| Requisito | Estado | Gap |
|-----------|--------|-----|
| Recordar `app_url` | ⚠️ Implícito | PWA fija al **origen** de instalación; cambio dominio = nueva instalación o pantalla config V1.1 |
| Recordar empresa/sucursal | ✅ | Cookies `tenantSlug`, `branchCode`, `tenantName`, `branchName` (30 días) |
| Recordar usuario (nombre) | ✅ | Cookie `lastOperatorName` |
| Solo PIN al abrir | ✅ | `login.vue` → `pinStep === 'pin'` si hay contexto |
| Cambiar empresa/sucursal/URL | ⚠️ | Empresa/sucursal ✅; **URL no** — no hay pantalla “Configuración app” |
| Token persistente | ✅ | `accessToken` cookie 14 d + refresh |
| No guardar PIN | ✅ | Confirmado en `LOGIN_CONTEXT_SELECTION_REPORT.md` |
| Logout revoca sesión | ✅ | `auth/logout` + clear cookies |
| Token expirado → PIN | ✅ | Redirect `?reason=session_expired` |
| Comandar tras cerrar app | ✅ | Token + contexto en cookies; SSE reconecta al abrir |
| Offline comandar | ❌ | V2 — solo shell offline V1 |
| Carga rápida | ⚠️ | Sin SW precache |
| Mensaje sin internet | ❌ | Agregar banner global |

### Flujo login actual (ya implementado)

1. Si hay `tenantSlug` + `branchCode` → pantalla PIN con nombres legibles
2. Botones: **Cambiar empresa**, **Cambiar sucursal**, **Cambiar usuario**
3. Primera vez → selectores empresa → sucursal → PIN
4. Validación API de contexto inválido con mensaje claro

**Gap principal para app garzón:** pantalla **“Configuración de acceso”** para cambiar URL/dominio sin desinstalar (V1.1). En V1, documentar: instalar PWA desde el dominio correcto.

---

## 6. PWA mínimo necesario (propuesta implementación)

### 6.1 Dependencia

```bash
npm i -D vite-plugin-pwa
```

Configurar en `vite.config.js` con `registerType: 'prompt'` o `autoUpdate`.

### 6.2 Manifest completo

- `name`: NightPOS Garzón
- `short_name`: Garzón
- `start_url`: `/nightpos/waiter`
- `scope`: `/`
- `display`: `standalone`
- `background_color`, `theme_color`
- **Iconos:** 192×192, 512×512, maskable (PNG en `public/icons/`)
- `categories`: `business`
- `lang`: `es`

### 6.3 Service worker (Workbox)

| Recurso | Estrategia |
|---------|------------|
| `index.html`, JS/CSS chunks | Precache + stale-while-revalidate |
| `/api/*` | **NetworkOnly** (nunca cachear) |
| Imágenes estáticas | CacheFirst con límite |
| Offline | Fallback `/offline.html` o ruta Vue `/offline` |

### 6.4 UX instalación

- Componente `InstallPwaBanner.vue` en shell garzón (captura `beforeinstallprompt`)
- Instrucciones iOS Safari (Share → Add to Home — no hay prompt automático)
- Tras instalar, abrir en `standalone` sin barra URL

### 6.5 Conectividad

- Composable `useNetworkStatus` (`@vueuse/core` → `useOnline`)
- Banner en `WaiterMobileHeader` cuando offline
- Deshabilitar acciones que requieran API con mensaje claro

### 6.6 Seguridad

| Regla | Implementación |
|-------|----------------|
| No PIN plano | ✅ Ya cumplido |
| No password plana | ✅ |
| Token revocable | ✅ Logout backend |
| Preferir httpOnly cookies | ⚠️ Mejora futura — hoy token en cookie JS-readable (igual que web) |
| Cambio dominio | V1.1: `localStorage` clave `nightpos_app_origin` + redirect — **validar HTTPS** |

---

## 7. Caso cambio de dominio

**Escenario:** `https://casa22.com` → `https://pos.casa22.com`

| Enfoque | V1 PWA | V1.1 |
|---------|--------|------|
| Usuario | Desinstalar acceso directo, abrir nuevo URL, instalar de nuevo | Pantalla “Configuración” → editar URL base → reload |
| Datos | Cookies del origen viejo no migran (aislamiento browser) | `localStorage` en app Capacitor o config Electron |
| Operaciones | Comunicar migración con fecha | Script admin opcional |

**Propuesta V1.1 (sin romper web):** ruta pública `/app-setup` con:

- Campo URL (validar HTTPS)
- Botón guardar en `localStorage`
- Bootstrap lee origen custom antes de axios `baseURL`  
Solo activo en modo standalone (`window.matchMedia('(display-mode: standalone)')`).

---

## 8. Capacitor V1.1 (preview)

Si PWA no alcanza:

```
frontend/ (build dist)
capacitor.config.ts → server.url o webDir: dist
android/ → APK debug/release
```

Ventajas sobre WebView casero: plugins oficiales, splash, status bar, secure storage para token (opcional).

**No duplicar lógica:** Capacitor carga el mismo `dist/` o URL remota del hosting.

---

## 9. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| iOS PWA limitaciones (SSE, background) | Probar Safari iOS; SSE ya usado en garzón — validar en dispositivo |
| Instalación no obvia para staff | Video/guía 1 página + banner install |
| Token en cookie accesible JS | Mismo riesgo web; Capacitor SecureStorage V1.1 |
| Cache SW sirve JS viejo | `skipWaiting` + toast “Actualizar app” |
| Manifest apunta garzón pero admin instala mismo build | Manifest único OK; desktop usa otro start_url o segunda PWA |
| Permisos menú no actualizados | Logout tras cambio rol (documentado) |

---

## 10. Qué instalar en el celular del garzón

1. Chrome (Android) o Safari (iOS)
2. Abrir URL del cliente (HTTPS)
3. Login primera vez: empresa → sucursal → PIN
4. **Agregar a pantalla principal**
5. Usar icono NightPOS (abre en standalone → `/nightpos/waiter`)

No requiere APK en V1.

---

## 11. Actualización

- **Hosting deploy** → nueva build JS
- **Service worker** detecta update → prompt al garzón “Recargar”
- **Sin** paso por Play Store en V1
- Capacitor V1.1: nuevo APK manual o MDM interno

---

## 12. Qué NO prometer V1

- Comandas offline en mesa sin red
- Push nativo iOS/Android
- APK en Play Store
- Biometría / Face ID
- GPS / beacon
- Sincronización multi-dispositivo offline
- Guardar PIN o “recordar PIN”

---

## 13. Respuestas directas (checklist)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Es posible? | **Sí** — garzón móvil ya existe |
| 2 | V1 | **PWA** completando manifest + SW + iconos |
| 3 | V1.1 | **Capacitor APK** si hace falta |
| 4 | Rápido | Manifest + iconos 1 día; SW + offline 1–2 días |
| 5 | Riesgos | iOS limits, SW cache, dominio, instalación manual |
| 6 | Por sucursal/garzón | Solo instalación PWA personal; backend compartido |
| 7 | Actualizar | Deploy hosting + SW |
| 8 | URL/contexto | Cookies hoy; config URL V1.1 |
| 9 | Agente | **N/A** — garzón no usa print agent local |
| 10 | No prometer | Offline comandar, push, APK V1 |

---

## 14. Archivos relevantes

| Archivo | Rol |
|---------|-----|
| `public/manifest.webmanifest` | Manifest actual |
| `index.html` | Link manifest + meta PWA |
| `vite.config.js` | Falta plugin PWA |
| `src/pages/login.vue` | Contexto + PIN |
| `src/stores/auth.js` | Sesión |
| `src/stores/context.js` | Empresa/sucursal |
| `src/pages/nightpos/waiter/**` | UI garzón |
| `LOGIN_CONTEXT_SELECTION_REPORT.md` | Spec login contexto |

---

## 15. Próximos pasos (post-aprobación)

1. Generar iconos 192/512 maskable
2. Integrar `vite-plugin-pwa`
3. Ruta/página offline
4. Banner install + guía garzón (`frontend/WAITER_PWA_INSTALL_GUIDE.md`)
5. QA: Android Chrome + iOS Safari standalone
6. Backlog: `/app-setup` cambio URL, Capacitor spike

**Principio:** la app garzón es el **mismo frontend** en `display: standalone` — cero duplicación de reglas de comandas.
