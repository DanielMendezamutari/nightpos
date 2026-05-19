<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import PdfPreviewModal from '../../components/PdfPreviewModal.vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'
import { usePdfPreview } from '../../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()
const { sites, sitePickerId, needsSitePicker, branchQuery, initSiteScope } = useBranchSiteScope(auth)

const {
  pdfPreviewOpen,
  pdfPreviewLoading,
  pdfPreviewUrl,
  pdfPreviewTitle,
  openPdfPreview,
  closePdfPreview,
  downloadPdfPreview,
} = usePdfPreview(() => auth.token.value)

const loading = ref(false)
const transfers = ref([])
const products = ref([])
const detail = ref(null)
const detailLoading = ref(false)

const form = reactive({
  to_site_id: '',
  document_ref: '',
  notes: '',
})

const lines = ref([{ product_id: null, quantity: 1 }])

const originSiteId = computed(() => {
  if (needsSitePicker.value) {
    return sitePickerId.value ? Number(sitePickerId.value) : null
  }
  return auth.user.value?.active_site_id ?? auth.user.value?.site_id ?? null
})

const originSiteLabel = computed(() => {
  const id = originSiteId.value
  if (!id) return '—'
  const list = auth.accessibleSites.value || []
  const row = list.find((s) => Number(s.id) === Number(id))
  return row ? `${row.code} — ${row.name}` : `#${id}`
})

const destinationSites = computed(() => {
  const origin = originSiteId.value
  const list = auth.accessibleSites.value || []
  return list.filter((s) => Number(s.id) !== Number(origin))
})

function formatWhen(iso) {
  if (!iso) return '—'
  try {
    const d = new Date(iso)
    if (Number.isNaN(d.getTime())) return String(iso)
    return d.toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return String(iso)
  }
}

function addLine() {
  lines.value.push({ product_id: null, quantity: 1 })
}

function removeLine(i) {
  if (lines.value.length <= 1) return
  lines.value.splice(i, 1)
}

async function bootstrap() {
  loading.value = true
  try {
    await initSiteScope()
    const q = branchQuery()
    const [tr, pr] = await Promise.all([
      apiRequest(`/maintenance/transfers${q}`, {}, auth.token.value),
      apiRequest(`/maintenance/products${q}`, {}, auth.token.value),
    ])
    transfers.value = tr.data || []
    products.value = pr.data || []
    if (!lines.value[0].product_id && products.value.length) {
      lines.value[0].product_id = products.value[0].id
    }
    if (!form.to_site_id && destinationSites.value.length) {
      form.to_site_id = String(destinationSites.value[0].id)
    }
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar traspasos.')
  } finally {
    loading.value = false
  }
}

async function submitTransfer() {
  const origin = originSiteId.value
  if (!origin) {
    notify.error('Indica sucursal de origen arriba.')
    return
  }
  const toId = Number(form.to_site_id)
  if (!toId || toId === origin) {
    notify.error('Elegí otra sucursal como destino.')
    return
  }
  const payloadLines = lines.value
    .filter((l) => l.product_id && Number(l.quantity) > 0)
    .map((l) => ({
      product_id: Number(l.product_id),
      quantity: Number(l.quantity),
    }))
  if (!payloadLines.length) {
    notify.error('Agrega al menos un producto y cantidad.')
    return
  }
  try {
    await apiRequest(
      `/maintenance/transfers${branchQuery()}`,
      {
        method: 'POST',
        body: JSON.stringify({
          to_site_id: toId,
          document_ref: form.document_ref || null,
          notes: form.notes || null,
          lines: payloadLines,
        }),
      },
      auth.token.value,
    )
    notify.success('Listo: el stock se movió entre sucursales.')
    form.document_ref = ''
    form.notes = ''
    lines.value = [{ product_id: products.value[0]?.id ?? null, quantity: 1 }]
    detail.value = null
    await bootstrap()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo registrar el traspaso.')
  }
}

async function openDetail(id) {
  detailLoading.value = true
  detail.value = null
  try {
    const payload = await apiRequest(`/maintenance/transfers/${id}${branchQuery()}`, {}, auth.token.value)
    detail.value = payload.data
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el detalle.')
  } finally {
    detailLoading.value = false
  }
}

function closeDetail() {
  detail.value = null
}

