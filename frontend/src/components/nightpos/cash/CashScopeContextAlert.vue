<script setup>
const props = defineProps({
  scope: {
    type: Object,
    required: true,
  },
})

const knownWarnings = new Set([
  'La caja incluye actividad de multiples official_shift_id.',
  'Existen multiples turnos OPEN en la sucursal.',
])

const contextualWarnings = computed(() =>
  (props.scope?.warnings ?? []).filter(warning => warning && !knownWarnings.has(warning)),
)
</script>

<template>
  <VCard>
    <VCardTitle>Contexto caja/turno</VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Numero de caja</div>
          <div class="text-h6">#{{ scope.cash_session_id ?? '—' }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Turno de apertura</div>
          <div class="text-h6">{{ scope.session_official_shift_id ?? '—' }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Turno operativo actual</div>
          <div class="text-h6">{{ scope.current_official_shift_id ?? '—' }}</div>
        </VCol>
      </VRow>

      <VRow class="mt-1">
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Turnos incluidos</div>
          <div class="text-body-1">{{ (scope.official_shift_ids_included ?? []).join(', ') || '—' }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Cruza multiples turnos</div>
          <div class="text-body-1">{{ scope.crosses_multiple_shifts ? 'Si' : 'No' }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Cajas historicas abiertas</div>
          <div class="text-body-1">{{ (scope.open_cash_sessions_on_historical_shifts ?? []).length }}</div>
        </VCol>
      </VRow>

      <VAlert
        v-if="scope.crosses_multiple_shifts"
        type="warning"
        variant="tonal"
        class="mt-4"
      >
        Advertencia: esta caja cruza multiples turnos.
      </VAlert>

      <VAlert
        v-if="scope.has_open_shift_conflict"
        type="error"
        variant="tonal"
        class="mt-2"
      >
        Alerta: se detecto conflicto por turnos duplicados abiertos.
      </VAlert>

      <VAlert
        v-for="warning in contextualWarnings"
        :key="warning"
        type="warning"
        variant="tonal"
        density="compact"
        class="mt-2"
      >
        {{ warning }}
      </VAlert>
    </VCardText>
  </VCard>
</template>
