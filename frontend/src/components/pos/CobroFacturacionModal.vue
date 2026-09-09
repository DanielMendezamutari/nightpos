<script setup>
import { ref, computed, watch } from 'vue'
import { useCajaStore } from '@/stores/caja'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  mesa: {
    type: Object,
    default: null,
  },
  visita: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'cobro-exitoso'])

const cajaStore = useCajaStore()

// State
const metodoPago = ref('EFECTIVO')
const tipoDocumento = ref('NIT')
const numeroDocumento = ref('0')
const razonSocial = ref('SIN NOMBRE')
const correo = ref('')
const montoRecibidoStr = ref('')
const processing = ref(false)
const errorMessage = ref('')
const ticketEmitidoDialog = ref(false)
const facturaEmitida = ref(null)

// Total a cobrar
const totalCobro = computed(() => {
  if (!props.visita) return 0
  if (parseFloat(props.visita.total) > 0) return parseFloat(props.visita.total)
  if (props.visita.detalles?.length) {
    return props.visita.detalles.reduce((acc, d) => acc + (parseFloat(d.subtotal) || 0), 0)
  }
  return 0
})

// Monto recibido numÃ©rico
const montoRecibidoNum = computed(() => {
  const val = parseFloat(montoRecibidoStr.value)
  return isNaN(val) ? 0 : val
})

// Cambio / Vuelto
const cambioCalculado = computed(() => {
  if (metodoPago.value !== 'EFECTIVO') return 0
  const diff = montoRecibidoNum.value - totalCobro.value
  return diff > 0 ? Math.round(diff * 100) / 100 : 0
})

// Es vÃ¡lido para procesar
const isValid = computed(() => {
  if (totalCobro.value <= 0) return false
  if (!numeroDocumento.value.trim() || !razonSocial.value.trim()) return false
  if (metodoPago.value === 'EFECTIVO' && montoRecibidoNum.value < totalCobro.value) return false
  return true
})

// Sync default on open
watch(() => props.modelValue, (val) => {
  if (val) {
    metodoPago.value = 'EFECTIVO'
    tipoDocumento.value = 'NIT'
    numeroDocumento.value = '0'
    razonSocial.value = 'SIN NOMBRE'
    correo.value = ''
    montoRecibidoStr.value = totalCobro.value > 0 ? totalCobro.value.toFixed(2) : '0'
    errorMessage.value = ''
  }
})

// Quick Cash Buttons
const setMontoExacto = () => {
  montoRecibidoStr.value = totalCobro.value.toFixed(2)
}

const addQuickCash = (extra) => {
  const base = Math.ceil(totalCobro.value / extra) * extra
  montoRecibidoStr.value = base.toFixed(2)
}

// Touch Numpad Actions
const appendKeypad = (char) => {
  if (char === 'C') {
    montoRecibidoStr.value = ''
    return
  }
  if (char === '.' && montoRecibidoStr.value.includes('.')) return
  montoRecibidoStr.value += char
}

const backspaceKeypad = () => {
  montoRecibidoStr.value = montoRecibidoStr.value.slice(0, -1)
}

// Quick customer shortcuts
const setSinNombre = () => {
  tipoDocumento.value = 'NIT'
  numeroDocumento.value = '0'
  razonSocial.value = 'SIN NOMBRE'
}

// Close dialog
const closeModal = () => {
  emit('update:modelValue', false)
}

// Submit payment and emit SIAT invoice
const submitCobro = async () => {
  if (!props.mesa?.id) return
  if (!cajaStore.isTurnoAbierto) {
    errorMessage.value = 'Debe tener un turno de caja abierto para cobrar. Abra la caja en el botÃ³n superior.'
    return
  }

  processing.value = true
  errorMessage.value = ''

  const payload = {
    tipo_documento: tipoDocumento.value,
    numero_documento: numeroDocumento.value,
    razon_social: razonSocial.value,
    correo: correo.value || null,
    metodo_pago: metodoPago.value,
    monto_recibido: metodoPago.value === 'EFECTIVO' ? montoRecibidoNum.value : totalCobro.value,
  }

  const res = await cajaStore.cobrarYFacturar(props.mesa.id, payload)

  processing.value = false

  if (res.success) {
    facturaEmitida.value = res.data.factura
    emit('update:modelValue', false)
    ticketEmitidoDialog.value = true
    emit('cobro-exitoso', res.data)
  } else {
    errorMessage.value = res.message || 'Error al procesar cobro'
  }
}

// Print ticket action
const printTicket = () => {
  window.print()
}

const finalizarTicket = () => {
  ticketEmitidoDialog.value = false
  facturaEmitida.value = null
}
</script>

