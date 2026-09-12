<script setup>
import { ref, watch, onMounted } from 'vue'
import { useComandaStore } from '@/stores/comanda'
import { useSalonMesaStore } from '@/stores/salonMesa'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  mesa: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'comandaEnviada'])

const comandaStore = useComandaStore()
const salonStore = useSalonMesaStore()

const activeObsProductoId = ref(null)

onMounted(async () => {
  await comandaStore.fetchCategorias()
  await comandaStore.fetchObservacionesCocina()
})

watch(() => props.modelValue, (val) => {
  if (val) {
    comandaStore.limpiarCarrito()
    activeObsProductoId.value = null
  }
})

const handleEnviar = async () => {
  if (!props.mesa) return

  const visitaId = props.visita?.id || props.mesa?.visitaActiva?.id || (typeof props.mesa.id === 'string' && props.mesa.id.startsWith('sin_mesa_') ? parseInt(props.mesa.id.replace('sin_mesa_', '')) : null)

  const res = await comandaStore.enviarComanda(props.mesa.id, visitaId)
  if (res.success) {
    emit('comandaEnviada')
    emit('update:modelValue', false)
    if (!props.mesa.esSinMesa) {
      await salonStore.fetchSalones()
      if (props.mesa.salon_id) {
        await salonStore.fetchMesas(props.mesa.salon_id)
      }
    }
  } else {
    alert(res.message || 'Error al enviar comanda')
  }
}

