<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatMoney } from '@/composables/useOrderHelpers'
import { formatPrintTime } from '@/composables/usePrintTicketFormat'

const props = defineProps({
  roomService: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const statusLabel = computed(() => {
  const status = String(props.roomService?.status ?? 'ACTIVE').toUpperCase()

  return ({
    ACTIVE: 'ACTIVA',
    DUE: 'TIEMPO CUMPLIDO',
    FINISHED: 'FINALIZADA',
    CANCELLED: 'CANCELADA',
  })[status] ?? status
})
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="PIEZA"
    subtitle="Comanda operativa"
    :loading="loading"
  >
    <template v-if="roomService">
      <div class="nightpos-print-hero">
        Pieza: {{ roomService.room_label || roomService.room_number || '—' }}
      </div>

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Chica</span>
        <span>{{ roomService.girl_name || '—' }}</span>
      </div>

      <div class="nightpos-print-row">
        <span>Inicio</span>
        <span>{{ formatPrintTime(roomService.started_at || roomService.registered_at) }}</span>
      </div>

      <div
        v-if="roomService.duration_minutes"
        class="nightpos-print-row"
      >
        <span>Duración</span>
        <span>{{ roomService.duration_minutes }} min</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Total</span>
        <span>{{ formatMoney(roomService.total_amount) }} BOB</span>
      </div>
      <div class="nightpos-print-row">
        <span>Chica {{ roomService.girl_percent }}%</span>
        <span>{{ formatMoney(roomService.gross_girl_amount || roomService.girl_amount) }} BOB</span>
      </div>
      <div
        v-if="Number(roomService.cleaning_amount ?? 0) > 0"
        class="nightpos-print-row"
      >
        <span>Limpieza</span>
        <span>-{{ formatMoney(roomService.cleaning_amount) }} BOB</span>
      </div>
      <div
        v-if="Number(roomService.cleaning_amount ?? 0) > 0"
        class="nightpos-print-row"
      >
        <span>Chica neta</span>
        <span>{{ formatMoney(roomService.girl_amount) }} BOB</span>
      </div>
      <div class="nightpos-print-row">
        <span>Casa</span>
        <span>{{ formatMoney(roomService.house_amount) }} BOB</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Estado</span>
        <span>{{ statusLabel }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Registro</span>
        <span>{{ roomService.registered_by_name || '—' }}</span>
      </div>

      <div
        v-if="roomService.notes"
        class="nightpos-print-muted mt-2"
      >
        Obs: {{ roomService.notes }}
      </div>

      <div class="nightpos-print-muted text-center mt-2">
        Comanda operativa — no fiscal.
      </div>
    </template>

    <template #footer>
      NightPOS — pieza / limpieza
    </template>
  </PrintableTicketShell>
</template>

<style scoped>
.nightpos-print-hero {
  font-size: 15px;
  font-weight: 700;
  text-align: center;
  margin-block: 8px;
}
</style>
