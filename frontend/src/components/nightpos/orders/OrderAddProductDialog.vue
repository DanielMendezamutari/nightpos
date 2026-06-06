<script setup>
import PosProductPicker from '@/components/nightpos/catalog/PosProductPicker.vue'
import {
  formatMoney,
  formatSaleMode,
} from '@/composables/useOrderHelpers'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  pricePreview: { type: Object, default: null },
  pricesLoading: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  missingPrice: { type: Boolean, default: false },
  canConfigurePrice: { type: Boolean, default: false },
  canCreateProduct: { type: Boolean, default: false },
  presetProductId: { type: [Number, String], default: null },
  girls: { type: Array, default: () => [] },
  allowGirlOnAdd: { type: Boolean, default: false },
  canQuickCreateGirl: { type: Boolean, default: false },
  mobileWaiter: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'submit', 'preview-price', 'configure-price', 'create-product', 'toggle-favorite', 'quick-create-girl'])

const pickerRef = ref(null)

const addForm = ref({
  product_id: null,
  sale_mode: 'SOLO_CLIENTE',
  quantity: 1,
  notes: '',
  girl_user_id: null,
})

const selectedProductName = ref('')

const pickProduct = product => {
  addForm.value.product_id = product.id
  selectedProductName.value = product.name ?? ''
}

const pickProductMode = ({ product, saleMode }) => {
  addForm.value.product_id = product.id
  addForm.value.sale_mode = saleMode
  selectedProductName.value = product.name ?? ''
}

watch(() => props.modelValue, open => {
  if (open) {
    addForm.value = { product_id: null, sale_mode: 'SOLO_CLIENTE', quantity: 1, notes: '', girl_user_id: null }
    selectedProductName.value = ''
    pickerRef.value?.resetBrowse()
  }
})

watch(
  () => [addForm.value.product_id, addForm.value.sale_mode],
  () => emit('preview-price', { ...addForm.value }),
)

watch(() => props.presetProductId, id => {
  if (id)
    addForm.value.product_id = Number(id)
})

const close = () => emit('update:modelValue', false)

const submit = () => {
  emit('submit', { ...addForm.value })
}

const onConfigurePrice = ({ product, saleMode }) => {
  pickProductMode({ product, saleMode: saleMode ?? 'SOLO_CLIENTE' })
  emit('configure-price', { product_id: product.id, sale_mode: saleMode ?? 'SOLO_CLIENTE' })
}

const refreshPicker = () => pickerRef.value?.refresh()

defineExpose({ refreshPicker })
</script>