async function openTransferPdfPreview(transferId) {
  try {
    await openPdfPreview(
      `/maintenance/transfers/${transferId}/pdf${branchQuery()}`,
      `Traspaso #${transferId}`,
    )
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el PDF.')
    closePdfPreview()
  }
}

async function downloadTransferPdfFromModal() {
  try {
    await downloadPdfPreview('traspaso.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(bootstrap)
</script>

<template>
  <section class="panel maint-traspasos-scope">
    <div class="panel-head">
      <h3>Traspasos</h3>
      <span>{{ loading ? 'Cargando…' : 'Salida en origen, ingreso en destino' }}</span>
    </div>

    <p class="traspaso-lead">
      Lo que sacás de <strong>esta sucursal</strong> aparece en la que elijas como destino, en el mismo acto.
    </p>

    <details class="traspaso-help">
      <summary>¿Cómo funciona?</summary>
      <ol>
        <li>La sucursal activa (barra superior) es siempre el <strong>origen</strong>.</li>
        <li>Elegís <strong>destino</strong> y qué productos van, con cantidades.</li>
        <li>Al guardar, el sistema descuenta acá, suma allá y deja registro para auditoría y kardex.</li>
      </ol>
    </details>

    <div v-if="needsSitePicker" class="field-block traspaso-origin-pick">
      <span>Origen</span>
      <select v-model.number="sitePickerId" @change="bootstrap">
        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
      </select>
    </div>

    <div v-if="!needsSitePicker" class="traspaso-origin-strip" aria-label="Sucursal de origen">
      <span class="traspaso-origin-label">Origen</span>
      <span class="traspaso-origin-value">{{ originSiteLabel }}</span>
    </div>

    <div class="content-grid">
      <article class="panel">
        <div class="panel-head">
          <h4>Nuevo envío</h4>
        </div>
        <form class="maint-field-grid" @submit.prevent="submitTransfer">
          <div class="field-block field-block--full">
            <span>Destino</span>
            <select v-model="form.to_site_id" required>
              <option disabled value="">Elegir sucursal</option>
              <option v-for="s in destinationSites" :key="s.id" :value="String(s.id)">
                {{ s.code }} — {{ s.name }}
              </option>
            </select>
            <p v-if="!destinationSites.length" class="field-hint">Necesitás acceso a otra sucursal para trasladar stock.</p>
          </div>

          <div class="field-block field-block--full traspaso-optional">
            <span class="traspaso-optional-title">Referencia (opcional)</span>
            <div class="traspaso-optional-row">
              <input v-model="form.document_ref" type="text" placeholder="Remito o número interno" />
              <input v-model="form.notes" type="text" placeholder="Nota breve" />
            </div>
          </div>

          <div class="field-block field-block--full maint-lines-head">
            <span>Productos</span>
            <button type="button" class="ghost-btn" @click="addLine">Añadir</button>
          </div>
          <div v-for="(line, idx) in lines" :key="idx" class="maint-purchase-line">
            <select v-model.number="line.product_id" required>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.sku }} — {{ p.name }}</option>
            </select>
            <input v-model.number="line.quantity" type="number" min="1" placeholder="Cant." />
            <button type="button" class="ghost-btn" :disabled="lines.length <= 1" @click="removeLine(idx)">
              Quitar
            </button>
          </div>

          <div class="maint-form-actions">
            <button type="submit" class="primary-btn" :disabled="!destinationSites.length">Confirmar traspaso</button>
          </div>
        </form>
      </article>

      <article class="panel">
        <div class="panel-head">
          <h4>Últimos traspasos</h4>
          <span>{{ transfers.length }}</span>
        </div>
        <div v-if="!transfers.length" class="admin-empty-card">
          <p>Todavía no hay movimientos en esta sucursal.</p>
        </div>
        <div v-else class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Cuándo</th>
                <th>Ruta</th>
                <th>Ref.</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transfers" :key="t.id">
                <td>{{ formatWhen(t.transferred_at) }}</td>
                <td>
                  <span class="traspaso-route">{{ t.from_site_name }} → {{ t.to_site_name }}</span>
                </td>
                <td>{{ t.document_ref || '—' }}</td>
                <td>
                  <button type="button" class="ghost-btn" @click="openDetail(t.id)">Ver</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </div>

    <div v-if="detail || detailLoading" class="maint-detail-overlay" @click.self="closeDetail">
      <article class="panel maint-detail-card" @click.stop>
        <div class="panel-head">
          <h4>Traspaso</h4>
          <button type="button" class="ghost-btn" @click="closeDetail">Cerrar</button>
        </div>
        <p v-if="detailLoading" class="traspaso-detail-loading">Cargando…</p>
        <template v-else-if="detail">
          <div class="traspaso-detail-pdf-row">
            <button type="button" class="ghost-btn traspaso-detail-pdf-btn" @click="openTransferPdfPreview(detail.transfer.id)">
              Ver PDF
            </button>
          </div>
          <p class="traspaso-detail-route">
            {{ detail.transfer.from_site_name }} → {{ detail.transfer.to_site_name }}
          </p>
          <p v-if="detail.transfer.document_ref || detail.transfer.notes" class="traspaso-detail-extra">
            <template v-if="detail.transfer.document_ref">Ref. {{ detail.transfer.document_ref }}</template>
            <template v-if="detail.transfer.document_ref && detail.transfer.notes"> · </template>
            <template v-if="detail.transfer.notes">{{ detail.transfer.notes }}</template>
          </p>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>SKU</th>
                  <th>Producto</th>
                  <th>Cant.</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ln in detail.lines" :key="ln.id">
                  <td>{{ ln.sku }}</td>
                  <td>{{ ln.product_name }}</td>
                  <td>{{ ln.quantity }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </article>
    </div>

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa del traspaso"
      @close="closePdfPreview"
      @download="downloadTransferPdfFromModal"
    />
  </section>
</template>

<style scoped>
.traspaso-lead {
  margin: 0 0 0.75rem;
  font-size: 0.95rem;
  line-height: 1.45;
  color: var(--color-text-soft, #5a5a5a);
}

.traspaso-help {
  margin: 0 0 1.25rem;
  padding: 0.65rem 0.85rem;
  border-radius: 8px;
  background: var(--panel-muted-bg, rgba(0, 0, 0, 0.04));
  border: 1px solid var(--border-subtle, rgba(0, 0, 0, 0.08));
  font-size: 0.88rem;
  line-height: 1.5;
}

.traspaso-help summary {
  cursor: pointer;
  font-weight: 600;
  color: var(--color-text, inherit);
  list-style-position: outside;
}

.traspaso-help summary::-webkit-details-marker {
  color: var(--color-muted, #888);
}

.traspaso-help ol {
  margin: 0.65rem 0 0 1.1rem;
  padding: 0;
}

.traspaso-help li {
  margin-bottom: 0.35rem;
}

.traspaso-help li:last-child {
  margin-bottom: 0;
}

.traspaso-origin-pick {
  max-width: 22rem;
  margin-bottom: 0.75rem;
}

.traspaso-origin-strip {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem 1rem;
  margin-bottom: 1rem;
  padding: 0.6rem 0.85rem;
  border-radius: 8px;
  border: 1px solid var(--border-subtle, rgba(0, 0, 0, 0.1));
  background: var(--panel-stack-bg, rgba(255, 255, 255, 0.5));
}

.traspaso-origin-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-muted, #777);
}

.traspaso-origin-value {
  font-weight: 600;
  font-size: 0.95rem;
}

.traspaso-optional {
  margin-top: 0.25rem;
}

.traspaso-optional-title {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.85rem;
  color: var(--color-muted, #666);
}

.traspaso-optional-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

@media (max-width: 640px) {
  .traspaso-optional-row {
    grid-template-columns: 1fr;
  }
}

.traspaso-route {
  white-space: normal;
  word-break: break-word;
}

.traspaso-detail-loading {
  margin: 0.5rem 0;
  color: var(--color-muted, #666);
}

.traspaso-detail-pdf-row {
  margin-bottom: 0.65rem;
}

.traspaso-detail-pdf-btn {
  font-weight: 600;
}

.traspaso-detail-route {
  margin: 0 0 0.35rem;
  font-size: 1.05rem;
  font-weight: 600;
}

.traspaso-detail-extra {
  margin: 0 0 1rem;
  font-size: 0.9rem;
  color: var(--color-muted, #666);
}

.maint-detail-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.maint-detail-card {
  max-width: 34rem;
  width: 100%;
  max-height: 90vh;
  overflow: auto;
}
</style>
