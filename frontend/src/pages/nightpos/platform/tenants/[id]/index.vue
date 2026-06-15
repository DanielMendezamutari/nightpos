<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminTenant } from '@/api/tenants'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.list' } })

const route = useRoute('nightpos-platform-tenants-id')
const { applyContext } = usePlatformContext()
const { notify } = useNightPosNotify()

const tenant = ref(null)
const loading = ref(true)

const USAGE_STATUS_COLORS = {
  OK: 'success',
  WARNING: 'warning',
  LIMIT_REACHED: 'error',
}

const load = async () => {
  loading.value = true
  try {
    tenant.value = await fetchAdminTenant(route.params.id)
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

const formatLimit = (current, limit) => {
  if (limit === null || limit === undefined)
    return `${current}`
  if (limit < 0)
    return `${current} / ∞`

  return `${current} / ${limit}`
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
    <VRow v-else-if="tenant">
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
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
                  {{ tenant.plan_usage?.plan?.name || tenant.plan_name || 'Sin plan' }}
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>Uso vs límites</VCardTitle>
            <VCardSubtitle>Solo informativo — sin bloqueo operativo</VCardSubtitle>
          </VCardItem>
          <VCardText>
            <VList
              v-if="tenant.plan_usage?.usage?.length"
              density="compact"
            >
              <VListItem
                v-for="row in tenant.plan_usage.usage"
                :key="row.key"
                :title="row.key"
              >
                <template #subtitle>
                  {{ formatLimit(row.current, row.limit) }}
                </template>
                <template #append>
                  <VChip
                    size="small"
                    :color="USAGE_STATUS_COLORS[row.status] || 'default'"
                  >
                    {{ row.status }}
                  </VChip>
                </template>
              </VListItem>
            </VList>
            <div
              v-else
              class="text-medium-emphasis"
            >
              Sin datos de uso.
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
