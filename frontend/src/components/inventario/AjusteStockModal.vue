<script setup>
import { ref, watch } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
  insumo: Object,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const nuevoStock = ref(0)
const tipo = ref('AJUSTE_NEGATIVO')
const motivo = ref('')

watch(() => props.modelValue, (val) => {
  if (val && props.insumo) {
    nuevoStock.value = Number(props.insumo.stock_actual || 0)
    tipo.value = 'AJUSTE_NEGATIVO'
    motivo.value = ''
  }
})

const guardar = async () => {
  if (!props.insumo || !motivo.value) return

  await store.ajustarStock(props.insumo.id, {
    nuevo_stock: Number(nuevoStock.value),
    tipo: tipo.value,
    motivo: motivo.value,
  })

  emit('update:modelValue', false)
  emit('guardado')
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="450px"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <span class="text-h6 font-weight-bold">
          <VIcon icon="ri-equalizer-line" class="me-2" />
          Ajuste FÃ­sico de Stock
        </span>
        <VBtn icon="ri-close-line" variant="text" color="white" density="comfortable" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VCardText class="pa-4">
        <VAlert color="info" variant="tonal" class="mb-3">
          <div class="text-subtitle-2 font-weight-bold">{{ insumo?.nombre }}</div>
          <div>Stock en Sistema: <span class="font-weight-bold">{{ insumo?.stock_actual }} {{ insumo?.unidad_medida }}</span></div>
        </VAlert>

        <VTextField
          v-model.number="nuevoStock"
          type="number"
          label="Stock FÃ­sico Real *"
          density="compact"
          variant="outlined"
          class="mb-3"
          step="0.001"
        />

        <VSelect
          v-model="tipo"
          :items="[
            { title: 'Ajuste Negativo (Faltante)', value: 'AJUSTE_NEGATIVO' },
            { title: 'Ajuste Positivo (Sobrante)', value: 'AJUSTE_POSITIVO' },
            { title: 'Merma / Vencimiento / Rotura', value: 'MERMA' }
          ]"
          label="Tipo de Ajuste *"
          density="compact"
          variant="outlined"
          class="mb-3"
        />

        <VTextField
          v-model="motivo"
          label="JustificaciÃ³n / Motivo *"
          placeholder="Conteo semanal, rotura de envase..."
          density="compact"
          variant="outlined"
        />
      </VCardText>

      <VCardActions class="pa-4 pt-0">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">Cancelar</VBtn>
        <VBtn color="primary" variant="elevated" @click="guardar" :disabled="!motivo">
          <VIcon icon="ri-check-line" class="me-1" />
          Confirmar Ajuste
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>