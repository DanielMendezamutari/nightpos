<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useSalonMesaStore } from '@/stores/salonMesa'
import { useAuthStore } from '@/stores/auth'
import { useComandaStore } from '@/stores/comanda'
import { useCajaStore } from '@/stores/caja'
import ComandaModal from '@/components/pos/ComandaModal.vue'
import CobroFacturacionModal from '@/components/pos/CobroFacturacionModal.vue'
import ControlCajaModal from '@/components/pos/ControlCajaModal.vue'

const salonStore = useSalonMesaStore()
const authStore = useAuthStore()
const comandaStore = useComandaStore()
const cajaStore = useCajaStore()

// State
const openTableDialog = ref(false)
const changeTableDialog = ref(false)
const tableDetailDialog = ref(false)
const comandaModalOpen = ref(false)
const cobroModalOpen = ref(false)
const controlCajaModalOpen = ref(false)
const targetMesa = ref(null)

// Form data for opening table
const openTableForm = ref({
  personas: 2,
  cliente_nombre: '',
  notas: '',
})

// Form data for changing table
const destinationMesaId = ref(null)

// Hotkeys handler (RestoTech Keyboard Accelerators)
const handleGlobalKeydown = (e) => {
  if (e.key === 'F9') {
    e.preventDefault()
    controlCajaModalOpen.value = true
  } else if (e.key === 'F12' && tableDetailDialog.value && targetMesa.value) {
    e.preventDefault()
    abrirCobroModal()
  }
}

// Load salones and active shift on mounted
onMounted(async () => {
  await salonStore.fetchSalones()
  await cajaStore.fetchTurnoActivo()
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})

// Quick open table dialog
const handleMesaClick = async (mesa) => {
  targetMesa.value = mesa

  if (mesa.estado === 'LIBRE') {
    openTableForm.value = {
      personas: mesa.capacidad || 2,
      cliente_nombre: '',
      notas: '',
    }
    openTableDialog.value = true
  } else {
    // Cargar detalle y abrir modal de detalle
    await salonStore.fetchMesaDetails(mesa.id)
    tableDetailDialog.value = true
  }
}

// Confirm open table
const submitOpenTable = async () => {
  if (!targetMesa.value) return

  const meseroId = authStore.user?.id

  const res = await salonStore.abrirMesa(targetMesa.value.id, {
    mesero_id: meseroId,
    personas: openTableForm.value.personas,
    cliente_nombre: openTableForm.value.cliente_nombre || 'Cliente Ocasional',
    notas: openTableForm.value.notas,
  })

  if (res.success) {
    openTableDialog.value = false
    // Abrir automÃ¡ticamente la toma de pedidos para la mesa reciÃ©n abierta
    const updatedMesa = salonStore.mesas.find(m => m.id === targetMesa.value.id)
    targetMesa.value = updatedMesa || targetMesa.value
    comandaModalOpen.value = true
  } else {
    alert(res.message || 'Error al abrir la mesa')
  }
}

// Open change table modal
const openChangeModal = () => {
  destinationMesaId.value = null
  changeTableDialog.value = true
}

// Confirm change table
const submitChangeTable = async () => {
  if (!targetMesa.value || !destinationMesaId.value) return

  const res = await salonStore.cambiarMesa(targetMesa.value.id, destinationMesaId.value)
  if (res.success) {
    changeTableDialog.value = false
    tableDetailDialog.value = false
    targetMesa.value = null
  } else {
    alert(res.message || 'Error al mover la mesa')
  }
}

// Request bill / precuenta
const handleSolicitarPrecuenta = async () => {
  if (!targetMesa.value) return
  await salonStore.solicitarPrecuenta(targetMesa.value.id)
}

// Open Cobro / Facturacion Modal
const abrirCobroModal = () => {
  if (!cajaStore.isTurnoAbierto) {
    alert('Debe abrir un turno de caja antes de realizar cobros y facturaciÃ³n.')
    controlCajaModalOpen.value = true
    return
  }
  tableDetailDialog.value = false
  cobroModalOpen.value = true
}

// On Cobro Exitoso
const onCobroExitoso = async () => {
  targetMesa.value = null
  await salonStore.fetchMesas(salonStore.activeSalonId)
}

// Delete existing item from order
const handleEliminarItem = async (detalleId) => {
  if (confirm('Â¿Eliminar este Ã­tem de la comanda?')) {
    const ok = await comandaStore.eliminarItemComandaExistente(detalleId)
    if (ok && targetMesa.value) {
      await salonStore.fetchMesaDetails(targetMesa.value.id)
      await salonStore.fetchMesas(salonStore.activeSalonId)
    }
  }
}

