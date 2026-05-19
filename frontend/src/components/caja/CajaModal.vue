<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  /** 'md' | 'lg' | 'xl' */
  size: { type: String, default: 'md' },
})

defineEmits(['close'])
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="caja-modal-overlay" @click.self="$emit('close')">
      <div class="caja-modal" :class="`caja-modal--${size}`" role="dialog" aria-modal="true" @click.stop>
        <header class="caja-modal-head">
          <h3>{{ title }}</h3>
          <button type="button" class="ghost-btn" @click="$emit('close')">Cerrar</button>
        </header>
        <div class="caja-modal-body">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.caja-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 80;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem;
  background: rgba(8, 12, 24, 0.75);
  backdrop-filter: blur(5px);
}

.caja-modal {
  width: 100%;
  max-height: min(92vh, 880px);
  display: flex;
  flex-direction: column;
  background: var(--panel-bg, #141c38);
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.25));
  border-radius: 14px;
  box-shadow: 0 18px 48px rgba(0, 0, 0, 0.45);
}

.caja-modal--md {
  max-width: min(28rem, 100%);
}

.caja-modal--lg {
  max-width: min(40rem, 100%);
}

.caja-modal--xl {
  max-width: min(52rem, 100%);
}

.caja-modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.2));
  flex-shrink: 0;
}

.caja-modal-head h3 {
  margin: 0;
  font-size: 1.05rem;
}

.caja-modal-body {
  padding: 1rem;
  overflow-y: auto;
  min-height: 0;
}
</style>
