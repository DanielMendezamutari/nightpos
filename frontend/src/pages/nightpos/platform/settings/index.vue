<script setup>
import NightPosModulePlaceholder from '@/components/nightpos/layout/NightPosModulePlaceholder.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'

definePage({ meta: { permission: 'admin.tenants.list' } })
</script>

<template>
  <div>
    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />
    <NightPosModulePlaceholder
      title="Configuración SaaS"
      module="Plataforma"
      backend-note="Parámetros globales de plataforma (branding, límites, emails) — pendiente API."
    />
  </div>
</template>
