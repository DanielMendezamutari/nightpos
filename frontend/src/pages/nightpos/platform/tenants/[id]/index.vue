<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminTenants } from '@/api/tenants'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.list' } })

const route = useRoute('nightpos-platform-tenants-id')
const { applyContext } = usePlatformContext()
const { notify } = useNightPosNotify()

const tenant = ref(null)
const loading = ref(true)

const load = async () => {
  loading.value = true
  try {
    const list = await fetchAdminTenants()
    tenant.value = list.find(t => String(t.id) === String(route.params.id)) || null
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const operateAs = async () => {
  if (!tenant.value)
    return
  try {
    await applyContext({ tenantSlug: tenant.value.slug, branchCode: null })
    notify(`Contexto: ${tenant.value.name}`)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

const breadcrumbs = computed(() => [
  { title: 'Plataforma', disabled: true },
  { title: 'Empresas', to: { name: 'nightpos-platform-tenants' } },
  { title: tenant.value?.name || 'Detalle', disabled: true },
])

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="tenant?.name || 'Empresa'"
      subtitle="Ficha del tenant SaaS."
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <VBtn
          variant="tonal"
          @click="operateAs"
        >
          Operar en esta empresa
        </VBtn>
        <VBtn
          variant="outlined"
          :to="{ name: 'nightpos-platform-tenants-id-edit', params: { id: route.params.id } }"
        >
          Editar
        </VBtn>
      </template>
    </NightPosPageHeader>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VCard v-else-if="tenant">
      <VCardText>
        <VList lines="two">
          <VListItem title="Slug">
            <template #subtitle>
              <code>{{ tenant.slug }}</code>
            </template>
          </VListItem>
          <VListItem title="Estado">
            <template #subtitle>
              {{ tenant.status }}
            </template>
          </VListItem>
          <VListItem title="Plan">
            <template #subtitle>
              {{ tenant.plan_name || '—' }}
            </template>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>
</div>
</template>
