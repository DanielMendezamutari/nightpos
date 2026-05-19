<script setup>
import { onMounted, reactive, ref } from 'vue'
import QuickSupplierModal from '../../components/QuickSupplierModal.vue'
import PdfPreviewModal from '../../components/PdfPreviewModal.vue'
import { apiDownloadFile, apiFormPost, apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'
import { usePdfPreview } from '../../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()
const { sites, sitePickerId, needsSitePicker, branchQuery, initSiteScope } = useBranchSiteScope(auth)

const loading = ref(false)
const purchaseModalOpen = ref(false)
const detailModalOpen = ref(false)
const detailLoading = ref(false)
const orderDetail = ref(null)
const supplierModalOpen = ref(false)
const orders = ref([])
const suppliers = ref([])
const products = ref([])
const purchaseDocumentFile = ref(null)
const purchaseDocumentInputRef = ref(null)
/** Subida posterior en compras que no tenían archivo (input oculto global). */
const attachDocInputRef = ref(null)
const attachTargetOrderId = ref(null)

const pdfPreviewOrderId = ref(null)
const {
  pdfPreviewOpen,
  pdfPreviewLoading,
  pdfPreviewUrl,
  pdfPreviewTitle,
  openPdfPreview,
  closePdfPreview,
  downloadPdfPreview,
} = usePdfPreview(() => auth.token.value)

const form = reactive({
  site_contact_id: '',
  document_ref: '',
  purchased_at: '',
  notes: '',
})

function emptyPurchaseLine() {
  return {
    product_id: products.value[0]?.id ?? null,
    purchase_packaging: 'unit',
    pack_quantity: 1,
    units_per_pack: 1,
    cost_per_pack: 0,
    custom_pack_label: '',
  }
}

const lines = ref([emptyPurchaseLine()])

function productById(id) {
  return products.value.find((p) => p.id === Number(id)) ?? null
}

function syncLineDefaults(line) {
  if (line.purchase_packaging === 'unit') {
    line.units_per_pack = 1
    return
  }
  const p = productById(line.product_id)
  if (line.purchase_packaging === 'box' && p?.purchase_units_per_box) {
    line.units_per_pack = p.purchase_units_per_box
    return
  }
  if (line.purchase_packaging === 'basket' && p?.purchase_units_per_basket) {
    line.units_per_pack = p.purchase_units_per_basket
    return
  }
  if (!line.units_per_pack || line.units_per_pack < 1) {
    line.units_per_pack = 1
  }
}

function linePackagingHint(packaging) {
  switch (packaging) {
    case 'unit':
      return 'Cantidad = unidades que entran al stock. Costo = precio por cada una.'
    case 'box':
      return 'Cantidad = n° de cajas. Uds/bulto = unidades de stock por caja (ej. 12 latas). Costo = precio por caja completa.'
    case 'basket':
      return 'Cantidad = n° de canastillos. Uds/bulto = unidades por canastillo. Costo = precio por canastillo.'
    case 'custom':
      return 'Nombrá el bulto abajo. Cantidad = cuántos bultos; uds/bulto = unidades por bulto; costo = precio por ese bulto.'
    default:
      return ''
  }
}

function lineStockSummary(line) {
  const pq = Number(line.pack_quantity) || 0
  const upp = line.purchase_packaging === 'unit' ? 1 : Number(line.units_per_pack) || 0
  const cpp = Number(line.cost_per_pack) || 0
  if (pq < 1 || upp < 1) return ''
  const base = pq * upp
  if (line.purchase_packaging === 'unit') {
    return `→ ${base} u. al stock en esta sucursal · costo unitario $${formatMoney(cpp)}`
  }
  const unitEst = Math.max(0, Math.round(cpp / upp))
  return `→ ${base} u. al stock (${pq}×${upp}) · costo unitario $${formatMoney(unitEst)} (${formatMoney(cpp)}÷${upp})`
}

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

function formatMoney(v) {
  return (Number(v) || 0).toLocaleString('es-AR', { maximumFractionDigits: 0 })
}

function statusLabel(status) {
  if (status === 'cancelled') return 'Anulada'
  return 'Recibida'
}

function closeDetailModal() {
  detailModalOpen.value = false
  orderDetail.value = null
}

async function openPurchaseDetail(orderId) {
  detailModalOpen.value = true
  detailLoading.value = true
  orderDetail.value = null
  try {
    const res = await apiRequest(`/maintenance/purchases/${orderId}${branchQuery()}`, {}, auth.token.value)
    orderDetail.value = res.data || null
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el detalle.')
    closeDetailModal()
  } finally {
    detailLoading.value = false
  }
}

async function confirmCancelPurchase(order) {
  if (!order?.id) return
  if ((order.status || 'received') === 'cancelled') {
    notify.info('Esta compra ya está anulada.')
    return
  }
  const ok = window.confirm(
    '¿Anular esta compra? Se revertirá el stock de los productos con control de inventario (solo si hay stock suficiente). Esta acción no se puede deshacer desde aquí.',
  )
  if (!ok) return
  try {
    await apiRequest(
      `/maintenance/purchases/${order.id}/cancel${branchQuery()}`,
      { method: 'POST', body: '{}' },
      auth.token.value,
    )
    notify.success('Compra anulada.')
    closeDetailModal()
    await loadOrders()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo anular la compra.')
  }
}

async function downloadPurchaseDocument(orderId, suggestedName) {
  try {
    await apiDownloadFile(
      `/maintenance/purchases/${orderId}/document${branchQuery()}`,
      auth.token.value,
      suggestedName || 'comprobante',
    )
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el archivo.')
  }
}

/** PDF generado en el servidor con sucursal, ítems y totales. */
async function downloadSystemPdf(orderId) {
  try {
    await apiDownloadFile(
      `/maintenance/purchases/${orderId}/pdf${branchQuery()}`,
      auth.token.value,
      `compra-${orderId}.pdf`,
    )
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo generar el PDF.')
  }
}

function onClosePdfPreview() {
  pdfPreviewOrderId.value = null
  closePdfPreview()
}

/** Abre el PDF en un modal (SPA) sin salir de la página. */
async function openSystemPdfPreview(orderId) {
  pdfPreviewOrderId.value = orderId
  try {
    await openPdfPreview(
      `/maintenance/purchases/${orderId}/pdf${branchQuery()}`,
      `Compra #${orderId}`,
    )
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el PDF.')
    pdfPreviewOrderId.value = null
    onClosePdfPreview()
  }
}

async function downloadFromPdfModal() {
  const id = pdfPreviewOrderId.value
  if (!id) return
  try {
    await downloadPdfPreview(`compra-${id}.pdf`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

function openAttachDocumentPicker(orderId) {
  attachTargetOrderId.value = orderId
  attachDocInputRef.value?.click()
}

async function onAttachDocSelected(ev) {
  const file = ev.target?.files?.[0]
  const orderId = attachTargetOrderId.value
  if (ev.target) {
    ev.target.value = ''
  }
  attachTargetOrderId.value = null
  if (!file || !orderId) {
    return
  }
  const fd = new FormData()
  fd.append('document', file)
  try {
    await apiFormPost(`/maintenance/purchases/${orderId}/document${branchQuery()}`, fd, auth.token.value)
    notify.success('Comprobante guardado.')
    await loadOrders()
    if (detailModalOpen.value && orderDetail.value?.order?.id === orderId) {
      await openPurchaseDetail(orderId)
    }
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar el archivo.')
  }
}

function addLine() {
  lines.value.push(emptyPurchaseLine())
}

function removeLine(i) {
  if (lines.value.length <= 1) return
  lines.value.splice(i, 1)
}

function clearPurchaseDocumentFile() {
  purchaseDocumentFile.value = null
  if (purchaseDocumentInputRef.value) {
    purchaseDocumentInputRef.value.value = ''
  }
}

function onPurchaseDocumentInputChange(ev) {
  const f = ev.target?.files?.[0]
  purchaseDocumentFile.value = f ?? null
}

function resetPurchaseForm() {
  form.site_contact_id = ''
  form.document_ref = ''
  form.purchased_at = ''
  form.notes = ''
  lines.value = [emptyPurchaseLine()]
  clearPurchaseDocumentFile()
}

async function openPurchaseModal() {
  purchaseModalOpen.value = true
  try {
    await loadSuppliers()
  } catch {
    /* loadSuppliers ya deja suppliers vacío ante error */
  }
  resetPurchaseForm()
}

function closePurchaseModal() {
  purchaseModalOpen.value = false
}

async function loadOrders() {
  const po = await apiRequest(`/maintenance/purchases${branchQuery()}`, {}, auth.token.value)
  orders.value = po.data || []
}

async function loadSuppliers() {
  try {
    const sitePart = branchQuery()
    const contactsUrl =
      '/branch/contacts?type=supplier' + (sitePart ? `&${sitePart.slice(1)}` : '')
    const sup = await apiRequest(contactsUrl, {}, auth.token.value)
    const list = sup.data?.contacts ?? []
    suppliers.value = [...list].sort((a, b) =>
      String(a.display_name || '').localeCompare(String(b.display_name || ''), 'es'),
    )
  } catch {
    suppliers.value = []
  }
}

function onSupplierCreated(row) {
  const next = [
    ...suppliers.value.filter((s) => Number(s.id) !== Number(row.id)),
    {
      id: row.id,
      display_name: row.display_name,
      contact_type: row.contact_type || 'supplier',
    },
  ]
  suppliers.value = next.sort((a, b) =>
    String(a.display_name || '').localeCompare(String(b.display_name || ''), 'es'),
  )
  form.site_contact_id = String(row.id)
}

async function loadProductsForLines() {
  const pr = await apiRequest(`/maintenance/products${branchQuery()}`, {}, auth.token.value)
  products.value = pr.data || []
  if (!lines.value[0].product_id && products.value.length) {
    lines.value[0].product_id = products.value[0].id
  }
}

async function bootstrap() {
  loading.value = true
  try {
    await initSiteScope()
    await Promise.all([loadOrders(), loadSuppliers(), loadProductsForLines()])
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar compras.')
  } finally {
    loading.value = false
  }
}

async function submitPurchase() {
  for (let i = 0; i < lines.value.length; i += 1) {
    const l = lines.value[i]
    if (l.purchase_packaging === 'custom' && !(l.custom_pack_label || '').trim()) {
      notify.error(`Ítem ${i + 1}: escribí el nombre del bulto (ej. palet).`)
      return
    }
  }

  const payloadLines = lines.value
    .filter((l) => l.product_id && Number(l.pack_quantity) > 0)
    .map((l) => {
      const row = {
        product_id: Number(l.product_id),
        purchase_packaging: l.purchase_packaging || 'unit',
        pack_quantity: Number(l.pack_quantity),
        cost_per_pack: Number(l.cost_per_pack) || 0,
      }
      if (l.purchase_packaging !== 'unit') {
        row.units_per_pack = Math.max(1, Number(l.units_per_pack) || 1)
      }
      if (l.purchase_packaging === 'custom') {
        row.custom_pack_label = String(l.custom_pack_label || '').trim()
      }
      return row
    })
  if (!payloadLines.length) {
    notify.error('Agregá al menos una línea con producto y cantidad.')
    return
  }
  try {
    const path = `/maintenance/purchases${branchQuery()}`
    const file = purchaseDocumentFile.value
    if (file instanceof File) {
      const fd = new FormData()
      fd.append('lines', JSON.stringify(payloadLines))
      if (form.site_contact_id) {
        fd.append('site_contact_id', String(Number(form.site_contact_id)))
      }
      if (form.document_ref) {
        fd.append('document_ref', form.document_ref)
      }
      if (form.purchased_at) {
        fd.append('purchased_at', form.purchased_at)
      }
      if (form.notes) {
        fd.append('notes', form.notes)
      }
      fd.append('document', file)
      await apiFormPost(path, fd, auth.token.value)
    } else {
      await apiRequest(
        path,
        {
          method: 'POST',
          body: JSON.stringify({
            site_contact_id: form.site_contact_id ? Number(form.site_contact_id) : null,
            document_ref: form.document_ref || null,
            purchased_at: form.purchased_at || null,
            notes: form.notes || null,
            lines: payloadLines,
          }),
        },
        auth.token.value,
      )
    }
    notify.success('Compra registrada: stock y costo actualizados.')
    closePurchaseModal()
    resetPurchaseForm()
    await loadOrders()
    await loadProductsForLines()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar la compra.')
  }
}

onMounted(bootstrap)
</script>

<template>
  <div class="maint-compras-scope">
    <section class="panel">
      <div class="panel-head">
        <h3>Compras</h3>
        <span>{{ loading ? 'Cargando…' : 'Ingresos de mercadería en esta sucursal' }}</span>
      </div>

      <p class="maint-tab-intro">
        Registrá <strong>factura o remito</strong>, proveedor opcional (desde Personal) y cada producto según cómo te
        facturan: <strong>por unidad</strong>, <strong>por caja</strong>, <strong>por canastillo</strong> u otro bulto.
        El sistema convierte a unidades de stock y actualiza el costo de compra.         Podés
        <strong>ver el PDF</strong> generado con los datos de cada compra (columna PDF) dentro de la misma pantalla y
        descargarlo si querés; aparte podés adjuntar un escaneo o foto del comprobante físico.
      </p>

      <div v-if="needsSitePicker" class="field-block maint-prod-site-pick">
        <span>Sucursal</span>
        <select v-model.number="sitePickerId" @change="bootstrap">
          <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
        </select>
      </div>

      <div class="maint-products-toolbar">
        <button type="button" class="primary-btn" @click="openPurchaseModal">Nueva compra</button>
        <span class="maint-products-count">{{ loading ? '…' : `${orders.length} compras` }}</span>
      </div>

      <div v-if="!orders.length && !loading" class="admin-empty-card">
        <p>No hay compras registradas</p>
        <small>Usá <strong>Nueva compra</strong> para cargar la primera.</small>
      </div>
      <div v-else-if="orders.length" class="table-wrap maint-catalog-table-wrap">
        <table class="data-table maint-products-table maint-compras-table">
          <thead>
            <tr>
              <th>Cuándo</th>
              <th>Proveedor</th>
              <th>Documento</th>
              <th class="num">Líneas</th>
              <th class="num">Total</th>
              <th>Estado</th>
              <th>Registró</th>
              <th class="maint-compras-pdf-col" title="Vista previa del PDF generado con datos del sistema">PDF</th>
              <th class="maint-compras-doc-col" title="Archivo escaneado o foto que subas">Escaneo</th>
              <th class="maint-compras-actions-col" />
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in orders" :key="o.id">
              <td>{{ formatWhen(o.purchased_at) }}</td>
              <td>{{ o.supplier_name || '—' }}</td>
              <td>{{ o.document_ref || '—' }}</td>
              <td class="num">{{ o.line_count ?? '—' }}</td>
              <td class="num">{{ formatMoney(o.total_amount) }}</td>
              <td>
                <span class="maint-compras-status" :class="'is-' + (o.status || 'received')">
                  {{ statusLabel(o.status) }}
                </span>
              </td>
              <td>{{ o.created_by_name || '—' }}</td>
              <td class="maint-compras-pdf-cell">
                <button
                  type="button"
                  class="ghost-btn maint-compras-row-btn maint-compras-row-btn--pdf"
                  title="Ver PDF generado en pantalla"
                  @click="openSystemPdfPreview(o.id)"
                >
                  Ver
                </button>
              </td>
              <td class="maint-compras-doc-cell">
                <button
                  v-if="o.has_document"
                  type="button"
                  class="ghost-btn maint-compras-row-btn"
                  :title="o.document_original_name || 'Descargar'"
                  @click="downloadPurchaseDocument(o.id, o.document_original_name)"
                >
                  Descargar
                </button>
                <button
                  v-else-if="(o.status || 'received') !== 'cancelled'"
                  type="button"
                  class="ghost-btn maint-compras-row-btn"
                  title="Adjuntar PDF o foto del comprobante"
                  @click="openAttachDocumentPicker(o.id)"
                >
                  Adjuntar
                </button>
                <span v-else class="maint-compras-no-doc">—</span>
              </td>
              <td class="maint-compras-actions-cell">
                <button type="button" class="ghost-btn maint-compras-row-btn" @click="openPurchaseDetail(o.id)">
                  Ver
                </button>
                <button
                  v-if="(o.status || 'received') !== 'cancelled'"
                  type="button"
                  class="ghost-btn maint-compras-row-btn maint-compras-row-btn--danger"
                  @click="confirmCancelPurchase(o)"
                >
                  Anular
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div v-if="purchaseModalOpen" class="maint-product-modal-overlay" @click.self="closePurchaseModal">
      <article class="panel maint-product-modal-card compra-modal" @click.stop>
        <div class="panel-head">
          <h3>Nueva compra</h3>
          <button type="button" class="ghost-btn" @click="closePurchaseModal">Cerrar</button>
        </div>
        <p class="compra-modal-lede">
          Una fila = un ítem del remito o factura. El sistema suma <strong>unidades de stock</strong> en esta sucursal y
          recalcula el <strong>costo de compra</strong> del producto (misma moneda que el resto del sistema).
        </p>
        <form class="maint-field-grid" @submit.prevent="submitPurchase">
          <div class="compra-modal-block">
            <h4 class="compra-modal-h4">Proveedor y documento</h4>
            <div class="field-block field-block--full">
              <span>Proveedor <span class="compra-label-opt">(opcional)</span></span>
              <div class="maint-compras-supplier-row">
                <select v-model="form.site_contact_id" class="maint-compras-supplier-select" aria-describedby="hint-supplier">
                  <option value="">— Sin proveedor —</option>
                  <option v-for="s in suppliers" :key="s.id" :value="String(s.id)">{{ s.display_name }}</option>
                </select>
                <button
                  type="button"
                  class="ghost-btn maint-compras-supplier-btn"
                  :disabled="needsSitePicker && !sitePickerId"
                  @click="supplierModalOpen = true"
                >
                  + Nuevo proveedor
                </button>
              </div>
              <p id="hint-supplier" class="field-hint compra-hint-tight">
                Para registrar uno nuevo sin salir: <strong>+ Nuevo proveedor</strong>. Listado completo en Administración →
                Personal.
              </p>
            </div>
            <div class="field-block field-block--full maint-compras-meta-row">
              <div class="field-block">
                <span>N° en papel</span>
                <input
                  v-model="form.document_ref"
                  type="text"
                  placeholder="Ej. factura A 1234"
                  title="Número que figura en la factura o remito; sirve para buscar esta compra después."
                  autocomplete="off"
                />
                <p class="field-hint compra-hint-tight">Lo que dice el comprobante físico o PDF.</p>
              </div>
              <div class="field-block">
                <span>Fecha y hora del ingreso</span>
                <input
                  v-model="form.purchased_at"
                  type="datetime-local"
                  title="Momento en que registrás la mercadería. Si lo dejás vacío, se usa ahora."
                />
                <p class="field-hint compra-hint-tight">Vacío = fecha y hora actuales.</p>
              </div>
            </div>
            <div class="field-block field-block--full">
              <span>Notas <span class="compra-label-opt">(opcional)</span></span>
              <input
                v-model="form.notes"
                type="text"
                placeholder="Ej. bonificación, observaciones internas"
                title="Texto libre; no afecta stock ni costos."
              />
            </div>
            <div class="field-block field-block--full compra-doc-field">
              <span>PDF o foto del comprobante <span class="compra-label-opt">(opcional)</span></span>
              <div class="compra-doc-row">
                <input
                  id="compra-doc-input"
                  ref="purchaseDocumentInputRef"
                  type="file"
                  class="compra-doc-input"
                  accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                  @change="onPurchaseDocumentInputChange"
                />
                <button
                  v-if="purchaseDocumentFile"
                  type="button"
                  class="ghost-btn compra-doc-clear"
                  @click="clearPurchaseDocumentFile"
                >
                  Quitar archivo
                </button>
              </div>
              <p class="field-hint compra-hint-tight">
                Opcional: foto o escaneo del papel (PDF, JPG o PNG, máx. ~10 MB).                 El <strong>PDF con datos del sistema</strong> lo ves desde el listado (columna PDF) sin salir de la app.
              </p>
            </div>
          </div>

          <div class="compra-lines-section">
            <div class="compra-lines-section-head">
              <div>
                <h4 class="compra-modal-h4">Productos en esta compra</h4>
                <ul class="compra-legend" aria-label="Cómo se calcula cada fila">
                  <li><strong>Stock</strong> = cantidad de bultos × unidades por bulto (en modo unidad: 1 bulto = 1 u.).</li>
                  <li><strong>Costo unitario</strong> = costo del bulto ÷ unidades por bulto (en unidad: igual al monto que cargás).</li>
                  <li>Si el producto tiene uds/caja guardadas en su ficha, se pueden rellenar solas al elegir caja o canastillo.</li>
                </ul>
              </div>
              <button type="button" class="ghost-btn" @click="addLine">+ Otra fila</button>
            </div>

            <p v-if="!products.length" class="field-hint compra-lines-warn">
              No hay productos en esta sucursal. Creálos en Mantenimiento → Productos.
            </p>

            <template v-else>
              <div class="compra-lines-table" role="group" :aria-label="'Ítems de la compra, ' + lines.length + ' filas'">
                <div class="compra-lines-thead">
                  <span class="col-producto" title="Producto tal como lo vendés en el POS / carta.">Producto</span>
                  <span
                    class="col-pack"
                    title="Cómo viene facturado: por unidad suelta, caja, canastillo u otro bulto."
                    >Tipo de bulto</span
                  >
                  <span
                    class="col-uds"
                    title="Solo si no es 'Unidad': cuántas unidades de stock entran en un solo bulto (ej. 12 latas por caja)."
                    >Uds. en 1 bulto</span
                  >
                  <span
                    class="col-cant"
                    title="En unidad: cuántas unidades comprás. En caja/canastillo: cuántos bultos completos."
                    >Cantidad</span
                  >
                  <span
                    class="col-costo"
                    title="En unidad: precio por unidad. En caja: precio por caja entera (no por lata suelta)."
                    >Costo ($)</span
                  >
                  <span class="col-acc" title="Quitar esta línea de la compra." />
                </div>
                <div v-for="(line, idx) in lines" :key="idx" class="compra-line-row">
                  <div class="compra-line-product" data-label="Producto">
                    <span class="compra-line-badge">Fila {{ idx + 1 }}</span>
                    <select
                      v-model.number="line.product_id"
                      class="compra-line-select"
                      required
                      :aria-label="'Producto ítem ' + (idx + 1)"
                      @change="syncLineDefaults(line)"
                    >
                      <option v-for="p in products" :key="p.id" :value="p.id">
                        {{ p.sku }} — {{ p.name }}
                      </option>
                    </select>
                  </div>
                  <div class="compra-line-packaging" data-label="Tipo de bulto">
                    <select
                      v-model="line.purchase_packaging"
                      class="compra-line-select"
                      :aria-label="'Tipo de bulto ítem ' + (idx + 1)"
                      @change="syncLineDefaults(line)"
                    >
                      <option value="unit">Unidad</option>
                      <option value="box">Caja</option>
                      <option value="basket">Canastillo</option>
                      <option value="custom">Otro bulto…</option>
                    </select>
                    <p class="compra-line-micro-hint">{{ linePackagingHint(line.purchase_packaging) }}</p>
                  </div>
                  <label class="compra-line-field compra-line-uds" data-label="Uds. en 1 bulto">
                    <span class="sr-only">Unidades por bulto ítem {{ idx + 1 }}</span>
                    <input
                      v-model.number="line.units_per_pack"
                      type="number"
                      min="1"
                      :disabled="line.purchase_packaging === 'unit'"
                      :required="line.purchase_packaging !== 'unit'"
                      placeholder="Ej. 12"
                      :aria-label="'Unidades por bulto ítem ' + (idx + 1)"
                    />
                  </label>
                  <label
                    class="compra-line-field"
                    :data-label="line.purchase_packaging === 'unit' ? 'Cantidad (unidades)' : 'Cantidad (bultos)'"
                  >
                    <span class="sr-only">Cantidad ítem {{ idx + 1 }}</span>
                    <input
                      v-model.number="line.pack_quantity"
                      type="number"
                      min="1"
                      required
                      :placeholder="line.purchase_packaging === 'unit' ? 'Ej. 48' : 'Ej. 5'"
                      :aria-label="'Cantidad ítem ' + (idx + 1)"
                    />
                  </label>
                  <label
                    class="compra-line-field"
                    :data-label="line.purchase_packaging === 'unit' ? 'Costo ($) por unidad' : 'Costo ($) por bulto'"
                  >
                    <span class="sr-only">Costo ítem {{ idx + 1 }}</span>
                    <input
                      v-model.number="line.cost_per_pack"
                      type="number"
                      min="0"
                      required
                      :placeholder="line.purchase_packaging === 'unit' ? 'Ej. 120' : 'Ej. 900'"
                      :aria-label="'Costo ítem ' + (idx + 1)"
                    />
                  </label>
                  <div class="compra-line-actions">
                    <button
                      type="button"
                      class="ghost-btn compra-line-remove"
                      :disabled="lines.length <= 1"
                      :title="lines.length <= 1 ? 'Debe haber al menos un producto' : 'Quitar esta fila'"
                      @click="removeLine(idx)"
                    >
                      Quitar
                    </button>
                  </div>
                  <div v-if="line.purchase_packaging === 'custom'" class="compra-line-custom field-block field-block--full">
                    <span>Nombre del bulto <span class="compra-label-req">obligatorio</span></span>
                    <input
                      v-model="line.custom_pack_label"
                      type="text"
                      maxlength="48"
                      placeholder="Ej. palet, bolsa, pack…"
                      :aria-label="'Nombre del bulto ítem ' + (idx + 1)"
                    />
                  </div>
                  <p v-if="lineStockSummary(line)" class="compra-line-summary field-hint">
                    {{ lineStockSummary(line) }}
                  </p>
                </div>
              </div>
            </template>
          </div>

          <div class="compra-modal-footer-hint">
            <p class="field-hint compra-hint-tight">
              <strong>Registrar compra</strong> suma stock solo en productos con control de inventario y actualiza su costo
              de compra.
            </p>
          </div>
          <div class="maint-form-actions maint-modal-actions">
            <button type="button" class="ghost-btn" @click="closePurchaseModal">Cancelar</button>
            <button type="submit" class="primary-btn">Registrar compra</button>
          </div>
        </form>
      </article>
    </div>

    <div v-if="detailModalOpen" class="maint-product-modal-overlay" @click.self="closeDetailModal">
      <article class="panel maint-product-modal-card maint-compras-detail-card" @click.stop>
        <div class="panel-head">
          <h3>Detalle de compra</h3>
          <button type="button" class="ghost-btn" @click="closeDetailModal">Cerrar</button>
        </div>
        <p v-if="detailLoading" class="field-hint">Cargando…</p>
        <template v-else-if="orderDetail?.order">
          <div class="maint-compras-detail-pdf-row">
            <div class="maint-compras-detail-pdf-actions">
              <button type="button" class="primary-btn maint-compras-pdf-main-btn" @click="openSystemPdfPreview(orderDetail.order.id)">
                Ver PDF del sistema
              </button>
              <button type="button" class="ghost-btn" @click="downloadSystemPdf(orderDetail.order.id)">Descargar</button>
            </div>
            <p class="field-hint maint-compras-pdf-main-hint">
              Se abre dentro de la app (sin cambiar de página). Podés descargar el archivo con el botón de al lado.
              Distinto del escaneo o foto que adjuntes abajo.
            </p>
          </div>
          <div class="maint-compras-detail-meta">
            <p>
              <span class="label">Fecha</span>
              <strong>{{ formatWhen(orderDetail.order.purchased_at) }}</strong>
            </p>
            <p>
              <span class="label">Proveedor</span>
              <strong>{{ orderDetail.order.supplier_name || '—' }}</strong>
            </p>
            <p>
              <span class="label">Documento</span>
              <strong>{{ orderDetail.order.document_ref || '—' }}</strong>
            </p>
            <p v-if="orderDetail.order.has_document" class="maint-compras-detail-doc">
              <span class="label">Escaneo / foto adjunta</span>
              <span class="maint-compras-detail-doc-actions">
                <button
                  type="button"
                  class="ghost-btn"
                  @click="downloadPurchaseDocument(orderDetail.order.id, orderDetail.order.document_original_name)"
                >
                  Descargar{{ orderDetail.order.document_original_name ? ' · ' + orderDetail.order.document_original_name : '' }}
                </button>
                <button type="button" class="ghost-btn" @click="openAttachDocumentPicker(orderDetail.order.id)">
                  Reemplazar
                </button>
              </span>
            </p>
            <p v-else-if="(orderDetail.order.status || 'received') !== 'cancelled'" class="maint-compras-detail-doc">
              <span class="label">Escaneo / foto del papel</span>
              <span class="maint-compras-detail-no-file">Opcional: subí una copia de la factura o remito físico.</span>
              <button type="button" class="ghost-btn" @click="openAttachDocumentPicker(orderDetail.order.id)">
                Adjuntar PDF o foto
              </button>
            </p>
            <p>
              <span class="label">Estado</span>
              <span class="maint-compras-status" :class="'is-' + (orderDetail.order.status || 'received')">
                {{ statusLabel(orderDetail.order.status) }}
              </span>
            </p>
            <p v-if="orderDetail.order.created_by_name">
              <span class="label">Registró</span>
              <strong>{{ orderDetail.order.created_by_name }}</strong>
            </p>
            <p v-if="orderDetail.order.cancelled_at">
              <span class="label">Anulada</span>
              <strong>{{ formatWhen(orderDetail.order.cancelled_at) }}</strong>
              <span v-if="orderDetail.order.cancelled_by_name"> · {{ orderDetail.order.cancelled_by_name }}</span>
            </p>
            <p v-if="orderDetail.order.notes">
              <span class="label">Notas</span>
              {{ orderDetail.order.notes }}
            </p>
          </div>
          <div class="table-wrap maint-compras-detail-lines">
            <table class="data-table">
              <thead>
                <tr>
                  <th>SKU</th>
                  <th>Producto</th>
                  <th class="num">Cant.</th>
                  <th class="num">Costo u.</th>
                  <th class="num">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ln in orderDetail.lines || []" :key="ln.id">
                  <td>{{ ln.sku }}</td>
                  <td>{{ ln.product_name }}</td>
                  <td class="num">{{ ln.quantity }}</td>
                  <td class="num">{{ formatMoney(ln.unit_cost) }}</td>
                  <td class="num">{{ formatMoney(ln.line_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="maint-compras-detail-total">
            Total <strong>{{ formatMoney(orderDetail.order.total_amount) }}</strong>
            · {{ orderDetail.order.line_count }} líneas
          </p>
          <div v-if="(orderDetail.order.status || 'received') !== 'cancelled'" class="maint-form-actions maint-modal-actions">
            <button type="button" class="ghost-btn" @click="closeDetailModal">Cerrar</button>
            <button type="button" class="ghost-btn maint-compras-cancel-main" @click="confirmCancelPurchase(orderDetail.order)">
              Anular compra
            </button>
          </div>
          <div v-else class="maint-form-actions maint-modal-actions">
            <button type="button" class="ghost-btn" @click="closeDetailModal">Cerrar</button>
          </div>
        </template>
      </article>
    </div>

    <input
      ref="attachDocInputRef"
      type="file"
      class="sr-only"
      tabindex="-1"
      aria-hidden="true"
      accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
      @change="onAttachDocSelected"
    />

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa del PDF de compra"
      :show-download="!!pdfPreviewOrderId"
      @close="onClosePdfPreview"
      @download="downloadFromPdfModal"
    />

    <QuickSupplierModal
      v-model="supplierModalOpen"
      :branch-suffix="branchQuery()"
      @created="onSupplierCreated"
    />
  </div>
</template>

<style scoped>
.maint-compras-scope {
  width: 100%;
}

.maint-compras-table .num {
  text-align: right;
  white-space: nowrap;
}

.maint-compras-pdf-col,
.maint-compras-pdf-cell {
  width: 1%;
  white-space: nowrap;
  text-align: center;
}

.maint-compras-row-btn--pdf {
  font-weight: 700;
  letter-spacing: 0.02em;
}

.maint-compras-doc-col,
.maint-compras-doc-cell {
  width: 1%;
  white-space: nowrap;
  text-align: center;
}

.maint-compras-no-doc {
  color: var(--color-muted, #666);
  font-size: 0.9rem;
}

.maint-compras-actions-col {
  width: 1%;
  white-space: nowrap;
}

.maint-compras-actions-cell {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  justify-content: flex-end;
  align-items: center;
}

.maint-compras-row-btn {
  font-size: 0.82rem;
  padding: 0.35rem 0.55rem;
}

.maint-compras-row-btn--danger {
  color: #ffb4a8;
}

.maint-compras-status {
  display: inline-block;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.2rem 0.45rem;
  border-radius: 6px;
  background: rgba(120, 160, 255, 0.15);
  color: #c8d8ff;
}

.maint-compras-status.is-cancelled {
  background: rgba(255, 120, 100, 0.18);
  color: #ffb4a8;
}

.maint-compras-detail-card {
  max-width: 44rem;
}

.maint-compras-detail-pdf-row {
  margin-bottom: 1rem;
  padding: 0.75rem 0.85rem;
  border-radius: 10px;
  background: rgba(80, 120, 220, 0.12);
  border: 1px solid rgba(142, 168, 245, 0.22);
}

.maint-compras-pdf-main-btn {
  margin-bottom: 0.35rem;
}

.maint-compras-pdf-main-hint {
  margin: 0;
  max-width: 34rem;
}

.maint-compras-detail-pdf-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 0.65rem;
  margin-bottom: 0.35rem;
}

.maint-compras-detail-meta {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(11rem, 1fr));
  gap: 0.65rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.maint-compras-detail-meta p {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.maint-compras-detail-meta .label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--color-muted, #9eb4ea);
}

.maint-compras-detail-lines {
  margin-bottom: 0.75rem;
  max-height: 40vh;
  overflow: auto;
}

.maint-compras-detail-total {
  margin: 0 0 1rem;
  font-size: 0.95rem;
  text-align: right;
}

.maint-compras-cancel-main {
  color: #ffb4a8;
  border-color: rgba(255, 180, 168, 0.35);
}

@media (max-width: 960px) {
  .maint-compras-table thead {
    display: none;
  }
  .maint-compras-table tbody tr {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.35rem 0.65rem;
    padding: 0.65rem 0.5rem;
    border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.12));
  }
  .maint-compras-table tbody td {
    border: none;
  }
  .maint-compras-table tbody td.maint-compras-actions-cell {
    grid-column: 1 / -1;
    justify-content: flex-start;
  }
}

.maint-prod-site-pick {
  max-width: 22rem;
  margin-bottom: 1rem;
}

.maint-products-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem 1rem;
  margin-bottom: 1rem;
}

.maint-products-count {
  font-size: 0.9rem;
  color: var(--color-muted, #666);
}

.maint-compras-supplier-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.maint-compras-supplier-select {
  flex: 1;
  min-width: 12rem;
  min-height: 42px;
}

.maint-compras-supplier-btn {
  flex-shrink: 0;
  white-space: nowrap;
}

.maint-compras-meta-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

@media (max-width: 640px) {
  .maint-compras-meta-row {
    grid-template-columns: 1fr;
  }
}

.maint-product-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 60;
  padding: 1rem;
}

.maint-product-modal-card {
  width: 100%;
  max-width: 52rem;
  max-height: 92vh;
  overflow: auto;
}

.compra-modal-lede {
  margin: 0 0 1rem;
  font-size: 0.86rem;
  line-height: 1.45;
  color: var(--color-muted, #9eb4ea);
}

.compra-modal-block {
  margin-bottom: 0.15rem;
}

.compra-modal-h4 {
  margin: 0 0 0.4rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-muted, #9eb4ea);
}

.compra-label-opt {
  font-weight: 500;
  text-transform: none;
  letter-spacing: 0;
  font-size: 0.78em;
  opacity: 0.85;
}

.compra-label-req {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: none;
  color: #ffb4a8;
}

.compra-hint-tight {
  margin: 0.2rem 0 0;
  font-size: 0.75rem;
  line-height: 1.4;
}

.compra-legend {
  margin: 0.2rem 0 0.5rem;
  padding-left: 1.1rem;
  max-width: 40rem;
  font-size: 0.75rem;
  line-height: 1.45;
  color: var(--color-muted, #a8bcee);
}

.compra-legend li {
  margin-bottom: 0.2rem;
}

.compra-legend li:last-child {
  margin-bottom: 0;
}

.compra-line-micro-hint {
  margin: 0.35rem 0 0;
  font-size: 0.7rem;
  line-height: 1.35;
  color: var(--color-muted, #8fa6d4);
  max-width: 14rem;
}

.compra-modal-footer-hint {
  grid-column: 1 / -1;
  margin-top: 0.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.12));
}

.compra-doc-field {
  margin-top: 0.15rem;
}

.compra-doc-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 0.75rem;
}

.compra-doc-input {
  flex: 1;
  min-width: 0;
  max-width: 100%;
  font-size: 0.86rem;
}

.compra-doc-clear {
  flex-shrink: 0;
  font-size: 0.82rem;
}

.maint-compras-detail-doc {
  grid-column: 1 / -1;
}

.maint-compras-detail-doc .label {
  display: block;
  margin-bottom: 0.25rem;
}

.maint-compras-detail-doc-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem 0.65rem;
}

.maint-compras-detail-no-file {
  display: block;
  font-size: 0.86rem;
  color: var(--color-muted, #9eb4ea);
  margin-bottom: 0.35rem;
}

.maint-modal-actions {
  justify-content: flex-end;
  gap: 0.5rem;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.compra-lines-section {
  grid-column: 1 / -1;
  margin-top: 0.25rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.2));
}

.compra-lines-section-head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.65rem;
}

.compra-lines-warn {
  margin: 0.5rem 0 0;
  color: #ffb4a8;
}

.compra-lines-table {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.compra-lines-thead {
  display: none;
}

.compra-line-row {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
  padding: 0.65rem 0.5rem;
  border-radius: 10px;
  background: var(--compra-line-bg, rgba(18, 28, 58, 0.35));
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.12));
}

@media (min-width: 900px) {
  .compra-lines-thead {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) 9.5rem 4.5rem 5.25rem 5.5rem auto;
    gap: 0.5rem 0.65rem;
    align-items: end;
    padding: 0 0 0.25rem;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-muted, #9eb4ea);
    border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.15));
  }
  .compra-lines-thead .col-acc {
    min-width: 4.25rem;
  }
  .compra-line-row {
    grid-template-columns: minmax(0, 1.1fr) 9.5rem 4.5rem 5.25rem 5.5rem auto;
    gap: 0.5rem 0.65rem;
    align-items: start;
    padding: 0.5rem 0.35rem;
    background: transparent;
    border: none;
    border-radius: 0;
    border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.1));
  }
  .compra-line-actions {
    padding-top: 0.45rem;
  }
}

.compra-line-packaging {
  min-width: 0;
}

.compra-line-packaging .compra-line-select {
  width: 100%;
  min-height: 42px;
}

.compra-line-custom {
  margin-top: 0.15rem;
}

.compra-line-custom.field-block--full {
  display: grid;
  gap: 0.35rem;
}

.compra-line-custom span {
  font-size: 0.82rem;
  font-weight: 600;
  color: #dbe6ff;
}

.compra-line-summary {
  grid-column: 1 / -1;
  margin: 0.15rem 0 0;
  font-size: 0.8rem;
}

.compra-line-uds input:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.compra-line-product {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  min-width: 0;
}

.compra-line-badge {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-muted, #a8bcee);
}

.compra-line-select {
  width: 100%;
  min-height: 42px;
}

.compra-line-field input {
  width: 100%;
  min-height: 42px;
}

.compra-line-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.compra-line-remove {
  font-size: 0.82rem;
}

@media (max-width: 899px) {
  .compra-line-product[data-label]::before,
  .compra-line-packaging[data-label]::before,
  .compra-line-field::before {
    content: attr(data-label);
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--color-muted, #a8bcee);
    display: block;
    margin-bottom: 0.2rem;
  }
}
</style>
