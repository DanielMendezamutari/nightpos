<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useCajaStore } from '@/stores/caja'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  turno: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'closed'])

const cajaStore = useCajaStore()

const step = ref('CONTEO') // 'CONTEO' | 'RESULTADO'
const loading = ref(false)
const errorMessage = ref('')
const notas = ref('')
const resultadoArqueo = ref(null)

// Conteo de billetes
const billetes = reactive({
  b200: 0,
  b100: 0,
  b50: 0,
  b20: 0,
  b10: 0,
})

// Conteo de monedas
const monedas = reactive({
  m5: 0,
  m2: 0,
  m1: 0,
  m050: 0,
  m020: 0,
  m010: 0,
})

const totalBilletes = computed(() => {
  return (billetes.b200 * 200) +
         (billetes.b100 * 100) +
         (billetes.b50 * 50) +
         (billetes.b20 * 20) +
         (billetes.b10 * 10)
})

const totalMonedas = computed(() => {
  return (monedas.m5 * 5) +
         (monedas.m2 * 2) +
         (monedas.m1 * 1) +
         (monedas.m050 * 0.5) +
         (monedas.m020 * 0.2) +
         (monedas.m010 * 0.1)
})

const totalDeclarado = computed(() => {
  return Math.round((totalBilletes.value + totalMonedas.value) * 100) / 100
})

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      step.value = 'CONTEO'
      errorMessage.value = ''
      notas.value = ''
      resultadoArqueo.value = null
      resetConteo()
    }
  }
)

const resetConteo = () => {
  billetes.b200 = 0
  billetes.b100 = 0
  billetes.b50 = 0
  billetes.b20 = 0
  billetes.b10 = 0

  monedas.m5 = 0
  monedas.m2 = 0
  monedas.m1 = 0
  monedas.m050 = 0
  monedas.m020 = 0
  monedas.m010 = 0
}

const addBill = (denominacion, qty) => {
  billetes[denominacion] = Math.max(0, (billetes[denominacion] || 0) + qty)
}

const addCoin = (denominacion, qty) => {
  monedas[denominacion] = Math.max(0, (monedas[denominacion] || 0) + qty)
}

const closeModal = () => {
  emit('update:modelValue', false)
}

