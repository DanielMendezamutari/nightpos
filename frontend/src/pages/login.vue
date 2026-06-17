<script setup>
import { VForm } from 'vuetify/components/VForm'
import { themeConfig } from '@themeConfig'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2LoginMaskDark from '@images/pages/auth-v2-login-mask-dark.png'
import authV2LoginMaskLight from '@images/pages/auth-v2-login-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { fetchLoginContextBranches, fetchLoginContextTenants } from '@/api/loginContext'
import { useAuthStore } from '@/stores/auth'
import { resolveHomeRoute } from '@/utils/resolveHomeRoute'
import { useContextStore } from '@/stores/context'
import { getApiErrorMessage } from '@/services/http'

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2LoginMaskLight, authV2LoginMaskDark)

definePage({
  meta: {
    layout: 'blank',
    public: true,
    unauthenticatedOnly: true,
  },
})

const REMEMBER_DAYS = 30
const COOKIE_OPTS = { maxAge: 60 * 60 * 24 * REMEMBER_DAYS }

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const sessionExpiredHint = computed(() =>
  route.query.reason === 'session_expired'
    ? 'Tu sesión expiró. Vuelve a ingresar.'
    : '',
)

const loginMode = ref('pin')
const errorMessage = ref('')
const suggestContextChange = ref(false)
const refVForm = ref()

const tenantSlugCookie = useCookie('tenantSlug', COOKIE_OPTS)
const branchCodeCookie = useCookie('branchCode', COOKIE_OPTS)
const tenantNameCookie = useCookie('tenantName', COOKIE_OPTS)
const branchNameCookie = useCookie('branchName', COOKIE_OPTS)

/** 'pin' = ingreso rápido; 'select-context' = elegir empresa/sucursal */
const pinStep = ref('pin')

const pinForm = ref({
  tenant_slug: '',
  branch_code: '',
  tenant_name: '',
  branch_name: '',
  pin: '',
})

const passwordForm = ref({
  username: '',
  password: '',
  tenant_slug: '',
})

const tenants = ref([])
const branches = ref([])
const loadingTenants = ref(false)
const loadingBranches = ref(false)

const selectedTenantSlug = ref(null)
const selectedBranchCode = ref(null)

const isPlatformLogin = computed(() =>
  passwordForm.value.username?.trim().toLowerCase() === 'superadmin',
)

const isPasswordVisible = ref(false)

const requiredRule = v => !!v || 'Requerido'

const pinRules = computed(() => (loginMode.value === 'pin' && pinStep.value === 'pin' ? [requiredRule] : []))
const passwordFieldRules = computed(() =>
  loginMode.value === 'password' ? [requiredRule] : [],
)
const tenantSlugPasswordRules = computed(() =>
  loginMode.value === 'password' && !isPlatformLogin.value
    ? [v => !!v?.trim() || 'Requerido para usuarios de empresa']
    : [],
)

const hasSavedContext = computed(() =>
  Boolean(tenantSlugCookie.value?.trim() && branchCodeCookie.value?.trim()),
)

const displayTenantName = computed(() =>
  pinForm.value.tenant_name || tenantNameCookie.value || pinForm.value.tenant_slug || '—',
)

const displayBranchName = computed(() =>
  pinForm.value.branch_name || branchNameCookie.value || pinForm.value.branch_code || '—',
)

const tenantItems = computed(() =>
  tenants.value.map(t => ({ title: t.name, value: t.slug, raw: t })),
)

const branchItems = computed(() =>
  branches.value.map(b => ({ title: b.name, value: b.code, raw: b })),
)

const syncPinFormFromCookies = () => {
  pinForm.value.tenant_slug = tenantSlugCookie.value || ''
  pinForm.value.branch_code = branchCodeCookie.value || ''
  pinForm.value.tenant_name = tenantNameCookie.value || ''
  pinForm.value.branch_name = branchNameCookie.value || ''
}

