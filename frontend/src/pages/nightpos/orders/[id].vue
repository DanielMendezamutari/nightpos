<script setup>
import { fetchCurrentCashSession } from '@/api/cash'
import { fetchProduct, fetchProductPrices } from '@/api/products'
import {
  addOrderItem,
  assignOrderItemGirl,
  cancelOrder,
  fetchOrder,
  sendOrderToBar,
  syncOrderItemAllocations,
  updateOrderHeader,
} from '@/api/orders'
import { fetchOrderPrintStatus, reprintOrderCommand } from '@/api/printDevices'
import { chargeOrder } from '@/api/sales'
import { loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import AssignGirlModal from '@/components/nightpos/orders/AssignGirlModal.vue'
import ChargeOrderModal from '@/components/nightpos/orders/ChargeOrderModal.vue'
import OrderActionsBar from '@/components/nightpos/orders/OrderActionsBar.vue'
import QuickProductCreateDialog from '@/components/nightpos/catalog/QuickProductCreateDialog.vue'
import QuickProductPriceCreateDialog from '@/components/nightpos/catalog/QuickProductPriceCreateDialog.vue'
import OrderAddProductDialog from '@/components/nightpos/orders/OrderAddProductDialog.vue'
import OrderHeader from '@/components/nightpos/orders/OrderHeader.vue'
import OrderHeaderEditDialog from '@/components/nightpos/orders/OrderHeaderEditDialog.vue'
import OrderItemsTable from '@/components/nightpos/orders/OrderItemsTable.vue'
import OrderTotals from '@/components/nightpos/orders/OrderTotals.vue'
import { useOrderProductShortcuts } from '@/composables/useOrderProductShortcuts'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useOrderOperationalEvents } from '@/composables/useOrderOperationalEvents'
import { useOperationalPollingFallback } from '@/composables/useOperationalPollingFallback'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useAuthStore } from '@/stores/auth'
import { activeOrderItems, canModifyOrder, itemsNeedingAllocation, itemsNeedingGirl } from '@/composables/useOrderHelpers'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: 'orders.access',
  },
})

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const {
  canAccessOrders,
  canChargeOrders,
  canUpdateOrderItems,
  canCancelOrderItem,
  canUpdateOrderHeader,
  canCancelOrder,
  can,
} = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()
const {
  toggleFavorite,
  recordRecent,
} = useOrderProductShortcuts()

const addDialogRef = ref(null)

const devMode = import.meta.env.DEV

const orderId = computed(() => Number(route.params.id))
const order = ref(null)
const loading = ref(true)
const actionLoading = ref(false)
const correctionLoading = ref(false)
const orderItemsTableRef = ref(null)
const cashSessionOpen = ref(false)

const showAddItem = ref(false)
const showSendDialog = ref(false)
const showCancelConfirm = ref(false)
const showChargeDialog = ref(false)
const showHeaderEdit = ref(false)

const fromCashier = computed(() => route.query.from === 'cashier')
const cashierCorrectionMode = computed(() =>
  fromCashier.value || route.query.mode === 'correction',
)
const isCorrectionMode = computed(() =>
  cashierCorrectionMode.value || canUpdateOrderItems.value,
)
const backRoute = computed(() =>
  fromCashier.value
    ? { name: 'nightpos-cashier-orders' }
    : { name: 'nightpos-orders' },
)
const backLabel = computed(() => fromCashier.value ? 'Cobrar comandas' : 'Comandas')

const pricePreview = ref(null)
const quickPriceProductName = ref('')
const pricesLoading = ref(false)
const addPriceContext = ref({ product_id: null, sale_mode: 'SOLO_CLIENTE' })
const showQuickPrice = ref(false)
const showQuickProduct = ref(false)
const presetProductId = ref(null)

const missingPriceForAdd = computed(() => Boolean(
  addPriceContext.value.product_id
  && addPriceContext.value.sale_mode
  && !pricesLoading.value
  && !pricePreview.value,
))

const canConfigurePriceQuick = computed(() =>
  can('product_prices.quick_create') || can('products.update'),
)

const canCreateProductQuick = computed(() => can('products.quick_create'))

const staffUsers = ref([])
const assignLoading = ref(false)
const printJob = ref(null)
const reprintLoading = ref(false)

const printStatusLabel = computed(() => {
  const status = printJob.value?.status
  if (!status)
    return null
  const map = {
    PENDING: 'Pendiente impresión',
    CLAIMED: 'Imprimiendo…',
    PRINTED: 'Impreso en barra',
    FAILED: 'Error de impresión',
  }
  return map[status] ?? status
})

