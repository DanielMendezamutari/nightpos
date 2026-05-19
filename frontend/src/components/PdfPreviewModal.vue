<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  loading: { type: Boolean, default: false },
  /** Blob URL para iframe */
  src: { type: String, default: null },
  iframeTitle: { type: String, default: 'Vista previa PDF' },
  /** Muestra botón Descargar (la acción la dispara el padre) */
  showDownload: { type: Boolean, default: true },
})

defineEmits(['close', 'download'])
</script>

<template>
  <div v-if="open" class="pdf-preview-overlay" @click.self="$emit('close')">
    <article class="panel pdf-preview-card" @click.stop>
      <div class="panel-head pdf-preview-head">
        <h3>{{ title }}</h3>
        <div class="pdf-preview-toolbar">
          <button
            v-if="showDownload"
            type="button"
            class="ghost-btn"
            @click="$emit('download')"
          >
            Descargar
          </button>
          <button type="button" class="ghost-btn" @click="$emit('close')">Cerrar</button>
        </div>
      </div>
      <p v-if="loading" class="field-hint pdf-preview-loading">Generando PDF…</p>
      <iframe
        v-else-if="src"
        :src="src"
        class="pdf-preview-iframe"
        :title="iframeTitle"
      />
      <p v-else class="field-hint pdf-preview-loading">Sin contenido.</p>
    </article>
  </div>
</template>

<style scoped>
.pdf-preview-overlay {
  position: fixed;
  inset: 0;
  z-index: 70;
  display: flex;
  align-items: stretch;
  justify-content: center;
  padding: 0.75rem;
  background: rgba(8, 12, 24, 0.72);
  backdrop-filter: blur(4px);
}

.pdf-preview-card {
  width: 100%;
  max-width: min(56rem, 96vw);
  height: min(92vh, 900px);
  max-height: 92vh;
  min-height: 0;
  display: flex;
  flex-direction: column;
  margin: auto;
}

.pdf-preview-head {
  flex-shrink: 0;
}

.pdf-preview-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  align-items: center;
}

.pdf-preview-loading {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
  padding: 2rem;
}

.pdf-preview-iframe {
  flex: 1 1 auto;
  width: 100%;
  min-height: 0;
  border: 0;
  border-radius: 0 0 8px 8px;
  background: #2a2a2a;
}
</style>
