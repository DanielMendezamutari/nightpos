<script setup>
defineProps({
  saving: { type: Boolean, default: false },
  saveDisabled: { type: Boolean, default: false },
  saveLabel: { type: String, default: 'Guardar' },
  cancelTo: { type: [String, Object], default: null },
})

const emit = defineEmits(['save', 'cancel'])

const router = useRouter()

const onCancel = () => {
  emit('cancel')

  if (cancelTo)
    router.push(cancelTo)
}
</script>

<template>
  <div class="d-flex flex-wrap gap-3 mt-6">
    <VBtn
      color="primary"
      size="large"
      :loading="saving"
      :disabled="saveDisabled || saving"
      @click="emit('save')"
    >
      <VIcon
        icon="ri-save-line"
        start
      />
      {{ saveLabel }}
    </VBtn>
    <VBtn
      variant="outlined"
      size="large"
      :disabled="saving"
      @click="onCancel"
    >
      Cancelar
    </VBtn>
    <slot />
  </div>
</template>
