<script setup>
const year = new Date().getFullYear()
</script>

<template>
  <footer class="nightpos-footer w-100">
    <div class="nightpos-footer__inner d-flex flex-wrap align-center justify-space-between gap-x-6 gap-y-2">
      <div class="d-flex align-center gap-2">
        <VIcon
          icon="ri-building-2-line"
          size="20"
          class="text-primary"
        />
        <div>
          <div class="text-body-1 font-weight-medium">
            Ribersoft
          </div>
          <div class="text-caption text-medium-emphasis">
            NightPOS — Sistemas para boliches
          </div>
        </div>
      </div>

      <div class="d-flex flex-wrap align-center gap-x-4 gap-y-1 text-body-2">
        <span class="d-inline-flex align-center gap-1 text-medium-emphasis">
          <VIcon
            icon="ri-user-line"
            size="16"
          />
          Ing. Daniel Méndez
        </span>
        <a
          href="tel:+59167369293"
          class="d-inline-flex align-center gap-1 text-primary text-decoration-none"
        >
          <VIcon
            icon="ri-phone-line"
            size="16"
          />
          6736 9293
        </a>
      </div>

      <div class="text-caption text-medium-emphasis">
        © {{ year }} Ribersoft. Todos los derechos reservados.
      </div>
    </div>
  </footer>
</template>

<style scoped lang="scss">
.nightpos-footer {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding-block: 0.75rem;
  padding-inline: 0.25rem;
}

.nightpos-footer__inner {
  min-height: 2.5rem;
}

@media (max-width: 600px) {
  .nightpos-footer__inner {
    flex-direction: column;
    align-items: flex-start !important;
  }
}
</style>
