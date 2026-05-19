<script setup>
import { useCajaFormatters } from '../../composables/useCajaFormatters'

defineProps({
  report: { type: Object, default: null },
  loadingReport: { type: Boolean, default: false },
  booting: { type: Boolean, default: false },
  needsSitePicker: { type: Boolean, default: false },
  sites: { type: Array, default: () => [] },
  sitePickerId: { type: Number, default: null },
  history: { type: Array, default: () => [] },
  selectedShiftId: { type: Number, default: null },
  currentShift: { type: Object, default: null },
})

defineEmits(['update:sitePickerId', 'update:selectedShiftId', 'go-arqueo'])

const { formatMoney, formatWhen, formatPct } = useCajaFormatters()
</script>

<template>
  <div class="liq-root">
    <p class="maint-tab-intro">
      Liquidación por turno al estilo ERP: cuadre de ventas, cobranzas por medio (efectivo / QR / tarjeta), pagos a chicas,
      egresos de caja y detalle operativo.
    </p>

    <div v-if="needsSitePicker" class="field-block caja-site-pick">
      <span>Sucursal</span>
      <select
        :value="sitePickerId"
        @change="$emit('update:sitePickerId', Number($event.target.value))"
      >
        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
      </select>
    </div>

    <div class="field-block caja-site-pick">
      <span>Turno</span>
      <select
        class="caja-shift-select"
        :value="selectedShiftId ?? ''"
        @change="$emit('update:selectedShiftId', Number($event.target.value) || null)"
      >
        <option value="" disabled>Elegí un turno…</option>
        <option v-for="h in history" :key="h.id" :value="h.id">
          #{{ h.id }} — {{ h.status === 'open' ? 'Abierto' : 'Cerrado' }} — {{ formatWhen(h.opened_at) }}
        </option>
      </select>
    </div>

    <p v-if="currentShift?.id" class="caja-link-row">
      <button type="button" class="ghost-btn" @click="$emit('go-arqueo')">Ir a cierre de caja (arqueo)</button>
    </p>

    <div v-if="loadingReport" class="panel-muted">Cargando reporte…</div>

    <template v-else-if="report">
      <div v-if="report.erp_summary" class="report-section erp-board">
        <h4>Cuadre del turno</h4>
        <div class="erp-grid">
          <div class="erp-cell">
            <span>Ventas ítems (suma subtotales)</span>
            <strong>{{ formatMoney(report.erp_summary.product_sales_subtotal) }}</strong>
          </div>
          <div class="erp-cell">
            <span>Cobrado efectivo · QR · tarjeta</span>
            <strong>
              {{ formatMoney(report.erp_summary.payments_collected?.cash) }} ·
              {{ formatMoney(report.erp_summary.payments_collected?.qr) }} ·
              {{ formatMoney(report.erp_summary.payments_collected?.card) }}
            </strong>
          </div>
          <div class="erp-cell erp-cell--accent">
            <span>Total cobrado (todos los medios)</span>
            <strong>{{ formatMoney(report.erp_summary.payments_collected?.total) }}</strong>
          </div>
          <div class="erp-cell">
            <span>Comisiones meseros</span>
            <strong>{{ formatMoney(report.erp_summary.waiter_commissions_total) }}</strong>
          </div>
          <div class="erp-cell">
            <span>Total pagado a chicas (salidas)</span>
            <strong>{{ formatMoney(report.erp_summary.companion_payouts_total) }}</strong>
          </div>
          <div class="erp-cell">
            <span>Egresos caja (incl. pagos chicas registrados en cajón)</span>
            <strong>{{ formatMoney(report.erp_summary.drawer_all_out) }}</strong>
          </div>
          <div class="erp-cell erp-cell--accent">
            <span>Efectivo esperado en cajón</span>
            <strong>{{ formatMoney(report.erp_summary.expected_cash_in_drawer) }}</strong>
          </div>
        </div>
        <p class="erp-hint">
          QR y tarjeta no entran al arqueo físico: figuran en “cobrado” y en el detalle de medios, pero el conteo de billetes
          debe coincidir con el efectivo esperado.
        </p>
      </div>

      <div class="report-section">
        <h4>Resumen de caja</h4>
        <div class="panel-muted caja-summary">
          <p>
            <span>Estado</span> <strong>{{ report.shift?.status === 'open' ? 'Abierto' : 'Cerrado' }}</strong>
          </p>
          <p>
            <span>Apertura</span> <strong>{{ formatWhen(report.shift?.opened_at) }}</strong>
          </p>
          <p v-if="report.shift?.closed_at">
            <span>Cierre</span> <strong>{{ formatWhen(report.shift.closed_at) }}</strong>
          </p>
          <p>
            <span>Efectivo inicial</span> <strong>{{ formatMoney(report.shift?.opening_cash) }}</strong>
          </p>
          <p>
            <span>Ventas efectivo</span> <strong>{{ formatMoney(report.cash_totals?.cash_from_sales) }}</strong>
          </p>
          <p>
            <span>QR / tarjeta</span>
            <strong
              >{{ formatMoney(report.cash_totals?.payment_totals?.qr) }} /
              {{ formatMoney(report.cash_totals?.payment_totals?.card) }}</strong
            >
          </p>
          <p>
            <span>Ingresos caja</span> <strong>+ {{ formatMoney(report.cash_totals?.drawer_in) }}</strong>
          </p>
          <p>
            <span>Retiros caja</span> <strong>- {{ formatMoney(report.cash_totals?.drawer_out) }}</strong>
          </p>
          <p class="caja-expected">
            <span>Efectivo esperado</span> <strong>{{ formatMoney(report.cash_totals?.expected_cash) }}</strong>
          </p>
        </div>
      </div>

      <div class="report-section">
        <h4>Parámetros de liquidación</h4>
        <ul class="payout-list">
          <li>Comisión meseros: {{ formatPct(report.payout_settings?.waiter_commission_rate_pct) }}</li>
          <li>Chicas — manillas: {{ formatPct(report.payout_settings?.companion_manilla_commission_pct) }}</li>
          <li>Chicas — piezas: {{ formatPct(report.payout_settings?.companion_pieza_commission_pct) }}</li>
        </ul>
        <small class="hint">Los sugeridos aparecen si hay porcentajes cargados en sistema.</small>
      </div>

      <div class="report-section">
        <h4>Meseros</h4>
        <p class="erp-hint" style="margin: 0 0 0.5rem">
          Quienes están como mensual/semanal en <strong>Administración → Usuarios</strong> no suman comisión acá: sus ventas
          siguen figurando para control, pero el monto en “Comisión” será 0 en el POS.
        </p>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Mesero</th>
                <th class="num">Órdenes</th>
                <th class="num">Unidades</th>
                <th class="num">Ventas ítems</th>
                <th class="num">Comisión</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="w in report.waiters" :key="w.waiter_user_id">
                <td>{{ w.waiter_name }}</td>
                <td class="num">{{ w.orders_count }}</td>
                <td class="num">{{ w.units_sold }}</td>
                <td class="num">{{ formatMoney(w.items_subtotal) }}</td>
                <td class="num strong">{{ formatMoney(w.commission_owed) }}</td>
              </tr>
              <tr v-if="!report.waiters?.length">
                <td colspan="5" class="empty">Sin ventas con mesero en este turno.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="report-section">
        <h4>Productos vendidos</h4>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th class="num">Cantidad</th>
                <th class="num">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in report.products_sold" :key="p.product_id">
                <td>{{ p.sku }}</td>
                <td>{{ p.name }}</td>
                <td class="num">{{ p.quantity }}</td>
                <td class="num">{{ formatMoney(p.subtotal) }}</td>
              </tr>
              <tr v-if="!report.products_sold?.length">
                <td colspan="4" class="empty">Sin ítems en este turno.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="report-section">
        <h4>Chicas — manillas y piezas</h4>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Chica</th>
                <th class="num">Lín. manilla</th>
                <th class="num">Uds.</th>
                <th class="num">$ man.</th>
                <th class="num">Piezas</th>
                <th class="num">$ piezas</th>
                <th class="num">Cobrado</th>
                <th class="num">Saldo</th>
                <th class="num">Sug. total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in report.companions_overview" :key="c.companion_id">
                <td>{{ c.stage_name }}</td>
                <td class="num">{{ c.manilla_lines }}</td>
                <td class="num">{{ c.manilla_units }}</td>
                <td class="num">{{ formatMoney(c.manilla_subtotal) }}</td>
                <td class="num">{{ c.pieza_count }}</td>
                <td class="num">{{ formatMoney(c.pieza_subtotal) }}</td>
                <td class="num">{{ formatMoney(c.pieza_paid_total) }}</td>
                <td class="num">{{ formatMoney(c.pieza_balance_due) }}</td>
                <td class="num strong">
                  {{ c.suggested_payout_total != null ? formatMoney(c.suggested_payout_total) : '—' }}
                </td>
              </tr>
              <tr v-if="!report.companions_overview?.length">
                <td colspan="9" class="empty">Sin datos de chicas en este turno.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="report-section">
        <h4>Piezas / habitación</h4>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Chica</th>
                <th>Sala</th>
                <th class="num">Min.</th>
                <th class="num">Subtotal</th>
                <th class="num">Cobrado</th>
                <th class="num">Saldo</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in report.pieza_services" :key="s.id">
                <td>{{ s.id }}</td>
                <td>{{ s.companion_name || '—' }}</td>
                <td>{{ s.room_label || '—' }}</td>
                <td class="num">{{ s.billed_minutes }}</td>
                <td class="num">{{ formatMoney(s.subtotal) }}</td>
                <td class="num">{{ formatMoney(s.paid_total) }}</td>
                <td class="num">{{ formatMoney(s.balance_due) }}</td>
                <td>{{ s.status }}</td>
              </tr>
              <tr v-if="!report.pieza_services?.length">
                <td colspan="8" class="empty">Sin servicios de habitación.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="report-section">
        <h4>Movimientos de caja (turno)</h4>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Cuándo</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th class="num">Monto</th>
                <th>Nota</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in report.cash_drawer_movements" :key="m.id">
                <td>{{ formatWhen(m.created_at) }}</td>
                <td>{{ m.user_name }}</td>
                <td>{{ m.direction === 'in' ? 'Ingreso' : 'Egreso' }}</td>
                <td class="num">{{ formatMoney(m.amount) }}</td>
                <td>{{ m.notes || '—' }}</td>
              </tr>
              <tr v-if="!report.cash_drawer_movements?.length">
                <td colspan="5" class="empty">Sin movimientos.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <div v-else-if="!booting && !loadingReport" class="admin-empty-card">
      <p>No hay datos</p>
      <small>Elegí un turno o revisá la sucursal.</small>
    </div>
  </div>
