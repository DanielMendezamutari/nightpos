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

const emit = defineEmits(['update:modelValue', 'anticipo-registrado'])

const clientesStore = useClientesStore()

const montoStr = ref('')
const concepto = ref('Anticipo de Reserva / Evento')
const processing = ref(false)
const errorMessage = ref('')

const montoNum = computed(() => {
  const val = parseFloat(montoStr.value)
  return isNaN(val) ? 0 : val
})

const isValid = computed(() => {
  return montoNum.value >= 1.00 && concepto.value.trim().length > 0
})

watch(() => props.modelValue, (val) => {
  if (val) {
    montoStr.value = ''
    concepto.value = 'Anticipo de Reserva / Evento'
    errorMessage.value = ''
  }
})

const close = () => {
  emit('update:modelValue', false)
}

const submitAnticipo = async () => {
  if (!props.cliente?.id || !isValid.value) return

  processing.value = true
  errorMessage.value = ''

  const res = await clientesStore.registrarAnticipo(props.cliente.id, {
    monto: montoNum.value,
    concepto: concepto.value,
  })

  processing.value = false

  if (res.success) {
    emit('update:modelValue', false)
    emit('anticipo-registrado', res.data)
  } else {
    errorMessage.value = res.message || 'Error al registrar anticipo'
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="500"
    persistent
  >
    <VCard class="anticipo-modal-card">
      <VCardItem class="bg-info text-white py-3 px-4">
        <div class="d-flex align-center justify-space-between w-100">
          <div class="d-flex align-center gap-2">
            <VIcon icon="ri-calendar-event-line" size="26" />
            <div>
              <span class="text-h6 font-weight-black text-uppercase">
                Anticipo / Seña de Reserva
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

        <div class="text-caption mb-3 text-medium-emphasis">
          El monto ingresado quedará guardado como saldo a favor del cliente y se registrará automáticamente como <strong>Ingreso en la Caja Activa</strong>.
        </div>

        <VTextField
          v-model="montoStr"
          label="Monto del Anticipo (Bs.) *"
          prefix="Bs."
          type="number"
          variant="outlined"
          density="comfortable"
          class="mb-3 font-weight-bold text-h6"
          autofocus
        />

        <VTextarea
          v-model="concepto"
          label="Concepto del Anticipo / Motivo de Reserva *"
          rows="3"
          variant="outlined"
          density="comfortable"
          placeholder="Ej: Seña para cumpleaños 20 personas el sábado 15/09..."
        />
      </VCardText>

      <VCardActions class="pa-4 bg-surface border-t d-flex justify-space-between align-center">
        <VBtn
          variant="outlined"
          color="secondary"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VBtn
          color="info"
          variant="flat"
          class="px-6 font-weight-bold"
          :loading="processing"
          :disabled="!isValid"
          @click="submitAnticipo"
        >
          REGISTRAR ANTICIPO EN CAJA
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>