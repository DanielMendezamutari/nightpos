# AUDITORÍA — Matriz de permisos NightPOS (Backend)

**Fecha:** 2026-06-16  
**Estado:** Diagnóstico — sin cambios de código  
**Fuente roles demo:** `database/seeders/Concerns/SeedsNightPosFoundation.php`  
**Fuente wizard SaaS:** `app/Application/Tenant/Support/TenantDefaultRolePermissions.php`  
**Total permisos catálogo demo:** 98 slugs

---

## 1. Roles existentes en el sistema

| Rol (`slug`) | Nombre demo | ¿En wizard provisioner? | Notas |
|--------------|-------------|-------------------------|-------|
| `super_admin` | Super Admin SaaS | N/A (plataforma) | Todos los permisos |
| `tenant_owner` | Administrador | ✓ `tenantOwner()` | Admin local completo |
| `cashier_senior` | Cajera Senior | **✗** | Solo demo seeder |
| `cashier` | Cajero | ✓ `cashier()` | Cajera básica |
| `waiter` | Garzón | ✓ `waiter()` | Modo garzón |
| `cleaning` | Limpieza | ✓ `cleaning()` | Modo limpieza |
| `girl` | Chica | ✓ `girl()` | Modo chica |
| `manager` | — | **No existe** | Referenciado solo en frontend (`cashierRouting.js`) |
| `guardia` | — | **No existe** | Sin implementación |

---

## 2. Matriz por grupo (demo seeder)

Leyenda: **●** = asignado | **○** = no asignado | **S** = super_admin (todos)

### 2.1 Caja y ventas

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| cash.access | ● | ● | ● | ● | ○ | ○ | ○ |
| sales.list | ● | ● | ● | ● | ○ | ○ | ○ |
| sales.charge | ● | ● | ● | ● | ○ | ○ | ○ |
| sales.direct_create | ● | ● | ● | ● | ○ | ○ | ○ |
| shift_console.access | ● | ● | ● | ● | ○ | ○ | ○ |

### 2.2 Comandas

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| orders.access | ● | ● | ● | ● | ● | ○ | ○ |
| orders.create | ● | ● | ● | ● | ● | ○ | ○ |
| orders.add_items | ● | ● | ● | ● | ● | ○ | ○ |
| orders.send_to_bar | ● | ● | ● | ● | ● | ○ | ○ |
| orders.update_items | ● | ● | ● | ● | ○ | ○ | ○ |
| orders.cancel_item | ● | ● | ● | ● | ○ | ○ | ○ |
| orders.update_header | ● | ● | ● | ● | ○ | ○ | ○ |
| orders.cancel | ● | ● | ● | ● | ○ | ○ | ○ |

### 2.3 Liquidaciones

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| settlements.access | ● | ● | ● | ● | ● | ○ | ● |
| settlements.generate | ● | ● | ● | ● | ○ | ○ | ○ |
| settlements.pay | ● | ● | ● | ● | ○ | ○ | ○ |
| settlements.history | ● | ● | ● | ● | ○ | ○ | ○ |
| settlements.pending_sources | ● | ● | ● | ● | ○ | ○ | ○ |

**Hallazgo crítico:** cajera tiene **todos** los slugs settlements en demo. El bloqueo operativo **no es** falta de permiso middleware.

**Diferenciador scope:** `admin.cash_sessions.view` → Owner/Sr.Cajera **●**, Cajera **○**.

### 2.4 Turnos

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| shifts.access | ● | ● | ● | ● | ○ | ○ | ○ |
| shifts.open | ● | ● | ○ | ○ | ○ | ○ | ○ |
| shifts.close | ● | ● | ● | ○ | ○ | ○ | ○ |
| shifts.list | ● | ● | ○ | ○ | ○ | ○ | ○ |

### 2.5 Servicios (manillas, piezas, shows)

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| bracelets.access/create | ● | ● | ● | ● | ○ | ○ | ○ |
| room_services.* | ● | ● | ● | ● | ○ | ○ | ○ |
| shows.access/create | ● | ● | ● | ● | ○ | ○ | ○ |

### 2.6 Habitaciones / catálogo

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| rooms.access | ● | ● | ● | ● | ○ | ○ | ○ |
| rooms.create/update/clean/maintenance | ● | ● | ○ | ○ | ○ | ○ | ○ |
| products.list | ● | ● | ● | ● | ● | ○ | ○ |
| products.create/update/quick_create | ● | ● | ○ | ●¹ | ○ | ○ | ○ |
| product-categories.* | ● | ● | ● | ●² | ● | ○ | ○ |
| product_prices.quick_create | ● | ● | ● | ○ | ○ | ○ | ○ |
| show_types.* | ● | ● | ● | ●³ | ○ | ○ | ○ |

¹ quick_create sí, create/update no  
² list sí, create no  
³ access/create sí, update no

### 2.7 Modos staff

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| waiter.dashboard/orders/my_tables | ○ | ○ | ○ | ○ | ● | ○ | ○ |
| cleaning.dashboard/… | ○ | ○ | ○ | ○ | ○ | ● | ○ |
| girl.dashboard/earnings | ○ | ○ | ○ | ○ | ○ | ○ | ● |

### 2.8 Admin / SaaS / roles

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| admin.tenants.* | ● | ○ | ○ | ○ | ○ | ○ | ○ |
| admin.branches/users.* | ● | ● | ○ | ○ | ○ | ○ | ○ |
| admin.cash_sessions.list/view/summary | ● | ● | ●⁴ | ○ | ○ | ○ | ○ |
| roles.* / permissions.access | ● | ● | ○ | ○ | ○ | ○ | ○ |
| platform.setup | ● | ○ | ○ | ○ | ○ | ○ | ○ |
| reports.access | ● | ● | ○ | ○ | ○ | ○ | ○ |
| audits.list | ● | ● | ○ | ○ | ○ | ○ | ○ |

