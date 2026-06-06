<script setup>

import {

  closeCashSession,

  fetchCurrentCashSession,

  openCashSession,

  registerCashMovement,

} from '@/api/cash'

import { fetchCashMovementReasons } from '@/api/cashMovementReasons'
import { fetchProductReconciliation } from '@/api/reports'
import { fetchCurrentShiftSettlements } from '@/api/settlements'
import ProductReconciliationPanel from '@/components/nightpos/reports/ProductReconciliationPanel.vue'

import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOperationalEvents } from '@/composables/useOperationalEvents'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { formatMoney } from '@/composables/useOrderHelpers'

import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { getApiErrorMessage } from '@/services/http'



definePage({

  meta: {

    permission: 'cash.access',

  },

})



const { canAccessCash, canDirectSale } = useNightPosPermissions()

const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()



const session = ref(null)

const loading = ref(true)

const actionLoading = ref(false)



const showOpen = ref(false)

const showMovement = ref(false)

const showClose = ref(false)



const openForm = ref({ opening_amount: 0, opening_notes: '' })

const movementReasons = ref([])

const movementForm = ref({
  movement_type: 'EXPENSE',
  amount: null,
  cash_movement_reason_id: null,
  notes: '',
})

const movementReasonOptions = computed(() => movementReasons.value
  .filter(r => r.status === 'active' && r.type === movementForm.value.movement_type)
  .map(r => ({ title: r.name, value: r.id })))

const loadMovementReasons = async () => {
  if (!canAccessCash.value)
    return
  try {
    movementReasons.value = await fetchCashMovementReasons({ active_only: true })
  }
  catch {
    movementReasons.value = []
  }
}

const closeForm = ref({ declared_closing_amount: null, closing_notes: '' })



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

    notify('Caja abierta')

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    actionLoading.value = false

  }

}



const submitMovement = async () => {

  actionLoading.value = true



  try {

    session.value = await registerCashMovement({

      movement_type: movementForm.value.movement_type,

      amount: Number(movementForm.value.amount),

      cash_movement_reason_id: movementForm.value.cash_movement_reason_id,

      notes: movementForm.value.notes || null,

    })

    showMovement.value = false

    movementForm.value = {
      movement_type: 'EXPENSE',
      amount: null,
      cash_movement_reason_id: null,
      notes: '',
    }

    notify('Movimiento registrado')

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

    await closeCashSession({

      declared_closing_amount: Number(closeForm.value.declared_closing_amount),

      closing_notes: closeForm.value.closing_notes || null,

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

const openCloseDialog = async () => {

  closeForm.value.declared_closing_amount = expectedClosing.value

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



watch(() => movementForm.value.movement_type, () => {
  movementForm.value.cash_movement_reason_id = null
})

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

onMounted(async () => {
  await Promise.all([loadSession(), loadMovementReasons()])
  startSse()
})

onUnmounted(() => {
  stopSse()
})

useOnContextChange(async () => {
  await Promise.all([loadSession(), loadMovementReasons()])
})

</script>



<template>

  <div class="cash-page">

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

                {{ item.payment_method }}

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



    <VDialog

      v-model="showMovement"

      max-width="440"

    >

      <VCard title="Registrar movimiento">

        <VCardText>

          <VSelect

            v-model="movementForm.movement_type"

            :items="[

              { title: 'Ingreso manual', value: 'INCOME' },

              { title: 'Egreso manual', value: 'EXPENSE' },

            ]"

            label="Tipo"

            class="mb-4"

          />

          <VTextField

            v-model.number="movementForm.amount"

            type="number"

            label="Monto (BOB)"

            min="0.01"

            class="mb-4"

          />

          <VSelect

            v-model="movementForm.cash_movement_reason_id"

            :items="movementReasonOptions"

            label="Motivo *"

            class="mb-4"

          />

          <VTextField

            v-model="movementForm.notes"

            label="Notas adicionales"

          />

        </VCardText>

        <VCardActions>

          <VBtn

            variant="text"

            @click="showMovement = false"

          >

            Cancelar

          </VBtn>

          <VSpacer />

          <VBtn

            color="primary"

            size="large"

            :loading="actionLoading"

            @click="submitMovement"

          >

            Guardar

          </VBtn>

        </VCardActions>

      </VCard>

    </VDialog>



    <VDialog

      v-model="showClose"

      max-width="480"

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

          <VAlert

            type="info"

            variant="tonal"

            class="mb-4"

          >

            Monto esperado según sistema:

            <strong>{{ fmtBob(expectedClosing) }}</strong>

          </VAlert>

          <VTextField

            v-model.number="closeForm.declared_closing_amount"

            type="number"

            label="Efectivo contado (BOB)"

            min="0"

            class="mb-2"

          />

          <p

            v-if="closeDifferencePreview != null"

            class="text-body-2 mb-4"

            :class="closeDifferencePreview === 0 ? 'text-success' : 'text-warning'"

          >

            Diferencia (contado − esperado):

            <strong>{{ fmtBob(closeDifferencePreview) }}</strong>

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

