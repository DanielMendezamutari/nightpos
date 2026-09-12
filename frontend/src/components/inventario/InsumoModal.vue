<script setup>
import { ref, watch } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
  insumo: Object,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const form = ref({
  id: null,
  almacen_id: null,
  codigo: '',
  nombre: '',
  unidad_medida: 'UNID',
  costo_promedio: 0,
  stock_actual: 0,
  stock_minimo: 5,
  stock_maximo: 100,
})

const unidades = ['KG', 'GR', 'LT', 'ML', 'UNID', 'LATA', 'BOTELLA', 'PORCION', 'PAQUETE', 'CAJA']

watch(() => props.modelValue, (val) => {
  if (val) {
    if (props.insumo && props.insumo.id) {
      form.value = { ...props.insumo }
    } else {
      form.value = {
        id: null,
        almacen_id: store.almacenes[0]?.id || null,
        codigo: '',
        nombre: '',
        unidad_medida: 'UNID',
        costo_promedio: 0,
        stock_actual: 0,
        stock_minimo: 5,
        stock_maximo: 100,
      }
    }
  }
})

const guardar = async () => {
  if (!form.value.nombre) return
  await store.guardarInsumo(form.value)
  emit('update:modelValue', false)
  emit('guardado')
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="600px"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <span class="text-h6 font-weight-bold">
          <VIcon icon="ri-archive-line" class="me-2" />
          {{ form.id ? 'Editar Insumo / Materia Prima' : 'Nuevo Insumo de Almacén' }}
        </span>
        <VBtn icon="ri-close-line" variant="text" color="white" density="comfortable" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VCardText class="pa-4">
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="form.nombre"
              label="Nombre del Insumo *"
              placeholder="Ej. Carne de Res, Tomate, Pan..."
              density="compact"
              variant="outlined"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.codigo"
              label="Código / Ref"
              placeholder="INS-001"
              density="compact"
              variant="outlined"
            />
          </VCol>

          <VCol cols="12" md="6">
            <VSelect
              v-model="form.almacen_id"
              :items="store.almacenes"
              item-title="nombre"
              item-value="id"
              label="Almacén *"
              density="compact"
              variant="outlined"
            />
          </VCol>

          <VCol cols="12" md="6">
            <VSelect
              v-model="form.unidad_medida"
              :items="unidades"
              label="Unidad de Medida *"
              density="compact"
              variant="outlined"
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model.number="form.costo_promedio"
              type="number"
              label="Costo Unitario (Bs)"
              density="compact"
              variant="outlined"
              step="0.01"
            />
          </VCol>

          <VCol cols="12" md="4" v-if="!form.id">
            <VTextField
              v-model.number="form.stock_actual"
              type="number"
              label="Stock Inicial"
              density="compact"
              variant="outlined"
              step="0.001"
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model.number="form.stock_minimo"
              type="number"
              label="Stock Mínimo (Alerta)"
              density="compact"
              variant="outlined"
              step="0.001"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="pa-4 pt-0">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">Cancelar</VBtn>
        <VBtn color="primary" variant="elevated" @click="guardar">
          <VIcon icon="ri-save-line" class="me-1" />
          Guardar Insumo
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>