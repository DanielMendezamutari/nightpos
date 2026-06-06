<script setup>
import { quickCreateGirl } from '@/api/staff'
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
  notes: '',
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = { name: '', pin: '', notes: '' }
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
    const girl = await quickCreateGirl({
      name: form.value.name.trim(),
      pin: form.value.pin || null,
      notes: form.value.notes || null,
    })
    notify('Chica registrada')
    emit('created', girl)
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
        <VIcon icon="ri-user-add-line" />
        Nueva chica
      </VCardTitle>
      <VCardText>
        <p class="text-body-2 mb-4">
          Alta rápida operativa. Se creará como chica activa en esta sucursal.
        </p>
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
            placeholder="4 a 6 dígitos"
            maxlength="6"
            hint="Para acceso operativo de la chica"
            persistent-hint
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
          Guardar chica
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
