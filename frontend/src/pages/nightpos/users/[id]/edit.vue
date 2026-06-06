<script setup>
import UserAdminFormFields from '@/components/nightpos/forms/UserAdminFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminUser, resetAdminUserPassword, resetAdminUserPin, updateAdminUser } from '@/api/users'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { buildUserPayload, userToForm, useUserAdminForm } from '@/composables/useUserAdminForm'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: 'admin.users.update',
  },
})

const route = useRoute('nightpos-users-id-edit')
const router = useRouter()
const { canUpdateAdminUser, canCreateAdminUser } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { form, branches, showCommissionField, showGirlCommissionField, showCleaningPayField, loadBranches } = useUserAdminForm()

const saving = ref(false)
const loading = ref(true)
const refForm = ref()
const user = ref(null)

const showResetPin = ref(false)
const showResetPassword = ref(false)
const resetPinValue = ref('')
const resetPasswordValue = ref('')

const userId = computed(() => Number(route.params.id))
const activeTab = ref('personal')

const breadcrumbs = computed(() => [
  { title: 'NightPOS', disabled: true },
  { title: 'Usuarios', to: { name: 'nightpos-users' } },
  { title: user.value?.name || 'Editar', disabled: true },
])

const load = async () => {
  loading.value = true

  try {
    await loadBranches()
    user.value = await fetchAdminUser(userId.value)
    form.value = userToForm(user.value)

    if (route.query.reset === 'pin')
      showResetPin.value = true
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid || !user.value)
    return

  saving.value = true

  try {
    await updateAdminUser(user.value.id, buildUserPayload(form.value))
    notify('Usuario actualizado')
    await router.push({ name: 'nightpos-users-id', params: { id: userId.value } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const submitResetPin = async () => {
  try {
    await resetAdminUserPin(userId.value, resetPinValue.value)
    notify('PIN actualizado')
    showResetPin.value = false
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

const submitResetPassword = async () => {
  try {
    await resetAdminUserPassword(userId.value, resetPasswordValue.value)
    notify('Contraseña actualizada')
    showResetPassword.value = false
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

onMounted(async () => {
  if (!canUpdateAdminUser.value) {
    await router.replace({ name: 'nightpos-users' })

    return
  }

  await load()
})
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="`Editar — ${user?.name || ''}`"
      subtitle="Actualice datos operativos del personal."
      :breadcrumbs="breadcrumbs"
    >
    </NightPosPageHeader>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VTabs
            v-model="activeTab"
            class="v-tabs-pill mb-4"
          >
            <VTab value="personal">
              Datos personales
            </VTab>
            <VTab value="access">
              Acceso
            </VTab>
            <VTab value="commission">
              Comisión
            </VTab>
            <VTab value="security">
              Seguridad
            </VTab>
          </VTabs>

          <VWindow v-model="activeTab">
            <VWindowItem value="personal">
              <UserAdminFormFields
                v-model="form"
                section="personal"
              />
            </VWindowItem>
            <VWindowItem value="access">
              <UserAdminFormFields
                v-model="form"
                :branches="branches"
                section="access"
              />
            </VWindowItem>
            <VWindowItem value="commission">
              <UserAdminFormFields
                v-model="form"
                :show-commission-field="showCommissionField"
                :show-girl-commission-field="showGirlCommissionField"
                :show-cleaning-pay-field="showCleaningPayField"
                section="commission"
              />
              <VAlert
                v-if="!showCommissionField && !showGirlCommissionField && !showCleaningPayField"
                type="info"
                variant="tonal"
                class="mt-2"
              >
                Sin campos de comisión para este rol operativo.
              </VAlert>
            </VWindowItem>
            <VWindowItem value="security">
              <p class="text-body-2 mb-4">
                Acciones de credenciales (modales de confirmación).
              </p>
              <VBtn
                v-if="canCreateAdminUser"
                variant="tonal"
                class="me-2 mb-2"
                @click="showResetPin = true"
              >
                Reset PIN
              </VBtn>
              <VBtn
                v-if="canCreateAdminUser"
                variant="outlined"
                class="mb-2"
                @click="showResetPassword = true"
              >
                Reset contraseña
              </VBtn>
            </VWindowItem>
          </VWindow>

          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-users-id', params: { id: userId } }"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>

    <VDialog
      v-model="showResetPin"
      max-width="400"
    >
      <VCard title="Resetear PIN">
        <VCardText>
          <VAlert
            type="warning"
            variant="tonal"
            class="mb-4"
          >
            El PIN anterior dejará de funcionar.
          </VAlert>
          <VTextField
            v-model="resetPinValue"
            label="Nuevo PIN"
            type="password"
            maxlength="6"
          />
        </VCardText>
        <VCardActions>
          <VBtn
            variant="text"
            @click="showResetPin = false"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            color="primary"
            @click="submitResetPin"
          >
            Guardar PIN
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="showResetPassword"
      max-width="400"
    >
      <VCard title="Resetear contraseña">
        <VCardText>
          <VTextField
            v-model="resetPasswordValue"
            label="Nueva contraseña"
            type="password"
            minlength="6"
          />
        </VCardText>
        <VCardActions>
          <VBtn
            variant="text"
            @click="showResetPassword = false"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            color="primary"
            @click="submitResetPassword"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</div>
</template>
