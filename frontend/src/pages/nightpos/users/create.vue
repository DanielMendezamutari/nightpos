<script setup>
import UserAdminFormFields from '@/components/nightpos/forms/UserAdminFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { createAdminUser } from '@/api/users'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { buildUserPayload, emptyUserForm, useUserAdminForm } from '@/composables/useUserAdminForm'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: 'admin.users.create',
  },
})

const router = useRouter()
const { canCreateAdminUser } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { form, branches, showCommissionField, showGirlCommissionField, showCleaningPayField, loadBranches, applyDefaultBranches } = useUserAdminForm()

const saving = ref(false)
const refForm = ref()

const breadcrumbs = [
  { title: 'NightPOS', disabled: true },
  { title: 'Usuarios', to: { name: 'nightpos-users' } },
  { title: 'Nuevo', disabled: true },
]

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true

  try {
    const user = await createAdminUser(buildUserPayload(form.value, { isCreate: true }))
    notify('Usuario creado')
    await router.push({ name: 'nightpos-users-id', params: { id: user.id } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!canCreateAdminUser.value) {
    await router.replace({ name: 'nightpos-users' })

    return
  }

  form.value = emptyUserForm()
  await loadBranches()
  applyDefaultBranches()
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Nuevo usuario"
      subtitle="Alta de personal — datos de acceso y rol operativo."
      :breadcrumbs="breadcrumbs"
    />

    <VCard>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <UserAdminFormFields
            v-model="form"
            :branches="branches"
            is-create
            :show-commission-field="showCommissionField"
            :show-girl-commission-field="showGirlCommissionField"
            :show-cleaning-pay-field="showCleaningPayField"
          />
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-users' }"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>
</div>
</template>
