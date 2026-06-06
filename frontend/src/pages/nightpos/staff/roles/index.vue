<script setup>
import NightPosModulePlaceholder from '@/components/nightpos/layout/NightPosModulePlaceholder.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'

definePage({ meta: { permission: 'admin.users.list' } })
</script>

<template>
  <div>
    <NightPosSectionTabs :tabs="STAFF_SECTION_TABS" />
    <NightPosModulePlaceholder
      title="Roles y permisos"
      module="Personal"
      backend-note="RBAC vía roles en BD; gestión visual de permisos pendiente (sin CRUD roles en API)."
    />
  </div>
</template>