<template>
  <VDialog
    :model-value="modelValue"
    fullscreen
    transition="dialog-bottom-transition"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="order-add-product">
      <VToolbar
        color="primary"
        density="comfortable"
      >
        <VBtn
          icon
          @click="close"
        >
          <VIcon icon="ri-close-line" />
        </VBtn>
        <VToolbarTitle>{{ mobileWaiter ? 'Agregar bebida' : 'Agregar producto' }}</VToolbarTitle>
      </VToolbar>

      <VCardText class="pt-3 pb-6">
        <p
          v-if="mobileWaiter"
          class="text-body-2 text-medium-emphasis mb-4"
        >
          Selecciona el producto y la modalidad
        </p>

        <PosProductPicker
          ref="pickerRef"
          :layout="mobileWaiter ? 'grid' : 'list'"
          :compact="mobileWaiter"
          :autofocus="mobileWaiter"
          :search-placeholder="mobileWaiter ? 'Buscar bebida…' : 'Buscar producto…'"
          :selected-product-id="addForm.product_id"
          :selected-sale-mode="addForm.sale_mode"
          :can-configure-price="canConfigurePrice"
          :can-create-product="canCreateProduct"
          @pick-product="pickProduct"
          @pick-mode="pickProductMode"
          @configure-price="onConfigurePrice"
          @create-product="emit('create-product')"
          @toggle-favorite="p => emit('toggle-favorite', p)"
        />

        <template v-if="!mobileWaiter">
          <VSelect
            v-model="addForm.sale_mode"
            :items="[
              { title: formatSaleMode('SOLO_CLIENTE'), value: 'SOLO_CLIENTE' },
              { title: formatSaleMode('CON_ACOMPANANTE'), value: 'CON_ACOMPANANTE' },
            ]"
            label="Modalidad"
            class="mb-2 mt-4"
          />
        </template>

        <VSelect
          v-if="allowGirlOnAdd && addForm.sale_mode === 'CON_ACOMPANANTE' && girls.length"
          v-model="addForm.girl_user_id"
          :items="girls"
          label="Chica"
          class="mb-4"
        />
        <VAlert
          v-else-if="addForm.sale_mode === 'CON_ACOMPANANTE' && mobileWaiter && !girls.length"
          type="info"
          variant="tonal"
          density="compact"
          class="mb-4"
        >
          Puedes asignar la chica al enviar a barra.
        </VAlert>
        <VAlert
          v-else-if="addForm.sale_mode === 'CON_ACOMPANANTE' && !mobileWaiter"
          type="warning"
          variant="tonal"
          density="compact"
          class="mb-4"
        >
          {{ allowGirlOnAdd ? 'Seleccione chica o asígnela antes de enviar a barra.' : 'La chica se asignará antes de enviar a barra.' }}
        </VAlert>
        <VBtn
          v-if="allowGirlOnAdd && addForm.sale_mode === 'CON_ACOMPANANTE' && canQuickCreateGirl && !mobileWaiter"
          size="small"
          variant="text"
          prepend-icon="ri-user-add-line"
          class="mb-4"
          @click="emit('quick-create-girl')"
        >
          Nueva chica
        </VBtn>
        <VBtn
          v-if="mobileWaiter && allowGirlOnAdd && addForm.sale_mode === 'CON_ACOMPANANTE' && canQuickCreateGirl"
          size="small"
          variant="tonal"
          prepend-icon="ri-user-add-line"
          class="mb-4"
          block
          @click="emit('quick-create-girl')"
        >
          Nueva chica
        </VBtn>

        <VTextField
          v-if="addForm.product_id"
          v-model.number="addForm.quantity"
          type="number"
          label="Cantidad"
          min="1"
          max="99"
          inputmode="numeric"
          class="mb-4"
        />

        <VCard
          v-if="pricePreview && addForm.product_id"
          variant="tonal"
          color="primary"
          class="mb-4"
        >
          <VCardText class="py-3">
            <div class="text-body-2 text-medium-emphasis mb-1">
              {{ selectedProductName }} · {{ formatSaleMode(addForm.sale_mode, true) }}
            </div>
            <div class="text-h5 font-weight-bold">
              {{ formatMoney(pricePreview.price, pricePreview.currency) }}
            </div>
          </VCardText>
        </VCard>

        <VProgressLinear
          v-else-if="pricesLoading && addForm.product_id"
          indeterminate
          class="mb-4"
        />

        <VAlert
          v-else-if="missingPrice && addForm.product_id"
          type="warning"
          variant="tonal"
          class="mb-4"
        >
          Este producto no tiene precio configurado para esta modalidad.
          <div
            v-if="canConfigurePrice"
            class="mt-2"
          >
            <VBtn
              size="small"
              color="primary"
              variant="tonal"
              @click="emit('configure-price', { ...addForm })"
            >
              Configurar precio ahora
            </VBtn>
          </div>
        </VAlert>

        <VTextField
          v-if="addForm.product_id"
          v-model="addForm.notes"
          label="Notas línea (opcional)"
          class="mb-6"
        />

        <VBtn
          v-if="addForm.product_id"
          color="primary"
          size="x-large"
          block
          class="order-add-product__submit"
          :loading="loading"
          :disabled="!addForm.product_id || missingPrice"
          @click="submit"
        >
          {{ mobileWaiter ? 'Agregar bebida' : 'Agregar a comanda' }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.order-add-product__submit {
  min-block-size: 3.25rem;
}
</style>
