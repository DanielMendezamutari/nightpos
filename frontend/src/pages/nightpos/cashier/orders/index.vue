<script setup>

import { fetchCurrentCashSession } from '@/api/cash'

import { fetchOrdersByScope } from '@/api/orders'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useOnContextChange } from '@/composables/useOnContextChange'

import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useOperationalEvents } from '@/composables/useOperationalEvents'

import { formatMoney, orderStatusColor, orderStatusLabel } from '@/composables/useOrderHelpers'

import {

  CASHIER_ORDER_TABS,

  orderEmptyMessage,

} from '@/composables/useOrderListTabs'

import { getApiErrorMessage } from '@/services/http'



definePage({

  meta: {

    permission: 'sales.charge',

  },

})



const router = useRouter()

const { canChargeOrders } = useNightPosPermissions()



const activeTab = ref('pending_charge')

const orders = ref([])

const loading = ref(false)

const cashSessionOpen = ref(false)

const { notify } = useNightPosNotify()

const emptyMessage = computed(() => orderEmptyMessage(activeTab.value))



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

      fetchOrdersByScope(tab.scope),

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



const goCharge = order => {
  router.push({
    name: 'nightpos-orders-id',
    params: { id: order.id },
    query: { from: 'cashier', mode: 'correction', charge: 1 },
  })
}



const goOpenCash = () => {

  router.push({ name: 'nightpos-cash', query: { open: 1 } })

}



const showChargeActions = computed(() => activeTab.value !== 'billed_recent')



// ─── SSE real-time ──────────────────────────────────────────────────────────
const { on, start: startSse, stop: stopSse, connected: sseConnected, reconnecting: sseReconnecting } = useOperationalEvents()

let reloadDebounce = null
const debouncedLoad = () => {
  clearTimeout(reloadDebounce)
  reloadDebounce = setTimeout(loadOrders, 500)
}

on('order.created', debouncedLoad)
on('order.sent_to_bar', (data) => {
  debouncedLoad()
  notify('Nueva comanda enviada a barra', 'info')
})
on('order.updated', debouncedLoad)
on('order.billed', debouncedLoad)
on('order.cancelled', debouncedLoad)
// ─────────────────────────────────────────────────────────────────────────────

onMounted(() => {
  loadOrders()
  startSse()
})

onUnmounted(() => {
  stopSse()
})

useOnContextChange(loadOrders)

</script>



<template>

  <div class="cashier-orders-page">

    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">

      <div>

        <h4 class="text-h4 mb-1">

          Cobrar comandas

        </h4>

        <p class="mb-0 text-body-2 text-medium-emphasis">

          Pendientes de cobro, comandas corregibles y cobradas recientes.

        </p>

      </div>

      <VChip

        :color="cashSessionOpen ? 'success' : 'warning'"

        variant="tonal"

      >

        {{ cashSessionOpen ? 'Caja abierta' : 'Caja cerrada' }}

      </VChip>

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

      prominent

    >

      <div class="d-flex flex-wrap align-center justify-space-between gap-3 w-100">

        <span>Debe abrir su caja antes de cobrar comandas.</span>

        <VBtn

          color="warning"

          variant="flat"

          size="large"

          :to="{ name: 'nightpos-cash', query: { open: 1 } }"

        >

          Abrir caja ahora

        </VBtn>

      </div>

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

        <VCard variant="outlined">

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

                block

                size="large"

                color="primary"

                variant="tonal"

                prepend-icon="ri-edit-line"

                @click="goCorrect(order)"

              >

                Ver / corregir

              </VBtn>

              <VBtn

                v-if="cashSessionOpen"

                block

                size="x-large"

                color="success"

                prepend-icon="ri-money-dollar-circle-line"

                @click="goCharge(order)"

              >

                Cobrar

              </VBtn>

              <VBtn

                v-else

                block

                size="x-large"

                color="warning"

                prepend-icon="ri-safe-2-line"

                @click="goOpenCash"

              >

                Abrir caja ahora

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
</div>

</template>

