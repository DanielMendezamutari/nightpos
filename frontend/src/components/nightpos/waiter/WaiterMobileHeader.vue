<script setup>
import { useAuthStore } from '@/stores/auth'
import { useOperationalStore } from '@/stores/operational'

const props = defineProps({
  title: { type: String, default: '' },
  showBack: { type: Boolean, default: false },
})

const router = useRouter()
const auth = useAuthStore()
const operational = useOperationalStore()

const isOnline = useOnline()

const displayName = computed(() => auth.user?.name || auth.user?.username || 'Garzón')
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
  <header class="waiter-mobile-header">
    <div class="waiter-mobile-header__top">
      <VBtn
        v-if="showBack"
        icon
        variant="text"
        size="small"
        class="waiter-mobile-header__back"
        @click="router.back()"
      >
        <VIcon icon="ri-arrow-left-line" />
      </VBtn>
      <div class="waiter-mobile-header__identity">
        <div class="text-body-2 font-weight-bold">
          {{ displayName }}
        </div>
        <div class="text-caption text-medium-emphasis d-flex align-center flex-wrap gap-1">
          <span>{{ branchLabel }}</span>
          <VChip
            size="x-small"
            :color="isOnline ? 'success' : 'warning'"
            variant="tonal"
            label
          >
            <VIcon
              :icon="isOnline ? 'ri-wifi-line' : 'ri-wifi-off-line'"
              size="12"
              start
            />
            {{ isOnline ? 'En línea' : 'Sin conexión' }}
          </VChip>
        </div>
      </div>
      <VBtn
        icon
        variant="text"
        size="small"
        aria-label="Cerrar sesión"
        @click="logout"
      >
        <VIcon icon="ri-logout-box-r-line" />
      </VBtn>
    </div>
    <h1
      v-if="title"
      class="waiter-mobile-header__title text-h6"
    >
      {{ title }}
    </h1>
  </header>
</template>

<style scoped>
.waiter-mobile-header {
  position: sticky;
  top: 0;
  z-index: 5;
  padding: 12px 16px 8px;
  background: rgb(var(--v-theme-surface));
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.waiter-mobile-header__top {
  display: flex;
  align-items: flex-start;
  gap: 4px;
}

.waiter-mobile-header__identity {
  flex: 1;
  min-width: 0;
}

.waiter-mobile-header__title {
  margin: 8px 0 0;
  font-weight: 600;
}
</style>
