# Auto shift rotation & settlement scope — Backend report

## Resumen

Corrección del bug donde liquidaciones mostraban datos de otros turnos/cajas: rotación de turnos AUTO vencidos, alcance por caja/cajera, y tests/documentación alineados.

## Cambios principales

### 1. Rotación de turnos AUTO (`EnsureOperationalShiftUseCase`)

- Rota turnos AUTO cuando cambia la ventana de negocio (tipo/fecha) **o** cuando `now() > ends_at`.
- Cierra con `markAutoClosed()` y nota `"Cerrado automáticamente por rotación de turno"`.
- Turnos abiertos manualmente por admin **nunca** se auto-cierran.
- Nuevo método `rotateStaleOpenShiftIfNeeded()`: rota solo si hay turno AUTO abierto vencido; **no crea** turno nuevo si no hay ninguno abierto (p. ej. tras cierre fiscal manual).

### 2. Alcance de liquidaciones (`SettlementShiftScopeResolver`)

| Rol | Scope | Comportamiento |
|-----|-------|----------------|
| Cajera (`cashier` + `cash.access`) | `my_cash_session` | Turno de su caja abierta; overview vacío si sin caja, turno distinto al abierto, o sin actividad en la sesión |
| Admin / cajera senior (`admin.cash_sessions.view`) | `shift` | Turno OPEN completo de la sucursal |
| Query `?scope=` | Opcional | Admin puede forzar `my_cash_session` |

Contexto extendido en respuestas: `scope`, `open_shift_id`, `cash_session_official_shift_id`, `shift_rotated`, `empty_overview`.

### 3. Repositorio y use cases

- `cashSessionHasActivity()` — detecta ventas/servicios en caja+turno.
- `countShiftSources()` filtra por `cash_session_id` en scope cajera.
- `GetCurrentShiftSettlementsUseCase`, `GetSettlementPendingSourcesUseCase`, `GenerateCurrentShiftSettlementsUseCase`, `GetCashSessionCloseCheckUseCase` usan el resolver.

### 4. JWT en tests (`JwtAuthRepository`, `Pest.php`)

- `issueTokenForUserId()` limpia guard/JWT tras emitir token (evita que tests autentiquen al admin cuando el Bearer es del cajero).
- Helper `nightposResetApiAuth()` con `JWTAuth::unsetToken()`.

## Tests

- `SettlementShiftScopeTest` — 9/9 (scope cajera, rotación stale, pending sources, historial).
- `ShiftAutoRotationTest` — 5/5.
- Suite completa: **451 tests OK**.

## Archivos clave

- `app/Application/Shift/UseCases/EnsureOperationalShiftUseCase.php`
- `app/Application/StaffSettlement/Services/SettlementShiftScopeResolver.php`
- `app/Application/StaffSettlement/UseCases/GetCurrentShiftSettlementsUseCase.php`
- `app/Infrastructure/Auth/JwtAuthRepository.php`
- `tests/Feature/Api/V1/SettlementShiftScopeTest.php`
