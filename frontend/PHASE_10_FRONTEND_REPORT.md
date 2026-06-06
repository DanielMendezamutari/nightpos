# PHASE_10_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend usuarios y personal  
**Fase:** 10  
**Fecha:** 2026-06-03  
**Referencias:** `PHASE_R1_REPORT.md`, `PHASE_R2_REPORT.md`, `FRONTEND_GUIDELINES.md`

---

## 1. Pantallas creadas

| Ruta | Archivo | Permiso meta |
| ---- | ------- | ------------- |
| `/nightpos/users` | `src/pages/nightpos/users/index.vue` | `admin.users.list` |

**Menú:** entrada «Usuarios / Personal» en `src/navigation/vertical/nightpos.js` (`admin.users.list`).

---

## 2. Funcionalidad UI

### Lista

- Tabla: nombre, usuario, rol operativo, sucursal, estado, comisión %, comisiones chica, acciones.
- 5× `CardStatisticsVertical`: total, garzones, cajeras, chicas, activos/inactivos.

### Crear / editar (dialog)

- Nombre, username, email, rol operativo, sucursal principal, sucursales permitidas (multi), estado.
- PIN y password solo en alta (no se muestra PIN existente).
- **Garzón:** campo comisión % visible.
- **Chica:** switch «Recibe comisiones de chica».
- **Cajera:** sin comisión (oculto por rol).

### Acciones

- Editar (`admin.users.update`).
- Reset PIN (`admin.users.create`).
- Activar / desactivar (`admin.users.update`).
- Reset contraseña desde edición.

---

## 3. API frontend

`src/api/users.js`: list, get, create, update, resetPin, resetPassword, grant/revoke branch.

`src/api/branches.js`: `fetchAdminBranches` para selects.

`useNightPosPermissions`: `canCreateAdminUser`, `canUpdateAdminUser`, `canListAdminBranches`.

---

## 4. Componentes Materialize reutilizados

| Componente | Uso |
| ---------- | --- |
| `CardStatisticsVertical` | KPIs resumen |
| `VDataTable` | Lista usuarios |
| `VDialog` / `VCard` | Formulario, reset PIN/password |
| `VChip` | Rol operativo y estado |
| `VAvatar` / `VIcon` | Fila nombre |
| `VSelect`, `VTextField`, `VSwitch` | Formularios |
| `VAlert` | Aviso reset PIN |
| `VSnackbar` vía `useNightPosNotify` | Feedback |

Sin componentes demo eliminados.

---

## 5. Validación dev (`pnpm run dev`)

| Check | Estado |
| ----- | ------ |
| Login admin | Manual |
| Menú Usuarios visible | Requiere `admin.users.list` + re-login tras migrate |
| Crear garzón 5% / 6% | API + formulario |
| Crear chica / cajera | Reglas UI por rol |
| Reset PIN / desactivar | Diálogos |
| Consola sin errores críticos | Revisar en navegador |

No se exige `npm run build` en esta fase.

---

## 6. Pendiente frontend

- Vista detalle dedicada `/nightpos/users/[id]` (hoy todo en modal).
- Filtros por rol/estado en tabla.
- Integrar `branches/available` si admin sin `admin.branches.list`.

---

## 7. Próxima fase

R3: mejorar comandas listado y flujo garzón móvil; acceso rápido a usuarios desde dashboard admin.
