<script setup>
const props = defineProps({
  total: { type: Number, required: true },
  filled: { type: Number, default: 0 },
  complete: { type: Boolean, default: false },
})

const dots = computed(() =>
  Array.from({ length: Math.min(props.total, 24) }, (_, i) => i < props.filled),
)
</script>

<template>
  <div
    class="bracelet-dots"
    :class="{ 'bracelet-dots--complete': complete || filled >= total }"
    role="img"
    :aria-label="`${filled} de ${total} manillas`"
  >
    <span
      v-for="(on, idx) in dots"
      :key="idx"
      class="bracelet-dots__dot"
      :class="{ 'bracelet-dots__dot--filled': on }"
    />
    <span
      v-if="total > 24"
      class="text-caption ms-2"
    >
      +{{ total - 24 }}
    </span>
  </div>
</template>

<style scoped>
.bracelet-dots {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  align-items: center;
}

.bracelet-dots__dot {
  inline-size: 0.85rem;
  block-size: 0.85rem;
  border-radius: 50%;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.25);
  background: transparent;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}

.bracelet-dots__dot--filled {
  border-color: rgb(var(--v-theme-warning));
  background: rgb(var(--v-theme-warning));
}

.bracelet-dots--complete .bracelet-dots__dot--filled {
  border-color: rgb(var(--v-theme-success));
  background: rgb(var(--v-theme-success));
}
</style>
