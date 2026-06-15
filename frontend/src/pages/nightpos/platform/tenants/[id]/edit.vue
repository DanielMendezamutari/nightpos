<script setup>
import TenantFormFields from '@/components/nightpos/forms/TenantFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosFormPageLayout from '@/components/nightpos/layout/NightPosFormPageLayout.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchPlatformPlans } from '@/api/plans'
import { fetchAdminTenant, updateAdminTenant } from '@/api/tenants'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.list' } })

const route = useRoute('nightpos-platform-tenants-id-edit')
const router = useRouter()
const { notify } = useNightPosNotify()

const plans = ref([])
const form = ref(null)
const saving = ref(false)
const loading = ref(true)
const refForm = ref()

const tenantId = computed(() => Number(route.params.id))

const breadcrumbs = computed(() => [
  { title: 'Plataforma', disabled: true },
  { title: 'Empresas', to: { name: 'nightpos-platform-tenants' } },
  { title: form.value?.name || 'Editar', disabled: true },
])

const load = async () => {
  loading.value = true

  try {
    const [t, planList] = await Promise.all([
      fetchAdminTenant(tenantId.value),
      fetchPlatformPlans(),
    ])
    plans.value = planList
    form.value = {
      name: t.name,
      slug: t.slug,
      status: t.status,
      plan_id: t.plan_id || null,
      plan_name: t.plan_name || '',
      subscription_starts_at: t.subscription_starts_at || '',
      subscription_ends_at: t.subscription_ends_at || '',
    }
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
  if (!valid)
    return

  saving.value = true

  try {
    const payload = {
      name: form.value.name.trim(),
      slug: form.value.slug.trim().toLowerCase(),
      status: form.value.status,
      plan_id: form.value.plan_id || null,
    }
    if (form.value.subscription_starts_at)
      payload.subscription_starts_at = form.value.subscription_starts_at
    if (form.value.subscription_ends_at)
      payload.subscription_ends_at = form.value.subscription_ends_at

    await updateAdminTenant(tenantId.value, payload)
    notify('Empresa actualizada')
    await router.push({ name: 'nightpos-platform-tenants-id', params: { id: tenantId.value } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Editar empresa"
      subtitle="Datos del cliente SaaS en la plataforma."
      :breadcrumbs="breadcrumbs"
    />
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VForm
      v-else-if="form"
      ref="refForm"
      @submit.prevent="save"
    >
      <NightPosFormPageLayout
        title="Empresa"
        hint="Nombre, slug, plan y vigencia de suscripción."
      >
        <TenantFormFields
          v-model="form"
          :plans="plans"
          show-plan-select
        />
        <template #actions>
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-platform-tenants-id', params: { id: tenantId } }"
            @save="save"
          />
        </template>
      </NightPosFormPageLayout>
    </VForm>
  </div>
</template>
