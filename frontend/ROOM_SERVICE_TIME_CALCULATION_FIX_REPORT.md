# ROOM SERVICE TIME CALCULATION FIX — V1-91.3 (Frontend)

## Problema

El campo `started_at` en el formulario de creación de piezas:

1. Iniciaba vacío (`''`), sin pre-poblar con la hora actual.
2. Cuando el usuario ingresaba una hora manualmente (e.g. `22:30`), se enviaba sin offset de timezone.
3. El backend (Laravel, anteriormente UTC) lo interpretaba como UTC, causando que la pieza apareciera vencida inmediatamente.

## Cambios en `create.vue`

### Helper `localDatetimeString()`

```javascript
function localDatetimeString(date = new Date()) {
  const pad = n => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}
```

Genera un string `YYYY-MM-DDTHH:mm` en la hora **local del navegador** (Bolivia).

### Pre-poblar `started_at`

```javascript
const form = ref({
  ...
  started_at: localDatetimeString(),  // Hora local actual
  ...
})
```

La cajera ve la hora correcta al abrir el formulario y puede ajustarla si la pieza empezó antes.

### Computed `estimatedEndTime`

```javascript
const estimatedEndTime = computed(() => {
  const raw = form.value.started_at
  const dur = Number(form.value.duration_minutes ?? 0)
  if (!raw || !dur) return null
  const start = new Date(raw)
  if (Number.isNaN(start.getTime())) return null
  const end = new Date(start.getTime() + dur * 60 * 1000)
  const pad = n => String(n).padStart(2, '0')
  return `${pad(end.getHours())}:${pad(end.getMinutes())}`
})
```

Muestra la hora estimada de fin directamente en el hint del campo `started_at`.

### UX del campo

```html
<VTextField
  v-model="form.started_at"
  type="datetime-local"
  label="Hora de inicio *"
  :hint="estimatedEndTime ? `Termina aprox: ${estimatedEndTime}` : ''"
  persistent-hint
  :rules="[v => !!v || 'Requerido']"
/>
```

La cajera ve algo como:

```
Hora de inicio *
[ 2026-06-06T22:15 ]
Termina aprox: 22:45
```

Confirmación visual inmediata del cálculo antes de guardar.

## Por qué esto funciona con el backend

Ahora el backend tiene `APP_TIMEZONE=America/La_Paz`. Cuando el frontend envía `"2026-06-06T22:15"` (hora local Bolivia), `Carbon::parse("2026-06-06T22:15", "America/La_Paz")` lo interpreta correctamente como las 22:15 en Bolivia. No se produce desplazamiento de 4 horas.

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `src/pages/nightpos/services/room-services/create.vue` | Helper `localDatetimeString`, pre-poblar `started_at`, computed `estimatedEndTime`, campo actualizado |
