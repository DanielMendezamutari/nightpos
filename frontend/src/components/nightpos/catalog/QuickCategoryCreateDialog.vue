<script setup>
import { createCategory } from '@/api/categories'
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
  status: 'active',
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = { name: '', status: 'active' }
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
    const category = await createCategory({
      name: form.value.name.trim(),
      status: form.value.status,
    })
    notify('Categoría creada')
    emit('created', category)
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
        <VIcon icon="ri-folder-add-line" />
        Nueva categoría
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
          <VSelect
            v-model="form.status"
            :items="[
              { title: 'Activa', value: 'active' },
              { title: 'Inactiva', value: 'inactive' },
            ]"
            label="Estado"
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
          Guardar categoría
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
