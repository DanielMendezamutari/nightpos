<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '../../stores/authStore'
import { apiRequest } from '../../services/api'
import { useNotificationStore } from '../../stores/notificationStore'

const auth = useAuthStore()
const notify = useNotificationStore()

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const editingId = ref(null)
const modalOpen = ref(false)

const form = reactive({
  name: '',
  slug: '',
  sort_order: '',
  product_type: 'drink',
})

const tipoLabel = computed(() => ({
  drink: 'Para vender (bar)',
  supply: 'Insumo / barra',
}))

function resetForm() {
  editingId.value = null
  form.name = ''
  form.slug = ''
  form.sort_order = ''
  form.product_type = 'drink'
}

function closeModal() {
  modalOpen.value = false
  resetForm()
}

function openCreateModal() {
  resetForm()
  modalOpen.value = true
}

async function loadCategories() {
  loading.value = true
  try {
    const payload = await apiRequest('/product-categories', {}, auth.token.value)
    categories.value = payload.data || []
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudieron cargar las categorías.')
  } finally {
    loading.value = false
  }
}

function startEdit(row) {
  editingId.value = row.id
  form.name = row.name
  form.slug = row.slug
  form.sort_order = String(row.sort_order ?? '')
  form.product_type = row.product_type
  modalOpen.value = true
}

async function submitForm() {
  saving.value = true
  try {
    const body = {
      name: form.name.trim(),
      product_type: form.product_type,
    }
    const slug = form.slug.trim()
    if (slug) body.slug = slug
    const so = form.sort_order === '' ? undefined : Number(form.sort_order)
    if (so !== undefined && !Number.isNaN(so)) body.sort_order = so

    if (editingId.value) {
      await apiRequest(
        `/product-categories/${editingId.value}`,
        { method: 'PATCH', body: JSON.stringify(body) },
        auth.token.value,
      )
      notify.success('Categoría actualizada.')
    } else {
      await apiRequest('/product-categories', { method: 'POST', body: JSON.stringify(body) }, auth.token.value)
      notify.success('Categoría creada.')
    }
    closeModal()
    await loadCategories()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar.')
  } finally {
    saving.value = false
  }
}

async function removeCategory(row) {
  const ok = window.confirm(`¿Borrar la categoría «${row.name}»? Solo se puede si no tiene productos.`)
  if (!ok) return
  try {
    await apiRequest(`/product-categories/${row.id}`, { method: 'DELETE' }, auth.token.value)
    notify.success('Categoría borrada.')
    if (editingId.value === row.id) closeModal()
    await loadCategories()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo borrar.')
  }
}

onMounted(loadCategories)
</script>

<template>
  <div class="admin-page-head">
    <h2>Categorías</h2>
    <p>Agrupá productos (bebidas, tragos, comida, insumos). Se usan al crear productos y en el catálogo.</p>
  </div>

  <section class="panel">
    <div class="maint-products-toolbar">
      <button type="button" class="primary-btn" @click="openCreateModal">Nueva categoría</button>
      <span class="maint-products-count">{{ loading ? '…' : `${categories.length} categorías` }}</span>
    </div>
    <div class="table-wrap">
      <table class="data-table admin-cat-table">
        <thead>
          <tr>
            <th>Orden</th>
            <th>Nombre</th>
            <th>Código</th>
            <th>Tipo</th>
            <th class="admin-cat-row-actions">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in categories" :key="row.id" :class="{ 'row-editing': editingId === row.id && modalOpen }">
            <td>{{ row.sort_order }}</td>
            <td>{{ row.name }}</td>
            <td><code class="admin-cat-slug">{{ row.slug }}</code></td>
            <td>{{ tipoLabel[row.product_type] || row.product_type }}</td>
            <td class="admin-cat-row-actions">
              <button type="button" class="ghost-btn ghost-btn-sm" @click="startEdit(row)">Editar</button>
              <button type="button" class="ghost-btn ghost-btn-sm admin-cat-btn-danger" @click="removeCategory(row)">
                Borrar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <div v-if="modalOpen" class="maint-product-modal-overlay" @click.self="closeModal">
    <article class="panel maint-product-modal-card" @click.stop>
      <div class="panel-head">
        <h3>{{ editingId ? 'Editar categoría' : 'Nueva categoría' }}</h3>
        <button type="button" class="ghost-btn" @click="closeModal">Cerrar</button>
      </div>
      <p class="modal-lead">
        {{ editingId ? 'El código interno no se modifica desde acá.' : 'Si dejás el código vacío, se genera solo.' }}
      </p>
      <form class="form-grid admin-cat-form" @submit.prevent="submitForm">
        <label class="admin-cat-field">
          Nombre visible
          <input v-model="form.name" type="text" required maxlength="120" placeholder="Ej. Cervezas importadas" />
        </label>
        <label class="admin-cat-field">
          Código interno (opcional)
          <input
            v-model="form.slug"
            type="text"
            maxlength="64"
            placeholder="solo_minusculas_y_guion_bajo"
            :disabled="!!editingId"
          />
        </label>
        <label class="admin-cat-field">
          Orden en lista
          <input v-model="form.sort_order" type="number" min="0" max="65535" placeholder="Auto si vacío" />
        </label>
        <label class="admin-cat-field">
          Tipo
          <select v-model="form.product_type">
            <option value="drink">{{ tipoLabel.drink }}</option>
            <option value="supply">{{ tipoLabel.supply }}</option>
          </select>
        </label>
        <div class="admin-cat-actions maint-modal-actions">
          <button type="button" class="ghost-btn" @click="closeModal">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">
            {{ saving ? 'Guardando…' : editingId ? 'Guardar cambios' : 'Crear categoría' }}
          </button>
        </div>
      </form>
    </article>
  </div>
</template>

<style scoped>
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

.modal-lead {
  margin: 0 0 1rem;
  font-size: 0.88rem;
  color: var(--color-muted, #a8bcee);
}

.admin-cat-form {
  align-items: end;
}

.admin-cat-field {
  display: grid;
  gap: 6px;
  margin: 0;
  font-size: 0.85rem;
  color: var(--muted, #a8bcee);
}

.admin-cat-field input,
.admin-cat-field select {
  min-height: 42px;
}

.admin-cat-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  justify-content: flex-end;
  grid-column: 1 / -1;
}

.maint-modal-actions {
  margin-top: 0.5rem;
}

.ghost-btn-sm {
  padding: 6px 10px;
  font-size: 0.8rem;
}

.admin-cat-btn-danger {
  color: #ff9e9e;
}

.admin-cat-slug {
  font-size: 0.8rem;
  padding: 2px 6px;
  border-radius: 6px;
  background: rgba(0, 0, 0, 0.2);
}

.row-editing {
  outline: 1px solid rgba(113, 215, 255, 0.45);
  background: rgba(92, 129, 255, 0.08);
}

.admin-cat-row-actions {
  white-space: nowrap;
  text-align: right;
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
  max-width: 32rem;
  max-height: 92vh;
  overflow: auto;
}

@media (max-width: 640px) {
  .admin-cat-row-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: stretch;
  }
}
</style>
