<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const tabs = [
  { value: 'solo', label: 'Solo', icon: 'ri-glass-line', color: 'primary' },
  { value: 'companion', label: 'Con compañía', icon: 'ri-group-line', color: 'secondary' },
  { value: 'combo', label: 'Combos', icon: 'ri-stack-line', color: 'warning' },
  { value: 'other', label: 'Otros', icon: 'ri-more-line', color: 'info' },
]

const select = value => emit('update:modelValue', value)
</script>

<template>
  <div class="waiter-sale-type-tabs mb-4">
    <VRow dense>
      <VCol
        v-for="tab in tabs"
        :key="tab.value"
        cols="6"
      >
        <VBtn
          block
          size="x-large"
          :color="modelValue === tab.value ? tab.color : undefined"
          :variant="modelValue === tab.value ? 'flat' : 'tonal'"
          class="waiter-sale-type-tabs__btn"
          @click="select(tab.value)"
        >
          <VIcon
            :icon="tab.icon"
            start
          />
          {{ tab.label }}
        </VBtn>
      </VCol>
    </VRow>
    <VBtn
      block
      size="large"
      variant="outlined"
      class="waiter-sale-type-tabs__fallback mt-2"
      :color="modelValue === 'all' ? 'primary' : undefined"
      @click="select('all')"
    >
      <VIcon
        icon="ri-list-check-2"
        start
      />
      Ver todos
    </VBtn>
  </div>
</template>

<style scoped>
.waiter-sale-type-tabs__btn {
  min-block-size: 3.5rem;
  font-size: 0.95rem;
  font-weight: 600;
}
</style>
