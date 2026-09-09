<script setup>
import { ref, computed, watch } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
  producto: Object,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const ingredientes = ref([])
const insumoSeleccionado = ref(null)
const cantInput = ref(0.100)
const mermaInput = ref(0)
const loading = ref(false)

watch(() => props.modelValue, async (val) => {
  if (val && props.producto) {
    loading.value = true
    const res = await store.fetchReceta(props.producto.id)
    if (res && res.ingredientes) {
      ingredientes.value = res.ingredientes.map(i => ({
        insumo_id: i.insumo_id,
        nombre: i.insumo?.nombre || 'Insumo',
        unidad: i.unidad_medida,
        cantidad: Number(i.cantidad),
        merma_porcentaje: Number(i.merma_porcentaje || 0),
        costo_unit: Number(i.insumo?.costo_promedio || 0),
      }))
    } else {
      ingredientes.value = []
    }
    loading.value = false
  }
})

const agregarIngrediente = () => {
  if (!insumoSeleccionado.value || cantInput.value <= 0) return

  ingredientes.value.push({
    insumo_id: insumoSeleccionado.value.id,
    nombre: insumoSeleccionado.value.nombre,
    unidad: insumoSeleccionado.value.unidad_medida,
    cantidad: Number(cantInput.value),
    merma_porcentaje: Number(mermaInput.value || 0),
    costo_unit: Number(insumoSeleccionado.value.costo_promedio || 0),
  })

  insumoSeleccionado.value = null
  cantInput.value = 0.100
  mermaInput.value = 0
}

const eliminarIngrediente = (idx) => {
  ingredientes.value.splice(idx, 1)
}

const costoTeoricoTotal = computed(() => {
  return ingredientes.value.reduce((acc, i) => {
    const cantReal = i.cantidad * (1 + (i.merma_porcentaje / 100))
    return acc + (cantReal * i.costo_unit)
  }, 0)
})

const precioVenta = computed(() => Number(props.producto?.precio || 0))
const margenGanancia = computed(() => {
  if (precioVenta.value <= 0) return 0
  return ((precioVenta.value - costoTeoricoTotal.value) / precioVenta.value) * 100
})

const guardar = async () => {
  await store.guardarReceta(props.producto.id, {
    ingredientes: ingredientes.value.map(i => ({
      insumo_id: i.insumo_id,
      cantidad: i.cantidad,
      unidad_medida: i.unidad,
      merma_porcentaje: i.merma_porcentaje,
    })),
  })
  emit('update:modelValue', false)
  emit('guardado')
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="750px"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <div>
          <span class="text-h6 font-weight-bold">
            <VIcon icon="ri-restaurant-line" class="me-2" />
            Ficha TÃ©cnica & Receta: {{ producto?.nombre }}
          </span>
          <div class="text-caption text-white opacity-80">
            Precio de Carta: Bs. {{ Number(producto?.precio || 0).toFixed(2) }}
          </div>
        </div>
        <VBtn icon="ri-close-line" variant="text" color="white" density="comfortable" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VCardText class="pa-4">
        <!-- Agregar ingrediente -->
        <VCard variant="tonal" color="secondary" class="pa-3 mb-3">
          <div class="text-subtitle-2 font-weight-bold mb-2">Dosificar Ingrediente</div>
          <VRow align="center">
            <VCol cols="12" md="5">
              <VSelect
                v-model="insumoSeleccionado"
                :items="store.insumos"
                item-title="nombre"
                return-object
                label="Insumo / Ingrediente"
                density="compact"
                variant="outlined"
              />
            </VCol>
            <VCol cols="6" md="3">
              <VTextField
                v-model.number="cantInput"
                type="number"
                label="Cantidad"
                density="compact"
                variant="outlined"
                step="0.001"
              />
            </VCol>
            <VCol cols="6" md="2">
              <VTextField
                v-model.number="mermaInput"
                type="number"
                label="Merma %"
                density="compact"
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="2">
              <VBtn block color="primary" @click="agregarIngrediente" :disabled="!insumoSeleccionado">
                <VIcon icon="ri-add-line" />
              </VBtn>
            </VCol>
          </VRow>
        </VCard>

        <!-- Grilla de ingredientes -->
        <VTable density="compact" class="border rounded mb-3">
          <thead>
            <tr>
              <th>Ingrediente</th>
              <th>Unidad</th>
              <th>Cantidad</th>
              <th>Merma %</th>
              <th>Costo TeÃ³rico (Bs)</th>
              <th class="text-center">Quitar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="ingredientes.length === 0">
              <td colspan="6" class="text-center py-4 text-medium-emphasis">
                Este plato no tiene receta configurada. Se venderÃ¡ sin descontar ingredientes.
              </td>
            </tr>
            <tr v-for="(item, idx) in ingredientes" :key="idx">
              <td class="font-weight-medium">{{ item.nombre }}</td>
              <td>{{ item.unidad }}</td>
              <td>{{ item.cantidad }}</td>
              <td>{{ item.merma_porcentaje }}%</td>
              <td class="font-weight-bold">
                Bs. {{ (item.cantidad * (1 + (item.merma_porcentaje / 100)) * item.costo_unit).toFixed(2) }}
              </td>
              <td class="text-center">
                <VBtn icon="ri-delete-bin-line" size="x-small" color="error" variant="text" @click="eliminarIngrediente(idx)" />
              </td>
            </tr>
          </tbody>
        </VTable>

        <!-- Resumen de Costos y Margen -->
        <VCard variant="outlined" class="pa-3">
          <VRow>
            <VCol cols="4" class="text-center border-e">
              <div class="text-caption text-medium-emphasis">Costo de ProducciÃ³n</div>
              <div class="text-h6 font-weight-bold text-error">Bs. {{ costoTeoricoTotal.toFixed(2) }}</div>
            </VCol>
            <VCol cols="4" class="text-center border-e">
              <div class="text-caption text-medium-emphasis">Precio de Venta</div>
              <div class="text-h6 font-weight-bold text-primary">Bs. {{ precioVenta.toFixed(2) }}</div>
            </VCol>
            <VCol cols="4" class="text-center">
              <div class="text-caption text-medium-emphasis">Margen Bruto</div>
              <div class="text-h6 font-weight-bold text-success">{{ margenGanancia.toFixed(1) }}%</div>
            </VCol>
          </VRow>
        </VCard>
      </VCardText>

      <VCardActions class="pa-4 pt-0">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">Cancelar</VBtn>
        <VBtn color="primary" variant="elevated" @click="guardar">
          <VIcon icon="ri-save-line" class="me-1" />
          Guardar Ficha TÃ©cnica
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>