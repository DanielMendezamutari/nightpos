<script setup>
import { createShowType } from '@/api/showTypes'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'created'])

const { notify } = useNightPosNotify()
const saving = ref(false)
const refForm = ref()

const form = ref({
  name: '',
  suggested_price: null,
  status: 'active',
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = { name: '', suggested_price: null, status: 'active' }
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
    const showType = await createShowType({
      name: form.value.name.trim(),
      suggested_price: form.value.suggested_price != null ? Number(form.value.suggested_price) : null,
      status: form.value.status,
    })
    notify('Tipo de show creado')
    emit('created', showType)
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
    max-width="400"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-mic-line" />
        Nuevo tipo de show
      </VCardTitle>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VTextField
            v-model="form.name"
            label="Nombre *"
            autofocus
            :rules="[v => !!v?.trim() || 'Requerido']"
            class="mb-3"
          />
          <VTextField
            v-model.number="form.suggested_price"
            type="number"
            label="Precio sugerido (opcional)"
            min="0"
            prefix="Bs"
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
          Guardar tipo
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
