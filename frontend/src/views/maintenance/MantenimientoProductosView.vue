<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import QuickCategoryModal from '../../components/QuickCategoryModal.vue'
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

const productModalOpen = ref(false)
const categoryModalOpen = ref(false)
const editingProductId = ref(null)

function sortCategoriesList(list) {
  return [...list].sort((a, b) => {
    const so = (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0)
    if (so !== 0) return so
    return String(a.name || '').localeCompare(String(b.name || ''), 'es')
  })
}

function onQuickCategoryCreated(cat) {
  const row = {
    id: cat.id,
    slug: cat.slug,
    name: cat.name,
    sort_order: cat.sort_order,
    product_type: cat.product_type,
  }
  categories.value = sortCategoriesList([...categories.value, row])
  createProductForm.category_id = row.id
}

const tab = ref('productos')
const loading = ref(false)
const products = ref([])
const categories = ref([])
const productOptions = ref([])
const kardexRows = ref([])
const valuedRows = ref([])
const recipes = ref([])

const movementForm = reactive({
  product_id: null,
  movement_kind: 'ingreso',
  quantity: 1,
  unit_cost: null,
  notes: '',
})

const createProductForm = reactive({
  sku: '',
  name: '',
  category_id: null,
  price_solo: 0,
  price_with_companion: 0,
  purchase_price: 0,
  purchase_units_per_box: '',
  purchase_units_per_basket: '',
  base_stock: 0,
  stock_min: 0,
  stock_max: '',
  track_stock: true,
  is_active: true,
})

const kardexProductId = ref(null)

const recipeForm = reactive({
  source_product_id: null,
  target_product_id: null,
  source_units: 1,
  target_units: 1,
  notes: '',
})

const applyRefillForm = reactive({
  recipe_id: null,
  batches: 1,
  notes: '',
})

const selectedKardexProduct = computed(() => productOptions.value.find((p) => p.id === Number(kardexProductId.value)) ?? null)

/** Solo productos con control de stock: movimientos manuales y relleno */
const productOptionsStock = computed(() =>
  products.value
    .filter((p) => p.track_stock)
    .map((p) => ({ id: p.id, name: `${p.sku} - ${p.name}`, stock_actual: p.stock_actual })),
)

function movementTypeLabel(type) {
  const map = {
    sale_out: 'Venta',
    transfer_in: 'Ingreso',
    transfer_out: 'Salida / relleno',
    adjustment: 'Ajuste',
  }
  return map[type] || String(type || '—')
}

function formatMovedAt(value) {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return String(value)
  }
}

function movementKindLabel(kind) {
  const map = { ingreso: 'Suma al stock', salida: 'Resta del stock', ajuste: 'Correccion de inventario' }
  return map[kind] || kind
}

function stockStatusLabel(status) {
  const map = {
    ok: 'En rango',
    bajo: 'Bajo minimo',
    exceso: 'Sobre maximo',
    sin_control: 'Sin control',
  }
  return map[status] || status
}

function stockStatusClass(status) {
  if (status === 'bajo') return 'maint-badge maint-badge--danger'
  if (status === 'exceso') return 'maint-badge maint-badge--warn'
  if (status === 'sin_control') return 'maint-badge maint-badge--muted'
  return 'maint-badge maint-badge--ok'
}

async function loadProducts() {
  const payload = await apiRequest(`/maintenance/products${branchQuery()}`, {}, auth.token.value)
  products.value = payload.data || []
  if (Array.isArray(payload.categories) && payload.categories.length) {
    categories.value = payload.categories
    if (!createProductForm.category_id) {
      createProductForm.category_id = categories.value[0].id
    }
  }
  productOptions.value = products.value.map((p) => ({ id: p.id, name: `${p.sku} - ${p.name}`, stock_actual: p.stock_actual }))
  if (!movementForm.product_id && productOptionsStock.value.length) movementForm.product_id = productOptionsStock.value[0].id
  if (!kardexProductId.value && productOptions.value.length) kardexProductId.value = productOptions.value[0].id
  const tracked = productOptionsStock.value
  if (!recipeForm.source_product_id && tracked.length) recipeForm.source_product_id = tracked[0].id
  if (!recipeForm.target_product_id && tracked.length > 1) recipeForm.target_product_id = tracked[1].id
}

