<script setup>
import UsersListPanel from '@/components/nightpos/users/UsersListPanel.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'

definePage({ meta: { permission: 'admin.users.list' } })
</script>

<template>
  <UsersListPanel
    page-title="Garzones"
    page-subtitle="Personal con rol operativo WAITER."
    staff-role-filter="WAITER"
    :breadcrumbs="[
      { title: 'Personal', disabled: true },
      { title: 'Garzones', disabled: true },
    ]"
    :section-tabs="STAFF_SECTION_TABS"
  />
</template>
