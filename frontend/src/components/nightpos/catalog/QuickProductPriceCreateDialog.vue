<script setup>
import { createQuickProductPrice } from '@/api/products'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { preventNumberWheelScroll } from '@/composables/usePreventNumberWheel'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  productId: { type: [Number, String], default: null },
  saleMode: { type: String, default: 'SOLO_CLIENTE' },
  productName: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'created'])

const { notify } = useNightPosNotify()
const saving = ref(false)
const refForm = ref()

const form = ref({
  sale_mode: 'SOLO_CLIENTE',
  price: null,
  girl_amount: null,
  house_amount: null,
})

const showAcompanante = computed(() => form.value.sale_mode === 'CON_ACOMPANANTE')

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = {
    sale_mode: props.saleMode || 'SOLO_CLIENTE',
    price: null,
    girl_amount: null,
    house_amount: null,
  }
  refForm.value?.resetValidation?.()
}

watch(() => props.modelValue, open => {
  if (open)
    reset()
})

watch(() => props.saleMode, mode => {
  if (props.modelValue && mode)
    form.value.sale_mode = mode
})

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid || !props.productId)
    return

  saving.value = true
  try {
    const payload = {
      sale_mode: form.value.sale_mode,
      price: Number(form.value.price),
    }
    if (showAcompanante.value) {
      payload.girl_amount = Number(form.value.girl_amount)
      payload.house_amount = Number(form.value.house_amount)
    }

    const price = await createQuickProductPrice(props.productId, payload)
    notify('Precio configurado')
    emit('created', price)
    close()
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
    max-width="440"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-price-tag-3-line" />
        Configurar precio
      </VCardTitle>
      <VCardText>
        <p
          v-if="productName"
          class="text-body-2 mb-4"
        >
          Producto: <strong>{{ productName }}</strong>
        </p>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VSelect
            v-model="form.sale_mode"
            label="Modalidad *"
            :items="[
              { title: 'Solo cliente', value: 'SOLO_CLIENTE' },
              { title: 'Con acompañante', value: 'CON_ACOMPANANTE' },
            ]"
            class="mb-3"
          />
          <VTextField
            v-model.number="form.price"
            type="number"
            label="Precio *"
            min="0"
            :rules="[v => v != null && v >= 0 || 'Requerido']"
            class="mb-3"
            @wheel="preventNumberWheelScroll"
          />
          <template v-if="showAcompanante">
            <VTextField
              v-model.number="form.girl_amount"
              type="number"
              label="Monto chica *"
              min="0"
              :rules="[v => v != null && v >= 0 || 'Requerido']"
              class="mb-3"
              @wheel="preventNumberWheelScroll"
            />
            <VTextField
              v-model.number="form.house_amount"
              type="number"
              label="Monto casa *"
              min="0"
              :rules="[v => v != null && v >= 0 || 'Requerido']"
              @wheel="preventNumberWheelScroll"
            />
          </template>
        </VForm>
      </VCardText>
      <VCardActions>
        <VBtn
          variant="text"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VSpacer />
        <VBtn
          color="primary"
          :loading="saving"
          @click="save"
        >
          Guardar precio
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
