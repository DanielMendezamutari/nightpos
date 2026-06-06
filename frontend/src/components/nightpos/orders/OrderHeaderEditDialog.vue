<script setup>
import { fetchServiceAreas } from '@/api/serviceAreas'

const props = defineProps({
  order: { type: Object, required: true },
})

const model = defineModel({ type: Boolean, default: false })

const emit = defineEmits(['save'])

const form = ref({
  table_label: '',
  service_area_id: null,
  notes: '',
})
const serviceAreas = ref([])
const loadingAreas = ref(false)

watch(model, async open => {
  if (!open)
    return

  form.value = {
    table_label: props.order.table_label || '',
    service_area_id: props.order.service_area_id ?? null,
    notes: props.order.notes || '',
  }

  if (!serviceAreas.value.length) {
    loadingAreas.value = true

    try {
      serviceAreas.value = (await fetchServiceAreas({ active_only: true })).map(a => ({
        title: a.name,
        value: a.id,
      }))
    }
    catch {
      serviceAreas.value = []
    }
    finally {
      loadingAreas.value = false
    }
  }
})

const submit = () => {
  emit('save', { ...form.value })
}
</script>

<template>
  <VDialog
    v-model="model"
    max-width="420"
  >
    <VCard title="Corregir mesa / ambiente">
      <VCardText>
        <VSelect
          v-if="serviceAreas.length"
          v-model="form.service_area_id"
          :items="serviceAreas"
          :loading="loadingAreas"
          label="Ambiente"
          clearable
          class="mb-3"
        />
        <VTextField
          v-model="form.table_label"
          label="Mesa / etiqueta"
          class="mb-3"
        />
        <VTextarea
          v-model="form.notes"
          label="Notas"
          rows="2"
          auto-grow
        />
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          variant="text"
          @click="model = false"
        >
          Cerrar
        </VBtn>
        <VBtn
          color="primary"
          @click="submit"
        >
          Guardar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
