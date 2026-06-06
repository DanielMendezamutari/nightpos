# Alta rápida de chica — Frontend

**Fecha:** 2026-06-02

---

## Componente

`src/components/nightpos/staff/QuickGirlCreateDialog.vue`

- Modal Materialize (`VDialog`, `VCard`, `VForm`)
- Campos: nombre, PIN opcional, observación opcional
- Emite `created` con la chica devuelta por API

---

## API

`src/api/staff.js`

- `fetchStaffGirls()` → `GET /staff/girls`
- `quickCreateGirl(payload)` → `POST /staff/quick-girls`

---

## Integración actual

**Registrar pieza** (`services/room-services/create.vue`):

- `VAutocomplete` para buscar chica
- Ítem “+ Registrar nueva chica” en el desplegable
- Botón “+ Nueva chica”
- Tras crear: refresca lista y selecciona la nueva chica

`useGirlIncomeStaffOptions.js` prioriza `fetchStaffGirls` (cajera sin `admin.users.list`).

---

## Permiso UI

`can('staff.quick_create_girl')` para mostrar acciones de alta rápida.

---

## Validación manual

1. Login cajera o admin.
2. Servicios → Piezas → Registrar pieza.
3. “+ Nueva chica” → completar nombre → guardar.
4. Verificar chica seleccionada y registrar pieza.
5. Revisar en Personal que aparece la chica.

---

## Reutilización prevista

Importar `QuickGirlCreateDialog` en:

- `bracelets/create.vue`
- `shows/create.vue`
- Comandas CON_ACOMPANANTE
- Liquidaciones manuales
