<script setup>
import { fetchProducts } from '@/api/products'
import {
  cancelOrderItem,
  removeOrderItem,
  updateOrderItem,
} from '@/api/orders'
import ChangeOrderItemProductDialog from '@/components/nightpos/orders/ChangeOrderItemProductDialog.vue'
import { loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import {
  formatMoney,
  orderItemStatusLabel,
  SALE_MODE_LABELS,
} from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  order: { type: Object, required: true },
  editable: { type: Boolean, default: false },
  cashierCorrectionMode: { type: Boolean, default: false },
  canUpdateItems: { type: Boolean, default: false },
  canCancelItems: { type: Boolean, default: false },
})

const emit = defineEmits(['updated', 'correction-loading'])

const { notify } = useNightPosNotify()

const localLoading = ref(false)
const busy = computed(() => localLoading.value)

const products = ref([])
const productsLoading = ref(false)
const girls = ref([])
const actionsDialogOpen = ref(false)
const dialog = ref({
  product: false,
  quantity: false,
  mode: false,
  girl: false,
  remove: false,
  cancel: false,
})
const activeItem = ref(null)
const form = ref({
  quantity: 1,
  sale_mode: 'SOLO_CLIENTE',
  girl_user_id: null,
  reason: '',
})

const isOpen = computed(() => props.order.status === 'OPEN')
const isSentToBar = computed(() => props.order.status === 'SENT_TO_BAR')
const useTableLayout = computed(() => props.cashierCorrectionMode)

const anyCorrectionDialogOpen = computed(() =>
  actionsDialogOpen.value
  || Object.values(dialog.value).some(Boolean),
)

const canEditLine = item => props.editable && item.item_status !== 'CANCELLED'

watch(localLoading, value => {
  emit('correction-loading', value)
})

const guardPermission = (needsUpdate = true) => {
  if (needsUpdate && !props.canUpdateItems) {
    notify('Sin permiso para corregir ítems. Cierre sesión y vuelva a entrar.', 'warning')

    return false
  }

  return true
}

const closeAllDialogs = () => {
  actionsDialogOpen.value = false
  Object.keys(dialog.value).forEach(k => { dialog.value[k] = false })
}

const clearActiveItemLater = async () => {
  await nextTick()
  if (!anyCorrectionDialogOpen.value)
    activeItem.value = null
}

const emitOrderUpdate = async order => {
  closeAllDialogs()
  await nextTick()
  emit('updated', order)
  await clearActiveItemLater()
}

const loadProducts = async () => {
  if (products.value.length)
    return

  productsLoading.value = true

  try {
    products.value = await fetchProducts()
  }
  catch {
    products.value = []
  }
  finally {
    productsLoading.value = false
  }
}

const openActionsDialog = item => {
  if (!canEditLine(item))
    return

  activeItem.value = item
  actionsDialogOpen.value = true
}

const pickAction = async type => {
  const item = activeItem.value
  if (!item)
    return

  actionsDialogOpen.value = false
  await nextTick()
  await openDialog(type, item)
}

const openDialog = async (type, item) => {
  if (type === 'cancel') {
    if (!props.canCancelItems) {
      notify('Sin permiso para cancelar líneas.', 'warning')

      return
    }
  }
  else if (!guardPermission()) {
    return
  }

  activeItem.value = item
  form.value = {
    quantity: item.quantity,
    sale_mode: item.sale_mode,
    girl_user_id: item.girl_user_id,
    reason: '',
  }

  if (type === 'product')
    await loadProducts()

  if ((type === 'girl' || type === 'mode') && !girls.value.length) {
    try {
      girls.value = await loadOperationalGirlsForSelect()
    }
    catch {
      girls.value = []
    }
  }

  await nextTick()
  dialog.value[type] = true
}

const closeDialog = async key => {
  dialog.value[key] = false
  await clearActiveItemLater()
}

const applyUpdate = async payload => {
  if (!activeItem.value || !guardPermission())
    return

  localLoading.value = true

  try {
    const order = await updateOrderItem(props.order.id, activeItem.value.id, payload)

    await emitOrderUpdate(order)
    notify('Línea actualizada')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    localLoading.value = false
  }
}

const confirmRemove = async () => {
  if (!activeItem.value || !guardPermission())
    return

  localLoading.value = true

  try {
    const order = await removeOrderItem(props.order.id, activeItem.value.id)

    await emitOrderUpdate(order)
    notify('Línea eliminada')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    localLoading.value = false
  }
}

const confirmCancelLine = async () => {
  if (!activeItem.value || !props.canCancelItems) {
    notify('Sin permiso para cancelar líneas.', 'warning')

    return
  }

  if (!form.value.reason?.trim()) {
    notify('Indique el motivo de cancelación.', 'warning')

    return
  }

  localLoading.value = true

  try {
    const order = await cancelOrderItem(
      props.order.id,
      activeItem.value.id,
      form.value.reason.trim(),
    )

    await emitOrderUpdate(order)
    notify('Línea cancelada')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    localLoading.value = false
  }
}

