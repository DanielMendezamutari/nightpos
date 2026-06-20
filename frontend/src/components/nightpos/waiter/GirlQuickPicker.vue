<script setup>
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  girls: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'select', 'cancel'])

const search = ref('')
const searchInputRef = ref(null)

const normalizedGirls = computed(() =>
  props.girls.map(g => ({
    id: g.id ?? g.value,
    name: g.name ?? g.title ?? '',
  })).filter(g => g.id && g.name),
)

const filteredGirls = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q)
    return normalizedGirls.value

  return normalizedGirls.value.filter(g => g.name.toLowerCase().includes(q))
})

watch(() => props.modelValue, open => {
  if (open) {
    search.value = ''
    nextTick(() => searchInputRef.value?.focus?.())
  }
})

const close = () => emit('update:modelValue', false)

const cancel = () => {
  emit('cancel')
  close()
}

const selectGirl = girl => {
  emit('select', girl)
  close()
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    fullscreen
    transition="dialog-bottom-transition"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="girl-quick-picker">
      <VToolbar
        color="secondary"
        density="comfortable"
      >
        <VBtn
          icon
          @click="cancel"
        >
          <VIcon icon="ri-close-line" />
        </VBtn>
        <VToolbarTitle>Elegir chica</VToolbarTitle>
      </VToolbar>

      <VCardText class="pt-4 pb-6">
        <VTextField
          ref="searchInputRef"
          v-model="search"
          placeholder="Buscar chica…"
          prepend-inner-icon="ri-search-line"
          clearable
          autofocus
          hide-details
          density="comfortable"
          variant="solo-filled"
          class="mb-4 girl-quick-picker__search"
          @click:clear="search = ''"
        />

        <VProgressLinear
          v-if="loading"
          indeterminate
          class="mb-4"
        />

        <VAlert
          v-else-if="!normalizedGirls.length"
          type="info"
          variant="tonal"
          class="mb-4"
        >
          No hay chicas disponibles.
        </VAlert>

        <VAlert
          v-else-if="!filteredGirls.length"
          type="info"
          variant="tonal"
          class="mb-4"
        >
          Ninguna chica coincide con «{{ search }}».
        </VAlert>

        <VRow v-else>
          <VCol
            v-for="girl in filteredGirls"
            :key="girl.id"
            cols="12"
            sm="6"
          >
            <VCard
              variant="outlined"
              class="girl-quick-picker__card"
              @click="selectGirl(girl)"
            >
              <VCardText class="d-flex align-center gap-3 py-4">
                <VAvatar
                  color="secondary"
                  variant="tonal"
                  size="48"
                >
                  <VIcon icon="ri-user-heart-line" />
                </VAvatar>
                <div class="text-h6 font-weight-medium">
                  {{ girl.name }}
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

        <VBtn
          block
          size="large"
          variant="text"
          class="mt-4"
          @click="cancel"
        >
          Cancelar
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.girl-quick-picker__search :deep(.v-field) {
  font-size: 1.05rem;
}

.girl-quick-picker__card {
  cursor: pointer;
  transition: border-color 0.15s ease, transform 0.1s ease;
}

.girl-quick-picker__card:active {
  transform: scale(0.98);
}

.girl-quick-picker__card:hover {
  border-color: rgb(var(--v-theme-secondary));
}
</style>
