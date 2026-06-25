<script setup>
import { fetchCurrentCashSession } from '@/api/cash'
import { fetchCashierOrdersByScope, fetchOrder, printOrderPrecheck } from '@/api/orders'
import { chargeOrder } from '@/api/sales'
import ChargeOrderModal from '@/components/nightpos/orders/ChargeOrderModal.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import CashierShell from '@/components/nightpos/cashier/CashierShell.vue'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { useOrderOperationalEvents } from '@/composables/useOrderOperationalEvents'
import { useOperationalPollingFallback } from '@/composables/useOperationalPollingFallback'
import { formatMoney, itemsNeedingAllocation, orderStatusColor, orderStatusLabel } from '@/composables/useOrderHelpers'
import {
  cashierOrderOperationalChips,
  formatWaitingMinutes,
} from '@/composables/useCashierOrderQueue'
import {
  CASHIER_ORDER_TABS,
  orderEmptyMessage,
} from '@/composables/useOrderListTabs'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: 'sales.charge',
    layout: 'blank',
  },
})

const router = useRouter()
const { canChargeOrders } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()

const activeTab = ref('cashier_chargeable')
const orders = ref([])
const loading = ref(false)
const cashSessionOpen = ref(false)

const showChargeDialog = ref(false)
const chargeOrderDetail = ref(null)
const chargeSubmitting = ref(false)
const chargingOrderId = ref(null)
const precheckOrderId = ref(null)
const showOpenCash = ref(false)

const emptyMessage = computed(() => orderEmptyMessage(activeTab.value))
const showChargeActions = computed(() => activeTab.value !== 'billed_recent')

const formatTime = value => {
  if (!value)
    return '—'

  try {
    return new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' })
  }
  catch {
    return value
  }
}

const loadCashSession = async () => {
  if (!canChargeOrders.value) {
    cashSessionOpen.value = false

    return
  }

  try {
    const session = await fetchCurrentCashSession()

    cashSessionOpen.value = session?.status === 'OPEN'
  }
  catch {
    cashSessionOpen.value = false
  }
}

