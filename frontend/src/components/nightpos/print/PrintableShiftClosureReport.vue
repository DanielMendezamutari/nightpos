<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { PRINT_TICKET_FOOTER } from '@/constants/printTicket'
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  shift: { type: Object, default: null },
  summary: { type: Object, default: null },
  managerial: { type: Object, default: null },
  tenantName: { type: String, default: '' },
  loading: { type: Boolean, default: false },
  width: {
    type: String,
    default: '80mm',
    validator: v => ['58mm', '80mm'].includes(v),
  },
})

const money = v => `${formatMoney(v)} BOB`
const num = v => Number(v ?? 0)

const mgr = computed(() => props.managerial ?? {})

const subtitle = computed(() => {
  const s = props.shift
  if (!s)
    return ''
  return [s.name, s.business_date, s.shift_type_label || s.shift_type].filter(Boolean).join(' · ')
})

const paymentMethodRows = [
  { key: 'CASH', label: 'Efectivo' },
  { key: 'QR', label: 'QR' },
  { key: 'CARD', label: 'Tarjeta' },
  { key: 'MIXED', label: 'Mixto' },
]

const settlementGroups = [
  { key: 'WAITER', title: 'Garzones' },
  { key: 'GIRL', title: 'Chicas' },
  { key: 'CLEANING', title: 'Limpieza' },
]

const durationLabel = computed(() => {
  const s = props.shift
  if (!s?.opened_at || !s?.closed_at)
    return null
  const mins = Math.round((new Date(s.closed_at) - new Date(s.opened_at)) / 60000)
  return mins > 0 ? `${mins} min` : null
})
</script>