const toggleObservaciones = (productoId) => {
  activeObsProductoId.value = activeObsProductoId.value === productoId ? null : productoId
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    fullscreen
    transition="dialog-bottom-transition"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="d-flex flex-column h-100 bg-surface">
      <!-- Top Bar Header (frmOrdenesPedido style) -->
      <VToolbar color="primary" density="compact" class="px-2">
        <VToolbarTitle class="text-white font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-restaurant-2-line" />
          <span>TOMA DE PEDIDOS — {{ mesa?.nombre }}</span>
          <VChip size="small" color="white" variant="tonal" class="ms-2 font-weight-bold">
            {{ mesa?.esSinMesa ? `SIN MESA (${mesa?.codigo || 'LLEVAR'})` : 'MESA ACTIVA' }}
          </VChip>
        </VToolbarTitle>

        <VSpacer />

        <div class="d-flex align-center gap-4 text-white text-body-2 d-none d-sm-flex">
          <span>Atiende: <strong>{{ mesa?.mesero_nombre || 'Mesero Salón' }}</strong></span>
          <span>Comensales: <strong>{{ mesa?.personas || 2 }} pers.</strong></span>
        </div>

        <VBtn icon="ri-close-line" color="white" variant="text" @click="emit('update:modelValue', false)" />
      </VToolbar>

      <!-- Main Content (3 Columns Layout) -->
      <div class="d-flex flex-grow-1 overflow-hidden">
        <!-- Columna 1: Categorías Táctiles (Izquierda) -->
        <div class="categories-panel border-e bg-var-theme-background pa-2 d-flex flex-column gap-2 overflow-y-auto">
          <div class="text-caption font-weight-bold text-disabled px-2 mb-1">
            CATEGORÍAS
          </div>

          <VBtn
            v-for="cat in comandaStore.categorias"
            :key="cat.id"
            :color="comandaStore.selectedCategoriaId === cat.id ? 'primary' : 'default'"
            :variant="comandaStore.selectedCategoriaId === cat.id ? 'elevated' : 'tonal'"
            class="justify-start py-3 px-3 h-auto text-none font-weight-bold"
            rounded="lg"
            @click="comandaStore.fetchProductos(cat.id)"
          >
            <VIcon :icon="cat.icono" class="me-2" size="20" />
            <span class="text-truncate flex-grow-1 text-start">{{ cat.nombre }}</span>
            <VChip size="x-small" variant="text" class="font-weight-bold ms-1">
              {{ cat.total_productos }}
            </VChip>
          </VBtn>
        </div>

        <!-- Columna 2: Catálogo de Productos Táctiles (Centro) -->
        <div class="products-panel flex-grow-1 pa-4 overflow-y-auto">
          <!-- Buscador de productos -->
          <div class="mb-4">
            <VTextField
              v-model="comandaStore.busqueda"
              placeholder="Buscar plato o bebida en la carta..."
              prepend-inner-icon="ri-search-line"
              density="compact"
              clearable
              hide-details
            />
          </div>

          <!-- Loading -->
          <div v-if="comandaStore.loading" class="text-center py-10">
            <VProgressCircular indeterminate color="primary" />
            <div class="text-caption text-disabled mt-2">Cargando productos...</div>
          </div>

          <!-- Grid de Productos -->
          <VRow v-else class="match-height">
            <VCol
              v-for="prod in comandaStore.filteredProductos"
              :key="prod.id"
              cols="6"
              sm="4"
              md="4"
              lg="3"
            >
              <VCard
                class="product-card cursor-pointer h-100 pa-3 d-flex flex-column justify-space-between transition-swing"
                variant="outlined"
                elevation="1"
                @click="comandaStore.agregarItem(prod)"
              >
                <div>
                  <div class="d-flex align-center justify-space-between mb-1">
                    <span class="text-caption text-disabled font-weight-bold">{{ prod.codigo }}</span>
                    <VChip
                      size="x-small"
                      :color="prod.destino_impresion === 'BAR' ? 'info' : 'warning'"
                      variant="tonal"
                      class="font-weight-bold"
                    >
                      {{ prod.destino_impresion }}
                    </VChip>
                  </div>

                  <h6 class="text-subtitle-2 font-weight-bold mb-1 line-clamp-2">
                    {{ prod.nombre }}
                  </h6>

                  <p v-if="prod.descripcion" class="text-caption text-medium-emphasis mb-2 line-clamp-2">
                    {{ prod.descripcion }}
                  </p>
                </div>

                <div class="d-flex align-center justify-space-between pt-2 border-t mt-2">
                  <span class="text-primary font-weight-bold text-h6">
                    Bs. {{ parseFloat(prod.precio).toFixed(2) }}
                  </span>
                  <VBtn size="x-small" color="primary" icon="ri-add-line" />
                </div>
              </VCard>
            </VCol>
          </VRow>
        </div>

        <!-- Columna 3: Comanda en Curso & Envío a Cocina (Derecha) -->
        <div class="order-panel border-s pa-3 d-flex flex-column bg-surface">
          <div class="d-flex align-center justify-space-between mb-2">
            <h6 class="text-subtitle-1 font-weight-bold d-flex align-center gap-1">
              <VIcon icon="ri-shopping-cart-2-line" />
              <span>NUEVA COMANDA</span>
            </h6>
            <VChip size="small" color="primary" variant="tonal" class="font-weight-bold">
              {{ comandaStore.totalItemsCarrito }} ítems
            </VChip>
          </div>

          <VDivider class="mb-2" />

          <!-- Lista de Ítems en el Carrito -->
          <div class="flex-grow-1 overflow-y-auto pr-1">
            <div v-if="comandaStore.carrito.length === 0" class="text-center py-12 text-disabled">
              <VIcon icon="ri-restaurant-line" size="40" class="mb-2" />
              <div class="text-body-2 font-weight-medium">Comanda vacía</div>
              <div class="text-caption">Toca los productos del centro para agregarlos.</div>
            </div>

            <div
              v-for="item in comandaStore.carrito"
              :key="item.producto_id"
              class="cart-item pa-2 rounded border mb-2"
            >
              <div class="d-flex align-center justify-space-between mb-1">
                <span class="font-weight-bold text-body-2 text-truncate flex-grow-1">
                  {{ item.producto_nombre }}
                </span>
                <span class="font-weight-bold text-primary ms-2">
                  Bs. {{ item.subtotal.toFixed(2) }}
                </span>
              </div>

              <!-- Controles de Cantidad -->
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center gap-1">
                  <VBtn
                    size="x-small"
                    variant="tonal"
                    color="secondary"
                    icon="ri-subtract-line"
                    @click="comandaStore.decrementarCantidad(item.producto_id)"
                  />
                  <span class="px-2 font-weight-bold text-body-2">{{ item.cantidad }}</span>
                  <VBtn
                    size="x-small"
                    variant="tonal"
                    color="secondary"
                    icon="ri-add-line"
                    @click="comandaStore.incrementarCantidad(item.producto_id)"
                  />
                </div>

                <div class="d-flex align-center gap-1">
                  <!-- Botón Notas de Cocina -->
                  <VBtn
                    size="x-small"
                    variant="text"
                    color="warning"
                    icon="ri-chat-1-line"
                    @click="toggleObservaciones(item.producto_id)"
                  />
                  <!-- Botón Eliminar -->
                  <VBtn
                    size="x-small"
                    variant="text"
                    color="error"
                    icon="ri-delete-bin-line"
                    @click="comandaStore.removerItem(item.producto_id)"
                  />
                </div>
              </div>

              <!-- Observación de Cocina actual -->
              <div v-if="item.observaciones" class="text-caption text-warning mt-1 font-italic">
                Nota: {{ item.observaciones }}
              </div>

              <!-- Chips Rápidos de Observación desplegables -->
              <div v-if="activeObsProductoId === item.producto_id" class="mt-2 pt-2 border-t">
                <div class="text-caption font-weight-bold text-disabled mb-1">NOTAS RÁPIDAS:</div>
                <div class="d-flex flex-wrap gap-1">
                  <VChip
                    v-for="obs in comandaStore.observacionesCocina"
                    :key="obs.id"
                    size="x-small"
                    variant="outlined"
                    class="cursor-pointer"
                    @click="comandaStore.setObservacion(item.producto_id, obs.descripcion)"
                  >
                    {{ obs.descripcion }}
                  </VChip>
                </div>
              </div>
            </div>
          </div>

          <!-- Total y Botón de Enviar a Cocina -->
          <div class="border-t pt-3 mt-2">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-subtitle-1 font-weight-bold">TOTAL PEDIDO:</span>
              <span class="text-h5 font-weight-bold text-primary">
                Bs. {{ comandaStore.totalMontoCarrito.toFixed(2) }}
              </span>
            </div>

            <VBtn
              block
              color="success"
              size="large"
              prepend-icon="ri-send-plane-fill"
              class="font-weight-bold"
              :disabled="comandaStore.carrito.length === 0"
              :loading="comandaStore.loading"
              @click="handleEnviar"
            >
              ENVIAR A COCINA / BAR
            </VBtn>
          </div>
        </div>
      </div>
    </VCard>
  </VDialog>
</template>

<style scoped>
.categories-panel {
  width: 220px;
  flex-shrink: 0;
}

.order-panel {
  width: 360px;
  flex-shrink: 0;
}

.product-card {
  border-radius: 10px;
  transition: all 0.2s ease;
}
.product-card:hover {
  transform: translateY(-3px);
  border-color: rgb(var(--v-theme-primary));
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>