<script setup>
import WaiterTableTile from '@/components/nightpos/waiter/WaiterTableTile.vue'

defineProps({
  groups: { type: Array, default: () => [] },
  openingId: { type: Number, default: null },
})

const emit = defineEmits(['tap'])

const onTap = table => emit('tap', table)
</script>

<template>
  <div class="waiter-tables-grid">
    <section
      v-for="group in groups"
      :key="group.area"
      class="waiter-tables-grid__section"
    >
      <h2 class="waiter-tables-grid__area">
        {{ group.area }}
      </h2>
      <div class="waiter-tables-grid__tiles">
        <WaiterTableTile
          v-for="table in group.items"
          :key="table.id"
          :table="table"
          :busy="openingId === table.id"
          @tap="onTap"
        />
      </div>
    </section>
  </div>
</template>

<style scoped lang="scss">
.waiter-tables-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;

  &__area {
    margin: 0 0 10px;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(var(--v-theme-on-surface), 0.55);
  }

  &__tiles {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
}

@media (min-width: 480px) {
  .waiter-tables-grid__tiles {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
