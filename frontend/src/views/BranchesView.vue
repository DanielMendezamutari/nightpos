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
  code: '',
  name: '',
  is_active: true,
  monthly_fee: 700,
  billing_contact_name: '',
  billing_contact_phone: '',
  billing_contact_email: '',
})

async function createBranch() {
  try {
    const payload = await apiRequest('/branches', {
      method: 'POST',
      body: JSON.stringify({
        ...form,
        monthly_fee: Number(form.monthly_fee),
      }),
    }, auth.token.value)
    message.value = `Sucursal creada: ${payload.data.name}. Cobro mensual: Bs ${payload.data.monthly_fee}. Responsable de cobro: ${payload.data.billing_contact_name}.`
    notify.success(message.value)
    Object.assign(form, {
      code: '',
      name: '',
      is_active: true,
      monthly_fee: 700,
      billing_contact_name: '',
      billing_contact_phone: '',
      billing_contact_email: '',
    })
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo crear la sucursal.'
    notify.error(message.value)
  }
}
</script>

<template>
  <AppLayout>
    <p v-if="message" class="info-text">{{ message }}</p>
    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Crear sucursal</h3><span>Solo Owner</span></div>
        <form class="form-grid" @submit.prevent="createBranch">
          <input v-model="form.code" placeholder="Codigo (ej: CASA22)" required />
          <input v-model="form.name" placeholder="Nombre de sucursal" required />
          <input v-model.number="form.monthly_fee" type="number" min="1" placeholder="Mensualidad SaaS (Bs)" required />
          <input v-model="form.billing_contact_name" placeholder="Responsable de cobro" required />
          <input v-model="form.billing_contact_phone" placeholder="Telefono de cobro (opcional)" />
          <input v-model="form.billing_contact_email" type="email" placeholder="Email de cobro (opcional)" />
          <button class="primary-btn" type="submit">Guardar sucursal</button>
        </form>
      </article>
    </section>
  </AppLayout>
</template>
