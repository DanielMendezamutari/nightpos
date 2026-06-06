<script setup>
import { useAuthStore } from '@/stores/auth'
import { useOperationalStore } from '@/stores/operational'

defineProps({
  title: { type: String, default: 'Limpieza' },
})

const auth = useAuthStore()
const operational = useOperationalStore()
const router = useRouter()

const displayName = computed(() => auth.user?.name || auth.user?.username || 'Limpieza')
const branchLabel = computed(() => operational.branch?.name || operational.branch?.code || 'Sucursal')

const logout = async () => {
  await auth.logout()
  await router.replace({ name: 'login' })
}

onMounted(() => {
  if (auth.isAuthenticated && !operational.branch)
    operational.refreshContext().catch(() => {})
})
</script>

<template>
  <header class="cleaning-mobile-header pa-4 pb-2">
    <div class="d-flex align-center justify-space-between mb-1">
      <div>
        <div class="text-h6 font-weight-bold">
          {{ title }}
        </div>
        <div class="text-caption text-medium-emphasis">
          {{ displayName }} · {{ branchLabel }}
        </div>
      </div>
      <VBtn
        icon
        variant="text"
        size="small"
        @click="logout"
      >
        <VIcon icon="ri-logout-box-r-line" />
      </VBtn>
    </div>
  </header>
</template>

<style scoped>
.cleaning-mobile-header {
  position: sticky;
  top: 0;
  z-index: 10;
  background: rgb(var(--v-theme-surface));
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}
</style>
