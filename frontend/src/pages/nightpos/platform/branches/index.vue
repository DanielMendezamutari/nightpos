<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import PlatformContextSelector from '@/components/nightpos/PlatformContextSelector.vue'
import { fetchAdminBranches } from '@/api/branches'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.branches.list' } })

const router = useRouter()
const { canCreateAdminBranch } = useNightPosPermissions()
const { isSuperAdmin, tenantSlug, hasTenantContext, applyContext } = usePlatformContext()
const { notify } = useNightPosNotify()

const contextSelector = ref(null)
const branches = ref([])
const loading = ref(false)
const needsTenant = computed(() => isSuperAdmin.value && !hasTenantContext.value)

const headers = [
  { title: 'Sucursal', key: 'name' },
  { title: 'Código', key: 'code' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  if (needsTenant.value) {
    branches.value = []
    return
  }
  loading.value = true
  try {
    branches.value = await fetchAdminBranches(isSuperAdmin.value ? tenantSlug.value : null)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const useBranch = async branch => {
  if (!tenantSlug.value)
    return
  try {
    await applyContext({ tenantSlug: tenantSlug.value, branchCode: branch.code })
    await router.push({ name: 'nightpos-dashboard' })
    notify(`Operando en ${branch.name}`)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

watch(tenantSlug, load)
onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Sucursales"
      subtitle="Locales por empresa."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Sucursales', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canCreateAdminBranch && !needsTenant"
          color="primary"
          size="large"
          :to="{ name: 'nightpos-platform-branches-create' }"
        >
          <VIcon
            icon="ri-add-line"
            start
          />
          Nueva sucursal
        </VBtn>
      </template>
    </NightPosPageHeader>

    <VAlert
      v-if="needsTenant"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      Seleccione una empresa para listar sucursales.
      <PlatformContextSelector
        ref="contextSelector"
        class="ms-2 d-inline-flex"
      />
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else-if="!needsTenant">
      <VDataTable
        :headers="headers"
        :items="branches"
        item-value="id"
      >
        <template #item.name="{ item }">
          <RouterLink
            :to="{ name: 'nightpos-platform-branches-id', params: { id: item.id } }"
            class="font-weight-medium text-primary"
          >
            {{ item.name }}
          </RouterLink>
        </template>
        <template #item.code="{ item }">
          <VChip
            size="small"
            variant="tonal"
          >
            {{ item.code }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            v-if="isSuperAdmin"
            size="small"
            variant="text"
            @click="useBranch(item)"
          >
            Operar
          </VBtn>
          <VBtn
            size="small"
            variant="tonal"
            :to="{ name: 'nightpos-platform-branches-id-edit', params: { id: item.id } }"
          >
            Editar
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
</div>
</template>