const procesarCierreArqueo = async () => {
  if (!props.turno || !props.turno.id) {
    errorMessage.value = 'No se ha detectado el turno activo para cerrar.'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const payload = {
      turno_id: props.turno.id,
      b200: billetes.b200,
      b100: billetes.b100,
      b50: billetes.b50,
      b20: billetes.b20,
      b10: billetes.b10,
      m5: monedas.m5,
      m2: monedas.m2,
      m1: monedas.m1,
      m050: monedas.m050,
      m020: monedas.m020,
      m010: monedas.m010,
      cerrar_turno: true,
      notas: notas.value?.trim() || null,
    }

    const res = await cajaStore.realizarArqueoCiego(payload)

    if (res.success) {
      resultadoArqueo.value = res.data
      step.value = 'RESULTADO'
      emit('closed', res.data)
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message || 'Error al procesar el arqueo ciego'
  } finally {
    loading.value = false
  }
}

const imprimirCierreZ = () => {
  window.print()
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="820"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="rounded-lg elevation-4">
      <!-- CABECERA -->
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-safe-2-line" size="26" />
          <div>
            <div class="text-h6 font-weight-bold line-height-1">Arqueo Ciego & Cierre de Turno</div>
            <div class="text-caption text-white opacity-90">RestoTech ControlCajaTurnoCiego • Conteo Físico sin Sesgo</div>
          </div>
        </div>
        <VBtn icon variant="text" color="white" @click="closeModal">
          <VIcon icon="ri-close-line" />
        </VBtn>
      </VCardTitle>

      <!-- PASO 1: CONTEO FÍSICO DE BILLETES Y MONEDAS -->
      <VCardText v-if="step === 'CONTEO'" class="pa-4 pt-5">
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-4 font-weight-medium"
        >
          {{ errorMessage }}
        </VAlert>

        <div class="d-flex align-center justify-space-between mb-4 pa-3 bg-surface-variant rounded-lg">
          <div>
            <div class="text-caption text-medium-emphasis">Turno Activo #{{ turno?.id }}</div>
            <div class="text-body-1 font-weight-bold">Cajero: {{ turno?.cajero_nombre || 'Principal' }}</div>
          </div>
          <div class="text-right">
            <div class="text-caption text-medium-emphasis">Total Declarado Físico</div>
            <div class="text-h4 font-weight-black text-primary">Bs. {{ totalDeclarado.toFixed(2) }}</div>
          </div>
        </div>

        <VRow dense>
          <!-- SECCIÓN BILLETES BOLIVIANOS -->
          <VCol cols="12" md="6">
            <VCard variant="outlined" class="pa-3 rounded-lg h-100">
              <div class="d-flex align-center justify-space-between mb-3 border-b pb-2">
                <span class="font-weight-bold text-body-1 d-flex align-center gap-1">
                  <VIcon icon="ri-money-dollar-box-line" color="primary" />
                  Billetes (Total: Bs. {{ totalBilletes.toFixed(2) }})
                </span>
              </div>

              <!-- Fila 200 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="primary" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 200
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addBill('b200', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addBill('b200', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="billetes.b200"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- Fila 100 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="primary" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 100
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addBill('b100', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addBill('b100', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="billetes.b100"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- Fila 50 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="primary" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 50
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addBill('b50', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addBill('b50', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="billetes.b50"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- Fila 20 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="primary" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 20
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addBill('b20', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addBill('b20', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="billetes.b20"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- Fila 10 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="primary" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 10
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addBill('b10', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addBill('b10', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="billetes.b10"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>
            </VCard>
          </VCol>

          <!-- SECCIÓN MONEDAS BOLIVIANAS -->
          <VCol cols="12" md="6">
            <VCard variant="outlined" class="pa-3 rounded-lg h-100">
              <div class="d-flex align-center justify-space-between mb-3 border-b pb-2">
                <span class="font-weight-bold text-body-1 d-flex align-center gap-1">
                  <VIcon icon="ri-copper-coin-line" color="warning" />
                  Monedas (Total: Bs. {{ totalMonedas.toFixed(2) }})
                </span>
              </div>

              <!-- 5 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="warning" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 5.00
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m5', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m5', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="monedas.m5"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- 2 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="warning" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 2.00
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m2', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m2', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="monedas.m2"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- 1 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="warning" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 1.00
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m1', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m1', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="monedas.m1"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>

              <!-- 0.50 Bs -->
              <div class="d-flex align-center justify-space-between mb-2">
                <VChip color="warning" variant="flat" class="font-weight-bold" style="width: 75px; justify-content: center;">
                  Bs. 0.50
                </VChip>
                <div class="d-flex align-center gap-1">
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m050', 1)">+1</VBtn>
                  <VBtn size="x-small" variant="tonal" @click="addCoin('m050', 5)">+5</VBtn>
                  <VTextField
                    v-model.number="monedas.m050"
                    type="number"
                    min="0"
                    density="compact"
                    hide-details
                    style="width: 70px;"
                  />
                </div>
              </div>
            </VCard>
          </VCol>

          <!-- Observaciones / Justificación -->
          <VCol cols="12" class="mt-3">
            <VLabel class="mb-1 font-weight-bold">Notas u Observaciones del Cierre</VLabel>
            <VTextField
              v-model="notas"
              placeholder="Ej: Turno cerrado conforme sin novedad..."
              density="comfortable"
              prepend-inner-icon="ri-chat-1-line"
            />
          </VCol>
        </VRow>
      </VCardText>

      <!-- PASO 2: RESULTADO DE CUADRATURA Y REPORTE Z -->
      <VCardText v-else-if="step === 'RESULTADO'" class="pa-4 pt-5 text-center">
        <div class="my-4">
          <VIcon
            :icon="resultadoArqueo?.resultado === 'CUADRADO' ? 'ri-checkbox-circle-line' : resultadoArqueo?.resultado === 'SOBRANTE' ? 'ri-information-line' : 'ri-error-warning-line'"
            :color="resultadoArqueo?.resultado === 'CUADRADO' ? 'success' : resultadoArqueo?.resultado === 'SOBRANTE' ? 'info' : 'error'"
            size="72"
          />

          <h2 class="text-h4 font-weight-black mt-2" :class="`text-${resultadoArqueo?.resultado === 'CUADRADO' ? 'success' : resultadoArqueo?.resultado === 'SOBRANTE' ? 'info' : 'error'}`">
            {{ resultadoArqueo?.resultado === 'CUADRADO' ? '¡Caja Cuadrada Perfecta!' : resultadoArqueo?.resultado === 'SOBRANTE' ? 'Sobrante en Caja' : 'Faltante en Caja' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis">
            Turno #{{ resultadoArqueo?.turno_id }} cerrado formalmente.
          </p>
        </div>

        <!-- TARJETA COMPARATIVA CUADRATURA -->
        <VRow justify="center" class="my-4">
          <VCol cols="12" sm="10">
            <VCard class="pa-4 bg-surface-variant rounded-lg elevation-1">
              <div class="d-flex justify-space-between py-2 border-b">
                <span class="text-body-1 font-weight-medium">Efectivo Físico Declarado:</span>
                <span class="text-h6 font-weight-bold text-primary">Bs. {{ resultadoArqueo?.total_declarado?.toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-space-between py-2 border-b">
                <span class="text-body-1 font-weight-medium">Efectivo Esperado del Sistema:</span>
                <span class="text-h6 font-weight-bold">Bs. {{ resultadoArqueo?.total_esperado?.toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-space-between py-2">
                <span class="text-h6 font-weight-black">Diferencia de Cuadratura:</span>
                <span
                  class="text-h5 font-weight-black"
                  :class="resultadoArqueo?.diferencia >= 0 ? 'text-success' : 'text-error'"
                >
                  {{ resultadoArqueo?.diferencia > 0 ? '+' : '' }}Bs. {{ resultadoArqueo?.diferencia?.toFixed(2) }}
                </span>
              </div>
            </VCard>
          </VCol>
        </VRow>
      </VCardText>

      <!-- PIE DE ACCIONES -->
      <VDivider />
      <VCardActions class="pa-4 bg-surface-variant d-flex justify-space-between">
        <VBtn
          v-if="step === 'CONTEO'"
          variant="tonal"
          color="default"
          @click="resetConteo"
        >
          <VIcon icon="ri-restart-line" start />
          Limpiar Conteo
        </VBtn>
        <div v-else></div>

        <div class="d-flex gap-2">
          <VBtn
            v-if="step === 'CONTEO'"
            variant="tonal"
            color="default"
            @click="closeModal"
          >
            Cancelar
          </VBtn>

          <VBtn
            v-if="step === 'CONTEO'"
            color="primary"
            variant="elevated"
            class="font-weight-black px-6"
            size="large"
            :loading="loading"
            @click="procesarCierreArqueo"
          >
            <VIcon icon="ri-lock-line" start />
            Realizar Arqueo y Cerrar Turno
          </VBtn>

          <VBtn
            v-if="step === 'RESULTADO'"
            color="secondary"
            variant="outlined"
            @click="imprimirCierreZ"
          >
            <VIcon icon="ri-printer-line" start />
            Imprimir Reporte Z
          </VBtn>

          <VBtn
            v-if="step === 'RESULTADO'"
            color="primary"
            variant="elevated"
            @click="closeModal"
          >
            Finalizar
          </VBtn>
        </div>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
