<script setup>

import {

  closeCashSession,

  fetchCashSessionCloseCheck,

  fetchCurrentCashSession,

  openCashSession,

  printCashClose,

} from '@/api/cash'
import { fetchProductReconciliation } from '@/api/reports'
import { fetchCurrentShiftSettlements } from '@/api/settlements'
import ProductReconciliationPanel from '@/components/nightpos/reports/ProductReconciliationPanel.vue'
import ComboBraceletSummaryPanel from '@/components/nightpos/reports/ComboBraceletSummaryPanel.vue'
import CashMovementDialog from '@/components/nightpos/cash/CashMovementDialog.vue'
import CashPhysicalSummaryCard from '@/components/nightpos/cash/CashPhysicalSummaryCard.vue'
import CashPendingOperationsPanel from '@/components/nightpos/cash/CashPendingOperationsPanel.vue'
import CashSalesSummaryPanel from '@/components/nightpos/cash/CashSalesSummaryPanel.vue'
import CashMovementSummaryPanel from '@/components/nightpos/cash/CashMovementSummaryPanel.vue'
import CashScopeContextAlert from '@/components/nightpos/cash/CashScopeContextAlert.vue'

import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { formatMoney } from '@/composables/useOrderHelpers'

import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { getApiErrorMessage } from '@/services/http'



definePage({

  meta: {

    permission: 'cash.access',

  },

})



const { canAccessCash, canDirectSale } = useNightPosPermissions()

const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()
const router = useRouter()
const route = useRoute()



const session = ref(null)

const loading = ref(true)
const apiError = ref(null)

const actionLoading = ref(false)



const showOpen = ref(false)

const showMovement = ref(false)

const showClose = ref(false)
const pendingSettlementsTotal = ref(0)
const pendingSettlementsLoading = ref(false)
const closeCheck = ref(null)
const closeCheckLoading = ref(false)
const showCloseBlockers = ref(false)

const lastClosedSession = ref(null)

const closeReprintLoading = ref(false)

const openForm = ref({ opening_amount: 0, opening_notes: '' })

const closeForm = ref({
  declared_closing_amount: null,
  declared_qr_amount: null,
  declared_card_amount: null,
  closing_notes: '',
})



const fmtBob = amount => formatMoney(amount, 'BOB')

const toNumber = value => {
  const number = Number(value)

  return Number.isFinite(number) ? number : 0
}

const sumKeys = (map, keys) => keys.reduce((acc, key) => acc + toNumber(map?.[key]), 0)

const financialDashboard = computed(() => session.value?.financial_dashboard ?? null)
const legacySummary = computed(() => session.value?.financial_summary ?? null)
const usingLegacyFallback = computed(() => Boolean(session.value) && !financialDashboard.value)

const cashSummaryData = computed(() => {
  const dashboardCash = financialDashboard.value?.cash_summary
  if (dashboardCash)
    return dashboardCash

  const legacy = legacySummary.value ?? {}

  return {
    opening_cash: legacy.opening_cash ?? session.value?.opening_amount ?? 0,
    cash_income_sales: legacy.total_cash ?? 0,
    cash_income_manual: legacy.total_manual_income ?? 0,
    cash_expense_total: legacy.expense_by_method?.cash ?? legacy.total_manual_expense ?? 0,
    expected_cash: legacy.expected_cash ?? session.value?.expected_amount ?? session.value?.opening_amount ?? 0,
    counted_cash: legacy.counted_cash ?? null,
    cash_difference: legacy.cash_difference ?? null,
  }
})

const salesSummaryData = computed(() => {
  const dashboardSales = financialDashboard.value?.sales_summary
  if (dashboardSales)
    return dashboardSales

  const legacy = legacySummary.value ?? {}
  const byMethod = legacy.sales_by_method ?? {}

  return {
    total_sales: legacy.total_sales ?? 0,
    sales_count: legacy.sales_count ?? 0,
    average_ticket: legacy.average_ticket ?? 0,
    sales_cash: byMethod.cash ?? legacy.total_cash ?? 0,
    sales_qr: byMethod.qr ?? legacy.total_qr ?? 0,
    sales_card: byMethod.card ?? legacy.total_card ?? 0,
    mixed_sales_count: legacy.mixed_sales_count ?? 0,
  }
})