<template>
  <div>
    <!-- Main Cobro & Facturacion Dialog (frmFacturacion1 Replica) -->
    <VDialog
      :model-value="modelValue"
      max-width="950"
      persistent
      scrollable
    >
      <VCard class="cobro-modal-card">
        <!-- Header -->
        <VCardItem class="bg-primary text-white py-3 px-4">
          <div class="d-flex align-center justify-space-between w-100">
            <div class="d-flex align-center gap-2">
              <VIcon icon="ri-secure-payment-line" size="28" />
              <div>
                <h3 class="text-h6 text-white font-weight-bold mb-0">
                  Cobro & FacturaciÃ³n SIAT Bolivia
                </h3>
                <span class="text-caption text-white opacity-80">
                  {{ mesa?.nombre || 'Mesa' }} | Cliente: {{ visita?.cliente_nombre || 'Cliente Ocasional' }}
                </span>
              </div>
            </div>
            <VBtn
              icon="ri-close-line"
              variant="text"
              color="white"
              density="comfortable"
              @click="closeModal"
            />
          </div>
        </VCardItem>

        <VCardText class="pa-4">
          <VAlert
            v-if="errorMessage"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="errorMessage = ''"
          >
            {{ errorMessage }}
          </VAlert>

          <VRow>
            <!-- Left Column: Totales, Metodo de Pago y Teclado TÃ¡ctil -->
            <VCol cols="12" md="7">
              <!-- Big Total Display -->
              <VCard variant="tonal" color="primary" class="mb-4 text-center py-3">
                <div class="text-overline text-uppercase font-weight-bold letter-spacing-1">
                  Total a Cobrar
                </div>
                <div class="text-h3 font-weight-black text-primary">
                  Bs. {{ totalCobro.toFixed(2) }}
                </div>
              </VCard>

              <!-- Payment Method Selector -->
              <div class="text-subtitle-2 font-weight-bold mb-2 d-flex align-center gap-1">
                <VIcon icon="ri-wallet-3-line" size="18" />
                <span>MÃ©todo de Pago:</span>
              </div>

              <div class="d-grid grid-cols-2 gap-2 mb-4">
                <VBtn
                  :variant="metodoPago === 'EFECTIVO' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'EFECTIVO' ? 'success' : 'default'"
                  height="48"
                  class="font-weight-bold"
                  @click="metodoPago = 'EFECTIVO'"
                >
                  <VIcon icon="ri-money-dollar-circle-line" class="me-1" />
                  Efectivo (Bs.)
                </VBtn>

                <VBtn
                  :variant="metodoPago === 'QR' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'QR' ? 'info' : 'default'"
                  height="48"
                  class="font-weight-bold"
                  @click="metodoPago = 'QR'"
                >
                  <VIcon icon="ri-qr-code-line" class="me-1" />
                  QR Digital
                </VBtn>

                <VBtn
                  :variant="metodoPago === 'TARJETA' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'TARJETA' ? 'warning' : 'default'"
                  height="48"
                  class="font-weight-bold"
                  @click="metodoPago = 'TARJETA'"
                >
                  <VIcon icon="ri-bank-card-line" class="me-1" />
                  Tarjeta POS
                </VBtn>

                <VBtn
                  :variant="metodoPago === 'MIXTO' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'MIXTO' ? 'secondary' : 'default'"
                  height="48"
                  class="font-weight-bold"
                  @click="metodoPago = 'MIXTO'"
                >
                  <VIcon icon="ri-exchange-line" class="me-1" />
                  Pago Mixto
                </VBtn>
              </div>

              <!-- Cash Tender & Change (Only for Efectivo) -->
              <div v-if="metodoPago === 'EFECTIVO'" class="cash-section">
                <!-- Quick Cash Presets -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <VBtn size="small" variant="tonal" color="success" @click="setMontoExacto">
                    Exacto (Bs. {{ totalCobro.toFixed(2) }})
                  </VBtn>
                  <VBtn size="small" variant="tonal" @click="addQuickCash(10)">+10 Bs</VBtn>
                  <VBtn size="small" variant="tonal" @click="addQuickCash(20)">+20 Bs</VBtn>
                  <VBtn size="small" variant="tonal" @click="addQuickCash(50)">+50 Bs</VBtn>
                  <VBtn size="small" variant="tonal" @click="addQuickCash(100)">+100 Bs</VBtn>
                  <VBtn size="small" variant="tonal" @click="addQuickCash(200)">+200 Bs</VBtn>
                </div>

                <!-- Input Monto Recibido -->
                <VTextField
                  v-model="montoRecibidoStr"
                  label="Monto Entregado por Cliente (Bs.)"
                  prefix="Bs."
                  variant="outlined"
                  density="comfortable"
                  type="number"
                  step="0.10"
                  class="mb-3 text-h6 font-weight-bold"
                  autofocus
                />

                <!-- Big Change Display -->
                <VCard variant="tonal" color="success" class="text-center py-2 mb-3">
                  <div class="text-caption text-uppercase font-weight-bold">
                    Cambio / Vuelto a Devolver:
                  </div>
                  <div class="text-h4 font-weight-black text-success">
                    Bs. {{ cambioCalculado.toFixed(2) }}
                  </div>
                </VCard>

                <!-- Touch Numeric Keypad -->
                <div class="touch-keypad">
                  <div class="d-grid grid-cols-3 gap-2">
                    <VBtn
                      v-for="k in ['1','2','3','4','5','6','7','8','9','C','0','.']"
                      :key="k"
                      variant="tonal"
                      size="large"
                      class="text-h6 font-weight-bold py-2"
                      @click="appendKeypad(k)"
                    >
                      {{ k }}
                    </VBtn>
                  </div>
                </div>
              </div>
            </VCol>

            <!-- Right Column: Datos de FacturaciÃ³n SIAT Bolivia -->
            <VCol cols="12" md="5">
              <VCard variant="outlined" class="pa-3 h-100">
                <div class="d-flex align-center justify-space-between mb-3">
                  <div class="text-subtitle-1 font-weight-bold d-flex align-center gap-1">
                    <VIcon icon="ri-file-shield-2-line" color="primary" size="20" />
                    <span>Datos de Factura (SIAT)</span>
                  </div>
                  <VBtn
                    size="x-small"
                    variant="tonal"
                    color="primary"
                    @click="setSinNombre"
                  >
                    Sin NIT / S/N
                  </VBtn>
                </div>

                <!-- Document Type -->
                <div class="mb-3">
                  <label class="text-caption font-weight-medium mb-1 d-block">Tipo de Documento:</label>
                  <VBtnToggle
                    v-model="tipoDocumento"
                    mandatory
                    density="compact"
                    color="primary"
                    variant="outlined"
                    class="w-100"
                  >
                    <VBtn value="NIT" class="flex-grow-1">NIT</VBtn>
                    <VBtn value="CI" class="flex-grow-1">CI</VBtn>
                    <VBtn value="CEX" class="flex-grow-1">CEX</VBtn>
                    <VBtn value="PASAPORTE" class="flex-grow-1">PAS</VBtn>
                  </VBtnToggle>
                </div>

                <!-- Document Number -->
                <VTextField
                  v-model="numeroDocumento"
                  label="NÂ° Documento / NIT"
                  variant="outlined"
                  density="compact"
                  class="mb-3"
                  prepend-inner-icon="ri-hashtag"
                  placeholder="0 o NIT de la empresa"
                />

                <!-- Razon Social -->
                <VTextField
                  v-model="razonSocial"
                  label="RazÃ³n Social / Nombre"
                  variant="outlined"
                  density="compact"
                  class="mb-3"
                  prepend-inner-icon="ri-building-line"
                  placeholder="SIN NOMBRE o Nombre del cliente"
                />

                <!-- Correo ElectrÃ³nico -->
                <VTextField
                  v-model="correo"
                  label="Correo ElectrÃ³nico (Opcional)"
                  variant="outlined"
                  density="compact"
                  type="email"
                  class="mb-3"
                  prepend-inner-icon="ri-mail-line"
                  placeholder="cliente@ejemplo.com"
                />

                <!-- Items Preview -->
                <div class="items-summary mt-2">
                  <div class="text-caption font-weight-bold text-medium-emphasis mb-1">
                    Resumen de Ãtems ({{ visita?.detalles?.length || 0 }}):
                  </div>
                  <div class="items-scroll" style="max-height: 140px; overflow-y: auto;">
                    <div
                      v-for="item in (visita?.detalles || [])"
                      :key="item.id"
                      class="d-flex justify-space-between text-caption py-1 border-b"
                    >
                      <span class="text-truncate" style="max-width: 170px;">
                        {{ item.cantidad }}x {{ item.producto_nombre }}
                      </span>
                      <span class="font-weight-bold">
                        Bs. {{ parseFloat(item.subtotal).toFixed(2) }}
                      </span>
                    </div>
                  </div>
                </div>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>

        <!-- Actions -->
        <VCardActions class="pa-4 bg-surface-variant d-flex justify-space-between align-center flex-wrap gap-2">
          <VBtn
            variant="outlined"
            color="secondary"
            prepend-icon="ri-close-circle-line"
            @click="closeModal"
          >
            Cancelar (ESC)
          </VBtn>

          <VBtn
            color="success"
            variant="flat"
            size="large"
            prepend-icon="ri-printer-line"
            :loading="processing"
            :disabled="!isValid"
            class="px-6 font-weight-bold"
            @click="submitCobro"
          >
            EMITIR FACTURA Y COBRAR (F12)
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Ticket / Invoice Success Dialog (Thermal 80mm SIAT Layout) -->
    <VDialog
      v-model="ticketEmitidoDialog"
      max-width="450"
      persistent
    >
      <VCard class="thermal-ticket-card">
        <VCardItem class="bg-success text-white py-2">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="font-weight-bold">Factura SIAT Emitida</span>
            <VIcon icon="ri-checkbox-circle-fill" />
          </div>
        </VCardItem>

        <VCardText class="pa-4 ticket-printable">
          <!-- Header -->
          <div class="text-center mb-3">
            <h4 class="font-weight-black text-h6 mb-0">RIBERRESTO POS</h4>
            <div class="text-caption font-weight-bold">RIBERSOFT BOLIVIA</div>
            <div class="text-caption">NIT: 1028456023 | Telf: 67369293</div>
            <div class="text-caption">Santa Cruz - Bolivia</div>
            <div class="border-b my-2" />
            <div class="text-subtitle-2 font-weight-bold">
              FACTURA NÂ° {{ facturaEmitida?.nro_factura }}
            </div>
            <div class="text-caption text-break opacity-75 font-mono" style="font-size: 9px;">
              CUF: {{ facturaEmitida?.cuf }}
            </div>
          </div>

          <!-- Customer Data -->
          <div class="text-caption mb-2 border-b pb-2">
            <div><strong>Fecha:</strong> {{ facturaEmitida?.fecha_emision }}</div>
            <div><strong>SeÃ±or(es):</strong> {{ facturaEmitida?.razon_social }}</div>
            <div><strong>NIT/CI:</strong> {{ facturaEmitida?.numero_documento }}</div>
            <div><strong>MÃ©todo:</strong> {{ facturaEmitida?.metodo_pago }}</div>
            <div><strong>Mesa:</strong> {{ facturaEmitida?.mesa_numero }}</div>
          </div>

          <!-- Items Details -->
          <div class="border-b pb-2 mb-2">
            <div class="d-flex justify-space-between text-caption font-weight-bold">
              <span>Cant. / Detalle</span>
              <span>Subtotal</span>
            </div>
            <div
              v-for="det in (facturaEmitida?.detalles || [])"
              :key="det.id"
              class="d-flex justify-space-between text-caption py-0"
            >
              <span>{{ det.cantidad }}x {{ det.producto_nombre }}</span>
              <span>Bs. {{ parseFloat(det.subtotal).toFixed(2) }}</span>
            </div>
          </div>

          <!-- Totals -->
          <div class="text-right text-caption mb-3">
            <div class="text-subtitle-1 font-weight-black">
              TOTAL: Bs. {{ parseFloat(facturaEmitida?.monto_total || 0).toFixed(2) }}
            </div>
            <div v-if="facturaEmitida?.metodo_pago === 'EFECTIVO'">
              <div>Efectivo: Bs. {{ parseFloat(facturaEmitida?.monto_efectivo || 0).toFixed(2) }}</div>
              <div class="font-weight-bold text-success">
                Cambio: Bs. {{ parseFloat(facturaEmitida?.monto_cambio || 0).toFixed(2) }}
              </div>
            </div>
          </div>

          <!-- QR & Legal Leyenda -->
          <div class="text-center mt-2">
            <div class="qr-placeholder mx-auto mb-2 pa-2 border rounded" style="width: 120px; height: 120px; background: #fff;">
              <VIcon icon="ri-qr-code-line" size="100" color="black" />
            </div>
            <div class="text-caption font-weight-bold" style="font-size: 10px;">
              "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÃS, EL USO ILÃCITO SERÃ SANCIONADO PENALMENTE DE ACUERDO A LEY"
            </div>
            <div class="text-caption text-medium-emphasis mt-1" style="font-size: 9px;">
              Ley NÂ° 453: El proveedor deberÃ¡ suministrar el servicio en las modalidades y tÃ©rminos ofertados o convenidos.
            </div>
          </div>
        </VCardText>

        <VCardActions class="pa-3 bg-surface-variant d-flex justify-space-between">
          <VBtn
            variant="outlined"
            prepend-icon="ri-printer-line"
            @click="printTicket"
          >
            Imprimir
          </VBtn>
          <VBtn
            color="primary"
            variant="flat"
            prepend-icon="ri-check-line"
            @click="finalizarTicket"
          >
            Finalizar y Liberar Mesa
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.d-grid {
  display: grid;
}
.grid-cols-2 {
  grid-template-columns: repeat(2, 1fr);
}
.grid-cols-3 {
  grid-template-columns: repeat(3, 1fr);
}
.font-mono {
  font-family: monospace;
}
</style>