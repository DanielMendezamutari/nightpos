<script setup>
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import {
  createProductPrice,
  fetchProduct,
  fetchProductPrices,
  replaceActiveProductPrice,
} from '@/api/products'
import { activePriceByMode, saleModeLabel } from '@/composables/useProductSaleModeLabels'
import { formatMoney } from '@/composables/useOrderHelpers'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { preventNumberWheelScroll } from '@/composables/usePreventNumberWheel'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.create' } })

const route = useRoute('nightpos-products-id-prices')
const { notify } = useNightPosNotify()

const product = ref(null)
const prices = ref([])
const loading = ref(true)
const saving = ref(false)

const priceForm = ref({
  sale_mode: 'SOLO_CLIENTE',
  solo_price: null,
  companion_price: null,
  girl_amount: null,
  house_amount: null,
})

const activeSolo = computed(() => activePriceByMode(prices.value, 'SOLO_CLIENTE'))
const activeCompanion = computed(() => activePriceByMode(prices.value, 'CON_ACOMPANANTE'))

const editingMode = computed(() => {
  if (priceForm.value.sale_mode === 'CON_ACOMPANANTE' && activeCompanion.value)
    return 'CON_ACOMPANANTE'
  if (activeSolo.value)
    return 'SOLO_CLIENTE'

  return priceForm.value.sale_mode
})

const isReplace = computed(() => {
  if (editingMode.value === 'SOLO_CLIENTE')
    return !!activeSolo.value

  return !!activeCompanion.value
})

const priceHeaders = [
  { title: 'Modalidad', key: 'sale_mode' },
  { title: 'Precio', key: 'price' },
  { title: 'Chica', key: 'girl_amount' },
  { title: 'Casa', key: 'house_amount' },
  { title: 'Estado', key: 'status' },
]

const load = async () => {
  loading.value = true
  try {
    product.value = await fetchProduct(route.params.id)
    prices.value = await fetchProductPrices(route.params.id)
    prefillForm()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const prefillForm = () => {
  const solo = activeSolo.value
  const comp = activeCompanion.value

  priceForm.value = {
    sale_mode: 'SOLO_CLIENTE',
    solo_price: solo ? Number(solo.price) : null,
    companion_price: comp ? Number(comp.price) : null,
    girl_amount: comp?.girl_amount != null ? Number(comp.girl_amount) : null,
    house_amount: comp?.house_amount != null ? Number(comp.house_amount) : null,
  }
}

watch(() => priceForm.value.sale_mode, () => {
  if (priceForm.value.sale_mode === 'SOLO_CLIENTE' && activeSolo.value) {
    priceForm.value.solo_price = Number(activeSolo.value.price)
  }
  if (priceForm.value.sale_mode === 'CON_ACOMPANANTE' && activeCompanion.value) {
    priceForm.value.companion_price = Number(activeCompanion.value.price)
    priceForm.value.girl_amount = Number(activeCompanion.value.girl_amount)
    priceForm.value.house_amount = Number(activeCompanion.value.house_amount)
  }
})

const savePrice = async () => {
  const mode = priceForm.value.sale_mode
  const payload = { sale_mode: mode }

  if (mode === 'SOLO_CLIENTE') {
    if (!priceForm.value.solo_price || priceForm.value.solo_price <= 0) {
      notify('Indique el precio cliente.', 'warning')

      return
    }
    payload.price = Number(priceForm.value.solo_price)
  }
  else {
    const total = Number(priceForm.value.companion_price)
    if (total <= 0) {
      notify('Indique el precio con acompañante.', 'warning')

      return
    }
    payload.price = total
    payload.girl_amount = Number(priceForm.value.girl_amount)
    payload.house_amount = Number(priceForm.value.house_amount)
  }

  saving.value = true
  try {
    if (isReplace.value && (mode === editingMode.value || activePriceByMode(prices.value, mode))) {
      await replaceActiveProductPrice(route.params.id, payload)
      notify('Precio actualizado')
    }
    else {
      await createProductPrice(route.params.id, payload)
      notify('Precio registrado')
    }

    prices.value = await fetchProductPrices(route.params.id)
    prefillForm()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="`Precios — ${product?.name || ''}`"
      subtitle="Actualice precios cliente y con acompañante. Los cambios guardan historial."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', to: { name: 'nightpos-products' } },
        { title: product?.name || 'Precios', disabled: true },
      ]"
    />

    <VAlert
      v-if="activeSolo || activeCompanion"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Al guardar se reemplaza el precio activo de la modalidad elegida (el anterior queda en historial).
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        lg="7"
      >
        <VCard title="Historial de precios">
          <VDataTable
            :headers="priceHeaders"
            :items="prices"
            :loading="loading"
            class="text-no-wrap"
          >
            <template #item.sale_mode="{ item }">
              {{ saleModeLabel(item.sale_mode) }}
              <div class="text-caption text-medium-emphasis">
                {{ item.sale_mode }}
              </div>
            </template>
            <template #item.price="{ item }">
              {{ formatMoney(item.price) }}
            </template>
            <template #item.girl_amount="{ item }">
              {{ item.girl_amount != null ? formatMoney(item.girl_amount) : '—' }}
            </template>
            <template #item.house_amount="{ item }">
              {{ item.house_amount != null ? formatMoney(item.house_amount) : '—' }}
            </template>
            <template #item.status="{ item }">
              <VChip
                size="small"
                :color="item.status === 'active' ? 'success' : 'secondary'"
                label
              >
                {{ item.status === 'active' ? 'Vigente' : 'Histórico' }}
              </VChip>
            </template>
          </VDataTable>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        lg="5"
      >
        <VCard title="Precios boliche">
          <VCardText>
            <VSelect
              v-model="priceForm.sale_mode"
              label="Qué precio desea configurar"
              :items="[
                { title: 'Precio cliente', value: 'SOLO_CLIENTE' },
                { title: 'Con acompañante', value: 'CON_ACOMPANANTE' },
              ]"
              class="mb-4"
            />

            <template v-if="priceForm.sale_mode === 'SOLO_CLIENTE'">
              <VTextField
                v-model.number="priceForm.solo_price"
                type="number"
                label="Precio cliente (BOB)"
                min="0.01"
                step="0.01"
                class="mb-3"
                @wheel="preventNumberWheelScroll"
              />
            </template>

            <template v-else>
              <VTextField
                v-model.number="priceForm.companion_price"
                type="number"
                label="Precio con acompañante (BOB)"
                min="0.01"
                step="0.01"
                class="mb-3"
                @wheel="preventNumberWheelScroll"
              />
              <VTextField
                v-model.number="priceForm.girl_amount"
                type="number"
                label="Monto chica (BOB)"
                min="0"
                step="0.01"
                class="mb-3"
                @wheel="preventNumberWheelScroll"
              />
              <VTextField
                v-model.number="priceForm.house_amount"
                type="number"
                label="Monto casa (BOB)"
                min="0"
                step="0.01"
                @wheel="preventNumberWheelScroll"
              />
            </template>

            <NightPosFormActions
              :saving="saving"
              :save-label="isReplace ? 'Actualizar precio' : 'Registrar precio'"
              :cancel-to="{ name: 'nightpos-products-id', params: { id: route.params.id } }"
              @save="savePrice"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
</div>
</template>