const movementSummaryData = computed(() => {
  const dashboardMovement = financialDashboard.value?.movement_summary
  if (dashboardMovement) {
    const expenseByCategory = dashboardMovement.expense_by_category ?? {}
    const incomeByCategory = dashboardMovement.income_by_category ?? {}

    return {
      manual_income: toNumber(incomeByCategory.MANUAL_INCOME) + toNumber(incomeByCategory.OTHER_INCOME),
      settlement_payments: sumKeys(expenseByCategory, [
        'SETTLEMENT_GIRL_PAYMENT',
        'SETTLEMENT_WAITER_PAYMENT',
        'SETTLEMENT_CLEANING_PAYMENT',
      ]),
      operating_expenses: toNumber(expenseByCategory.OPERATING_EXPENSE),
      purchases: toNumber(expenseByCategory.PURCHASE),
      other_expenses: toNumber(expenseByCategory.OTHER_EXPENSE),
    }
  }

  const legacy = legacySummary.value ?? {}
  const expenseByCategory = legacy.expense_by_category ?? {}
  const incomeByCategory = legacy.income_by_category ?? {}

  return {
    manual_income: toNumber(incomeByCategory.MANUAL_INCOME) + toNumber(incomeByCategory.OTHER_INCOME),
    settlement_payments: sumKeys(expenseByCategory, [
      'SETTLEMENT_GIRL_PAYMENT',
      'SETTLEMENT_WAITER_PAYMENT',
      'SETTLEMENT_CLEANING_PAYMENT',
    ]),
    operating_expenses: toNumber(expenseByCategory.OPERATING_EXPENSE),
    purchases: toNumber(expenseByCategory.PURCHASE),
    other_expenses: toNumber(expenseByCategory.OTHER_EXPENSE) || toNumber(legacy.total_manual_expense),
  }
})

const scopeSummaryData = computed(() => {
  const dashboardScope = financialDashboard.value?.scope_summary
  if (dashboardScope)
    return dashboardScope

  return {
    cash_session_id: session.value?.id ?? null,
    session_official_shift_id: session.value?.official_shift_id ?? null,
    current_official_shift_id: null,
    official_shift_ids_included: [],
    crosses_multiple_shifts: false,
    has_open_shift_conflict: false,
    open_cash_sessions_on_historical_shifts: [],
    warnings: [],
  }
})

const pendingSummaryData = computed(() => {
  const settlement = financialDashboard.value?.settlement_summary
  const blockers = closeCheck.value?.blockers ?? []
  const pendingOrdersCount = Number(
    closeCheck.value?.pending_orders_count
    ?? closeCheck.value?.summary?.pending_orders_count
    ?? 0,
  )

  if (settlement) {
    return {
      waiters: toNumber(settlement.waiters?.pending_net_amount),
      girls: toNumber(settlement.girls?.pending_net_amount),
      cleaning: toNumber(settlement.cleaning?.pending_net_amount),
      total: toNumber(settlement.totals?.pending_total_net),
      pending_orders_count: pendingOrdersCount,
      critical_alerts: blockers.map(item => item.message),
    }
  }

  const legacy = legacySummary.value ?? {}

  return {
    waiters: toNumber(legacy.pending_waiters),
    girls: toNumber(legacy.pending_girls),
    cleaning: toNumber(legacy.pending_cleaning),
    total: toNumber(legacy.pending_total),
    pending_orders_count: pendingOrdersCount,
    critical_alerts: blockers.map(item => item.message),
  }
})

const recentMovements = computed(() => {
  const list = session.value?.movements ?? []

  return [...list]
    .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    .slice(0, 8)
})



