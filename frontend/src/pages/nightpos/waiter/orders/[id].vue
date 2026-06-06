<script setup>
import WaiterBottomNav from '@/components/nightpos/waiter/WaiterBottomNav.vue'
import WaiterMobileHeader from '@/components/nightpos/waiter/WaiterMobileHeader.vue'
import AssignGirlModal from '@/components/nightpos/orders/AssignGirlModal.vue'
import OrderAddProductDialog from '@/components/nightpos/orders/OrderAddProductDialog.vue'
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import { fetchProductPrices } from '@/api/products'
import {
  addOrderItem,
  assignOrderItemGirl,
  fetchOrder,
  sendOrderToBar,
} from '@/api/orders'
import { loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { useOrderProductShortcuts } from '@/composables/useOrderProductShortcuts'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { canModifyOrder, formatMoney, itemsNeedingGirl } from '@/composables/useOrderHelpers'
import { waiterOrderStatus } from '@/composables/useWaiterOrderStatus'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    layout: 'blank',
    permission: 'orders.access',
  },
})

const route = useRoute()
const router = useRouter()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const {
  toggleFavorite,
  recordRecent,
} = useOrderProductShortcuts()

const addDialogRef = ref(null)

const orderId = computed(() => Number(route.params.id))
const order = ref(null)
const loading = ref(true)
const actionLoading = ref(false)
const showAddItem = ref(false)
const showSendDialog = ref(false)
const showQuickGirl = ref(false)
const pricePreview = ref(null)
const pricesLoading = ref(false)
const addPriceContext = ref({ product_id: null, sale_mode: 'SOLO_CLIENTE' })
const staffUsers = ref([])
const girlsForSelect = computed(() =>
  staffUsers.value.map(u => ({ title: u.name, value: u.id })),
)

const modifiable = computed(() => canModifyOrder(order.value))
const isOpen = computed(() => order.value?.status === 'OPEN')
const isSentToBar = computed(() => order.value?.status === 'SENT_TO_BAR')
const isPendingCharge = computed(() => ['IN_PREPARATION', 'READY'].includes(order.value?.status))
const isReadOnly = computed(() => !modifiable.value)
const hasItems = computed(() => (order.value?.items?.length ?? 0) > 0)

const orderStatus = computed(() => waiterOrderStatus(order.value?.status))

const saleModeLabel = mode =>
  mode === 'CON_ACOMPANANTE' ? 'Con acompañante' : 'Solo'

const loadOrder = async () => {
  loading.value = true
  try {
    order.value = await fetchOrder(orderId.value)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    await router.replace({ name: 'nightpos-waiter-orders' })
  }
  finally {
    loading.value = false
  }
}

const loadGirls = async () => {
  try {
    const items = await loadOperationalGirlsForSelect({ waiterMode: true })
    staffUsers.value = items.map(i => ({ id: i.value, name: i.title }))
  }
  catch {
    staffUsers.value = []
  }
}

const onPreviewPrice = async ({ product_id, sale_mode }) => {
  pricePreview.value = null
  addPriceContext.value = { product_id, sale_mode }
  if (!product_id || !sale_mode)
    return

  pricesLoading.value = true
  try {
    const prices = await fetchProductPrices(product_id)
    pricePreview.value = prices.find(p => p.sale_mode === sale_mode && p.status === 'active') ?? null
  }
  finally {
    pricesLoading.value = false
  }
}

const openAddItem = async () => {
  await loadGirls()
  showAddItem.value = true
}

