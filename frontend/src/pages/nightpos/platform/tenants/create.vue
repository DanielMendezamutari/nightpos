<script setup>
import TenantFormFields from '@/components/nightpos/forms/TenantFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosFormPageLayout from '@/components/nightpos/layout/NightPosFormPageLayout.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { createAdminTenant } from '@/api/tenants'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.create' } })

const router = useRouter()
const { canCreateAdminTenant } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const form = ref({
  name: '',
  slug: '',
  status: 'active',
  plan_name: 'standard',
  subscription_starts_at: '',
  subscription_ends_at: '',
})
const saving = ref(false)
const refForm = ref()

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
      plan_name: form.value.plan_name || null,
    }
    if (form.value.subscription_starts_at)
      payload.subscription_starts_at = form.value.subscription_starts_at
    if (form.value.subscription_ends_at)
      payload.subscription_ends_at = form.value.subscription_ends_at

    const data = await createAdminTenant(payload)
    notify('Empresa creada')
    await router.push({ name: 'nightpos-platform-tenants-id', params: { id: data.id } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(() => {
  if (!canCreateAdminTenant.value)
    router.replace({ name: 'nightpos-platform-tenants' })
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Nueva empresa"
      subtitle="Alta de cliente SaaS en la plataforma NightPOS."
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
        title="Plan y estado"
        hint="Defina suscripción y visibilidad del tenant."
      >
        <template #form-title>
          Datos de la empresa
        </template>
        <TenantFormFields v-model="form" />
        <template #aside>
          <VList density="compact">
            <VListItem
              prepend-icon="ri-information-line"
              title="Slug único"
              subtitle="Usado en login PIN y headers X-Tenant-Slug"
            />
            <VListItem
              prepend-icon="ri-vip-crown-line"
              title="Plan"
              subtitle="Nombre comercial del plan asignado"
            />
            <VListItem
              prepend-icon="ri-calendar-line"
              title="Suscripción"
              subtitle="Opcional al crear; renovación en módulo Planes"
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
