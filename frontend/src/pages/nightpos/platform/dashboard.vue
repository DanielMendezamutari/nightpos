<script setup>
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchAdminTenants } from '@/api/tenants'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.list' } })

const { notify } = useNightPosNotify()
const tenants = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    tenants.value = await fetchAdminTenants()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
})

const cards = computed(() => [
  { title: 'Empresas', color: 'primary', icon: 'ri-building-4-line', stats: String(tenants.value.length), change: 0, subtitle: 'Tenants activos en plataforma' },
  { title: 'Activas', color: 'success', icon: 'ri-checkbox-circle-line', stats: String(tenants.value.filter(t => t.status === 'active').length), change: 0, subtitle: 'Estado active' },
  { title: 'Suspendidas', color: 'warning', icon: 'ri-pause-circle-line', stats: String(tenants.value.filter(t => t.status === 'suspended').length), change: 0, subtitle: 'Revisión' },
])
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Dashboard SaaS"
      subtitle="Vista global de la plataforma NightPOS."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Dashboard', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />
    <NightPosContextCards />
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VRow v-else>
      <VCol
        v-for="card in cards"
        :key="card.title"
        cols="12"
        md="4"
      >
        <CardStatisticsVertical v-bind="card" />
      </VCol>
    </VRow>
    <VCard class="mt-4">
      <VCardItem>
        <VCardTitle>Accesos rápidos</VCardTitle>
      </VCardItem>
      <VCardText class="d-flex flex-wrap gap-2">
        <VBtn :to="{ name: 'nightpos-platform-tenants' }">
          Empresas
        </VBtn>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-tenants-create' }"
        >
          Crear empresa
        </VBtn>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-branches' }"
        >
          Sucursales
        </VBtn>
      </VCardText>
    </VCard>
  </div>
</template>
