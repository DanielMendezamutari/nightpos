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

const rows = ref([])
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
    const payload = await apiRequest(`/reports/products/sold${reportQs.value}`, {}, auth.token.value)
    rows.value = payload.data || []
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar el reporte.'
    notify.error(message.value)
  } finally {
    loading.value = false
  }
}

async function runPdf() {
  try {
    await openPdfPreview(`/reports/products/sold/pdf${reportQs.value}`, 'Productos vendidos')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo generar el PDF.')
    closePdfPreview()
  }
}

async function downloadFromModal() {
  try {
    await downloadPdfPreview('reporte-productos-vendidos.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(load)
</script>

<template>
  <section class="content-grid">
    <article class="panel">
      <div class="panel-head">
        <h3>Productos vendidos</h3>
        <span>{{ loading ? 'Cargando...' : `${rows.length} productos` }}</span>
      </div>
      <p class="reports-lead">
        Solo órdenes pagadas. Elegí un <strong>turno de caja</strong> o un <strong>rango desde/hasta</strong> (fecha y hora).
        Si elegís turno, el rango se ignora.
      </p>

      <div v-if="needsSitePicker" class="reports-site-row form-grid" style="grid-template-columns: 1fr">
        <label>
          Sucursal
          <select v-model="sitePickerId" class="site-switcher" @change="load">
            <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
          </select>
        </label>
      </div>

      <div class="reports-filters">
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
        <button type="button" class="primary-btn" @click="runPdf">Imprimir / PDF</button>
      </div>

      <p v-if="message" class="info-text">{{ message }}</p>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r.product_id">
              <td>{{ r.sku }}</td>
              <td>{{ r.product_name }}</td>
              <td>{{ r.quantity_sold }}</td>
              <td>{{ formatMoney(r.total_amount) }}</td>
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
  margin-bottom: 14px;
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
</style>