const clearSavedContext = () => {
  tenantSlugCookie.value = null
  branchCodeCookie.value = null
  tenantNameCookie.value = null
  branchNameCookie.value = null
  pinForm.value.tenant_slug = ''
  pinForm.value.branch_code = ''
  pinForm.value.tenant_name = ''
  pinForm.value.branch_name = ''
}

const loadTenants = async () => {
  loadingTenants.value = true
  try {
    tenants.value = await fetchLoginContextTenants()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error)
  }
  finally {
    loadingTenants.value = false
  }
}

const loadBranches = async slug => {
  if (!slug) {
    branches.value = []
    selectedBranchCode.value = null

    return
  }

  loadingBranches.value = true
  try {
    branches.value = await fetchLoginContextBranches(slug)
    if (branches.value.length === 1)
      selectedBranchCode.value = branches.value[0].code
  }
  catch (error) {
    branches.value = []
    errorMessage.value = getApiErrorMessage(error)
    suggestContextChange.value = true
  }
  finally {
    loadingBranches.value = false
  }
}

const hydratePinStep = async () => {
  syncPinFormFromCookies()
  if (!hasSavedContext.value) {
    pinStep.value = 'select-context'
    await loadTenants()

    return
  }

  try {
    const list = await fetchLoginContextBranches(tenantSlugCookie.value)
    const branch = list.find(b => b.code === branchCodeCookie.value)

    if (!branch) {
      throw new Error('Sucursal no disponible')
    }

    const tenant = tenants.value.length
      ? tenants.value.find(t => t.slug === tenantSlugCookie.value)
      : null

    if (!tenant) {
      const allTenants = await fetchLoginContextTenants()

      tenants.value = allTenants
      const found = allTenants.find(t => t.slug === tenantSlugCookie.value)
      if (found) {
        pinForm.value.tenant_name = found.name
        tenantNameCookie.value = found.name
      }
    }

    pinForm.value.branch_name = branch.name
    branchNameCookie.value = branch.name
    pinStep.value = 'pin'
  }
  catch {
    errorMessage.value = 'La empresa o sucursal guardada ya no está disponible. Elija de nuevo.'
    suggestContextChange.value = true
    clearSavedContext()
    pinStep.value = 'select-context'
    await loadTenants()
  }
}

const startChangeContext = async () => {
  auth.clearAuthOnly()
  auth.loading = false
  errorMessage.value = ''
  suggestContextChange.value = false
  pinForm.value.pin = ''
  selectedTenantSlug.value = null
  selectedBranchCode.value = null
  branches.value = []
  clearSavedContext()
  pinStep.value = 'select-context'
  await loadTenants()
}

const confirmContextSelection = () => {
  errorMessage.value = ''

  const tenant = tenants.value.find(t => t.slug === selectedTenantSlug.value)
  const branch = branches.value.find(b => b.code === selectedBranchCode.value)

  if (!tenant || !branch) {
    errorMessage.value = 'Seleccione empresa y sucursal para continuar.'

    return
  }

  tenantSlugCookie.value = tenant.slug
  branchCodeCookie.value = branch.code
  tenantNameCookie.value = tenant.name
  branchNameCookie.value = branch.name

  pinForm.value.tenant_slug = tenant.slug
  pinForm.value.branch_code = branch.code
  pinForm.value.tenant_name = tenant.name
  pinForm.value.branch_name = branch.name
  pinForm.value.pin = ''
  pinStep.value = 'pin'
  suggestContextChange.value = false
}

const isContextRelatedError = (error, message) => {
  const status = error?.response?.status
  const lower = (message || '').toLowerCase()

  return [403, 404, 422].includes(status)
    || lower.includes('empresa')
    || lower.includes('sucursal')
    || lower.includes('tenant')
    || lower.includes('branch')
    || lower.includes('acceso')
    || lower.includes('disponible')
    || lower.includes('encontrad')
}

watch(selectedTenantSlug, slug => {
  selectedBranchCode.value = null
  if (slug)
    loadBranches(slug)
  else
    branches.value = []
})

