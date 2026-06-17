<script setup>
import ProductForm from '@/components/nightpos/products/ProductForm.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchCategories } from '@/api/categories'
import { fetchProduct, quickCreateProduct } from '@/api/products'
import {
  createDefaultProductForm,
  formToQuickCreatePayload,
  productToForm,
} from '@/composables/useProductForm'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.create' } })

const route = useRoute()
const router = useRouter()
const { canCreateProduct, can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const categories = ref([])
const saving = ref(false)
const loading = ref(true)
const productFormRef = ref()

const duplicateId = computed(() => {
  const raw = route.query.duplicate
  return raw ? String(raw) : null
})

const isDuplicate = computed(() => !!duplicateId.value)

const form = ref(createDefaultProductForm())

const canSaveWithPrices = computed(() => can('products.quick_create'))

const reloadCategories = async () => {
  categories.value = await fetchCategories().catch(() => [])
}

const save = async () => {
  if (!canSaveWithPrices.value) {
    notify('Sin permiso para crear producto con precios. Use un usuario con acceso de catálogo en sucursal.', 'warning')

    return
  }

  saving.value = true
  try {
    await quickCreateProduct(formToQuickCreatePayload(form.value))
    notify(isDuplicate.value ? 'Producto duplicado y guardado' : 'Producto creado con precios')
    await router.push({ name: 'nightpos-products' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!canCreateProduct.value) {
    await router.replace({ name: 'nightpos-products' })

    return
  }

  try {
    await reloadCategories()

    if (duplicateId.value) {
      const source = await fetchProduct(duplicateId.value)
      form.value = productToForm(source, source.active_prices ?? [])
      form.value.name = `${source.name} (copia)`
      form.value.sku = ''
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
      :title="isDuplicate ? 'Duplicar producto' : 'Nuevo producto'"
      subtitle="Nombre, categoría, precios y combo en un solo paso — listo para comandar."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', to: { name: 'nightpos-products' } },
        { title: isDuplicate ? 'Duplicar' : 'Nuevo', disabled: true },
      ]"
    />

    <VAlert
      v-if="!canSaveWithPrices"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      Necesita sucursal activa y permiso de alta rápida de productos para guardar con precios.
    </VAlert>

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
          mode="create"
          :categories="categories"
          :saving="saving"
          @submit="save"
        >
          <template #actions>
            <NightPosFormActions
              class="mt-4"
              :saving="saving"
              :save-disabled="!canSaveWithPrices"
              save-label="Guardar producto"
              :cancel-to="{ name: 'nightpos-products' }"
              @save="productFormRef?.submit?.()"
            />
          </template>
        </ProductForm>
      </VCardText>
    </VCard>
  </div>
</template>