async function loadCategories() {
  try {
    const payload = await apiRequest('/product-categories', {}, auth.token.value)
    categories.value = payload.data || []
    if (!createProductForm.category_id && categories.value.length) {
      createProductForm.category_id = categories.value[0].id
    }
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudieron cargar las categorías.')
    throw e
  }
}

async function loadValuedKardex() {
  const payload = await apiRequest(`/maintenance/kardex-valued${branchQuery()}`, {}, auth.token.value)
  valuedRows.value = payload.data?.rows || []
}

async function loadKardex() {
  if (!kardexProductId.value) return
  const payload = await apiRequest(
    `/maintenance/products/${kardexProductId.value}/kardex${branchQuery()}`,
    {},
    auth.token.value,
  )
  kardexRows.value = payload.data?.movements || []
}

async function loadRecipes() {
  try {
    const payload = await apiRequest(`/maintenance/refill-recipes${branchQuery()}`, {}, auth.token.value)
    recipes.value = payload.data || []
  } catch {
    recipes.value = []
  }
}

async function bootstrap() {
  loading.value = true
  try {
    await initSiteScope()
    await loadProducts()
    if (!categories.value.length) {
      await loadCategories()
    }
    await Promise.all([loadValuedKardex(), loadKardex(), loadRecipes()])
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo cargar mantenimiento.')
  } finally {
    loading.value = false
  }
}

function resetProductForm() {
  createProductForm.sku = ''
  createProductForm.name = ''
  createProductForm.category_id = categories.value[0]?.id ?? null
  createProductForm.price_solo = 0
  createProductForm.price_with_companion = 0
  createProductForm.purchase_price = 0
  createProductForm.purchase_units_per_box = ''
  createProductForm.purchase_units_per_basket = ''
  createProductForm.base_stock = 0
  createProductForm.stock_min = 0
  createProductForm.stock_max = ''
  createProductForm.track_stock = true
  createProductForm.is_active = true
}

async function openCreateProductModal() {
  if (!categories.value.length) {
    try {
      await loadCategories()
    } catch {
      /* notify en loadCategories / usuario puede reintentar */
    }
  }
  editingProductId.value = null
  resetProductForm()
  productModalOpen.value = true
}

async function openEditProductModal(p) {
  if (!categories.value.length) {
    try {
      await loadCategories()
    } catch {
      /* idem */
    }
  }
  editingProductId.value = p.id
  createProductForm.sku = p.sku
  createProductForm.name = p.name
  createProductForm.category_id = p.category_id ?? categories.value[0]?.id ?? null
  createProductForm.price_solo = p.price_solo
  createProductForm.price_with_companion = p.price_with_companion
  createProductForm.purchase_price = p.purchase_price
  createProductForm.purchase_units_per_box =
    p.purchase_units_per_box != null && p.purchase_units_per_box !== '' ? p.purchase_units_per_box : ''
  createProductForm.purchase_units_per_basket =
    p.purchase_units_per_basket != null && p.purchase_units_per_basket !== '' ? p.purchase_units_per_basket : ''
  createProductForm.base_stock = p.stock_actual
  createProductForm.stock_min = p.stock_min
  createProductForm.stock_max = p.stock_max !== null && p.stock_max !== undefined ? p.stock_max : ''
  createProductForm.track_stock = p.track_stock
  createProductForm.is_active = p.is_active
  productModalOpen.value = true
}

function closeProductModal() {
  productModalOpen.value = false
  editingProductId.value = null
}

