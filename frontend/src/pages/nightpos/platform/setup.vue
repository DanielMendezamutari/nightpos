<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { platformSetup } from '@/api/platform'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { getApiErrorMessage } from '@/services/http'
import { useContextStore } from '@/stores/context'

definePage({ meta: { permission: 'platform.setup' } })

const router = useRouter()
const contextStore = useContextStore()
const { isSuperAdmin } = usePlatformContext()
const { notify } = useNightPosNotify()

const step = ref(1)
const saving = ref(false)
const refForm = ref()

const tenant = ref({
  name: '',
  slug: '',
  status: 'active',
  plan_name: 'standard',
})
const branch = ref({
  name: '',
  code: '',
  address: '',
  status: 'active',
})
const admin = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  pin: '',
})

const result = ref(null)

watch(() => tenant.value.name, name => {
  if (!tenant.value.slug && name) {
    tenant.value.slug = name.trim().toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9-]/g, '')
  }
})

const finish = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    const data = await platformSetup({
      tenant: {
        name: tenant.value.name.trim(),
        slug: tenant.value.slug.trim(),
        status: tenant.value.status,
        plan_name: tenant.value.plan_name || null,
      },
      branch: {
        name: branch.value.name.trim(),
        code: branch.value.code.trim().toUpperCase(),
        address: branch.value.address?.trim() || null,
        status: branch.value.status,
      },
      admin: {
        name: admin.value.name.trim(),
        username: admin.value.username.trim(),
        email: admin.value.email?.trim() || null,
        password: admin.value.password,
        pin: admin.value.pin || null,
      },
    })
    result.value = data
    step.value = 4
    notify('Empresa operativa creada')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const operate = async () => {
  if (!result.value?.tenant?.slug)
    return

  await contextStore.applyContext({
    tenantSlug: result.value.tenant.slug,
    branchCode: result.value.branch?.code ?? null,
  })
  await router.push({ name: 'nightpos-dashboard' })
}

onMounted(() => {
  if (!isSuperAdmin.value)
    router.replace({ name: 'nightpos-platform-dashboard' })
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Alta rápida SaaS"
      subtitle="Empresa + primera sucursal + administrador en un solo flujo."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Setup', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VCard>
      <VCardText>
        <VStepper
          v-model="step"
          alt-labels
          class="mb-6"
        >
          <VStepperHeader>
            <VStepperItem
              :value="1"
              title="Empresa"
            />
            <VStepperItem
              :value="2"
              title="Sucursal"
            />
            <VStepperItem
              :value="3"
              title="Administrador"
            />
            <VStepperItem
              :value="4"
              title="Confirmación"
            />
          </VStepperHeader>
        </VStepper>

        <VForm
          ref="refForm"
          @submit.prevent="step === 3 ? finish() : null"
        >
          <div v-show="step === 1">
            <VTextField
              v-model="tenant.name"
              label="Nombre empresa *"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'Requerido']"
            />
            <VTextField
              v-model="tenant.slug"
              label="Slug *"
              hint="Único — login y contexto"
              persistent-hint
              class="mb-3"
              :rules="[v => !!v?.trim() || 'Requerido']"
            />
            <VSelect
              v-model="tenant.status"
              :items="[
                { title: 'Activa', value: 'active' },
                { title: 'Inactiva', value: 'inactive' },
              ]"
              label="Estado"
              class="mb-3"
            />
            <VTextField
              v-model="tenant.plan_name"
              label="Plan"
            />
          </div>

          <div v-show="step === 2">
            <VTextField
              v-model="branch.name"
              label="Nombre sucursal *"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'Requerido']"
            />
            <VTextField
              v-model="branch.code"
              label="Código sucursal *"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'Requerido']"
            />
            <VTextField
              v-model="branch.address"
              label="Dirección (opcional)"
            />
          </div>

          <div v-show="step === 3">
            <VTextField
              v-model="admin.name"
              label="Nombre admin *"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'Requerido']"
            />
            <VTextField
              v-model="admin.username"
              label="Usuario *"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'Requerido']"
            />
            <VTextField
              v-model="admin.email"
              label="Email (opcional)"
              class="mb-3"
            />
            <VTextField
              v-model="admin.password"
              type="password"
              label="Contraseña *"
              class="mb-3"
              :rules="[v => !!v && v.length >= 6 || 'Mínimo 6 caracteres']"
            />
            <VTextField
              v-model="admin.pin"
              label="PIN (opcional)"
              maxlength="6"
            />
          </div>

          <div v-show="step === 4 && result">
            <VAlert
              type="success"
              variant="tonal"
              class="mb-4"
            >
              <strong>{{ result.tenant?.name }}</strong> lista para operar.
              Sucursal <strong>{{ result.branch?.name }}</strong> ({{ result.branch?.code }}).
              Admin: <strong>{{ result.admin?.username }}</strong>.
            </VAlert>
            <VBtn
              color="primary"
              size="large"
              prepend-icon="ri-dashboard-line"
              @click="operate"
            >
              Operar en esta empresa
            </VBtn>
          </div>

          <div
            v-if="step < 4"
            class="d-flex gap-2 mt-6"
          >
            <VBtn
              v-if="step > 1"
              variant="tonal"
              @click="step -= 1"
            >
              Atrás
            </VBtn>
            <VSpacer />
            <VBtn
              v-if="step < 3"
              color="primary"
              @click="step += 1"
            >
              Siguiente
            </VBtn>
            <VBtn
              v-else
              color="primary"
              :loading="saving"
              @click="finish"
            >
              Crear empresa operativa
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
</div>
</template>
