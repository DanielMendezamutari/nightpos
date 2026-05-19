<script setup>
import { computed, reactive, ref, watch } from 'vue'
import PdfPreviewModal from '../../components/PdfPreviewModal.vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'
import { usePdfPreview } from '../../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()
const { branchQuery, initSiteScope } = useBranchSiteScope(auth)

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
const products = ref([])
const companions = ref([])
const waiterTables = ref([])
const sessions = ref([])
const orders = ref([])
const selectedOrder = ref(null)
const detail = ref(null)
const selectedTableId = ref(null)
let bootstrapSeq = 0

const orderForm = reactive({ customer_session_id: '' })
const itemForm = reactive({ product_id: '', quantity: 1, consumption_type: 'solo', companion_id: '' })
const companionQuery = ref('')

const selectedTable = computed(() => waiterTables.value.find((t) => Number(t.table_id) === Number(selectedTableId.value)) || null)
const siteChip = computed(() => {
  const d = auth.activeSiteDisplay.value
  return d ? d.code : ''
})
const openSessions = computed(() => sessions.value.filter((s) => s.status === 'open'))
const filteredOrders = computed(() => {
  if (!selectedTable.value) return orders.value
  return orders.value.filter((o) => (o.table_code || '') === (selectedTable.value.table_code || ''))
})
const filteredCompanions = computed(() => {
  const q = companionQuery.value.trim().toLowerCase()
  if (!q) return companions.value.slice(0, 8)
  return companions.value
    .filter((c) => String(c.stage_name || '').toLowerCase().includes(q))
    .slice(0, 8)
})
const companionDatalistId = 'mesero-companion-options'