watch(loginMode, () => {
  errorMessage.value = ''
  suggestContextChange.value = false
  refVForm.value?.resetValidation()
  if (loginMode.value === 'pin')
    hydratePinStep()
})

onMounted(() => {
  auth.clearAuthOnly()
  hydratePinStep()
})

const submit = async () => {
  errorMessage.value = ''
  suggestContextChange.value = false

  if (loginMode.value === 'password' && isPlatformLogin.value) {
    tenantSlugCookie.value = null
    branchCodeCookie.value = null
    tenantNameCookie.value = null
    branchNameCookie.value = null
  }

  try {
    if (loginMode.value === 'pin') {
      await auth.loginWithPin({
        pin: pinForm.value.pin,
        tenantSlug: pinForm.value.tenant_slug,
        branchCode: pinForm.value.branch_code,
        tenantName: pinForm.value.tenant_name,
        branchName: pinForm.value.branch_name,
      })
    }
    else {
      const slug = isPlatformLogin.value
        ? null
        : (passwordForm.value.tenant_slug?.trim() || tenantSlugCookie.value || null)

      await auth.loginWithPassword({
        username: passwordForm.value.username.trim(),
        password: passwordForm.value.password,
        tenantSlug: slug,
      })
    }

    const contextStore = useContextStore()
    const home = resolveHomeRoute(auth.user, {
      tenantSlug: contextStore.tenantSlug,
      branchCode: contextStore.branchCode,
    })

    await router.replace(route.query.to ? String(route.query.to) : home)
  }
  catch (error) {
    auth.clearAuthOnly()
    const raw = auth.error || getApiErrorMessage(error)

    if (loginMode.value === 'pin' && isContextRelatedError(error, raw)) {
      errorMessage.value = 'No se pudo ingresar con esta empresa/sucursal. Cambia la empresa o sucursal.'
      suggestContextChange.value = true
    }
    else {
      errorMessage.value = /timeout/i.test(raw)
        ? 'No se pudo conectar con el servidor. Verifique que Apache, MySQL y el backend estén activos e intente de nuevo.'
        : raw
    }
  }
}

const onSubmit = async () => {
  if (loginMode.value === 'pin' && pinStep.value === 'select-context') {
    confirmContextSelection()

    return
  }

  const { valid } = await refVForm.value?.validate() ?? { valid: false }

  if (!valid) {
    errorMessage.value = loginMode.value === 'password'
      ? 'Complete usuario y contraseña (y empresa si no es superadmin).'
      : 'Ingrese su PIN.'

    return
  }

  await submit()
}

