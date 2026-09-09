<script setup>
import { ref, computed } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
  proveedor: Object,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const monto = ref(0)
const metodoPago = ref('EFECTIVO')
const referencia = ref('')

const nuevoSaldo = computed(() => {
  const deudaActual = Number(props.proveedor?.saldo_deuda || 0)
  return Math.max(0, deudaActual - Number(monto.value || 0))
})

const registrarPago = async () => {
  if (!props.proveedor || monto.value <= 0) return

  await store.registrarPagoProveedor(props.proveedor.id, {
    monto: Number(monto.value),
    metodo_pago: metodoPago.value,
    referencia: referencia.value,
  })

  emit('update:modelValue', false)
  emit('guardado')
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="450px"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <span class="text-h6 font-weight-bold">
          <VIcon icon="ri-hand-coin-line" class="me-2" />
          Pago a Proveedor
        </span>
        <VBtn icon="ri-close-line" variant="text" color="white" density="comfortable" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VCardText class="pa-4">
        <VAlert color="info" variant="tonal" class="mb-4">
          <div class="text-subtitle-2 font-weight-bold">{{ proveedor?.nombre }}</div>
          <div>Deuda Actual: <span class="font-weight-bold text-error">Bs. {{ Number(proveedor?.saldo_deuda || 0).toFixed(2) }}</span></div>
        </VAlert>

        <VTextField
          v-model.number="monto"
          type="number"
          label="Monto a Pagar (Bs) *"
          density="compact"
          variant="outlined"
          class="mb-3"
          step="0.01"
        />

        <VSelect
          v-model="metodoPago"
          :items="['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE']"
          label="MÃ©todo de Pago"
          density="compact"
          variant="outlined"
          class="mb-3"
        />

        <VTextField
          v-model="referencia"
          label="Referencia / Nro. Comprobante"
          placeholder="Transf. BNB #..."
          density="compact"
          variant="outlined"
          class="mb-3"
        />

        <div class="d-flex justify-space-between text-subtitle-2 pt-2 border-t">
          <span>Nuevo Saldo Deudor:</span>
          <span class="font-weight-bold text-primary">Bs. {{ nuevoSaldo.toFixed(2) }}</span>
        </div>
      </VCardText>

      <VCardActions class="pa-4 pt-0">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">Cancelar</VBtn>
        <VBtn color="primary" variant="elevated" @click="registrarPago" :disabled="monto <= 0">
          <VIcon icon="ri-check-line" class="me-1" />
          Confirmar Pago
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>