<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import QuickCategoryModal from '../components/QuickCategoryModal.vue'
import { useAuthStore } from '../stores/authStore'
import { apiRequest } from '../services/api'
import { useNotificationStore } from '../stores/notificationStore'

const auth = useAuthStore()
const notify = useNotificationStore()
const isWaiter = computed(() => auth.user.value?.role === 'waiter')
const activeProducts = computed(() => (products.value || []).filter((p) => p.is_active !== false))
const products = ref([])
const categories = ref([])
const loading = ref(false)
const message = ref('')
const categoryModalOpen = ref(false)

function sortCategoriesList(list) {
  return [...list].sort((a, b) => {
    const so = (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0)
    if (so !== 0) return so
    return String(a.name || '').localeCompare(String(b.name || ''), 'es')
  })
}

function onCategoryCreated(cat) {
  const row = {
    id: cat.id,
    slug: cat.slug,
    name: cat.name,
    sort_order: cat.sort_order,
    product_type: cat.product_type,
  }
  categories.value = sortCategoriesList([...categories.value, row])
  form.category_id = row.id
}

const form = reactive({
  sku: '',
  name: '',
  category_id: null,
  price_solo: 0,
  price_with_companion: 0,
  purchase_price: 0,
  base_stock: 0,
  stock_min: 0,
  stock_max: '',
  track_stock: true,
})

function alertLabel(alert) {
  const map = {
    ok: 'OK',
    low: 'Bajo min.',
    over: 'Sobre max.',
    untracked: 'Sin control',
  }
  return map[alert] || alert
}

async function loadCategories() {
  if (!auth.canManageProducts.value) return
  try {
    const payload = await apiRequest('/product-categories', {}, auth.token.value)
    categories.value = payload.data || []
    if (!form.category_id && categories.value.length) {
      form.category_id = categories.value[0].id
    }
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudieron cargar las categorías.')
  }
}

async function loadProducts() {
  loading.value = true
  try {
    const payload = await apiRequest('/products', {}, auth.token.value)
    products.value = payload.data || []
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar productos.'
    notify.error(message.value)
  } finally {
    loading.value = false
  }
}

async function createProduct() {
  try {
    const stockMaxRaw = form.stock_max
    const payload = {
      sku: form.sku,
      name: form.name,
      category_id: Number(form.category_id),
      price_solo: Number(form.price_solo),
      price_with_companion: Number(form.price_with_companion),
      purchase_price: Number(form.purchase_price) || 0,
      base_stock: Number(form.base_stock),
      stock_min: Number(form.stock_min) || 0,
      track_stock: !!form.track_stock,
    }
    if (stockMaxRaw !== '' && stockMaxRaw !== null && stockMaxRaw !== undefined) {
      payload.stock_max = Number(stockMaxRaw)
    } else {
      payload.stock_max = null
    }

    await apiRequest('/products', {
      method: 'POST',
      body: JSON.stringify(payload),
    }, auth.token.value)
    message.value = 'Producto creado correctamente.'
    notify.success(message.value)
    const defaultCat = categories.value[0]?.id ?? null
    Object.assign(form, {
      sku: '',
      name: '',
      category_id: defaultCat,
      price_solo: 0,
      price_with_companion: 0,
      purchase_price: 0,
      base_stock: 0,
      stock_min: 0,
      stock_max: '',
      track_stock: true,
    })
    await loadProducts()
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo crear el producto.'
    notify.error(message.value)
  }
}

onMounted(async () => {
  if (!isWaiter.value) {
    await loadCategories()
  }
  await loadProducts()
})
</script>