const expectedClosing = computed(() => {
  if (!session.value)
    return 0

  return toNumber(cashSummaryData.value.expected_cash)

})
const expectedByMethod = computed(() => {
  const fin = legacySummary.value
  const expected = fin?.expected_by_method ?? {}

  return {
    cash: toNumber(cashSummaryData.value.expected_cash ?? expected.cash ?? fin?.expected_cash),
    qr: expected.qr != null ? toNumber(expected.qr) : null,
    card: expected.card != null ? toNumber(expected.card) : null,
  }
})

const closeDifferenceByMethod = computed(() => {
  const form = closeForm.value
  const expected = expectedByMethod.value

  const diff = (declared, methodExpected) => {
    if (declared == null || declared === '' || methodExpected == null)
      return null

    return Number(declared) - methodExpected
  }

  return {
    cash: diff(form.declared_closing_amount, expected.cash),
    qr: diff(form.declared_qr_amount, expected.qr),
    card: diff(form.declared_card_amount, expected.card),
  }
})



const onMovementRegistered = result => {
  if (result?.session)
    session.value = result.session
}



const reconciliation = ref(null)
const reconciliationLoading = ref(false)

const loadReconciliation = async () => {
  if (!session.value?.id)
    return

  reconciliationLoading.value = true
  try {
    reconciliation.value = await fetchProductReconciliation({ cashSessionId: session.value.id })
  }
  catch {
    reconciliation.value = null
  }
  finally {
    reconciliationLoading.value = false
  }
}

const loadSession = async () => {

  loading.value = true



  try {

    session.value = await fetchCurrentCashSession()
    apiError.value = null
    await loadReconciliation()

  }

  catch (error) {
    apiError.value = getApiErrorMessage(error)
    notify(getApiErrorMessage(error), 'error')
    session.value = null

  }

  finally {

    loading.value = false

  }

}



const submitOpen = async () => {

  actionLoading.value = true



  try {

    session.value = await openCashSession({

      opening_amount: Number(openForm.value.opening_amount),

      opening_notes: openForm.value.opening_notes || null,

    })

    showOpen.value = false

    clearOpenCashQuery()

    notify('Caja abierta')

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    actionLoading.value = false

  }

}



const submitClose = async () => {

  actionLoading.value = true



  try {
    const verificationNotes = []
    const form = closeForm.value

    if (form.declared_qr_amount != null && form.declared_qr_amount !== '')
      verificationNotes.push(`QR verificado: ${form.declared_qr_amount}`)

    if (form.declared_card_amount != null && form.declared_card_amount !== '')
      verificationNotes.push(`Tarjeta verificada: ${form.declared_card_amount}`)

    const diff = closeDifferenceByMethod.value
    if (diff.qr != null)
      verificationNotes.push(`Diferencia QR: ${diff.qr.toFixed(2)}`)

    if (diff.card != null)
      verificationNotes.push(`Diferencia tarjeta: ${diff.card.toFixed(2)}`)

    const closingNotes = [form.closing_notes, ...verificationNotes]
      .filter(Boolean)
      .join(' | ') || null

    const result = await closeCashSession({

      declared_closing_amount: Number(form.declared_closing_amount),

      closing_notes: closingNotes,

    })

    lastClosedSession.value = {
      ...(result?.session ?? {}),
      print_job: result?.print_job ?? null,
      print_warning: result?.print_warning ?? null,
    }

    showClose.value = false

    session.value = null

    if (result?.print_warning) {
      notify(result.print_warning, 'warning')
    }
    else if (result?.print_job) {
      notify('Caja cerrada y comprobante enviado a impresora.')
    }
    else {
      notify('Caja cerrada')
    }

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    actionLoading.value = false

  }

}



const openCloseReceipt = () => {
  if (!lastClosedSession.value?.id)
    return

  openPrintRoute({ name: 'nightpos-print-my-cash-session-id', params: { id: lastClosedSession.value.id } })
}

