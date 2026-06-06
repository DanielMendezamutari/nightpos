# Alta rápida de chica (Quick Girl Create)

**Fecha:** 2026-06-02

---

## Problema

Registrar pieza requería salir a Usuarios/Personal si la chica no existía.

## Solución

Endpoint operativo y diálogo reutilizable en el flujo de caja.

---

## API

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/staff/girls` | `staff.quick_create_girl` |
| POST | `/api/v1/staff/quick-girls` | `staff.quick_create_girl` |

### POST payload

```json
{
  "name": "Nombre chica",
  "pin": "4321",
  "notes": "opcional"
}
```

### Reglas al crear

- `staff_role` = `GIRL`
- `can_receive_girl_commissions` = `true`
- `tenant_id` / `branch_id` = contexto operativo
- Acceso a sucursal actual (`user_branch_access`)
- `status` = `active`
- `username` autogenerado si no se envía
- PIN hasheado (`pin_hash` + fingerprint)
- No duplicar nombre de chica activa en la misma sucursal (case insensitive)
- Observación en `staff_profiles.notes`

---

## Permisos

| Rol | `staff.quick_create_girl` |
|-----|---------------------------|
| Admin (`tenant_owner`) | Sí |
| Cajera | Sí |
| Garzón | No |
| Limpieza | No |

---

## Tests

`tests/Feature/Api/V1/QuickGirlCreateTest.php` — 9 casos.

---

## Reutilización

Componente frontend: `QuickGirlCreateDialog.vue` — usable en manillas, shows, comandas, liquidaciones.