function buildProductPayload() {
  const stockMaxRaw = createProductForm.stock_max
  const payload = {
    sku: createProductForm.sku,
    name: createProductForm.name,
    category_id: Number(createProductForm.category_id),
    price_solo: Number(createProductForm.price_solo),
    price_with_companion: Number(createProductForm.price_with_companion),
    purchase_price: Number(createProductForm.purchase_price) || 0,
    base_stock: Number(createProductForm.base_stock),
    stock_min: Number(createProductForm.stock_min) || 0,
    track_stock: !!createProductForm.track_stock,
    is_active: !!createProductForm.is_active,
  }
  if (stockMaxRaw !== '' && stockMaxRaw !== null && stockMaxRaw !== undefined) {
    payload.stock_max = Number(stockMaxRaw)
  } else {
    payload.stock_max = null
  }
  const boxRaw = createProductForm.purchase_units_per_box
  const basketRaw = createProductForm.purchase_units_per_basket
  const boxNum =
    boxRaw !== '' && boxRaw !== null && boxRaw !== undefined ? Number(boxRaw) : null
  const basketNum =
    basketRaw !== '' && basketRaw !== null && basketRaw !== undefined ? Number(basketRaw) : null
  payload.purchase_units_per_box = boxNum != null && boxNum >= 1 ? boxNum : null
  payload.purchase_units_per_basket = basketNum != null && basketNum >= 1 ? basketNum : null
  return payload
}

async function saveProduct() {
  try {
    const q = branchQuery()
    const payload = buildProductPayload()
    if (editingProductId.value) {
      await apiRequest(
        `/products/${editingProductId.value}${q}`,
        { method: 'PATCH', body: JSON.stringify(payload) },
        auth.token.value,
      )
      notify.success('Producto actualizado.')
    } else {
      await apiRequest(`/products${q}`, { method: 'POST', body: JSON.stringify(payload) }, auth.token.value)
      notify.success('Producto creado.')
    }
    closeProductModal()
    resetProductForm()
    await Promise.all([loadProducts(), loadValuedKardex()])
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo guardar el producto.')
  }
}

async function toggleProductActive(p) {
  try {
    await apiRequest(
      `/products/${p.id}${branchQuery()}`,
      { method: 'PATCH', body: JSON.stringify({ is_active: !p.is_active }) },
      auth.token.value,
    )
    notify.success(p.is_active ? 'Producto desactivado.' : 'Producto activado.')
    await loadProducts()
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo cambiar el estado.')
  }
}

async function registerMovement() {
  try {
    await apiRequest(
      `/maintenance/movements${branchQuery()}`,
      {
        method: 'POST',
        body: JSON.stringify({
          ...movementForm,
          product_id: Number(movementForm.product_id),
          quantity: Number(movementForm.quantity),
          unit_cost: movementForm.unit_cost !== null && movementForm.unit_cost !== '' ? Number(movementForm.unit_cost) : null,
        }),
      },
      auth.token.value,
    )
    notify.success('Movimiento registrado.')
    movementForm.quantity = 1
    movementForm.notes = ''
    await Promise.all([loadProducts(), loadValuedKardex(), loadKardex()])
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo registrar movimiento.')
  }
}

async function createRecipe() {
  try {
    await apiRequest(
      `/maintenance/refill-recipes${branchQuery()}`,
      {
        method: 'POST',
        body: JSON.stringify({
          ...recipeForm,
          source_product_id: Number(recipeForm.source_product_id),
          target_product_id: Number(recipeForm.target_product_id),
          source_units: Number(recipeForm.source_units),
          target_units: Number(recipeForm.target_units),
        }),
      },
      auth.token.value,
    )
    notify.success('Receta de relleno creada.')
    recipeForm.source_units = 1
    recipeForm.target_units = 1
    recipeForm.notes = ''
    await loadRecipes()
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo crear receta.')
  }
}

async function applyRecipe() {
  if (!applyRefillForm.recipe_id) return
  try {
    await apiRequest(
      `/maintenance/refill-recipes/${applyRefillForm.recipe_id}/apply${branchQuery()}`,
      {
        method: 'POST',
        body: JSON.stringify({
          batches: Number(applyRefillForm.batches),
          notes: applyRefillForm.notes || null,
        }),
      },
      auth.token.value,
    )
    notify.success('Relleno aplicado correctamente.')
    applyRefillForm.batches = 1
    applyRefillForm.notes = ''
    await Promise.all([loadProducts(), loadValuedKardex(), loadKardex()])
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo aplicar el relleno.')
  }
}

function selectRecipeForApply(id) {
  applyRefillForm.recipe_id = id
}

