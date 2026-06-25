<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { forceCloseReasonLabel } from '@/api/adminCashSessions'
import { CASH_CLOSE_BANNER, PRINT_TICKET_FOOTER } from '@/constants/printTicket'
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  data: { type: Object, default: null },
  summary: { type: Object, default: null },
  operational: { type: Object, default: null },
  branchName: { type: String, default: '' },
  tenantName: { type: String, default: '' },
  shiftLabel: { type: String, default: '' },
  adminName: { type: String, default: '' },
  width: {
    type: String,
    default: '80mm',
    validator: v => ['58mm', '80mm'].includes(v),
  },
  loading: { type: Boolean, default: false },
})

const formatDateTime = value => {
  if (!value)
    return '—'
  try {
    return new Date(value).toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' })
  }
  catch {
    return value
  }
}

const hasValue = v => v != null && v !== ''
const money = v => `${formatMoney(v ?? 0)} BOB`
const num = v => Number(v ?? 0)

const op = computed(() => props.operational ?? {})
const fin = computed(() => {
  const s = props.summary ?? {}
  const d = props.data ?? {}
  return {
    opening: s.opening_cash ?? d.openingAmount ?? 0,
    expectedCash: s.expected_cash ?? d.expected ?? 0,
    expectedQr: s.expected_qr ?? 0,
    expectedCard: s.expected_card ?? 0,
    counted: s.counted_cash ?? d.counted,
    difference: s.cash_difference ?? d.difference,
  }
})

const salesInfo = computed(() => op.value.sales ?? {})
const paymentStats = computed(() => op.value.payment_stats ?? {})
const paymentMethodRows = computed(() => [
  { key: 'CASH', label: 'Efectivo' },
  { key: 'QR', label: 'QR' },
  { key: 'CARD', label: 'Tarjeta' },
  { key: 'MIXED', label: 'Mixto' },
])

const closeBanner = computed(() => {
  const d = props.data
  if (!d)
    return { label: '', variant: 'normal' }
  if (d.isForcedClose)
    return { label: CASH_CLOSE_BANNER.ADMIN, variant: 'admin' }
  const diff = num(fin.value.difference)
  const hasNotes = hasValue(d.closingNotes) || hasValue(d.openingNotes)
  const hasBlockers = (d.blockerMessages ?? []).length > 0
  const hasPending = Object.values(op.value.pending ?? {}).some(v => num(v) > 0)
  if (hasNotes || hasBlockers || Math.abs(diff) > 0.009 || hasPending)
    return { label: CASH_CLOSE_BANNER.WITH_NOTES, variant: 'notes' }
  return { label: CASH_CLOSE_BANNER.NORMAL, variant: 'normal' }
})

const settlementsPaid = computed(() => op.value.settlements_paid ?? {})
const adjustments = computed(() => op.value.settlement_adjustments ?? {})
const pending = computed(() => op.value.pending ?? {})
const movements = computed(() => op.value.movements ?? [])
const movementsSummary = computed(() => op.value.movements_summary ?? {})

const hasPending = computed(() =>
  num(pending.value.settlements) > 0
  || num(pending.value.orders) > 0
  || num(pending.value.room_services) > 0
  || num(pending.value.shows) > 0,
)

const hasAdjustments = computed(() =>
  num(adjustments.value.fines?.count) > 0
  || num(adjustments.value.cleaning?.count) > 0
  || num(adjustments.value.manual_discount?.count) > 0,
)

const hasSettlementsPaid = computed(() => num(settlementsPaid.value.grand_total) > 0)

const durationLabel = computed(() => {
  const general = op.value.general ?? {}
  const opened = general.opened_at || props.data?.openedAt
  const closed = general.closed_at || props.data?.closedAt
  if (!opened || !closed)
    return null
  const mins = Math.round((new Date(closed) - new Date(opened)) / 60000)
  return mins > 0 ? `${mins} min` : null
})

const openedAtDisplay = computed(() => op.value.general?.opened_at || props.data?.openedAt)
const closedAtDisplay = computed(() => op.value.general?.closed_at || props.data?.closedAt)

