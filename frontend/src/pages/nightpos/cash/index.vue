<script setup>

import {

  closeCashSession,

  fetchCashSessionCloseCheck,

  fetchCurrentCashSession,

  openCashSession,

} from '@/api/cash'
import { fetchProductReconciliation } from '@/api/reports'
import { fetchCurrentShiftSettlements } from '@/api/settlements'
import ProductReconciliationPanel from '@/components/nightpos/reports/ProductReconciliationPanel.vue'
import ComboBraceletSummaryPanel from '@/components/nightpos/reports/ComboBraceletSummaryPanel.vue'
import CashMovementDialog from '@/components/nightpos/cash/CashMovementDialog.vue'
import { paymentMethodLabel } from '@/constants/paymentMethods'

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



const { canAccessCash, canDirectSale, can } = useNightPosPermissions()

const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()
const router = useRouter()
const route = useRoute()



const session = ref(null)

const loading = ref(true)

const actionLoading = ref(false)



const showOpen = ref(false)

const showMovement = ref(false)

const showClose = ref(false)

const openForm = ref({ opening_amount: 0, opening_notes: '' })

const closeForm = ref({
  declared_closing_amount: null,
  declared_qr_amount: null,
  declared_card_amount: null,
  closing_notes: '',
})



const fmtBob = amount => formatMoney(amount, 'BOB')



const expectedClosing = computed(() => {

  if (!session.value)

    return 0



  const fin = session.value.financial_summary

  if (fin?.expected_cash != null && fin.expected_cash !== '')

    return Number(fin.expected_cash)



  const fromApi = session.value.expected_amount

  if (fromApi != null && fromApi !== '')

    return Number(fromApi)



  // Fallback using manual movements only (not cobros)
  const fin2 = session.value.financial_summary
  if (fin2) {
    return Number(session.value.opening_amount)
      + Number(fin2.total_cash ?? 0)
      + Number(fin2.total_manual_income ?? 0)
      - Number(fin2.total_manual_expense ?? 0)
  }

  return Number(session.value.opening_amount)

    + Number(session.value.income_total)

    - Number(session.value.expense_total)

})



const closeDifferencePreview = computed(() => {

  const declared = closeForm.value.declared_closing_amount



  if (declared == null || declared === '')

    return null



  return Number(declared) - expectedClosing.value

})

const expectedByMethod = computed(() => {
  const fin = session.value?.financial_summary
  const expected = fin?.expected_by_method ?? {}

  return {
    cash: Number(expected.cash ?? fin?.expected_cash ?? expectedClosing.value),
    qr: Number(expected.qr ?? fin?.expected_qr ?? 0),
    card: Number(expected.card ?? fin?.expected_card ?? 0),
  }
})

const closeDifferenceByMethod = computed(() => {
  const form = closeForm.value
  const expected = expectedByMethod.value

  const diff = (declared, methodExpected) => {
    if (declared == null || declared === '')
      return null

    return Number(declared) - methodExpected
  }

  return {
    cash: diff(form.declared_closing_amount, expected.cash),
    qr: diff(form.declared_qr_amount, expected.qr),
    card: diff(form.declared_card_amount, expected.card),
  }
})



const kpiCards = computed(() => {

  if (!session.value)

    return []



  const s = session.value

  const sales = s.sales_by_method ?? {}



  return [

    {

      title: 'Estado de caja',

      color: s.status === 'OPEN' ? 'success' : 'secondary',

      icon: 'ri-safe-2-line',

      stats: s.status === 'OPEN' ? 'Abierta' : 'Cerrada',

      change: 0,

      subtitle: `Sesión #${s.id}`,

    },

    {

      title: 'Fondo inicial',

      color: 'primary',

      icon: 'ri-wallet-3-line',

      stats: fmtBob(s.opening_amount),

      change: 0,

      subtitle: 'Apertura',

    },

    {

      title: 'Efectivo (ventas)',

      color: 'success',

      icon: 'ri-money-dollar-box-line',

      stats: fmtBob(sales.cash ?? 0),

      change: 0,

      subtitle: 'Cobros en efectivo',

    },

    {

      title: 'QR (ventas)',

      color: 'info',

      icon: 'ri-qr-code-line',

      stats: fmtBob(sales.qr ?? 0),

      change: 0,

      subtitle: 'Cobros QR',

    },

    {

      title: 'Tarjeta (ventas)',

      color: 'warning',

      icon: 'ri-bank-card-line',

      stats: fmtBob(sales.card ?? 0),

      change: 0,

      subtitle: 'Cobros tarjeta',

    },

    {

      title: 'Ingresos manuales',

      color: 'success',

      icon: 'ri-add-circle-line',

      stats: fmtBob(s.financial_summary?.total_manual_income ?? s.income_total),

      change: 0,

      subtitle: 'Entradas manuales de caja',

    },

    {

      title: 'Egresos manuales',

      color: 'error',

      icon: 'ri-indeterminate-circle-line',

      stats: fmtBob(s.financial_summary?.total_manual_expense ?? s.expense_total),

      change: 0,

      subtitle: 'Salidas manuales de caja',

    },

    {

      title: 'Total esperado',

      color: 'secondary',

      icon: 'ri-calculator-line',

      stats: fmtBob(expectedClosing.value),

      change: 0,

      subtitle: 'En caja según sistema',

    },

  ]

})

