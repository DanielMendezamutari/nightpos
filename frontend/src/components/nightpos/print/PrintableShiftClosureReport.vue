<script setup>
import PrintableReconciliationSection from '@/components/nightpos/print/PrintableReconciliationSection.vue'
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  shift: {
    type: Object,
    default: null,
  },
  summary: {
    type: Object,
    default: null,
  },
  reconciliation: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const hasValue = v => v != null && v !== ''
const money = v => `${formatMoney(v)} BOB`

const subtitle = computed(() => {
  const s = props.shift
  if (!s)
    return ''

  return [s.name, s.business_date, s.shift_type_label || s.shift_type].filter(Boolean).join(' · ')
})
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="Cierre de turno"
    :subtitle="subtitle"
    :loading="loading"
  >
    <template v-if="summary">
      <div class="nightpos-print-muted mb-1">
        Ventas
      </div>
      <div class="nightpos-print-row">
        <span>Ventas total</span>
        <span>{{ money(summary.total_sales) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Efectivo</span>
        <span>{{ money(summary.total_cash) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>QR</span>
        <span>{{ money(summary.total_qr) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Tarjeta</span>
        <span>{{ money(summary.total_card) }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-muted mb-1">
        Caja
      </div>
      <div
        v-if="hasValue(summary.total_manual_income)"
        class="nightpos-print-row"
      >
        <span>Ingresos manuales</span>
        <span>{{ money(summary.total_manual_income) }}</span>
      </div>
      <div
        v-if="hasValue(summary.total_manual_expense)"
        class="nightpos-print-row"
      >
        <span>Egresos</span>
        <span>{{ money(summary.total_manual_expense) }}</span>
      </div>
      <div
        v-if="hasValue(summary.expected_cash)"
        class="nightpos-print-row"
      >
        <span>Esperado en caja</span>
        <span>{{ money(summary.expected_cash) }}</span>
      </div>
      <div
        v-if="hasValue(summary.counted_cash)"
        class="nightpos-print-row"
      >
        <span>Efectivo contado</span>
        <span>{{ money(summary.counted_cash) }}</span>
      </div>

      <template v-if="hasValue(summary.total_settlements) || hasValue(summary.total_settlements_pending)">
        <hr class="nightpos-print-divider">
        <div class="nightpos-print-muted mb-1">
          Liquidaciones
        </div>
        <div
          v-if="hasValue(summary.total_settlements)"
          class="nightpos-print-row"
        >
          <span>Pagadas</span>
          <span>{{ money(summary.total_settlements) }}</span>
        </div>
        <div
          v-if="hasValue(summary.total_settlements_pending)"
          class="nightpos-print-row"
        >
          <span>Pendientes</span>
          <span>{{ money(summary.total_settlements_pending) }}</span>
        </div>
      </template>

      <template v-if="hasValue(summary.total_services) || hasValue(summary.services_count)">
        <hr class="nightpos-print-divider">
        <div class="nightpos-print-muted mb-1">
          Servicios
        </div>
        <div
          v-if="hasValue(summary.services_count)"
          class="nightpos-print-row"
        >
          <span>Registros</span>
          <span>{{ summary.services_count }}</span>
        </div>
        <div
          v-if="hasValue(summary.total_services)"
          class="nightpos-print-row"
        >
          <span>Total servicios</span>
          <span>{{ money(summary.total_services) }}</span>
        </div>
      </template>

      <template v-if="hasValue(summary.rooms_cleaned) || hasValue(summary.rooms_occupied)">
        <hr class="nightpos-print-divider">
        <div class="nightpos-print-muted mb-1">
          Habitaciones
        </div>
        <div
          v-if="hasValue(summary.rooms_cleaned)"
          class="nightpos-print-row"
        >
          <span>Limpiadas</span>
          <span>{{ summary.rooms_cleaned }}</span>
        </div>
        <div
          v-if="hasValue(summary.rooms_occupied)"
          class="nightpos-print-row"
        >
          <span>Ocupadas</span>
          <span>{{ summary.rooms_occupied }}</span>
        </div>
      </template>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Diferencia caja</span>
        <span>{{ hasValue(summary.cash_difference) ? money(summary.cash_difference) : '—' }}</span>
      </div>

      <PrintableReconciliationSection :data="reconciliation" />
    </template>

    <template #footer>
      NightPOS — resumen de turno oficial
    </template>
  </PrintableTicketShell>
</template>
