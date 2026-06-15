<script setup>
import TenantFormFields from '@/components/nightpos/forms/TenantFormFields.vue'
import TenantProvisionFields from '@/components/nightpos/forms/TenantProvisionFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosFormPageLayout from '@/components/nightpos/layout/NightPosFormPageLayout.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchPlatformPlans } from '@/api/plans'
import { createAdminTenant } from '@/api/tenants'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.create' } })

const router = useRouter()
const { canCreateAdminTenant } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const plans = ref([])
const form = ref({
  name: '',
  slug: '',
  status: 'active',
  plan_id: null,
  plan_name: '',
  subscription_starts_at: '',
  subscription_ends_at: '',
  branch: { name: '', code: '', address: '' },
  admin: { name: '', username: '', email: '', password: '', pin: '' },
})
const saving = ref(false)
const refForm = ref()

watch(() => form.value.name, name => {
  if (!form.value.slug && name) {
    form.value.slug = name.trim().toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9-]/g, '')
  }
})

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    const payload = {
      name: form.value.name.trim(),
      slug: form.value.slug.trim().toLowerCase(),
      status: form.value.status,
      branch: {
        name: form.value.branch.name.trim(),
        code: form.value.branch.code.trim().toUpperCase(),
        address: form.value.branch.address?.trim() || null,
        status: 'active',
      },
      admin: {
        name: form.value.admin.name.trim(),
        username: form.value.admin.username.trim(),
        email: form.value.admin.email?.trim() || null,
        password: form.value.admin.password,
        pin: form.value.admin.pin || null,
      },
    }
    if (form.value.plan_id)
      payload.plan_id = form.value.plan_id
    if (form.value.subscription_starts_at)
      payload.subscription_starts_at = form.value.subscription_starts_at
    if (form.value.subscription_ends_at)
      payload.subscription_ends_at = form.value.subscription_ends_at

    const data = await createAdminTenant(payload)
    notify('Empresa operativa creada')
    await router.push({ name: 'nightpos-platform-tenants-id', params: { id: data.tenant.id } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!canCreateAdminTenant.value)
    router.replace({ name: 'nightpos-platform-tenants' })

  try {
    plans.value = await fetchPlatformPlans()
    const freePlan = plans.value.find(p => p.code === 'FREE')
    if (freePlan)
      form.value.plan_id = freePlan.id
  }
  catch {
    plans.value = []
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Nueva empresa"
      subtitle="Alta completa: empresa, sucursal inicial y administrador."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Empresas', to: { name: 'nightpos-platform-tenants' } },
        { title: 'Crear', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VForm
      ref="refForm"
      @submit.prevent="save"
    >
      <NightPosFormPageLayout
        title="Datos de la empresa"
        hint="Provisiona tenant, roles, permisos, sucursal y admin en una sola operación."
      >
        <TenantFormFields
          v-model="form"
          :plans="plans"
          show-plan-select
        />
        <VDivider class="my-4" />
        <div class="text-subtitle-1 mb-3">
          Sucursal y administrador inicial
        </div>
        <TenantProvisionFields v-model="form" />
        <template #aside>
          <VList density="compact">
            <VListItem
              prepend-icon="ri-information-line"
              title="Provisionamiento completo"
              subtitle="Igual que el wizard de setup: nunca crea tenant vacío"
            />
            <VListItem
              prepend-icon="ri-vip-crown-line"
              title="Plan"
              subtitle="Asigna límites operativos desde el catálogo"
            />
          </VList>
        </template>
        <template #actions>
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-platform-tenants' }"
            @save="save"
          />
        </template>
      </NightPosFormPageLayout>
    </VForm>
  </div>
</template>