const observations = computed(() => {
  const lines = []
  const d = props.data
  if (hasValue(d?.openingNotes))
    lines.push({ label: 'Apertura', text: d.openingNotes })
  if (hasValue(d?.closingNotes))
    lines.push({ label: 'Cierre', text: d.closingNotes })
  if (d?.isForcedClose && hasValue(d?.forcedCloseNotes))
    lines.push({ label: 'Admin', text: d.forcedCloseNotes })
  return lines
})

const incidents = computed(() => {
  const items = []
  const d = props.data
  if (Math.abs(num(fin.value.difference)) > 0.009 && hasValue(fin.value.difference))
    items.push(`Diferencia de arqueo: ${money(fin.value.difference)}`)
  for (const msg of d?.blockerMessages ?? [])
    if (msg)
      items.push(msg)
  if (d?.isForcedClose && d?.forcedCloseReason)
    items.push(`Motivo admin: ${forceCloseReasonLabel(d.forcedCloseReason) || d.forcedCloseReason}`)
  return items
})

const settlementGroups = computed(() => [
  { key: 'WAITER', title: 'Garzones' },
  { key: 'GIRL', title: 'Chicas' },
  { key: 'CLEANING', title: 'Limpieza' },
])
</script>

<template>
  <PrintableTicketShell
    :width="width"
    title="Cierre de caja"
    :subtitle="branchName || (data ? `Sesión #${data.id}` : '')"
    :loading="loading"
    :footer-text="PRINT_TICKET_FOOTER"
  >
    <template
      v-if="closeBanner.label"
      #banner
    >
      <div
        class="nightpos-print-banner"
        :class="{
          'nightpos-print-banner--admin': closeBanner.variant === 'admin',
          'nightpos-print-banner--notes': closeBanner.variant === 'notes',
        }"
      >
        {{ closeBanner.label }}
      </div>
    </template>

    <template v-if="data">
      <section class="nightpos-print-section">
        <div class="nightpos-print-section__title">
          Información general
        </div>
        <div
          v-if="tenantName"
          class="nightpos-print-row"
        >
          <span>Empresa</span>
          <span>{{ tenantName }}</span>
        </div>
        <div
          v-if="branchName"
          class="nightpos-print-row"
        >
          <span>Sucursal</span>
          <span>{{ branchName }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Caja</span>
          <span>#{{ data.id }}</span>
        </div>
        <div
          v-if="shiftLabel"
          class="nightpos-print-row"
        >
          <span>Turno</span>
          <span>{{ shiftLabel }}</span>
        </div>
        <div
          v-if="data.cashierName"
          class="nightpos-print-row"
        >
          <span>Cajera</span>
          <span>{{ data.cashierName }}</span>
        </div>
        <div
          v-if="adminName"
          class="nightpos-print-row"
        >
          <span>Administrador</span>
          <span>{{ adminName }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Apertura</span>
          <span>{{ formatDateTime(openedAtDisplay) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Cierre</span>
          <span>{{ closedAtDisplay ? formatDateTime(closedAtDisplay) : 'Abierta' }}</span>
        </div>
        <div
          v-if="durationLabel"
          class="nightpos-print-row"
        >
          <span>Duración</span>
          <span>{{ durationLabel }}</span>
        </div>
      </section>

      <section class="nightpos-print-section">
        <div class="nightpos-print-section__title">
          Resumen de ventas
        </div>
        <div class="nightpos-print-kpi">
          <span>Total vendido</span>
          <span>{{ money(salesInfo.total) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Cantidad de ventas</span>
          <span>{{ salesInfo.count ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Ticket promedio</span>
          <span>{{ money(salesInfo.average_ticket) }}</span>
        </div>
      </section>

      <section class="nightpos-print-section">
        <div class="nightpos-print-section__title">
          Métodos de pago
        </div>
        <div
          v-for="row in paymentMethodRows"
          :key="row.key"
          class="nightpos-print-list-item"
        >
          <span class="nightpos-print-list-item__name">
            {{ row.label }} ({{ paymentStats[row.key]?.count ?? 0 }})
          </span>
          <span class="nightpos-print-list-item__meta">{{ money(paymentStats[row.key]?.amount) }}</span>
        </div>
      </section>

      <section class="nightpos-print-section">
        <div class="nightpos-print-section__title">
          Arqueo
        </div>
        <div class="nightpos-print-row">
          <span>Monto inicial</span>
          <span>{{ money(fin.opening) }}</span>
        </div>
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>Efectivo esperado</span>
          <span>{{ money(fin.expectedCash) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Efectivo declarado</span>
          <span>{{ data.isForcedClose && !hasValue(fin.counted) ? 'Sin arqueo' : money(fin.counted) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Diferencia</span>
          <span>{{ data.isForcedClose && !hasValue(fin.difference) ? 'Sin arqueo' : money(fin.difference) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>QR esperado</span>
          <span>{{ money(fin.expectedQr) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Tarjeta esperada</span>
          <span>{{ money(fin.expectedCard) }}</span>
        </div>
      </section>

      <section
        v-if="movements.length"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Movimientos de caja
        </div>
        <div class="nightpos-print-row">
          <span>Ingresos</span>
          <span>{{ money(movementsSummary.income_total) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Egresos</span>
          <span>{{ money(movementsSummary.expense_total) }}</span>
        </div>
        <div
          v-for="(mov, index) in movements"
          :key="`mov-${index}`"
          class="nightpos-print-list-item"
        >
          <span class="nightpos-print-list-item__name">
            {{ mov.movement_type === 'INCOME' ? 'Ingreso' : 'Egreso' }} - {{ mov.reason || 'Movimiento' }}
          </span>
          <span class="nightpos-print-list-item__meta">{{ money(mov.amount) }}</span>
        </div>
      </section>

      <section
        v-if="hasSettlementsPaid"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Liquidaciones pagadas
        </div>
        <div
          v-for="group in settlementGroups"
          :key="group.key"
          class="nightpos-print-row"
        >
          <span>{{ group.title }}</span>
          <span>
            {{ settlementsPaid[group.key]?.count ?? 0 }} · {{ money(settlementsPaid[group.key]?.total) }}
          </span>
        </div>
        <div class="nightpos-print-kpi">
          <span>Total pagado</span>
          <span>{{ money(settlementsPaid.grand_total) }}</span>
        </div>
        <template
          v-for="group in settlementGroups"
          :key="`detail-${group.key}`"
        >
          <div
            v-if="(settlementsPaid[group.key]?.people ?? []).length"
            class="nightpos-print-section__hint"
          >
            {{ group.title }}
          </div>
          <div
            v-for="person in settlementsPaid[group.key]?.people ?? []"
            :key="`${group.key}-${person.name}`"
            class="nightpos-print-list-item"
          >
            <span class="nightpos-print-list-item__name">{{ person.name }}</span>
            <span class="nightpos-print-list-item__meta">{{ money(person.amount) }}</span>
          </div>
        </template>
      </section>

      <section
        v-if="hasAdjustments"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Ajustes de liquidaciones
        </div>
        <div class="nightpos-print-row">
          <span>Multas ({{ adjustments.fines?.count ?? 0 }})</span>
          <span>{{ money(adjustments.fines?.amount) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Limpieza descontada ({{ adjustments.cleaning?.count ?? 0 }})</span>
          <span>{{ money(adjustments.cleaning?.amount) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Descuentos manuales ({{ adjustments.manual_discount?.count ?? 0 }})</span>
          <span>{{ money(adjustments.manual_discount?.amount) }}</span>
        </div>
      </section>

      <section
        v-if="hasPending"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Pendientes
        </div>
        <div class="nightpos-print-row">
          <span>Liquidaciones</span>
          <span>{{ pending.settlements ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Comandas</span>
          <span>{{ pending.orders ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Piezas</span>
          <span>{{ pending.room_services ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Shows</span>
          <span>{{ pending.shows ?? 0 }}</span>
        </div>
      </section>

      <section
        v-if="incidents.length"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Incidencias
        </div>
        <div
          v-for="(line, index) in incidents"
          :key="`inc-${index}`"
          class="nightpos-print-section__hint"
        >
          • {{ line }}
        </div>
      </section>

      <section
        v-if="observations.length"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Observaciones
        </div>
        <div
          v-for="obs in observations"
          :key="obs.label"
          class="nightpos-print-row"
        >
          <span>{{ obs.label }}</span>
          <span>{{ obs.text }}</span>
        </div>
      </section>
    </template>
  </PrintableTicketShell>
</template>
