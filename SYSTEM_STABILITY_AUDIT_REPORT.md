# SYSTEM_STABILITY_AUDIT_REPORT

Auditoría de estabilidad UI — NightPOS (post SAAS-1)  
Fecha: 2026-06-14

---

## 1. Causa exacta del congelamiento

El congelamiento de la interfaz **no era un problema de backend**. Era una cascada de errores en el **frontend Vue 3** al navegar entre rutas:

1. **Remount agresivo del layout** — `Suspense` con `:key="route.fullPath"` y `VerticalNavLayout` con `:key="navMenuKey"` destruían y recreaban todo el árbol de componentes en cada navegación o cambio de contexto tenant/sucursal.

2. **Corrupción de vnode/refs** — Eso provocaba errores internos de Vue durante el patch/unmount:
   - `TypeError: Cannot read properties of null (reading 'emitsOptions')`
   - `TypeError: Cannot read properties of null (reading 'exposed')`

3. **Error de render en Setup wizard** — En `/nightpos/platform/setup`, el paso 4 (confirmación) renderizaba un `VAlert` dentro del `VForm` con datos aún no listos, lanzando excepciones en el render que bloqueaban el scheduler de Vue.

4. **Contribuyentes secundarios** (no bloqueantes solos, pero empeoraban la UX):
   - Overlays VDialog/scrim residuales tras navegación
   - Handlers SSE duplicados sin cleanup
   - Spam masivo de `[intlify] Not found ...` por uso incorrecto de `i18n-t` con títulos en español

---

## 2. Frontend vs backend

| Área | ¿Afectado? | Detalle |
|------|------------|---------|
| **Frontend** | **Sí — causa raíz** | Layout Suspense, setup wizard, i18n nav, overlays |
| **Backend** | No | APIs SAAS-1 y provisioning responden correctamente; tests verdes |

---

## 3. Rutas donde ocurre

- **Global:** cualquier navegación con layout vertical (`DefaultLayoutWithVerticalNav`)
- **Crítica:** `/nightpos/platform/setup` (wizard paso 4)
- **Agravante:** cambio de contexto tenant/sucursal (remount del menú)

---

## 4. Errores de consola observados

### Críticos (causan freeze)
```
setup.vue:295 — Unhandled error during execution of render function (VAlert)
TypeError: Cannot read properties of null (reading 'emitsOptions')
TypeError: Cannot read properties of null (reading 'exposed')
[Vue Router warn]: uncaught error during route navigation
```

### Ruido (no bloquean, pero saturan consola)
```
[intlify] Not found 'Operación' key in 'en' locale messages.
[Vue warn]: Runtime directive used on component with non-element root node (NavSearchBar)
```

---

## 5. Errores backend

**Ninguno** relacionado con el congelamiento. No hay 500 ni excepciones Laravel en este flujo.

---

## 6. Fixes aplicados

### Layout y navegación
| Archivo | Cambio |
|---------|--------|
| `frontend/src/layouts/components/DefaultLayoutWithVerticalNav.vue` | Eliminado `:key="route.fullPath"` en Suspense y `:key="navMenuKey"` en layout |
| `frontend/src/layouts/components/DefaultLayoutWithHorizontalNav.vue` | Eliminado `:key="route.fullPath"` en Suspense |
| `frontend/src/layouts/blank.vue` | Eliminado `:key="route.fullPath"` en Suspense |

### Setup wizard
| Archivo | Cambio |
|---------|--------|
| `frontend/src/pages/nightpos/platform/setup.vue` | Paso 4 fuera de `VForm`; `setupSummary` computed seguro; `v-if` por paso; `onBeforeRouteLeave` resetea wizard; plan default `FREE` |

### i18n / menú
| Archivo | Cambio |
|---------|--------|
| `frontend/src/@layouts/utils.js` | `hasI18nTranslationKey`, `getI18nComponentForKey` — no usar `i18n-t` para títulos españoles |
| `frontend/src/@layouts/components/VerticalNavLink.vue` | Usa `getI18nComponentForKey` |
| `frontend/src/@layouts/components/VerticalNavGroup.vue` | Idem |
| `frontend/src/@layouts/components/VerticalNavSectionTitle.vue` | Idem |
| `frontend/src/@layouts/components/HorizontalNavLink.vue` | Idem |
| `frontend/src/@layouts/components/HorizontalNavGroup.vue` | Idem |
| `frontend/src/plugins/i18n/index.js` | `missingWarn: false`, `fallbackWarn: false` |

### Overlays y SSE (ronda anterior, conservados)
| Archivo | Cambio |
|---------|--------|
| `frontend/src/utils/overlaySafety.js` | Limpia scrims tras `router.afterEach` |
| `frontend/src/plugins/1.router/index.js` | `setupOverlaySafety(router)` |
| `frontend/src/composables/useOperationalEvents.js` | Singleton SSE + refCount + cleanup |
| `frontend/src/composables/useRouteDialogCleanup.js` | Cierra diálogos al salir de ruta |

### NavSearchBar
| Archivo | Cambio |
|---------|--------|
| `frontend/src/layouts/components/NavSearchBar.vue` | Wrapper `<div>` único (fix directive en multi-root) |

### Diagnóstico dev
| Archivo | Cambio |
|---------|--------|
| `frontend/src/components/nightpos/dev/NightPosStabilityDebug.vue` | Panel DBG (solo dev) |

---

## 7. Tests ejecutados

### Backend
```bash
php artisan test --filter="TenantProvisioning|PlanManagement"
# 11 passed (61 assertions)

php artisan test  # suite completa previamente: 415 passed
```

### Frontend
```bash
pnpm run build
# Exit code: 0 — build producción OK
```

---

## 8. Validación manual requerida

Tras **reiniciar el dev server** (`pnpm run dev`) y **hard refresh** (Ctrl+Shift+R):

1. Login como superadmin
2. Navegar: Dashboard → Operación → Caja → Finanzas → Personal → Plataforma SaaS → Planes
3. Abrir Setup wizard, completar flujo hasta paso 4, volver atrás
4. Cambiar contexto tenant/sucursal desde navbar
5. Verificar consola:
   - Sin `emitsOptions` / `exposed` null
   - Sin errores en `setup.vue`
   - Sin spam `[intlify] Not found`
   - UI responde a clicks (sin overlay bloqueante)

Botón **DBG** (esquina, solo dev) muestra conteo de overlays activos.

---

## 9. ¿Se puede continuar con SAAS-1?

**Sí.** La funcionalidad SAAS-1 (planes, límites, provisioning unificado, UI planes/dashboard) está implementada y los tests backend pasan.

El bloqueo era **estabilidad UI**, no lógica de negocio SaaS. Con los fixes de layout, setup e i18n aplicados y build verde, se puede:

- Cerrar SAAS-1 con validación manual del usuario
- **No iniciar SAAS-2 (Suscripciones)** hasta confirmar navegación fluida en entorno real

---

## Resumen ejecutivo

| Pregunta | Respuesta |
|----------|-----------|
| ¿Qué congelaba la UI? | Remount forzado de Suspense + error render en setup.vue |
| ¿Backend involucrado? | No |
| ¿Fix principal? | Quitar keys agresivos + reestructurar setup paso 4 |
| ¿Build OK? | Sí |
| ¿Tests OK? | Sí (11 filtrados + 415 suite previa) |
| ¿SAAS-1 listo? | Funcionalmente sí; pendiente confirmación manual del usuario |