<template>
  <AppLayout>
    <p v-if="message && !isWaiter" class="info-text">{{ message }}</p>

    <template v-if="isWaiter">
      <section class="waiter-catalog">
        <div class="panel panel-muted waiter-catalog-intro">
          <h3 class="waiter-catalog-h">Carta</h3>
          <p class="waiter-catalog-p">
            {{ loading ? 'Cargando…' : `${activeProducts.length} productos activos` }}
          </p>
        </div>
        <p v-if="!loading && !activeProducts.length" class="waiter-catalog-empty">No hay productos para mostrar.</p>
        <ul v-else class="waiter-catalog-list">
          <li v-for="item in activeProducts" :key="item.id" class="waiter-product-card">
            <div class="waiter-product-top">
              <span class="waiter-product-name">{{ item.name }}</span>
              <span class="waiter-product-sku">{{ item.sku }}</span>
            </div>
            <div class="waiter-product-prices">
              <span><em>Solo</em> {{ (Number(item.price_solo) || 0).toLocaleString('es-AR') }}</span>
              <span><em>+ chica</em> {{ (Number(item.price_with_companion) || 0).toLocaleString('es-AR') }}</span>
            </div>
          </li>
        </ul>
      </section>
    </template>

    <section v-else class="content-grid">
      <article class="panel">
        <div class="panel-head">
          <h3>Catálogo de productos</h3>
          <span>{{ loading ? 'Cargando...' : `${products.length} registros` }}</span>
        </div>
        <p class="admin-hint products-catalog-hint">
          La columna <strong>Alerta</strong> usa min/max: bajo minimo para reponer, sobre maximo si hay demasia.
          <strong>Sin control</strong> = no descuenta stock al vender (cortesias, covers, etc.).
        </p>
        <div class="table-wrap products-table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Min</th>
                <th>Max</th>
                <th>Alerta</th>
                <th>Compra</th>
                <th>Solo</th>
                <th>Con chica</th>
                <th>Control stock</th>
                <th>Activo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in products" :key="item.id">
                <td>{{ item.sku }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.category_name || '—' }}</td>
                <td>{{ item.base_stock }}</td>
                <td>{{ item.stock_min }}</td>
                <td>{{ item.stock_max ?? '—' }}</td>
                <td>{{ alertLabel(item.stock_alert) }}</td>
                <td>{{ item.purchase_price }}</td>
                <td>{{ item.price_solo }}</td>
                <td>{{ item.price_with_companion }}</td>
                <td>{{ item.track_stock ? 'Sí' : 'No' }}</td>
                <td>{{ item.is_active ? 'Sí' : 'No' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <article v-if="auth.canManageProducts.value" class="panel">
        <div class="panel-head">
          <h3>Crear producto</h3>
          <span>Precios de venta, compra y reglas de stock</span>
        </div>
        <form class="form-grid products-create-form" @submit.prevent="createProduct">
          <input v-model="form.sku" placeholder="SKU" required />
          <input v-model="form.name" placeholder="Nombre" required />
          <div class="products-category-field">
            <label class="products-category-label">Categoría</label>
            <div class="products-category-row">
              <select v-model.number="form.category_id" class="products-category-select" required>
                <option v-if="!categories.length" disabled :value="null">— Creá una categoría con + Nueva —</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <button
                type="button"
                class="ghost-btn products-category-add-btn"
                @click="categoryModalOpen = true"
              >
                + Nueva
              </button>
            </div>
            <p v-if="!categories.length" class="products-category-hint">
              No hay categorías: usá <strong>+ Nueva</strong> o creadas en Administración → Categorías.
            </p>
          </div>
          <input v-model.number="form.price_solo" type="number" min="0" placeholder="Precio solo" required />
          <input v-model.number="form.price_with_companion" type="number" min="0" placeholder="Precio con chica" required />
          <input v-model.number="form.purchase_price" type="number" min="0" placeholder="Precio de compra" />
          <input v-model.number="form.base_stock" type="number" min="0" placeholder="Stock inicial" required />
          <input v-model.number="form.stock_min" type="number" min="0" placeholder="Stock mínimo (alerta)" />
          <input v-model="form.stock_max" type="number" min="0" placeholder="Stock máx. (opcional)" />
          <label class="products-check-row">
            <input v-model="form.track_stock" type="checkbox" />
            <span>Controlar stock (ventas descuentan unidades)</span>
          </label>
          <button class="primary-btn" type="submit">Guardar producto</button>
        </form>
      </article>
    </section>

    <QuickCategoryModal v-model="categoryModalOpen" @created="onCategoryCreated" />
  </AppLayout>
</template>

<style scoped>
.waiter-catalog {
  display: grid;
  gap: 12px;
}

.waiter-catalog-intro {
  padding: 14px 16px !important;
}

.waiter-catalog-h {
  margin: 0 0 4px;
  font-size: 1.15rem;
  font-weight: 800;
}

.waiter-catalog-p {
  margin: 0;
  font-size: 0.88rem;
  opacity: 0.85;
}

.waiter-catalog-empty {
  text-align: center;
  padding: 1.5rem;
  opacity: 0.8;
}

.waiter-catalog-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

.waiter-product-card {
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.25));
  background: var(--panel-muted-bg, rgba(25, 40, 85, 0.35));
}

.waiter-product-top {
  display: grid;
  gap: 4px;
  margin-bottom: 10px;
}

.waiter-product-name {
  font-weight: 800;
  font-size: 1rem;
  line-height: 1.25;
}

.waiter-product-sku {
  font-size: 0.78rem;
  opacity: 0.75;
  font-family: ui-monospace, monospace;
}

.waiter-product-prices {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 18px;
  font-size: 0.92rem;
  font-weight: 700;
}

.waiter-product-prices em {
  font-style: normal;
  font-weight: 600;
  opacity: 0.75;
  margin-right: 4px;
  font-size: 0.82rem;
}

.products-category-field {
  grid-column: 1 / -1;
  display: grid;
  gap: 6px;
}

.products-category-label {
  font-size: 0.82rem;
  font-weight: 700;
  opacity: 0.9;
}

.products-category-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.products-category-select {
  flex: 1;
  min-width: 160px;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid rgba(145, 175, 255, 0.28);
  background: rgba(8, 14, 32, 0.55);
  color: inherit;
  font: inherit;
}

.products-category-add-btn {
  flex-shrink: 0;
  white-space: nowrap;
}

.products-category-hint {
  margin: 0;
  font-size: 0.8rem;
  opacity: 0.85;
  line-height: 1.35;
}
</style>
