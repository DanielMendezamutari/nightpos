<script setup>

import { fetchAdminUsers } from '@/api/users'

import { fetchSales } from '@/api/sales'

import SaleDetailDialog from '@/components/nightpos/sales/SaleDetailDialog.vue'

import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import { useOperationalPollingFallback } from '@/composables/useOperationalPollingFallback'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { formatMoney } from '@/composables/useOrderHelpers'

import { getApiErrorMessage } from '@/services/http'



definePage({

  meta: {

    permission: 'sales.list',

  },

})



const { canListSales, canListAdminUsers } = useNightPosPermissions()

const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()



const sales = ref([])

const loading = ref(false)

const userMap = ref({})



const showDetail = ref(false)

const selectedSaleId = ref(null)



const PAYMENT_LABELS = {

  CASH: 'Efectivo',

  QR: 'QR',

  CARD: 'Tarjeta',

  MIXED: 'Mixto',

}



const PAYMENT_CHIP_COLOR = {

  CASH: 'success',

  QR: 'info',

  CARD: 'primary',

  MIXED: 'warning',

}



const headers = [

  { title: 'Nº venta', key: 'sale_number' },

  { title: 'Comanda', key: 'order_id' },

  { title: 'Cajera', key: 'cashier_user_id' },

  { title: 'Garzón', key: 'waiter_user_id' },

  { title: 'Pago', key: 'payment_mode' },

  { title: 'Total', key: 'total' },

  { title: 'Estado', key: 'status' },

  { title: 'Fecha', key: 'paid_at' },

  { title: '', key: 'actions', sortable: false, width: '100px' },

]



const summaryCards = computed(() => {

  const list = sales.value

  const totalAmount = list.reduce((sum, s) => sum + Number(s.total || 0), 0)

  const byMode = { CASH: 0, QR: 0, CARD: 0, MIXED: 0 }



  list.forEach(s => {

    if (byMode[s.payment_mode] != null)

      byMode[s.payment_mode] += 1

  })



  return [

    {

      title: 'Ventas en sesión',

      color: 'primary',

      icon: 'ri-bill-line',

      stats: String(list.length),

      change: 0,

      subtitle: 'Turno / caja abierta',

    },

    {

      title: 'Total cobrado',

      color: 'success',

      icon: 'ri-funds-line',

      stats: formatMoney(totalAmount),

      change: 0,

      subtitle: 'Suma de ventas listadas',

    },

    {

      title: 'Efectivo',

      color: 'success',

      icon: 'ri-money-dollar-box-line',

      stats: String(byMode.CASH),

      change: 0,

      subtitle: 'Ventas modo efectivo',

    },

    {

      title: 'QR / Tarjeta / Mixto',

      color: 'info',

      icon: 'ri-bank-card-line',

      stats: String(byMode.QR + byMode.CARD + byMode.MIXED),

      change: 0,

      subtitle: `QR ${byMode.QR} · Tarjeta ${byMode.CARD} · Mixto ${byMode.MIXED}`,

    },

  ]

})



const resolveUserName = id => {

  if (!id)

    return '—'



  return userMap.value[id] || `Usuario #${id}`

}



