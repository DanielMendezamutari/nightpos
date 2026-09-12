<script setup>
import { ref, watch, onMounted } from 'vue'
import { useComandaStore } from '@/stores/comanda'

const props = defineProps({
  modelValue: Boolean,
  salones: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue', 'abrirComanda', 'abrirCobro', 'pedidoAsignadoAMesa'])

const comandaStore = useComandaStore()

const tabActiva = ref('activos')
const pedidosActivos = ref([])
const cargando = ref(false)

// Formulario Nuevo Pedido
const nuevoPedido = ref({
  tipo_despacho: 'LLEVAR',
  cliente_nombre: '',
  telefono_cliente: '',
  direccion_envio: '',
  notas: '',
})

// Modal secundario: Asignar a Mesa Física
const dialogAsignarMesa = ref(false)
const pedidoParaAsignar = ref(null)
const mesaSeleccionada = ref(null)
const cargandoAsignar = ref(false)

watch(() => props.modelValue, (val) => {
  if (val) {
    cargarPedidos()
  }
})

async function cargarPedidos() {
  cargando.value = true
  try {
    pedidosActivos.value = await comandaStore.fetchPedidosSinMesa()
  } catch (err) {
    console.error('Error cargando pedidos sin mesa:', err)
  } finally {
    cargando.value = false
  }
}

async function crearPedido() {
  if (!nuevoPedido.value.cliente_nombre) {
    alert('Por favor ingrese el nombre del cliente.')
    return
  }

  cargando.value = true
  try {
    const res = await comandaStore.crearPedidoSinMesa({
      tipo_despacho: nuevoPedido.value.tipo_despacho,
      cliente_nombre: nuevoPedido.value.cliente_nombre,
      telefono_cliente: nuevoPedido.value.telefono_cliente || null,
      direccion_envio: nuevoPedido.value.direccion_envio || null,
      notas: nuevoPedido.value.notas || null,
    })

    // Reset form
    nuevoPedido.value = {
      tipo_despacho: 'LLEVAR',
      cliente_nombre: '',
      telefono_cliente: '',
      direccion_envio: '',
      notas: '',
    }

    await cargarPedidos()
    tabActiva.value = 'activos'

    // Abrir comanda directamente para el nuevo pedido
    if (res.visita) {
      emit('abrirComanda', {
        id: `sin_mesa_${res.visita.id}`,
        codigo: res.visita.tipo_despacho,
        nombre: res.visita.cliente_nombre,
        visitaActiva: res.visita,
        visita_id: res.visita.id,
        esSinMesa: true,
      })
      emit('update:modelValue', false)
    }
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Error al crear pedido.')
  } finally {
    cargando.value = false
  }
}

function abrirComandaPedido(pedido) {
  emit('abrirComanda', {
    id: `sin_mesa_${pedido.id}`,
    codigo: pedido.tipo_despacho,
    nombre: pedido.cliente_nombre,
    visitaActiva: pedido,
    visita_id: pedido.id,
    esSinMesa: true,
  })
  emit('update:modelValue', false)
}

function abrirCobroPedido(pedido) {
  emit('abrirCobro', {
    id: `sin_mesa_${pedido.id}`,
    codigo: pedido.tipo_despacho,
    nombre: pedido.cliente_nombre,
    visitaActiva: pedido,
    visita_id: pedido.id,
    esSinMesa: true,
  })
  emit('update:modelValue', false)
}

function iniciarAsignarMesa(pedido) {
  pedidoParaAsignar.value = pedido
  mesaSeleccionada.value = null
  dialogAsignarMesa.value = true
}

// Obtener todas las mesas libres de todos los salones
function getMesasLibres() {
  if (!props.salones) return []
  const libres = []
  props.salones.forEach(s => {
    (s.mesas || []).forEach(m => {
      if (!m.visitaActiva) {
        libres.push({ ...m, salon_nombre: s.nombre })
      }
    })
  })
  return libres
}

async function confirmarAsignarMesa() {
  if (!pedidoParaAsignar.value || !mesaSeleccionada.value) return

  cargandoAsignar.value = true
  try {
    const res = await comandaStore.asignarMesaAPedidoSinMesa(
      pedidoParaAsignar.value.id,
      mesaSeleccionada.value.id
    )
    dialogAsignarMesa.value = false
    await cargarPedidos()
    emit('pedidoAsignadoAMesa', res)
    emit('update:modelValue', false)
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Error al asignar mesa.')
  } finally {
    cargandoAsignar.value = false
  }
}

function getColorBadge(minutos) {
  if (minutos < 15) return 'success'
  if (minutos < 30) return 'warning'
  return 'error'
}

function getIconDespacho(tipo) {
  switch (tipo) {
    case 'DELIVERY': return 'ri-riding-line'
    case 'LLEVAR': return 'ri-shopping-bag-3-line'
    case 'BARRA': return 'ri-goblet-line'
    default: return 'ri-store-line'
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="950"
    persistent
    @update:model-value="val => emit('update:modelValue', val)"
  >
    <VCard class="pa-2">
      <VCardTitle class="d-flex justify-space-between align-center pb-2">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-takeaway-line" color="warning" size="26" />
          <span class="text-h6 font-weight-bold">Pedidos Sin Mesa / Para Llevar / Mostrador (F9)</span>
        </div>
        <VBtn icon="ri-close-line" variant="text" density="compact" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VTabs v-model="tabActiva" color="primary" class="px-2">
        <VTab value="activos">
          <VIcon icon="ri-list-check-2" start />
          Pedidos Activos ({{ pedidosActivos.length }})
        </VTab>
        <VTab value="nuevo">
          <VIcon icon="ri-add-circle-line" start />
          + Nuevo Pedido Rápido
        </VTab>
      </VTabs>

      <VDivider />

      <VCardText class="pt-4" style="min-height: 380px;">
        <!-- Pestaña 1: Pedidos Activos -->
        <div v-if="tabActiva === 'activos'">
          <div v-if="cargando" class="text-center py-8">
            <VProgressCircular indeterminate color="primary" />
            <div class="mt-2 text-caption">Cargando pedidos sin mesa...</div>
          </div>

          <div v-else-if="pedidosActivos.length === 0" class="text-center py-8 text-medium-emphasis">
            <VIcon icon="ri-inbox-line" size="48" class="mb-2 opacity-50" />
            <div class="text-subtitle-1 font-weight-bold">No hay pedidos sin mesa activos</div>
            <div class="text-caption mb-4">Los pedidos para llevar, mostrador o delivery aparecerán aquí.</div>
            <VBtn color="primary" variant="tonal" size="small" @click="tabActiva = 'nuevo'">
              Crear Nuevo Pedido
            </VBtn>
          </div>

          <div v-else style="max-height: 420px; overflow-y: auto;">
            <VRow dense>
              <VCol
                v-for="pedido in pedidosActivos"
                :key="pedido.id"
                cols="12"
                sm="6"
                md="4"
              >
                <VCard variant="outlined" class="pa-3 d-flex flex-column justify-space-between h-100">
                  <div>
                    <!-- Header Tarjeta -->
                    <div class="d-flex justify-space-between align-center mb-2">
                      <VChip
                        size="small"
                        :color="pedido.tipo_despacho === 'DELIVERY' ? 'info' : (pedido.tipo_despacho === 'LLEVAR' ? 'warning' : 'primary')"
                        variant="flat"
                      >
                        <VIcon :icon="getIconDespacho(pedido.tipo_despacho)" start size="14" />
                        {{ pedido.tipo_despacho }}
                      </VChip>
                      <VChip
                        size="x-small"
                        :color="getColorBadge(pedido.minutos_transcurridos)"
                        variant="tonal"
                      >
                        <VIcon icon="ri-time-line" start size="12" />
                        {{ pedido.minutos_transcurridos }} min
                      </VChip>
                    </div>

                    <!-- Cliente y Teléfono -->
                    <div class="text-subtitle-1 font-weight-black mb-1 text-truncate">
                      {{ pedido.cliente_nombre }}
                    </div>
                    <div v-if="pedido.telefono_cliente" class="text-caption text-medium-emphasis mb-1">
                      <VIcon icon="ri-phone-line" size="12" class="me-1" />
                      {{ pedido.telefono_cliente }}
                    </div>
                    <div v-if="pedido.direccion_envio" class="text-caption text-medium-emphasis mb-2 text-truncate">
                      <VIcon icon="ri-map-pin-line" size="12" class="me-1" />
                      {{ pedido.direccion_envio }}
                    </div>

                    <!-- Consumo Total -->
                    <div class="d-flex justify-space-between align-center my-2 pa-2 rounded bg-grey-lighten-4">
                      <span class="text-caption font-weight-medium">
                        {{ pedido.cant_items }} {{ pedido.cant_items === 1 ? 'ítem' : 'ítems' }}
                      </span>
                      <span class="text-h6 font-weight-black text-primary">
                        Bs. {{ Number(pedido.total).toFixed(2) }}
                      </span>
                    </div>
                  </div>

                  <!-- Botones de Acción -->
                  <div class="d-flex gap-1 mt-3 flex-wrap">
                    <VBtn
                      size="small"
                      color="primary"
                      variant="outlined"
                      class="flex-grow-1"
                      @click="abrirComandaPedido(pedido)"
                    >
                      <VIcon icon="ri-restaurant-line" start size="14" />
                      Comanda
                    </VBtn>
                    <VBtn
                      size="small"
                      color="success"
                      variant="elevated"
                      class="flex-grow-1"
                      @click="abrirCobroPedido(pedido)"
                    >
                      <VIcon icon="ri-money-dollar-circle-line" start size="14" />
                      Cobrar
                    </VBtn>
                    <VBtn
                      size="small"
                      color="secondary"
                      variant="tonal"
                      icon="ri-arrow-right-circle-line"
                      title="Pasar a Mesa Física"
                      @click="iniciarAsignarMesa(pedido)"
                    />
                  </div>
                </VCard>
              </VCol>
            </VRow>
          </div>
        </div>

        <!-- Pestaña 2: Nuevo Pedido -->
        <div v-else-if="tabActiva === 'nuevo'" class="pa-2">
          <VRow>
            <VCol cols="12">
              <label class="text-caption font-weight-bold mb-1 d-block">Tipo de Despacho:</label>
              <div class="d-flex gap-2 flex-wrap">
                <VBtn
                  :variant="nuevoPedido.tipo_despacho === 'LLEVAR' ? 'elevated' : 'outlined'"
                  :color="nuevoPedido.tipo_despacho === 'LLEVAR' ? 'warning' : 'secondary'"
                  @click="nuevoPedido.tipo_despacho = 'LLEVAR'"
                >
                  <VIcon icon="ri-shopping-bag-3-line" start /> Para Llevar (Takeout)
                </VBtn>
                <VBtn
                  :variant="nuevoPedido.tipo_despacho === 'MOSTRADOR' ? 'elevated' : 'outlined'"
                  :color="nuevoPedido.tipo_despacho === 'MOSTRADOR' ? 'primary' : 'secondary'"
                  @click="nuevoPedido.tipo_despacho = 'MOSTRADOR'"
                >
                  <VIcon icon="ri-store-line" start /> Mostrador
                </VBtn>
                <VBtn
                  :variant="nuevoPedido.tipo_despacho === 'BARRA' ? 'elevated' : 'outlined'"
                  :color="nuevoPedido.tipo_despacho === 'BARRA' ? 'purple' : 'secondary'"
                  @click="nuevoPedido.tipo_despacho = 'BARRA'"
                >
                  <VIcon icon="ri-goblet-line" start /> Barra
                </VBtn>
                <VBtn
                  :variant="nuevoPedido.tipo_despacho === 'DELIVERY' ? 'elevated' : 'outlined'"
                  :color="nuevoPedido.tipo_despacho === 'DELIVERY' ? 'info' : 'secondary'"
                  @click="nuevoPedido.tipo_despacho = 'DELIVERY'"
                >
                  <VIcon icon="ri-riding-line" start /> Delivery
                </VBtn>
              </div>
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="nuevoPedido.cliente_nombre"
                label="Para quién es el pedido (Nombre Cliente) *"
                placeholder="Ej: Roberto Gomez"
                variant="outlined"
                density="comfortable"
                autofocus
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="nuevoPedido.telefono_cliente"
                label="Teléfono / Celular (De dónde llamaron)"
                placeholder="Ej: 71234567"
                variant="outlined"
                density="comfortable"
              />
            </VCol>

            <VCol v-if="nuevoPedido.tipo_despacho === 'DELIVERY'" cols="12">
              <VTextarea
                v-model="nuevoPedido.direccion_envio"
                label="Dirección de Entrega"
                placeholder="Calle, número, zona, referencias"
                rows="2"
                variant="outlined"
                density="comfortable"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="nuevoPedido.notas"
                label="Observaciones Generales"
                placeholder="Ej: Enviar cubiertos / Sin sal"
                variant="outlined"
                density="comfortable"
              />
            </VCol>
          </VRow>

          <div class="d-flex justify-end gap-2 mt-4">
            <VBtn variant="outlined" color="secondary" @click="tabActiva = 'activos'">
              Cancelar
            </VBtn>
            <VBtn
              color="primary"
              variant="elevated"
              :loading="cargando"
              @click="crearPedido"
            >
              <VIcon icon="ri-check-line" start />
              Crear e Iniciar Comanda
            </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>
  </VDialog>

  <!-- Sub-modal para Asignar Pedido a Mesa Física (PonerCodigoEnMesa) -->
  <VDialog v-model="dialogAsignarMesa" max-width="500">
    <VCard class="pa-3">
      <VCardTitle class="d-flex align-center gap-2 text-h6 font-weight-bold">
        <VIcon icon="ri-layout-grid-line" color="primary" />
        Pasar Pedido a Mesa Física
      </VCardTitle>
      <VCardText>
        <p class="text-caption mb-3">
          Seleccione la mesa libre a la cual ubicar el pedido de <strong>{{ pedidoParaAsignar?.cliente_nombre }}</strong>:
        </p>

        <div style="max-height: 220px; overflow-y: auto;">
          <VList density="compact">
            <VListItem
              v-for="mesa in getMesasLibres()"
              :key="mesa.id"
              :class="{ 'bg-primary-lighten-5': mesaSeleccionada?.id === mesa.id }"
              class="rounded mb-1 cursor-pointer border"
              @click="mesaSeleccionada = mesa"
            >
              <template #prepend>
                <VIcon icon="ri-table-line" color="success" />
              </template>
              <VListItemTitle class="font-weight-bold">
                Mesa {{ mesa.codigo }} - {{ mesa.nombre }}
              </VListItemTitle>
              <VListItemSubtitle>
                {{ mesa.salon_nombre }} (Cap: {{ mesa.capacidad }} personas)
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </div>
      </VCardText>
      <VCardActions class="pt-0">
        <VSpacer />
        <VBtn variant="text" color="secondary" @click="dialogAsignarMesa = false">Cancelar</VBtn>
        <VBtn
          color="primary"
          variant="elevated"
          :disabled="!mesaSeleccionada"
          :loading="cargandoAsignar"
          @click="confirmarAsignarMesa"
        >
          Asignar a Mesa {{ mesaSeleccionada?.codigo }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