const printStatusColor = computed(() => {
  const status = printJob.value?.status
  if (status === 'PRINTED')
    return 'success'
  if (status === 'FAILED')
    return 'error'
  if (status === 'PENDING' || status === 'CLAIMED')
    return 'warning'
  return 'default'
})

const loadPrintStatus = async () => {
  if (!orderId.value)
    return
  try {
    printJob.value = await fetchOrderPrintStatus(orderId.value)
  }
  catch {
    printJob.value = null
  }
}

const handleReprint = async () => {
  reprintLoading.value = true
  try {
    await reprintOrderCommand(orderId.value)
    notify('Reimpresión encolada')
    await loadPrintStatus()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    reprintLoading.value = false
  }
}

const loadOrder = async () => {
  loading.value = true

  try {
    order.value = await fetchOrder(orderId.value)
    if (order.value && order.value.status !== 'OPEN')
      await loadPrintStatus()
    else
      printJob.value = null
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    await router.replace(backRoute.value)
  }
  finally {
    loading.value = false
  }
}

const refreshOrderAndPrint = async () => {
  await loadOrder()
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

const loadStaffUsers = async () => {
  try {
    const items = await loadOperationalGirlsForSelect()
    staffUsers.value = items.map(item => ({ id: item.value, name: item.title }))
  }
  catch {
    staffUsers.value = []
  }
}

const onStaffUpdated = ({ id, name }) => {
  if (!staffUsers.value.some(u => u.id === id))
    staffUsers.value = [...staffUsers.value, { id, name }].sort((a, b) => a.name.localeCompare(b.name))
}

const onPreviewPrice = async ({ product_id, sale_mode }) => {
  pricePreview.value = null
  addPriceContext.value = { product_id, sale_mode }

  if (!product_id || !sale_mode)
    return

  pricesLoading.value = true

  try {
    const prices = await fetchProductPrices(product_id)
    const active = prices.find(p => p.sale_mode === sale_mode && p.status === 'active')

    pricePreview.value = active ?? null
  }
  catch {
    pricePreview.value = null
  }
  finally {
    pricesLoading.value = false
  }
}

const openAddItem = () => {
  showAddItem.value = true
  pricePreview.value = null
  presetProductId.value = null
  addPriceContext.value = { product_id: null, sale_mode: 'SOLO_CLIENTE' }
}

const openQuickProduct = () => {
  showQuickProduct.value = true
}

const onQuickProductCreated = async result => {
  const productId = result?.product?.id
  if (!productId)
    return

  await addDialogRef.value?.refreshPicker()
  presetProductId.value = productId
  await onPreviewPrice({ product_id: productId, sale_mode: 'SOLO_CLIENTE' })
}

const openQuickPrice = async ({ product_id, sale_mode }) => {
  addPriceContext.value = { product_id, sale_mode }
  quickPriceProductName.value = ''

  if (product_id) {
    try {
      const product = await fetchProduct(product_id)
      quickPriceProductName.value = product?.name ?? ''
    }
    catch {
      quickPriceProductName.value = ''
    }
  }

  showQuickPrice.value = true
}

const onQuickPriceCreated = async () => {
  await addDialogRef.value?.refreshPicker()
  await onPreviewPrice(addPriceContext.value)
}

const submitAddItem = async (addForm) => {
  if (!addForm.product_id) {
    notify('Seleccione un producto.', 'warning')

    return
  }

  actionLoading.value = true

  try {
    order.value = await addOrderItem(orderId.value, {
      product_id: addForm.product_id,
      sale_mode: addForm.sale_mode,
      quantity: Number(addForm.quantity) || 1,
      notes: addForm.notes?.trim() || null,
    })
    recordRecent(addForm.product_id)

    if (addForm.allocations?.length) {
      const added = [...(order.value?.items ?? [])]
        .reverse()
        .find(i => i.product_id === addForm.product_id && i.requires_allocation)

      if (added) {
        order.value = await syncOrderItemAllocations(orderId.value, added.id, addForm.allocations)
      }
    }

    showAddItem.value = false

    const pending = [...(order.value?.items ?? [])]
      .reverse()
      .find(i => i.product_id === addForm.product_id && i.requires_allocation && !i.allocation_complete)

    if (pending) {
      notify('Complete el reparto de manillas', 'warning')
      await nextTick()
      orderItemsTableRef.value?.openAllocationForItem?.(pending)
    }
    else {
      notify(addForm.is_combo ? 'Combo agregado' : 'Producto agregado')
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const openSendDialog = async () => {
  if (itemsNeedingAllocation(order.value).length) {
    notify('Complete el reparto de manillas antes de enviar a barra.', 'warning')

    return
  }

  if (itemsNeedingGirl(order.value).length) {
    await loadStaffUsers()
    showSendDialog.value = true

    return
  }

  await confirmSendToBar()
}

const confirmSendToBar = async () => {
  actionLoading.value = true

  try {
    order.value = await sendOrderToBar(orderId.value)
    showSendDialog.value = false
    notify('Comanda enviada a barra')
    await loadPrintStatus()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const assignGirlsAndSend = async (girlAssignments) => {
  const pending = itemsNeedingGirl(order.value)

  for (const item of pending) {
    if (!girlAssignments[item.id]) {
      notify(`Asigne chica para: ${item.product_name}`, 'warning')

      return
    }
  }

  assignLoading.value = true

  try {
    for (const item of pending) {
      order.value = await assignOrderItemGirl(
        orderId.value,
        item.id,
        Number(girlAssignments[item.id]),
      )
    }

    await confirmSendToBar()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    assignLoading.value = false
  }
}

const confirmCancel = async () => {
  actionLoading.value = true

  try {
    await cancelOrder(orderId.value)
    showCancelConfirm.value = false
    notify('Comanda cancelada')
    await router.replace(backRoute.value)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const onChargeConfirm = async (payload) => {
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

  if (itemsNeedingAllocation(order.value).length) {
    notify('Complete el reparto de manillas antes de cobrar.', 'warning')

    return
  }

  actionLoading.value = true

  try {
    const result = await chargeOrder(orderId.value, payments)

    order.value = { ...order.value, status: result.order_status ?? 'BILLED' }
    showChargeDialog.value = false
    notify('Comanda cobrada correctamente')
    await loadCashSession()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const modifiable = computed(() => canModifyOrder(order.value))
const isOpen = computed(() => order.value?.status === 'OPEN')
const hasItems = computed(() => activeOrderItems(order.value).length > 0)
const canEditLines = computed(() =>
  cashierCorrectionMode.value && modifiable.value,
)
const missingCorrectionPermission = computed(() =>
  cashierCorrectionMode.value
  && modifiable.value
  && !canUpdateOrderItems.value,
)
const showAddButton = computed(() => {
  if (!can('orders.add_items'))
    return false

  if (isCorrectionMode.value)
    return modifiable.value

  return isOpen.value
})

const showChargeButton = computed(() =>
  canChargeOrders.value
  && hasItems.value
  && order.value
  && !['BILLED', 'CANCELLED'].includes(order.value.status),
)

const showSendButton = computed(() =>
  !isCorrectionMode.value
  && isOpen.value
  && hasItems.value
  && can('orders.send_to_bar'),
)

const showCancelButton = computed(() =>
  canCancelOrder.value
  && order.value
  && order.value.status !== 'CANCELLED'
  && order.value.status !== 'BILLED',
)

const mainActionsBusy = computed(() => actionLoading.value && !correctionLoading.value)

if (import.meta.env.DEV) {
  watchEffect(() => {
    if (!order.value)
      return

    console.debug('[order-detail actions]', {
      isOpen: isOpen.value,
      isSentToBar: order.value.status === 'SENT_TO_BAR',
      canModifyOrder: modifiable.value,
      canCharge: canCharge.value,
      showAdd: showAddButton.value,
      showCharge: showChargeButton.value,
      showCancel: showCancelButton.value,
      showSend: showSendButton.value,
      addDialogOpen: showAddItem.value,
      chargeDialogOpen: showChargeDialog.value,
      cancelDialogOpen: showCancelConfirm.value,
      actionLoading: actionLoading.value,
      correctionLoading: correctionLoading.value,
    })
  })
}

const onHeaderSave = async headerForm => {
  actionLoading.value = true

  try {
    order.value = await updateOrderHeader(orderId.value, {
      table_label: headerForm.table_label?.trim() || null,
      service_area_id: headerForm.service_area_id ?? null,
      notes: headerForm.notes?.trim() || null,
    })
    showHeaderEdit.value = false
    notify('Mesa / ambiente actualizado')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    actionLoading.value = false
  }
}

const canCharge = computed(() =>
  canChargeOrders.value
  && order.value
  && hasItems.value
  && cashSessionOpen.value
  && !['BILLED', 'CANCELLED'].includes(order.value.status),
)

const chargeHint = computed(() => {
  if (!canChargeOrders.value || !hasItems.value)
    return ''
  if (['BILLED', 'CANCELLED'].includes(order.value?.status))
    return ''
  if (!cashSessionOpen.value)
    return 'Abra caja para habilitar el cobro de esta comanda.'

  return ''
})

const onOrderItemsUpdated = async updatedOrder => {
  await nextTick()
  order.value = updatedOrder
}

const onCorrectionLoading = value => {
  correctionLoading.value = value
}

const openChargeDialog = () => {
  if (import.meta.env.DEV)
    console.log('[OrderDetail] opening charge dialog')
  orderItemsTableRef.value?.closeAllDialogs?.()
  showChargeDialog.value = true
}

const openCancelDialog = () => {
  if (import.meta.env.DEV)
    console.log('[OrderDetail] opening cancel dialog')
  orderItemsTableRef.value?.closeAllDialogs?.()
  showCancelConfirm.value = true
}

const openAddProductDialog = async () => {
  if (import.meta.env.DEV)
    console.log('[OrderDetail] opening add dialog')
  orderItemsTableRef.value?.closeAllDialogs?.()
  await openAddItem()
}

const { connected: sseConnected, reconnecting: sseReconnecting } = useOrderOperationalEvents(refreshOrderAndPrint, {
  orderId: orderId.value,
  toastOnUpdated: true,
  updatedDebounceMs: 400,
  onTerminalStatus: (status) => {
    if (['BILLED', 'CANCELLED'].includes(status))
      notify('La comanda fue cerrada por otro usuario.', 'warning')
  },
})

useOperationalPollingFallback(refreshOrderAndPrint)

onMounted(async () => {
  const refreshMe = cashierCorrectionMode.value
    ? auth.fetchMe().catch(() => {})
    : Promise.resolve()

  await Promise.all([loadOrder(), loadCashSession(), refreshMe])

  if (route.query.charge === '1' && canChargeOrders.value && cashSessionOpen.value && order.value && !['BILLED', 'CANCELLED'].includes(order.value.status))
    showChargeDialog.value = true
})
</script>

<template>
  <div class="order-detail">
    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <VBtn
      variant="text"
      class="mb-2"
      :to="backRoute"
    >
      <VIcon
        icon="ri-arrow-left-line"
        start
      />
      {{ backLabel }}
    </VBtn>

    <VProgressLinear
      v-if="loading"
      indeterminate
      color="primary"
      class="mb-4"
    />

    <template v-else-if="order">
      <!-- DEV debug panel: removed from production builds -->
      <template v-if="devMode">
        <VCard
          class="mb-3 pa-3"
          color="warning"
          variant="tonal"
          density="compact"
        >
          <div class="text-caption font-weight-bold mb-2">
            🛠 DEV — Estado de acciones
          </div>
          <div class="text-caption">
            status={{ order.status }} | showAdd={{ showAddButton }} | showCharge={{ showChargeButton }} |
            showCancel={{ showCancelButton }} | showSend={{ showSendButton }} |
            canCharge={{ canCharge }} | actionLoading={{ actionLoading }} |
            correctionLoading={{ correctionLoading }} | mainActionsBusy={{ mainActionsBusy }}
          </div>
          <div class="d-flex gap-2 mt-2 flex-wrap">
            <VBtn
              size="x-small"
              color="primary"
              @click="() => { console.log('[OrderDetail] DEBUG opening add'); openAddProductDialog() }"
            >
              DEBUG Agregar
            </VBtn>
            <VBtn
              size="x-small"
              color="success"
              @click="() => { console.log('[OrderDetail] DEBUG opening charge'); openChargeDialog() }"
            >
              DEBUG Cobrar
            </VBtn>
            <VBtn
              size="x-small"
              color="error"
              @click="() => { console.log('[OrderDetail] DEBUG opening cancel'); openCancelDialog() }"
            >
              DEBUG Cancelar
            </VBtn>
          </div>
        </VCard>
      </template>

      <div class="d-flex justify-end align-center gap-2 mb-2 flex-wrap">
        <VChip
          v-if="printStatusLabel"
          :color="printStatusColor"
          size="small"
          variant="tonal"
        >
          {{ printStatusLabel }}
        </VChip>
        <VBtn
          v-if="can('printing.reprint') && order?.status === 'SENT_TO_BAR'"
          size="small"
          variant="tonal"
          prepend-icon="ri-refresh-line"
          :loading="reprintLoading"
          @click="handleReprint"
        >
          Reimprimir barra
        </VBtn>
        <VBtn
          size="small"
          variant="tonal"
          prepend-icon="ri-printer-line"
          @click="openPrintRoute({ name: 'nightpos-print-order-id', params: { id: orderId } })"
        >
          Imprimir barra
        </VBtn>
      </div>
      <div
        v-if="isCorrectionMode && canUpdateOrderHeader && isOpen"
        class="d-flex justify-end mb-2"
      >
        <VBtn
          size="small"
          variant="tonal"
          prepend-icon="ri-map-pin-line"
          @click="showHeaderEdit = true"
        >
          Corregir mesa / ambiente
        </VBtn>
      </div>
      <VAlert
        v-if="cashierCorrectionMode && modifiable"
        type="info"
        variant="tonal"
        class="mb-3"
        prominent
      >
        Modo corrección de caja — puede ajustar productos antes de cobrar.
      </VAlert>

      <VAlert
        v-if="missingCorrectionPermission"
        type="warning"
        variant="tonal"
        class="mb-3"
        prominent
      >
        Su sesión no tiene permisos de corrección cargados. Cierre sesión y vuelva a entrar para habilitar «Corregir».
      </VAlert>

      <OrderHeader :order="order" />
      <OrderItemsTable
        ref="orderItemsTableRef"
        :order="order"
        :editable="canEditLines"
        :cashier-correction-mode="cashierCorrectionMode"
        :can-update-items="canUpdateOrderItems"
        :can-cancel-items="canCancelOrderItem"
        @updated="onOrderItemsUpdated"
        @correction-loading="onCorrectionLoading"
      />
      <OrderTotals :order="order" />

      <OrderActionsBar
        v-if="(modifiable || canChargeOrders) && canAccessOrders"
        :show-add="showAddButton || (modifiable && can('orders.add_items') && !hasItems)"
        :show-charge="showChargeButton"
        :show-send="showSendButton"
        :show-cancel="showCancelButton"
        :charge-hint="chargeHint"
        :action-loading="mainActionsBusy"
        @add="openAddProductDialog"
        @charge="openChargeDialog"
        @send="openSendDialog"
        @cancel="openCancelDialog"
      />
    </template>

    <OrderAddProductDialog
      ref="addDialogRef"
      v-model="showAddItem"
      :price-preview="pricePreview"
      :prices-loading="pricesLoading"
      :loading="actionLoading"
      :missing-price="missingPriceForAdd"
      :can-configure-price="canConfigurePriceQuick"
      :can-create-product="canCreateProductQuick"
      :preset-product-id="presetProductId"
      @preview-price="onPreviewPrice"
      @configure-price="openQuickPrice"
      @create-product="openQuickProduct"
      @toggle-favorite="p => toggleFavorite(p.id)"
      @submit="submitAddItem"
    />

    <QuickProductCreateDialog
      v-model="showQuickProduct"
      @created="onQuickProductCreated"
    />

    <QuickProductPriceCreateDialog
      v-model="showQuickPrice"
      :product-id="addPriceContext.product_id"
      :sale-mode="addPriceContext.sale_mode"
      :product-name="quickPriceProductName"
      @created="onQuickPriceCreated"
    />

    <AssignGirlModal
      v-model="showSendDialog"
      :order="order"
      :staff-users="staffUsers"
      :loading="assignLoading || actionLoading"
      @staff-updated="onStaffUpdated"
      @confirm="assignGirlsAndSend"
    />

    <ChargeOrderModal
      v-model="showChargeDialog"
      :order="order"
      :cash-session-open="cashSessionOpen"
      :loading="actionLoading"
      @confirm="onChargeConfirm"
      @cash-opened="cashSessionOpen = true"
    />

    <OrderHeaderEditDialog
      v-if="order"
      v-model="showHeaderEdit"
      :order="order"
      @save="onHeaderSave"
    />

    <VDialog
      v-model="showCancelConfirm"
      max-width="400"
    >
      <VCard>
        <VCardTitle>¿Cancelar comanda?</VCardTitle>
        <VCardText>
          Esta acción no se puede deshacer desde la app.
        </VCardText>
        <VCardActions>
          <VBtn
            variant="text"
            @click="showCancelConfirm = false"
          >
            No
          </VBtn>
          <VBtn
            color="error"
            :loading="actionLoading"
            @click="confirmCancel"
          >
            Sí, cancelar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</div>
</template>

<style scoped>
.order-detail {
  padding-block-end: 6rem;
}
</style>
