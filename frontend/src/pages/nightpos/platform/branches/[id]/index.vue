<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminBranches } from '@/api/branches'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.branches.list' } })

const route = useRoute('nightpos-platform-branches-id')
const router = useRouter()
const { tenantSlug, applyContext } = usePlatformContext()
const { notify } = useNightPosNotify()

const branch = ref(null)
const loading = ref(true)

const load = async () => {
  loading.value = true
  try {
    const list = await fetchAdminBranches(tenantSlug.value)
    branch.value = list.find(b => String(b.id) === String(route.params.id)) || null
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const operateHere = async () => {
  if (!branch.value || !tenantSlug.value)
    return
  try {
    await applyContext({ tenantSlug: tenantSlug.value, branchCode: branch.value.code })
    notify(`Operando en ${branch.value.name}`)
    await router.push({ name: 'nightpos-dashboard' })
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
      :title="branch?.name || 'Sucursal'"
      subtitle="Detalle de sucursal del tenant."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Sucursales', to: { name: 'nightpos-platform-branches' } },
        { title: branch?.name || 'Detalle', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          variant="tonal"
          @click="operateHere"
        >
          Operar aquí
        </VBtn>
        <VBtn
          variant="outlined"
          :to="{ name: 'nightpos-platform-branches-id-edit', params: { id: route.params.id } }"
        >
          Editar
        </VBtn>
      </template>
    </NightPosPageHeader>
    <VProgressLinear
      v-if="loading"
      indeterminate
    />
    <VCard v-else-if="branch">
      <VCardText>
        <VList lines="two">
          <VListItem title="Código">
            <template #subtitle>
              {{ branch.code }}
            </template>
          </VListItem>
          <VListItem title="Estado">
            <template #subtitle>
              {{ branch.status }}
            </template>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>
</div>
</template>
