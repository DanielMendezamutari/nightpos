<script setup>
import { reactive, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useAuthStore } from '../stores/authStore'
import { apiRequest } from '../services/api'
import { useNotificationStore } from '../stores/notificationStore'

const auth = useAuthStore()
const notify = useNotificationStore()
const message = ref('')

const form = reactive({
  is_locked: false,
  reason: '',
})

async function updateLock() {
  try {
    await apiRequest('/system/lock', {
      method: 'PATCH',
      body: JSON.stringify(form),
    }, auth.token.value)
    message.value = form.is_locked ? 'Sistema bloqueado correctamente.' : 'Sistema desbloqueado correctamente.'
    if (form.is_locked) notify.warning(message.value)
    else notify.success(message.value)
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo actualizar el estado del sistema.'
    notify.error(message.value)
  }
}
</script>

<template>
  <AppLayout>
    <p v-if="message" class="info-text">{{ message }}</p>
    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Bloqueo global SaaS</h3><span>Solo Owner</span></div>
        <form class="form-grid" @submit.prevent="updateLock">
          <select v-model="form.is_locked">
            <option :value="false">Desbloquear sistema</option>
            <option :value="true">Bloquear sistema</option>
          </select>
          <input v-model="form.reason" placeholder="Motivo (ej: Mensualidad vencida)" />
          <button class="primary-btn" type="submit">Actualizar estado</button>
        </form>
      </article>
    </section>
  </AppLayout>
</template>