const reprintCloseReceipt = async () => {
  if (!lastClosedSession.value?.id)
    return

  closeReprintLoading.value = true
  try {
    const result = await printCashClose(lastClosedSession.value.id, { reprint: true })
    if (result?.print_warning)
      notify(result.print_warning, 'warning')
    else
      notify('Comprobante de cierre reenviado a impresora.')
  }
  catch (error) {
    notify(getApiErrorMessage(error) || 'No se pudo reimprimir. Puede abrir la vista imprimible.', 'error')
    openCloseReceipt()
  }
  finally {
    closeReprintLoading.value = false
  }
}

const openCloseDialog = async () => {
  closeCheckLoading.value = true
  closeCheck.value = null

  try {
    closeCheck.value = await fetchCashSessionCloseCheck()

    if (!closeCheck.value?.can_close) {
      showCloseBlockers.value = true
      return
    }

    closeForm.value = {
      declared_closing_amount: expectedByMethod.value.cash,
      declared_qr_amount: expectedByMethod.value.qr,
      declared_card_amount: expectedByMethod.value.card,
      closing_notes: '',
    }
    showClose.value = true

    pendingSettlementsLoading.value = true
    try {
      const data = await fetchCurrentShiftSettlements()
      const pending = Number(data.summary?.total_pending ?? 0)
      pendingSettlementsTotal.value = pending
    }
    catch {
      pendingSettlementsTotal.value = pendingSummaryData.value.total
    }
    finally {
      pendingSettlementsLoading.value = false
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    closeCheckLoading.value = false
  }
}



// ─── SSE real-time ──────────────────────────────────────────────────────────
const { on, start: startSse, stop: stopSse, connected: sseConnected, reconnecting: sseReconnecting } = useOperationalEvents()

let cashReloadDebounce = null
const debouncedCashLoad = () => {
  clearTimeout(cashReloadDebounce)
  cashReloadDebounce = setTimeout(loadSession, 600)
}

on('cash.movement.created', debouncedCashLoad)
on('cash.session.opened', debouncedCashLoad)
on('cash.session.closed', debouncedCashLoad)
on('sale.created', debouncedCashLoad)
on('direct_sale.created', debouncedCashLoad)
on('settlement.paid', debouncedCashLoad)
// ─────────────────────────────────────────────────────────────────────────────

const clearOpenCashQuery = () => {
  if (route.query.open == null)
    return

  const nextQuery = { ...route.query }

  delete nextQuery.open
  router.replace({ query: nextQuery })
}

const maybeOpenCashFromQuery = () => {
  if (route.query.open !== '1' || session.value || loading.value)
    return

  showOpen.value = true
  clearOpenCashQuery()
}

const goToSettlements = () => {
  router.push({ name: 'nightpos-settlements' })
}

const goToCharge = async () => {
  try {
    await router.push({ name: 'nightpos-orders' })
  }
  catch {
    await router.push({ name: 'nightpos-cash-direct-sale' })
  }
}

useDialogKeyboardShortcuts({
  active: showOpen,
  onConfirm: submitOpen,
  onCancel: () => { showOpen.value = false },
  canConfirm: () => !actionLoading.value,
  loading: actionLoading,
})

useDialogKeyboardShortcuts({
  active: showClose,
  onConfirm: submitClose,
  onCancel: () => { showClose.value = false },
  canConfirm: () => !actionLoading.value && closeForm.value.declared_closing_amount != null,
  loading: actionLoading,
})

onMounted(async () => {
  await loadSession()
  maybeOpenCashFromQuery()
  startSse()
})

watch([session, loading], () => {
  maybeOpenCashFromQuery()
})

onUnmounted(() => {
  stopSse()
})

useOnContextChange(async () => {
  await loadSession()
})

</script>



<template>

  <div class="cash-page">

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <div class="mb-4 d-flex flex-wrap justify-space-between align-start gap-2">

      <div>
        <h4 class="text-h4 mb-1">
          Caja
        </h4>
        <p class="mb-0 text-body-2">
          Dashboard operativo para cajera basado en financial_dashboard.
        </p>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <VBtn
          variant="tonal"
          prepend-icon="ri-time-line"
          :to="{ name: 'nightpos-finance-cash-sessions-by-cashier' }"
        >
          Historial
        </VBtn>

        <VBtn
          v-if="canDirectSale"
          color="primary"
          size="large"
          prepend-icon="ri-shopping-cart-line"
          :to="{ name: 'nightpos-cash-direct-sale' }"
        >
          Venta directa
        </VBtn>

        <VBtn
          v-if="session"
          variant="tonal"
          prepend-icon="ri-printer-line"
          @click="openPrintRoute({ name: 'nightpos-print-cash' })"
        >
          Imprimir arqueo
        </VBtn>
      </div>

    </div>



    <VProgressLinear

      v-if="loading"

      indeterminate

      color="primary"

      class="mb-4"

    />



    <template v-else-if="!session">

      <VAlert
        v-if="apiError"
        type="error"
        variant="tonal"
        class="mb-4"
      >
        {{ apiError }}
        <VBtn
          size="small"
          variant="text"
          class="ms-2"
          @click="loadSession"
        >
          Reintentar
        </VBtn>
      </VAlert>

      <VAlert
        v-if="lastClosedSession?.id"
        :type="lastClosedSession.print_warning ? 'warning' : 'success'"
        variant="tonal"
        class="mb-4"
        max-width="640"
      >
        <div class="mb-3">
          {{
            lastClosedSession.print_warning
              ? 'Caja cerrada, pero no se pudo imprimir.'
              : 'Caja cerrada y comprobante enviado a impresora.'
          }}
        </div>
        <div class="d-flex flex-wrap gap-2">
          <VBtn
            size="small"
            variant="tonal"
            prepend-icon="ri-file-text-line"
            @click="openCloseReceipt"
          >
            Ver cierre
          </VBtn>
          <VBtn
            size="small"
            variant="tonal"
            prepend-icon="ri-printer-line"
            :loading="closeReprintLoading"
            @click="reprintCloseReceipt"
          >
            Reimprimir cierre
          </VBtn>
        </div>
      </VAlert>

      <VCard max-width="520">

        <VCardText>

          <VAlert

            type="info"

            variant="tonal"

            class="mb-4"

          >

            No hay caja abierta. Indique el fondo inicial para comenzar a cobrar.

          </VAlert>

          <VBtn

            v-if="canAccessCash"

            color="primary"

            size="x-large"

            block

            @click="showOpen = true"

          >

            <VIcon

              icon="ri-lock-unlock-line"

              start

            />

            Abrir caja

          </VBtn>

        </VCardText>

      </VCard>

    </template>



    <template v-else>

      <VAlert
        v-if="usingLegacyFallback"
        type="warning"
        variant="tonal"
        class="mb-4"
      >
        financial_dashboard ausente. Se activo fallback legacy temporal (financial_summary).
      </VAlert>

      <VRow class="mb-4">
        <VCol cols="12">
          <CashPhysicalSummaryCard
            :summary="cashSummaryData"
            :session-status="session.status"
            :opened-at="session.opened_at"
          />
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12">
          <CashPendingOperationsPanel
            :pending="pendingSummaryData"
            @go-settlements="goToSettlements"
            @go-charge="goToCharge"
          />
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12">
          <CashSalesSummaryPanel :sales="salesSummaryData" />
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12">
          <CashMovementSummaryPanel
            :movement="movementSummaryData"
            :recent-movements="recentMovements"
          />
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12">
          <CashScopeContextAlert :scope="scopeSummaryData" />
        </VCol>
      </VRow>



      <VCard v-if="reconciliation" class="mb-4">
        <VCardTitle>Productos vendidos</VCardTitle>
        <VCardText>
          <ComboBraceletSummaryPanel
            v-if="session?.combo_bracelets?.total_bracelet_units"
            :summary="session.combo_bracelets"
            compact
            class="mb-4"
          />

          <ProductReconciliationPanel
            :data="reconciliation"
            :loading="reconciliationLoading"
            title=""
          />
        </VCardText>
      </VCard>



      <div

        v-if="session.status === 'OPEN' && canAccessCash"

        class="cash-page__actions"

      >

        <VBtn

          color="success"

          size="x-large"

          class="mb-3"

          block

          @click="showMovement = true"

        >

          <VIcon

            icon="ri-add-line"

            start

          />

          Ingreso / egreso manual

        </VBtn>

        <VBtn

          color="error"

          variant="elevated"

          size="x-large"

          block

          :loading="closeCheckLoading"

          @click="openCloseDialog"

        >

          <VIcon

            icon="ri-lock-line"

            start

          />

          Cerrar caja

        </VBtn>

      </div>

    </template>



    <VDialog

      v-model="showOpen"

      max-width="440"

    >

      <VCard title="Abrir caja">

        <VCardText>

          <VTextField

            v-model.number="openForm.opening_amount"

            type="number"

            label="Fondo inicial (BOB)"

            min="0"

            class="mb-4"

          />

          <VTextField

            v-model="openForm.opening_notes"

            label="Notas (opcional)"

          />

        </VCardText>

        <VCardActions>

          <VBtn

            variant="text"

            @click="showOpen = false"

          >

            Cancelar

          </VBtn>

          <VSpacer />

          <VBtn

            color="primary"

            size="large"

            :loading="actionLoading"

            @click="submitOpen"

          >

            Abrir

          </VBtn>

        </VCardActions>

      </VCard>

    </VDialog>



    <CashMovementDialog
      v-model="showMovement"
      @registered="onMovementRegistered"
    />

    <VDialog
      v-model="showCloseBlockers"
      max-width="520"
    >
      <VCard title="No puedes cerrar caja todavía">
        <VCardText>
          <VAlert
            type="error"
            variant="tonal"
            class="mb-4"
          >
            Resuelve los pendientes operativos antes de cerrar tu caja.
          </VAlert>

          <ComboBraceletSummaryPanel
            v-if="closeCheck?.combo_bracelets?.total_bracelet_units"
            :summary="closeCheck.combo_bracelets"
            compact
            class="mb-4"
          />

          <VAlert
            v-for="blocker in closeCheck?.blockers ?? []"
            :key="blocker.code"
            type="error"
            variant="tonal"
            density="compact"
            class="mb-2"
          >
            <div class="d-flex flex-wrap align-center justify-space-between gap-2">
              <span>{{ blocker.message }}</span>
              <VBtn
                v-if="blocker.route"
                size="x-small"
                variant="tonal"
                color="primary"
                @click="router.push({ name: blocker.route }); showCloseBlockers = false"
              >
                Ir
              </VBtn>
            </div>
          </VAlert>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <VBtn
              v-for="action in closeCheck?.actions ?? []"
              :key="action.route"
              size="small"
              variant="tonal"
              color="primary"
              @click="router.push({ name: action.route }); showCloseBlockers = false"
            >
              {{ action.label }}
            </VBtn>
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="showCloseBlockers = false"
          >
            Entendido
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog

      v-model="showClose"

      max-width="640"

    >

      <VCard title="Cerrar caja">

        <VCardText>

          <VAlert
            v-if="pendingSettlementsTotal > 0"
            type="warning"
            variant="tonal"
            class="mb-4"
          >
            Tienes <strong>liquidaciones pendientes</strong> por <strong>{{ fmtBob(pendingSettlementsTotal) }}</strong>.
            Si las pagas ahora, se descontarán de tu caja.
            <VBtn
              size="small"
              variant="text"
              color="warning"
              class="ms-2"
              :to="{ name: 'nightpos-settlements' }"
              @click="showClose = false"
            >
              Ir a Liquidaciones
            </VBtn>
          </VAlert>

          <VTable density="compact" class="mb-4">
            <thead>
              <tr>
                <th>Método</th>
                <th>Esperado</th>
                <th>Declarado</th>
                <th>Diferencia</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Efectivo</td>
                <td>{{ fmtBob(expectedByMethod.cash) }}</td>
                <td>{{ closeForm.declared_closing_amount == null ? '—' : fmtBob(closeForm.declared_closing_amount) }}</td>
                <td>{{ closeDifferenceByMethod.cash == null ? '—' : fmtBob(closeDifferenceByMethod.cash) }}</td>
              </tr>
              <tr>
                <td>QR</td>
                <td>{{ expectedByMethod.qr == null ? 'No disponible' : fmtBob(expectedByMethod.qr) }}</td>
                <td>{{ closeForm.declared_qr_amount == null ? '—' : fmtBob(closeForm.declared_qr_amount) }}</td>
                <td>{{ closeDifferenceByMethod.qr == null ? '—' : fmtBob(closeDifferenceByMethod.qr) }}</td>
              </tr>
              <tr>
                <td>Tarjeta</td>
                <td>{{ expectedByMethod.card == null ? 'No disponible' : fmtBob(expectedByMethod.card) }}</td>
                <td>{{ closeForm.declared_card_amount == null ? '—' : fmtBob(closeForm.declared_card_amount) }}</td>
                <td>{{ closeDifferenceByMethod.card == null ? '—' : fmtBob(closeDifferenceByMethod.card) }}</td>
              </tr>
            </tbody>
          </VTable>

          <VDivider class="mb-4" />

          <p class="text-subtitle-2 mb-3">
            Debe declarar
          </p>

          <VTextField

            v-model.number="closeForm.declared_closing_amount"

            type="number"

            label="Efectivo contado (BOB)"

            min="0"

            class="mb-1"

          />

          <p
            v-if="closeDifferenceByMethod.cash != null"
            class="text-body-2 mb-3"
            :class="closeDifferenceByMethod.cash === 0 ? 'text-success' : 'text-warning'"
          >
            Diferencia efectivo:
            <strong>{{ fmtBob(closeDifferenceByMethod.cash) }}</strong>
          </p>

          <VTextField

            v-model.number="closeForm.declared_qr_amount"

            type="number"

            label="QR verificado (BOB)"

            min="0"

            class="mb-1"

          />

          <p
            v-if="closeDifferenceByMethod.qr != null"
            class="text-body-2 mb-3"
            :class="closeDifferenceByMethod.qr === 0 ? 'text-success' : 'text-info'"
          >
            Diferencia QR:
            <strong>{{ fmtBob(closeDifferenceByMethod.qr) }}</strong>
          </p>

          <VTextField

            v-model.number="closeForm.declared_card_amount"

            type="number"

            label="Tarjeta verificada (BOB)"

            min="0"

            class="mb-1"

          />

          <p
            v-if="closeDifferenceByMethod.card != null"
            class="text-body-2 mb-4"
            :class="closeDifferenceByMethod.card === 0 ? 'text-success' : 'text-info'"
          >
            Diferencia tarjeta:
            <strong>{{ fmtBob(closeDifferenceByMethod.card) }}</strong>
          </p>

          <VTextField

            v-model="closeForm.closing_notes"

            label="Notas de cierre"

          />

        </VCardText>

        <VCardActions>

          <VBtn

            variant="text"

            @click="showClose = false"

          >

            Cancelar

          </VBtn>

          <VSpacer />

          <VBtn

            color="error"

            size="large"

            :loading="actionLoading"

            @click="submitClose"

          >

            Confirmar cierre

          </VBtn>

        </VCardActions>

      </VCard>

    </VDialog>
</div>

</template>



<style scoped>

.cash-page__actions {

  max-width: 520px;

  margin-inline: auto;

}

</style>

