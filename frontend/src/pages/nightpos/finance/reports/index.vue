<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import ProductReconciliationPanel from '@/components/nightpos/reports/ProductReconciliationPanel.vue'
import {
  fetchCashReport,
  fetchDailyReport,
  fetchProductReconciliation,
  fetchRoomsReport,
  fetchSalesReport,
  fetchServicesReport,
  fetchSettlementsReport,
} from '@/api/reports'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'reports.access' } })

const { notify } = useNightPosNotify()

// ─── State ─────────────────────────────────────────────────────────────────────
const activeTab   = ref('daily')
const loading     = ref(false)
const exporting   = ref(false)
const filters     = ref({ dateFrom: '', dateTo: '', officialShiftId: '', cashSessionId: '', waiterUserId: '' })

const daily       = ref(null)
const sales       = ref(null)
const cash        = ref(null)
const services    = ref(null)
const settlements = ref(null)
const rooms       = ref(null)
const products    = ref(null)

const tabs = [
  { value: 'daily',       title: 'Resumen diario', icon: 'ri-dashboard-line' },
  { value: 'sales',       title: 'Ventas',          icon: 'ri-shopping-bag-line' },
  { value: 'products',    title: 'Productos',       icon: 'ri-goblet-line' },
  { value: 'cash',        title: 'Caja',            icon: 'ri-safe-line' },
  { value: 'services',    title: 'Servicios',       icon: 'ri-hotel-bed-line' },
  { value: 'settlements', title: 'Liquidaciones',   icon: 'ri-money-dollar-circle-line' },
  { value: 'rooms',       title: 'Habitaciones',    icon: 'ri-home-line' },
]

// ─── Load ────────────────────────────────────────────────────────────────────
const getFilters = () => ({
  dateFrom: filters.value.dateFrom || undefined,
  dateTo:   filters.value.dateTo   || undefined,
  officialShiftId: filters.value.officialShiftId || undefined,
  cashSessionId: filters.value.cashSessionId || undefined,
  waiterUserId: filters.value.waiterUserId || undefined,
})

const loaders = {
  daily:       () => fetchDailyReport(getFilters()).then(d => { daily.value = d }),
  sales:       () => fetchSalesReport(getFilters()).then(d => { sales.value = d }),
  products:    () => fetchProductReconciliation(getFilters()).then(d => { products.value = d }),
  cash:        () => fetchCashReport(getFilters()).then(d => { cash.value = d }),
  services:    () => fetchServicesReport(getFilters()).then(d => { services.value = d }),
  settlements: () => fetchSettlementsReport(getFilters()).then(d => { settlements.value = d }),
  rooms:       () => fetchRoomsReport(getFilters()).then(d => { rooms.value = d }),
}