const submitAddItem = async (addForm) => {
  if (!addForm.product_id) {
    notify('Seleccione producto', 'warning')
    return
  }
  actionLoading.value = true
  try {
    order.value = await addOrderItem(orderId.value, {
      product_id: addForm.product_id,
      sale_mode: addForm.sale_mode,
      quantity: Number(addForm.quantity) || 1,
      girl_user_id: addForm.girl_user_id || null,
      notes: addForm.notes?.trim() || null,
    })
    recordRecent(addForm.product_id)
    showAddItem.value = false
    notify('Agregado')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const confirmSendToBar = async () => {
  actionLoading.value = true
  try {
    order.value = await sendOrderToBar(orderId.value)
    showSendDialog.value = false
    notify('Enviada a barra')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const openSendDialog = async () => {
  if (itemsNeedingGirl(order.value).length) {
    await loadGirls()
    showSendDialog.value = true
    return
  }
  await confirmSendToBar()
}

const assignGirlsAndSend = async (girlAssignments) => {
  const pending = itemsNeedingGirl(order.value)
  for (const item of pending) {
    if (!girlAssignments[item.id]) {
      notify(`Asigne chica: ${item.product_name}`, 'warning')
      return
    }
  }
  actionLoading.value = true
  try {
    for (const item of pending) {
      order.value = await assignOrderItemGirl(orderId.value, item.id, Number(girlAssignments[item.id]))
    }
    await confirmSendToBar()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const onGirlCreated = girl => {
  if (!girl?.id)
    return
  staffUsers.value = [...staffUsers.value, { id: girl.id, name: girl.name }]
}

onMounted(async () => {
  await loadOrder()
  if (route.query.add === '1' && modifiable.value)
    await openAddItem()
  if (route.query.send === '1' && isOpen.value && hasItems.value)
    await openSendDialog()
})
</script>

<template>
  <div class="waiter-shell">
    <WaiterMobileHeader
      :title="order?.table_label || 'Comanda'"
      show-back
    />

    <VContainer
      v-if="order"
      class="py-4 px-4"
    >
      <VChip
        class="mb-3"
        :color="orderStatus.color"
        variant="tonal"
      >
        {{ orderStatus.label }}
      </VChip>
      <div class="text-h4 font-weight-bold mb-4">
        {{ formatMoney(order.total, order.currency) }}
      </div>

      <VList class="mb-4">
        <VListItem
          v-for="item in order.items"
          :key="item.id"
          :subtitle="saleModeLabel(item.sale_mode)"
        >
          <VListItemTitle>{{ item.product_name }} × {{ item.quantity }}</VListItemTitle>
          <template #append>
            {{ formatMoney(item.line_total) }}
          </template>
        </VListItem>
      </VList>

      <VAlert
        v-if="isPendingCharge"
        type="info"
        variant="tonal"
        class="mb-4"
      >
        Pendiente de cobro por caja
      </VAlert>

      <VAlert
        v-else-if="isSentToBar"
        type="warning"
        variant="tonal"
        class="mb-4"
      >
        En barra — puede agregar extras si el cliente lo solicita
      </VAlert>

      <div
        v-if="modifiable"
        class="d-flex flex-column gap-3"
      >
        <VBtn
          size="x-large"
          color="primary"
          prepend-icon="ri-add-line"
          @click="openAddItem"
        >
          {{ isSentToBar ? 'Agregar extra' : '+ Producto' }}
        </VBtn>
        <VBtn
          v-if="isOpen"
          size="x-large"
          color="warning"
          variant="tonal"
          prepend-icon="ri-send-plane-line"
          :disabled="!hasItems"
          :loading="actionLoading"
          @click="openSendDialog"
        >
          Enviar a barra
        </VBtn>
      </div>

      <VAlert
        v-else-if="isReadOnly"
        type="info"
        variant="tonal"
      >
        Comanda en solo lectura
      </VAlert>
    </VContainer>

    <OrderAddProductDialog
      v-if="order"
      ref="addDialogRef"
      v-model="showAddItem"
      mobile-waiter
      :price-preview="pricePreview"
      :prices-loading="pricesLoading"
      :loading="actionLoading"
      :girls="girlsForSelect"
      allow-girl-on-add
      :can-quick-create-girl="can('staff.quick_create_girl')"
      @submit="submitAddItem"
      @preview-price="onPreviewPrice"
      @toggle-favorite="p => toggleFavorite(p.id)"
      @quick-create-girl="showQuickGirl = true"
    />

    <AssignGirlModal
      v-model="showSendDialog"
      :order="order"
      :staff-users="staffUsers"
      :loading="actionLoading"
      @confirm="assignGirlsAndSend"
      @staff-updated="onGirlCreated"
    />

    <QuickGirlCreateDialog
      v-model="showQuickGirl"
      @created="onGirlCreated"
    />

    <WaiterBottomNav />
</div>
</template>

<style scoped lang="scss">
@use '@styles/waiter-mobile';
</style>
