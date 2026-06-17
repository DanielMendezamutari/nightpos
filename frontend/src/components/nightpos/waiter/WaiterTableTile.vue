<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  table: { type: Object, required: true },
  busy: { type: Boolean, default: false },
})

defineEmits(['tap'])

const isFree = computed(() => props.table.status === 'FREE')
const isOccupied = computed(() => props.table.status === 'OCCUPIED')

const statusLabel = computed(() => (isFree.value ? 'Libre' : 'Ocupada'))
const statusIcon = computed(() => (isFree.value ? 'ri-checkbox-blank-circle-line' : 'ri-restaurant-2-line'))
</script>

<template>
  <button
    type="button"
    class="waiter-table-tile"
    :class="{
      'waiter-table-tile--free': isFree,
      'waiter-table-tile--occupied': isOccupied,
      'waiter-table-tile--busy': busy,
    }"
    :disabled="busy"
    :aria-label="`${table.label}, ${statusLabel}${table.total ? `, ${formatMoney(table.total)}` : ''}`"
    @click="$emit('tap', table)"
  >
    <div
      v-if="busy"
      class="waiter-table-tile__overlay"
    >
      <VProgressCircular
        indeterminate
        size="28"
        width="3"
        color="primary"
      />
    </div>

    <div class="waiter-table-tile__label">
      {{ table.label }}
    </div>

    <div class="waiter-table-tile__status">
      <VIcon
        :icon="statusIcon"
        size="18"
        class="me-1"
      />
      {{ statusLabel }}
    </div>

    <div
      v-if="isOccupied && table.total"
      class="waiter-table-tile__total"
    >
      {{ formatMoney(table.total) }}
    </div>

    <div
      v-else-if="isFree"
      class="waiter-table-tile__hint"
    >
      Tocar para abrir
    </div>
  </button>
</template>

<style scoped lang="scss">
.waiter-table-tile {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  min-height: 112px;
  padding: 16px 12px;
  border-radius: 16px;
  border: 2px solid transparent;
  cursor: pointer;
  text-align: center;
  transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  user-select: none;

  &:active:not(:disabled) {
    transform: scale(0.97);
  }

  &:disabled {
    cursor: wait;
  }

  &--free {
    background: rgba(var(--v-theme-success), 0.08);
    border-color: rgba(var(--v-theme-success), 0.35);
    color: rgb(var(--v-theme-on-surface));
  }

  &--occupied {
    background: rgba(var(--v-theme-primary), 0.12);
    border-color: rgba(var(--v-theme-primary), 0.45);
    color: rgb(var(--v-theme-on-surface));
  }

  &--busy {
    opacity: 0.85;
  }

  &__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--v-theme-surface), 0.72);
    border-radius: 14px;
    z-index: 1;
  }

  &__label {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.2;
  }

  &__status {
    display: inline-flex;
    align-items: center;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  &__total {
    font-size: 1rem;
    font-weight: 700;
    color: rgb(var(--v-theme-primary));
  }

  &__hint {
    font-size: 0.75rem;
    color: rgba(var(--v-theme-on-surface), 0.65);
  }
}
</style>