</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo app-logo">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="app-logo-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </RouterLink>

  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="8"
      class="d-none d-md-flex position-relative"
    >
      <div class="d-flex align-center justify-center w-100 h-100 pa-10">
        <VImg
          :src="authThemeImg"
          class="auth-illustration"
          max-width="700"
        />
      </div>
      <img
        class="auth-footer-mask"
        :src="authThemeMask"
        alt=""
        height="280"
      >
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="420"
        class="mt-12 mt-sm-0 pa-6 login-card"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Bienvenido a {{ themeConfig.app.title }}
          </h4>
          <p class="mb-0">
            Acceso operativo — PIN para caja/garzón o usuario para administración.
          </p>
        </VCardText>

        <VCardText>
          <VTabs
            v-model="loginMode"
            class="mb-4"
          >
            <VTab value="pin">
              PIN
            </VTab>
            <VTab value="password">
              Usuario / contraseña
            </VTab>
          </VTabs>

          <VAlert
            v-if="sessionExpiredHint"
            type="warning"
            variant="tonal"
            class="mb-4"
          >
            {{ sessionExpiredHint }}
          </VAlert>

          <VAlert
            v-if="errorMessage"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ errorMessage }}
            <VBtn
              v-if="loginMode === 'pin' && suggestContextChange"
              variant="text"
              size="small"
              class="mt-2"
              @click="startChangeContext"
            >
              Cambiar empresa / sucursal
            </VBtn>
            <p
              v-else-if="loginMode === 'pin'"
              class="text-caption mb-0 mt-2"
            >
              Verifique PIN, empresa y sucursal. Si el problema continúa, cambie el contexto operativo.
            </p>
            <p
              v-else
              class="text-caption mb-0 mt-2"
            >
              Verifique usuario y contraseña. Los administradores de empresa deben indicar el slug correcto.
            </p>
          </VAlert>

          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VWindow v-model="loginMode">
              <VWindowItem value="pin">
                <template v-if="pinStep === 'pin'">
                  <VCard
                    variant="tonal"
                    color="primary"
                    class="mb-4"
                  >
                    <VCardText class="py-3">
                      <div class="text-body-2">
                        <strong>Empresa:</strong> {{ displayTenantName }}
                      </div>
                      <div class="text-body-2 mt-1">
                        <strong>Sucursal:</strong> {{ displayBranchName }}
                      </div>
                      <VBtn
                        variant="text"
                        size="small"
                        class="mt-2 px-0"
                        @click="startChangeContext"
                      >
                        Cambiar empresa / sucursal
                      </VBtn>
                    </VCardText>
                  </VCard>

                  <VTextField
                    v-model="pinForm.pin"
                    label="PIN"
                    type="password"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    class="login-pin-field"
                    :rules="pinRules"
                    autofocus
                  />
                </template>

                <template v-else>
                  <p class="text-body-2 mb-4">
                    Elija la empresa y sucursal donde va a operar.
                  </p>

                  <VSelect
                    v-model="selectedTenantSlug"
                    :items="tenantItems"
                    label="Empresa"
                    placeholder="Seleccione empresa"
                    :loading="loadingTenants"
                    :disabled="loadingTenants"
                    class="mb-3"
                  />

                  <VSelect
                    v-model="selectedBranchCode"
                    :items="branchItems"
                    label="Sucursal"
                    placeholder="Seleccione sucursal"
                    :loading="loadingBranches"
                    :disabled="!selectedTenantSlug || loadingBranches"
                    class="mb-2"
                  />

                  <VBtn
                    v-if="hasSavedContext"
                    variant="text"
                    size="small"
                    class="mb-2"
                    @click="pinStep = 'pin'; syncPinFormFromCookies()"
                  >
                    Volver al PIN
                  </VBtn>
                </template>
              </VWindowItem>

              <VWindowItem value="password">
                <VRow>
                  <VCol cols="12">
                    <VTextField
                      v-model="passwordForm.username"
                      label="Usuario"
                      :rules="passwordFieldRules"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="passwordForm.password"
                      label="Contraseña"
                      :type="isPasswordVisible ? 'text' : 'password'"
                      :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                      :rules="passwordFieldRules"
                      @click:append-inner="isPasswordVisible = !isPasswordVisible"
                    />
                  </VCol>
                  <VCol
                    v-if="!isPlatformLogin"
                    cols="12"
                  >
                    <VTextField
                      v-model="passwordForm.tenant_slug"
                      label="Empresa (slug)"
                      placeholder="casa-demo"
                      hint="Obligatorio para administradores de tenant"
                      persistent-hint
                      :rules="tenantSlugPasswordRules"
                    />
                  </VCol>
                  <VCol
                    v-else
                    cols="12"
                  >
                    <VAlert
                      type="info"
                      variant="tonal"
                      density="compact"
                    >
                      Acceso plataforma global — no requiere empresa ni sucursal.
                    </VAlert>
                  </VCol>
                </VRow>
              </VWindowItem>
            </VWindow>

            <VBtn
              block
              type="submit"
              size="large"
              class="mt-6 login-submit-btn"
              :loading="auth.loading"
            >
              {{ loginMode === 'pin' && pinStep === 'select-context' ? 'Continuar' : 'Ingresar' }}
            </VBtn>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core/scss/template/pages/page-auth";

.login-card {
  inline-size: 100%;
}

.login-pin-field :deep(input) {
  font-size: 1.75rem;
  letter-spacing: 0.35em;
  text-align: center;
}

.login-submit-btn {
  min-block-size: 3.25rem;
}
</style>
