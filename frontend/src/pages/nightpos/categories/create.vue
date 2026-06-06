<script setup>
import CategoryFormFields from '@/components/nightpos/forms/CategoryFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { createCategory } from '@/api/categories'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.create' } })

const router = useRouter()
const { canCreateCategory } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const form = ref({ name: '', type: 'general', status: 'active' })
const saving = ref(false)
const refForm = ref()

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    await createCategory(form.value)
    notify('Categoría creada')
    await router.push({ name: 'nightpos-categories' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(() => {
  if (!canCreateCategory.value)
    router.replace({ name: 'nightpos-categories' })
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Nueva categoría"
      subtitle="Clasificación del catálogo de productos."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Categorías', to: { name: 'nightpos-categories' } },
        { title: 'Nueva', disabled: true },
      ]"
    />
    <VCard>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <CategoryFormFields v-model="form" />
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-categories' }"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>
</div>
</template>
