<script setup>
import { computed, onMounted, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useAuthStore } from '../stores/authStore'
import { apiRequest } from '../services/api'
import { useNotificationStore } from '../stores/notificationStore'

const auth = useAuthStore()
const notify = useNotificationStore()
const message = ref('')
const loading = ref(false)
const productsCount = ref(0)
const ranking = ref([])
const saasOverview = ref({
  total_branches: 0,
  active_branches: 0,
  suspended_branches: 0,
  monthly_revenue: 0,
})
const saasSubscriptions = ref([])

const canSeeRanking = computed(() =>
  ['cashier', 'manager', 'admin', 'super_admin'].includes(auth.user.value?.role)
)

const ownerAlerts = computed(() => {
  const warning = saasSubscriptions.value.filter((s) => s.due_status === 'warning')
  const overdue = saasSubscriptions.value.filter((s) => s.due_status === 'overdue')
  return { warning, overdue }
})

const stats = computed(() => {
  const role = auth.user.value?.role
  if (role === 'owner') {
    return [
      { label: 'Sucursales activas', value: `${saasOverview.value.active_branches}`, trend: `de ${saasOverview.value.total_branches} totales` },
      { label: 'Sucursales suspendidas', value: `${saasOverview.value.suspended_branches}`, trend: 'Requieren accion' },
      { label: 'Ingreso SaaS mensual', value: `Bs ${saasOverview.value.monthly_revenue}`, trend: 'Cobrado este mes' },
      { label: 'Por vencer (<=5 dias)', value: `${ownerAlerts.value.warning.length}`, trend: 'Riesgo alto' },
    ]
  }

  return [
    { label: 'Rol actual', value: role || '-', trend: 'Sesion activa' },
    { label: 'Productos visibles', value: `${productsCount.value}`, trend: 'Catalogo operativo' },
    { label: 'Top chicas (ranking)', value: `${ranking.value.length}`, trend: canSeeRanking.value ? 'Datos disponibles' : 'Sin permiso' },
    { label: 'Estado', value: 'Online', trend: 'API conectada' },
  ]
})

async function loadDashboard() {
  loading.value = true
  message.value = ''
  try {
    const calls = []

    if (!auth.isOwner.value) {
      calls.push(apiRequest('/products', {}, auth.token.value))
    }

    if (canSeeRanking.value) calls.push(apiRequest('/reports/companions/ranking', {}, auth.token.value))
    if (auth.isOwner.value) {
      calls.push(apiRequest('/saas/overview', {}, auth.token.value))
      calls.push(apiRequest('/saas/subscriptions', {}, auth.token.value))
    }

    const results = await Promise.all(calls)
    let index = 0
    if (!auth.isOwner.value) {
      const productsPayload = results[index]
      productsCount.value = (productsPayload.data || []).length
      index += 1
    } else {
      productsCount.value = 0
    }

    if (canSeeRanking.value) {
      ranking.value = results[index].data || []
      index += 1
    }

    if (auth.isOwner.value) {
      saasOverview.value = results[index].data || saasOverview.value
      saasSubscriptions.value = results[index + 1].data || []
    }
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar el dashboard.'
    notify.error(message.value)
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <AppLayout>
    <p v-if="message" class="info-text">{{ message }}</p>

    <section class="stats-grid">
      <article v-for="item in stats" :key="item.label" class="stat-card">
        <p class="stat-label">{{ item.label }}</p>
        <h3 class="stat-value">{{ item.value }}</h3>
        <span class="stat-trend">{{ item.trend }}</span>
      </article>
    </section>

    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Resumen ejecutivo</h3><span>{{ loading ? 'Actualizando...' : 'Datos reales' }}</span></div>
        <div class="modules-list">
          <div v-if="auth.isOwner.value" class="module-item">
            <div><h4>Riesgo de corte</h4><p>{{ ownerAlerts.overdue.length }} sucursales vencidas requieren accion inmediata.</p></div>
            <strong>{{ ownerAlerts.overdue.length }}</strong>
          </div>
          <div v-if="auth.isOwner.value" class="module-item">
            <div><h4>Por vencer pronto</h4><p>{{ ownerAlerts.warning.length }} sucursales vencen en 5 dias o menos.</p></div>
            <strong>{{ ownerAlerts.warning.length }}</strong>
          </div>
          <div v-if="!auth.isOwner.value" class="module-item">
            <div><h4>Catalogo actual</h4><p>{{ productsCount }} productos disponibles para operacion.</p></div>
            <strong>{{ productsCount }}</strong>
          </div>
          <div v-if="canSeeRanking" class="module-item">
            <div><h4>Ranking activo</h4><p>Top 3 chicas por ingreso en el sistema.</p></div>
            <strong>{{ Math.min(ranking.length, 3) }}</strong>
          </div>
        </div>
      </article>

      <article class="panel">
        <div class="panel-head"><h3>Alertas prioritarias</h3><span>Acciones sugeridas</span></div>
        <ul class="activity-list">
          <li v-if="auth.isOwner.value && ownerAlerts.overdue.length">
            <time>Critico</time>
            <p>Tienes {{ ownerAlerts.overdue.length }} sucursales vencidas. Ve a SaaS Owner para cortar o cobrar.</p>
          </li>
          <li v-if="auth.isOwner.value && ownerAlerts.warning.length">
            <time>Alerta</time>
            <p>{{ ownerAlerts.warning.length }} sucursales estan por vencer. Recomendado cobrar antes del corte.</p>
          </li>
          <li v-if="canSeeRanking && ranking.length">
            <time>Ranking</time>
            <p>Lider actual: {{ ranking[0]?.stage_name }} con total {{ ranking[0]?.total_generated }}.</p>
          </li>
          <li>
            <time>Operacion</time>
            <p>Usa los modulos del menu lateral para ejecutar caja, POS, SaaS y reportes.</p>
          </li>
        </ul>
      </article>
    </section>
  </AppLayout>
</template>
