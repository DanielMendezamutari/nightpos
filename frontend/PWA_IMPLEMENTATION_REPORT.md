# PWA_IMPLEMENTATION_REPORT.md

**Feature:** NightPOS como app instalable (PWA)  
**Fecha:** 2026-06-25  
**Estado:** Implementado — V1

---

## Resumen

NightPOS ahora es instalable como Progressive Web App en Android, iPhone y Windows. No se duplicó lógica — es el mismo frontend Vue con las mismas rutas, API y stores. La "app" es una mejor forma de acceder al mismo sistema SaaS.

---

## Cambios implementados

### 1. Dependencia

`package.json` → `vite-plugin-pwa: latest` (devDependency)

### 2. vite.config.js — plugin VitePWA

- `registerType: 'prompt'` — SW se instala pero no toma control hasta que el usuario acepta
- `manifest: false` — manifests manejados en `public/`
- Workbox:
  - Precache: todos los assets build (JS, CSS, HTML, íconos, WOFF2)
  - `navigateFallback: 'index.html'` — SPA shell offline
  - `NetworkOnly` para `/api/*` — jamás se cachea la API
  - `CacheFirst` para imágenes/fuentes
  - `skipWaiting: false` — respeta el flujo "prompt antes de actualizar"

### 3. Manifests

| Archivo | Uso | `start_url` | `orientation` |
|---------|-----|-------------|---------------|
| `public/manifest.webmanifest` | Caja/Admin (default) | `/login` | `any` |
| `public/manifest-waiter.webmanifest` | Garzón | `/nightpos/waiter` | `portrait` |

### 4. Iconos SVG

`public/icons/`

| Archivo | Uso |
|---------|-----|
| `icon-192.svg` | Manifest icon 192×192 |
| `icon-512.svg` | Manifest icon 512×512 |
| `icon-maskable.svg` | Maskable (content en safe zone 60% del canvas) |

> **Producción:** para máxima compatibilidad iOS/Android ejecutar el script de generación PNG (pendiente V1.1). Los SVG funcionan en Chrome 103+, Edge, Firefox. iOS usa `apple-touch-icon`.

### 5. index.html — metas PWA

- `<link id="pwa-manifest">` con id para cambio dinámico
- `apple-mobile-web-app-capable`, `apple-mobile-web-app-status-bar-style`
- `apple-touch-icon` apuntando a SVG
- `msapplication-TileColor`

### 6. Composables

#### `src/composables/usePwaManifest.js`

Cambia dinámicamente el `<link rel="manifest">` según la ruta actual:
- Rutas `/nightpos/waiter*` → `manifest-waiter.webmanifest`
- Otras → `manifest.webmanifest`

Registrado en `App.vue` para watch de rutas global.

#### `src/composables/useNetworkStatus.js`

Envuelve `useOnline` de VueUse. Expone:
- `isOnline` / `isOffline`
- `offlineMessage` — texto estándar para snackbars

#### `src/composables/useSwUpdate.js`

Detecta SW actualizado (nuevo deploy en hosting). Cuando hay actualización:
- `needsUpdate = true`
- Snackbar "Nueva versión" con botón "Actualizar" (App.vue)
- `applyUpdate()` aplica el nuevo SW sin forzar refresh silencioso

### 7. Componentes

#### `InstallPwaBanner.vue`

- Props: `context` (`waiter` | `cashier`)
- Android/Chrome/Edge: captura `beforeinstallprompt` → botón "Instalar"
- iOS Safari: botón "Cómo instalar" → diálogo con pasos (Compartir → Agregar a pantalla)
- Se oculta si: ya instalado (standalone), ya descartado (localStorage)
- Se descarta permanentemente con `nightpos_pwa_install_dismissed_{context}`

#### `OfflineBanner.vue`

- Variante `compact` (chip) y full (alert)
- Detecta online/offline con `useNetworkStatus`
- Muestra "Sin conexión" offline y "Conexión restaurada" 3 s al volver

### 8. Integración layouts

