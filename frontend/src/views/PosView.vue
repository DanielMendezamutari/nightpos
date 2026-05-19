<script setup>
import { onMounted, reactive, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { apiRequest } from '../services/api'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'

const auth = useAuthStore()
const notify = useNotificationStore()
const products = ref([])
const message = ref('')

const form = reactive({
  order_id: '',
  product_id: '',
  waiter_id: '',
  companion_id: '',
  quantity: 1,
  consumption_type: 'solo',
})

onMounted(async () => {
  form.waiter_id = auth.user.value?.id || ''
  await loadProducts()
})

async function loadProducts() {
  try {
    const payload = await apiRequest('/products', {}, auth.token.value)
    products.value = payload.data || []
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar productos.'
    notify.error(message.value)
  }
}

async function sendOrderItem() {
  try {
    await apiRequest('/orders/items', {
      method: 'POST',
      body: JSON.stringify({
        order_id: Number(form.order_id),
        product_id: Number(form.product_id),
        waiter_id: Number(form.waiter_id),
        companion_id: form.companion_id ? Number(form.companion_id) : null,
        quantity: Number(form.quantity),
        consumption_type: form.consumption_type,
      }),
    }, auth.token.value)
    message.value = 'Comanda registrada correctamente.'
    notify.success(message.value)
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo registrar la comanda.'
    notify.error(message.value)
  }
}
</script>

<template>
  <AppLayout>
    <p v-if="message" class="info-text">{{ message }}</p>
    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>POS Garzon</h3><span>Comanda en tiempo real</span></div>
        <form class="form-grid" @submit.prevent="sendOrderItem">
          <input v-model="form.order_id" type="number" min="1" placeholder="ID orden existente" required />
          <select v-model="form.product_id" required>
            <option disabled value="">Selecciona un producto</option>
            <option v-for="item in products" :key="item.id" :value="item.id">
              {{ item.name }} (Solo: {{ item.price_solo }} / Con chica: {{ item.price_with_companion }})
            </option>
          </select>
          <input v-model="form.waiter_id" type="number" min="1" placeholder="ID garzon" required />
          <select v-model="form.consumption_type">
            <option value="solo">Solo</option>
            <option value="with_companion">Con chica</option>
          </select>
          <input v-if="form.consumption_type === 'with_companion'" v-model="form.companion_id" type="number" min="1" placeholder="ID chica" />
          <input v-model="form.quantity" type="number" min="1" placeholder="Cantidad" required />
          <button class="primary-btn" type="submit">Enviar comanda</button>
        </form>
      </article>
    </section>
  </AppLayout>
</template>
