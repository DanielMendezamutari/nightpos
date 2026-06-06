<script setup>
import { fetchProductPrices } from '@/api/products'
import { updateOrderItem } from '@/api/orders'
import { loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  order: { type: Object, required: true },
  item: { type: Object, default: null },
  products: { type: Array, default: () => [] },
  productsLoading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'updated'])

const { notify } = useNightPosNotify()

const saving = ref(false)
const pricesLoading = ref(false)
const pricePreview = ref(null)
const girls = ref([])

const form = ref({
  product_id: null,
  sale_mode: 'SOLO_CLIENTE',
  girl_user_id: null,
  reason: '',
})

const requiresReason = computed(() =>
  props.order?.status === 'SENT_TO_BAR' && props.item?.item_status === 'SENT',
)

const productItems = computed(() =>
  (props.products ?? []).map(p => ({ title: p.name, value: p.id })),
)

const resetForm = () => {
  form.value = {
    product_id: null,
    sale_mode: props.item?.sale_mode ?? 'SOLO_CLIENTE',
    girl_user_id: props.item?.girl_user_id ?? null,
    reason: '',
  }
  pricePreview.value = null
}

watch(() => props.modelValue, open => {
  if (open)
    resetForm()
})

const loadPricePreview = async () => {
  pricePreview.value = null

  if (!form.value.product_id || !form.value.sale_mode)
    return

  pricesLoading.value = true

  try {
    const prices = await fetchProductPrices(form.value.product_id)
    const active = prices.find(p => p.sale_mode === form.value.sale_mode && p.status === 'active')

    pricePreview.value = active ?? null
  }
  catch {
    pricePreview.value = null
  }
  finally {
    pricesLoading.value = false
  }
}

watch(
  () => [form.value.product_id, form.value.sale_mode],
  loadPricePreview,
)

watch(
  () => form.value.sale_mode,
  async mode => {
    if (mode === 'CON_ACOMPANANTE' && !girls.value.length) {
      try {
        girls.value = await loadOperationalGirlsForSelect()
      }
      catch {
        girls.value = []
      }
    }
  },
)

const close = async () => {
  emit('update:modelValue', false)
  await nextTick()
}

const save = async () => {
  if (!props.item || !form.value.product_id) {
    notify('Seleccione el nuevo producto.', 'warning')

    return
  }

  if (form.value.product_id === props.item.product_id) {
    notify('Elija un producto distinto al actual.', 'warning')

    return
  }

  if (requiresReason.value && !form.value.reason?.trim()) {
    notify('Indique el motivo del cambio (línea ya enviada a barra).', 'warning')

    return
  }

  if (!pricePreview.value) {
    notify('El producto no tiene precio activo para la modalidad seleccionada.', 'error')

    return
  }

  saving.value = true

  try {
    const payload = {
      product_id: form.value.product_id,
      sale_mode: form.value.sale_mode,
    }

    if (form.value.sale_mode === 'CON_ACOMPANANTE')
      payload.girl_user_id = form.value.girl_user_id

    if (requiresReason.value)
      payload.reason = form.value.reason.trim()

    const order = await updateOrderItem(props.order.id, props.item.id, payload)

    notify('Producto actualizado correctamente')
    await close()
    await nextTick()
    emit('updated', order)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="480"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard
      v-if="item"
      title="Cambiar producto"
    >
      <VCardText>
        <p class="text-body-2 mb-4">
          Producto actual:
          <strong>{{ item?.product_name }}</strong>
        </p>

        <VAutocomplete
          v-model="form.product_id"
          :items="productItems"
          label="Nuevo producto"
          :loading="productsLoading"
          clearable
          auto-select-first
        />

        <VSelect
          v-model="form.sale_mode"
          class="mt-3"
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

        <VTextarea
          v-if="requiresReason"
          v-model="form.reason"
          class="mt-3"
          label="Motivo del cambio"
          rows="2"
          auto-grow
          hint="Obligatorio: la línea ya fue enviada a barra"
          persistent-hint
        />

        <VAlert
          v-if="form.product_id && !pricesLoading && !pricePreview"
          type="warning"
          variant="tonal"
          class="mt-3"
        >
          Este producto no tiene precio activo para la modalidad seleccionada.
        </VAlert>

        <div
          v-else-if="pricePreview"
          class="text-body-2 mt-3"
        >
          Precio unitario:
          <strong>{{ formatMoney(pricePreview.price, order.currency) }}</strong>
        </div>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          variant="text"
          @click="close"
        >
          Cerrar
        </VBtn>
        <VBtn
          color="primary"
          :loading="saving"
          :disabled="!form.product_id || !pricePreview"
          @click="save"
        >
          Guardar cambio
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
