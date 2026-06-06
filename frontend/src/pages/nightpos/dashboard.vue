<script setup>
import { loadDashboardOperationalStats } from '@/composables/useDashboardOperationalStats'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useAuthStore } from '@/stores/auth'
import { useOperationalStore } from '@/stores/operational'

definePage({
  meta: {
    permission: null,
  },
})

const auth = useAuthStore()
const operational = useOperationalStore()
const { hasTenantContext, needsBranchSelection } = usePlatformContext()
const {
  canAccessCash,
  canAccessOrders,
  canListSales,
  canListAdminTenants,
  canListAdminBranches,
  canChargeOrders,
  canDirectSale,
  canAccessSettlements,
} = useNightPosPermissions()

const isGlobalSuperAdmin = computed(() =>
  auth.role === 'super_admin' && !hasTenantContext.value,
)

const loading = ref(true)
const stats = ref(null)

const shortcuts = computed(() => {
  if (isGlobalSuperAdmin.value) {
    return [
      { title: 'Empresas SaaS', icon: 'ri-building-4-line', to: 'nightpos-platform-tenants', enabled: canListAdminTenants.value },
      { title: 'Sucursales', icon: 'ri-store-3-line', to: 'nightpos-platform-branches', enabled: canListAdminBranches.value },
      { title: 'Usuarios', icon: 'ri-team-line', to: 'nightpos-users', enabled: false, hint: 'Elija empresa primero' },
      { title: 'Productos', icon: 'ri-goblet-line', to: 'nightpos-products', enabled: false, hint: 'Elija empresa primero' },
    ]
  }

  return [
    { title: 'Cobrar comandas', icon: 'ri-bank-card-line', to: 'nightpos-cashier-orders', enabled: canChargeOrders.value, color: 'primary' },
    { title: 'Venta directa', icon: 'ri-shopping-cart-line', to: 'nightpos-cash-direct-sale', enabled: canDirectSale.value, color: 'success' },
    { title: 'Mi caja', icon: 'ri-safe-2-line', to: 'nightpos-cash', enabled: canAccessCash.value, color: undefined },
    { title: 'Servicios', icon: 'ri-service-line', to: 'nightpos-services-bracelets', enabled: true, color: undefined },
    { title: 'Habitaciones', icon: 'ri-building-2-line', to: 'nightpos-rooms-list', enabled: true, color: undefined },
    { title: 'Liquidaciones', icon: 'ri-wallet-3-line', to: 'nightpos-settlements', enabled: canAccessSettlements.value, color: undefined },
  ]
})

const row1Cards = computed(() => {
  if (!stats.value)
    return []

  const s = stats.value

  return [
    {
      title: 'Estado de caja',
      color: s.cashOpen ? 'success' : 'secondary',
      icon: 'ri-cash-line',
      stats: s.cashOpen ? 'Abierta' : 'Cerrada',
      change: 0,
      subtitle: s.cashSessionLabel,
    },
    {
      title: 'Turno actual',
      color: s.shiftOpen ? 'info' : 'secondary',
      icon: 'ri-time-line',
      stats: s.shiftOpen ? (s.shiftTypeLabel || 'Abierto') : 'Cerrado',
      change: 0,
      subtitle: s.shiftError || s.currentShiftLabel,
    },
    {
      title: 'Ventas del día',
      color: 'primary',
      icon: 'ri-calendar-line',
      stats: '—',
      change: 0,
      subtitle: 'Placeholder — reporte diario pendiente',
    },
    {
      title: 'Comandas activas',
      color: 'warning',
      icon: 'ri-restaurant-line',
      stats: s.activeOrdersCount !== null ? String(s.activeOrdersCount) : '—',
      change: 0,
      subtitle: s.openOrdersError
        ? 'Error al cargar'
        : `Abiertas: ${s.openOrdersCount ?? '—'} · En barra: ${s.sentToBarOrdersCount ?? '—'}`,
    },
  ]
})

