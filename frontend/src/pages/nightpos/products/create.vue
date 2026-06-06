<script setup>
import ProductPricingFields from '@/components/nightpos/catalog/ProductPricingFields.vue'
import QuickCategoryCreateDialog from '@/components/nightpos/catalog/QuickCategoryCreateDialog.vue'
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchCategories } from '@/api/categories'
import { quickCreateProduct } from '@/api/products'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.create' } })

const router = useRouter()
const { can, canCreateProduct } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const tab = ref('basico')
const categories = ref([])
const showQuickCategory = ref(false)
const saving = ref(false)
const refForm = ref()

const form = ref({
  name: '',
  category_id: null,
  product_type: 'beverage',
  unit: 'unit',
  status: 'active',
  solo_price: null,
  companion_price: null,
  girl_amount: null,
  house_amount: null,
})

const canSaveWithPrices = computed(() => can('products.quick_create'))

const reloadCategories = async () => {
  categories.value = await fetchCategories().catch(() => [])
}

const onCategoryCreated = async category => {
  await reloadCategories()
  if (category?.id)
    form.value.category_id = category.id
}

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  if (!canSaveWithPrices.value) {
    notify('Sin permiso para crear producto con precios. Use un usuario con acceso de catálogo en sucursal.', 'warning')

    return
  }

  if (!form.value.category_id) {
    notify('Seleccione una categoría.', 'warning')

    return
  }

  saving.value = true
  try {
    const payload = {
      name: form.value.name.trim(),
      category_id: form.value.category_id,
      solo_price: Number(form.value.solo_price),
    }

    const companion = Number(form.value.companion_price)
    if (companion > 0) {
      payload.companion_price = companion
      payload.girl_amount = Number(form.value.girl_amount)
      payload.house_amount = Number(form.value.house_amount)
    }

    await quickCreateProduct(payload)
    notify('Producto creado con precios')
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

  await reloadCategories()
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Nuevo producto"
      subtitle="Nombre, categoría y precios en un solo paso — listo para comandar."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', to: { name: 'nightpos-products' } },
        { title: 'Nuevo', disabled: true },
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

    <VCard>
      <VTabs
        v-model="tab"
        class="border-b"
      >
        <VTab value="basico">
          Básico y precios
        </VTab>
        <VTab value="avanzado">
          Avanzado
        </VTab>
      </VTabs>

      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VWindow v-model="tab">
            <VWindowItem value="basico">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="form.name"
                    label="Nombre del producto"
                    placeholder="Ej. Paceña"
                    :rules="[v => !!v?.trim() || 'Requerido']"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VSelect
                    v-model="form.category_id"
                    label="Categoría"
                    :items="categories.map(c => ({ title: c.name, value: c.id }))"
                    :rules="[v => !!v || 'Requerido']"
                  >
                    <template
                      v-if="can('product-categories.create')"
                      #append-item
                    >
                      <VDivider class="my-2" />
                      <VListItem
                        prepend-icon="ri-folder-add-line"
                        title="+ Nueva categoría"
                        class="text-primary"
                        @click="showQuickCategory = true"
                      />
                    </template>
                  </VSelect>
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VSelect
                    v-model="form.status"
                    label="Activo"
                    :items="[
                      { title: 'Sí — activo', value: 'active' },
                      { title: 'No — inactivo', value: 'inactive' },
                    ]"
                  />
                </VCol>
              </VRow>

              <VDivider class="my-4" />

              <ProductPricingFields v-model="form" />
            </VWindowItem>

            <VWindowItem value="avanzado">
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VSelect
                    v-model="form.product_type"
                    label="Tipo"
                    :items="[
                      { title: 'Bebida', value: 'beverage' },
                      { title: 'Servicio', value: 'service' },
                      { title: 'Comida', value: 'food' },
                    ]"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.unit"
                    label="Unidad"
                    hint="unit, botella, etc."
                    persistent-hint
                  />
                </VCol>
              </VRow>
              <VAlert
                type="info"
                variant="tonal"
                class="mt-4"
              >
                SKU, código de barras e inventario se agregarán en una versión posterior. El alta operativa usa bebida por defecto.
              </VAlert>
            </VWindowItem>
          </VWindow>

          <NightPosFormActions
            class="mt-4"
            :saving="saving"
            save-label="Guardar producto"
            :cancel-to="{ name: 'nightpos-products' }"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>

    <QuickCategoryCreateDialog
      v-model="showQuickCategory"
      @created="onCategoryCreated"
    />
</div>
</template>
