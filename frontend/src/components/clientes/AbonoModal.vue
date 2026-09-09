<script setup>
import { ref, computed, watch } from 'vue'
import { useClientesStore } from '@/stores/clientes'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  cliente: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'abono-registrado'])

const clientesStore = useClientesStore()

const montoStr = ref('')
const metodoPago = ref('EFECTIVO')
const referencia = ref('')
const processing = ref(false)
const errorMessage = ref('')

const deudaActual = computed(() => {
  return parseFloat(props.cliente?.saldo_deuda) || 0
})

const montoNum = computed(() => {
  const val = parseFloat(montoStr.value)
  return isNaN(val) ? 0 : val
})

const nuevoSaldo = computed(() => {
  return Math.max(0, deudaActual.value - montoNum.value)
})

const isValid = computed(() => {
  return montoNum.value > 0 && montoNum.value <= (deudaActual.value + 0.01)
})

watch(() => props.modelValue, (val) => {
  if (val) {
    montoStr.value = ''
    metodoPago.value = 'EFECTIVO'
    referencia.value = ''
    errorMessage.value = ''
  }
})

const setMontoTotal = () => {
  montoStr.value = deudaActual.value.toFixed(2)
}

const addQuick = (val) => {
  montoStr.value = Math.min(deudaActual.value, val).toFixed(2)
}

const appendKeypad = (char) => {
  if (char === 'C') {
    montoStr.value = ''
    return
  }
  if (char === '.' && montoStr.value.includes('.')) return
  montoStr.value += char
}

const close = () => {
  emit('update:modelValue', false)
}

const submitAbono = async () => {
  if (!props.cliente?.id || !isValid.value) return

  processing.value = true
  errorMessage.value = ''

  const res = await clientesStore.registrarAbono(props.cliente.id, {
    monto: montoNum.value,
    metodo_pago: metodoPago.value,
    referencia: referencia.value || 'Abono en Caja',
  })

  processing.value = false

  if (res.success) {
    emit('update:modelValue', false)
    emit('abono-registrado', res.data)
  } else {
    errorMessage.value = res.message || 'Error al registrar abono'
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="550"
    persistent
  >
    <VCard class="abono-modal-card">
      <VCardItem class="bg-success text-white py-3 px-4">
        <div class="d-flex align-center justify-space-between w-100">
          <div class="d-flex align-center gap-2">
            <VIcon icon="ri-hand-coin-line" size="26" />
            <div>
              <span class="text-h6 font-weight-black text-uppercase">
                Abono a Cuenta Corriente
              </span>
              <div class="text-caption text-white opacity-80">
                Cliente: {{ cliente?.nombre_completo || cliente?.nombre }}
              </div>
            </div>
          </div>
          <VBtn
            icon="ri-close-line"
            variant="text"
            color="white"
            density="comfortable"
            @click="close"
          />
        </div>
      </VCardItem>

      <VCardText class="pa-4 bg-surface">
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-3"
          closable
          @click:close="errorMessage = ''"
        >
          {{ errorMessage }}
        </VAlert>

        <!-- Estado de Deuda y Nuevo Saldo -->
        <VRow class="mb-3">
          <VCol cols="6">
            <VCard variant="tonal" color="error" class="text-center pa-2">
              <div class="text-caption font-weight-bold">DEUDA ACTUAL:</div>
              <div class="text-h5 font-weight-black text-error">
                Bs. {{ deudaActual.toFixed(2) }}
              </div>
            </VCard>
          </VCol>
          <VCol cols="6">
            <VCard variant="tonal" color="success" class="text-center pa-2">
              <div class="text-caption font-weight-bold">NUEVO SALDO RESTANTE:</div>
              <div class="text-h5 font-weight-black text-success">
                Bs. {{ nuevoSaldo.toFixed(2) }}
              </div>
            </VCard>
          </VCol>
        </VRow>

        <!-- Atajos de Monto -->
        <div class="d-flex flex-wrap gap-2 mb-3">
          <VBtn size="small" variant="tonal" color="primary" class="font-weight-bold" @click="setMontoTotal">
            Pagar Totalidad (Bs. {{ deudaActual.toFixed(2) }})
          </VBtn>
          <VBtn size="small" variant="tonal" @click="addQuick(50)">50 Bs</VBtn>
          <VBtn size="small" variant="tonal" @click="addQuick(100)">100 Bs</VBtn>
          <VBtn size="small" variant="tonal" @click="addQuick(200)">200 Bs</VBtn>
          <VBtn size="small" variant="tonal" @click="addQuick(500)">500 Bs</VBtn>
        </div>

        <!-- Input Monto -->
        <VTextField
          v-model="montoStr"
          label="Monto a Abonar (Bs.)"
          prefix="Bs."
          variant="outlined"
          density="comfortable"
          type="number"
          class="mb-3 font-weight-bold text-h6"
          autofocus
        />

        <!-- Teclado TÃ¡ctil -->
        <div class="d-grid grid-cols-4 gap-2 mb-3">
          <VBtn
            v-for="k in ['7','8','9','C','4','5','6','00','1','2','3','0','.','âŒ«']"
            :key="k"
            variant="tonal"
            size="small"
            class="text-subtitle-1 font-weight-bold py-1"
            :color="k === 'C' ? 'error' : (k === 'âŒ«' ? 'warning' : 'default')"
            @click="k === 'âŒ«' ? (montoStr = montoStr.slice(0, -1)) : appendKeypad(k)"
          >
            {{ k }}
          </VBtn>
        </div>

        <!-- MÃ©todo de Pago del Abono -->
        <VRadioGroup v-model="metodoPago" inline density="compact" class="mb-2">
          <VRadio label="Efectivo" value="EFECTIVO" color="success" />
          <VRadio label="Tarjeta POS" value="TARJETA" color="warning" />
          <VRadio label="QR Bancario" value="QR" color="info" />
        </VRadioGroup>

        <!-- Referencia / Observaciones -->
        <VTextField
          v-model="referencia"
          label="Comprobante / NÂ° DepÃ³sito / Nota"
          variant="outlined"
          density="compact"
          placeholder="Ej: DepÃ³sito BCP #998811"
        />
      </VCardText>

      <VCardActions class="pa-4 bg-surface-variant d-flex justify-space-between align-center">
        <VBtn
          variant="outlined"
          color="secondary"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VBtn
          color="success"
          variant="flat"
          size="large"
          class="px-6 font-weight-black"
          :loading="processing"
          :disabled="!isValid"
          @click="submitAbono"
        >
          REGISTRAR ABONO EN CAJA
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>