const formatDateTime = value => {

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



const openDetail = sale => {

  selectedSaleId.value = sale.id

  showDetail.value = true

}



const loadUsers = async () => {

  if (!canListAdminUsers.value)

    return



  try {

    const users = await fetchAdminUsers()

    const map = {}



    users.forEach(u => {

      map[u.id] = u.name || u.email || `Usuario #${u.id}`

    })

    userMap.value = map

  }

  catch {

    userMap.value = {}

  }

}



const loadSales = async () => {

  loading.value = true



  try {

    sales.value = await fetchSales(true)

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    loading.value = false

  }

}



const reloadSalesPage = async () => {
  if (!canListSales.value)
    return

  await loadUsers()
  await loadSales()
}

const lastSale = computed(() => sales.value[0] ?? null)

const reprintLastSale = () => {
  if (!lastSale.value?.id) {
    notify('No hay ventas para reimprimir.', 'info')

    return
  }

  openPrintRoute({ name: 'nightpos-print-sale-id', params: { id: lastSale.value.id } })
}

const { on, start: startSse, stop: stopSse, connected: sseConnected, reconnecting: sseReconnecting } = useOperationalEvents()

let salesDebounce = null
const debouncedLoadSales = () => {
  clearTimeout(salesDebounce)
  salesDebounce = setTimeout(loadSales, 600)
}

on('sale.created', debouncedLoadSales)
on('direct_sale.created', debouncedLoadSales)

useOperationalPollingFallback(loadSales)

onMounted(async () => {
  await reloadSalesPage()
  startSse()
})

onUnmounted(() => {
  stopSse()
})

useOnContextChange(reloadSalesPage)

</script>



<template>

  <div class="sales-page">

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <div class="mb-4 d-flex flex-wrap align-center justify-space-between gap-3">

      <div>
        <h4 class="text-h4 mb-1">

          Ventas del turno

        </h4>

        <p class="mb-0 text-body-2">

          Cobros de la sesión de caja abierta. Abra el detalle para ver ítems, snapshots y comisiones.

        </p>
      </div>

      <VBtn
        v-if="lastSale"
        variant="tonal"
        prepend-icon="ri-printer-line"
        @click="reprintLastSale"
      >
        Reimprimir última venta
      </VBtn>

    </div>



    <VProgressLinear

      v-if="loading"

      indeterminate

      color="primary"

      class="mb-4"

    />



    <template v-else>

      <VRow

        v-if="sales.length"

        class="match-height mb-4"

      >

        <VCol

          v-for="card in summaryCards"

          :key="card.title"

          cols="12"

          sm="6"

          lg="3"

        >

          <CardStatisticsVertical v-bind="card" />

        </VCol>

      </VRow>



      <VAlert

        v-if="!sales.length"

        type="info"

        variant="tonal"

        class="mb-4"

      >

        No hay ventas en esta sesión. Cobre una comanda desde Comandas.

      </VAlert>



      <VCard v-else>

        <VCardText class="d-none d-md-block text-caption text-medium-emphasis pb-0">

          Toque «Ver» o la fila en móvil para abrir el detalle completo.

        </VCardText>

        <VDataTable

          :items="sales"

          :headers="headers"

          density="comfortable"

          :items-per-page="15"

          class="text-no-wrap"

          @click:row="(_e, { item }) => openDetail(item)"

        >

          <template #item.sale_number="{ item }">

            <span class="font-weight-medium">{{ item.sale_number }}</span>

          </template>

          <template #item.order_id="{ item }">

            #{{ item.order_id }}

          </template>

          <template #item.cashier_user_id="{ item }">

            {{ resolveUserName(item.cashier_user_id) }}

          </template>

          <template #item.waiter_user_id="{ item }">

            {{ resolveUserName(item.waiter_user_id) }}

          </template>

          <template #item.payment_mode="{ item }">

            <VChip

              size="small"

              label

              :color="PAYMENT_CHIP_COLOR[item.payment_mode] || 'secondary'"

            >

              {{ PAYMENT_LABELS[item.payment_mode] || item.payment_mode }}

            </VChip>

          </template>

          <template #item.total="{ item }">

            {{ formatMoney(item.total, item.currency) }}

          </template>

          <template #item.status="{ item }">

            <VChip

              size="small"

              color="success"

              label

            >

              {{ item.status === 'PAID' ? 'Pagada' : item.status }}

            </VChip>

          </template>

          <template #item.paid_at="{ item }">

            {{ formatDateTime(item.paid_at) }}

          </template>

          <template #item.actions="{ item }">

            <VBtn

              size="small"

              variant="tonal"

              @click.stop="openDetail(item)"

            >

              Ver

            </VBtn>

          </template>

        </VDataTable>

      </VCard>

    </template>



    <SaleDetailDialog

      v-model="showDetail"

      :sale-id="selectedSaleId"

      :user-name="resolveUserName"

    />
</div>

</template>



<style scoped>

@media (max-width: 959px) {

  .sales-page :deep(.v-data-table tbody tr) {

    cursor: pointer;

  }

}

</style>

