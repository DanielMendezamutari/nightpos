<script setup>
import {
  formatMoney,
  productActivePrice,
  productCategoryLabel,
} from '@/composables/useOrderHelpers'
import { useOrderProductShortcuts } from '@/composables/useOrderProductShortcuts'
import { usePosCatalog } from '@/composables/usePosCatalog'

const props = defineProps({
  layout: { type: String, default: 'grid' },
  sellableOnly: { type: Boolean, default: true },
  compact: { type: Boolean, default: false },
  showFavorites: { type: Boolean, default: true },
  canConfigurePrice: { type: Boolean, default: false },
  canCreateProduct: { type: Boolean, default: false },
  selectedProductId: { type: [Number, String], default: null },
  selectedSaleMode: { type: String, default: null },
  searchPlaceholder: { type: String, default: 'Buscar producto…' },
  autofocus: { type: Boolean, default: false },
})

const emit = defineEmits([
  'pick-product',
  'pick-mode',
  'pick-combo',
  'configure-price',
  'create-product',
  'toggle-favorite',
])

const {
  sellableOnly: catalogSellableOnly,
  categories,
  products,
  meta,
  loading,
  search,
  categoryId,
  showResults,
  categoryMap,
  loadMeta,
  fetchResults,
  fetchByIds,
  selectCategory,
  clearCategory,
  showFavorites: showFavoritesView,
  showRecents: showRecentsView,
  resetBrowse,
} = usePosCatalog({ sellableOnly: props.sellableOnly })

const {
  favorites,
  recents,
  isFavorite,
  favoriteProducts,
  recentProducts,
} = useOrderProductShortcuts()

const shortcutProducts = ref([])
const shortcutsLoading = ref(false)

const favoriteList = computed(() => favoriteProducts(shortcutProducts.value))
const recentList = computed(() => recentProducts(shortcutProducts.value))

const categoryChips = computed(() => {
  const chips = categories.value
    .filter(c => (c.product_count ?? 0) > 0 || (c.sellable_count ?? 0) > 0)
    .map(c => ({
      id: c.id,
      name: c.name,
      count: props.sellableOnly ? (c.sellable_count ?? 0) : (c.product_count ?? 0),
    }))
    .filter(c => c.count > 0)

  return chips
})

const priceFor = (product, saleMode) => productActivePrice(product, saleMode)

const priceLabel = (product, saleMode) => {
  const row = priceFor(product, saleMode)

  return row ? formatMoney(row.price, row.currency) : null
}

const isModeSelected = (productId, saleMode) =>
  Number(props.selectedProductId) === Number(productId) && props.selectedSaleMode === saleMode

const loadShortcuts = async () => {
  if (!props.showFavorites)
    return

  const ids = [...new Set([...favorites.value, ...recents.value].map(Number).filter(Boolean))]

  if (!ids.length) {
    shortcutProducts.value = []

    return
  }

  shortcutsLoading.value = true

  try {
    shortcutProducts.value = await fetchByIds(ids)
  }
  finally {
    shortcutsLoading.value = false
  }
}

const onPickProduct = product => emit('pick-product', product)

const onPickMode = (product, saleMode) => emit('pick-mode', { product, saleMode })

const onPickCombo = product => emit('pick-combo', { product, saleMode: 'CON_ACOMPANANTE' })

const onToggleFavorite = product => {
  emit('toggle-favorite', product)
  loadShortcuts()
}

const onFavoriteChip = product => {
  showFavoritesView(favorites.value)
  onPickProduct(product)
}

const onRecentChip = product => {
  showRecentsView(recents.value)
  onPickProduct(product)
}

const onCategoryClick = chip => {
  if (categoryId.value === chip.id)
    clearCategory()
  else
    selectCategory(chip.id)
}

const refresh = async () => {
  await loadMeta()
  await fetchResults()
  await loadShortcuts()
}

defineExpose({ refresh, resetBrowse })

onMounted(async () => {
  await loadMeta()
  await loadShortcuts()
})

watch(() => props.sellableOnly, value => {
  catalogSellableOnly.value = value
})
</script>

