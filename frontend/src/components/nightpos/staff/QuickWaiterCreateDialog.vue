<script setup>
import { quickCreateWaiter } from '@/api/staff'
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
  pin: '',
  waiter_commission_percent: 5,
  notes: '',
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = { name: '', pin: '', waiter_commission_percent: 5, notes: '' }
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
    const waiter = await quickCreateWaiter({
      name: form.value.name.trim(),
      pin: form.value.pin || null,
      waiter_commission_percent: Number(form.value.waiter_commission_percent),
      notes: form.value.notes?.trim() || null,
    })
    notify('Garzón registrado')
    emit('created', waiter)
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
        <VIcon icon="ri-user-star-line" />
        Nuevo garzón
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
            v-model="form.pin"
            label="PIN (opcional)"
            maxlength="6"
            class="mb-3"
          />
          <VTextField
            v-model.number="form.waiter_commission_percent"
            type="number"
            label="Comisión % *"
            min="0"
            max="100"
            :rules="[v => v !== '' && v != null || 'Requerido']"
            class="mb-3"
          />
          <VTextarea
            v-model="form.notes"
            label="Observación (opcional)"
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
          Guardar garzón
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