<template>
  <PrintableTicketShell
    :width="width"
    title="Cierre de turno"
    :subtitle="subtitle"
    :loading="loading"
    :footer-text="PRINT_TICKET_FOOTER"
  >
    <template v-if="mgr.general || mgr.sales">
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
          v-if="shift?.branch_name"
          class="nightpos-print-row"
        >
          <span>Sucursal</span>
          <span>{{ shift.branch_name }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Administrador</span>
          <span>{{ shift?.closed_by_name || '—' }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Cajas cerradas</span>
          <span>{{ mgr.general?.closed_cash_sessions ?? 0 }}</span>
        </div>
        <div
          v-if="(mgr.general?.cashiers ?? []).length"
          class="nightpos-print-row"
        >
          <span>Cajeros</span>
          <span>{{ mgr.general.cashiers.join(', ') }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Fecha</span>
          <span>{{ shift?.business_date || '—' }}</span>
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
          Resumen general
        </div>
        <div class="nightpos-print-kpi">
          <span>Venta total</span>
          <span>{{ money(mgr.sales?.total ?? summary?.total_sales) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Cantidad de ventas</span>
          <span>{{ mgr.sales?.count ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Ticket promedio</span>
          <span>{{ money(mgr.sales?.average_ticket) }}</span>
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
            {{ row.label }} ({{ mgr.payment_stats?.[row.key]?.count ?? 0 }})
          </span>
          <span class="nightpos-print-list-item__meta">
            {{ money(mgr.payment_stats?.[row.key]?.amount) }}
            <template v-if="mgr.payment_stats?.[row.key]?.percent">
              · {{ mgr.payment_stats[row.key].percent }}%
            </template>
          </span>
        </div>
      </section>

      <section class="nightpos-print-section">
        <div class="nightpos-print-section__title">
          Resultado financiero
        </div>
        <div class="nightpos-print-kpi">
          <span>Ventas</span>
          <span>{{ money(mgr.financial_result?.sales) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Pagado garzones</span>
          <span>{{ money(mgr.financial_result?.paid_waiters) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Pagado chicas</span>
          <span>{{ money(mgr.financial_result?.paid_girls) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Pagado limpieza</span>
          <span>{{ money(mgr.financial_result?.paid_cleaning) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Egresos caja</span>
          <span>{{ money(mgr.financial_result?.cash_expenses) }}</span>
        </div>
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>Total egresos</span>
          <span>{{ money(mgr.financial_result?.total_outflows) }}</span>
        </div>
        <div class="nightpos-print-kpi">
          <span>Venta neta</span>
          <span>{{ money(mgr.financial_result?.net_sales) }}</span>
        </div>
      </section>

      <section
        v-if="num(mgr.settlements_paid?.grand_total) > 0"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Liquidaciones
        </div>
        <div
          v-for="group in settlementGroups"
          :key="group.key"
          class="nightpos-print-row"
        >
          <span>{{ group.title }}</span>
          <span>
            {{ mgr.settlements_paid?.[group.key]?.count ?? 0 }} ·
            {{ money(mgr.settlements_paid?.[group.key]?.total) }}
          </span>
        </div>
        <template
          v-for="group in settlementGroups"
          :key="`people-${group.key}`"
        >
          <div
            v-if="(mgr.settlements_paid?.[group.key]?.people ?? []).length"
            class="nightpos-print-section__hint"
          >
            {{ group.title }}
          </div>
          <div
            v-for="person in mgr.settlements_paid?.[group.key]?.people ?? []"
            :key="`${group.key}-${person.name}`"
            class="nightpos-print-list-item"
          >
            <span class="nightpos-print-list-item__name">{{ person.name }}</span>
            <span class="nightpos-print-list-item__meta">{{ money(person.amount) }}</span>
          </div>
        </template>
      </section>

      <section
        v-if="num(mgr.settlement_adjustments?.total_discounted) > 0"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Ajustes
        </div>
        <div class="nightpos-print-row">
          <span>Multas</span>
          <span>{{ money(mgr.settlement_adjustments?.fines?.amount) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Limpieza</span>
          <span>{{ money(mgr.settlement_adjustments?.cleaning?.amount) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Descuentos manuales</span>
          <span>{{ money(mgr.settlement_adjustments?.manual_discount?.amount) }}</span>
        </div>
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>Total descontado</span>
          <span>{{ money(mgr.settlement_adjustments?.total_discounted) }}</span>
        </div>
      </section>

      <section
        v-if="(mgr.top_products ?? []).length"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Top productos
        </div>
        <div
          v-for="(row, index) in mgr.top_products.slice(0, 20)"
          :key="`prod-${index}`"
          class="nightpos-print-list-item"
        >
          <span class="nightpos-print-list-item__name">
            {{ index + 1 }}. {{ row.quantity_sold }}× {{ row.product_name }}
          </span>
          <span class="nightpos-print-list-item__meta">{{ money(row.total_amount) }}</span>
        </div>
      </section>

      <section
        v-if="Object.keys(mgr.categories ?? {}).length"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Categorías
        </div>
        <div
          v-for="(amount, label) in mgr.categories"
          :key="label"
          class="nightpos-print-list-item"
        >
          <span class="nightpos-print-list-item__name">{{ label }}</span>
          <span class="nightpos-print-list-item__meta">{{ money(amount) }}</span>
        </div>
      </section>

      <section
        v-if="(mgr.waiters ?? []).length"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Garzones
        </div>
        <div
          v-for="(row, index) in mgr.waiters.slice(0, 10)"
          :key="`waiter-${row.name}`"
          class="nightpos-print-list-item"
        >
          <span class="nightpos-print-list-item__name">
            #{{ index + 1 }} {{ row.name }}
          </span>
          <span class="nightpos-print-list-item__meta">{{ money(row.sales) }}</span>
        </div>
      </section>

      <section
        v-if="num(mgr.room_services?.count) > 0 || num(mgr.shows?.count) > 0"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Piezas y shows
        </div>
        <div class="nightpos-print-row">
          <span>Piezas</span>
          <span>{{ mgr.room_services?.count ?? 0 }} · {{ money(mgr.room_services?.total) }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Shows</span>
          <span>{{ mgr.shows?.count ?? 0 }} · {{ money(mgr.shows?.total) }}</span>
        </div>
      </section>

      <section
        v-if="mgr.orders"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Comandas
        </div>
        <div class="nightpos-print-row">
          <span>Creadas</span><span>{{ mgr.orders.created ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Enviadas a barra</span><span>{{ mgr.orders.sent_to_bar ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Cobradas</span><span>{{ mgr.orders.billed ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Canceladas</span><span>{{ mgr.orders.cancelled ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Corregidas</span><span>{{ mgr.orders.corrected ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>Pendientes</span><span>{{ mgr.orders.pending ?? 0 }}</span>
        </div>
      </section>

      <section
        v-if="mgr.incidents && (mgr.incidents.force_close || mgr.incidents.corrections || mgr.incidents.reprints || mgr.incidents.print_errors)"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Incidencias
        </div>
        <div class="nightpos-print-row">
          <span>Force close</span><span>{{ mgr.incidents.force_close ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Correcciones</span><span>{{ mgr.incidents.corrections ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Reimpresiones</span><span>{{ mgr.incidents.reprints ?? 0 }}</span>
        </div>
        <div class="nightpos-print-row">
          <span>Errores impresión</span><span>{{ mgr.incidents.print_errors ?? 0 }}</span>
        </div>
      </section>

      <section
        v-if="mgr.kpis?.top_waiter || mgr.kpis?.top_girl || mgr.kpis?.top_product"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          KPIs del turno
        </div>
        <div
          v-if="mgr.kpis.top_waiter"
          class="nightpos-print-row"
        >
          <span>Garzón top</span>
          <span>{{ mgr.kpis.top_waiter.name }} · {{ money(mgr.kpis.top_waiter.sales) }}</span>
        </div>
        <div
          v-if="mgr.kpis.top_girl"
          class="nightpos-print-row"
        >
          <span>Chica top</span>
          <span>{{ mgr.kpis.top_girl.name }} · {{ money(mgr.kpis.top_girl.settlement) }}</span>
        </div>
        <div
          v-if="mgr.kpis.top_product"
          class="nightpos-print-row"
        >
          <span>Producto top</span>
          <span>{{ mgr.kpis.top_product.product_name }} · {{ mgr.kpis.top_product.quantity_sold }} u.</span>
        </div>
        <div
          v-if="mgr.kpis.top_room"
          class="nightpos-print-row"
        >
          <span>Pieza más vendida</span>
          <span>{{ mgr.kpis.top_room.name }} · {{ mgr.kpis.top_room.uses }} usos</span>
        </div>
      </section>

      <section
        v-if="shift?.closure?.notes"
        class="nightpos-print-section"
      >
        <div class="nightpos-print-section__title">
          Observaciones
        </div>
        <div class="nightpos-print-section__hint">
          {{ shift.closure.notes }}
        </div>
      </section>
    </template>
  </PrintableTicketShell>
</template>
