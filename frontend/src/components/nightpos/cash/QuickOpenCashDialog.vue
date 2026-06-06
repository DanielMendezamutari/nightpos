<script setup>
import { openCashSession } from '@/api/cash'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'opened'])

const { notify } = useNightPosNotify()
const saving = ref(false)
const refForm = ref()

const form = ref({
  opening_amount: 0,
  opening_notes: '',
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = { opening_amount: 0, opening_notes: '' }
  refForm.value?.resetValidation?.()
}

watch(() => props.modelValue, open => {
  if (open)
    reset()
})

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    const session = await openCashSession({
      opening_amount: Number(form.value.opening_amount),
      opening_notes: form.value.opening_notes?.trim() || null,
    })
    notify('Caja abierta')
    emit('opened', session)
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
    max-width="420"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-safe-2-line" />
        Abrir caja
      </VCardTitle>
      <VCardText>
        <p class="text-body-2 mb-4">
          Indique el fondo inicial para continuar con el cobro.
        </p>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VTextField
            v-model.number="form.opening_amount"
            type="number"
            label="Fondo inicial (BOB) *"
            min="0"
            step="0.01"
            :rules="[v => v >= 0 || 'Debe ser 0 o mayor']"
            class="mb-3"
          />
          <VTextarea
            v-model="form.opening_notes"
            label="Notas (opcional)"
            rows="2"
          />
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
          Abrir y continuar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