const row2Cards = computed(() => {
  if (!stats.value)
    return []

  const s = stats.value
  const hasSessionSales = s.cashOpen && s.sessionSalesTotal !== null

  return [
    {
      title: 'Efectivo (sesión)',
      color: 'success',
      icon: 'ri-money-dollar-box-line',
      stats: hasSessionSales ? `${s.salesByMethod.cash} BOB` : '—',
      change: 0,
      subtitle: hasSessionSales ? 'Ventas cobradas en efectivo' : 'Abra caja para ver datos',
    },
    {
      title: 'QR (sesión)',
      color: 'info',
      icon: 'ri-qr-code-line',
      stats: hasSessionSales ? `${s.salesByMethod.qr} BOB` : '—',
      change: 0,
      subtitle: hasSessionSales ? 'Ventas cobradas con QR' : 'Sin sesión de caja',
    },
    {
      title: 'Tarjeta (sesión)',
      color: 'primary',
      icon: 'ri-bank-card-line',
      stats: hasSessionSales ? `${s.salesByMethod.card} BOB` : '—',
      change: 0,
      subtitle: hasSessionSales ? 'Ventas cobradas con tarjeta' : 'Sin sesión de caja',
    },
    {
      title: 'Total vendido (sesión)',
      color: 'secondary',
      icon: 'ri-funds-line',
      stats: hasSessionSales ? `${s.sessionSalesTotal} BOB` : '—',
      change: 0,
      subtitle: hasSessionSales
        ? `${s.sessionSalesCount ?? 0} venta(s) en turno`
        : 'Placeholder hasta caja abierta',
    },
  ]
})

const reloadDashboard = async () => {
  loading.value = true

  try {
    await operational.refreshContext()

    if (!isGlobalSuperAdmin.value)
      stats.value = await loadDashboardOperationalStats()
    else
      stats.value = null
  }
  catch {
    stats.value = null
  }
  finally {
    loading.value = false
  }
}

onMounted(reloadDashboard)
useOnContextChange(reloadDashboard)
</script>

<template>
  <div class="nightpos-dashboard">
    <VRow class="mb-2">
      <VCol cols="12">
        <h4 class="text-h4 mb-1">
          Panel operativo
        </h4>
        <p class="text-body-2 mb-0">
          <template v-if="isGlobalSuperAdmin">
            Acceso plataforma (superadmin) — seleccione empresa/sucursal cuando opere en un tenant.
          </template>
          <template v-else>
            {{ operational.tenant?.name || 'Empresa' }}
            <span v-if="operational.branch?.code"> · {{ operational.branch.name }} [{{ operational.branch.code }}]</span>
          </template>
        </p>
      </VCol>
    </VRow>

    <VAlert
      v-if="isGlobalSuperAdmin && !loading"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Sesión global activa. Elija empresa en la barra superior o use el módulo Plataforma SaaS.
    </VAlert>

    <VAlert
      v-if="needsBranchSelection && !loading"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      Empresa seleccionada. Elija sucursal en la barra superior para operar caja, comandas y ventas.
    </VAlert>

    <NightPosContextCards v-if="!isGlobalSuperAdmin" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      color="primary"
      class="mb-4"
    />

    <template v-else>
      <VAlert
        v-if="stats?.cashError"
        type="warning"
        variant="tonal"
        class="mb-4"
      >
        Caja: {{ stats.cashError }}
      </VAlert>

      <p class="text-caption text-medium-emphasis mb-3">
        Fila 1 — operación en vivo. Fila 2 — ventas de la sesión de caja abierta (API actual).
      </p>

      <VRow class="match-height mb-4">
        <VCol
          v-for="card in row1Cards"
          :key="card.title"
          cols="12"
          sm="6"
          lg="3"
        >
          <CardStatisticsVertical v-bind="card" />
        </VCol>
      </VRow>

      <VRow class="match-height mb-6">
        <VCol
          v-for="card in row2Cards"
          :key="card.title"
          cols="12"
          sm="6"
          lg="3"
        >
          <CardStatisticsVertical v-bind="card" />
        </VCol>
      </VRow>

      <VCard>
        <VCardText class="py-3">
          <div class="text-overline text-medium-emphasis mb-2">
            Accesos rápidos
          </div>
          <div class="d-flex flex-wrap gap-2">
            <VBtn
              v-for="item in shortcuts"
              :key="item.title"
              :to="item.enabled ? { name: item.to } : undefined"
              :color="item.enabled && item.color ? item.color : undefined"
              :variant="item.enabled ? (item.color ? 'elevated' : 'tonal') : 'outlined'"
              :disabled="!item.enabled"
              prepend-icon
              class="nightpos-dashboard__shortcut"
            >
              <template #prepend>
                <VIcon :icon="item.icon" />
              </template>
              {{ item.title }}
              <VTooltip
                v-if="!item.enabled && item.hint"
                activator="parent"
              >
                {{ item.hint }}
              </VTooltip>
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </template>
  </div>
</template>

<style scoped>
.nightpos-dashboard__shortcut {
  min-block-size: 3rem;
}
</style>
