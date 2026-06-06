<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import BranchChangeDialog from '@/components/nightpos/BranchChangeDialog.vue'
import { useNightPosShell } from '@/composables/useNightPosShell'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'
import { useOperationalStore } from '@/stores/operational'
import { resolveHomeRoute } from '@/utils/resolveHomeRoute'

const router = useRouter()
const auth = useAuthStore()
const contextStore = useContextStore()
const operational = useOperationalStore()
const { isMaterializeDemoRoute } = useNightPosShell()

const userData = computed(() => auth.user || useCookie('userData').value)

const showBranchDialog = ref(false)

const logout = async () => {
  await auth.logout()
  await router.push('/login')
}

/** Menú demo Materialize — conservado, solo visible en rutas demo. */
const userProfileListDemo = [
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'ri-user-line',
    title: 'Profile',
    to: { name: 'apps-user-view-id', params: { id: 21 } },
  },
  {
    type: 'navItem',
    icon: 'ri-settings-4-line',
    title: 'Settings',
    to: { name: 'pages-account-settings-tab', params: { tab: 'account' } },
  },
  {
    type: 'navItem',
    icon: 'ri-file-text-line',
    title: 'Billing Plan',
    to: { name: 'pages-account-settings-tab', params: { tab: 'billing-plans' } },
    chipsProps: { color: 'error', text: '4', size: 'small' },
  },
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'ri-money-dollar-circle-line',
    title: 'Pricing',
    to: { name: 'pages-pricing' },
  },
  {
    type: 'navItem',
    icon: 'ri-question-line',
    title: 'FAQ',
    to: { name: 'pages-faq' },
  },
]

const userProfileListNightPos = computed(() => [
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'ri-user-line',
    title: 'Mi perfil',
    action: 'profile',
  },
  {
    type: 'navItem',
    icon: 'ri-store-2-line',
    title: 'Cambiar sucursal',
    action: 'branch',
  },
])

const displayName = computed(() => userData.value?.name || userData.value?.username || 'Usuario')

const onMenuAction = item => {
  if (item.action === 'branch')
    showBranchDialog.value = true
  else if (item.action === 'profile') {
    router.push(resolveHomeRoute(auth.user, {
      tenantSlug: contextStore.tenantSlug,
      branchCode: contextStore.branchCode,
    }))
  }
}

const menuItems = computed(() =>
  isMaterializeDemoRoute.value ? userProfileListDemo : userProfileListNightPos.value,
)
</script>

<template>
  <VBadge
    v-if="userData"
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
      :color="!(userData && userData.avatar) ? 'primary' : undefined"
      :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
    >
      <VImg
        v-if="userData && userData.avatar"
        :src="userData.avatar"
      />
      <VIcon
        v-else
        icon="ri-user-line"
      />

      <VMenu
        activator="parent"
        width="260"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4">
            <div class="d-flex gap-x-2 align-center">
              <VAvatar
                :color="!(userData && userData.avatar) ? 'primary' : undefined"
                :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
              >
                <VImg
                  v-if="userData && userData.avatar"
                  :src="userData.avatar"
                />
                <VIcon
                  v-else
                  icon="ri-user-line"
                />
              </VAvatar>

              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ displayName }}
                </div>
                <div class="text-caption text-disabled">
                  {{ userData.role }}
                  <span v-if="userData.staff_role"> · {{ userData.staff_role }}</span>
                </div>
                <div
                  v-if="!isMaterializeDemoRoute && operational.tenant?.name"
                  class="text-caption"
                >
                  {{ operational.tenant.name }}
                </div>
                <div
                  v-if="!isMaterializeDemoRoute && operational.branch?.code"
                  class="text-caption"
                >
                  Sucursal {{ operational.branch.code }}
                </div>
              </div>
            </div>
          </VListItem>

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <template
              v-for="(item, index) in menuItems"
              :key="item.title || `div-${index}`"
            >
              <VListItem
                v-if="item.type === 'navItem' && item.to"
                :to="item.to"
                class="px-4"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>
                <VListItemTitle>{{ item.title }}</VListItemTitle>
                <template
                  v-if="item.chipsProps"
                  #append
                >
                  <VChip
                    v-bind="item.chipsProps"
                    variant="elevated"
                  />
                </template>
              </VListItem>

              <VListItem
                v-else-if="item.type === 'navItem' && item.action"
                class="px-4"
                @click="onMenuAction(item)"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>
                <VListItemTitle>{{ item.title }}</VListItemTitle>
              </VListItem>

              <VDivider
                v-else-if="item.type === 'divider'"
                class="my-1"
              />
            </template>

            <VListItem class="px-4">
              <VBtn
                block
                color="error"
                size="small"
                append-icon="ri-logout-box-r-line"
                @click="logout"
              >
                Cerrar sesión
              </VBtn>
            </VListItem>
          </PerfectScrollbar>
        </VList>
      </VMenu>
    </VAvatar>
  </VBadge>

  <BranchChangeDialog v-model="showBranchDialog" />
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>