const methodBalanceRows = computed(() => {
  if (!session.value?.financial_summary)
    return []

  const fin = session.value.financial_summary
  const income = fin.income_by_method ?? {}
  const expense = fin.expense_by_method ?? {}
  const expected = fin.expected_by_method ?? {}
  const sales = fin.sales_by_method ?? {
    cash: fin.sales_cash ?? fin.total_cash ?? '0.00',
    qr: fin.sales_qr ?? fin.total_qr ?? '0.00',
    card: fin.sales_card ?? fin.total_card ?? '0.00',
  }
  const openingCash = fin.opening_cash ?? session.value.opening_amount ?? '0.00'

  return [
    { key: 'cash', label: 'Efectivo', color: 'success' },
    { key: 'qr', label: 'QR', color: 'info' },
    { key: 'card', label: 'Tarjeta', color: 'warning' },
  ].map(row => ({
    ...row,
    opening: row.key === 'cash' ? openingCash : null,
    sales: sales[row.key] ?? '0.00',
    income: income[row.key] ?? fin[`income_${row.key}`] ?? '0.00',
    expense: expense[row.key] ?? fin[`expense_${row.key}`] ?? '0.00',
    expected: expected[row.key] ?? fin[`expected_${row.key}`] ?? '0.00',
  }))
})

const onMovementRegistered = updatedSession => {
  session.value = updatedSession
}

const movementHeaders = [

  { title: 'Tipo', key: 'movement_type' },

  { title: 'Monto', key: 'amount' },

  { title: 'Método', key: 'payment_method' },

  { title: 'Descripción', key: 'description' },

  { title: 'Fecha', key: 'created_at' },

]



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
    await loadReconciliation()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

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

    await closeCashSession({

      declared_closing_amount: Number(form.declared_closing_amount),

      closing_notes: closingNotes,

    })

    showClose.value = false

    session.value = null

    notify('Caja cerrada')

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    actionLoading.value = false

  }

}



const pendingSettlementsTotal = ref(0)
const pendingSettlementsLoading = ref(false)
const closeCheck = ref(null)
const closeCheckLoading = ref(false)
const showCloseBlockers = ref(false)

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
      pendingSettlementsTotal.value = 0
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



const formatMovementDate = value => {

  if (!value)

    return '—'



  try {

    return new Date(value).toLocaleString('es-BO', {

      dateStyle: 'short',

      timeStyle: 'short',

    })

  }

  catch {

    return value

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
          Apertura, movimientos manuales, cobros por método y cierre de sesión.
        </p>
      </div>

      <div class="d-flex flex-wrap gap-2">
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

      <VRow class="match-height mb-4">

        <VCol

          v-for="card in kpiCards"

          :key="card.title"

          cols="12"

          sm="6"

          lg="3"

        >

          <CardStatisticsVertical v-bind="card" />

        </VCol>

      </VRow>



      <VCard
        v-if="methodBalanceRows.length"
        class="mb-4"
        title="Resumen por método de pago"
      >
        <VCardText>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Método</th>
                <th>Inicial</th>
                <th>Ingresos</th>
                <th>Ventas</th>
                <th>Egresos</th>
                <th>Esperado / neto</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in methodBalanceRows"
                :key="row.key"
              >
                <td>
                  <VChip
                    size="small"
                    :color="row.color"
                    variant="tonal"
                  >
                    {{ row.label }}
                  </VChip>
                </td>
                <td>{{ row.opening != null ? fmtBob(row.opening) : '—' }}</td>
                <td>{{ fmtBob(row.income) }}</td>
                <td>{{ fmtBob(row.sales) }}</td>
                <td>{{ fmtBob(row.expense) }}</td>
                <td><strong>{{ fmtBob(row.expected) }}</strong></td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>
      </VCard>



      <VCard class="mb-4">

        <VCardTitle class="d-flex flex-wrap align-center gap-2">

          Movimientos de caja

          <VChip

            v-if="session.status === 'OPEN'"

            color="success"

            label

            size="small"

          >

            Sesión activa

          </VChip>

        </VCardTitle>

        <VCardText>

          <VDataTable

            :items="session.movements ?? []"

            :headers="movementHeaders"

            density="comfortable"

            :items-per-page="10"

            class="text-no-wrap"

          >

            <template #item.movement_type="{ item }">

              <VChip

                size="small"

                label

                :color="item.movement_type === 'INCOME' ? 'success' : 'warning'"

              >

                {{ item.movement_type === 'INCOME' ? 'Ingreso' : 'Egreso' }}

              </VChip>

            </template>

            <template #item.amount="{ item }">

              {{ fmtBob(item.amount) }}

            </template>

            <template #item.payment_method="{ item }">

              <VChip

                v-if="item.payment_method"

                size="x-small"

                variant="tonal"

              >

                {{ paymentMethodLabel(item.payment_method) }}

              </VChip>

              <span v-else>—</span>

            </template>

            <template #item.created_at="{ item }">

              {{ formatMovementDate(item.created_at) }}

            </template>

            <template #no-data>

              Sin movimientos registrados.

            </template>

          </VDataTable>

        </VCardText>

      </VCard>



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

          <p class="text-subtitle-2 mb-2">
            Resumen por método
          </p>

          <VTable
            v-if="methodBalanceRows.length"
            density="compact"
            class="mb-4"
          >
            <thead>
              <tr>
                <th>Método</th>
                <th>Inicial</th>
                <th>Ingresos</th>
                <th>Ventas</th>
                <th>Egresos</th>
                <th>Esperado</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in methodBalanceRows"
                :key="`close-${row.key}`"
              >
                <td>{{ row.label }}</td>
                <td>{{ row.opening != null ? fmtBob(row.opening) : '—' }}</td>
                <td>{{ fmtBob(row.income) }}</td>
                <td>{{ fmtBob(row.sales) }}</td>
                <td>{{ fmtBob(row.expense) }}</td>
                <td><strong>{{ fmtBob(row.expected) }}</strong></td>
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

