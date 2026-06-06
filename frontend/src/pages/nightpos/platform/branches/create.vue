<script setup>
import BranchFormFields from '@/components/nightpos/forms/BranchFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { createAdminBranch } from '@/api/branches'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.branches.create' } })

const router = useRouter()
const { canCreateAdminBranch } = useNightPosPermissions()
const { hasTenantContext, tenantSlug } = usePlatformContext()
const { notify } = useNightPosNotify()

const form = ref({ name: '', code: '', address: '', status: 'active' })
const saving = ref(false)
const refForm = ref()

const breadcrumbs = [
  { title: 'Plataforma', disabled: true },
  { title: 'Sucursales', to: { name: 'nightpos-platform-branches' } },
  { title: 'Nueva', disabled: true },
]

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    await createAdminBranch({
      name: form.value.name.trim(),
      code: form.value.code.trim().toUpperCase(),
      address: form.value.address?.trim() || null,
      status: form.value.status,
    })
    notify('Sucursal creada')
    await router.push({ name: 'nightpos-platform-branches' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(() => {
  if (!canCreateAdminBranch.value || !hasTenantContext.value) {
    router.replace({ name: 'nightpos-platform-branches' })
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Nueva sucursal"
      :subtitle="`Empresa en contexto: ${tenantSlug || '—'}`"
      :breadcrumbs="breadcrumbs"
    />
    <VCard>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <BranchFormFields v-model="form" />
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-platform-branches' }"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>
</div>
</template>
