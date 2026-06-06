<script setup>
import CategoryFormFields from '@/components/nightpos/forms/CategoryFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosFormPageLayout from '@/components/nightpos/layout/NightPosFormPageLayout.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchCategory, updateCategory } from '@/api/categories'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.update' } })

const route = useRoute('nightpos-categories-id-edit')
const router = useRouter()
const { canUpdateProduct } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const form = ref(null)
const saving = ref(false)
const loading = ref(true)
const refForm = ref()

const categoryId = computed(() => Number(route.params.id))

const load = async () => {
  loading.value = true

  try {
    const c = await fetchCategory(categoryId.value)
    form.value = {
      name: c.name,
      type: c.type,
      status: c.status,
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
    await updateCategory(categoryId.value, {
      name: form.value.name.trim(),
      type: form.value.type,
      status: form.value.status,
    })
    notify('Categoría actualizada')
    await router.push({ name: 'nightpos-categories' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!canUpdateProduct.value) {
    await router.replace({ name: 'nightpos-categories' })

    return
  }

  await load()
})
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="form?.name ? `Editar — ${form.name}` : 'Editar categoría'"
      subtitle="Nombre, tipo y estado en el catálogo."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Categorías', to: { name: 'nightpos-categories' } },
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
        title="Categoría"
        hint="Clasificación de productos del tenant actual."
      >
        <CategoryFormFields v-model="form" />
        <template #actions>
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-categories' }"
            @save="save"
          />
        </template>
      </NightPosFormPageLayout>
    </VForm>
</div>
</template>
