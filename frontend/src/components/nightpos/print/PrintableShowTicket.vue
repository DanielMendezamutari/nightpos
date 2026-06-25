<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatMoney } from '@/composables/useOrderHelpers'
import { formatPrintTime } from '@/composables/usePrintTicketFormat'

defineProps({
  show: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="SHOW"
    subtitle="Comanda operativa"
    :loading="loading"
  >
    <template v-if="show">
      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Tipo</span>
        <span>{{ show.show_type_label || show.show_type || '—' }}</span>
      </div>

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Chica</span>
        <span>{{ show.girl_name || '—' }}</span>
      </div>

      <div class="nightpos-print-row">
        <span>Hora</span>
        <span>{{ formatPrintTime(show.registered_at) }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Total</span>
        <span>{{ formatMoney(show.total_amount || show.unit_price) }} BOB</span>
      </div>
      <div class="nightpos-print-row">
        <span>Chica</span>
        <span>{{ formatMoney(show.total_amount || show.unit_price) }} BOB</span>
      </div>
      <div class="nightpos-print-row">
        <span>Casa</span>
        <span>{{ formatMoney(0) }} BOB</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Estado</span>
        <span>REGISTRADO</span>
      </div>
      <div class="nightpos-print-row">
        <span>Registro</span>
        <span>{{ show.registered_by_name || '—' }}</span>
      </div>

      <div
        v-if="show.notes"
        class="nightpos-print-muted mt-2"
      >
        Obs: {{ show.notes }}
      </div>

      <div class="nightpos-print-muted text-center mt-2">
        Comanda operativa — no fiscal.
      </div>
    </template>

    <template #footer>
      NightPOS — show
    </template>
  </PrintableTicketShell>
</template>
