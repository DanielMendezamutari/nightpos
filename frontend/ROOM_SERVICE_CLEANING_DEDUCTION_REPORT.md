# ROOM_SERVICE_CLEANING_DEDUCTION_REPORT.md — Frontend
# Ajuste UI: Descuento de Limpieza en Registro de Pieza

**Fecha:** 2026-06-06  
**Estado:** COMPLETADO

---

## Cambios en `create.vue` (Registrar Pieza)

Archivo: `frontend/src/pages/nightpos/services/room-services/create.vue`

### Nuevo campo: Monto limpieza

Se agregó campo `cleaning_amount` al formulario de registro de pieza:
- Label: "Monto limpieza (BOB)"
- Hint: "Se descuenta del bruto de la chica"
- Tipo: número, mínimo 0
- Validación inline: error si `cleaning_amount > gross_girl_amount`

### Cálculo en tiempo real

Se actualizaron los `computed` para mostrar la distribución completa:

| Computed | Descripción |
|----------|-------------|
| `grossGirlAmount` | `total × girl_percent / 100` |
| `cleaningAmountValue` | `min(cleaning_amount, grossGirlAmount)` |
| `netGirlAmount` | `grossGirlAmount - cleaningAmountValue` |
| `calculatedHouseAmount` | `total - grossGirlAmount` |
| `cleaningExceedsGirl` | `cleaning_amount > grossGirlAmount` → muestra error |

### Tarjeta de distribución (reemplaza la tarjeta anterior)

La tarjeta ahora muestra 5 valores:
```
Total cobrado: 200 BOB
Chica bruta:   100 BOB
Limpieza:      -10 BOB  (warning color)
Chica neta:     90 BOB  (success color)
Casa:          100 BOB
```

### Envío al API

El campo `cleaning_amount` se envía en el payload:
```javascript
cleaning_amount: form.value.cleaning_amount !== null ? Number(form.value.cleaning_amount) : 0
```

---

## Liquidaciones — Vista de Chica

El `girl_amount` que llega del backend ya es neto (post-deducción). La descripción del ítem incluye el descuento:

> `"Pieza — Hab. 1 (limpieza -10.00)"`

Esto es visible en:
- `frontend/src/pages/nightpos/settlements/girls.vue`
- `frontend/src/pages/nightpos/settlements/[id].vue`

No fue necesario modificar estos archivos — el backend ya emite la descripción correcta.

---

## Liquidaciones — Vista de Limpieza

El `amount` del ítem de limpieza viene de `cleaning_task.amount`, que a su vez viene de `room_service.cleaning_amount`. Tampoco requirió cambios en frontend.

---

## UX Notes

1. El campo "Monto limpieza" es **opcional** — si no se llena, defecto es 0 (sin descuento).
2. La tarjeta de distribución se actualiza en tiempo real conforme el usuario escribe.
3. Si `cleaning_amount > gross_girl_amount`, el campo muestra error rojo y el botón de guardar fallará la validación.
4. Los colores ayudan a la cajera a identificar rápidamente los flujos de dinero.