const loadOrders = async () => {
  loading.value = true

  try {
    const tab = CASHIER_ORDER_TABS.find(t => t.value === activeTab.value) ?? CASHIER_ORDER_TABS[0]

    const [orderRows] = await Promise.all([
      fetchCashierOrdersByScope(tab.scope),
      loadCashSession(),
    ])

    orders.value = orderRows ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onTabChange = tab => {
  activeTab.value = tab
  loadOrders()
}

const goCorrect = order => {
  router.push({
    name: 'nightpos-orders-id',
    params: { id: order.id },
    query: { from: 'cashier', mode: 'correction' },
  })
}

const openCharge = async order => {
  if (order?.charge_blocked) {
    notify('Esta comanda no se puede cobrar todavía. Use Corregir para completar pendientes.', 'warning')

    return
  }

  if (!cashSessionOpen.value) {
    notify('Debe abrir su caja antes de cobrar.', 'warning')
    showOpenCash.value = true

    return
  }

  chargingOrderId.value = order.id

  try {
    chargeOrderDetail.value = await fetchOrder(order.id)
    showChargeDialog.value = true
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    chargingOrderId.value = null
  }
}

const closeChargeDialog = () => {
  showChargeDialog.value = false
  chargeOrderDetail.value = null
}

const onChargeConfirm = async payload => {
  if (!chargeOrderDetail.value?.id)
    return

  const { payments, chargePaymentsSum, orderTotal, cashPortion, receivedAmount } = payload

  if (!payments.length) {
    notify('Indique los montos de pago.', 'warning')

    return
  }

  if (Math.abs(chargePaymentsSum - orderTotal) > 0.01) {
    notify('La suma de pagos debe igualar el total de la comanda.', 'warning')

    return
  }

  if (cashPortion > 0 && (!receivedAmount || receivedAmount < cashPortion)) {
    notify('El efectivo recibido debe cubrir la parte en efectivo.', 'warning')

    return
  }

  if (itemsNeedingAllocation(chargeOrderDetail.value).length) {
    notify('Complete el reparto de manillas antes de cobrar.', 'warning')
    closeChargeDialog()
    await loadOrders()

    return
  }

  chargeSubmitting.value = true

  try {
    const result = await chargeOrder(chargeOrderDetail.value.id, payments)
    closeChargeDialog()

    if (result?.print_warning)
      notify(result.print_warning, 'warning')
    else if (result?.print_job?.status === 'FAILED')
      notify('El cobro se registró, pero falló la impresión del ticket.', 'warning')
    else
      notify('Comanda cobrada.')

    await loadOrders()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    await loadOrders()
  }
  finally {
    chargeSubmitting.value = false
  }
}

const onCashOpened = async () => {
  await loadCashSession()
}

const orderChips = order => cashierOrderOperationalChips(order)
const waitingLabel = order => formatWaitingMinutes(order?.waiting_minutes)
const isCardLoading = orderId => chargingOrderId.value === orderId
const isPrecheckLoading = orderId => precheckOrderId.value === orderId

const printPrecheckFromQueue = async order => {
  if (!order?.id)
    return

  precheckOrderId.value = order.id
  try {
    await printOrderPrecheck(order.id)
    notify('Precuenta enviada a impresora.')
  }
  catch (error) {
    notify(getApiErrorMessage(error) || 'No se pudo enviar a impresora.', 'error')
    openPrintRoute({ name: 'nightpos-print-precheck-order-id', params: { id: order.id } })
  }
  finally {
    precheckOrderId.value = null
  }
}

useOrderOperationalEvents(loadOrders, {
  refreshOnCreated: false,
  toastOnSentToBar: true,
  createdDebounceMs: 100,
  updatedDebounceMs: 500,
})

useOperationalPollingFallback(loadOrders)

onMounted(loadOrders)
useOnContextChange(loadOrders)
</script>

<template>
  <CashierShell active-tab="cobrar">
    <div class="cashier-orders-page">
      <div class="mb-4">
        <h4 class="text-h5 mb-1">
          Cola de cobro
        </h4>
        <p class="mb-0 text-body-2 text-medium-emphasis">
          Toque Cobrar en la comanda lista. Use Corregir solo si hay pendientes.
        </p>
      </div>

    <VTabs
      :model-value="activeTab"
      class="mb-4"
      @update:model-value="onTabChange"
    >
      <VTab
        v-for="tab in CASHIER_ORDER_TABS"
        :key="tab.value"
        :value="tab.value"
      >
        {{ tab.label }}
      </VTab>
    </VTabs>

    <VAlert
      v-if="showChargeActions && !cashSessionOpen && !loading"
      type="warning"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Abra su caja para habilitar el cobro en esta cola.
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      color="primary"
      class="mb-4"
    />

    <VAlert
      v-else-if="!orders.length"
      type="info"
      variant="tonal"
    >
      {{ emptyMessage }}
    </VAlert>

    <VRow v-else>
      <VCol
        v-for="order in orders"
        :key="order.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard
          variant="outlined"
          class="cashier-order-card"
          :class="{ 'cashier-order-card--loading': isCardLoading(order.id) }"
        >
          <VOverlay
            :model-value="isCardLoading(order.id)"
            contained
            class="align-center justify-center"
            persistent
          >
            <VProgressCircular
              indeterminate
              color="primary"
            />
          </VOverlay>

          <VCardText>
            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-h6 font-weight-bold">
                {{ order.table_label || 'Sin mesa' }}
              </span>
              <VChip
                size="small"
                :color="orderStatusColor(order.status)"
                variant="tonal"
              >
                {{ orderStatusLabel(order.status) }}
              </VChip>
            </div>

            <div class="text-body-2 text-medium-emphasis mb-1">
              {{ order.order_number }}
            </div>

            <div class="text-body-2 mb-1">
              Garzón: {{ order.waiter_name || '—' }}
            </div>

            <div
              class="text-body-2 mb-1"
              :class="Number(order.waiting_minutes ?? 0) >= 10 ? 'text-warning font-weight-medium' : ''"
            >
              {{ waitingLabel(order) }}
            </div>

            <div
              v-if="orderChips(order).length"
              class="d-flex flex-wrap gap-1 mb-2"
            >
              <VChip
                v-for="chip in orderChips(order)"
                :key="`${order.id}-${chip.key}`"
                size="x-small"
                :color="chip.color"
                variant="tonal"
              >
                {{ chip.label }}
              </VChip>
            </div>

            <div class="text-body-2 mb-1">
              Hora: {{ formatTime(order.opened_at || order.sent_to_bar_at) }}
            </div>

            <div class="text-body-2 mb-3">
              {{ order.items_count ?? 0 }} ítems
            </div>

            <div class="text-h5 font-weight-bold mb-4">
              {{ formatMoney(order.total, order.currency) }}
            </div>

            <div
              v-if="showChargeActions"
              class="d-flex flex-column gap-2"
            >
              <VBtn
                v-if="cashSessionOpen"
                block
                size="x-large"
                color="success"
                prepend-icon="ri-money-dollar-circle-line"
                :disabled="order.charge_blocked || isCardLoading(order.id)"
                :loading="isCardLoading(order.id)"
                @click="openCharge(order)"
              >
                Cobrar
              </VBtn>

              <VBtn
                v-else
                block
                size="x-large"
                color="warning"
                prepend-icon="ri-safe-2-line"
                @click="showOpenCash = true"
              >
                Abrir caja ahora
              </VBtn>

              <VBtn
                block
                size="large"
                color="primary"
                variant="outlined"
                prepend-icon="ri-edit-line"
                :disabled="isCardLoading(order.id) || isPrecheckLoading(order.id)"
                @click="goCorrect(order)"
              >
                Corregir
              </VBtn>

              <VBtn
                block
                size="large"
                variant="tonal"
                prepend-icon="ri-file-list-3-line"
                :loading="isPrecheckLoading(order.id)"
                :disabled="isCardLoading(order.id)"
                @click="printPrecheckFromQueue(order)"
              >
                Imprimir precuenta
              </VBtn>
            </div>

            <VBtn
              v-else
              block
              size="large"
              color="primary"
              variant="tonal"
              prepend-icon="ri-eye-line"
              @click="goCorrect(order)"
            >
              Ver detalle
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <ChargeOrderModal
      v-model="showChargeDialog"
      :order="chargeOrderDetail"
      :cash-session-open="cashSessionOpen"
      :loading="chargeSubmitting"
      @confirm="onChargeConfirm"
      @cash-opened="onCashOpened"
    />

    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />
    </div>
  </CashierShell>
</template>

<style scoped>
.cashier-order-card {
  position: relative;
}

.cashier-order-card--loading {
  pointer-events: none;
}
</style>