async function runPdfPreview(path, title, errMsg) {
  try {
    await openPdfPreview(path, title)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : errMsg)
    closePdfPreview()
  }
}

function openCatalogPdf() {
  return runPdfPreview(`/maintenance/products/pdf${branchQuery()}`, 'Catálogo de productos', 'No se pudo generar el PDF del catálogo.')
}

function openKardexProductPdf() {
  if (!kardexProductId.value) {
    notify.error('Elegí un producto en la lista.')
    return
  }
  return runPdfPreview(
    `/maintenance/products/${kardexProductId.value}/kardex/pdf${branchQuery()}`,
    `Kardex · ${selectedKardexProduct.value?.name || 'producto'}`,
    'No se pudo generar el PDF de kardex.',
  )
}

function openValuedKardexPdf() {
  return runPdfPreview(
    `/maintenance/kardex-valued/pdf${branchQuery()}`,
    'Kardex valorizado',
    'No se pudo generar el PDF del kardex valorizado.',
  )
}

function openRefillRecipesPdf() {
  return runPdfPreview(
    `/maintenance/refill-recipes/pdf${branchQuery()}`,
    'Recetas de relleno',
    'No se pudo generar el PDF de recetas.',
  )
}

async function downloadProductPdfFromModal() {
  try {
    await downloadPdfPreview('documento.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(bootstrap)
</script>

<template>
  <div class="maint-product-scope">
    <header class="admin-page-head">
      <h2>Productos de la sucursal</h2>
      <p>
        Aca ves el catalogo, cuanto se vendio, el historial de cada item y el <strong>relleno</strong>
        (cuando la botella que vendes no es la misma bebida que cargas adentro).
      </p>
    </header>

    <section class="panel">
      <div class="panel-head">
        <h3>Elegi la tarea</h3>
        <span>{{ loading ? 'Cargando datos...' : 'Toca una pestaña y segui los pasos abajo' }}</span>
      </div>

      <div class="admin-tabs" role="tablist" aria-label="Secciones de productos">
        <button
          type="button"
          role="tab"
          class="tab-btn"
          :class="{ active: tab === 'productos' }"
          :aria-selected="tab === 'productos'"
          @click="tab = 'productos'"
        >
          Lista y altas
        </button>
        <button
          type="button"
          role="tab"
          class="tab-btn"
          :class="{ active: tab === 'kardex' }"
          :aria-selected="tab === 'kardex'"
          @click="tab = 'kardex'"
        >
          Historial (kardex)
        </button>
        <button
          type="button"
          role="tab"
          class="tab-btn"
          :class="{ active: tab === 'valorizado' }"
          :aria-selected="tab === 'valorizado'"
          @click="tab = 'valorizado'"
        >
          Stock con valor
        </button>
        <button
          type="button"
          role="tab"
          class="tab-btn"
          :class="{ active: tab === 'relleno' }"
          :aria-selected="tab === 'relleno'"
          @click="tab = 'relleno'"
        >
          Relleno
        </button>
      </div>

      <!-- Lista y altas -->
      <div v-show="tab === 'productos'" class="maint-tab-panel">
        <p class="maint-tab-intro">
          Catalogo de la sucursal actual: stock y alertas reflejan esta rama. Alta y edición en el botón
          <strong>Nuevo producto</strong> / columna Acciones.
        </p>

        <div v-if="needsSitePicker" class="field-block maint-prod-site-pick">
          <span>Sucursal (catalogo y stock)</span>
          <select v-model.number="sitePickerId" @change="bootstrap">
            <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
          </select>
        </div>

        <div class="maint-products-toolbar">
          <button type="button" class="primary-btn" @click="openCreateProductModal">Nuevo producto</button>
          <button type="button" class="ghost-btn" @click="openCatalogPdf">Ver PDF catálogo</button>
          <span class="maint-products-count">{{ loading ? '…' : `${products.length} productos` }}</span>
        </div>

        <div v-if="!products.length && !loading" class="admin-empty-card">
          <p>No hay productos en esta sucursal</p>
          <small>Creá el primero con <strong>Nuevo producto</strong>.</small>
        </div>
        <div v-else-if="products.length" class="table-wrap maint-catalog-table-wrap">
          <table class="data-table maint-products-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Solo</th>
                <th>Con chica</th>
                <th>Activo</th>
                <th class="maint-col-actions">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in products" :key="p.id" :class="{ 'maint-row-inactive': !p.is_active }">
                <td>{{ p.sku }}</td>
                <td>{{ p.name }}</td>
                <td>{{ p.category_name || '—' }}</td>
                <td>{{ p.stock_actual }}</td>
                <td>
                  <span :class="stockStatusClass(p.stock_status)">{{ stockStatusLabel(p.stock_status) }}</span>
                </td>
                <td>{{ p.price_solo }}</td>
                <td>{{ p.price_with_companion }}</td>
                <td>{{ p.is_active ? 'Sí' : 'No' }}</td>
                <td class="maint-col-actions">
                  <button type="button" class="ghost-btn maint-btn-compact" @click="openEditProductModal(p)">
                    Editar
                  </button>
                  <button type="button" class="ghost-btn maint-btn-compact" @click="toggleProductActive(p)">
                    {{ p.is_active ? 'Desactivar' : 'Activar' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Kardex -->
      <div v-show="tab === 'kardex'" class="maint-tab-panel">
        <p class="maint-tab-intro">
          Elegi un producto y revisa <strong>linea por linea</strong> que paso: ventas, ingresos, ajustes y rellenos.
          La columna <strong>Saldo</strong> es el acumulado despues de cada movimiento.
        </p>

        <div class="field-block maint-kardex-select">
          <span>Producto a consultar</span>
          <select v-model.number="kardexProductId" @change="loadKardex">
            <option v-for="opt in productOptions" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
          </select>
        </div>

        <div class="maint-kardex-summary">
          <span>Stock actual del sistema: <strong>{{ selectedKardexProduct?.stock_actual ?? 0 }}</strong> unidades</span>
          <span v-if="kardexRows.length">Movimientos: <strong>{{ kardexRows.length }}</strong></span>
          <button type="button" class="ghost-btn" @click="openKardexProductPdf">Ver PDF kardex</button>
        </div>

        <div v-if="!kardexRows.length && !loading" class="admin-empty-card">
          <p>Sin movimientos todavia</p>
          <small>Cuando haya ventas, ingresos o ajustes, aparecen aca con fecha y tipo.</small>
        </div>
        <div v-else class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cant.</th>
                <th>+ / -</th>
                <th>Saldo</th>
                <th>Nota</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in kardexRows" :key="row.id">
                <td>{{ formatMovedAt(row.moved_at) }}</td>
                <td>{{ movementTypeLabel(row.movement_type) }}</td>
                <td>{{ row.quantity }}</td>
                <td>{{ row.delta }}</td>
                <td>{{ row.running_stock }}</td>
                <td>{{ row.notes || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Valorizado -->
      <div v-show="tab === 'valorizado'" class="maint-tab-panel">
        <p class="maint-tab-intro">
          <strong>Costo ref.</strong> usa el ultimo costo en movimientos de ingreso; si no hubo movimientos con costo, toma el <strong>precio de compra</strong> del producto.
          Sin control de stock, el valor mostrado es <strong>0</strong>. La ganancia aproximada por unidad vendida seria precio de venta menos costo ref.
        </p>

        <div class="maint-pdf-row">
          <button type="button" class="ghost-btn" @click="openValuedKardexPdf">Ver PDF valorizado</button>
        </div>

        <div v-if="!valuedRows.length && !loading" class="admin-empty-card">
          <p>No hay datos para mostrar</p>
          <small>Cuando registres ingresos con costo, el valor del stock se calcula solo.</small>
        </div>
        <div v-else class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Codigo</th>
                <th>Producto</th>
                <th title="Suma de entradas registradas">Entradas</th>
                <th title="Ventas, salidas y ajustes negativos">Salidas</th>
                <th title="Unidades vendidas en POS">Vendido</th>
                <th>Stock</th>
                <th>Control</th>
                <th>Compra</th>
                <th>Costo ref.</th>
                <th>Valor stock</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in valuedRows" :key="row.product_id">
                <td>{{ row.sku }}</td>
                <td>{{ row.name }}</td>
                <td>{{ row.entradas_qty }}</td>
                <td>{{ row.salidas_qty }}</td>
                <td>{{ row.sold_units }}</td>
                <td>{{ row.stock_actual }}</td>
                <td>{{ row.track_stock ? 'Si' : 'No' }}</td>
                <td>{{ row.purchase_price }}</td>
                <td>{{ row.ref_unit_cost }}</td>
                <td>{{ row.stock_valorizado }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Relleno -->
      <div v-show="tab === 'relleno'" class="maint-tab-panel">
        <p class="maint-tab-intro">
          En el boliche a veces se vende <strong>“Corona”</strong> pero se carga con otra cerveza mas barata: eso es <strong>relleno</strong>.
          Primero definis la <strong>receta</strong> (cuantas unidades de origen equivalen a cuantas del producto de venta). Despues la <strong>aplicas</strong> por lotes.
        </p>

        <div class="maint-pdf-row">
          <button type="button" class="ghost-btn" @click="openRefillRecipesPdf">Ver PDF recetas</button>
        </div>

        <div class="maint-refill-grid">
          <article class="panel">
            <div class="panel-head">
              <h3>Movimiento manual de stock</h3>
              <span>Sin receta: entrada de compra, salida, o ajuste</span>
            </div>
            <form class="maint-field-grid" @submit.prevent="registerMovement">
              <div class="field-block field-block--full">
                <span>Producto</span>
                <p v-if="!productOptionsStock.length" class="field-hint">No hay productos con control de stock. Activá la casilla al crear o editar productos.</p>
                <select v-else v-model.number="movementForm.product_id" required>
                  <option v-for="opt in productOptionsStock" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                </select>
              </div>
              <div class="field-block field-block--full">
                <span>Que tipo de movimiento es</span>
                <select v-model="movementForm.movement_kind" required>
                  <option value="ingreso">Ingreso — suma al stock (ej. compra)</option>
                  <option value="salida">Salida — resta del stock (ej. rotura, consumo interno)</option>
                  <option value="ajuste">Ajuste — corrige diferencias de inventario</option>
                </select>
                <p class="field-hint">{{ movementKindLabel(movementForm.movement_kind) }}</p>
              </div>
              <div class="field-block">
                <span>Cantidad</span>
                <input v-model.number="movementForm.quantity" type="number" min="1" required />
              </div>
              <div class="field-block">
                <span>Costo por unidad (opcional)</span>
                <p class="field-hint">Sirve para el valor del stock. Si no cargas, solo mueve cantidades.</p>
                <input v-model.number="movementForm.unit_cost" type="number" min="0" />
              </div>
              <div class="field-block field-block--full">
                <span>Comentario</span>
                <input v-model="movementForm.notes" type="text" placeholder="Ej: compra proveedor, merienda staff..." />
              </div>
              <div class="maint-form-actions">
                <button type="submit" class="primary-btn" :disabled="!productOptionsStock.length">Registrar movimiento</button>
              </div>
            </form>
          </article>

          <article class="panel">
            <div class="panel-head">
              <h3>Recetas y aplicacion</h3>
              <span>Dos pasos: crear receta, luego aplicar</span>
            </div>

            <ol class="maint-steps">
              <li><strong>Crear receta:</strong> indicar de que producto sale y a cual “botella de venta” entra, y la proporcion.</li>
              <li><strong>Aplicar relleno:</strong> cuantas veces repetis esa receta (lotes).</li>
            </ol>

            <p class="maint-table-caption" style="margin-top: 14px">Paso 1 — Nueva receta</p>
            <p v-if="productOptionsStock.length < 2" class="field-hint maint-recipe-warn">Necesitas al menos dos productos con control de stock para armar una receta.</p>
            <form class="maint-field-grid" @submit.prevent="createRecipe">
              <div class="field-block field-block--full">
                <span>Se descuenta de (origen real)</span>
                <p class="field-hint">Ej: lata Moema, bidon, insumo barato.</p>
                <select v-model.number="recipeForm.source_product_id" required>
                  <option v-for="opt in productOptionsStock" :key="`src-${opt.id}`" :value="opt.id">{{ opt.name }}</option>
                </select>
              </div>
              <div class="field-block field-block--full">
                <span>Se suma a (lo que vendes)</span>
                <p class="field-hint">Ej: botella Corona en carta.</p>
                <select v-model.number="recipeForm.target_product_id" required>
                  <option v-for="opt in productOptionsStock" :key="`dst-${opt.id}`" :value="opt.id">{{ opt.name }}</option>
                </select>
              </div>
              <div class="field-block">
                <span>Unidades que gastas del origen</span>
                <input v-model.number="recipeForm.source_units" type="number" min="1" required />
              </div>
              <div class="field-block">
                <span>Unidades que generas en destino</span>
                <input v-model.number="recipeForm.target_units" type="number" min="1" required />
              </div>
              <div class="field-block field-block--full">
                <span>Nota (opcional)</span>
                <input v-model="recipeForm.notes" type="text" />
              </div>
              <div class="maint-form-actions">
                <button type="submit" class="primary-btn" :disabled="productOptionsStock.length < 2">Guardar receta</button>
              </div>
            </form>

            <p class="maint-table-caption">Tus recetas guardadas</p>
            <div v-if="!recipes.length" class="admin-empty-card">
              <p>Todavia no hay recetas</p>
              <small>Cuando guardes la primera, aparece en esta lista.</small>
            </div>
            <div v-else class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Origen → venta</th>
                    <th>Proporcion</th>
                    <th>Usar en paso 2</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in recipes" :key="r.id">
                    <td>{{ r.source_name }} → {{ r.target_name }}</td>
                    <td>{{ r.source_units }} origen = {{ r.target_units }} venta</td>
                    <td>
                      <button type="button" class="ghost-btn" @click="selectRecipeForApply(r.id)">Elegir para aplicar</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p class="maint-table-caption">Paso 2 — Aplicar relleno</p>
            <form class="maint-field-grid" @submit.prevent="applyRecipe">
              <div class="field-block field-block--full">
                <span>Receta</span>
                <select v-model.number="applyRefillForm.recipe_id" required>
                  <option disabled :value="null">Seleccionar...</option>
                  <option v-for="r in recipes" :key="`apply-${r.id}`" :value="r.id">
                    {{ r.source_name }} → {{ r.target_name }} ({{ r.source_units }}:{{ r.target_units }})
                  </option>
                </select>
              </div>
              <div class="field-block">
                <span>Cuantas veces (lotes)</span>
                <p class="field-hint">Cada lote aplica la proporcion completa de la receta.</p>
                <input v-model.number="applyRefillForm.batches" type="number" min="1" required />
              </div>
              <div class="field-block field-block--full">
                <span>Nota del movimiento</span>
                <input v-model="applyRefillForm.notes" type="text" placeholder="Ej: turno noche, barra principal" />
              </div>
              <div class="maint-form-actions">
                <button type="submit" class="primary-btn">Aplicar relleno</button>
              </div>
            </form>
          </article>
        </div>
      </div>
    </section>

    <div v-if="productModalOpen" class="maint-product-modal-overlay" @click.self="closeProductModal">
      <article class="panel maint-product-modal-card" @click.stop>
        <div class="panel-head">
          <h3>{{ editingProductId ? 'Editar producto' : 'Nuevo producto' }}</h3>
          <button type="button" class="ghost-btn" @click="closeProductModal">Cerrar</button>
        </div>
        <form class="maint-field-grid" @submit.prevent="saveProduct">
          <div class="field-block">
            <span>Código (SKU)</span>
            <p class="field-hint">Único en todo el sistema.</p>
            <input v-model="createProductForm.sku" type="text" required autocomplete="off" />
          </div>
          <div class="field-block">
            <span>Nombre</span>
            <input v-model="createProductForm.name" type="text" required />
          </div>
          <div class="field-block field-block--full">
            <span>Categoría</span>
            <div class="maint-category-inline">
              <select
                v-model.number="createProductForm.category_id"
                class="maint-category-select"
                required
                :disabled="!categories.length"
              >
                <option v-if="!categories.length" disabled :value="null">— Sin categorías —</option>
                <option v-for="c in categories" :key="c.id" :value="Number(c.id)">{{ c.name }}</option>
              </select>
              <button type="button" class="ghost-btn maint-category-new-btn" @click="categoryModalOpen = true">
                + Nueva
              </button>
            </div>
            <p v-if="!categories.length" class="field-hint">
              Creá una categoría con <strong>+ Nueva</strong> o desde Administración → Categorías.
            </p>
          </div>
          <div class="field-block">
            <span>Precio venta solo</span>
            <input v-model.number="createProductForm.price_solo" type="number" min="0" required />
          </div>
          <div class="field-block">
            <span>Precio con chica</span>
            <input v-model.number="createProductForm.price_with_companion" type="number" min="0" required />
          </div>
          <div class="field-block field-block--full">
            <span>Stock en esta sucursal</span>
            <p class="field-hint">Unidades en depósito / barra de la sucursal actual.</p>
            <input v-model.number="createProductForm.base_stock" type="number" min="0" required />
          </div>
          <div class="field-block">
            <span>Precio de compra</span>
            <input v-model.number="createProductForm.purchase_price" type="number" min="0" />
          </div>
          <div class="field-block">
            <span>Unid. por caja (compras)</span>
            <p class="field-hint">Opcional. Se sugiere al registrar compra por caja.</p>
            <input
              v-model="createProductForm.purchase_units_per_box"
              type="number"
              min="1"
              placeholder="Ej. 12"
            />
          </div>
          <div class="field-block">
            <span>Unid. por canastillo</span>
            <p class="field-hint">Opcional. Igual que arriba, para canastillos.</p>
            <input
              v-model="createProductForm.purchase_units_per_basket"
              type="number"
              min="1"
              placeholder="Ej. 24"
            />
          </div>
          <div class="field-block">
            <span>Stock mínimo</span>
            <input v-model.number="createProductForm.stock_min" type="number" min="0" />
          </div>
          <div class="field-block">
            <span>Stock máximo</span>
            <input v-model="createProductForm.stock_max" type="number" min="0" placeholder="Opcional" />
          </div>
          <div class="field-block field-block--full maint-track-row">
            <label class="maint-check-label">
              <input v-model="createProductForm.track_stock" type="checkbox" />
              <span>Control de stock</span>
            </label>
          </div>
          <div class="field-block field-block--full maint-track-row">
            <label class="maint-check-label">
              <input v-model="createProductForm.is_active" type="checkbox" />
              <span>Visible y vendible en esta sede</span>
            </label>
          </div>
          <div class="maint-form-actions maint-modal-actions">
            <button type="button" class="ghost-btn" @click="closeProductModal">Cancelar</button>
            <button type="submit" class="primary-btn">{{ editingProductId ? 'Guardar cambios' : 'Crear producto' }}</button>
          </div>
        </form>
      </article>
    </div>

    <QuickCategoryModal v-model="categoryModalOpen" @created="onQuickCategoryCreated" />

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa PDF"
      @close="closePdfPreview"
      @download="downloadProductPdfFromModal"
    />
  </div>
</template>

<style scoped>
.maint-category-inline {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.maint-category-select {
  flex: 1;
  min-width: 12rem;
}

.maint-category-new-btn {
  flex-shrink: 0;
  white-space: nowrap;
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

.maint-products-table .maint-col-actions {
  white-space: nowrap;
  text-align: right;
}

.maint-btn-compact {
  display: inline-block;
  margin-left: 0.35rem;
}

.maint-btn-compact:first-child {
  margin-left: 0;
}

.maint-row-inactive td {
  opacity: 0.65;
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
  max-width: 36rem;
  max-height: 92vh;
  overflow: auto;
}

.maint-modal-actions {
  justify-content: flex-end;
  gap: 0.5rem;
}

.maint-pdf-row {
  margin-bottom: 0.75rem;
}

.maint-kardex-summary {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 1rem;
  margin-bottom: 0.75rem;
}
</style>