async function openOrderPdf(orderId) {
  try {
    await openPdfPreview(`/pos/orders/${orderId}/pdf${branchQuery()}`, `Orden POS #${orderId}`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el PDF.')
    closePdfPreview()
  }
}

async function downloadOrderPdfFromModal() {
  try {
    await downloadPdfPreview('orden-pos.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

function formatMoney(v) { return (Number(v) || 0).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }
function formatWhen(iso) {
  if (!iso) return '—'
  try { return new Date(iso).toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' }) } catch { return iso }
}

async function bootstrap() {
  const seq = ++bootstrapSeq
  loading.value = true
  try {
    await initSiteScope()
    await auth.refreshMe()
    if (seq !== bootstrapSeq) return

    const t = await apiRequest('/waiter/tables', {}, auth.token.value)
    if (seq !== bootstrapSeq) return

    const metaSite = t.meta?.site_id != null ? Number(t.meta.site_id) : null
    if (metaSite && metaSite !== Number(auth.resolvedActiveSiteId.value)) {
      await auth.setActiveSite(metaSite)
      if (seq !== bootstrapSeq) return
    }

    const [p, s, o, c] = await Promise.all([
      apiRequest('/products', {}, auth.token.value),
      apiRequest('/pos/sessions', {}, auth.token.value),
      apiRequest('/pos/orders', {}, auth.token.value),
      apiRequest('/companions', {}, auth.token.value),
    ])
    if (seq !== bootstrapSeq) return

    products.value = p.data || []
    companions.value = c.data || []
    waiterTables.value = t.data || []
    sessions.value = s.data || []
    orders.value = o.data || []

    if (!selectedTableId.value && waiterTables.value.length) {
      selectedTableId.value = waiterTables.value[0].table_id
    }

    if (selectedTable.value?.open_session_id) {
      orderForm.customer_session_id = selectedTable.value.open_session_id
    }
  } catch (e) {
    if (seq !== bootstrapSeq) return
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar POS mesero.')
  } finally {
    if (seq === bootstrapSeq) loading.value = false
  }
}

async function openSessionForTable(table) {
  try {
    const payload = await apiRequest('/pos/sessions', {
      method: 'POST',
      body: JSON.stringify({ site_table_id: Number(table.table_id) }),
    }, auth.token.value)

    selectedTableId.value = table.table_id
    orderForm.customer_session_id = payload.data.id
    notify.success(`Mesa ${table.table_code} abierta.`)
    await bootstrap()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo abrir sesión de mesa.')
  }
}

async function createOrder() {
  if (!orderForm.customer_session_id) return notify.error('Selecciona o abre una mesa primero.')
  try {
    const payload = await apiRequest('/pos/orders', {
      method: 'POST',
      body: JSON.stringify({ customer_session_id: Number(orderForm.customer_session_id) }),
    }, auth.token.value)

    selectedOrder.value = payload.data.id
    notify.success(`Orden #${payload.data.id} creada.`)
    await bootstrap()
    await loadOrderDetail(payload.data.id)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo crear orden.')
  }
}

async function loadOrderDetail(orderId) {
  selectedOrder.value = Number(orderId)
  try {
    const payload = await apiRequest(`/pos/orders/${selectedOrder.value}`, {}, auth.token.value)
    detail.value = payload.data
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar detalle de orden.')
  }
}

async function addItem() {
  if (!selectedOrder.value) return notify.error('Selecciona una orden.')
  if (itemForm.consumption_type === 'with_companion' && !itemForm.companion_id) {
    return notify.error('Selecciona una chica de la lista.')
  }
  try {
    await apiRequest(`/pos/orders/${selectedOrder.value}/items`, {
      method: 'POST',
      body: JSON.stringify({
        product_id: Number(itemForm.product_id),
        quantity: Number(itemForm.quantity),
        consumption_type: itemForm.consumption_type,
        companion_id: itemForm.companion_id ? Number(itemForm.companion_id) : null,
      }),
    }, auth.token.value)

    itemForm.quantity = 1
    itemForm.companion_id = ''
    companionQuery.value = ''
    notify.success('Item agregado.')
    await bootstrap()
    await loadOrderDetail(selectedOrder.value)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo agregar item.')
  }
}

function pickCompanion(c) {
  itemForm.companion_id = String(c.id)
  companionQuery.value = c.stage_name
}

function resolveCompanionFromQuery() {
  const q = companionQuery.value.trim().toLowerCase()
  if (!q) {
    itemForm.companion_id = ''
    return
  }

  const exact = companions.value.find((c) => String(c.stage_name || '').trim().toLowerCase() === q)
  if (exact) {
    itemForm.companion_id = String(exact.id)
    companionQuery.value = exact.stage_name
    return
  }

  const starts = companions.value.filter((c) => String(c.stage_name || '').toLowerCase().startsWith(q))
  if (starts.length === 1) {
    pickCompanion(starts[0])
    return
  }

  itemForm.companion_id = ''
}

watch(
  () => auth.resolvedActiveSiteId.value,
  () => {
    bootstrap()
  },
  { immediate: true },
)

watch(
  () => itemForm.consumption_type,
  (v) => {
    if (v !== 'with_companion') {
      itemForm.companion_id = ''
      companionQuery.value = ''
    }
  },
)

watch(companionQuery, () => {
  if (itemForm.consumption_type === 'with_companion') {
    resolveCompanionFromQuery()
  }
})
</script>

<template>
  <section class="mesero">
    <header class="mesero-head">
      <h1 class="mesero-title">Mesas</h1>
      <span v-if="siteChip" class="mesero-site">{{ siteChip }}</span>
      <span class="mesero-count">{{ loading ? '…' : waiterTables.length }}</span>
    </header>

    <div v-if="waiterTables.length" class="mesero-grid">
      <button
        v-for="t in waiterTables"
        :key="t.table_id"
        type="button"
        class="mesero-tile"
        :class="{ 'mesero-tile--on': t.occupied, 'mesero-tile--pick': Number(selectedTableId) === Number(t.table_id) }"
        @click="selectedTableId = t.table_id; orderForm.customer_session_id = t.open_session_id || ''"
      >
        <span class="mesero-tile-code">{{ t.table_code }}</span>
        <span class="mesero-tile-sub">{{ t.occupied ? formatWhen(t.opened_at) : 'Libre' }}</span>
      </button>
    </div>

    <p v-else-if="!loading" class="mesero-empty">
      Sin mesas en esta sucursal. Pedí en administración que te asignen o cambiá de sede arriba.
    </p>

    <div v-if="selectedTable" class="mesero-panel">
      <button
        type="button"
        class="mesero-open"
        :disabled="selectedTable.occupied"
        @click="openSessionForTable(selectedTable)"
      >
        Abrir {{ selectedTable.table_code }}
      </button>
    </div>

    <details class="mesero-details">
      <summary>Pedidos</summary>
      <div class="mesero-details-body">
        <form class="mesero-form" @submit.prevent="createOrder">
          <label class="mesero-label">Sesión</label>
          <select v-model.number="orderForm.customer_session_id" class="mesero-input" required>
            <option value="" disabled>Elegir…</option>
            <option v-for="s in openSessions" :key="s.id" :value="s.id">#{{ s.id }} · {{ s.table_code || '—' }}</option>
          </select>
          <button type="submit" class="mesero-btn mesero-btn--primary">Nueva orden</button>
        </form>

        <ul v-if="filteredOrders.length" class="mesero-orders">
          <li v-for="o in filteredOrders" :key="o.id">
            <span>#{{ o.id }}</span>
            <span>{{ formatMoney(o.subtotal) }}</span>
            <button type="button" class="mesero-link" @click="loadOrderDetail(o.id)">Ver</button>
            <button type="button" class="mesero-link" @click="openOrderPdf(o.id)">PDF</button>
          </li>
        </ul>

        <template v-if="selectedOrder">
          <form class="mesero-form mesero-form--items" @submit.prevent="addItem">
            <select v-model="itemForm.product_id" class="mesero-input" required>
              <option value="" disabled>Producto</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <input v-model.number="itemForm.quantity" class="mesero-input mesero-input--num" type="number" min="1" required />
            <select v-model="itemForm.consumption_type" class="mesero-input">
              <option value="solo">Solo</option>
              <option value="with_companion">Con chica</option>
            </select>
            <div v-if="itemForm.consumption_type === 'with_companion'" class="mesero-companion-wrap">
              <input
                v-model="companionQuery"
                class="mesero-input"
                type="text"
                placeholder="Buscar chica (ej: clau)"
                autocomplete="off"
                :list="companionDatalistId"
                required
              />
              <datalist :id="companionDatalistId">
                <option v-for="c in filteredCompanions" :key="c.id" :value="c.stage_name"></option>
              </datalist>
              <input v-model="itemForm.companion_id" type="hidden" :required="itemForm.consumption_type === 'with_companion'" />
            </div>
            <button type="submit" class="mesero-btn mesero-btn--primary">Agregar</button>
          </form>
          <ul v-if="detail?.items?.length" class="mesero-lines">
            <li v-for="it in detail.items" :key="it.id">
              {{ it.product_name }} × {{ it.quantity }} · {{ formatMoney(it.subtotal) }}
            </li>
          </ul>
        </template>
      </div>
    </details>

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa orden POS"
      @close="closePdfPreview"
      @download="downloadOrderPdfFromModal"
    />
  </section>
</template>

<style scoped>
.mesero {
  padding: 0.35rem 0.25rem 4.5rem;
  max-width: 520px;
  margin: 0 auto;
}

.mesero-head {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 0.65rem;
  flex-wrap: wrap;
}

.mesero-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
}

.mesero-site {
  font-size: 0.72rem;
  opacity: 0.75;
  padding: 2px 8px;
  border-radius: 999px;
  border: 1px solid var(--color-border-soft, rgba(140, 160, 220, 0.35));
}

.mesero-count {
  margin-left: auto;
  font-size: 0.9rem;
  font-variant-numeric: tabular-nums;
  opacity: 0.85;
}

.mesero-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
  gap: 8px;
}

.mesero-tile {
  border: 1px solid var(--color-border-soft, #ccd4e7);
  border-radius: 12px;
  padding: 10px 6px;
  min-height: 64px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  background: transparent;
  font: inherit;
  cursor: pointer;
}

.mesero-tile-code {
  font-weight: 700;
  font-size: 0.95rem;
}

.mesero-tile-sub {
  font-size: 0.65rem;
  opacity: 0.8;
}

.mesero-tile--on {
  border-color: rgba(255, 185, 40, 0.5);
  background: rgba(255, 185, 40, 0.07);
}

.mesero-tile--pick {
  outline: 2px solid rgba(73, 136, 255, 0.45);
  outline-offset: 1px;
}

.mesero-empty {
  margin: 0.5rem 0 0;
  font-size: 0.82rem;
  line-height: 1.4;
  opacity: 0.88;
}

.mesero-panel {
  margin-top: 0.75rem;
}

.mesero-open {
  width: 100%;
  padding: 10px 12px;
  border-radius: 12px;
  border: none;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  background: rgba(73, 136, 255, 0.22);
  color: inherit;
}

.mesero-open:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.mesero-details {
  margin-top: 1rem;
  border-radius: 12px;
  border: 1px solid var(--color-border-soft, rgba(140, 160, 220, 0.28));
  overflow: hidden;
}

.mesero-details summary {
  padding: 10px 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.88rem;
  list-style: none;
}

.mesero-details summary::-webkit-details-marker {
  display: none;
}

.mesero-details-body {
  padding: 0 12px 12px;
  display: grid;
  gap: 12px;
}

.mesero-form {
  display: grid;
  gap: 8px;
}

.mesero-form--items {
  margin-top: 4px;
}

.mesero-label {
  font-size: 0.72rem;
  opacity: 0.8;
}

.mesero-input {
  width: 100%;
  padding: 8px 10px;
  border-radius: 10px;
  border: 1px solid var(--color-border-soft, #ccd4e7);
  font: inherit;
  background: transparent;
  color: inherit;
}

.mesero-input--num {
  max-width: 100%;
}

.mesero-btn {
  padding: 9px 12px;
  border-radius: 10px;
  border: none;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
}

.mesero-btn--primary {
  background: rgba(73, 136, 255, 0.28);
  color: inherit;
}

.mesero-orders {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 6px;
  font-size: 0.82rem;
}

.mesero-orders li {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 8px;
  align-items: center;
}

.mesero-link {
  border: none;
  background: none;
  color: rgba(120, 170, 255, 0.95);
  font: inherit;
  cursor: pointer;
  text-decoration: underline;
  padding: 0;
}

.mesero-lines {
  margin: 0;
  padding-left: 1rem;
  font-size: 0.78rem;
  line-height: 1.45;
  opacity: 0.92;
}

.mesero-companion-wrap {
  position: relative;
}
</style>