<template>
  <div class="pos-product-picker">
    <VTextField
      v-model="search"
      :placeholder="searchPlaceholder"
      prepend-inner-icon="ri-search-line"
      clearable
      :autofocus="autofocus"
      :density="compact ? 'comfortable' : 'compact'"
      :variant="compact ? 'solo-filled' : 'outlined'"
      hide-details
      class="mb-3 pos-product-picker__search"
    />

    <div
      v-if="showFavorites && !search && favoriteList.length"
      class="mb-3"
    >
      <div class="text-caption text-medium-emphasis mb-2">
        Favoritos
      </div>
      <div class="d-flex flex-wrap gap-2">
        <VChip
          v-for="product in favoriteList"
          :key="`f-${product.id}`"
          :color="Number(selectedProductId) === product.id ? 'primary' : undefined"
          variant="tonal"
          size="small"
          prepend-icon="ri-star-fill"
          @click="onFavoriteChip(product)"
        >
          {{ product.name }}
        </VChip>
      </div>
    </div>

    <div
      v-if="showFavorites && !search && recentList.length"
      class="mb-3"
    >
      <div class="text-caption text-medium-emphasis mb-2">
        Recientes
      </div>
      <div class="d-flex flex-wrap gap-2">
        <VChip
          v-for="product in recentList"
          :key="`r-${product.id}`"
          :color="Number(selectedProductId) === product.id ? 'primary' : undefined"
          variant="outlined"
          size="small"
          @click="onRecentChip(product)"
        >
          {{ product.name }}
        </VChip>
      </div>
    </div>

    <div
      v-if="categoryChips.length && !search"
      class="mb-3 pos-product-picker__chips"
    >
      <VChip
        v-for="chip in categoryChips"
        :key="chip.id ?? 'uncategorized'"
        :color="categoryId === chip.id ? 'primary' : undefined"
        :variant="categoryId === chip.id ? 'flat' : 'tonal'"
        class="me-2 mb-2"
        size="small"
        @click="onCategoryClick(chip)"
      >
        {{ chip.name }}
        <span
          v-if="chip.count"
          class="ms-1 text-caption"
        >({{ chip.count }})</span>
      </VChip>
    </div>

    <VProgressLinear
      v-if="loading || shortcutsLoading"
      indeterminate
      class="mb-3"
    />

    <VAlert
      v-else-if="!showResults"
      type="info"
      variant="tonal"
      :density="compact ? 'compact' : 'default'"
      class="mb-3"
    >
      Escribe al menos 2 letras, elige una categoría o usa favoritos/recientes para ver productos.
    </VAlert>

    <VAlert
      v-else-if="!products.length"
      type="info"
      variant="tonal"
      class="mb-3"
    >
      No hay productos que coincidan.
      <div
        v-if="canCreateProduct"
        class="mt-2"
      >
        <VBtn
          size="small"
          color="primary"
          variant="tonal"
          prepend-icon="ri-add-line"
          @click="emit('create-product')"
        >
          Crear producto ahora
        </VBtn>
      </div>
    </VAlert>

    <VRow
      v-else-if="layout === 'grid' && showResults"
      class="mb-2"
    >
      <VCol
        v-for="product in products"
        :key="product.id"
        :cols="compact ? 12 : 6"
        :md="compact ? 12 : 6"
        :lg="4"
      >
        <VCard
          variant="outlined"
          :class="{ 'pos-product-picker__card--selected': Number(selectedProductId) === product.id }"
          class="pos-product-picker__card"
        >
          <VCardText :class="compact ? 'pb-2' : 'pa-2 pb-2'">
            <div class="d-flex align-start justify-space-between gap-2">
              <div class="flex-grow-1 min-w-0">
                <div :class="compact ? 'text-h6 text-truncate' : 'font-weight-medium text-body-2 text-truncate'">
                  {{ product.name }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ productCategoryLabel(product, categoryMap) }}
                </div>
                <VChip
                  v-if="product.requires_allocation"
                  size="x-small"
                  color="secondary"
                  variant="tonal"
                  class="mt-1"
                >
                  Combo {{ product.bracelet_units_per_line }} manillas
                </VChip>
              </div>
              <VBtn
                v-if="showFavorites"
                icon
                variant="text"
                size="small"
                @click="onToggleFavorite(product)"
              >
                <VIcon
                  :icon="isFavorite(product.id) ? 'ri-star-fill' : 'ri-star-line'"
                  :color="isFavorite(product.id) ? 'warning' : undefined"
                />
              </VBtn>
            </div>

            <div
              v-if="!product.requires_allocation"
              :class="compact ? 'text-body-2 mt-3' : 'text-caption mb-2 mt-2'"
            >
              <div class="d-flex justify-space-between">
                <span class="text-medium-emphasis">Solo</span>
                <span class="font-weight-medium">
                  {{ priceLabel(product, 'SOLO_CLIENTE') || 'Sin precio' }}
                </span>
              </div>
              <div class="d-flex justify-space-between mt-1">
                <span class="text-medium-emphasis">Con acompañante</span>
                <span class="font-weight-medium">
                  {{ priceLabel(product, 'CON_ACOMPANANTE') || 'Sin precio' }}
                </span>
              </div>
            </div>
            <div
              v-else
              :class="compact ? 'text-body-2 mt-3' : 'text-caption mb-2 mt-2'"
            >
              <div class="d-flex justify-space-between">
                <span class="text-medium-emphasis">Precio combo</span>
                <span class="font-weight-medium">
                  {{ priceLabel(product, 'CON_ACOMPANANTE') || 'Sin precio' }}
                </span>
              </div>
            </div>

            <div
              v-if="product.requires_allocation"
              class="mt-2"
            >
              <VBtn
                class="w-100"
                :size="compact ? 'large' : 'small'"
                color="primary"
                :disabled="!priceFor(product, 'CON_ACOMPANANTE')"
                @click="onPickCombo(product)"
              >
                Agregar combo
              </VBtn>
            </div>
            <div
              v-else
              class="d-flex gap-2 mt-2"
            >
              <VBtn
                class="flex-grow-1"
                :size="compact ? 'large' : 'small'"
                :color="isModeSelected(product.id, 'SOLO_CLIENTE') ? 'primary' : 'tonal'"
                :disabled="!priceFor(product, 'SOLO_CLIENTE')"
                @click="onPickMode(product, 'SOLO_CLIENTE')"
              >
                Solo
              </VBtn>
              <VBtn
                class="flex-grow-1"
                :size="compact ? 'large' : 'small'"
                :color="isModeSelected(product.id, 'CON_ACOMPANANTE') ? 'primary' : 'tonal'"
                :disabled="!priceFor(product, 'CON_ACOMPANANTE')"
                @click="onPickMode(product, 'CON_ACOMPANANTE')"
              >
                {{ compact ? 'Con acompañante' : '+Acomp.' }}
              </VBtn>
            </div>

            <VBtn
              v-if="!product.requires_allocation && canConfigurePrice && (!priceFor(product, 'SOLO_CLIENTE') || !priceFor(product, 'CON_ACOMPANANTE'))"
              size="small"
              variant="text"
              class="mt-1 px-0"
              @click="emit('configure-price', { product, saleMode: 'SOLO_CLIENTE' })"
            >
              Configurar precio
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VList
      v-else-if="layout === 'list' && showResults"
      class="pos-product-picker__list mb-2"
    >
      <VListItem
        v-for="product in products"
        :key="product.id"
        :active="Number(selectedProductId) === product.id"
        @click="product.requires_allocation ? onPickCombo(product) : onPickProduct(product)"
      >
        <VListItemTitle>
          {{ product.name }}
          <VChip
            v-if="product.requires_allocation"
            size="x-small"
            color="secondary"
            variant="tonal"
            class="ms-2"
          >
            Combo {{ product.bracelet_units_per_line }} manillas
          </VChip>
        </VListItemTitle>
        <VListItemSubtitle>
          {{ productCategoryLabel(product, categoryMap) }}
          <span
            v-if="product.requires_allocation && priceLabel(product, 'CON_ACOMPANANTE')"
            class="ms-1"
          >· {{ priceLabel(product, 'CON_ACOMPANANTE') }}</span>
          <span
            v-else-if="priceLabel(product, 'SOLO_CLIENTE')"
            class="ms-1"
          >· {{ priceLabel(product, 'SOLO_CLIENTE') }}</span>
        </VListItemSubtitle>
        <template
          v-if="showFavorites"
          #append
        >
          <VBtn
            icon
            variant="text"
            size="small"
            @click.stop="onToggleFavorite(product)"
          >
            <VIcon
              :icon="isFavorite(product.id) ? 'ri-star-fill' : 'ri-star-line'"
              :color="isFavorite(product.id) ? 'warning' : undefined"
            />
          </VBtn>
        </template>
      </VListItem>
      <template v-if="canCreateProduct">
        <VDivider class="my-2" />
        <VListItem
          prepend-icon="ri-add-line"
          title="Crear producto nuevo"
          class="text-primary"
          @click="emit('create-product')"
        />
      </template>
    </VList>

    <div
      v-if="showResults && meta.has_more"
      class="text-caption text-medium-emphasis text-center mt-2"
    >
      Mostrando {{ meta.result_count }} de {{ meta.matched_count }} resultados. Refina la búsqueda o categoría.
    </div>
  </div>
</template>

<style scoped>
.pos-product-picker__search :deep(.v-field) {
  font-size: 1.05rem;
}

.pos-product-picker__chips {
  overflow-x: auto;
  white-space: nowrap;
  -webkit-overflow-scrolling: touch;
}

.pos-product-picker__list {
  max-block-size: 40vh;
  overflow-y: auto;
}

.pos-product-picker__card {
  transition: border-color 0.15s ease;
}

.pos-product-picker__card--selected {
  border-color: rgb(var(--v-theme-primary));
  border-width: 2px;
}
</style>