⁴ Sr.Cajera: list + view + summary

### 2.9 Settings

| Permiso | S | Owner | Sr.Cajera | Cajera | Garzón | Limpieza | Chica |
|---------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| settings.cash_reasons | ● | ● | ● | ● | ○ | ○ | ○ |
| settings.cash_reasons.manage | ● | ● | ● | ○ | ○ | ○ | ○ |
| settings.payment_methods | ● | ● | ● | ● | ○ | ○ | ○ |
| settings.payment_methods.manage | ● | ● | ○ | ○ | ○ | ○ | ○ |
| settings.service_areas | ● | ● | ● | ● | ● | ○ | ○ |
| settings.service_areas.manage | ● | ● | ○ | ○ | ○ | ○ | ○ |
| settings.service_tables | ● | ● | ● | ● | ○ | ○ | ○ |
| settings.service_tables.manage | ● | ● | ● | ○ | ○ | ○ | ○ |
| settings.waiter_assignments(.manage) | ● | ● | ● | ○ | ○ | ○ | ○ |
| settings.room_types(.manage) | ● | ● | ● | ● | ○ | ○ | ○ |
| settings.checklist / bootstrap | ● | ● | ○ | ○ | ○ | ○ | ○ |
| settings.printers(.manage) | ● | ● | ● | ● | ○ | ○ | ○ |
| printing.reprint | ● | ● | ● | ● | ● | ○ | ○ |

---

## 3. Drift: Demo seeder vs Wizard (`TenantDefaultRolePermissions`)

| Aspecto | Demo | Wizard |
|---------|------|--------|
| Rol `cashier_senior` | Existe | **No provisionado** |
| Cajera: `shifts.close` | No | **Sí** |
| Cajera: `shift_console.access` | Sí | Sí |
| Garzón: `settlements.access` | Sí | **No** |
| Chica: `settlements.access` | Sí | **No** |
| Owner: permisos admin/settings extendidos | Completo | Subconjunto (sin printers, audits, roles en lista base) |

**Impacto:** tenants reales pueden tener matriz distinta al demo `cajero.demo`.

---

## 4. Permisos evaluados vs no evaluados

### 4.1 Siempre evaluados (middleware `nightpos.permission` en `routes/api.php`)

~100 rutas con middleware explícito. Todo slug usado en rutas API **sí se evalúa** en requests HTTP correspondientes.

### 4.2 Evaluados solo en use cases (sin middleware dedicado)

Algunos checks inline (`hasPermission` en use cases) duplican o complementan middleware.

### 4.3 Permisos con evaluación limitada / riesgo

| Slug | Evaluación | Observación |
|------|------------|-------------|
| `settlements.access` (waiter/girl) | Middleware GET settlements | Rol staff ve liquidaciones propias vía `SettlementAccessPolicy` + staff scope — **poco uso UI** |
| `admin.cash_sessions.summary` | 1 ruta API | Sin entrada menú cajera básica |
| `platform.setup` | Solo super_admin | Correcto |
| `settings.bootstrap` | API + checklist UI | Solo owner |

### 4.4 Permisos “duplicados” semánticos

| Par | Relación |
|-----|----------|
| `products.create` / `products.quick_create` | Quick es subconjunto operativo caja |
| `settings.*` / `settings.*.manage` | Lectura vs escritura |
| `settlements.access` / `settlements.generate` | Acceso pantalla vs acción |

No son duplicados técnicos; son granularidad CRUD.

---

## 5. Permiso clave no visible como “permiso settlements”

| Slug | Efecto en liquidaciones |
|------|-------------------------|
| **`admin.cash_sessions.view`** | Cambia `SettlementShiftScopeResolver` de `my_cash_session` → `shift`. **Más impacto que `settlements.*` extra.** |

Cajera senior lo tiene; cajera básica no.

---

## 6. Permisos sin rol Manager / Guardia

No hay definición backend. Si se crearan roles custom vía UI admin (`roles.create`), heredarían whitelist de `ManageablePermissionCatalog` — **no incluye** `platform.setup` ni `admin.tenants.*`.

---

## 7. Catálogo gestionable por admin local

`ManageablePermissionCatalog::assignableSlugs()` — whitelist para wizard de roles tenant.  
**No incluye:** `platform.setup`, `admin.tenants.*`, `admin.cash_sessions.summary` (sí list/view), `settings.bootstrap`, `settings.checklist`.

---

## 8. Resumen hallazgos matriz

| Hallazgo | Severidad |
|----------|-----------|
| Cajera demo tiene settlements completos; bug reportado **no es** missing slug | Alta (aclara diagnóstico) |
| `admin.cash_sessions.view` separa cajera de cajera senior en **scope**, no en menú | Alta |
| `cashier_senior` solo en demo | Media |
| Garzón/chica con `settlements.access` en demo pero sin generate/pay | Baja |
| Rol `manager` fantasma en frontend | Baja |
| Permisos editados en BD no refrescan JWT/cookie | Alta (problema 3) |

---

## 9. Referencias

- `SETTLEMENTS_PERMISSION_AUDIT.md` — Diagnóstico liquidaciones
- `SETTLEMENT_SHIFT_SCOPE_DIAGNOSTIC_REPORT.md` — Caso turno stale (histórico)
- `SettlementShiftScopeTest.php` — Tests scope cashier/admin
