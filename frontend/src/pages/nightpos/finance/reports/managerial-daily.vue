<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchManagerialDailyReport } from '@/api/reports'
import { fetchShifts } from '@/api/shifts'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'reports.access' } })

const { notify } = useNightPosNotify()

const loading = ref(false)
const exporting = ref(false)
const loadingShifts = ref(false)
const report = ref(null)
const shifts = ref([])
const filters = ref({
  dateFrom: '',
  dateTo: '',
  officialShiftId: null,
  includeRankingsLimit: 10,
  includeHoursGranularity: 'hour_24',
})

const shiftItems = computed(() => {
  return (shifts.value ?? []).map(shift => {
    const typeLabel = shift.shift_type_label || (shift.shift_type === 'NIGHT' ? 'Noche' : 'Dia')
    const statusLabel = shift.status === 'OPEN' ? 'Abierto' : 'Cerrado'

    return {
      title: `#${shift.id} · ${typeLabel} · ${shift.business_date} · ${statusLabel}`,
      value: Number(shift.id),
    }
  })
})

const getFilters = () => ({
  dateFrom: filters.value.dateFrom || undefined,
  dateTo: filters.value.dateTo || undefined,
  officialShiftId: filters.value.officialShiftId || undefined,
  includeRankingsLimit: filters.value.includeRankingsLimit || undefined,
  includeHoursGranularity: filters.value.includeHoursGranularity || undefined,
})

const fmtMoney = v => `${Number(v ?? 0).toFixed(2)} BOB`

