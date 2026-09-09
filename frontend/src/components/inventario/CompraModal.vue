<script setup>
import { ref, computed, watch } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const proveedorId = ref(null)
const almacenId = ref(null)
const nroFactura = ref('')
const metodoPago = ref('CONTADO')
const observaciones = ref('')
const descuento = ref(0)
const ice = ref(0)

const items = ref([])
const insumoSeleccionado = ref(null)
const cantInput = ref(1)
const costoInput = ref(0)

watch(() => props.modelValue, (val) => {
  if (val) {
    proveedorId.value = store.proveedores[0]?.id || null
    almacenId.value = store.almacenes[0]?.id || null
    nroFactura.value = ''
    metodoPago.value = 'CONTADO'
    observaciones.value = ''
    descuento.value = 0
    ice.value = 0
    items.value = []
    insumoSeleccionado.value = null
    cantInput.value = 1
    costoInput.value = 0
  }
})

watch(insumoSeleccionado, (insumo) => {
  if (insumo) {
    costoInput.value = Number(insumo.ultimo_costo || insumo.costo_promedio || 0)
  }
})

const agregarItem = () => {
  if (!insumoSeleccionado.value || cantInput.value <= 0 || costoInput.value < 0) return

  items.value.push({
    insumo_id: insumoSeleccionado.value.id,
    nombre: insumoSeleccionado.value.nombre,
    unidad: insumoSeleccionado.value.unidad_medida,
    cantidad: Number(cantInput.value),
    costo_unitario: Number(costoInput.value),
    subtotal: Number(cantInput.value) * Number(costoInput.value),
  })

  insumoSeleccionado.value = null
  cantInput.value = 1
  costoInput.value = 0
}

const eliminarItem = (index) => {
  items.value.splice(index, 1)
}

const totalBruto = computed(() => items.value.reduce((acc, i) => acc + i.subtotal, 0))
const totalNeto = computed(() => Math.max(0, totalBruto.value - Number(descuento.value || 0) + Number(ice.value || 0)))

const guardarCompra = async () => {
  if (!proveedorId.value || !almacenId.value || items.value.length === 0) return

  await store.registrarCompra({
    proveedor_id: proveedorId.value,
    almacen_id: almacenId.value,
    nro_factura: nroFactura.value,
    metodo_pago: metodoPago.value,
    descuento: Number(descuento.value || 0),
    ice: Number(ice.value || 0),
    observaciones: observaciones.value,
    items: items.value.map(i => ({
      insumo_id: i.insumo_id,
      cantidad: i.cantidad,
      costo_unitario: i.costo_unitario,
    })),
  })

  emit('update:modelValue', false)
  emit('guardado')
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="850px"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <span class="text-h6 font-weight-bold">
          <VIcon icon="ri-shopping-cart-2-line" class="me-2" />
          RecepciÃ³n de MercaderÃ­a & Compra
        </span>
        <VBtn icon="ri-close-line" variant="text" color="white" density="comfortable" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VCardText class="pa-4">
        <VRow>
          <VCol cols="12" md="4">
            <VSelect
              v-model="proveedorId"
              :items="store.proveedores"
              item-title="nombre"
              item-value="id"
              label="Proveedor *"
              density="compact"
              variant="outlined"
            />
          </VCol>

          <VCol cols="12" md="4">
            <VSelect
              v-model="almacenId"
              :items="store.almacenes"
              item-title="nombre"
              item-value="id"
              label="AlmacÃ©n Receptor *"
              density="compact"
              variant="outlined"
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model="nroFactura"
              label="Nro. Factura / Nota"
              placeholder="FAC-00123"
              density="compact"
              variant="outlined"
            />
          </VCol>
        </VRow>

        <!-- Agregar Items -->
        <VCard variant="tonal" color="secondary" class="pa-3 my-3">
          <div class="text-subtitle-2 font-weight-bold mb-2">Agregar Insumos a la Compra</div>
          <VRow align="center">
            <VCol cols="12" md="5">
              <VSelect
                v-model="insumoSeleccionado"
                :items="store.insumos"
                item-title="nombre"
                return-object
                label="Seleccionar Insumo"
                density="compact"
                variant="outlined"
              />
            </VCol>
            <VCol cols="6" md="2">
              <VTextField
                v-model.number="cantInput"
                type="number"
                label="Cantidad"
                density="compact"
                variant="outlined"
                step="0.001"
              />
            </VCol>
            <VCol cols="6" md="3">
              <VTextField
                v-model.number="costoInput"
                type="number"
                label="Costo Unit. (Bs)"
                density="compact"
                variant="outlined"
                step="0.01"
              />
            </VCol>
            <VCol cols="12" md="2">
              <VBtn block color="primary" @click="agregarItem" :disabled="!insumoSeleccionado">
                <VIcon icon="ri-add-line" /> Agregar
              </VBtn>
            </VCol>
          </VRow>
        </VCard>

        <!-- Tabla de Items Agregados -->
        <VTable density="compact" class="border rounded mb-3">
          <thead>
            <tr>
              <th>Insumo</th>
              <th>Unidad</th>
              <th>Cantidad</th>
              <th>Costo Unit. (Bs)</th>
              <th>Subtotal (Bs)</th>
              <th class="text-center">AcciÃ³n</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="6" class="text-center py-4 text-medium-emphasis">
                No hay insumos agregados a esta compra
              </td>
            </tr>
            <tr v-for="(item, idx) in items" :key="idx">
              <td class="font-weight-medium">{{ item.nombre }}</td>
              <td>{{ item.unidad }}</td>
              <td>{{ item.cantidad }}</td>
              <td>{{ item.costo_unitario.toFixed(2) }}</td>
              <td class="font-weight-bold">{{ item.subtotal.toFixed(2) }}</td>
              <td class="text-center">
                <VBtn icon="ri-delete-bin-line" size="x-small" color="error" variant="text" @click="eliminarItem(idx)" />
              </td>
            </tr>
          </tbody>
        </VTable>

        <!-- Totales y Pago -->
        <VRow align="center" class="mt-2">
          <VCol cols="12" md="4">
            <VSelect
              v-model="metodoPago"
              :items="[
                { title: 'Contado (Pagado)', value: 'CONTADO' },
                { title: 'CrÃ©dito (A Deber)', value: 'CREDITO' }
              ]"
              label="CondiciÃ³n de Pago *"
              density="compact"
              variant="outlined"
            />
          </VCol>
          <VCol cols="6" md="4">
            <VTextField
              v-model.number="descuento"
              type="number"
              label="Descuento (Bs)"
              density="compact"
              variant="outlined"
            />
          </VCol>
          <VCol cols="6" md="4" class="text-end">
            <div class="text-caption text-medium-emphasis">Total a Pagar</div>
            <div class="text-h5 font-weight-bold text-primary">Bs. {{ totalNeto.toFixed(2) }}</div>
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="pa-4 pt-0">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">Cancelar</VBtn>
        <VBtn color="primary" variant="elevated" @click="guardarCompra" :disabled="items.length === 0">
          <VIcon icon="ri-check-line" class="me-1" />
          Registrar Compra e Ingresar a Stock
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>