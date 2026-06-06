<script setup>
import { fetchAdminBranches } from '@/api/branches'
import { fetchAdminTenants } from '@/api/tenants'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'
import { resolveHomeRoute } from '@/utils/resolveHomeRoute'
import { useAuthStore } from '@/stores/auth'

const {
  isSuperAdmin,
  tenantSlug,
  branchCode,
  hasTenantContext,
  contextLabel,
  applyContext,
  clearContext,
} = usePlatformContext()

const { notify } = useNightPosNotify()
const router = useRouter()
const auth = useAuthStore()

const showDialog = ref(false)
const loading = ref(false)
const tenants = ref([])
const branches = ref([])

const draft = ref({
  tenant_slug: null,
  branch_code: null,
})

const open = async () => {
  if (!isSuperAdmin.value)
    return

  draft.value = {
    tenant_slug: tenantSlug.value,
    branch_code: branchCode.value,
  }

  showDialog.value = true
  await loadOptions()
}

const loadOptions = async () => {
  loading.value = true

  try {
    tenants.value = await fetchAdminTenants()
    branches.value = draft.value.tenant_slug
      ? await fetchAdminBranches(draft.value.tenant_slug)
      : []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onTenantChange = async slug => {
  draft.value.tenant_slug = slug
  draft.value.branch_code = null
  branches.value = []

  if (!slug)
    return

  loading.value = true

  try {
    branches.value = await fetchAdminBranches(slug)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const save = async () => {
  loading.value = true

  try {
    if (!draft.value.tenant_slug) {
      await clearContext()
      notify('Contexto global (sin empresa)')
    }
    else {
      await applyContext({
        tenantSlug: draft.value.tenant_slug,
        branchCode: draft.value.branch_code,
      })
      notify('Contexto operativo actualizado')

      if (draft.value.branch_code) {
        await router.push(resolveHomeRoute(auth.user, {
          tenantSlug: draft.value.tenant_slug,
          branchCode: draft.value.branch_code,
        }))
      }
      else if (!draft.value.tenant_slug) {
        await router.push(resolveHomeRoute(auth.user, { tenantSlug: null, branchCode: null }))
      }
    }

    showDialog.value = false
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

defineExpose({ open })
</script>

<template>
  <template v-if="isSuperAdmin">
    <VBtn
      size="small"
      variant="tonal"
      color="primary"
      prepend-icon="ri-focus-3-line"
      @click="open"
    >
      {{ hasTenantContext ? contextLabel : 'Elegir empresa' }}
    </VBtn>

    <VDialog
      v-model="showDialog"
      max-width="480"
    >
      <VCard title="Contexto operativo">
        <VCardText>
          <p class="text-body-2 mb-4">
            Seleccione empresa y sucursal para operar caja, comandas y catálogo como ese local.
            Sin selección permanece en modo plataforma global.
          </p>

          <VSelect
            v-model="draft.tenant_slug"
            :items="tenants"
            item-title="name"
            item-value="slug"
            label="Empresa"
            clearable
            placeholder="Modo global"
            :loading="loading"
            @update:model-value="onTenantChange"
          >
            <template #item="{ props: itemProps, item }">
              <VListItem
                v-bind="itemProps"
                :subtitle="item.raw.slug"
              />
            </template>
          </VSelect>

          <VSelect
            v-model="draft.branch_code"
            :items="branches"
            item-title="name"
            item-value="code"
            label="Sucursal"
            class="mt-4"
            clearable
            :disabled="!draft.tenant_slug"
            :loading="loading"
            :hint="draft.tenant_slug ? 'Opcional hasta abrir caja' : ''"
            persistent-hint
          >
            <template #item="{ props: itemProps, item }">
              <VListItem
                v-bind="itemProps"
                :subtitle="item.raw.code"
              />
            </template>
          </VSelect>
        </VCardText>

        <VCardActions>
          <VBtn
            variant="text"
            @click="showDialog = false"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            color="primary"
            :loading="loading"
            @click="save"
          >
            Aplicar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </template>
</template>
