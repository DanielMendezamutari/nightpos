<script setup>
import UsersListPanel from '@/components/nightpos/users/UsersListPanel.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'

definePage({ meta: { permission: 'admin.users.list' } })
</script>

<template>
  <UsersListPanel
    page-title="Cajeras"
    page-subtitle="Personal con rol operativo CASHIER."
    staff-role-filter="CASHIER"
    :breadcrumbs="[
      { title: 'Personal', disabled: true },
      { title: 'Cajeras', disabled: true },
    ]"
    :section-tabs="STAFF_SECTION_TABS"
  />
</template>