| Componente | InstallPwaBanner | OfflineBanner |
|------------|------------------|---------------|
| `waiter/index.vue` | ✅ `context="waiter"` | ✅ |
| `CashierShell.vue` | ✅ `context="cashier"` | ✅ |

`WaiterMobileHeader.vue` ya tenía chip online/offline propio — se conserva.

### 9. App.vue

- Registra `usePwaManifest()` (watch global de rutas)
- Registra `useSwUpdate()` + snackbar "Nueva versión disponible"

### 10. Plugin `src/plugins/pwa.js`

Placeholder para extensión futura del ciclo de vida del SW. En producción, el SW se registra automáticamente por el `injectRegister: 'script'` del plugin de Vite.

---

## Flujo de instalación

### Garzón (Android Chrome)

1. Abre `https://pos.cliente.com` en Chrome
2. Navega a Garzón o hace login
3. Chrome detecta PWA instalable (manifest waiter) → `InstallPwaBanner` aparece
4. Toca "Instalar" → acepta → se agrega icono "Garzón" a la pantalla
5. Abre desde ícono → `start_url: /nightpos/waiter` → login solo PIN → mesas

### Caja (Windows Edge/Chrome)

1. Abre `https://pos.cliente.com` en Edge
2. Instala desde `InstallPwaBanner` o menú "..." → "Instalar esta aplicación"
3. Se agrega a Aplicaciones → acceso desde escritorio
4. Abre en ventana standalone → `start_url: /login` → cajera directo

### Actualización automática

1. Se sube nueva build al hosting
2. Próxima vez que abre la PWA: SW nuevo detectado
3. Snackbar "Nueva versión disponible → Actualizar"
4. Usuario acepta → SW nuevo toma control → recarga

---

## Qué NO incluye V1

- PNG 192×512 para iOS (SVG funciona en Chrome/Edge; iOS fallback OK)
- Offline comandar/cobrar (NetworkOnly en API)
- Push notifications
- APK Play Store
- Electron
- Status agente de impresión dentro de la PWA
- Cambio de URL/dominio en pantalla (V1.1)

---

## Comandos

```bash
# Instalar dependencia (si no se hizo en background)
npm install

# Build producción (genera dist/sw.js)
npm run build

# Preview build (verificar PWA en localhost)
npm run preview
```

---

## Archivos tocados

| Archivo | Cambio |
|---------|--------|
| `package.json` | `vite-plugin-pwa: latest` |
| `vite.config.js` | Plugin VitePWA |
| `public/manifest.webmanifest` | Manifest caja actualizado |
| `public/manifest-waiter.webmanifest` | Manifest garzón actualizado |
| `public/icons/icon-192.svg` | Nuevo |
| `public/icons/icon-512.svg` | Nuevo |
| `public/icons/icon-maskable.svg` | Nuevo |
| `index.html` | Metas PWA, id manifest, apple-touch-icon |
| `src/App.vue` | usePwaManifest + useSwUpdate + snackbar |
| `src/plugins/pwa.js` | Nuevo |
| `src/composables/usePwaManifest.js` | Nuevo |
| `src/composables/useNetworkStatus.js` | Nuevo |
| `src/composables/useSwUpdate.js` | Nuevo |
| `src/components/nightpos/layout/InstallPwaBanner.vue` | Nuevo |
| `src/components/nightpos/layout/OfflineBanner.vue` | Nuevo |
| `src/pages/nightpos/waiter/index.vue` | + banners |
| `src/components/nightpos/cashier/CashierShell.vue` | + banners |

---

## Verificación post-build

```bash
npm run build
# Verificar en dist/:
#   sw.js             ← service worker generado
#   workbox-*.js      ← workbox runtime
#   manifest.webmanifest   ← incluido en precache
#   manifest-waiter.webmanifest ← incluido
#   icons/*.svg       ← incluidos
```

En DevTools → Application → Service Workers: debe aparecer registrado.  
En DevTools → Application → Manifest: debe leer correctamente.  
Lighthouse → PWA audit: debe pasar los criterios básicos.