async function load(options = {}) {
  if (loading.value)
    return

  loading.value = true
  try {
    report.value = await fetchManagerialDailyReport(getFilters())
    if (options.toast)
      notify('Reporte gerencial actualizado', 'success')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

function applyFilters() {
  load({ toast: true })
}

function clearFilters() {
  filters.value = {
    dateFrom: '',
    dateTo: '',
    officialShiftId: null,
    includeRankingsLimit: 10,
    includeHoursGranularity: 'hour_24',
  }
  applyFilters()
}

async function loadShifts() {
  loadingShifts.value = true
  try {
    shifts.value = await fetchShifts()
  }
  catch {
    shifts.value = []
  }
  finally {
    loadingShifts.value = false
  }
}

function printReport() {
  window.print()
}

function csvEscape(value) {
  return JSON.stringify(value ?? '')
}

function buildSimpleCsvRows() {
  if (!report.value)
    return []

  const rows = []

  rows.push({ section: 'kpi', key: 'total_sales', value: report.value.kpis?.total_sales ?? '0.00' })
  rows.push({ section: 'kpi', key: 'total_cash', value: report.value.kpis?.total_cash ?? '0.00' })
  rows.push({ section: 'kpi', key: 'total_qr', value: report.value.kpis?.total_qr ?? '0.00' })
  rows.push({ section: 'kpi', key: 'total_card', value: report.value.kpis?.total_card ?? '0.00' })
  rows.push({ section: 'kpi', key: 'settlements_paid_total', value: report.value.kpis?.settlements_paid_total ?? '0.00' })
  rows.push({ section: 'kpi', key: 'cash_difference_total', value: report.value.kpis?.cash_difference_total ?? '0.00' })
  rows.push({ section: 'kpi', key: 'net_house_estimated', value: report.value.kpis?.net_house_estimated ?? '0.00' })

  for (const row of report.value.waiter_rankings?.top_waiters_by_sales ?? []) {
    rows.push({ section: 'waiter_sales', key: row.waiter_name, value: row.sales_total })
  }

  for (const row of report.value.girl_rankings?.top_girls_by_generated_income ?? []) {
    rows.push({ section: 'girls', key: row.girl_name, value: row.generated_income_total })
  }

  for (const row of report.value.product_rankings?.top_products_by_revenue ?? []) {
    rows.push({ section: 'products', key: row.product_name, value: row.revenue_total })
  }

  return rows
}

function exportSimpleCsv() {
  if (exporting.value)
    return

  const rows = buildSimpleCsvRows()
  if (!rows.length) {
    notify('No hay datos para exportar', 'warning')
    return
  }

  exporting.value = true
  try {
    const headers = Object.keys(rows[0])
    const content = [
      headers.join(','),
      ...rows.map(row => headers.map(h => csvEscape(row[h])).join(',')),
    ].join('\n')

    const blob = new Blob([content], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'reporte-gerencial-diario.csv'
    a.click()
    URL.revokeObjectURL(url)

    notify('CSV exportado', 'success')
  }
  finally {
    exporting.value = false
  }
}

onMounted(async () => {
  await loadShifts()
  await load()
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Reporte Gerencial Diario"
      subtitle="Vista ejecutiva para owner y administración."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Reportes gerenciales', disabled: true },
      ]"
    />

    <VCard class="mb-4">
      <VCardText>
        <VRow dense>
          <VCol cols="12" sm="3">
            <VTextField v-model="filters.dateFrom" type="date" label="Desde" density="compact" hide-details />
          </VCol>
          <VCol cols="12" sm="3">
            <VTextField v-model="filters.dateTo" type="date" label="Hasta" density="compact" hide-details />
          </VCol>
          <VCol cols="12" sm="2">
            <VAutocomplete
              v-model="filters.officialShiftId"
              :items="shiftItems"
              item-title="title"
              item-value="value"
              label="Turno"
              placeholder="Selecciona un turno"
              density="compact"
              clearable
              hide-details
              :loading="loadingShifts"
              :disabled="loadingShifts"
              no-data-text="Sin turnos disponibles"
            />
          </VCol>
          <VCol cols="12" sm="2">
            <VSelect
              v-model="filters.includeRankingsLimit"
              :items="[5, 10, 20]"
              label="Top N"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol cols="12" sm="2">
            <VSelect
              v-model="filters.includeHoursGranularity"
              :items="[
                { title: 'Hora', value: 'hour_24' },
                { title: 'Bloques 3h', value: 'hour_block' },
              ]"
              label="Horario"
              density="compact"
              hide-details
            />
          </VCol>
        </VRow>

        <div class="d-flex flex-wrap gap-2 mt-3">
          <VBtn prepend-icon="ri-search-line" :loading="loading" :disabled="loading" @click="applyFilters">Aplicar filtros</VBtn>
          <VBtn variant="tonal" prepend-icon="ri-refresh-line" :disabled="loading" @click="clearFilters">Limpiar</VBtn>
          <VSpacer />
          <VBtn variant="tonal" prepend-icon="ri-file-download-line" :loading="exporting" :disabled="loading || exporting" @click="exportSimpleCsv">Export CSV</VBtn>
          <VBtn variant="outlined" prepend-icon="ri-printer-line" :disabled="loading" @click="printReport">Imprimir</VBtn>
        </div>
      </VCardText>
    </VCard>

    <VProgressLinear v-if="loading" indeterminate class="mb-4" />

    <template v-if="report">
      <VRow class="mb-4">
        <VCol cols="6" md="3"><VCard><VCardText><div class="text-caption">Venta total</div><div class="text-h6 font-weight-bold text-success">{{ fmtMoney(report.kpis?.total_sales) }}</div></VCardText></VCard></VCol>
        <VCol cols="6" md="3"><VCard><VCardText><div class="text-caption">Efectivo</div><div class="text-h6">{{ fmtMoney(report.kpis?.total_cash) }}</div></VCardText></VCard></VCol>
        <VCol cols="6" md="3"><VCard><VCardText><div class="text-caption">QR</div><div class="text-h6 text-info">{{ fmtMoney(report.kpis?.total_qr) }}</div></VCardText></VCard></VCol>
        <VCol cols="6" md="3"><VCard><VCardText><div class="text-caption">Tarjeta</div><div class="text-h6 text-primary">{{ fmtMoney(report.kpis?.total_card) }}</div></VCardText></VCard></VCol>
        <VCol cols="6" md="3"><VCard><VCardText><div class="text-caption">Liquidaciones pagadas</div><div class="text-h6">{{ fmtMoney(report.kpis?.settlements_paid_total) }}</div></VCardText></VCard></VCol>
        <VCol cols="6" md="3"><VCard><VCardText><div class="text-caption">Diferencia caja</div><div class="text-h6" :class="Number(report.kpis?.cash_difference_total) !== 0 ? 'text-warning' : 'text-success'">{{ fmtMoney(report.kpis?.cash_difference_total) }}</div></VCardText></VCard></VCol>
        <VCol cols="12" md="6"><VCard color="success" variant="tonal"><VCardText><div class="text-caption">Neto estimado casa</div><div class="text-h5 font-weight-bold">{{ fmtMoney(report.kpis?.net_house_estimated) }}</div></VCardText></VCard></VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Ranking garzones por venta</VCardTitle>
            <VTable density="compact">
              <thead><tr><th>Garzón</th><th>Ventas</th><th>Ticket prom.</th></tr></thead>
              <tbody>
                <tr v-for="row in report.waiter_rankings?.top_waiters_by_sales ?? []" :key="`ws-${row.waiter_user_id}`">
                  <td>{{ row.waiter_name }}</td><td>{{ fmtMoney(row.sales_total) }}</td><td>{{ fmtMoney(row.average_ticket) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>

        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Ranking garzones por compensación</VCardTitle>
            <VTable density="compact">
              <thead><tr><th>Garzón</th><th>Total</th><th>Manual</th></tr></thead>
              <tbody>
                <tr v-for="row in report.waiter_rankings?.top_waiters_by_compensation ?? []" :key="`wc-${row.waiter_user_id}`">
                  <td>{{ row.waiter_name }}</td><td>{{ fmtMoney(row.compensation_total) }}</td><td>{{ fmtMoney(row.compensation_manual_total) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Ranking chicas</VCardTitle>
            <VTable density="compact">
              <thead><tr><th>Chica</th><th>Generado</th><th>Liquidado</th></tr></thead>
              <tbody>
                <tr v-for="row in report.girl_rankings?.top_girls_by_generated_income ?? []" :key="`g-${row.girl_name}`">
                  <td>{{ row.girl_name }}</td><td>{{ fmtMoney(row.generated_income_total) }}</td><td>{{ fmtMoney(row.settlement_paid_total) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>

        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Top productos por ingreso</VCardTitle>
            <VTable density="compact">
              <thead><tr><th>Producto</th><th>Ingreso</th><th>Unidades</th></tr></thead>
              <tbody>
                <tr v-for="row in report.product_rankings?.top_products_by_revenue ?? []" :key="`p-${row.product_id}`">
                  <td>{{ row.product_name }}</td><td>{{ fmtMoney(row.revenue_total) }}</td><td>{{ row.units }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Rendimiento por hora</VCardTitle>
            <VTable density="compact">
              <thead><tr><th>Hora</th><th>Ventas</th><th>Monto</th></tr></thead>
              <tbody>
                <tr v-for="row in report.hourly_performance?.hourly_buckets ?? []" :key="`h-${row.bucket}`">
                  <td>{{ row.bucket }}</td><td>{{ row.sales_count }}</td><td>{{ fmtMoney(row.revenue_total) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>

        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Habitaciones/piezas</VCardTitle>
            <VTable density="compact">
              <thead><tr><th>Habitación</th><th>Servicios</th><th>Ingreso</th></tr></thead>
              <tbody>
                <tr v-for="row in report.room_performance?.top_rooms_by_revenue ?? []" :key="`r-${row.room_id}`">
                  <td>{{ row.room_name }}</td><td>{{ row.services_count }}</td><td>{{ fmtMoney(row.total_income) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mb-4">
        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Alertas</VCardTitle>
            <VCardText>
              <div class="text-subtitle-2 mb-2">Blockers</div>
              <VChip v-for="row in report.alerts?.blockers ?? []" :key="`b-${row.code}`" class="me-2 mb-2" color="error" size="small">
                {{ row.code }} ({{ row.count }})
              </VChip>
              <div class="text-subtitle-2 mt-4 mb-2">Warnings</div>
              <VChip v-for="row in report.alerts?.warnings ?? []" :key="`w-${row.code}`" class="me-2 mb-2" color="warning" size="small">
                {{ row.code }} ({{ row.count }})
              </VChip>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle>Fórmula de neto</VCardTitle>
            <VTable density="compact">
              <tbody>
                <tr><td>Ingresos brutos</td><td class="text-right">{{ fmtMoney(report.managerial_formula?.gross_revenue) }}</td></tr>
                <tr><td>Liquidaciones pagadas</td><td class="text-right">{{ fmtMoney(report.managerial_formula?.outflows_settlements) }}</td></tr>
                <tr><td>Egresos caja</td><td class="text-right">{{ fmtMoney(report.managerial_formula?.outflows_cash_expenses) }}</td></tr>
                <tr><td class="font-weight-bold">Neto estimado</td><td class="text-right font-weight-bold">{{ fmtMoney(report.managerial_formula?.net_house_estimated) }}</td></tr>
              </tbody>
            </VTable>
            <VCardText class="text-caption text-medium-emphasis">
              {{ report.managerial_formula?.formula_text }}
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <VAlert v-else-if="!loading" type="info" variant="tonal">
      No hay datos para los filtros seleccionados.
    </VAlert>
  </div>
</template>
