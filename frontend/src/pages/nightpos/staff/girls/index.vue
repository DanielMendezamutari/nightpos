<script setup>
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import UsersListPanel from '@/components/nightpos/users/UsersListPanel.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

definePage({ meta: { permission: 'admin.users.list' } })

const { can } = useNightPosPermissions()
const showQuickGirl = ref(false)
const listKey = ref(0)

const onGirlCreated = () => {
  listKey.value += 1
}
</script>

<template>
  <div>
    <UsersListPanel
      :key="listKey"
      page-title="Chicas"
      page-subtitle="Personal GIRL o con comisiones de chica habilitadas."
      :girl-commissions-only="true"
      :breadcrumbs="[
        { title: 'Personal', disabled: true },
        { title: 'Chicas', disabled: true },
      ]"
      :section-tabs="STAFF_SECTION_TABS"
    >
      <template #extra-actions>
        <VBtn
          v-if="can('staff.quick_create_girl')"
          color="primary"
          variant="tonal"
          class="me-2"
          @click="showQuickGirl = true"
        >
          <VIcon
            icon="ri-user-add-line"
            start
          />
          Nueva chica
        </VBtn>
      </template>
    </UsersListPanel>

    <QuickGirlCreateDialog
      v-model="showQuickGirl"
      @created="onGirlCreated"
    />
  </div>
</template>
