<script setup>
import BranchFormFields from '@/components/nightpos/forms/BranchFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosFormPageLayout from '@/components/nightpos/layout/NightPosFormPageLayout.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminBranch, updateAdminBranch } from '@/api/branches'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.branches.list' } })

const route = useRoute('nightpos-platform-branches-id-edit')
const router = useRouter()
const { tenantSlug } = usePlatformContext()
const { notify } = useNightPosNotify()

const form = ref(null)
const saving = ref(false)
const loading = ref(true)
const refForm = ref()

const branchId = computed(() => Number(route.params.id))

const load = async () => {
  loading.value = true

  try {
    const b = await fetchAdminBranch(branchId.value, tenantSlug.value)
    form.value = {
      name: b.name,
      code: b.code,
      address: b.address || '',
      status: b.status,
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
    await updateAdminBranch(branchId.value, {
      name: form.value.name.trim(),
      code: form.value.code.trim().toUpperCase(),
      address: form.value.address?.trim() || null,
      status: form.value.status,
    }, tenantSlug.value)
    notify('Sucursal actualizada')
    await router.push({ name: 'nightpos-platform-branches-id', params: { id: branchId.value } })
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
      title="Editar sucursal"
      subtitle="Datos operativos de la sucursal seleccionada."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Sucursales', to: { name: 'nightpos-platform-branches' } },
        { title: form?.name || 'Editar', disabled: true },
      ]"
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
        title="Sucursal"
        hint="Código único por empresa, dirección y estado."
      >
        <BranchFormFields v-model="form" />
        <template #actions>
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-platform-branches-id', params: { id: branchId } }"
            @save="save"
          />
        </template>
      </NightPosFormPageLayout>
    </VForm>
</div>
</template>
