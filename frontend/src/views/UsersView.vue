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
  name: '',
  email: '',
  password: 'password',
  role: 'cashier',
  site_id: '',
})

async function createUser() {
  try {
    await apiRequest('/users', {
      method: 'POST',
      body: JSON.stringify({
        ...form,
        site_id: form.site_id ? Number(form.site_id) : null,
      }),
    }, auth.token.value)
    message.value = 'Usuario creado correctamente.'
    notify.success(message.value)
    Object.assign(form, { name: '', email: '', password: 'password', role: 'cashier', site_id: '' })
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo crear el usuario.'
    notify.error(message.value)
  }
}
</script>

<template>
  <AppLayout>
    <p v-if="message" class="info-text">{{ message }}</p>
    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Crear usuario</h3><span>Jerarquia SaaS</span></div>
        <form class="form-grid" @submit.prevent="createUser">
          <input v-model="form.name" placeholder="Nombre" required />
          <input v-model="form.email" type="email" placeholder="Email" required />
          <input v-model="form.password" type="text" placeholder="Contrasena" required />
          <select v-model="form.role">
            <option value="admin">Admin</option>
            <option value="cashier">Cajera</option>
            <option value="waiter">Garzon</option>
            <option value="manager">Encargada</option>
            <option v-if="auth.isOwner.value" value="super_admin">Super Admin</option>
          </select>
          <input v-model="form.site_id" type="number" min="1" placeholder="ID sucursal (opcional)" />
          <button class="primary-btn" type="submit">Crear usuario</button>
        </form>
      </article>
    </section>
  </AppLayout>
</template>