async function load(options = {}) {
  if (loading.value)
    return

  loading.value = true
  try {
    await loaders[activeTab.value]?.()
    if (options.toast)
      notify('Reporte actualizado', 'success')
  }
  catch (e) {
    notify(getApiErrorMessage(e), 'error')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = () => load({ toast: true })

watch(activeTab, () => load())
onMounted(() => load())

// ─── Formatters ──────────────────────────────────────────────────────────────
const fmtMoney = v => `${Number(v ?? 0).toFixed(2)} BOB`
const fmtDate  = v => v ? new Date(v).toLocaleString('es-BO') : '-'
const methodLabel = { CASH: 'Efectivo', QR: 'QR', CARD: 'Tarjeta', MIXED: 'Mixto' }

// ─── CSV Export (basic) ───────────────────────────────────────────────────────
async function exportCsv(rows, filename) {
  if (exporting.value)
    return

  if (!rows || !rows.length) {
    notify('No hay datos para exportar', 'warning')
    return
  }

  exporting.value = true
  try {
    const headers = Object.keys(rows[0])
    const lines   = [
      headers.join(','),
      ...rows.map(r => headers.map(h => JSON.stringify(r[h] ?? '')).join(',')),
    ]
    const blob = new Blob([lines.join('\n')], { type: 'text/csv' })
    const url  = URL.createObjectURL(blob)
    const a    = document.createElement('a')
    a.href = url
    a.download = `${filename}.csv`
    a.click()
    URL.revokeObjectURL(url)
    notify(`Exportado: ${filename}.csv`, 'success')
  }
  finally {
    exporting.value = false
  }
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Reportes"
      subtitle="Estadísticas operativas del turno y período seleccionado."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Reportes', disabled: true },
      ]"
    />

    <!-- Filtros globales -->
    <VCard class="mb-4">
      <VCardText>
        <VRow dense>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="filters.dateFrom"
              label="Desde"
              type="date"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="filters.dateTo"
              label="Hasta"
              type="date"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="filters.officialShiftId"
              label="ID Turno"
              type="number"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="filters.cashSessionId"
              label="ID Caja"
              type="number"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="filters.waiterUserId"
              label="ID Garzón"
              type="number"
              density="compact"
              hide-details
            />
          </VCol>
        </VRow>
        <div class="mt-3 d-flex gap-2">
          <VBtn
            size="small"
            prepend-icon="ri-search-line"
            :loading="loading"
            :disabled="loading"
            @click="applyFilters"
          >
            Aplicar filtros
          </VBtn>
          <VBtn
            size="small"
            variant="tonal"
            prepend-icon="ri-refresh-line"
            :disabled="loading"
            @click="filters = { dateFrom: '', dateTo: '', officialShiftId: '', cashSessionId: '', waiterUserId: '' }; applyFilters()"
          >
            Limpiar
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabs -->
    <VTabs
      v-model="activeTab"
      class="mb-4"
      show-arrows
    >
      <VTab
        v-for="tab in tabs"
        :key="tab.value"
        :value="tab.value"
        :prepend-icon="tab.icon"
      >
        {{ tab.title }}
      </VTab>
    </VTabs>

    <VProgressLinear v-if="loading" indeterminate class="mb-4" />

    <VWindow v-model="activeTab">
      <!-- ─── DAILY ─── -->
      <VWindowItem value="daily">
        <template v-if="daily">
          <VRow class="mb-4">
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Total ventas</div>
                <div class="text-h6 font-weight-bold text-success">{{ fmtMoney(daily.sales?.total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Efectivo</div>
                <div class="text-h6">{{ fmtMoney(daily.sales?.total_cash) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">QR</div>
                <div class="text-h6 text-info">{{ fmtMoney(daily.sales?.total_qr) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Tarjeta</div>
                <div class="text-h6 text-primary">{{ fmtMoney(daily.sales?.total_card) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Total servicios</div>
                <div class="text-h6 text-secondary">{{ fmtMoney(daily.services?.total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Liquidaciones pagadas</div>
                <div class="text-h6 text-success">{{ fmtMoney(daily.settlements?.paid) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Liquidaciones pendientes</div>
                <div class="text-h6" :class="Number(daily.settlements?.pending) > 0 ? 'text-error' : 'text-success'">
                  {{ fmtMoney(daily.settlements?.pending) }}
                </div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Efectivo esperado</div>
                <div class="text-h6 text-warning">{{ fmtMoney(daily.cash?.expected_cash) }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>
          <VRow>
            <VCol cols="6" sm="4" md="3">
              <VCard variant="tonal" color="info"><VCardText>
                <div class="text-caption">Manillas</div>
                <div class="text-body-1 font-weight-bold">{{ daily.services?.bracelets_count ?? 0 }} | {{ fmtMoney(daily.services?.bracelets_total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard variant="tonal" color="secondary"><VCardText>
                <div class="text-caption">Piezas</div>
                <div class="text-body-1 font-weight-bold">{{ daily.services?.room_services_count ?? 0 }} | {{ fmtMoney(daily.services?.room_services_total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard variant="tonal" color="primary"><VCardText>
                <div class="text-caption">Shows</div>
                <div class="text-body-1 font-weight-bold">{{ daily.services?.shows_count ?? 0 }} | {{ fmtMoney(daily.services?.shows_total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="4" md="3">
              <VCard variant="tonal" color="success"><VCardText>
                <div class="text-caption">Habitaciones usadas</div>
                <div class="text-body-1 font-weight-bold">{{ daily.rooms?.used ?? 0 }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>
        </template>
        <VAlert v-else-if="!loading" type="info" variant="tonal">
          No hay datos para los filtros seleccionados.
        </VAlert>
      </VWindowItem>

      <!-- ─── VENTAS ─── -->
      <VWindowItem value="sales">
        <template v-if="sales">
          <VRow class="mb-4">
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Total ventas</div>
                <div class="text-h6 font-weight-bold text-success">{{ fmtMoney(sales.totals?.total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption text-medium-emphasis">Cantidad</div>
                <div class="text-h6">{{ sales.totals?.count ?? 0 }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>
          <div class="d-flex justify-end mb-2">
            <VBtn size="small" variant="tonal" prepend-icon="ri-file-download-line" :loading="exporting" :disabled="exporting" @click="exportCsv(sales.sales, 'ventas')">
              Exportar CSV
            </VBtn>
          </div>
          <VTable density="compact">
            <thead>
              <tr>
                <th>#</th>
                <th>Tipo</th>
                <th>Cajero</th>
                <th>Método</th>
                <th>Total</th>
                <th>Items</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in sales.sales" :key="s.id">
                <td>{{ s.sale_number }}</td>
                <td><VChip size="x-small" :color="s.type === 'order' ? 'primary' : 'secondary'">{{ s.type === 'order' ? 'Comanda' : 'Directa' }}</VChip></td>
                <td>{{ s.cashier }}</td>
                <td><VChip size="x-small">{{ methodLabel[s.payment_mode] ?? s.payment_mode }}</VChip></td>
                <td class="font-weight-bold">{{ fmtMoney(s.total) }}</td>
                <td>{{ s.items?.length ?? 0 }}</td>
                <td class="text-caption">{{ fmtDate(s.paid_at) }}</td>
              </tr>
            </tbody>
          </VTable>
          <div v-if="!sales.sales?.length" class="text-center py-4 text-medium-emphasis">
            Sin ventas para el período.
          </div>
        </template>
      </VWindowItem>

      <!-- ─── PRODUCTOS ─── -->
      <VWindowItem value="products">
        <template v-if="products">
          <div class="d-flex justify-end mb-2">
            <VBtn size="small" variant="tonal" prepend-icon="ri-file-download-line" :loading="exporting" :disabled="exporting" @click="exportCsv(products.comparison, 'conciliacion-productos')">
              Exportar CSV
            </VBtn>
          </div>
          <ProductReconciliationPanel :data="products" :loading="loading" title="" />
        </template>
        <VAlert v-else-if="!loading" type="info" variant="tonal">
          No hay datos para los filtros seleccionados.
        </VAlert>
      </VWindowItem>

      <!-- ─── CAJA ─── -->
      <VWindowItem value="cash">
        <template v-if="cash">
          <VRow class="mb-4">
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Cajas abiertas</div>
                <div class="text-h6" :class="cash.open_count > 0 ? 'text-warning' : 'text-success'">{{ cash.open_count }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Cajas cerradas</div>
                <div class="text-h6">{{ cash.closed_count }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>
          <div
            v-for="session in cash.sessions"
            :key="session.id"
            class="mb-4"
          >
            <VCard>
              <VCardTitle class="d-flex align-center justify-space-between">
                <span>{{ session.register }}</span>
                <VChip :color="session.status === 'OPEN' ? 'warning' : 'success'" size="small">{{ session.status }}</VChip>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="6" sm="3">
                    <div class="text-caption">Apertura</div>
                    <div>{{ fmtMoney(session.opening_amount) }}</div>
                  </VCol>
                  <VCol cols="6" sm="3">
                    <div class="text-caption">Ventas Efectivo</div>
                    <div>{{ fmtMoney(session.sales_cash) }}</div>
                  </VCol>
                  <VCol cols="6" sm="3">
                    <div class="text-caption">Ventas QR</div>
                    <div>{{ fmtMoney(session.sales_qr) }}</div>
                  </VCol>
                  <VCol cols="6" sm="3">
                    <div class="text-caption">Ventas Tarjeta</div>
                    <div>{{ fmtMoney(session.sales_card) }}</div>
                  </VCol>
                  <VCol cols="6" sm="3">
                    <div class="text-caption">Ingresos manuales</div>
                    <div class="text-success">{{ fmtMoney(session.manual_income) }}</div>
                  </VCol>
                  <VCol cols="6" sm="3">
                    <div class="text-caption">Egresos manuales</div>
                    <div class="text-error">{{ fmtMoney(session.manual_expense) }}</div>
                  </VCol>
                  <VCol v-if="session.status === 'CLOSED'" cols="6" sm="3">
                    <div class="text-caption">Diferencia</div>
                    <div :class="Math.abs(Number(session.difference)) > 0 ? 'text-error' : 'text-success'">
                      {{ fmtMoney(session.difference) }}
                    </div>
                  </VCol>
                </VRow>
                <div class="text-caption mt-2 text-medium-emphasis">
                  Apertura: {{ fmtDate(session.opened_at) }}
                  <span v-if="session.closed_at"> · Cierre: {{ fmtDate(session.closed_at) }}</span>
                </div>
              </VCardText>
            </VCard>
          </div>
          <div v-if="!cash.sessions?.length" class="text-center py-4 text-medium-emphasis">
            Sin sesiones de caja.
          </div>
        </template>
      </VWindowItem>

      <!-- ─── SERVICIOS ─── -->
      <VWindowItem value="services">
        <template v-if="services">
          <VRow class="mb-4">
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Total servicios</div>
                <div class="text-h6 font-weight-bold text-secondary">{{ fmtMoney(services.totals?.grand_total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Monto casa</div>
                <div class="text-h6 text-primary">{{ fmtMoney(services.totals?.house_total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Monto chica</div>
                <div class="text-h6 text-success">{{ fmtMoney(services.totals?.girl_total) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Monto limpieza</div>
                <div class="text-h6">{{ fmtMoney(services.totals?.cleaning_total) }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>

          <!-- Piezas -->
          <div class="text-subtitle-1 mb-2 font-weight-bold">Piezas ({{ services.room_services?.length ?? 0 }})</div>
          <VTable density="compact" class="mb-4">
            <thead><tr>
              <th>Chica</th><th>Habitación</th><th>Total</th><th>Casa</th><th>Chica</th><th>Limpieza</th><th>Estado</th><th>Inicio</th>
            </tr></thead>
            <tbody>
              <tr v-for="rs in services.room_services" :key="rs.id">
                <td>{{ rs.girl }}</td>
                <td>{{ rs.room }}</td>
                <td>{{ fmtMoney(rs.total_amount) }}</td>
                <td class="text-caption">{{ fmtMoney(rs.house_amount) }}</td>
                <td class="text-caption text-success">{{ fmtMoney(rs.girl_amount) }}</td>
                <td class="text-caption">{{ fmtMoney(rs.cleaning_amount) }}</td>
                <td><VChip size="x-small" :color="rs.status === 'FINISHED' ? 'success' : rs.status === 'DUE' ? 'error' : 'warning'">{{ rs.status }}</VChip></td>
                <td class="text-caption">{{ fmtDate(rs.started_at) }}</td>
              </tr>
            </tbody>
          </VTable>

          <!-- Manillas -->
          <div class="text-subtitle-1 mb-2 font-weight-bold">Manillas ({{ services.bracelets?.length ?? 0 }})</div>
          <VTable density="compact" class="mb-4">
            <thead><tr><th>Chica</th><th>Garzón</th><th>Cantidad</th><th>Total</th><th>Método</th><th>Fecha</th></tr></thead>
            <tbody>
              <tr v-for="b in services.bracelets" :key="b.id">
                <td>{{ b.girl }}</td><td>{{ b.waiter }}</td><td>{{ b.quantity }}</td>
                <td>{{ fmtMoney(b.total_amount) }}</td>
                <td><VChip size="x-small">{{ methodLabel[b.payment_method] ?? b.payment_method }}</VChip></td>
                <td class="text-caption">{{ fmtDate(b.registered_at) }}</td>
              </tr>
            </tbody>
          </VTable>

          <!-- Shows -->
          <div class="text-subtitle-1 mb-2 font-weight-bold">Shows ({{ services.shows?.length ?? 0 }})</div>
          <VTable density="compact">
            <thead><tr><th>Chica</th><th>Tipo</th><th>Total</th><th>Método</th><th>Fecha</th></tr></thead>
            <tbody>
              <tr v-for="s in services.shows" :key="s.id">
                <td>{{ s.girl }}</td><td>{{ s.show_type }}</td>
                <td>{{ fmtMoney(s.total_amount) }}</td>
                <td><VChip size="x-small">{{ methodLabel[s.payment_method] ?? s.payment_method }}</VChip></td>
                <td class="text-caption">{{ fmtDate(s.registered_at) }}</td>
              </tr>
            </tbody>
          </VTable>

          <div class="d-flex justify-end mt-3">
            <VBtn size="small" variant="tonal" prepend-icon="ri-file-download-line" :loading="exporting" :disabled="exporting" @click="exportCsv([...services.room_services, ...services.bracelets, ...services.shows], 'servicios')">
              Exportar CSV
            </VBtn>
          </div>
        </template>
      </VWindowItem>

      <!-- ─── LIQUIDACIONES ─── -->
      <VWindowItem value="settlements">
        <template v-if="settlements">
          <VRow class="mb-4">
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Total generado</div>
                <div class="text-h6">{{ fmtMoney(settlements.totals?.total_generated) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Total pagado</div>
                <div class="text-h6 text-success">{{ fmtMoney(settlements.totals?.total_paid) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Total pendiente</div>
                <div class="text-h6" :class="Number(settlements.totals?.total_pending) > 0 ? 'text-error' : 'text-success'">{{ fmtMoney(settlements.totals?.total_pending) }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Pendientes</div>
                <div class="text-h6" :class="(settlements.totals?.pending_count ?? 0) > 0 ? 'text-error' : 'text-success'">{{ settlements.totals?.pending_count ?? 0 }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>
          <div class="d-flex justify-end mb-2">
            <VBtn size="small" variant="tonal" prepend-icon="ri-file-download-line" :loading="exporting" :disabled="exporting" @click="exportCsv(settlements.settlements, 'liquidaciones')">
              Exportar CSV
            </VBtn>
          </div>
          <VTable density="compact">
            <thead>
              <tr><th>Personal</th><th>Rol</th><th>Tipo</th><th>Total</th><th>Estado</th><th>Pagado por</th><th>Fecha pago</th></tr>
            </thead>
            <tbody>
              <tr v-for="s in settlements.settlements" :key="s.id">
                <td>{{ s.staff }}</td>
                <td><VChip size="x-small">{{ s.staff_role }}</VChip></td>
                <td class="text-caption">{{ s.settlement_type }}</td>
                <td class="font-weight-bold">{{ fmtMoney(s.total_amount) }}</td>
                <td><VChip size="x-small" :color="s.status === 'PAID' ? 'success' : 'warning'">{{ s.status === 'PAID' ? 'Pagado' : 'Pendiente' }}</VChip></td>
                <td>{{ s.paid_by }}</td>
                <td class="text-caption">{{ s.paid_at ? fmtDate(s.paid_at) : '-' }}</td>
              </tr>
            </tbody>
          </VTable>
          <div v-if="!settlements.settlements?.length" class="text-center py-4 text-medium-emphasis">Sin liquidaciones.</div>
        </template>
      </VWindowItem>

      <!-- ─── HABITACIONES ─── -->
      <VWindowItem value="rooms">
        <template v-if="rooms">
          <VRow class="mb-4">
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Habitaciones</div>
                <div class="text-h6">{{ rooms.totals?.rooms_count ?? 0 }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Usadas</div>
                <div class="text-h6 text-success">{{ rooms.totals?.rooms_used ?? 0 }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Total servicios</div>
                <div class="text-h6 text-secondary">{{ rooms.totals?.total_services ?? 0 }}</div>
              </VCardText></VCard>
            </VCol>
            <VCol cols="6" sm="3">
              <VCard><VCardText>
                <div class="text-caption">Total ingresos</div>
                <div class="text-h6 text-primary">{{ fmtMoney(rooms.totals?.total_income) }}</div>
              </VCardText></VCard>
            </VCol>
          </VRow>
          <div class="d-flex justify-end mb-2">
            <VBtn size="small" variant="tonal" prepend-icon="ri-file-download-line" :loading="exporting" :disabled="exporting" @click="exportCsv(rooms.rooms, 'habitaciones')">
              Exportar CSV
            </VBtn>
          </div>
          <VTable density="compact">
            <thead>
              <tr><th>Código</th><th>Nombre</th><th>Estado</th><th>Servicios</th><th>Ingresos</th><th>Duración prom.</th><th>Limpiezas</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in rooms.rooms" :key="r.id">
                <td>{{ r.code }}</td>
                <td>{{ r.name }}</td>
                <td>
                  <VChip
                    size="x-small"
                    :color="r.status === 'AVAILABLE' ? 'success' : r.status === 'OCCUPIED' ? 'warning' : r.status === 'CLEANING' ? 'info' : 'default'"
                  >
                    {{ r.status }}
                  </VChip>
                </td>
                <td>{{ r.services_count }}</td>
                <td class="font-weight-bold">{{ fmtMoney(r.total_income) }}</td>
                <td>{{ r.avg_duration ? `${r.avg_duration} min` : '-' }}</td>
                <td>{{ r.cleanings }}</td>
              </tr>
            </tbody>
          </VTable>
        </template>
      </VWindowItem>
    </VWindow>
  </div>
</template>
