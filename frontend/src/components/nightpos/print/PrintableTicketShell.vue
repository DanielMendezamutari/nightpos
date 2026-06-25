<script setup>
defineProps({
  title: {
    type: String,
    default: 'NightPOS',
  },
  subtitle: {
    type: String,
    default: '',
  },
  width: {
    type: String,
    default: '80mm',
    validator: v => ['58mm', '80mm', 'a4'].includes(v),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  footerText: {
    type: String,
    default: '',
  },
})

const router = useRouter()

const goBack = () => {
  if (window.history.length > 1)
    router.back()
  else
    window.close()
}

const print = () => window.print()
</script>

<template>
  <div class="nightpos-print-wrapper">
    <div class="nightpos-print-toolbar no-print">
      <VBtn
        variant="tonal"
        prepend-icon="ri-arrow-left-line"
        @click="goBack"
      >
        Volver
      </VBtn>
      <VBtn
        color="primary"
        prepend-icon="ri-printer-line"
        :disabled="loading"
        @click="print"
      >
        Imprimir
      </VBtn>
    </div>

    <div
      class="nightpos-ticket"
      :class="[`nightpos-ticket--${width === 'a4' ? 'a4' : width.replace('mm', '')}`]"
    >
      <div
        v-if="loading"
        class="nightpos-ticket__loading"
      >
        Cargando…
      </div>

      <template v-else>
        <slot name="banner" />

        <header class="nightpos-ticket__header">
          <div class="nightpos-ticket__brand">
            {{ title }}
          </div>
          <div
            v-if="subtitle"
            class="nightpos-ticket__subtitle"
          >
            {{ subtitle }}
          </div>
        </header>

        <slot />

        <footer class="nightpos-ticket__footer">
          <slot name="footer">
            {{ footerText || 'NightPOS' }}
          </slot>
        </footer>
      </template>
    </div>
  </div>
</template>

<style>
.nightpos-print-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  padding: 16px;
  background: #f4f4f4;
}

.nightpos-print-toolbar {
  display: flex;
  gap: 12px;
  margin-block-end: 16px;
}

.nightpos-ticket {
  box-sizing: border-box;
  padding: 12px;
  background: #fff;
  color: #000;
  font-family: "Courier New", "Consolas", monospace;
  font-size: 12px;
  line-height: 1.35;
}

.nightpos-ticket--58 { inline-size: 58mm; }
.nightpos-ticket--80 { inline-size: 80mm; }
.nightpos-ticket--a4 { inline-size: 210mm; max-inline-size: 100%; font-family: Arial, sans-serif; font-size: 13px; }

.nightpos-ticket__header {
  text-align: center;
  border-block-end: 1px dashed #000;
  padding-block-end: 8px;
  margin-block-end: 8px;
}

.nightpos-ticket__brand {
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
}

.nightpos-ticket__subtitle {
  font-size: 11px;
  margin-block-start: 2px;
}

.nightpos-ticket__footer {
  text-align: center;
  border-block-start: 1px dashed #000;
  padding-block-start: 8px;
  margin-block-start: 8px;
  font-size: 10px;
}

.nightpos-ticket__loading {
  text-align: center;
  padding: 24px 0;
}

.nightpos-print-row {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.nightpos-print-row--strong {
  font-weight: 700;
  font-size: 13px;
}

.nightpos-print-divider {
  border: none;
  border-block-start: 1px dashed #000;
  margin: 8px 0;
}

.nightpos-print-line-table {
  inline-size: 100%;
  border-collapse: collapse;
}

.nightpos-print-line-table th,
.nightpos-print-line-table td {
  text-align: start;
  padding: 2px 0;
  vertical-align: top;
}

.nightpos-print-line-table th:last-child,
.nightpos-print-line-table td:last-child,
.nightpos-print-line-table .text-end {
  text-align: end;
}

.nightpos-print-muted {
  font-size: 10px;
  color: #333;
}

.nightpos-print-banner {
  text-align: center;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  border: 2px solid #000;
  padding: 6px 4px;
  margin-block-end: 8px;
}

.nightpos-print-banner--admin {
  border-width: 3px;
}

.nightpos-print-banner--notes {
  border-style: dashed;
}

.nightpos-print-section {
  margin-block-end: 6px;
}

.nightpos-print-section__title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-block-end: 4px;
  padding-block-end: 2px;
  border-block-end: 1px solid #000;
}

.nightpos-print-section__hint {
  font-size: 10px;
  color: #444;
  margin-block-end: 4px;
}

.nightpos-print-kpi {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
  padding: 4px 0;
}

.nightpos-print-kpi--alert {
  border: 1px solid #000;
  padding: 4px 6px;
  margin-block: 4px;
}

.nightpos-print-list-item {
  display: flex;
  justify-content: space-between;
  gap: 6px;
  font-size: 11px;
  padding: 1px 0;
}

.nightpos-print-list-item__name {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.nightpos-print-list-item__meta {
  flex-shrink: 0;
  text-align: end;
}

@media print {
  @page { margin: 0; }

  body { background: #fff !important; }

  .no-print { display: none !important; }

  .nightpos-print-wrapper {
    padding: 0;
    background: #fff;
    min-height: auto;
  }

  .nightpos-ticket {
    inline-size: auto;
    padding: 0;
  }
}
</style>
