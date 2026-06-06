<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminUsers } from '@/api/users'
import { STAFF_CHIP_COLOR, STAFF_LABELS } from '@/composables/useUserAdminForm'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  pageTitle: { type: String, required: true },
  pageSubtitle: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] },
  staffRoleFilter: { type: String, default: null },
  girlCommissionsOnly: { type: Boolean, default: false },
  sectionTabs: { type: Array, default: () => [] },
})

const { canCreateAdminUser, canUpdateAdminUser } = useNightPosPermissions()
const { snackbar, notify } = useNightPosNotify()

const users = ref([])
const loading = ref(false)
const confirmDeactivate = ref(null)

const headers = [
  { title: 'Nombre', key: 'name' },
  { title: 'Usuario', key: 'username' },
  { title: 'Rol', key: 'staff_role' },
  { title: 'Sucursal', key: 'branch_name' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const filteredUsers = computed(() => {
  let list = users.value
  if (props.staffRoleFilter)
    list = list.filter(u => u.staff_role === props.staffRoleFilter)
  if (props.girlCommissionsOnly)
    list = list.filter(u => u.staff_role === 'GIRL' || u.can_receive_girl_commissions)
  return list
})

const load = async () => {
  loading.value = true
  try {
    users.value = await fetchAdminUsers()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="pageTitle"
      :subtitle="pageSubtitle"
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <VBtn
          v-if="canCreateAdminUser"
          color="primary"
          :to="{ name: 'nightpos-users-create' }"
        >
          <VIcon
            icon="ri-user-add-line"
            start
          />
          Crear usuario
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs
      v-if="sectionTabs.length"
      :tabs="sectionTabs"
    />

    <VCard>
      <VDataTable
        :headers="headers"
        :items="filteredUsers"
        :loading="loading"
        :items-per-page="15"
      >
        <template #item.name="{ item }">
          <RouterLink
            :to="{ name: 'nightpos-users-id', params: { id: item.id } }"
            class="text-primary font-weight-medium"
          >
            {{ item.name }}
          </RouterLink>
        </template>
        <template #item.staff_role="{ item }">
          <VChip
            v-if="item.staff_role"
            size="small"
            :color="STAFF_CHIP_COLOR[item.staff_role]"
          >
            {{ STAFF_LABELS[item.staff_role] || item.staff_role }}
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
        </template>
      </VDataTable>
    </VCard>

    <VSnackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      location="top end"
    >
      {{ snackbar.text }}
    </VSnackbar>
  </div>
</template>
