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

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const sessionExpiredHint = computed(() =>
  route.query.reason === 'session_expired'
    ? 'La sesión expiró o fue invalidada. Vuelva a iniciar sesión.'
    : '',
)

const loginMode = ref('pin')
const errorMessage = ref('')
const refVForm = ref()
const showContextFields = ref(false)

const tenantSlugCookie = useCookie('tenantSlug', { maxAge: 60 * 60 * 24 * REMEMBER_DAYS })
const branchCodeCookie = useCookie('branchCode', { maxAge: 60 * 60 * 24 * REMEMBER_DAYS })

const pinForm = ref({
  tenant_slug: tenantSlugCookie.value || 'casa-demo',
  branch_code: branchCodeCookie.value || 'CENTRO',
  pin: '',
})

const passwordForm = ref({
  username: '',
  password: '',
  tenant_slug: '',
})

const isPlatformLogin = computed(() =>
  passwordForm.value.username?.trim().toLowerCase() === 'superadmin',
)

const isPasswordVisible = ref(false)

const requiredRule = v => !!v || 'Requerido'
const pinRules = computed(() => (loginMode.value === 'pin' ? [requiredRule] : []))
const pinContextRules = computed(() =>
  loginMode.value === 'pin' && showContextFields.value ? [requiredRule] : [],
)
const passwordFieldRules = computed(() =>
  loginMode.value === 'password' ? [requiredRule] : [],
)
const tenantSlugPasswordRules = computed(() =>
  loginMode.value === 'password' && !isPlatformLogin.value
    ? [v => !!v?.trim() || 'Requerido para usuarios de empresa']
    : [],
)

const rememberedContext = computed(() =>
  Boolean(tenantSlugCookie.value && branchCodeCookie.value),
)

const contextSummary = computed(() => {
  if (!rememberedContext.value)
    return ''

  return `${pinForm.value.tenant_slug} · ${pinForm.value.branch_code}`
})

onMounted(() => {
  showContextFields.value = !rememberedContext.value
})

const persistContextCookies = () => {
  tenantSlugCookie.value = pinForm.value.tenant_slug?.trim() || null
  branchCodeCookie.value = pinForm.value.branch_code?.trim() || null
}

const clearRememberedContext = () => {
  tenantSlugCookie.value = null
  branchCodeCookie.value = null
  pinForm.value.tenant_slug = 'casa-demo'
  pinForm.value.branch_code = 'CENTRO'
  showContextFields.value = true
  errorMessage.value = ''
}

watch(loginMode, () => {
  errorMessage.value = ''
  refVForm.value?.resetValidation()
})

const submit = async () => {
  errorMessage.value = ''

  if (loginMode.value === 'pin') {
    persistContextCookies()
  }
  else if (isPlatformLogin.value) {
    tenantSlugCookie.value = null
    branchCodeCookie.value = null
  }

  try {
    if (loginMode.value === 'pin') {
      await auth.loginWithPin({
        pin: pinForm.value.pin,
        tenantSlug: pinForm.value.tenant_slug,
        branchCode: pinForm.value.branch_code,
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
    const raw = auth.error || getApiErrorMessage(error)

    errorMessage.value = /timeout/i.test(raw)
      ? 'No se pudo conectar con el servidor. Verifique que Apache, MySQL y el backend estén activos e intente de nuevo.'
      : raw
  }
}

const onSubmit = async () => {
  const { valid } = await refVForm.value?.validate() ?? { valid: false }

  if (!valid) {
    errorMessage.value = loginMode.value === 'password'
      ? 'Complete usuario y contraseña (y empresa si no es superadmin).'
      : 'Complete PIN, empresa y sucursal.'

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
            <p
              v-if="loginMode === 'pin'"
              class="text-caption mb-0 mt-2"
            >
              Verifique que el PIN, la empresa y la sucursal sean correctos.
              Si el problema continúa, contacte al administrador del local.
            </p>
            <p
              v-else
              class="text-caption mb-0 mt-2"
            >
              Verifique usuario y contraseña. Los administradores de empresa deben indicar el slug correcto.
            </p>
          </VAlert>

          <VAlert
            v-if="loginMode === 'pin' && rememberedContext && !showContextFields"
            type="info"
            variant="tonal"
            class="mb-4"
          >
            Operando en: <strong>{{ contextSummary }}</strong>
            (normal para cajero/garzón; no es un error)
            <VBtn
              variant="text"
              size="small"
              class="ms-1"
              @click="showContextFields = true"
            >
              Cambiar sucursal
            </VBtn>
            <VBtn
              variant="text"
              size="small"
              @click="clearRememberedContext"
            >
              Cambiar empresa
            </VBtn>
          </VAlert>

          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VWindow v-model="loginMode">
              <VWindowItem value="pin">
                <VExpandTransition>
                  <VRow v-if="showContextFields">
                    <VCol cols="12">
                      <VTextField
                        v-model="pinForm.tenant_slug"
                        label="Empresa (slug)"
                        placeholder="casa-demo"
                        :rules="pinContextRules"
                      />
                    </VCol>
                    <VCol cols="12">
                      <VTextField
                        v-model="pinForm.branch_code"
                        label="Sucursal (código)"
                        placeholder="CENTRO"
                        :rules="pinContextRules"
                      />
                    </VCol>
                  </VRow>
                </VExpandTransition>

                <div class="mt-4">
                  <VTextField
                    v-model="pinForm.pin"
                    label="PIN"
                    type="password"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    class="login-pin-field"
                    :rules="pinRules"
                  />
                </div>
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
              Ingresar
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
  font-size: 1.5rem;
  letter-spacing: 0.35em;
  text-align: center;
}

.login-submit-btn {
  min-block-size: 3.25rem;
}
</style>
