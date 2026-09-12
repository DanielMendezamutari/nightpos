<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useKdsStore } from '@/stores/kds'

const kdsStore = useKdsStore()

const dialogHistorial = ref(false)
const despachandoId = ref(null)

onMounted(async () => {
  await kdsStore.fetchTickets()
  kdsStore.startPolling(8)
})

onUnmounted(() => {
  kdsStore.stopPolling()
})

const getUrgencyBorder = (urgencia) => {
  if (urgencia === 'DEMORADO') return 'border-error border-dashed'
  if (urgencia === 'ALERTA') return 'border-warning'
  return 'border-success'
}

const getUrgencyColor = (urgencia) => {
  if (urgencia === 'DEMORADO') return 'error'
  if (urgencia === 'ALERTA') return 'warning'
  return 'success'
}

const toggleItemListo = async (item) => {
  const nuevoEstado = item.es_terminado ? 'EN_PREPARACION' : 'LISTO'
  await kdsStore.cambiarEstadoItem(item.id, nuevoEstado)
}

const despacharComanda = async (ticket) => {
  despachandoId.value = ticket.visita_id
  try {
    await kdsStore.despacharTicket(ticket.visita_id)
  } finally {
    despachandoId.value = null
  }
}

const abrirHistorial = async () => {
  await kdsStore.fetchHistorial()
  dialogHistorial.value = true
}

const restaurarComanda = async (item) => {
  await kdsStore.revertirDespacho(item.visita_id)
  dialogHistorial.value = false
}
</script>

