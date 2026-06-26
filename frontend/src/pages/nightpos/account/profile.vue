<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { changeOwnPassword, changeOwnPin } from '@/api/account'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: null,
  },
})

const auth = useAuthStore()
const contextStore = useContextStore()
const router = useRouter()
const { notify } = useNightPosNotify()

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const pinForm = ref({
  current_password: '',
  new_pin: '',
  new_pin_confirmation: '',
})

const savingPassword = ref(false)
const savingPin = ref(false)
const refPasswordForm = ref()
const refPinForm = ref()

const user = computed(() => auth.user)

const breadcrumbs = computed(() => [
  { title: 'NightPOS', disabled: true },
  { title: 'Mi perfil', disabled: true },
])

const contextLabel = computed(() => {
  if (user.value?.role === 'super_admin')
    return 'Plataforma Ribersoft'

  const parts = []
  if (contextStore.tenantName || contextStore.tenantSlug)
    parts.push(contextStore.tenantName || contextStore.tenantSlug)
  if (contextStore.branchCode)
    parts.push(`Sucursal ${contextStore.branchCode}`)

  return parts.join(' · ') || '—'
})

const submitPassword = async () => {
  const { valid } = await refPasswordForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  savingPassword.value = true
  try {
    await changeOwnPassword({ ...passwordForm.value })
    notify('Contraseña actualizada. Inicie sesión nuevamente.')
    await auth.logout()
    await router.push('/login')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    savingPassword.value = false
  }
}

const submitPin = async () => {
  const { valid } = await refPinForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  savingPin.value = true
  try {
    await changeOwnPin({ ...pinForm.value })
    notify('PIN actualizado')
    pinForm.value = {
      current_password: '',
      new_pin: '',
      new_pin_confirmation: '',
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    savingPin.value = false
  }
}

const logout = async () => {
  await auth.logout()
  await router.push('/login')
}

onMounted(async () => {
  if (!auth.isAuthenticated) {
    await router.replace('/login')

    return
  }

  try {
    await auth.fetchMe()
  }
  catch {
    // Mantener datos de sesión si /me falla temporalmente.
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Mi perfil"
      subtitle="Datos de cuenta, contraseña y PIN operativo."
      :breadcrumbs="breadcrumbs"
    />

    <VRow>
      <VCol
        cols="12"
        md="5"
      >
        <VCard class="mb-4">
          <VCardTitle>Datos de usuario</VCardTitle>
          <VCardText>
            <div class="text-body-1 font-weight-medium mb-1">
              {{ user?.name || user?.username || '—' }}
            </div>
            <div class="text-body-2 text-medium-emphasis mb-1">
              Usuario: {{ user?.username || '—' }}
            </div>
            <div
              v-if="user?.email"
              class="text-body-2 text-medium-emphasis mb-1"
            >
              Email: {{ user.email }}
            </div>
            <div class="text-body-2 text-medium-emphasis mb-1">
              Rol: {{ user?.role || '—' }}
              <span v-if="user?.staff_role"> · {{ user.staff_role }}</span>
            </div>
            <div class="text-body-2 text-medium-emphasis">
              Contexto: {{ contextLabel }}
            </div>
          </VCardText>
          <VCardActions>
            <VBtn
              color="error"
              variant="tonal"
              prepend-icon="ri-logout-box-r-line"
              @click="logout"
            >
              Cerrar sesión
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="7"
      >
        <VCard class="mb-4">
          <VCardTitle>Cambiar contraseña</VCardTitle>
          <VCardText>
            <VForm
              ref="refPasswordForm"
              @submit.prevent="submitPassword"
            >
              <VTextField
                v-model="passwordForm.current_password"
                label="Contraseña actual *"
                type="password"
                autocomplete="current-password"
                class="mb-3"
                :rules="[v => !!v || 'Requerido']"
              />
              <VTextField
                v-model="passwordForm.new_password"
                label="Nueva contraseña *"
                type="password"
                autocomplete="new-password"
                class="mb-3"
                :rules="[v => (v && v.length >= 8) || 'Mínimo 8 caracteres']"
              />
              <VTextField
                v-model="passwordForm.new_password_confirmation"
                label="Confirmar nueva contraseña *"
                type="password"
                autocomplete="new-password"
                class="mb-3"
                :rules="[v => v === passwordForm.new_password || 'No coincide']"
              />
              <VBtn
                type="submit"
                color="primary"
                :loading="savingPassword"
              >
                Guardar contraseña
              </VBtn>
            </VForm>
          </VCardText>
        </VCard>

        <VCard>
          <VCardTitle>Cambiar PIN</VCardTitle>
          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-4">
              El PIN se usa en caja, garzón y otros accesos rápidos. Debe tener entre 4 y 6 dígitos.
            </p>
            <VForm
              ref="refPinForm"
              @submit.prevent="submitPin"
            >
              <VTextField
                v-model="pinForm.current_password"
                label="Contraseña actual *"
                type="password"
                autocomplete="current-password"
                class="mb-3"
                :rules="[v => !!v || 'Requerido']"
              />
              <VTextField
                v-model="pinForm.new_pin"
                label="Nuevo PIN *"
                type="password"
                inputmode="numeric"
                maxlength="6"
                autocomplete="off"
                class="mb-3"
                :rules="[v => /^\d{4,6}$/.test(v || '') || '4 a 6 dígitos']"
              />
              <VTextField
                v-model="pinForm.new_pin_confirmation"
                label="Confirmar nuevo PIN *"
                type="password"
                inputmode="numeric"
                maxlength="6"
                autocomplete="off"
                class="mb-3"
                :rules="[v => v === pinForm.new_pin || 'No coincide']"
              />
              <VBtn
                type="submit"
                color="primary"
                :loading="savingPin"
              >
                Guardar PIN
              </VBtn>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
