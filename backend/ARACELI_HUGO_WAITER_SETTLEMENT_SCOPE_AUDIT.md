# Auditoría de alcance de liquidación ARACELY / HUGO

**Estado:** Auditoría completada, sin cambios de código
**Alcance:** Por qué la cajera ARACELY no ve la liquidación/pago de HUGO mientras LAURA admin sí puede verla
**Fuente de verdad:** Base MySQL real `nigtpos`

## Hallazgo ejecutivo

El problema no es que falte una liquidación. El problema es un desajuste de alcance.

ARACELY se resuelve como cajera, por lo que el backend fuerza la pantalla de liquidaciones actuales al alcance `my_cash_session`. Su caja abierta es `cash_session_id = 11`, y esa caja se abrió cuando el turno de sesión era `official_shift_id = 13`. Las ventas reales de HUGO y la liquidación de garzón resultante se generaron después bajo `official_shift_id = 19`.

Como el alcance de cajera filtra el resumen por `official_shift_id = 13` y `cash_session_id = 11`, la liquidación de HUGO en el turno 19 queda fuera. LAURA es dueña/admin de tenant, así que resuelve al alcance completo `shift` y sí ve la liquidación de todo el turno.

## Evidencia de base de datos

- ARACELY es la usuaria cajera `id = 28`, `tenant_id = 2`, `branch_id = 2`, `role_id = 9`, `staff_role = CASHIER`.
- LAURA es la usuaria `id = 10`, `tenant_id = 2`, `branch_id = 2`, `role_slug = tenant_owner`, `staff_role = MANAGER`.
- HUGO es la usuaria `id = 18`, `tenant_id = 2`, `branch_id = 2`, `role_slug = waiter`, `staff_role = WAITER`, `waiter_commission_percent = 0.00`.
- La caja abierta de ARACELY es `cash_session_id = 11`, vinculada a `official_shift_id = 13`.
- Las filas reales de ventas de HUGO en la base están en `official_shift_id = 19` con `cash_session_id = 11`.
- La liquidación pendiente de HUGO es `staff_settlements.id = 21`, `official_shift_id = 19`, `cash_session_id = 11`, `status = PENDING`, `settlement_type = WAITER`.
- La misma caja también contiene otra liquidación de RAY (`id = 19`) y filas más antiguas de una sesión cerrada distinta, lo que confirma que no falta un registro en la tabla de liquidaciones.
- Las consultas reales de ventas para `HUGO`/`ARACELY` no devolvieron filas en la importación actual, por lo que el problema visible está en la capa de liquidación generada y no en una búsqueda directa de ventas.

## Ruta técnica backend

La decisión de alcance ocurre en `SettlementShiftScopeResolver`.

- Las cajeras sin `admin.cash_sessions.view` son forzadas a `my_cash_session`.
- Para ese alcance, el resolver devuelve el `shift_id` de la caja abierta, no el turno operativo abierto.
- `GetCurrentShiftSettlementsUseCase` llama después a `getCurrentShiftOverview()` con ese `shift_id` resuelto y con el `cash_session_id`.

Eso significa que la vista de cajera es intencionalmente más estrecha que la vista de admin.

Código relevante:

- [backend/app/Application/StaffSettlement/Services/SettlementShiftScopeResolver.php](backend/app/Application/StaffSettlement/Services/SettlementShiftScopeResolver.php)
- [backend/app/Application/StaffSettlement/UseCases/GetCurrentShiftSettlementsUseCase.php](backend/app/Application/StaffSettlement/UseCases/GetCurrentShiftSettlementsUseCase.php)
- [backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php](backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php)

## Por qué ARACELY no ve a HUGO

La diferencia decisiva es esta:

- Alcance de ARACELY: `my_cash_session`
- Turno de sesión de ARACELY al abrir caja: `official_shift_id = 13`
- Turno de ventas/liquidación de HUGO: `official_shift_id = 19`

La consulta de resumen filtra las liquidaciones por el `shift_id` resuelto. La fila de HUGO está asociada al turno 19, por lo que queda fuera del alcance de ARACELY aunque el `cash_session_id` coincida.

Eso también explica por qué LAURA sí puede verlo: el acceso de admin/owner resuelve a `shift`, no a `my_cash_session`.

## Nota adicional

La importación real muestra que HUGO y RAY tienen `waiter_commission_percent = 0.00`, por lo que sus liquidaciones de garzón se registran como filas pendientes/manuales y no como comisiones automáticas pagadas. Eso las hace más sensibles a errores de alcance y a ventanas parciales de generación.

## Conclusión

Esto es una discrepancia real de datos y alcance, no un bug de renderizado del frontend ni una liquidación inexistente. La vista de cajera está haciendo lo que las reglas de alcance actuales le indican, pero esas reglas son demasiado estrechas para la expectativa operativa en este caso porque la liquidación se generó bajo un turno oficial distinto al de la caja abierta.

## Recomendación

No cambiar código todavía. La siguiente decisión de implementación debería resolver una de estas rutas:

1. Mantener el alcance de cajera estricto y comunicar que solo ve las liquidaciones generadas para su propia caja abierta.
2. Si la regla de negocio es que la cajera debe ver todas las liquidaciones ligadas a su caja aunque el turno oficial registrado sea otro, entonces el filtro del resumen debe ampliarse para usar el límite de sesión y no el límite del turno de la sesión.
3. Aplicar una corrección/migración de datos si el turno oficial de la fila de liquidación está mal y debió ser `13` en lugar de `19`.
