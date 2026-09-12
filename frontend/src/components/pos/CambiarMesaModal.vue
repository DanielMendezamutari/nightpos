<script setup>
import { ref, computed, watch } from 'vue'
import { useComandaStore } from '@/stores/comanda'

const props = defineProps({
  modelValue: Boolean,
  mesaOrigen: {
    type: Object,
    default: null,
  },
  salones: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue', 'mesaCambiada', 'mesaJuntada'])

const comandaStore = useComandaStore()

const salonSeleccionado = ref(null)
const mesaDestinoSeleccionada = ref(null)
const motivo = ref('')
const cargando = ref(false)
const dialogConfirmacion = ref(false)
const esOperacionJuntar = ref(false)
const ticketAuditoria = ref('')
const dialogExito = ref(false)
const mensajeExito = ref('')

watch(() => props.modelValue, (val) => {
  if (val) {
    if (props.salones && props.salones.length > 0) {
      salonSeleccionado.value = props.salones[0].id
    }
    mesaDestinoSeleccionada.value = null
    motivo.value = ''
    ticketAuditoria.value = ''
    dialogExito.value = false
  }
})

const mesasDelSalon = computed(() => {
  if (!props.salones || !salonSeleccionado.value) return []
  const salon = props.salones.find(s => s.id === salonSeleccionado.value)
  return salon ? salon.mesas || [] : []
})

function seleccionarMesaDestino(mesa) {
  if (!props.mesaOrigen || mesa.id === props.mesaOrigen.id) return
  mesaDestinoSeleccionada.value = mesa
  esOperacionJuntar.value = !!mesa.visitaActiva
}

function abrirConfirmacion() {
  if (!mesaDestinoSeleccionada.value) return
  dialogConfirmacion.value = true
}

async function ejecutarOperacion() {
  if (!props.mesaOrigen || !mesaDestinoSeleccionada.value) return

  cargando.value = true
  dialogConfirmacion.value = false

  try {
    if (esOperacionJuntar.value) {
      // JUNTAR MESAS
      const res = await comandaStore.juntarMesa(
        props.mesaOrigen.id,
        mesaDestinoSeleccionada.value.id,
        motivo.value || 'Unión de cuentas solicitada por comensales'
      )
      ticketAuditoria.value = res.ticket_auditoria || ''
      mensajeExito.value = res.message || 'Cuentas unificadas exitosamente.'
      dialogExito.value = true
      emit('mesaJuntada', { origen: props.mesaOrigen, destino: mesaDestinoSeleccionada.value })
    } else {
      // CAMBIAR MESA
      const res = await comandaStore.cambiarMesa(
        props.mesaOrigen.id,
        mesaDestinoSeleccionada.value.id,
        motivo.value || 'Cambio solicitado por cliente'
      )
      ticketAuditoria.value = res.ticket_auditoria || ''
      mensajeExito.value = res.message || 'Mesa cambiada exitosamente.'
      dialogExito.value = true
      emit('mesaCambiada', { origen: props.mesaOrigen, destino: mesaDestinoSeleccionada.value })
    }
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Error en la operación de mesa.')
  } finally {
    cargando.value = false
  }
}

function cerrarTodo() {
  dialogExito.value = false
  emit('update:modelValue', false)
}

function imprimirTicketAuditoria() {
  const printWindow = window.open('', '_blank')
  if (printWindow) {
    printWindow.document.write(`<pre style="font-family: monospace; font-size: 14px;">${ticketAuditoria.value}</pre>`)
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
    printWindow.close()
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="850"
    persistent
    @update:model-value="val => emit('update:modelValue', val)"
  >
    <VCard class="pa-2">
      <VCardTitle class="d-flex justify-space-between align-center pb-2">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-arrow-left-right-line" color="primary" size="26" />
          <span class="text-h6 font-weight-bold">Cambiar / Juntar Mesa (F7)</span>
        </div>
        <VBtn icon="ri-close-line" variant="text" density="compact" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VDivider />

      <VCardText class="pt-4">
        <!-- Info Mesa Origen -->
        <VAlert
          v-if="mesaOrigen"
          color="primary"
          variant="tonal"
          density="comfortable"
          class="mb-4"
        >
          <div class="d-flex justify-space-between align-center flex-wrap gap-2">
            <div>
              <strong>Mesa Origen:</strong>
              <span class="text-h6 font-weight-black ms-2 text-primary">Mesa {{ mesaOrigen.codigo }} - {{ mesaOrigen.nombre }}</span>
            </div>
            <div>
              <span class="text-caption text-medium-emphasis me-2">Consumo Actual:</span>
              <VChip color="primary" font-weight-bold>
                Bs. {{ Number(mesaOrigen.visitaActiva?.total || 0).toFixed(2) }}
              </VChip>
            </div>
          </div>
        </VAlert>

        <!-- Selector de Salón -->
        <div class="mb-3">
          <div class="text-caption font-weight-medium mb-1 text-medium-emphasis">Seleccione el Salón de Destino:</div>
          <div class="d-flex gap-2 flex-wrap">
            <VBtn
              v-for="salon in salones"
              :key="salon.id"
              :variant="salonSeleccionado === salon.id ? 'elevated' : 'outlined'"
              :color="salonSeleccionado === salon.id ? 'primary' : 'secondary'"
              size="small"
              @click="salonSeleccionado = salon.id"
            >
              <VIcon icon="ri-store-2-line" start size="16" />
              {{ salon.nombre }}
            </VBtn>
          </div>
        </div>

        <VDivider class="my-3" />

        <!-- Grid de Mesas del Salón -->
        <div class="text-caption font-weight-medium mb-2 text-medium-emphasis">
          Seleccione la Mesa de Destino:
        </div>

        <div style="max-height: 280px; overflow-y: auto;">
          <VRow dense>
            <VCol
              v-for="mesa in mesasDelSalon"
              :key="mesa.id"
              cols="6"
              sm="4"
              md="3"
            >
              <VCard
                variant="outlined"
                class="pa-3 text-center cursor-pointer transition-all"
                :class="{
                  'border-primary bg-primary-lighten-5': mesaDestinoSeleccionada?.id === mesa.id && !mesa.visitaActiva,
                  'border-warning bg-warning-lighten-5': mesaDestinoSeleccionada?.id === mesa.id && mesa.visitaActiva,
                  'opacity-40 pointer-events-none': mesaOrigen?.id === mesa.id
                }"
                :style="{
                  borderColor: mesaDestinoSeleccionada?.id === mesa.id 
                    ? (mesa.visitaActiva ? '#f59e0b' : '#3b82f6')
                    : (mesa.visitaActiva ? '#fbbf24' : '#10b981')
                }"
                @click="seleccionarMesaDestino(mesa)"
              >
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="text-subtitle-2 font-weight-bold">Mesa {{ mesa.codigo }}</span>
                  <VChip
                    :color="mesa.visitaActiva ? 'warning' : 'success'"
                    size="x-small"
                    variant="flat"
                  >
                    {{ mesa.visitaActiva ? 'OCUPADA' : 'LIBRE' }}
                  </VChip>
                </div>

                <div class="text-caption text-truncate">{{ mesa.nombre }}</div>

                <div v-if="mesa.visitaActiva" class="mt-2 text-caption font-weight-bold text-warning">
                  Bs. {{ Number(mesa.visitaActiva.total || 0).toFixed(2) }}
                </div>
                <div v-else class="mt-2 text-caption text-success font-weight-medium">
                  Disponible
                </div>
              </VCard>
            </VCol>
          </VRow>
        </div>

        <!-- Panel de Confirmación / Motivo -->
        <div v-if="mesaDestinoSeleccionada" class="mt-4 pa-3 rounded bg-var-theme-surface elevation-1">
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="d-flex align-center gap-2">
              <VIcon
                :icon="esOperacionJuntar ? 'ri-git-merge-line' : 'ri-arrow-right-line'"
                :color="esOperacionJuntar ? 'warning' : 'primary'"
              />
              <span class="font-weight-bold">
                {{ esOperacionJuntar ? 'Operación: JUNTAR CUENTAS (Unir Mesas)' : 'Operación: CAMBIAR DE MESA' }}
              </span>
            </div>
            <VChip :color="esOperacionJuntar ? 'warning' : 'primary'" variant="tonal" size="small">
              Mesa Destino: {{ mesaDestinoSeleccionada.codigo }}
            </VChip>
          </div>

          <VAlert
            v-if="esOperacionJuntar"
            color="warning"
            variant="tonal"
            density="compact"
            icon="ri-alert-line"
            class="mb-3 text-caption"
          >
            La Mesa {{ mesaDestinoSeleccionada.codigo }} ya tiene comensales. Los pedidos de Mesa {{ mesaOrigen.codigo }} se sumarán a esta cuenta y la Mesa {{ mesaOrigen.codigo }} quedará libre.
          </VAlert>

          <VTextField
            v-model="motivo"
            label="Motivo del cambio / unión (opcional)"
            placeholder="Ej: Cambio a terraza / Se unen con amigos"
            density="compact"
            variant="outlined"
            hide-details
          />
        </div>
      </VCardText>

      <VCardActions class="pa-3">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">
          Cancelar
        </VBtn>
        <VBtn
          v-if="mesaDestinoSeleccionada"
          :color="esOperacionJuntar ? 'warning' : 'primary'"
          variant="elevated"
          :loading="cargando"
          @click="abrirConfirmacion"
        >
          <VIcon :icon="esOperacionJuntar ? 'ri-git-merge-line' : 'ri-arrow-right-line'" start />
          {{ esOperacionJuntar ? `Juntar Cuentas con Mesa ${mesaDestinoSeleccionada.codigo}` : `Cambiar a Mesa ${mesaDestinoSeleccionada.codigo}` }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Modal Diálogo de Confirmación Definitiva -->
  <VDialog v-model="dialogConfirmacion" max-width="450">
    <VCard>
      <VCardTitle class="text-h6 font-weight-bold pt-4 px-4">
        {{ esOperacionJuntar ? '¿Desea juntar las Cuentas?' : '¿Confirmar Cambio de Mesa?' }}
      </VCardTitle>
      <VCardText class="px-4 py-2">
        <p v-if="esOperacionJuntar">
          Se trasladarán todos los consumos de la <strong>Mesa {{ mesaOrigen?.codigo }}</strong> a la <strong>Mesa {{ mesaDestinoSeleccionada?.codigo }}</strong>.
          La Mesa {{ mesaOrigen?.codigo }} quedará completamente libre.
        </p>
        <p v-else>
          La cuenta y pedidos activos se trasladarán a la <strong>Mesa {{ mesaDestinoSeleccionada?.codigo }}</strong> y la <strong>Mesa {{ mesaOrigen?.codigo }}</strong> quedará disponible.
        </p>
      </VCardText>
      <VCardActions class="px-4 pb-4">
        <VSpacer />
        <VBtn variant="text" color="secondary" @click="dialogConfirmacion = false">No, cancelar</VBtn>
        <VBtn
          :color="esOperacionJuntar ? 'warning' : 'primary'"
          variant="elevated"
          @click="ejecutarOperacion"
        >
          Sí, confirmar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Modal Éxito y Ticket Auditoría -->
  <VDialog v-model="dialogExito" max-width="500" persistent>
    <VCard>
      <VCardTitle class="d-flex align-center gap-2 text-success font-weight-bold pt-4 px-4">
        <VIcon icon="ri-checkbox-circle-line" size="24" />
        Operación Completada
      </VCardTitle>
      <VCardText class="px-4 py-2">
        <p class="mb-3">{{ mensajeExito }}</p>
        <div v-if="ticketAuditoria" class="pa-3 bg-grey-lighten-4 rounded font-monospace text-caption">
          <pre style="white-space: pre-wrap; font-family: monospace;">{{ ticketAuditoria }}</pre>
        </div>
      </VCardText>
      <VCardActions class="px-4 pb-4">
        <VBtn variant="outlined" color="primary" @click="imprimirTicketAuditoria">
          <VIcon icon="ri-printer-line" start /> Imprimir Ticket
        </VBtn>
        <VSpacer />
        <VBtn color="success" variant="elevated" @click="cerrarTodo">
          Aceptar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