<template>
  <div class="kds-container pa-2 pa-md-4">
    <!-- BARRA SUPERIOR DE CONTROL KDS -->
    <VCard class="mb-4 elevation-2 rounded-lg" color="surface">
      <VCardText class="d-flex flex-wrap align-center justify-space-between gap-3 py-3">
        <!-- Selector de Estación Táctil -->
        <div class="d-flex align-center flex-wrap gap-2">
          <VBtn
            v-for="est in kdsStore.estacionesDisponibles"
            :key="est.id"
            :variant="kdsStore.estacionActiva === est.id ? 'elevated' : 'tonal'"
            :color="kdsStore.estacionActiva === est.id ? 'primary' : 'default'"
            size="large"
            class="text-body-1 font-weight-bold"
            @click="kdsStore.setEstacion(est.id)"
          >
            <VIcon :icon="est.icon" start size="20" />
            {{ est.label }}
          </VBtn>
        </div>

        <!-- Métricas y Controles Rápidos -->
        <div class="d-flex align-center flex-wrap gap-3">
          <!-- Contador Activos -->
          <VChip color="primary" size="large" variant="flat" class="font-weight-bold">
            <VIcon icon="ri-time-line" start />
            Activas: {{ kdsStore.metrics.total_activos }}
          </VChip>

          <!-- Demoradas -->
          <VChip
            :color="kdsStore.metrics.demorados > 0 ? 'error' : 'default'"
            size="large"
            :variant="kdsStore.metrics.demorados > 0 ? 'flat' : 'tonal'"
            class="font-weight-bold"
          >
            <VIcon icon="ri-alarm-warning-line" start />
            Demoradas (>20m): {{ kdsStore.metrics.demorados }}
          </VChip>

          <!-- Tiempo Promedio -->
          <VChip color="info" size="large" variant="tonal" class="d-none d-sm-inline-flex">
            Promedio: {{ kdsStore.metrics.tiempo_promedio_min }} min
          </VChip>

          <!-- Botón Audio / Chime -->
          <VTooltip text="Alerta acústica de nueva comanda (RestoTech Chime)">
            <template #activator="{ props }">
              <VBtn
                v-bind="props"
                icon
                size="large"
                :color="kdsStore.audioEnabled ? 'warning' : 'default'"
                :variant="kdsStore.audioEnabled ? 'elevated' : 'tonal'"
                @click="kdsStore.audioEnabled = !kdsStore.audioEnabled"
              >
                <VIcon :icon="kdsStore.audioEnabled ? 'ri-volume-up-line' : 'ri-volume-mute-line'" />
              </VBtn>
            </template>
          </VTooltip>

          <!-- Refrescar Manual -->
          <VBtn
            icon
            size="large"
            variant="tonal"
            :loading="kdsStore.loading"
            @click="kdsStore.fetchTickets()"
          >
            <VIcon icon="ri-refresh-line" />
          </VBtn>

          <!-- Historial de Despachados -->
          <VBtn
            variant="outlined"
            color="secondary"
            size="large"
            class="font-weight-bold"
            @click="abrirHistorial"
          >
            <VIcon icon="ri-history-line" start />
            Historial
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- ESTADO VACÍO CUANDO NO HAY COMANDAS ACTIVAS -->
    <div
      v-if="!kdsStore.loading && kdsStore.tickets.length === 0"
      class="d-flex flex-column align-center justify-center py-16 text-center"
    >
      <VIcon icon="ri-check-double-line" size="96" color="success" class="mb-4 opacity-75" />
      <h2 class="text-h4 font-weight-bold text-success mb-2">¡Todo Listo en Cocina!</h2>
      <p class="text-body-1 text-medium-emphasis">
        No hay comandas pendientes de preparación en la estación <strong>{{ kdsStore.estacionActiva }}</strong>.
      </p>
      <VBtn
        variant="tonal"
        color="primary"
        class="mt-4"
        @click="kdsStore.fetchTickets()"
      >
        <VIcon icon="ri-refresh-line" start />
        Buscar Nuevas Órdenes
      </VBtn>
    </div>

    <!-- CUADRÍCULA DE TICKETS KDS TÁCTIL -->
    <VRow v-else class="match-height">
      <VCol
        v-for="ticket in kdsStore.tickets"
        :key="ticket.visita_id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
        xl="2"
      >
        <VCard
          class="ticket-card elevation-3 d-flex flex-column h-100 rounded-lg overflow-hidden"
          :class="[getUrgencyBorder(ticket.categoria_urgencia), { 'ticket-pulse': ticket.categoria_urgencia === 'DEMORADO' }]"
          style="border-width: 3px;"
        >
          <!-- CABECERA DEL TICKET -->
          <div
            class="pa-3 d-flex align-center justify-space-between"
            :class="`bg-${getUrgencyColor(ticket.categoria_urgencia)}`"
          >
            <div>
              <div class="text-h6 font-weight-black text-white line-height-1">
                {{ ticket.mesa_nombre }}
              </div>
              <div class="text-caption text-white opacity-90 font-weight-medium">
                {{ ticket.salon_nombre }} • {{ ticket.mesero_nombre }}
              </div>
            </div>

            <!-- Cronómetro y Minutos Transcurridos -->
            <div class="text-right">
              <VChip
                color="white"
                variant="flat"
                size="small"
                class="font-weight-black text-dark elevation-1"
              >
                <VIcon icon="ri-timer-line" start size="14" />
                {{ ticket.minutos_transcurridos }}m
              </VChip>
              <div class="text-caption text-white opacity-90 mt-1">
                {{ ticket.hora_pedido }}
              </div>
            </div>
          </div>

          <!-- NOTA GENERAL DE VISITA (SI EXISTE) -->
          <div v-if="ticket.notas" class="bg-amber-lighten-5 pa-2 text-caption text-amber-darken-4 font-weight-medium border-b">
            <VIcon icon="ri-information-line" size="14" class="mr-1" />
            {{ ticket.notas }}
          </div>

          <!-- CUERPO DEL TICKET: LISTA DE PLATOS -->
          <VCardText class="pa-2 flex-grow-1 overflow-y-auto" style="max-height: 380px;">
            <VList density="compact" class="pa-0">
              <VListItem
                v-for="item in ticket.items"
                :key="item.id"
                class="px-2 py-2 mb-1 rounded cursor-pointer transition-all item-row"
                :class="{
                  'bg-surface-variant text-decoration-line-through opacity-50': item.es_terminado,
                  'hover-bg': !item.es_terminado,
                }"
                @click="toggleItemListo(item)"
              >
                <template #prepend>
                  <!-- Badge con Cantidad Grande Táctil -->
                  <div
                    class="item-badge font-weight-black mr-2 d-flex align-center justify-center rounded-circle"
                    :class="item.es_terminado ? 'bg-secondary text-white' : 'bg-primary text-white'"
                  >
                    {{ item.cantidad }}
                  </div>
                </template>

                <VListItemTitle class="font-weight-bold text-body-1 text-wrap">
                  {{ item.producto_nombre }}
                </VListItemTitle>

                <!-- Observaciones de cocina resaltadas -->
                <VListItemSubtitle v-if="item.observaciones" class="mt-1">
                  <VChip
                    color="warning"
                    size="x-small"
                    variant="flat"
                    class="font-weight-bold text-wrap"
                  >
                    <VIcon icon="ri-chat-1-line" start size="12" />
                    {{ item.observaciones }}
                  </VChip>
                </VListItemSubtitle>

                <template #append>
                  <VIcon
                    :icon="item.es_terminado ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line'"
                    :color="item.es_terminado ? 'success' : 'medium-emphasis'"
                    size="22"
                  />
                </template>
              </VListItem>
            </VList>
          </VCardText>

          <!-- PIE DE TICKET: ACCIÓN DE DESPACHO (FoodIsReady) -->
          <VDivider />
          <div class="pa-2 bg-surface">
            <VBtn
              block
              size="large"
              color="success"
              variant="elevated"
              class="font-weight-black text-body-1 elevation-2"
              :loading="despachandoId === ticket.visita_id"
              @click="despacharComanda(ticket)"
            >
              <VIcon icon="ri-check-double-line" start size="20" />
              DESPACHAR TODO
            </VBtn>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- DIÁLOGO HISTORIAL DE DESPACHADOS (RESTAURAR) -->
    <VDialog v-model="dialogHistorial" max-width="850">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
          <div class="d-flex align-center gap-2">
            <VIcon icon="ri-history-line" size="24" />
            <span class="text-h6 font-weight-bold">Historial de Comandas Despachadas</span>
          </div>
          <VBtn icon variant="text" color="white" @click="dialogHistorial = false">
            <VIcon icon="ri-close-line" />
          </VBtn>
        </VCardTitle>

        <VCardText class="pa-4">
          <div v-if="kdsStore.historial.length === 0" class="text-center py-8 text-medium-emphasis">
            No hay despachos recientes en esta sesión.
          </div>

          <VTable v-else hover class="text-no-wrap">
            <thead>
              <tr>
                <th class="font-weight-bold">Mesa</th>
                <th class="font-weight-bold">Salón & Mozo</th>
                <th class="font-weight-bold">Hora Despacho</th>
                <th class="font-weight-bold">Tiempo Cocina</th>
                <th class="font-weight-bold">Platos</th>
                <th class="font-weight-bold text-center">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in kdsStore.historial" :key="item.visita_id">
                <td class="font-weight-black text-primary">{{ item.mesa_nombre }}</td>
                <td>{{ item.salon_nombre }} ({{ item.mesero_nombre }})</td>
                <td>{{ item.hora_despacho }}</td>
                <td>
                  <VChip
                    :color="item.tiempo_entrega_min > 20 ? 'error' : item.tiempo_entrega_min > 10 ? 'warning' : 'success'"
                    size="small"
                    variant="flat"
                    class="font-weight-bold"
                  >
                    {{ item.tiempo_entrega_min }} min
                  </VChip>
                </td>
                <td>
                  <span class="text-caption font-weight-medium">
                    {{ item.items.map(i => `${i.cantidad}x ${i.producto_nombre}`).join(', ') }}
                  </span>
                </td>
                <td class="text-center">
                  <VBtn
                    size="small"
                    color="warning"
                    variant="tonal"
                    class="font-weight-bold"
                    @click="restaurarComanda(item)"
                  >
                    <VIcon icon="ri-arrow-go-back-line" start size="16" />
                    Devolver
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>

        <VCardActions class="pa-4 bg-surface-variant d-flex justify-end">
          <VBtn variant="tonal" color="default" @click="dialogHistorial = false">
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.kds-container {
  min-height: 85vh;
}

.ticket-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.ticket-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.item-badge {
  width: 28px;
  height: 28px;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.hover-bg:hover {
  background-color: rgba(var(--v-theme-primary), 0.08);
}

.ticket-pulse {
  animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
  0% {
    box-shadow: 0 0 0 0 rgba(var(--v-theme-error), 0.7);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(var(--v-theme-error), 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(var(--v-theme-error), 0);
  }
}
</style>
