<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { fetchAdminTenants } from '@/api/tenants'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.list' } })

const { canCreateAdminTenant } = useNightPosPermissions()
const { isSuperAdmin, applyContext } = usePlatformContext()
const { notify } = useNightPosNotify()

const tenants = ref([])
const loading = ref(false)

const STATUS_COLORS = { active: 'success', inactive: 'secondary', suspended: 'warning' }

const headers = [
  { title: 'Empresa', key: 'name' },
  { title: 'Slug', key: 'slug' },
  { title: 'Estado', key: 'status' },
  { title: 'Plan', key: 'plan_name' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    tenants.value = await fetchAdminTenants()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const router = useRouter()

const operateAs = async tenant => {
  try {
    await applyContext({ tenantSlug: tenant.slug, branchCode: null })
    notify(`Contexto: ${tenant.name}`)
    await router.push({ name: 'nightpos-platform-branches' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Empresas (SaaS)"
      subtitle="Clientes de la plataforma NightPOS."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Empresas', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canCreateAdminTenant"
          color="primary"
          size="large"
          :to="{ name: 'nightpos-platform-tenants-create' }"
        >
          <VIcon
            icon="ri-add-line"
            start
          />
          Nueva empresa
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="tenants"
        item-value="id"
      >
        <template #item.name="{ item }">
          <RouterLink
            :to="{ name: 'nightpos-platform-tenants-id', params: { id: item.id } }"
            class="font-weight-medium text-primary"
          >
            {{ item.name }}
          </RouterLink>
        </template>
        <template #item.slug="{ item }">
          <code>{{ item.slug }}</code>
        </template>
        <template #item.status="{ item }">
          <VChip
            :color="STATUS_COLORS[item.status] || 'default'"
            size="small"
            label
          >
            {{ item.status }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            v-if="isSuperAdmin"
            size="small"
            variant="text"
            @click="operateAs(item)"
          >
            Operar
          </VBtn>
          <VBtn
            size="small"
            variant="tonal"
            :to="{ name: 'nightpos-platform-tenants-id-edit', params: { id: item.id } }"
          >
            Editar
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
</div>
</template>
