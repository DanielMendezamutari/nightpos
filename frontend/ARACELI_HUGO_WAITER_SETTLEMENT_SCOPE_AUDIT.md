# ARACELY / HUGO waiter settlement scope audit

**Status:** Audit completed, no code changes made
**Scope:** Why ARACELY cannot see HUGO's liquidation/payment while LAURA can

## Executive finding

The frontend is not the source of truth for this issue. It simply consumes the current-shift settlements payload and displays whatever the backend scope resolver returns.

For ARACELY, the page is driven by `useCurrentShiftSettlements()`, which calls `GET /settlements/current-shift`. That response already comes back scoped to `my_cash_session`, so the UI only renders the cashier's own session-bound view. LAURA, as admin/owner, receives the broader `shift` scope and therefore sees HUGO's settlement.

## Evidence from the UI flow

- `frontend/src/pages/nightpos/settlements/index.vue` reads the `context.scope` field and labels it as either "mi caja actual" or "el turno".
- The page does not expose a manual selector to widen the current-shift scope. It depends entirely on the backend response.
- `frontend/src/composables/useCurrentShiftSettlements.js` stores `shift`, `summary`, `context`, `sourcesSummary`, `waiters`, `girls`, and `cleaning` from the backend payload without post-processing.
- The history screen is a different route. It does allow an `official_shift_id` filter, but that is not the current settlements screen the cashier is using.

Relevant files:

- [frontend/src/composables/useCurrentShiftSettlements.js](frontend/src/composables/useCurrentShiftSettlements.js)
- [frontend/src/pages/nightpos/settlements/index.vue](frontend/src/pages/nightpos/settlements/index.vue)
- [frontend/src/pages/nightpos/settlements/history.vue](frontend/src/pages/nightpos/settlements/history.vue)

## What the cashier sees

The current settlements page is designed around a live operational scope. For cashier roles, that scope is intentionally narrow:

- `context.scope = my_cash_session`
- `context.cash_session_id = 11`
- `context.cash_session_official_shift_id = 13`

So even if a settlement exists in the database for the same cash session id, it will not appear if it belongs to another official shift id.

## Why this feels like a frontend bug

The page only shows a friendly label like "Mostrando liquidaciones de mi caja actual". If the operator expects a shift-wide list, this can look like a UI omission, but the actual decision is made server-side before the component ever renders.

## Conclusion

No frontend bug was found. The UI is faithfully reflecting the backend scope rules. The visible mismatch is between the operator expectation and the scope rule applied to ARACELY's role.

## Recommended follow-up

Before changing the UI, confirm the business rule:

1. If cashiers should only see their own session, keep the current UI and improve the explanatory copy.
2. If cashiers must see all settlements created for their active caja even when the official shift differs, the backend payload must change first; the frontend can only expose that new scope.
