<script setup>
import UsersListPanel from '@/components/nightpos/users/UsersListPanel.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'

definePage({ meta: { permission: 'admin.users.list' } })
</script>

<template>
  <UsersListPanel
    page-title="Chicas"
    page-subtitle="Personal GIRL o con comisiones de chica habilitadas."
    :girl-commissions-only="true"
    :breadcrumbs="[
      { title: 'Personal', disabled: true },
      { title: 'Chicas', disabled: true },
    ]"
    :section-tabs="STAFF_SECTION_TABS"
  />
</template>
