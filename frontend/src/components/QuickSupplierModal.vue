<script setup>
import { reactive, ref, watch } from 'vue'
import { apiRequest } from '../services/api'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  /** Mismo sufijo que `branchQuery()` (ej. `?site_id=3` o vacío). */
  branchSuffix: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'created'])

const auth = useAuthStore()
const notify = useNotificationStore()

const form = reactive({
  display_name: '',
  business_name: '',
  phone: '',
  email: '',
  service_category: '',
})
const saving = ref(false)

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      form.display_name = ''
      form.business_name = ''
      form.phone = ''
      form.email = ''
      form.service_category = ''
      saving.value = false
    }
  },
)

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  const name = form.display_name.trim()
  if (!name) {
    notify.error('Indicá el nombre o razón del proveedor.')
    return
  }
  saving.value = true
  try {
    const body = {
      contact_type: 'supplier',
      display_name: name,
      phone: form.phone.trim() || null,
      email: form.email.trim() || null,
      business_name: form.business_name.trim() || null,
      service_category: form.service_category.trim() || null,
    }
    const url = `/branch/contacts${props.branchSuffix || ''}`
    const payload = await apiRequest(url, { method: 'POST', body: JSON.stringify(body) }, auth.token.value)
    const data = payload.data
    notify.success('Proveedor agregado.')
    emit('created', {
      id: data.id,
      display_name: data.display_name,
      contact_type: data.contact_type ?? 'supplier',
    })
    close()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo crear el proveedor.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div v-if="modelValue" class="modal-overlay quick-supplier-overlay" @click.self="close">
    <div class="modal-card quick-supplier-card" @click.stop>
      <div class="modal-head">
        <h3>Nuevo proveedor</h3>
        <button type="button" class="ghost-btn" @click="close">Cerrar</button>
      </div>
      <p class="quick-supplier-intro">
        Queda asociado a <strong>esta sucursal</strong> y se selecciona en la compra. No salís de esta pantalla.
      </p>
      <form class="form-grid quick-supplier-form" @submit.prevent="submit">
        <label>
          Nombre o razón social
          <input v-model="form.display_name" type="text" required maxlength="140" autocomplete="organization" />
        </label>
        <label>
          Nombre comercial (opcional)
          <input v-model="form.business_name" type="text" maxlength="160" />
        </label>
        <label>
          Rubro / categoría (opcional)
          <input v-model="form.service_category" type="text" maxlength="80" placeholder="Ej. bebidas, descartables" />
        </label>
        <label>
          Teléfono
          <input v-model="form.phone" type="text" maxlength="40" inputmode="tel" />
        </label>
        <label>
          Email
          <input v-model="form.email" type="email" maxlength="140" />
        </label>
        <div class="profile-form-actions">
          <button type="button" class="ghost-btn" :disabled="saving" @click="close">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">
            {{ saving ? 'Guardando…' : 'Guardar proveedor' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.quick-supplier-intro {
  margin: 0 0 14px;
  font-size: 0.88rem;
  opacity: 0.9;
  line-height: 1.4;
}

.quick-supplier-form {
  display: grid;
  gap: 12px;
}

.quick-supplier-form label {
  display: grid;
  gap: 6px;
  font-size: 0.88rem;
  font-weight: 600;
}

.quick-supplier-form input {
  font: inherit;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid rgba(145, 175, 255, 0.28);
  background: rgba(8, 14, 32, 0.65);
  color: inherit;
}

.quick-supplier-overlay {
  z-index: 200;
}
</style>
