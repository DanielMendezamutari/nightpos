<script setup>
import { ref, reactive, watch } from 'vue'
import { useCajaStore } from '@/stores/caja'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'saved'])

const cajaStore = useCajaStore()

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = reactive({
  tipo_gasto_id: null,
  monto: null,
  beneficiario: '',
  forma_pago: 'EFECTIVO',
  comprobante_nro: '',
  observaciones: '',
})

watch(
  () => props.modelValue,
  async (isOpen) => {
    if (isOpen) {
      errorMessage.value = ''
      successMessage.value = ''
      resetForm()
      await cajaStore.fetchTiposGastos()
      if (cajaStore.tiposGastos.length > 0 && !form.tipo_gasto_id) {
        form.tipo_gasto_id = cajaStore.tiposGastos[0].id
      }
    }
  }
)

const resetForm = () => {
  form.monto = null
  form.beneficiario = ''
  form.forma_pago = 'EFECTIVO'
  form.comprobante_nro = ''
  form.observaciones = ''
}

const closeModal = () => {
  emit('update:modelValue', false)
}

const guardarGasto = async () => {
  if (!form.tipo_gasto_id || !form.monto || form.monto <= 0 || !form.beneficiario.trim()) {
    errorMessage.value = 'Por favor completa la categoría, beneficiario y monto válido.'
    return
  }

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await cajaStore.registrarGasto({
      tipo_gasto_id: form.tipo_gasto_id,
      monto: parseFloat(form.monto),
      beneficiario: form.beneficiario.trim(),
      forma_pago: form.forma_pago,
      comprobante_nro: form.comprobante_nro?.trim() || null,
      observaciones: form.observaciones?.trim() || null,
    })

    if (res.success) {
      successMessage.value = 'Egreso registrado correctamente'
      emit('saved', res.data)
      setTimeout(() => {
        closeModal()
      }, 700)
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message || 'Error registrando egreso'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="580"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="rounded-lg elevation-4">
      <!-- CABECERA -->
      <VCardTitle class="d-flex align-center justify-space-between bg-error text-white pa-4">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-hand-coin-line" size="24" />
          <span class="text-h6 font-weight-bold">Registrar Egreso / Gasto de Caja Chica</span>
        </div>
        <VBtn icon variant="text" color="white" @click="closeModal">
          <VIcon icon="ri-close-line" />
        </VBtn>
      </VCardTitle>

      <!-- CUERPO -->
      <VCardText class="pa-4 pt-5">
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-4 font-weight-medium"
        >
          {{ errorMessage }}
        </VAlert>

        <VAlert
          v-if="successMessage"
          type="success"
          variant="tonal"
          density="compact"
          class="mb-4 font-weight-medium"
        >
          {{ successMessage }}
        </VAlert>

        <VRow dense>
          <!-- Categoría de Gasto -->
          <VCol cols="12">
            <VLabel class="mb-1 font-weight-bold">Categoría de Gasto (RestoTech Tipos)</VLabel>
            <VSelect
              v-model="form.tipo_gasto_id"
              :items="cajaStore.tiposGastos"
              item-title="nombre"
              item-value="id"
              density="comfortable"
              prepend-inner-icon="ri-price-tag-3-line"
              placeholder="Seleccionar tipo de gasto..."
            />
          </VCol>

          <!-- Monto y Método -->
          <VCol cols="12" sm="6">
            <VLabel class="mb-1 font-weight-bold">Monto del Egreso (Bs)</VLabel>
            <VTextField
              v-model.number="form.monto"
              type="number"
              min="0.50"
              step="0.50"
              placeholder="0.00"
              prefix="Bs."
              density="comfortable"
              autofocus
            />
          </VCol>

          <VCol cols="12" sm="6">
            <VLabel class="mb-1 font-weight-bold">Origen del Fondo</VLabel>
            <VSelect
              v-model="form.forma_pago"
              :items="[
                { title: '💵 Efectivo de Caja', value: 'EFECTIVO' },
                { title: '📱 QR Banco / Cuenta', value: 'QR' },
                { title: '🏦 Transferencia Externa', value: 'TRANSFERENCIA' },
              ]"
              item-title="title"
              item-value="value"
              density="comfortable"
            />
          </VCol>

          <!-- Beneficiario -->
          <VCol cols="12" sm="7">
            <VLabel class="mb-1 font-weight-bold">Pagado a / Beneficiario</VLabel>
            <VTextField
              v-model="form.beneficiario"
              placeholder="Ej: Distribuidora de Hielo / Chofer Taxi"
              density="comfortable"
              prepend-inner-icon="ri-user-follow-line"
            />
          </VCol>

          <!-- Comprobante / Recibo -->
          <VCol cols="12" sm="5">
            <VLabel class="mb-1 font-weight-bold">Nro. Recibo / Factura</VLabel>
            <VTextField
              v-model="form.comprobante_nro"
              placeholder="Ej: REC-1024"
              density="comfortable"
              prepend-inner-icon="ri-file-list-3-line"
            />
          </VCol>

          <!-- Observaciones / Motivo -->
          <VCol cols="12">
            <VLabel class="mb-1 font-weight-bold">Motivo / Detalle del Gasto</VLabel>
            <VTextarea
              v-model="form.observaciones"
              rows="2"
              placeholder="Detalle el motivo o justificación del gasto operativo..."
              density="comfortable"
            />
          </VCol>
        </VRow>

        <div v-if="form.forma_pago === 'EFECTIVO'" class="mt-3 pa-2 bg-error-lighten-5 rounded border border-error text-caption text-error font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-error-warning-line" size="18" />
          <span>Este gasto se restará automáticamente del efectivo físico esperado al momento del cierre de caja.</span>
        </div>
      </VCardText>

      <!-- PIE -->
      <VDivider />
      <VCardActions class="pa-4 bg-surface-variant d-flex justify-end gap-2">
        <VBtn variant="tonal" color="default" @click="closeModal">
          Cancelar
        </VBtn>
        <VBtn
          color="error"
          variant="elevated"
          class="font-weight-bold px-6"
          :loading="loading"
          @click="guardarGasto"
        >
          <VIcon icon="ri-check-line" start />
          Registrar Egreso
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
