<script setup>
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import QuickProductPriceCreateDialog from '@/components/nightpos/catalog/QuickProductPriceCreateDialog.vue'
import { CATALOG_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { usePosCatalog } from '@/composables/usePosCatalog'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { productCategoryLabel } from '@/composables/useOrderHelpers'

definePage({ meta: { permission: 'products.list' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const {
  categories,
  products,
  meta,
  loading,
  search,
  categoryId,
  categoryMap,
  loadMeta,
  fetchResults,
  selectCategory,
  clearCategory,
  sellableOnly,
  unpricedOnly,
  limit,
} = usePosCatalog({
  sellableOnly: false,
  unpricedOnly: true,
  limit: 100,
})

sellableOnly.value = false
unpricedOnly.value = true
limit.value = 100

const canConfigurePrice = computed(() =>
  can('product_prices.quick_create') || can('products.update'),
)

const showPriceDialog = ref(false)
const priceDialogProductId = ref(null)
const priceDialogProductName = ref('')
const priceDialogMode = ref('SOLO_CLIENTE')

const categoryFilterItems = computed(() => [
  { title: 'Todas', value: null },
  ...categories.value
    .filter(c => c.id != null)
    .map(c => ({ title: c.name, value: c.id })),
])

const openPriceDialog = product => {
  priceDialogProductId.value = product.id
  priceDialogProductName.value = product.name ?? ''
  priceDialogMode.value = 'SOLO_CLIENTE'
  showPriceDialog.value = true
}

const onPriceCreated = async () => {
  showPriceDialog.value = false
  await loadMeta()
  await fetchResults()
  notify('Precio configurado. El producto ya es vendible.', 'success')
}

onMounted(async () => {
  await loadMeta()
  await fetchResults()
})
</script>

<template>
  <div class="unpriced-products-page">
    <NightPosPageHeader
      title="Productos sin precio"
      subtitle="Activos que aún no pueden venderse en caja, garzón o venta directa."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Catálogo', disabled: true },
        { title: 'Sin precio', disabled: true },
      ]"
    />

    <NightPosSectionTabs :tabs="CATALOG_SECTION_TABS" />
    <NightPosContextCards />

    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="search"
              label="Buscar producto"
              prepend-inner-icon="ri-search-line"
              clearable
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              :model-value="categoryId"
              label="Categoría"
              :items="categoryFilterItems"
              clearable
              hide-details
              @update:model-value="value => value == null ? clearCategory() : selectCategory(value)"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VAlert
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      {{ meta.unpriced_count ?? 0 }} producto(s) activo(s) sin precio vigente.
      Configura al menos modalidad Solo para habilitar la venta.
    </VAlert>

    <VCard>
      <VDataTable
        :headers="[
          { title: 'Producto', key: 'name' },
          { title: 'Categoría', key: 'category_id' },
          { title: 'Estado', key: 'status' },
          { title: 'Acción', key: 'actions', sortable: false },
        ]"
        :items="products"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
      >
        <template #item.category_id="{ item }">
          {{ productCategoryLabel(item, categoryMap) }}
        </template>

        <template #item.status>
          <VChip
            color="warning"
            size="small"
            variant="tonal"
          >
            Sin precio
          </VChip>
        </template>

        <template #item.actions="{ item }">
          <VBtn
            v-if="canConfigurePrice"
            size="small"
            variant="tonal"
            color="primary"
            prepend-icon="ri-price-tag-3-line"
            @click="openPriceDialog(item)"
          >
            Configurar precio
          </VBtn>
          <VBtn
            v-else
            size="small"
            variant="text"
            :to="{ name: 'nightpos-products-id-prices', params: { id: item.id } }"
          >
            Ver precios
          </VBtn>
        </template>

        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">
            No hay productos sin precio con los filtros actuales.
          </div>
        </template>
      </VDataTable>
    </VCard>

    <QuickProductPriceCreateDialog
      v-model="showPriceDialog"
      :product-id="priceDialogProductId"
      :product-name="priceDialogProductName"
      :sale-mode="priceDialogMode"
      @created="onPriceCreated"
    />
  </div>
</template>
