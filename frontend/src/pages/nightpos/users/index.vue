<script setup>
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { fetchAdminBranches } from '@/api/branches'
import { fetchAdminUsers, updateAdminUser } from '@/api/users'
import { STAFF_CHIP_COLOR, STAFF_LABELS } from '@/composables/useUserAdminForm'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.users.list' } })

const { canCreateAdminUser, canUpdateAdminUser, canListAdminBranches } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const users = ref([])
const loading = ref(false)
const confirmDeactivate = ref(null)

const headers = [
  { title: 'Nombre', key: 'name' },
  { title: 'Usuario', key: 'username' },
  { title: 'Rol operativo', key: 'staff_role' },
  { title: 'Sucursal', key: 'branch_name' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const summaryCards = computed(() => {
  const list = users.value
  return [
    { title: 'Total', color: 'primary', icon: 'ri-team-line', stats: String(list.length), change: 0, subtitle: 'Personal' },
    { title: 'Garzones', color: 'info', icon: 'ri-walk-line', stats: String(list.filter(u => u.staff_role === 'WAITER').length), change: 0, subtitle: 'WAITER' },
    { title: 'Cajeras', color: 'success', icon: 'ri-cash-line', stats: String(list.filter(u => u.staff_role === 'CASHIER').length), change: 0, subtitle: 'CASHIER' },
    { title: 'Activos', color: 'secondary', icon: 'ri-checkbox-circle-line', stats: String(list.filter(u => u.status === 'active').length), change: 0, subtitle: 'En operación' },
  ]
})

const load = async () => {
  loading.value = true
  try {
    users.value = await fetchAdminUsers()
    if (canListAdminBranches.value)
      await fetchAdminBranches()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const toggleStatus = async () => {
  const user = confirmDeactivate.value
  if (!user || !canUpdateAdminUser.value)
    return

  try {
    await updateAdminUser(user.id, {
      name: user.name,
      username: user.username,
      email: user.email,
      branch_id: user.branch_id,
      status: user.status === 'active' ? 'inactive' : 'active',
      staff_role: user.staff_role,
      waiter_commission_percent: user.waiter_commission_percent,
      can_receive_girl_commissions: user.can_receive_girl_commissions,
      accessible_branch_ids: user.accessible_branch_ids || [],
    })
    notify(user.status === 'active' ? 'Usuario desactivado' : 'Usuario activado')
    confirmDeactivate.value = null
    await load()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

onMounted(load)
</script>

<template>
  <div class="users-page">
    <NightPosPageHeader
      title="Usuarios / Personal"
      subtitle="Administradores, cajeras, garzones y chicas — acceso por sucursal."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Usuarios', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canCreateAdminUser"
          color="primary"
          size="large"
          :to="{ name: 'nightpos-users-create' }"
        >
          <VIcon
            icon="ri-user-add-line"
            start
          />
          Nuevo usuario
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="STAFF_SECTION_TABS" />
    <NightPosContextCards />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else>
      <VRow class="match-height mb-4">
        <VCol
          v-for="card in summaryCards"
          :key="card.title"
          cols="12"
          sm="6"
          md="3"
        >
          <CardStatisticsVertical v-bind="card" />
        </VCol>
      </VRow>

      <VCard>
        <VDataTable
          :headers="headers"
          :items="users"
          :items-per-page="15"
          class="text-no-wrap"
        >
          <template #item.name="{ item }">
            <RouterLink
              :to="{ name: 'nightpos-users-id', params: { id: item.id } }"
              class="font-weight-medium text-primary"
            >
              {{ item.name }}
            </RouterLink>
          </template>
          <template #item.staff_role="{ item }">
            <VChip
              v-if="item.staff_role"
              size="small"
              label
              :color="STAFF_CHIP_COLOR[item.staff_role] || 'secondary'"
            >
              {{ STAFF_LABELS[item.staff_role] || item.staff_role }}
            </VChip>
          </template>
          <template #item.status="{ item }">
            <VChip
              :color="item.status === 'active' ? 'success' : 'secondary'"
              label
              size="small"
            >
              {{ item.status === 'active' ? 'Activo' : 'Inactivo' }}
            </VChip>
          </template>
          <template #item.actions="{ item }">
            <VBtn
              v-if="canUpdateAdminUser"
              size="small"
              variant="tonal"
              :to="{ name: 'nightpos-users-id-edit', params: { id: item.id } }"
            >
              Editar
            </VBtn>
            <VBtn
              v-if="canUpdateAdminUser"
              size="small"
              variant="text"
              :color="item.status === 'active' ? 'warning' : 'success'"
              @click="confirmDeactivate = item"
            >
              {{ item.status === 'active' ? 'Desactivar' : 'Activar' }}
            </VBtn>
          </template>
        </VDataTable>
      </VCard>
    </template>

    <VDialog
      :model-value="Boolean(confirmDeactivate)"
      max-width="420"
      @update:model-value="v => { if (!v) confirmDeactivate = null }"
    >
      <VCard :title="confirmDeactivate?.status === 'active' ? 'Desactivar usuario' : 'Activar usuario'">
        <VCardText>
          ¿Confirmar cambio de estado para <strong>{{ confirmDeactivate?.name }}</strong>?
        </VCardText>
        <VCardActions>
          <VBtn
            variant="text"
            @click="confirmDeactivate = null"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            :color="confirmDeactivate?.status === 'active' ? 'warning' : 'success'"
            @click="toggleStatus"
          >
            Confirmar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</div>
</template>