</template>

<style scoped>
.liq-root {
  width: 100%;
}

.erp-board {
  border-radius: 12px;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.28));
  padding: 0.75rem 1rem 1rem;
  background: rgba(20, 35, 80, 0.35);
}

.erp-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
  gap: 0.65rem;
  margin-top: 0.5rem;
}

.erp-cell {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 0.5rem 0.65rem;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0.2);
  font-size: 0.85rem;
}

.erp-cell span {
  color: var(--color-muted, #97ace4);
  font-size: 0.78rem;
}

.erp-cell strong {
  font-size: 1rem;
}

.erp-cell--accent {
  border: 1px solid rgba(130, 170, 255, 0.35);
  background: rgba(80, 120, 255, 0.12);
}

.erp-hint {
  margin: 0.65rem 0 0;
  font-size: 0.78rem;
  color: var(--color-muted, #97ace4);
  line-height: 1.45;
}

.caja-site-pick {
  max-width: 28rem;
  margin-bottom: 0.75rem;
}

.caja-shift-select {
  width: 100%;
  max-width: 28rem;
}

.caja-link-row {
  margin: 0 0 1rem;
}

.report-section {
  margin-bottom: 1.5rem;
}

.report-section h4 {
  margin: 0 0 0.5rem;
  font-size: 1rem;
}

.caja-summary {
  border-radius: 12px;
  padding: 0.9rem 1rem;
  display: grid;
  gap: 0.35rem;
}

.caja-summary p {
  margin: 0;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.caja-summary span {
  color: var(--color-muted, #97ace4);
}

.caja-expected {
  padding-top: 0.35rem;
  margin-top: 0.25rem;
  border-top: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.2));
}

.payout-list {
  margin: 0.25rem 0 0.5rem;
  padding-left: 1.2rem;
}

.hint {
  display: block;
  color: var(--color-muted, #97ace4);
  line-height: 1.4;
}

.table-wrap {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.2));
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.data-table th,
.data-table td {
  padding: 0.5rem 0.65rem;
  text-align: left;
  border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.15));
}

.data-table th {
  background: rgba(0, 0, 0, 0.15);
  font-weight: 600;
}

.data-table .num {
  text-align: right;
  white-space: nowrap;
}

.data-table .strong {
  font-weight: 700;
}

.data-table .empty {
  text-align: center;
  color: var(--color-muted, #97ace4);
  font-style: italic;
}
</style>
