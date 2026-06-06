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
      title="Planes y suscripciones"
      module="Plataforma SaaS"
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Planes', disabled: true },
      ]"
      backend-note="Campos plan_name y fechas existen al crear tenant; falta módulo de facturación y renovación."
    />
  </div>
</template>