const onProductChanged = async order => {
  await emitOrderUpdate(order)
}

const onActionsDialogToggle = async open => {
  if (!open) {
    actionsDialogOpen.value = false
    await clearActiveItemLater()
  }
}

onBeforeUnmount(() => {
  closeAllDialogs()
  activeItem.value = null
})

defineExpose({ closeAllDialogs })
</script>

<template>
  <div class="order-items-table-root">
    <VCard class="mb-4">
      <VCardTitle class="d-flex justify-space-between align-center flex-wrap gap-2">
        <span>Productos</span>
        <span class="text-h5 text-primary">{{ formatMoney(order.total, order.currency) }}</span>
      </VCardTitle>
      <VCardText>
        <VAlert
          v-if="!order.items?.length"
          type="info"
          variant="tonal"
        >
          Sin productos. Agregue al menos uno antes de enviar a barra.
        </VAlert>

        <VTable
          v-else-if="useTableLayout"
          class="order-items-table"
          density="comfortable"
        >
          <thead>
            <tr>
              <th>Producto</th>
              <th class="text-center">
                Cantidad
              </th>
              <th>Modalidad</th>
              <th class="text-end">
                Total
              </th>
              <th
                v-if="editable"
                class="text-end"
              >
                Acciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in order.items"
              :key="item.id"
              :class="{ 'order-item-row--cancelled': item.item_status === 'CANCELLED' }"
            >
              <td>
                <div
                  class="font-weight-medium"
                  :class="{ 'text-decoration-line-through text-medium-emphasis': item.item_status === 'CANCELLED' }"
                >
                  {{ item.product_name }}
                </div>
                <VChip
                  v-if="item.item_status !== 'PENDING'"
                  size="x-small"
                  :color="item.item_status === 'CANCELLED' ? 'error' : 'info'"
                  variant="tonal"
                  class="mt-1"
                >
                  {{ orderItemStatusLabel(item.item_status) }}
                </VChip>
                <div
                  v-if="item.cancellation_reason"
                  class="text-error text-caption mt-1"
                >
                  Motivo: {{ item.cancellation_reason }}
                </div>
              </td>
              <td class="text-center">
                {{ item.quantity }}
              </td>
              <td>
                {{ SALE_MODE_LABELS[item.sale_mode] || item.sale_mode }}
              </td>
              <td class="text-end">
                {{ formatMoney(item.line_total, order.currency) }}
              </td>
              <td
                v-if="editable"
                class="text-end"
              >
                <VBtn
                  v-if="canEditLine(item)"
                  color="primary"
                  variant="tonal"
                  size="small"
                  prepend-icon="ri-edit-line"
                  :disabled="busy"
                  @click="openActionsDialog(item)"
                >
                  Corregir
                </VBtn>
              </td>
            </tr>
          </tbody>
        </VTable>

        <VList v-else>
          <VListItem
            v-for="item in order.items"
            :key="item.id"
            class="order-item-row"
            :class="{ 'order-item-row--cancelled': item.item_status === 'CANCELLED' }"
          >
            <VListItemTitle class="d-flex flex-wrap align-center gap-2">
              <span :class="{ 'text-decoration-line-through text-medium-emphasis': item.item_status === 'CANCELLED' }">
                {{ item.product_name }}
              </span>
              <VChip
                size="x-small"
                variant="outlined"
              >
                {{ SALE_MODE_LABELS[item.sale_mode] || item.sale_mode }}
              </VChip>
            </VListItemTitle>
            <VListItemSubtitle>
              {{ item.quantity }} × {{ formatMoney(item.unit_price, order.currency) }}
              = {{ formatMoney(item.line_total, order.currency) }}
            </VListItemSubtitle>
            <template
              v-if="canEditLine(item)"
              #append
            >
              <VBtn
                color="primary"
                variant="tonal"
                size="small"
                prepend-icon="ri-edit-line"
                :disabled="busy"
                @click="openActionsDialog(item)"
              >
                Corregir
              </VBtn>
            </template>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>

    <!-- Diálogos montados solo cuando abiertos (evita scrim huérfano) -->
    <VDialog
      v-if="actionsDialogOpen"
      :model-value="true"
      max-width="400"
      @update:model-value="onActionsDialogToggle"
    >
      <VCard :title="activeItem ? `Corregir: ${activeItem.product_name}` : 'Corregir línea'">
        <VCardText class="pa-0">
          <VList v-if="activeItem">
            <VListItem
              title="Cambiar producto"
              prepend-icon="ri-swap-line"
              @click="pickAction('product')"
            />
            <VListItem
              v-if="isOpen"
              title="Cambiar cantidad"
              prepend-icon="ri-hashtag"
              @click="pickAction('quantity')"
            />
            <VListItem
              v-if="isOpen"
              title="Cambiar modalidad"
              prepend-icon="ri-exchange-line"
              @click="pickAction('mode')"
            />
            <VListItem
              v-if="activeItem.sale_mode === 'CON_ACOMPANANTE'"
              title="Cambiar chica"
              prepend-icon="ri-user-star-line"
              @click="pickAction('girl')"
            />
            <VListItem
              v-if="isOpen && activeItem.item_status === 'PENDING'"
              title="Quitar línea"
              prepend-icon="ri-delete-bin-line"
              class="text-error"
              @click="pickAction('remove')"
            />
            <VListItem
              v-if="isSentToBar && activeItem.item_status === 'SENT'"
              title="Cancelar línea"
              prepend-icon="ri-close-circle-line"
              class="text-error"
              @click="pickAction('cancel')"
            />
          </VList>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="onActionsDialogToggle(false)"
          >
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <ChangeOrderItemProductDialog
      v-if="dialog.product && activeItem"
      :model-value="true"
      :order="order"
      :item="activeItem"
      :products="products"
      :products-loading="productsLoading"
      @update:model-value="val => { if (!val) closeDialog('product') }"
      @updated="onProductChanged"
    />

    <VDialog
      v-if="dialog.quantity && activeItem"
      :model-value="true"
      max-width="360"
      @update:model-value="val => { if (!val) closeDialog('quantity') }"
    >
      <VCard title="Cambiar cantidad">
        <VCardText>
          <p class="text-body-2 mb-3">
            {{ activeItem.product_name }}
          </p>
          <VTextField
            v-model.number="form.quantity"
            type="number"
            min="1"
            label="Cantidad"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="closeDialog('quantity')"
          >
            Cerrar
          </VBtn>
          <VBtn
            color="primary"
            :loading="busy"
            @click="applyUpdate({ quantity: Number(form.quantity) || 1 })"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-if="dialog.mode && activeItem"
      :model-value="true"
      max-width="360"
      @update:model-value="val => { if (!val) closeDialog('mode') }"
    >
      <VCard title="Cambiar modalidad">
        <VCardText>
          <VSelect
            v-model="form.sale_mode"
            :items="[
              { title: 'Solo cliente', value: 'SOLO_CLIENTE' },
              { title: 'Con acompañante', value: 'CON_ACOMPANANTE' },
            ]"
            label="Modalidad"
          />
          <VSelect
            v-if="form.sale_mode === 'CON_ACOMPANANTE'"
            v-model="form.girl_user_id"
            :items="girls"
            label="Chica"
            class="mt-3"
            clearable
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="closeDialog('mode')"
          >
            Cerrar
          </VBtn>
          <VBtn
            color="primary"
            :loading="busy"
            @click="applyUpdate({
              sale_mode: form.sale_mode,
              girl_user_id: form.sale_mode === 'CON_ACOMPANANTE' ? form.girl_user_id : null,
            })"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-if="dialog.girl && activeItem"
      :model-value="true"
      max-width="360"
      @update:model-value="val => { if (!val) closeDialog('girl') }"
    >
      <VCard title="Cambiar chica">
        <VCardText>
          <VSelect
            v-model="form.girl_user_id"
            :items="girls"
            label="Chica"
            clearable
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="closeDialog('girl')"
          >
            Cerrar
          </VBtn>
          <VBtn
            color="primary"
            :loading="busy"
            @click="applyUpdate({ girl_user_id: form.girl_user_id })"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-if="dialog.remove && activeItem"
      :model-value="true"
      max-width="360"
      @update:model-value="val => { if (!val) closeDialog('remove') }"
    >
      <VCard title="Quitar línea">
        <VCardText>
          ¿Quitar <strong>{{ activeItem.product_name }}</strong> de la comanda?
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="closeDialog('remove')"
          >
            No
          </VBtn>
          <VBtn
            color="error"
            :loading="busy"
            @click="confirmRemove"
          >
            Quitar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-if="dialog.cancel && activeItem"
      :model-value="true"
      max-width="400"
      @update:model-value="val => { if (!val) closeDialog('cancel') }"
    >
      <VCard title="Cancelar línea enviada">
        <VCardText>
          <p class="mb-3 text-body-2">
            {{ activeItem.product_name }} — indique el motivo (obligatorio).
          </p>
          <VTextarea
            v-model="form.reason"
            label="Motivo"
            rows="2"
            auto-grow
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="closeDialog('cancel')"
          >
            Cerrar
          </VBtn>
          <VBtn
            color="error"
            :loading="busy"
            @click="confirmCancelLine"
          >
            Cancelar línea
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.order-item-row {
  border-block-end: 1px solid rgba(var(--v-border-color), 0.12);
}

.order-item-row--cancelled {
  opacity: 0.75;
}

.order-items-table th {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  white-space: nowrap;
}
</style>