// On comanda sent
const onComandaEnviada = async () => {
  if (targetMesa.value) {
    await salonStore.fetchMesaDetails(targetMesa.value.id)
  }
}

// Available free tables for moving
const freeTablesForMove = computed(() => {
  return salonStore.mesas.filter(m => m.estado === 'LIBRE' && m.id !== targetMesa.value?.id)
})
</script>

<template>
  <div class="pos-tables-container">
    <!-- Top Bar: Salon Navigation & Quick Metrics -->
    <VCard class="mb-4 elevation-1">
      <VCardText class="py-3 px-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-3">
          <!-- Salones Tabs -->
          <div class="d-flex align-center gap-2 flex-wrap">
            <VTabs
              :model-value="salonStore.activeSalonId"
              class="v-tabs-pill"
              @update:model-value="(val) => salonStore.fetchMesas(val)"
            >
              <VTab
                v-for="salon in salonStore.salones"
                :key="salon.id"
                :value="salon.id"
                class="font-weight-bold"
              >
                <VIcon icon="ri-layout-grid-line" class="me-2" size="18" />
                {{ salon.nombre }}
                <VChip
                  size="x-small"
                  :color="salon.mesas_ocupadas > 0 ? 'error' : 'secondary'"
                  variant="tonal"
                  class="ms-2 font-weight-bold"
                >
                  {{ salon.mesas_ocupadas }}/{{ salon.total_mesas }}
                </VChip>
              </VTab>
            </VTabs>
          </div>

          <!-- Top Actions: Control de Caja (F9) & Refresh -->
          <div class="d-flex align-center gap-2">
            <VBtn
              :color="cajaStore.isTurnoAbierto ? 'success' : 'warning'"
              :variant="cajaStore.isTurnoAbierto ? 'tonal' : 'flat'"
              size="small"
              prepend-icon="ri-safe-2-line"
              class="font-weight-bold"
              @click="controlCajaModalOpen = true"
            >
              <span v-if="cajaStore.isTurnoAbierto">
                Turno #{{ cajaStore.turnoActivo?.id }} (Bs. {{ parseFloat(cajaStore.turnoActivo?.efectivo_esperado || 0).toFixed(2) }}) [F9]
              </span>
              <span v-else>
                Caja Cerrada - Abrir Turno [F9]
              </span>
            </VBtn>

            <VBtn
              icon="ri-refresh-line"
              size="small"
              variant="tonal"
              color="primary"
              :loading="salonStore.loading"
              @click="salonStore.fetchSalones()"
            />
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Operational Filter & Metrics Bar (RestoTech Style) -->
    <VCard class="mb-4 elevation-1">
      <VCardText class="py-2 px-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-3">
          <!-- Status Filter Chips -->
          <div class="d-flex align-center gap-2 flex-wrap">
            <span class="text-caption font-weight-bold text-medium-emphasis me-1">ESTADO:</span>
            
            <VChip
              :color="salonStore.filterStatus === 'TODAS' ? 'primary' : 'default'"
              :variant="salonStore.filterStatus === 'TODAS' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-medium"
              @click="salonStore.filterStatus = 'TODAS'"
            >
              Todas ({{ salonStore.totalesResumen.total }})
            </VChip>

            <VChip
              color="success"
              :variant="salonStore.filterStatus === 'LIBRE' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-bold"
              @click="salonStore.filterStatus = 'LIBRE'"
            >
              <VIcon icon="ri-checkbox-circle-fill" size="14" class="me-1" />
              Libres ({{ salonStore.totalesResumen.libres }})
            </VChip>

            <VChip
              color="error"
              :variant="salonStore.filterStatus === 'OCUPADA' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-bold"
              @click="salonStore.filterStatus = 'OCUPADA'"
            >
              <VIcon icon="ri-fire-fill" size="14" class="me-1" />
              Ocupadas ({{ salonStore.totalesResumen.ocupadas }})
            </VChip>

            <VChip
              color="warning"
              :variant="salonStore.filterStatus === 'PRECUENTA' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-bold"
              @click="salonStore.filterStatus = 'PRECUENTA'"
            >
              <VIcon icon="ri-file-list-3-fill" size="14" class="me-1" />
              Pre-cuenta ({{ salonStore.totalesResumen.precuenta }})
            </VChip>
          </div>

          <!-- Total Consumo SalÃ³n & Quick Search -->
          <div class="d-flex align-center gap-3">
            <div class="d-flex align-center gap-2 bg-var-theme-background px-3 py-1 rounded">
              <span class="text-caption text-medium-emphasis">Consumo SalÃ³n:</span>
              <strong class="text-primary text-body-1 font-weight-bold">
                Bs. {{ salonStore.totalesResumen.consumoTotal.toFixed(2) }}
              </strong>
            </div>

            <VTextField
              v-model="salonStore.searchQuery"
              placeholder="Buscar mesa..."
              prepend-inner-icon="ri-search-line"
              density="compact"
              hide-details
              style="width: 160px;"
            />
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Interactive Table Grid (Visual Salon Map) -->
    <VRow class="match-height">
      <VCol
        v-for="mesa in salonStore.filteredMesas"
        :key="mesa.id"
        cols="6"
        sm="4"
        md="3"
        lg="2"
      >
        <VCard
          class="mesa-card cursor-pointer transition-swing"
          :class="[`mesa-${mesa.estado.toLowerCase()}`]"
          elevation="2"
          @click="handleMesaClick(mesa)"
        >
          <!-- Top Badge & Table Number -->
          <div class="d-flex align-center justify-space-between pa-3 pb-1">
            <span class="text-h6 font-weight-bold mesa-title">
              {{ mesa.codigo }}
            </span>
            <VChip
              :color="mesa.estado_color"
              size="x-small"
              variant="elevated"
              class="font-weight-bold text-uppercase"
            >
              {{ mesa.estado_label }}
            </VChip>
          </div>

          <!-- Card Body -->
          <VCardText class="pa-3 pt-1 text-center">
            <!-- Icon by Shape -->
            <VAvatar
              :color="mesa.estado_color"
              variant="tonal"
              size="46"
              class="my-2"
            >
              <VIcon
                :icon="mesa.forma === 'redonda' ? 'ri-record-circle-line' : 'ri-layout-grid-line'"
                size="26"
              />
            </VAvatar>

            <!-- Status Details -->
            <template v-if="mesa.estado === 'LIBRE'">
              <div class="text-caption text-disabled mb-1">
                Capacidad: {{ mesa.capacidad }} pers.
              </div>
              <div class="text-caption font-weight-medium text-success">
                Tocar para Abrir
              </div>
            </template>

            <template v-else>
              <div class="text-caption text-truncate font-weight-medium mb-1">
                {{ mesa.mesero_nombre || 'Mesero' }}
              </div>

              <!-- Total Consumo -->
              <div class="text-h6 font-weight-bold text-high-emphasis">
                Bs. {{ (parseFloat(mesa.total_consumo) || 0).toFixed(2) }}
              </div>

              <!-- Time elapsed -->
              <div class="text-caption text-disabled mt-1 d-flex align-center justify-center gap-1">
                <VIcon icon="ri-time-line" size="13" />
                <span>{{ mesa.minutos_abierta }} min</span>
                <span class="mx-1">â€¢</span>
                <VIcon icon="ri-user-line" size="13" />
                <span>{{ mesa.personas }}p</span>
              </div>
            </template>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Empty State -->
    <VCard v-if="salonStore.filteredMesas.length === 0" class="pa-8 text-center mt-4">
      <VIcon icon="ri-restaurant-line" size="48" color="disabled" class="mb-2" />
      <h5 class="text-h5 text-medium-emphasis">No se encontraron mesas</h5>
      <p class="text-caption text-disabled mb-0">Prueba cambiando el filtro de estado o la bÃºsqueda.</p>
    </VCard>

    <!-- DIALOG: Abrir Mesa -->
    <VDialog
      v-model="openTableDialog"
      max-width="450"
    >
      <VCard v-if="targetMesa">
        <VCardItem class="bg-primary text-white">
          <template #prepend>
            <VAvatar color="white" size="36">
              <VIcon icon="ri-restaurant-2-line" color="primary" size="20" />
            </VAvatar>
          </template>
          <VCardTitle class="text-white font-weight-bold">
            Abrir {{ targetMesa.nombre }}
          </VCardTitle>
          <VCardSubtitle class="text-white opacity-80">
            Capacidad: {{ targetMesa.capacidad }} comensales
          </VCardSubtitle>
        </VCardItem>

        <VCardText class="pt-5">
          <!-- Personas selector -->
          <div class="mb-4">
            <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-2">
              CANTIDAD DE COMENSALES:
            </label>
            <div class="d-flex gap-2 flex-wrap mb-2">
              <VBtn
                v-for="p in [1, 2, 3, 4, 5, 6, 8]"
                :key="p"
                size="small"
                :variant="openTableForm.personas === p ? 'elevated' : 'outlined'"
                :color="openTableForm.personas === p ? 'primary' : 'default'"
                class="font-weight-bold"
                @click="openTableForm.personas = p"
              >
                {{ p }}
              </VBtn>
            </div>
          </div>

          <!-- Cliente nombre -->
          <VTextField
            v-model="openTableForm.cliente_nombre"
            label="Nombre del Cliente (Opcional)"
            placeholder="Ej. Familia PÃ©rez"
            prepend-inner-icon="ri-user-smile-line"
            class="mb-3"
          />

          <!-- Notas -->
          <VTextField
            v-model="openTableForm.notas"
            label="Notas de Mesa (Opcional)"
            placeholder="Ej. Mesa preferencial con niÃ±os"
            prepend-inner-icon="ri-sticky-note-line"
          />
        </VCardText>

        <VCardActions class="pa-4 pt-0 d-flex justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="openTableDialog = false">
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            prepend-icon="ri-check-line"
            :loading="salonStore.loading"
            @click="submitOpenTable"
          >
            Abrir & Tomar Pedido
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- DIALOG: Detalle de Mesa y Comanda Activa (RestoTech Style) -->
    <VDialog
      v-model="tableDetailDialog"
      max-width="650"
    >
      <VCard v-if="salonStore.selectedMesaDetails">
        <VCardItem class="bg-primary text-white">
          <template #prepend>
            <VAvatar color="white" size="40">
              <VIcon icon="ri-receipt-line" color="primary" size="22" />
            </VAvatar>
          </template>
          <VCardTitle class="text-white font-weight-bold d-flex align-center justify-space-between">
            <span>{{ salonStore.selectedMesaDetails.nombre }} ({{ salonStore.selectedMesaDetails.salon_nombre }})</span>
            <VChip
              :color="salonStore.selectedMesaDetails.estado === 'PRECUENTA' ? 'warning' : 'success'"
              size="small"
              class="font-weight-bold"
            >
              {{ salonStore.selectedMesaDetails.estado }}
            </VChip>
          </VCardTitle>
          <VCardSubtitle class="text-white opacity-80">
            Atiende: {{ salonStore.selectedMesaDetails.visita?.mesero_nombre }} â€¢ {{ salonStore.selectedMesaDetails.visita?.personas }} personas
          </VCardSubtitle>
        </VCardItem>

        <VCardText class="pa-4">
          <!-- Detalle de Consumos -->
          <div class="d-flex align-center justify-space-between mb-2">
            <h6 class="text-subtitle-1 font-weight-bold">ÃTEMS DE LA COMANDA</h6>
            <VBtn
              color="primary"
              size="small"
              variant="elevated"
              prepend-icon="ri-add-circle-fill"
              class="font-weight-bold"
              @click="comandaModalOpen = true"
            >
              + Cargar Pedido
            </VBtn>
          </div>

          <VTable density="compact" class="border rounded mb-4">
            <thead>
              <tr>
                <th class="font-weight-bold">CANT</th>
                <th class="font-weight-bold">PRODUCTO</th>
                <th class="text-end font-weight-bold">P. UNIT</th>
                <th class="text-end font-weight-bold">SUBTOTAL</th>
                <th class="text-center font-weight-bold" style="width: 50px;">ACCIÃ“N</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in salonStore.selectedMesaDetails.visita?.detalles"
                :key="item.id"
              >
                <td class="font-weight-bold">{{ item.cantidad }}x</td>
                <td>
                  <div class="font-weight-medium">{{ item.producto_nombre }}</div>
                  <div v-if="item.observaciones" class="text-caption text-warning">
                    Nota: {{ item.observaciones }}
                  </div>
                </td>
                <td class="text-end">Bs. {{ item.precio_unitario.toFixed(2) }}</td>
                <td class="text-end font-weight-bold">Bs. {{ item.subtotal.toFixed(2) }}</td>
                <td class="text-center">
                  <VBtn
                    size="x-small"
                    variant="text"
                    color="error"
                    icon="ri-delete-bin-line"
                    @click="handleEliminarItem(item.id)"
                  />
                </td>
              </tr>
            </tbody>
          </VTable>

          <!-- Total Consumo -->
          <div class="d-flex justify-end mb-4">
            <div class="text-h6 font-weight-bold">
              Total Acumulado: <span class="text-primary">Bs. {{ salonStore.selectedMesaDetails.visita?.total?.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Acciones TÃ¡ctiles de Mesa (RestoTech Faithful) -->
          <div class="d-flex gap-2 flex-wrap">
            <VBtn
              color="warning"
              variant="tonal"
              prepend-icon="ri-printer-line"
              class="flex-grow-1"
              @click="handleSolicitarPrecuenta"
            >
              Pre-cuenta
            </VBtn>

            <VBtn
              color="info"
              variant="tonal"
              prepend-icon="ri-arrow-left-right-line"
              class="flex-grow-1"
              @click="openChangeModal"
            >
              Mover Mesa
            </VBtn>

            <!-- RestoTech frmFacturacion1 Trigger -->
            <VBtn
              color="success"
              variant="elevated"
              prepend-icon="ri-secure-payment-line"
              class="flex-grow-1 font-weight-bold"
              @click="abrirCobroModal"
            >
              COBRAR & FACTURAR (F12)
            </VBtn>
          </div>
        </VCardText>

        <VCardActions class="pa-4 pt-0">
          <VBtn block variant="tonal" color="secondary" @click="tableDetailDialog = false">
            Cerrar Ventana
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- DIALOG: Cambiar de Mesa (frmCambiarMesa) -->
    <VDialog
      v-model="changeTableDialog"
      max-width="450"
    >
      <VCard>
        <VCardItem class="bg-info text-white">
          <VCardTitle class="text-white font-weight-bold">
            Cambiar de Mesa
          </VCardTitle>
          <VCardSubtitle class="text-white opacity-80">
            Mover cuenta de {{ targetMesa?.nombre }} a otra mesa disponible
          </VCardSubtitle>
        </VCardItem>

        <VCardText class="pt-5">
          <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-2">
            SELECCIONE LA MESA DESTINO:
          </label>

          <VSelect
            v-model="destinationMesaId"
            :items="freeTablesForMove"
            item-title="nombre"
            item-value="id"
            placeholder="Seleccione mesa libre..."
            prepend-inner-icon="ri-layout-grid-line"
          />
        </VCardText>

        <VCardActions class="pa-4 pt-0 d-flex justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="changeTableDialog = false">
            Cancelar
          </VBtn>
          <VBtn
            color="info"
            variant="elevated"
            :disabled="!destinationMesaId"
            :loading="salonStore.loading"
            @click="submitChangeTable"
          >
            Confirmar Cambio
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- FULLSCREEN MODAL: Toma de Pedidos TÃ¡ctil (frmOrdenesPedido) -->
    <ComandaModal
      v-model="comandaModalOpen"
      :mesa="targetMesa"
      :visita="salonStore.selectedMesaDetails?.visita"
      @comanda-enviada="onComandaEnviada"
    />

    <!-- MODAL: Cobro y Facturacion SIAT Bolivia (frmFacturacion1) -->
    <CobroFacturacionModal
      v-model="cobroModalOpen"
      :mesa="targetMesa"
      :visita="salonStore.selectedMesaDetails?.visita"
      @cobro-exitoso="onCobroExitoso"
    />

    <!-- MODAL: Control de Caja & Arqueo de Turno (frmControlCajaTurno) -->
    <ControlCajaModal
      v-model="controlCajaModalOpen"
      @turno-actualizado="cajaStore.fetchTurnoActivo()"
    />
  </div>
</template>

<style scoped>
.pos-tables-container {
  padding: 8px 0;
}

/* Mesa Cards Styling */
.mesa-card {
  border-radius: 12px;
  border: 2px solid transparent;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 180px;
}

.mesa-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(var(--v-shadow-key-umbrella), 0.15) !important;
}

/* Colores oficiales RestoTech */
.mesa-libre {
  border-color: rgba(var(--v-theme-success), 0.35);
  background: linear-gradient(180deg, rgba(var(--v-theme-success), 0.03) 0%, transparent 100%);
}

.mesa-ocupada {
  border-color: rgba(var(--v-theme-error), 0.5);
  background: linear-gradient(180deg, rgba(var(--v-theme-error), 0.05) 0%, transparent 100%);
}

.mesa-precuenta {
  border-color: rgba(var(--v-theme-warning), 0.6);
  background: linear-gradient(180deg, rgba(var(--v-theme-warning), 0.06) 0%, transparent 100%);
  animation: pulse-precuenta 2s infinite;
}

@keyframes pulse-precuenta {
  0% {
    box-shadow: 0 0 0 0 rgba(var(--v-theme-warning), 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(var(--v-theme-warning), 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(var(--v-theme-warning), 0);
  }
}

.mesa-title {
  font-size: 1.15rem;
  letter-spacing: 0.5px;
}
</style>