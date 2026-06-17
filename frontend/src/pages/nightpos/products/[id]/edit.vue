<script setup>
import ProductForm from '@/components/nightpos/products/ProductForm.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchCategories } from '@/api/categories'
import { fetchProduct, replaceActiveProductPrice, updateProduct } from '@/api/products'
import {
  createDefaultProductForm,
  formPricePayloads,
  formToUpdatePayload,
  productToForm,
} from '@/composables/useProductForm'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.update' } })

const route = useRoute('nightpos-products-id-edit')
const router = useRouter()
const { canUpdateProduct } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const categories = ref([])
const form = ref(createDefaultProductForm())
const saving = ref(false)
const loading = ref(true)
const productFormRef = ref()

const save = async () => {
  saving.value = true
  try {
    await updateProduct(route.params.id, formToUpdatePayload(form.value))

    for (const pricePayload of formPricePayloads(form.value)) {
      await replaceActiveProductPrice(route.params.id, pricePayload)
    }

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
    const [product, cats] = await Promise.all([
      fetchProduct(route.params.id),
      fetchCategories().catch(() => []),
    ])
    categories.value = cats
    form.value = productToForm(product, product.active_prices ?? [])
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
      :title="`Editar — ${form.name || ''}`"
      subtitle="Datos, precios y combo en una sola pantalla."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', to: { name: 'nightpos-products' } },
        { title: form.name || 'Editar', disabled: true },
      ]"
    />
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VCard v-else>
      <VCardText>
        <ProductForm
          ref="productFormRef"
          v-model="form"
          mode="edit"
          :categories="categories"
          :saving="saving"
          @submit="save"
        >
          <template #actions>
            <NightPosFormActions
              class="mt-4"
              :saving="saving"
              :cancel-to="{ name: 'nightpos-products-id', params: { id: route.params.id } }"
              @save="productFormRef?.submit?.()"
            />
          </template>
        </ProductForm>
      </VCardText>
    </VCard>
  </div>
</template>
