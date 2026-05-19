<script setup>
import { reactive, ref, watch } from 'vue'
import { apiRequest } from '../services/api'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'created'])

const auth = useAuthStore()
const notify = useNotificationStore()

const form = reactive({
  name: '',
  product_type: 'drink',
  slug: '',
})
const saving = ref(false)

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      form.name = ''
      form.product_type = 'drink'
      form.slug = ''
      saving.value = false
    }
  },
)

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  const name = form.name.trim()
  if (!name) {
    notify.error('Indicá el nombre de la categoría.')
    return
  }
  saving.value = true
  try {
    const body = {
      name,
      product_type: form.product_type,
    }
    const slug = form.slug.trim()
    if (slug) {
      body.slug = slug
    }
    const payload = await apiRequest(
      '/product-categories',
      { method: 'POST', body: JSON.stringify(body) },
      auth.token.value,
    )
    const data = payload.data
    notify.success('Categoría creada.')
    emit('created', data)
    close()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo crear la categoría.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div v-if="modelValue" class="modal-overlay quick-category-overlay" @click.self="close">
    <div class="modal-card quick-category-card" @click.stop>
      <div class="modal-head">
        <h3>Nueva categoría</h3>
        <button type="button" class="ghost-btn" @click="close">Cerrar</button>
      </div>
      <p class="quick-category-intro">
        Se crea al instante y queda seleccionada para este producto. No salís de esta pantalla.
      </p>
      <form class="form-grid quick-category-form" @submit.prevent="submit">
        <label>
          Nombre
          <input v-model="form.name" type="text" required maxlength="120" autocomplete="off" />
        </label>
        <label>
          Tipo de productos
          <select v-model="form.product_type">
            <option value="drink">Bebida</option>
            <option value="supply">Insumo</option>
          </select>
        </label>
        <label class="quick-category-slug">
          Slug (opcional)
          <input v-model="form.slug" type="text" maxlength="64" placeholder="Se genera solo si lo dejás vacío" />
        </label>
        <div class="profile-form-actions">
          <button type="button" class="ghost-btn" :disabled="saving" @click="close">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">
            {{ saving ? 'Guardando…' : 'Crear categoría' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.quick-category-intro {
  margin: 0 0 14px;
  font-size: 0.88rem;
  opacity: 0.88;
  line-height: 1.4;
}

.quick-category-form {
  display: grid;
  gap: 12px;
}

.quick-category-form label {
  display: grid;
  gap: 6px;
  font-size: 0.88rem;
  font-weight: 600;
}

.quick-category-form input,
.quick-category-form select {
  font: inherit;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid rgba(145, 175, 255, 0.28);
  background: rgba(8, 14, 32, 0.65);
  color: inherit;
}

.quick-category-slug {
  grid-column: 1 / -1;
}

.quick-category-overlay {
  z-index: 180;
}
</style>
