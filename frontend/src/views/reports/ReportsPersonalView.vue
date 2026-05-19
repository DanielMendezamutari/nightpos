<script setup>
import { onMounted, ref } from 'vue'
import PdfPreviewModal from '../../components/PdfPreviewModal.vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'
import { useReportShiftFilter } from '../../composables/useReportShiftFilter'
import { usePdfPreview } from '../../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()
const branchScope = useBranchSiteScope(auth)
const { sites, sitePickerId, needsSitePicker } = branchScope
const { shiftTurns, selectedShiftId, dateFrom, dateTo, reportQs, loadShiftTurns, shiftOptionLabel } =
  useReportShiftFilter(auth, branchScope)

const {
  pdfPreviewOpen,
  pdfPreviewLoading,
  pdfPreviewUrl,
  pdfPreviewTitle,
  openPdfPreview,
  closePdfPreview,
  downloadPdfPreview,
} = usePdfPreview(() => auth.token.value)

const ranking = ref([])
const commissions = ref([])
const staffSales = ref([])
const loading = ref(false)
const message = ref('')

function formatMoney(n) {
  return (Number(n) || 0).toLocaleString('es-AR', { maximumFractionDigits: 0 })
}

async function load() {
  loading.value = true
  message.value = ''
  try {
    await loadShiftTurns()
    const qs = reportQs.value
    const [r, c, s] = await Promise.all([
      apiRequest(`/reports/companions/ranking${qs}`, {}, auth.token.value),
      apiRequest(`/reports/waiters/commissions${qs}`, {}, auth.token.value),
      apiRequest(`/reports/staff/sales${qs}`, {}, auth.token.value),
    ])
    ranking.value = r.data || []
    commissions.value = c.data || []
    staffSales.value = s.data || []
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar los reportes.'
    notify.error(message.value)
  } finally {
    loading.value = false
  }
}

async function runPdf(path, title) {
  try {
    await openPdfPreview(`${path}${reportQs.value}`, title)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo generar el PDF.')
    closePdfPreview()
  }
}

async function downloadFromModal() {
  try {
    await downloadPdfPreview('reporte.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(load)
</script>

<template>
  <p v-if="message" class="info-text">{{ message }}</p>

  <p class="reports-lead">
    Mismo alcance para las tres tablas y los PDF: elegí <strong>turno</strong> o <strong>rango fecha/hora</strong> (órdenes / pagos según cada reporte).
  </p>

  <div v-if="needsSitePicker" class="reports-site-row form-grid panel" style="grid-template-columns: 1fr; padding: 14px">
    <label>
      Sucursal
      <select v-model="sitePickerId" class="site-switcher" @change="load">
        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
      </select>
    </label>
  </div>

  <div class="reports-filters panel-edge">
    <label class="reports-field-wide">
      Turno de caja
      <select v-model="selectedShiftId" class="site-switcher" @change="load">
        <option value="">Sin turno (filtrar por rango abajo)</option>
        <option v-for="s in shiftTurns" :key="s.id" :value="String(s.id)">{{ shiftOptionLabel(s) }}</option>
      </select>
    </label>
    <label>
      Desde (fecha y hora)
      <input v-model="dateFrom" type="datetime-local" :disabled="!!selectedShiftId" />
    </label>
    <label>
      Hasta (fecha y hora)
      <input v-model="dateTo" type="datetime-local" :disabled="!!selectedShiftId" />
    </label>
    <button type="button" class="ghost-btn" @click="load">Actualizar</button>
  </div>

  <section class="content-grid">
    <article class="panel">
      <div class="panel-head">
        <h3>Ventas por personal (mozos)</h3>
        <span>{{ loading ? '...' : `${staffSales.length}` }}</span>
      </div>
      <p class="reports-pdf-row">
        <button type="button" class="primary-btn" @click="runPdf('/reports/staff/sales/pdf', 'Ventas por personal')">
          Imprimir / PDF
        </button>
      </p>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Unidades</th>
              <th>Total líneas</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in staffSales" :key="r.user_id">
              <td>{{ r.staff_name }}</td>
              <td>{{ r.quantity_sold }}</td>
              <td>{{ formatMoney(r.total_amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <article class="panel">
      <div class="panel-head">
        <h3>Comisiones de mozos</h3>
        <span>{{ loading ? '...' : `${commissions.length}` }}</span>
      </div>
      <p class="reports-pdf-row">
        <button type="button" class="primary-btn" @click="runPdf('/reports/waiters/commissions/pdf', 'Comisiones de mozos')">
          Imprimir / PDF
        </button>
      </p>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Mozo/a</th>
              <th>Base</th>
              <th>Comisión</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="w in commissions" :key="w.waiter_id">
              <td>{{ w.waiter_name }}</td>
              <td>{{ formatMoney(w.billed_base) }}</td>
              <td>{{ formatMoney(w.commission_total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <article class="panel">
      <div class="panel-head">
        <h3>Ranking de chicas</h3>
        <span>{{ loading ? '...' : `${ranking.length}` }}</span>
      </div>
      <p class="reports-pdf-row">
        <button type="button" class="primary-btn" @click="runPdf('/reports/companions/ranking/pdf', 'Ranking de chicas')">
          Imprimir / PDF
        </button>
      </p>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Chica</th>
              <th>Bebidas</th>
              <th>Total generado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in ranking" :key="item.companion_id">
              <td>{{ item.stage_name }}</td>
              <td>{{ item.drinks_count }}</td>
              <td>{{ item.total_generated }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>
  </section>

  <PdfPreviewModal
    :open="pdfPreviewOpen"
    :title="pdfPreviewTitle"
    :loading="pdfPreviewLoading"
    :src="pdfPreviewUrl"
    iframe-title="Vista previa reporte"
    @close="closePdfPreview"
    @download="downloadFromModal"
  />
</template>

<style scoped>
.reports-lead {
  margin: 0 0 12px;
  color: var(--color-text-soft, #a8bcee);
  font-size: 0.9rem;
}

.reports-site-row {
  margin-bottom: 12px;
}

.reports-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: flex-end;
  margin-bottom: 16px;
}

.reports-filters label {
  display: grid;
  gap: 4px;
  font-size: 0.82rem;
  font-weight: 600;
}

.reports-field-wide {
  flex: 1 1 220px;
  min-width: 200px;
}

.reports-filters input[type='datetime-local'] {
  padding: 8px 10px;
  border-radius: 10px;
  font: inherit;
}

.panel-edge {
  padding: 0;
  background: transparent;
  border: none;
}

.reports-pdf-row {
  margin: 0 0 0.75rem;
}
</style>
