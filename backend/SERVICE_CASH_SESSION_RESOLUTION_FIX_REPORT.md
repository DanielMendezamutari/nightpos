# SERVICE_CASH_SESSION_RESOLUTION_FIX_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Bugfix:** Servicios no detectaban caja abierta (alineación con `/cash/session/current`)  
**Fecha:** 2026-06-08

---

## 1. Causa exacta

El backend ya resolvía caja correctamente con `findOpenForUser(tenant_id, branch_id, user_id)`.

El fallo reportado en producción era **frontend**: `useServiceCashSession` leía mal la respuesta de `fetchCurrentCashSession()`.

`fetchCurrentCashSession()` devuelve el objeto **session** directamente, pero el composable evaluaba `data?.session?.status`, que siempre era `undefined` → el UI mostraba “Debe abrir caja” aunque `/cash/session/current` devolviera `OPEN`.

---

## 2. Resolver único de caja

Nuevo servicio:

`App\Application\Cash\Services\OpenCashSessionResolver`

Método: `findOpenForCurrentUser(int $tenantId, int $branchId, int $userId)`

Usado por:

| Consumidor | Uso |
| ---------- | --- |
| `GetCurrentCashSessionUseCase` | `GET /cash/session/current` |
| `ServiceIncomeCashRecorder` | manilla / pieza / show |
| `RegisterCashMovementUseCase` | movimientos manuales |
| `ChargeOrderUseCase` | cobro de comandas |

Criterio único: `tenant_id` + `branch_id` + `opened_by_user_id` (usuario autenticado) + `status = OPEN`.

---

## 3. Archivos corregidos

| Archivo | Cambio |
| ------- | ------ |
| `OpenCashSessionResolver.php` | **Nuevo** resolver central |
| `ServiceIncomeCashRecorder.php` | Usa resolver |
| `GetCurrentCashSessionUseCase.php` | Usa resolver |
| `RegisterCashMovementUseCase.php` | Usa resolver |
| `ChargeOrderUseCase.php` | Usa resolver |
| `NightPosServiceProvider.php` | Registra singleton |
| `ServiceCashSessionResolutionTest.php` | **Nuevo** — tests cajero + misma sesión |

---

## 4. Validación manual

1. Login cajero PIN 1234  
2. Abrir caja en `/nightpos/cash`  
3. `GET /api/v1/cash/session/current` → `session.status = OPEN`  
4. Registrar pieza / manilla / show → **201** + `cash_session_id` igual al de current  
5. Sin caja propia del cajero → **422** aunque otro usuario tenga caja abierta en la sucursal  

---

## 5. Tests

`tests/Feature/Api/V1/ServiceCashSessionResolutionTest.php`

- Cajero abre caja y registra pieza, manilla, show  
- `/cash/session/current` y servicio usan el mismo `cash_session_id`  
- Caja de otro usuario no habilita registro al cajero  
- Sucursal sin acceso → 403  
- Sin caja → 422 en servicios  
