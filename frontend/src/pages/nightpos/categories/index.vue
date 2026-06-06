<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { CATALOG_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { fetchCategories } from '@/api/categories'
import { fetchProducts } from '@/api/products'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.list' } })

const { canCreateCategory } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const categories = ref([])
const productCounts = ref({})
const loading = ref(false)

const TYPE_LABELS = {
  general: 'General',
  beverage: 'Bebidas',
  service: 'Servicios',
  food: 'Comida',
}

const headers = [
  { title: 'Nombre', key: 'name' },
  { title: 'Tipo', key: 'type' },
  { title: 'Estado', key: 'status' },
  { title: 'Productos', key: 'products_count' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    const [cats, products] = await Promise.all([
      fetchCategories(),
      fetchProducts().catch(() => []),
    ])
    categories.value = cats
    const counts = {}
    products.forEach(p => {
      if (p.category_id)
        counts[p.category_id] = (counts[p.category_id] || 0) + 1
    })
    productCounts.value = counts
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Categorías de producto"
      subtitle="Clasificación del catálogo."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Categorías', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canCreateCategory"
          color="primary"
          size="large"
          :to="{ name: 'nightpos-categories-create' }"
        >
          <VIcon
            icon="ri-add-line"
            start
          />
          Nueva categoría
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="CATALOG_SECTION_TABS" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="categories"
        item-value="id"
      >
        <template #item.name="{ item }">
          <RouterLink
            :to="{ name: 'nightpos-categories-id-edit', params: { id: item.id } }"
            class="font-weight-medium text-primary"
          >
            {{ item.name }}
          </RouterLink>
        </template>
        <template #item.type="{ item }">
          {{ TYPE_LABELS[item.type] || item.type }}
        </template>
        <template #item.products_count="{ item }">
          {{ productCounts[item.id] ?? 0 }}
        </template>
        <template #item.actions="{ item }">
          <VBtn
            size="small"
            variant="tonal"
            :to="{ name: 'nightpos-categories-id-edit', params: { id: item.id } }"
          >
            Ver / editar
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
</div>
</template>
