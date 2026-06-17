<script setup>
import { fetchCurrentCashSession } from '@/api/cash'
import { createDirectSale } from '@/api/sales'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import PosProductPicker from '@/components/nightpos/catalog/PosProductPicker.vue'
import QuickProductPriceCreateDialog from '@/components/nightpos/catalog/QuickProductPriceCreateDialog.vue'
import MixedPaymentForm from '@/components/nightpos/payments/MixedPaymentForm.vue'
import { loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOrderProductShortcuts } from '@/composables/useOrderProductShortcuts'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import { formatMoney, productActivePrice } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: 'sales.direct_create',
  },
})

const { canDirectSale, can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const canConfigurePrice = computed(() =>
  can('product_prices.quick_create') || can('products.update'),
)

const { recordRecent } = useOrderProductShortcuts()
const pickerRef = ref(null)

// ---- Estado de caja ----
const cashSessionOpen = ref(false)
const cashLoading = ref(true)
const showOpenCash = ref(false)

// ---- Chicas ----
const girls = ref([])
const girlsLoading = ref(false)

// ---- Carrito ----
const cart = ref([])

// ---- Pago mixto ----
const paymentFormRef = ref(null)

// ---- Resultado ----
const saving = ref(false)
const lastSale = ref(null)

const cartTotal = computed(() =>
  cart.value.reduce((sum, item) => sum + item.line_total, 0),
)

const cartEmpty = computed(() => cart.value.length === 0)

const loadCash = async () => {
  cashLoading.value = true

  try {
    const session = await fetchCurrentCashSession()

    cashSessionOpen.value = session?.status === 'OPEN'
  }
  catch {
    cashSessionOpen.value = false
  }
  finally {
    cashLoading.value = false
  }
}

// ─── SSE ─────────────────────────────────────────────────────────────────────
const { on, start: startSse, stop: stopSse } = useOperationalEvents()
on('cash.session.opened', loadCash)
on('cash.session.closed', loadCash)
// ─────────────────────────────────────────────────────────────────────────────

// ---- Init ----
onMounted(() => {
  loadCash()
  startSse()
})
onUnmounted(() => { stopSse() })

// ---- Configurar precio desde POS ----
const showPriceDialog = ref(false)
const priceDialogProduct = ref(null)
const priceDialogMode = ref('SOLO_CLIENTE')

const openPriceDialog = (product, saleMode = 'SOLO_CLIENTE') => {
  priceDialogProduct.value = product
  priceDialogMode.value = saleMode
  showPriceDialog.value = true
}

const onPriceCreated = async () => {
  showPriceDialog.value = false
  await pickerRef.value?.refresh()
  notify('Precio configurado. Ya puede vender este producto.', 'success')
}

const loadGirls = async () => {
  if (girls.value.length)
    return

  girlsLoading.value = true

  try {
    girls.value = await loadOperationalGirlsForSelect()
  }
  catch {
    girls.value = []
  }
  finally {
    girlsLoading.value = false
  }
}

const getPrice = (product, saleMode) => productActivePrice(product, saleMode) ?? null

const onPickMode = ({ product, saleMode }) => addToCart(product, saleMode)

const onConfigurePrice = ({ product, saleMode }) => openPriceDialog(product, saleMode ?? 'SOLO_CLIENTE')

// ---- Carrito ----
const addToCart = async (product, saleMode = 'SOLO_CLIENTE') => {
  const priceRow = getPrice(product, saleMode)

  if (!priceRow) {
    notify('Este producto no tiene precio activo para esa modalidad.', 'warning')

    return
  }

  if (product.requires_allocation) {
    notify('Este combo debe venderse por comanda para asignar manillas.', 'warning')

    return
  }

  const existing = cart.value.find(
    i => i.product_id === product.id && i.sale_mode === saleMode && !i.girl_user_id,
  )

  if (existing) {
    existing.quantity += 1
    existing.line_total = existing.quantity * parseFloat(priceRow.price)

    return
  }

  if (saleMode === 'CON_ACOMPANANTE')
    await loadGirls()

  cart.value.push({
    _key: Date.now(),
    product_id: product.id,
    product_name: product.name,
    sale_mode: saleMode,
    quantity: 1,
    unit_price: parseFloat(priceRow.price),
    line_total: parseFloat(priceRow.price),
    currency: priceRow.currency,
    girl_user_id: null,
  })

  recordRecent(product.id)
}

const removeFromCart = index => {
  cart.value.splice(index, 1)
}

const changeQty = (item, delta) => {
  const newQty = item.quantity + delta

  if (newQty < 1) {
    const idx = cart.value.indexOf(item)

    if (idx >= 0)
      cart.value.splice(idx, 1)

    return
  }

  item.quantity = newQty
  item.line_total = newQty * item.unit_price
}

const clearCart = () => {
  cart.value = []
}

// ---- Confirmar venta ----
const confirmSale = async () => {
  if (saving.value)
    return

  if (cartEmpty.value) {
    notify('Agregue al menos un producto.', 'warning')

    return
  }

  const itemsMissingGirl = cart.value.filter(
    i => i.sale_mode === 'CON_ACOMPANANTE' && !i.girl_user_id,
  )

  if (itemsMissingGirl.length) {
    notify('Asigne chica a los ítems CON_ACOMPANANTE antes de cobrar.', 'warning')

    return
  }

  const paymentCheck = paymentFormRef.value?.validate()

  if (!paymentCheck?.valid) {
    notify(paymentCheck?.message ?? 'Revise los montos de pago.', 'warning')

    return
  }

  const paymentPayload = paymentFormRef.value?.toPayload()

  if (!paymentPayload?.payments?.length) {
    notify('Indique al menos un monto de pago.', 'warning')

    return
  }

  saving.value = true

  try {
    const payload = {
      items: cart.value.map(i => ({
        product_id: i.product_id,
        sale_mode: i.sale_mode,
        quantity: i.quantity,
        girl_user_id: i.girl_user_id ?? null,
      })),
      payments: paymentPayload.payments,
    }

    const result = await createDirectSale(payload)

    lastSale.value = result.sale
    clearCart()
    notify('Venta registrada correctamente', 'success')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const startNewSale = () => {
  lastSale.value = null
  pickerRef.value?.resetBrowse()
  paymentFormRef.value?.reset()
}

const onCashOpened = () => {
  cashSessionOpen.value = true
  showOpenCash.value = false
}
</script>

<template>
  <div class="direct-sale-page">
    <h5 class="text-h5 font-weight-bold mb-4">
      <VIcon
        icon="ri-shopping-bag-3-line"
        class="me-2"
      />
      Venta directa
    </h5>

    <!-- Sin caja abierta -->
    <VAlert
      v-if="!cashLoading && !cashSessionOpen"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      <div class="d-flex flex-column flex-sm-row align-sm-center justify-space-between gap-2">
        <span>Debe abrir caja para realizar una venta directa.</span>
        <VBtn
          color="primary"
          size="small"
          variant="tonal"
          @click="showOpenCash = true"
        >
          Abrir caja ahora
        </VBtn>
      </div>
    </VAlert>

    <!-- Venta completada -->
    <VCard
      v-if="lastSale"
      color="success"
      variant="tonal"
      class="mb-4"
    >
      <VCardText class="d-flex flex-column align-center text-center py-6">
        <VIcon
          icon="ri-checkbox-circle-line"
          size="48"
          class="mb-3"
        />
        <div class="text-h6 font-weight-bold mb-1">
          Venta registrada correctamente
        </div>
        <div class="text-body-2 mb-1">
          Número: <strong>{{ lastSale.sale_number }}</strong>
        </div>
        <div class="text-body-2 mb-1">
          Total cobrado: <strong>{{ formatMoney(lastSale.total, lastSale.currency) }}</strong>
        </div>
        <div class="text-body-2 mb-3">
          Método: <strong>{{ lastSale.payment_mode }}</strong>
        </div>
        <VBtn
          color="success"
          variant="tonal"
          prepend-icon="ri-add-circle-line"
          @click="startNewSale"
        >
          Nueva venta
        </VBtn>
      </VCardText>
    </VCard>

    <!-- POS Layout -->
    <template v-if="!lastSale && cashSessionOpen">
      <VRow>
        <!-- Panel izquierdo: catálogo -->
        <VCol
          cols="12"
          md="7"
        >
          <VCard>
            <VCardText>
              <PosProductPicker
                ref="pickerRef"
                layout="grid"
                :can-configure-price="canConfigurePrice"
                @pick-mode="onPickMode"
                @configure-price="onConfigurePrice"
              />
            </VCardText>
          </VCard>
        </VCol>

        <!-- Panel derecho: carrito -->
        <VCol
          cols="12"
          md="5"
        >
          <VCard>
            <VCardTitle class="d-flex justify-space-between align-center">
              <span>Carrito</span>
              <VBtn
                v-if="!cartEmpty"
                size="small"
                variant="text"
                color="error"
                @click="clearCart"
              >
                Limpiar
              </VBtn>
            </VCardTitle>

            <VCardText>
              <VAlert
                v-if="cartEmpty"
                type="info"
                variant="tonal"
                density="compact"
              >
                Seleccione productos del catálogo.
              </VAlert>

              <div
                v-else
                class="cart-items"
              >
                <div
                  v-for="(item, idx) in cart"
                  :key="item._key"
                  class="cart-item d-flex align-start gap-2 mb-3"
                >
                  <div class="flex-grow-1">
                    <div class="text-body-2 font-weight-medium">
                      {{ item.product_name }}
                    </div>
                    <VChip
                      size="x-small"
                      :color="item.sale_mode === 'CON_ACOMPANANTE' ? 'secondary' : 'primary'"
                      variant="tonal"
                      class="mt-1"
                    >
                      {{ item.sale_mode === 'CON_ACOMPANANTE' ? 'Con acompañante' : 'Solo' }}
                    </VChip>

                    <!-- Chica para CON_ACOMPANANTE -->
                    <VSelect
                      v-if="item.sale_mode === 'CON_ACOMPANANTE'"
                      v-model="item.girl_user_id"
                      :items="girls"
                      :loading="girlsLoading"
                      label="Chica"
                      density="compact"
                      class="mt-2"
                      clearable
                    />

                    <!-- Precio línea -->
                    <div class="text-body-2 text-medium-emphasis mt-1">
                      {{ formatMoney(item.unit_price, item.currency) }} × {{ item.quantity }}
                      = <strong>{{ formatMoney(item.line_total, item.currency) }}</strong>
                    </div>
                  </div>

                  <!-- Controles cantidad -->
                  <div class="d-flex flex-column align-center gap-1">
                    <VBtn
                      icon
                      size="x-small"
                      variant="tonal"
                      @click="changeQty(item, +1)"
                    >
                      <VIcon icon="ri-add-line" />
                    </VBtn>
                    <span class="text-body-2 font-weight-bold">{{ item.quantity }}</span>
                    <VBtn
                      icon
                      size="x-small"
                      variant="tonal"
                      color="error"
                      @click="changeQty(item, -1)"
                    >
                      <VIcon icon="ri-subtract-line" />
                    </VBtn>
                  </div>

                  <VBtn
                    icon
                    size="x-small"
                    variant="text"
                    color="error"
                    class="mt-1"
                    @click="removeFromCart(idx)"
                  >
                    <VIcon icon="ri-delete-bin-line" />
                  </VBtn>
                </div>
              </div>

              <!-- Total -->
              <VDivider
                v-if="!cartEmpty"
                class="mb-3 mt-1"
              />

              <div
                v-if="!cartEmpty"
                class="d-flex justify-space-between text-h6 font-weight-bold mb-4"
              >
                <span>Total</span>
                <span class="text-primary">
                  {{ formatMoney(cartTotal, cart[0]?.currency ?? 'BOB') }}
                </span>
              </div>

              <!-- Pago mixto + cobro -->
              <VForm
                v-if="!cartEmpty"
                @submit.prevent="confirmSale"
              >
                <MixedPaymentForm
                  ref="paymentFormRef"
                  :total="cartTotal"
                  :currency="cart[0]?.currency ?? 'BOB'"
                  show-quick-buttons
                  class="mb-4"
                />

                <VBtn
                  color="success"
                  size="x-large"
                  block
                  type="submit"
                  :loading="saving"
                  :disabled="!cashSessionOpen || saving"
                  prepend-icon="ri-money-dollar-circle-line"
                >
                  Cobrar {{ formatMoney(cartTotal, cart[0]?.currency ?? 'BOB') }}
                </VBtn>
              </VForm>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <!-- Snackbar -->
<!-- Abrir caja -->
    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />

    <!-- Configurar precio desde POS (DSP-3) -->
    <QuickProductPriceCreateDialog
      v-model="showPriceDialog"
      :product-id="priceDialogProduct?.id ?? null"
      :product-name="priceDialogProduct?.name ?? ''"
      :sale-mode="priceDialogMode"
      @created="onPriceCreated"
    />
  </div>
</template>

<style scoped>
.cart-item {
  border-block-end: 1px solid rgba(var(--v-border-color), 0.12);
  padding-block-end: 0.5rem;
}
</style>
