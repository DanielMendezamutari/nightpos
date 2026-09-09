<template>
  <div class="h-100 d-flex align-center justify-space-between text-medium-emphasis flex-wrap gap-2">
    <!-- Footer: left content -->
    <div class="d-flex align-center text-body-2">
      &copy; {{ new Date().getFullYear() }}
      <strong class="text-primary ms-1">Ribersoft</strong>
      <span class="mx-1">•</span>
      <span>RiberResto POS Gastronómico</span>
    </div>

    <!-- Footer: right marketing & contact -->
    <div class="d-flex align-center gap-x-4">
      <a
        href="https://wa.me/59167369293"
        target="_blank"
        rel="noopener noreferrer"
        class="text-decoration-none"
      >
        <VChip
          color="success"
          size="small"
          variant="tonal"
          prepend-icon="ri-whatsapp-line"
          class="cursor-pointer font-weight-medium"
        >
          Soporte & Ventas: Cel. 67369293
        </VChip>
      </a>

      <span class="text-caption text-disabled d-none d-md-inline">
        Desarrollado en Bolivia por Ribersoft
      </span>
    </div>
  </div>
</template>