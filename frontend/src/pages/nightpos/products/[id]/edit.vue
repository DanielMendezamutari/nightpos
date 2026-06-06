<script setup>
import ProductFormFields from '@/components/nightpos/forms/ProductFormFields.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchCategories } from '@/api/categories'
import { fetchProduct, updateProduct } from '@/api/products'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.update' } })

const route = useRoute('nightpos-products-id-edit')
const router = useRouter()
const { canUpdateProduct } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const categories = ref([])
const form = ref(null)
const saving = ref(false)
const loading = ref(true)
const refForm = ref()

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    await updateProduct(route.params.id, {
      ...form.value,
      category_id: form.value.category_id || null,
    })
    notify('Producto actualizado')
    await router.push({ name: 'nightpos-products-id', params: { id: route.params.id } })
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
    await router.replace({ name: 'nightpos-products' })

    return
  }

  try {
    const [p, cats] = await Promise.all([
      fetchProduct(route.params.id),
      fetchCategories().catch(() => []),
    ])
    categories.value = cats
    form.value = {
      name: p.name,
      product_type: p.product_type,
      category_id: p.category_id,
      unit: p.unit || 'unit',
      status: p.status,
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="`Editar — ${form?.name || ''}`"
      subtitle="Datos del producto en catálogo."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', to: { name: 'nightpos-products' } },
        { title: form?.name || 'Editar', disabled: true },
      ]"
    />
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VCard v-else-if="form">
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <ProductFormFields
            v-model="form"
            :categories="categories"
          />
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-products-id', params: { id: route.params.id } }"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>
</div>
</template>
