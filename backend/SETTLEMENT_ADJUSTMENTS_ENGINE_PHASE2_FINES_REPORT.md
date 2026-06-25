# SETTLEMENT_ADJUSTMENTS_ENGINE — Fase 2 Backend (Multas)



**Fecha:** 2026-06-21  

**Estado:** Completado — Fase 2 backend  

**Diseño:** `SETTLEMENT_ADJUSTMENTS_ENGINE_AUDIT.md` (Parte E)



---



## Resumen



Multas como **entidad independiente** (`staff_fines`). No se tocan al generar liquidaciones. Se aplican **opcionalmente al pagar**, con selección explícita por la cajera/admin.



**Fuera de alcance:** descuento manual (Fase 3), ticket settlement payment (Fase 4), frontend.



---



## Migraciones



| Archivo | Contenido |

|---------|-----------|

| `2026_06_21_100092_settlement_adjustments_engine_phase2_fines.php` | Tabla `staff_fines` + FK `staff_fine_id` en `staff_settlement_adjustments` |

| `2026_06_21_100093_add_settlements_fines_manage_permission.php` | Permiso `settlements.fines.manage` → admin + cajera senior |



### Tabla `staff_fines`



Estados: `PENDING` → `APPLIED` | `CANCELLED`



Trazabilidad al aplicar: `applied_settlement_id`, `applied_at`, `applied_by_user_id`



---



## API



| Método | Ruta | Permiso |

|--------|------|---------|

| GET | `/api/v1/staff-fines` | `settlements.fines.manage` |

| POST | `/api/v1/staff-fines` | `settlements.fines.manage` |

| POST | `/api/v1/staff-fines/{id}/cancel` | `settlements.fines.manage` |

| GET | `/api/v1/settlements/{id}/pay-preview` | `settlements.pay` |

| POST | `/api/v1/settlements/{id}/mark-paid` | `settlements.pay` (+ `applied_fine_ids[]`) |



### Pay preview



Query: `applied_fine_ids[]=1&applied_fine_ids[]=2`



Respuesta: `gross_amount`, `adjustments[]` (limpieza + multas simuladas), `net_amount`, `available_fines[]`



### Mark paid



Body ampliado:



```json

{

  "payment_method": "CASH",

  "notes": "...",

  "applied_fine_ids": [1, 2]

}

```



---



## Servicios y casos de uso



| Componente | Rol |

|------------|-----|

| `SettlementFineApplier` | Preview neto, aplicar multas al pagar, dedup `fine:{id}` |

| `SettlementStaffValidator` | Validar personal tenant/sucursal/rol |

| `CreateStaffFineUseCase` | Registrar multa PENDING (turno actual) |

| `CancelStaffFineUseCase` | Cancelar PENDING con motivo |

| `ListStaffFinesUseCase` | Listar con filtros |

| `GetSettlementPayPreviewUseCase` | Simular neto antes de pagar |

| `MarkSettlementPaidUseCase` | Aplica multas → recalcula net → egreso caja |



**Generación liquidaciones:** sin cambios — solo bruto + limpieza (Fase 1).



**Cálculo Fase 2:**



```

neto = bruto + limpieza − Σ(multas seleccionadas)

```



Ejemplo: 100 − 10 − 30 = **60 Bs**



---



## Reglas de negocio



- Multa puede crearse **antes** de existir settlement

- Solo multas `PENDING` del mismo `staff_user_id` + `official_shift_id` + sucursal son elegibles

- Multas no seleccionadas permanecen `PENDING`

- Multas `APPLIED` son inmutables (no cancelar, no re-aplicar)

- Ajuste `MANUAL_FINE` se crea **al confirmar pago**, no al generar

- Cajera básica: requiere caja abierta al crear multa (`cash_session_id`)



---



## Permiso nuevo



`settlements.fines.manage` — admin (`tenant_owner`) y cajera senior por defecto.



---



## Tests



`tests/Feature/Api/V1/SettlementAdjustmentsEnginePhase2FinesTest.php` — **15 passed**



1. Crear multa PENDING  

2. Cancelar multa PENDING  

3. No cancelar multa APPLIED  

4. Multa antes de generate en pay-preview  

5. Pay-preview recalcula neto  

6. Multa no seleccionada queda PENDING  

7. Mark-paid aplica multa  

8. Mark-paid crea ajuste MANUAL_FINE  

9. Cash movement usa net_amount final  

10. Multa APPLIED con settlement_id  

11. Rechaza multa de otra persona  

12. Rechaza multa de otra sucursal  

13. Rechaza multa cancelada  

14. No duplica en reintento (settlement PAID)  

15. Limpieza + multas = neto correcto  



Regresión Fase 1: `SettlementAdjustmentsEnginePhase1Test` — OK.



---



## Próximo paso



**Fase 3:** descuento manual (`MANUAL_DISCOUNT`).  

**Fase 4:** ticket settlement payment.  

**Fase 5:** frontend (`StaffFineDialog`, `SettlementPayFinesSelector`).


