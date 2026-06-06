# Fase C3 — Datos maestros administrativos (Frontend)

## 1. Módulo Configuración

Subpestañas (`useSettingsSectionTabs.js` + `NightPosSectionTabs`):

| Ruta | Página |
|------|--------|
| `nightpos-settings-payments` | Métodos de pago |
| `nightpos-settings-cash-reasons` | Motivos de caja |
| `nightpos-settings-service-areas` | Ambientes |
| `nightpos-settings-room-types` | Tipos de habitación |
| `nightpos-settings-first-night-checklist` | Checklist primera noche |

Navegación: `nightpos-r4.js` — grupo Configuración ampliado.

## 2. APIs cliente

- `api/cashMovementReasons.js`
- `api/paymentMethods.js`
- `api/serviceAreas.js`
- `api/roomTypes.js`
- `api/firstNightChecklist.js`

## 3. Integraciones operativas

- **Caja** (`cash/index.vue`): select motivo por tipo + notas; sin modal grande de configuración.
- **Nueva comanda** (`orders/new.vue`): ambiente catalogado opcional + etiqueta libre.
- **Nueva habitación** (`rooms/create.vue`): tipo desde catálogo con autocompletado duración/precio.

## 4. UI Materialize

Cards, tablas (`VDataTable`), formularios en panel lateral, chips de estado, `NightPosPageHeader`, breadcrumbs. Sin modales principales para CRUD de configuración.

## 5. Permisos en UI

- Listado: `settings.*`
- Alta/edición: `settings.*.manage`
- Checklist: `settings.checklist`

## 6. Validación manual (`pnpm run dev`)

1. Login admin.
2. Configuración → Motivos caja → crear motivo.
3. Caja → egreso con motivo y notas.
4. Configuración → Métodos de pago.
5. Configuración → Ambientes → crear.
6. Nueva comanda con ambiente.
7. Configuración → Tipos habitación → crear habitación con tipo.
8. Checklist primera noche → ítems y botón «Ir a configurar».
9. Consola del navegador sin errores críticos.

## 7. Próxima fase recomendada

Consumir métodos/motivos en pantallas de cobro y reportes (C4), manteniendo Materialize.
