<script setup>
import { useAuthStore } from '@/stores/auth'
import avatar1 from '@images/avatars/avatar-1.png'

const authStore = useAuthStore()

const handleLogout = () => {
  authStore.logout()
}

const roleBadgeColor = computed(() => {
  const role = authStore.userRole?.toLowerCase() || ''
  if (role.includes('admin')) return 'error'
  if (role.includes('cajero')) return 'warning'
  if (role.includes('mesero')) return 'primary'
  if (role.includes('cocina')) return 'secondary'
  return 'info'
})
</script>

<template>
  <VBadge
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
    >
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="260"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4 py-2">
            <div class="d-flex gap-x-3 align-center">
              <VAvatar size="40">
                <VImg :src="avatar1" />
              </VAvatar>

              <div>
                <div class="text-body-1 font-weight-semibold text-high-emphasis">
                  {{ authStore.userName }}
                </div>
                <div class="d-flex align-center gap-1 mt-1">
                  <VChip
                    :color="roleBadgeColor"
                    size="x-small"
                    variant="tonal"
                    class="text-uppercase font-weight-bold"
                  >
                    {{ authStore.userRole }}
                  </VChip>
                  <span class="text-caption text-disabled">
                    ({{ authStore.branchCode }})
                  </span>
                </div>
              </div>
            </div>
          </VListItem>

          <VDivider class="my-2" />

          <VListItem class="px-4">
            <div class="text-caption text-disabled">
              Restaurante:
            </div>
            <div class="text-body-2 font-weight-medium">
              Casa Ribersoft Demo
            </div>
          </VListItem>

          <VListItem class="px-4">
            <a
              href="https://wa.me/59167369293"
              target="_blank"
              class="text-decoration-none d-flex align-center gap-2 text-success"
            >
              <VIcon icon="ri-whatsapp-line" size="18" />
              <span class="text-caption font-weight-medium">Soporte Ribersoft: 67369293</span>
            </a>
          </VListItem>

          <VDivider class="my-2" />

          <VListItem class="px-4">
            <VBtn
              block
              color="error"
              size="small"
              variant="tonal"
              prepend-icon="ri-logout-box-r-line"
              @click="handleLogout"
            >
              Cerrar Sesión
            </VBtn>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>