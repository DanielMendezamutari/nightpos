<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import { useCajaStore } from '@/stores/caja'
import { useComandaStore } from '@/stores/comanda'
import QRCode from 'qrcode'

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
const comandaStore = useComandaStore()

// --- State (frmPagondoConTecladoNumerico & frmFacturacion1 Replica) ---
const moneda = ref('BOB') // 'BOB' o 'USD'
const tipoCambio = ref(6.96) // _txtTipoCambio de RestoTech
const metodoPago = ref('EFECTIVO') // EFECTIVO, TARJETA, QR, MIXTO

// Tarjeta POS
const tarjetaIni = ref('') // _txtTarjeta24ini
const tarjetaFin = ref('') // _txtTarjeta24fin
const tarjetaTipo = ref('DEBITO')

// QR Digital Dinamico Bancario
const qrCanvasRef = ref(null)
const qrData = ref(null)
const qrPollingTimer = ref(null)
const qrCountdown = ref(600) // 10 minutos
const qrTimerInterval = ref(null)
const qrStatus = ref('INACTIVO') // INACTIVO, GENERANDO, ESPERANDO, PAGADO, EXPIRADO
const qrBancoConfirmado = ref('')
const qrRefBancaria = ref('')

// Monto recibido
const montoRecibidoStr = ref('')

// Pago Mixto
const montoMixtoEfectivo = ref(0)
const montoMixtoDigital = ref(0)
const segundoMetodo = ref('TARJETA') // 'TARJETA' | 'QR'

const onMontoMixtoEfectivoChange = (val) => {
  const ef = parseFloat(val) || 0
  montoMixtoEfectivo.value = ef
  montoMixtoDigital.value = Math.max(0, Math.round((totalCobroBob.value - ef) * 100) / 100)
}


// --- Propinas Mesero (FrmPropinas RestoTech) ---
const propinaPorcentaje = ref(0)
const propinaMonto = ref(0)

const seleccionarPropina = (pct) => {
  propinaPorcentaje.value = pct
  if (pct === 0) {
    propinaMonto.value = 0
  } else {
    propinaMonto.value = Math.round((totalCobroBob.value * pct / 100) * 100) / 100
  }
}

const onMontoMixtoDigitalChange = (val) => {
  const dig = parseFloat(val) || 0
  montoMixtoDigital.value = dig
  montoMixtoEfectivo.value = Math.max(0, Math.round((totalCobroBob.value - dig) * 100) / 100)
}

// Facturacion SIAT (frmFacturacion1)
const tipoComprobante = ref('FACTURA') // 'FACTURA' o 'RECIBO'
  const tipoDocumento = ref('NIT') // CI, NIT, CEX, PASAPORTE
const numeroDocumento = ref('0')
const razonSocial = ref('SIN NOMBRE')
const correo = ref('')
const imprimirFisico = ref(true) // _chbImprimirFisico

// UI State
const processing = ref(false)
const errorMessage = ref('')
const facturaEmitida = ref(null)

// Visita activa resuelta (mesa física o pedido sin mesa)
const activeVisita = computed(() => {
  return props.visita || props.mesa?.visitaActiva || null
})

// Total a cobrar en Bolivianos
const totalCobroBob = computed(() => {
  const v = activeVisita.value
  if (!v) return 0
  if (parseFloat(v.total) > 0) return parseFloat(v.total)
  if (v.detalles?.length) {
    return v.detalles.reduce((acc, d) => acc + (parseFloat(d.subtotal) || 0), 0)
  }
  return 0
})

// Total en Dolares segun Tipo de Cambio
const totalCobroUsd = computed(() => {
  const tc = parseFloat(tipoCambio.value) || 6.96
  return Math.round((totalCobroBob.value / tc) * 100) / 100
})

// Total segun moneda seleccionada
const totalActual = computed(() => {
  return moneda.value === 'BOB' ? totalCobroBob.value : totalCobroUsd.value
})

// Monto recibido numerico
const montoRecibidoNum = computed(() => {
  const val = parseFloat(montoRecibidoStr.value)
  return isNaN(val) ? 0 : val
})

// Monto en Bolivianos del dinero entregado
const montoEntregadoEnBob = computed(() => {
  if (moneda.value === 'BOB') return montoRecibidoNum.value
  const tc = parseFloat(tipoCambio.value) || 6.96
  return Math.round(montoRecibidoNum.value * tc * 100) / 100
})

// Cambio / Vuelto a devolver en Bolivianos
const cambioCalculadoBob = computed(() => {
  if (metodoPago.value !== 'EFECTIVO') return 0
  const diff = montoEntregadoEnBob.value - totalCobroBob.value
  return diff > 0 ? Math.round(diff * 100) / 100 : 0
})

// Cambio / Vuelto en Dolares
const cambioCalculadoUsd = computed(() => {
  const tc = parseFloat(tipoCambio.value) || 6.96
  return Math.round((cambioCalculadoBob.value / tc) * 100) / 100
})

// Validacion para habilitar boton de cobro
const isValid = computed(() => {
  if (totalCobroBob.value <= 0) return false
  if (!numeroDocumento.value.trim() || !razonSocial.value.trim()) return false

  if (metodoPago.value === 'EFECTIVO') {
    return montoEntregadoEnBob.value >= totalCobroBob.value
  }

  if (metodoPago.value === 'TARJETA') {
    return tarjetaIni.value.length === 4 && tarjetaFin.value.length === 4
  }

  if (metodoPago.value === 'QR') {
    return qrStatus.value === 'PAGADO'
  }

  if (metodoPago.value === 'MIXTO') {
    const totalMixto = (parseFloat(montoMixtoEfectivo.value) || 0) + (parseFloat(montoMixtoDigital.value) || 0)
    if (totalMixto < totalCobroBob.value) return false
    if (segundoMetodo.value === 'TARJETA') {
      return tarjetaIni.value.length === 4 && tarjetaFin.value.length === 4
    }
    return true
  }

  return true
})

// Reset on Modal Open
watch(() => props.modelValue, (val) => {
  if (val) {
    moneda.value = 'BOB'
    tipoCambio.value = 6.96
    metodoPago.value = 'EFECTIVO'
    tarjetaIni.value = ''
    tarjetaFin.value = ''
    tipoDocumento.value = 'NIT'
    numeroDocumento.value = '0'
    razonSocial.value = 'SIN NOMBRE'
    correo.value = ''
    imprimirFisico.value = true
    if (activeVisita.value?.cliente_nombre && activeVisita.value.cliente_nombre !== 'Cliente General') {
      razonSocial.value = activeVisita.value.cliente_nombre
    }
    montoRecibidoStr.value = totalCobroBob.value > 0 ? totalCobroBob.value.toFixed(2) : '0'
    segundoMetodo.value = 'TARJETA'
    montoMixtoEfectivo.value = Math.round(totalCobroBob.value / 2)
    montoMixtoDigital.value = totalCobroBob.value - montoMixtoEfectivo.value
    errorMessage.value = ''
    qrStatus.value = 'INACTIVO'
    qrData.value = null
    stopQrPolling()
  } else {
    stopQrPolling()
  }
})

// Quick Bill buttons (Bolivian Banknotes)
const setMontoExacto = () => {
  montoRecibidoStr.value = totalActual.value.toFixed(2)
}

const addQuickBill = (bs) => {
  if (moneda.value === 'BOB') {
    montoRecibidoStr.value = bs.toFixed(2)
  } else {
    const tc = parseFloat(tipoCambio.value) || 6.96
    const usd = Math.round((bs / tc) * 100) / 100
    montoRecibidoStr.value = usd.toFixed(2)
  }
}

// Touch Numpad
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

// Quick customer info
const setSinNombre = () => {
  tipoDocumento.value = 'NIT'
  numeroDocumento.value = '0'
  razonSocial.value = 'SIN NOMBRE'
}

const appendCorreoDomain = (domain) => {
  if (!correo.value.includes('@')) {
    correo.value += domain
  } else {
    const user = correo.value.split('@')[0]
    correo.value = user + domain
  }
}

// --- QR Payment Engine ---
const generarQr = async () => {
  if (!props.mesa?.id) return
  qrStatus.value = 'GENERANDO'
  errorMessage.value = ''

  const res = await cajaStore.generarPagoQr({
    mesa_id: props.mesa.id,
    monto: totalCobroBob.value,
    glosa: `Mesa ${props.mesa.numero || props.mesa.id} - RiberResto POS`,
  })

  if (res.success) {
    qrData.value = res.data
    qrStatus.value = 'ESPERANDO'
    qrCountdown.value = 600

    await nextTick()
    if (qrCanvasRef.value && res.data.qr_payload) {
      QRCode.toCanvas(qrCanvasRef.value, res.data.qr_payload, {
        width: 220,
        margin: 1,
        color: {
          dark: '#111827',
          light: '#ffffff',
        },
      })
    }

    startQrPolling(res.data.codigo_transaccion)
  } else {
    qrStatus.value = 'INACTIVO'
    errorMessage.value = res.message || 'Error al generar código QR'
  }
}

const startQrPolling = (codigo) => {
  stopQrPolling()

  qrPollingTimer.value = setInterval(async () => {
    const res = await cajaStore.consultarEstadoQr(codigo)
    if (res.success && res.data.estado === 'PAGADO') {
      qrStatus.value = 'PAGADO'
      qrBancoConfirmado.value = res.data.banco || 'BANCO CONFIRMADO'
      qrRefBancaria.value = res.data.referencia_bancaria || 'TRANS-OK'
      stopQrPolling()

      // DISPARO AUTOMATICO: Confirmado el QR, emitir factura e imprimir silenciosamente
      await submitCobro()
    } else if (res.success && res.data.estado === 'EXPIRADO') {
      qrStatus.value = 'EXPIRADO'
      stopQrPolling()
    }
  }, 2000)

  qrTimerInterval.value = setInterval(() => {
    if (qrCountdown.value > 0) {
      qrCountdown.value--
    } else {
      qrStatus.value = 'EXPIRADO'
      stopQrPolling()
    }
  }, 1000)
}

const stopQrPolling = () => {
  if (qrPollingTimer.value) {
    clearInterval(qrPollingTimer.value)
    qrPollingTimer.value = null
  }
  if (qrTimerInterval.value) {
    clearInterval(qrTimerInterval.value)
    qrTimerInterval.value = null
  }
}

// Simular pago bancario instantaneo (para pruebas del cajero)
const simularPagoBancario = async () => {
  if (!qrData.value?.codigo_transaccion) return
  processing.value = true
  const res = await cajaStore.simularPagoQr(qrData.value.codigo_transaccion)
  processing.value = false

  if (res.success) {
    qrStatus.value = 'PAGADO'
    qrBancoConfirmado.value = res.data.banco || 'BANCO BCP'
    qrRefBancaria.value = res.data.referencia_bancaria || 'SIM-TEST-OK'
    stopQrPolling()

    // DISPARO AUTOMATICO DE COBRO E IMPRESION
    await submitCobro()
  } else {
    errorMessage.value = res.message || 'Error al simular pago bancario'
  }
}

watch(metodoPago, (newVal) => {
  if (newVal === 'QR') {
    generarQr()
  } else {
    stopQrPolling()
  }
})

const formatTime = (secs) => {
  const m = Math.floor(secs / 60).toString().padStart(2, '0')
  const s = (secs % 60).toString().padStart(2, '0')
  return `${m}:${s}`
}

const closeModal = () => {
  stopQrPolling()
  emit('update:modelValue', false)
}

// --- IMPRESIÓN DIRECTA AISLADA MEDIANTE IFRAME OCULTO (100% LIMPIO 80MM) ---
const imprimirTicketTermicoIframe = (factura) => {
  if (!imprimirFisico.value || !factura) return

  let iframe = document.getElementById('resto-thermal-iframe')
  if (!iframe) {
    iframe = document.createElement('iframe')
    iframe.id = 'resto-thermal-iframe'
    iframe.style.position = 'fixed'
    iframe.style.right = '0'
    iframe.style.bottom = '0'
    iframe.style.width = '0'
    iframe.style.height = '0'
    iframe.style.border = '0'
    document.body.appendChild(iframe)
  }

  const itemsHtml = (factura.detalles || []).map(d => `
    <div style="display:flex; justify-content:space-between; margin:2px 0;">
      <span>${d.cantidad}x ${d.producto_nombre}</span>
      <span>Bs. ${parseFloat(d.subtotal).toFixed(2)}</span>
    </div>
  `).join('')

  const ticketHtml = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Ticket Factura ${factura.nro_factura}</title>
      <style>
        @page {
          margin: 0;
          size: 80mm auto;
        }
        body {
          margin: 0;
          padding: 3mm 4mm;
          font-family: 'Courier New', Courier, monospace;
          font-size: 11px;
          line-height: 1.25;
          color: #000000;
          background: #ffffff;
          width: 72mm;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .title { font-size: 15px; font-weight: 900; margin: 0; }
        .sub { font-size: 12px; font-weight: bold; }
        .divider { border-bottom: 1px dashed #000000; margin: 4px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        .total-row { font-size: 13px; font-weight: 900; }
        .cuf { font-size: 8px; word-break: break-all; margin: 2px 0; }
        .legal { font-size: 8px; text-align: center; margin-top: 4px; line-height: 1.2; }
        .footer { font-size: 8px; font-weight: bold; text-align: center; margin-top: 6px; }
      </style>
    </head>
    <body>
      <div class="text-center">
        <div class="title">RIBERRESTO POS</div>
        <div class="sub">RIBERSOFT BOLIVIA</div>
        <div>NIT: 1028456023 | Telf: 67369293</div>
        <div>Santa Cruz - Bolivia</div>
        <div class="divider"></div>
        ${factura.tipo_comprobante === 'RECIBO'
          ? `<div class="bold" style="font-size:13px; text-transform:uppercase;">RECIBO DE CAJA / NOTA DE VENTA</div>
             <div class="bold" style="font-size:11px;">N° ${factura.nro_comprobante || 'REC-' + factura.nro_factura}</div>
             <div style="font-size:9px; margin-top:2px;">*** COMPROBANTE DE CONSUMO INTERNO ***</div>`
          : `<div class="bold" style="font-size:12px;">FACTURA N° ${factura.nro_factura}</div>
             <div class="cuf">CUF: ${factura.cuf || ''}</div>`
        }
      </div>

      <div class="divider"></div>

      <div>
        <div><strong>Fecha:</strong> ${factura.fecha_emision || ''}</div>
        <div><strong>Señor(es):</strong> ${factura.razon_social || 'SIN NOMBRE'}</div>
        <div><strong>NIT/CI:</strong> ${factura.numero_documento || '0'}</div>
        <div><strong>Método:</strong> ${factura.metodo_pago || 'EFECTIVO'}</div>
        <div><strong>Mesa:</strong> ${factura.mesa_numero || ''}</div>
      </div>

      <div class="divider"></div>

      <div class="bold" style="display:flex; justify-content:space-between; border-bottom:1px solid #000; padding-bottom:2px;">
        <span>Cant. / Detalle</span>
        <span>Subtotal</span>
      </div>
      ${itemsHtml}

      <div class="divider"></div>

      <div class="row total-row">
        <span>TOTAL A PAGAR:</span>
        <span>Bs. ${parseFloat(factura.monto_total || 0).toFixed(2)}</span>
      </div>
      <div class="row">
        <span>Monto Entregado:</span>
        <span>Bs. ${parseFloat(factura.monto_recibido || factura.monto_total || 0).toFixed(2)}</span>
      </div>
      <div class="row">
        <span>Cambio / Vuelto:</span>
        <span>Bs. ${parseFloat(factura.cambio || 0).toFixed(2)}</span>
      </div>

      <div class="divider"></div>

      <div class="text-center">
        <div style="border:1px solid #000; padding:4px; margin:4px auto; width:80%; font-size:9px; font-weight:bold;">
          [ CÓDIGO QR VALIDACIÓN SIAT ]
        </div>
        <div class="legal">
          "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"
        </div>
        <div class="legal">
          Ley N° 453: El proveedor deberá suministrar el servicio en las condiciones ofertadas.
        </div>
        <div class="footer">
          Desarrollado por Ribersoft | Soporte: 67369293
        </div>
      </div>
    </body>
    </html>
  `

  const frameDoc = iframe.contentWindow.document
  frameDoc.open()
  frameDoc.write(ticketHtml)
  frameDoc.close()

  setTimeout(() => {
    iframe.contentWindow.focus()
    iframe.contentWindow.print()
  }, 250)
}

// Submit payment and emit SIAT invoice
const submitCobro = async () => {
  if (!props.mesa?.id) return
  if (!cajaStore.isTurnoAbierto) {
    errorMessage.value = 'Debe tener un turno de caja abierto para cobrar. Abra la caja en el menú o control de caja.'
    return
  }

  processing.value = true
  errorMessage.value = ''

  let montoRecibidoFinal = totalCobroBob.value
  if (metodoPago.value === 'EFECTIVO') {
    montoRecibidoFinal = montoEntregadoEnBob.value
  }

  const payload = {
    tipo_documento: tipoDocumento.value,
    numero_documento: numeroDocumento.value,
    razon_social: razonSocial.value,
    correo: correo.value || null,
    metodo_pago: metodoPago.value,
    monto_recibido: montoRecibidoFinal,
    tipo_comprobante: tipoComprobante.value,
    propina_monto: parseFloat(propinaMonto.value) || 0,
    propina_porcentaje: parseFloat(propinaPorcentaje.value) || 0,
    datos_adicionales: {
      moneda: moneda.value,
      tipo_cambio: tipoCambio.value,
      tarjeta_ini: tarjetaIni.value || null,
      tarjeta_fin: tarjetaFin.value || null,
      tarjeta_tipo: tarjetaTipo.value,
      banco_confirmado: qrBancoConfirmado.value || null,
      referencia_qr: qrRefBancaria.value || null,
    },
  }

  let res
  if (props.visita?.subcuenta_id) {
    res = await comandaStore.cobrarSubcuenta(props.visita.subcuenta_id, payload)
  } else {
    res = await cajaStore.cobrarYFacturar(props.mesa.id, payload)
  }

  processing.value = false

  if (res.success) {
    facturaEmitida.value = res.data.factura
    stopQrPolling()

    // 1. Disparar impresion termica aislada y limpia de 80mm
    imprimirTicketTermicoIframe(res.data.factura)

    // 2. Cerrar modal y notificar al salon
    emit('update:modelValue', false)
    emit('cobro-exitoso', res.data)
  } else {
    errorMessage.value = res.message || 'Error al procesar cobro'
  }
}

// RestoTech Keyboard Shortcuts (FuncKeysModule)
const handleGlobalKeyDown = (e) => {
  if (!props.modelValue) return

  if (e.key === 'F1') {
    e.preventDefault()
    metodoPago.value = 'EFECTIVO'
  } else if (e.key === 'F4') {
    e.preventDefault()
    metodoPago.value = metodoPago.value === 'TARJETA' ? 'EFECTIVO' : 'TARJETA'
  } else if (e.key === 'F8') {
    e.preventDefault()
    tipoComprobante.value = 'FACTURA'
  } else if (e.key === 'F9') {
    e.preventDefault()
    tipoComprobante.value = 'RECIBO'
  } else if (e.key === 'F12') {
    e.preventDefault()
    if (isValid.value && !processing.value) {
      procesarCobro()
    }
  } else if (e.key === 'Escape') {
    e.preventDefault()
    closeModal()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeyDown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleGlobalKeyDown)
  stopQrPolling()
})
</script>

<template>
  <div>
    <!-- Main Tactile POS Dialog (frmPagondoConTecladoNumerico + frmFacturacion1 Replica) -->
    <VDialog
      :model-value="modelValue"
      max-width="1000"
      persistent
      scrollable
    >
      <VCard class="cobro-modal-card">
        <!-- Header con estética POS Tactil RestoTech -->
        <VCardItem class="bg-primary text-white py-3 px-4">
          <div class="d-flex align-center justify-space-between w-100">
            <div class="d-flex align-center gap-2">
              <VIcon icon="ri-cash-fill" size="30" />
              <div>
                <h3 class="text-h6 text-white font-weight-black mb-0 text-uppercase letter-spacing-1">
                  COBRO & FACTURACIÓN TÁCTIL (RIBERSOFT POS)
                </h3>
                <span class="text-caption text-white opacity-90">
                  {{ mesa?.nombre || 'Mesa ' + (mesa?.numero || '') }} | Mozo: {{ activeVisita?.usuario_nombre || activeVisita?.mesero?.name || 'Cajero' }} | Comensales: {{ activeVisita?.personas || activeVisita?.comensales || 1 }}
                </span>
              </div>
            </div>

            <!-- Currency Toggle: BOB vs USD (_rbBs / _rbDolares) -->
            <div class="d-flex align-center gap-2">
              <div class="d-flex align-center bg-white rounded pa-1 text-primary">
                <VBtn
                  :variant="moneda === 'BOB' ? 'flat' : 'text'"
                  :color="moneda === 'BOB' ? 'primary' : 'default'"
                  size="small"
                  class="font-weight-bold"
                  @click="moneda = 'BOB'"
                >
                  Bs. (BOB)
                </VBtn>
                <VBtn
                  :variant="moneda === 'USD' ? 'flat' : 'text'"
                  :color="moneda === 'USD' ? 'primary' : 'default'"
                  size="small"
                  class="font-weight-bold"
                  @click="moneda = 'USD'"
                >
                  $US (Dólar)
                </VBtn>
              </div>

              <VBtn
                icon="ri-close-line"
                variant="text"
                color="white"
                density="comfortable"
                @click="closeModal"
              />
            </div>
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

          <VRow>
            <!-- COLUMNA IZQUIERDA: PANTALLA TÁCTIL DE PAGO (_frmPagondoConTecladoNumerico) -->
            <VCol cols="12" md="7">
              <!-- Big Amount Display -->
              <VCard variant="tonal" color="primary" class="mb-3 text-center py-2 px-3 border">
                <div class="d-flex justify-space-between align-center">
                  <div class="text-left">
                    <div class="text-caption text-uppercase font-weight-bold opacity-80">
                      Total de la Cuenta
                    </div>
                    <div class="text-h3 font-weight-black text-primary">
                      {{ moneda === 'BOB' ? 'Bs.' : '$US' }} {{ totalActual.toFixed(2) }}
                    </div>
                  </div>
                  <div v-if="moneda === 'USD'" class="text-right">
                    <div class="text-caption font-weight-bold">T.C. Of.: {{ tipoCambio }} Bs</div>
                    <div class="text-subtitle-2 font-weight-bold text-medium-emphasis">
                      Equiv: Bs. {{ totalCobroBob.toFixed(2) }}
                    </div>
                  </div>
                </div>
              </VCard>

              <!-- Selector de Métodos de Pago Táctil -->
              <div class="d-grid grid-cols-4 gap-2 mb-3">
                <VBtn
                  :variant="metodoPago === 'EFECTIVO' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'EFECTIVO' ? 'success' : 'default'"
                  height="52"
                  class="font-weight-black text-body-2"
                  @click="metodoPago = 'EFECTIVO'"
                >
                  <VIcon icon="ri-money-dollar-circle-line" class="me-1" />
                  Efectivo
                </VBtn>

                <VBtn
                  :variant="metodoPago === 'TARJETA' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'TARJETA' ? 'warning' : 'default'"
                  height="52"
                  class="font-weight-black text-body-2"
                  @click="metodoPago = 'TARJETA'"
                >
                  <VIcon icon="ri-bank-card-line" class="me-1" />
                  Tarjeta POS
                </VBtn>

                <VBtn
                  :variant="metodoPago === 'QR' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'QR' ? 'info' : 'default'"
                  height="52"
                  class="font-weight-black text-body-2"
                  @click="metodoPago = 'QR'"
                >
                  <VIcon icon="ri-qr-code-line" class="me-1" />
                  QR Simple
                </VBtn>

                <VBtn
                  :variant="metodoPago === 'MIXTO' ? 'flat' : 'outlined'"
                  :color="metodoPago === 'MIXTO' ? 'secondary' : 'default'"
                  height="52"
                  class="font-weight-black text-body-2"
                  @click="metodoPago = 'MIXTO'"
                >
                  <VIcon icon="ri-exchange-line" class="me-1" />
                  Mixto
                </VBtn>
              </div>

              <!-- 1. VISTA EFECTIVO (TECLADO TÁCTIL Y BILLETES DE BOLIVIA) -->
              <div v-if="metodoPago === 'EFECTIVO'" class="cash-section">
                <!-- Billetes Rápidos de Bolivia -->
                <div class="d-flex flex-wrap gap-2 mb-2">
                  <VBtn
                    size="small"
                    variant="tonal"
                    color="primary"
                    class="font-weight-bold"
                    @click="setMontoExacto"
                  >
                    Monto Exacto
                  </VBtn>
                  <VBtn size="small" variant="tonal" color="success" class="font-weight-bold" @click="addQuickBill(10)">
                    10 Bs
                  </VBtn>
                  <VBtn size="small" variant="tonal" color="success" class="font-weight-bold" @click="addQuickBill(20)">
                    20 Bs
                  </VBtn>
                  <VBtn size="small" variant="tonal" color="success" class="font-weight-bold" @click="addQuickBill(50)">
                    50 Bs
                  </VBtn>
                  <VBtn size="small" variant="tonal" color="success" class="font-weight-bold" @click="addQuickBill(100)">
                    100 Bs
                  </VBtn>
                  <VBtn size="small" variant="tonal" color="success" class="font-weight-bold" @click="addQuickBill(200)">
                    200 Bs
                  </VBtn>
                </div>

                <!-- Input Monto Entregado -->
                <VTextField
                  v-model="montoRecibidoStr"
                  label="Monto Entregado por Cliente"
                  :prefix="moneda === 'BOB' ? 'Bs.' : '$US'"
                  variant="outlined"
                  density="comfortable"
                  type="number"
                  step="0.50"
                  class="mb-2 font-weight-bold text-h6"
                  autofocus
                />

                <!-- Big Change / Vuelto Display -->
                <VCard variant="tonal" color="success" class="text-center py-2 mb-2 border">
                  <div class="text-caption text-uppercase font-weight-bold">
                    CAMBIO / VUELTO A DEVOLVER:
                  </div>
                  <div class="text-h4 font-weight-black text-success">
                    Bs. {{ cambioCalculadoBob.toFixed(2) }}
                  </div>
                  <div v-if="moneda === 'USD'" class="text-caption text-medium-emphasis">
                    (Equiv. $US {{ cambioCalculadoUsd.toFixed(2) }})
                  </div>
                </VCard>

                <!-- Touch Numeric Keypad RestoTech -->
                <div class="touch-keypad mb-2">
                  <div class="d-grid grid-cols-4 gap-2">
                    <VBtn
                      v-for="k in ['7','8','9','C','4','5','6','00','1','2','3','0','.','⌫']"
                      :key="k"
                      variant="tonal"
                      size="large"
                      class="text-h6 font-weight-bold py-2"
                      :color="k === 'C' ? 'error' : (k === 'âŒ«' ? 'warning' : 'default')"
                      @click="k === 'âŒ«' ? backspaceKeypad() : appendKeypad(k)"
                    >
                      {{ k }}
                    </VBtn>
                  </div>
                </div>
              </div>

              <!-- 2. VISTA TARJETA POS (_txtTarjeta24ini, _txtTarjeta24fin) -->
              <div v-else-if="metodoPago === 'TARJETA'" class="card-section pa-3 border rounded bg-surface">
                <div class="text-subtitle-1 font-weight-bold mb-2 d-flex align-center gap-2 text-warning">
                  <VIcon icon="ri-bank-card-fill" />
                  <span>Cobro con Tarjeta de Débito / Crédito</span>
                </div>
                <div class="text-caption mb-3 text-medium-emphasis">
                  Introduzca los primeros 4 y últimos 4 dígitos del voucher emitido por el POS físico:
                </div>

                <VRow>
                  <VCol cols="6">
                    <VTextField
                      v-model="tarjetaIni"
                      label="Primeros 4 Dígitos"
                      maxlength="4"
                      variant="outlined"
                      density="comfortable"
                      prepend-inner-icon="ri-hashtag"
                      placeholder="Ej: 4568"
                    />
                  </VCol>
                  <VCol cols="6">
                    <VTextField
                      v-model="tarjetaFin"
                      label="Ãšltimos 4 Dígitos"
                      maxlength="4"
                      variant="outlined"
                      density="comfortable"
                      prepend-inner-icon="ri-hashtag"
                      placeholder="Ej: 9012"
                    />
                  </VCol>
                </VRow>

                <div class="d-flex gap-4 mt-2">
                  <VRadioGroup v-model="tarjetaTipo" inline density="compact">
                    <VRadio label="Tarjeta de Débito" value="DEBITO" color="warning" />
                    <VRadio label="Tarjeta de Crédito" value="CREDITO" color="warning" />
                  </VRadioGroup>
                </div>
              </div>

              <!-- 3. VISTA QR DINÁMICO SIMPLE BOLIVIA CON CONFIRMACIÓN BANCARIA -->
              <div v-else-if="metodoPago === 'QR'" class="qr-section pa-3 border rounded bg-surface text-center">
                <div class="text-subtitle-1 font-weight-black mb-1 text-info d-flex align-center justify-center gap-1">
                  <VIcon icon="ri-qr-code-line" size="22" />
                  <span>PAGO CON QR DINÁMICO (SIMPLE QR BOLIVIA)</span>
                </div>
                <div class="text-caption mb-2 text-medium-emphasis">
                  Compatible con cualquier banco de Bolivia (BCP, BNB, Ganadero, Unión, FIE, Mercantil, etc.)
                </div>

                <div class="d-flex flex-column align-center justify-center py-2">
                  <div class="qr-canvas-wrapper pa-2 bg-white rounded elevation-2 mb-2" style="border: 2px solid #0284c7;">
                    <canvas ref="qrCanvasRef" />
                  </div>

                  <div class="d-flex align-center justify-center gap-2 mb-1">
                    <span class="text-subtitle-1 font-weight-black text-primary">
                      Monto: Bs. {{ totalCobroBob.toFixed(2) }}
                    </span>
                    <VChip size="small" :color="qrStatus === 'PAGADO' ? 'success' : 'warning'" class="font-weight-bold">
                      {{ qrStatus === 'PAGADO' ? 'CONFIRMADO POR BANCO' : 'Vence en ' + formatTime(qrCountdown) }}
                    </VChip>
                  </div>

                  <div class="text-caption font-mono text-medium-emphasis mb-2">
                    Ref: {{ qrData?.codigo_transaccion || 'Generando...' }}
                  </div>

                  <div v-if="qrStatus === 'ESPERANDO'" class="d-flex align-center gap-2 text-info text-caption font-weight-bold mb-3">
                    <VProgressCircular indeterminate size="16" width="2" color="info" />
                    <span>ESPERANDO NOTIFICACIÓN EN TIEMPO REAL DEL BANCO...</span>
                  </div>

                  <div v-else-if="qrStatus === 'PAGADO'" class="text-success font-weight-black d-flex align-center gap-1 mb-2">
                    <VIcon icon="ri-checkbox-circle-fill" size="24" color="success" />
                    <span>¡PAGO ACREDITADO! Banco: {{ qrBancoConfirmado }} (Ref: {{ qrRefBancaria }})</span>
                  </div>

                  <div class="d-flex gap-2">
                    <VBtn
                      size="small"
                      variant="tonal"
                      color="info"
                      prepend-icon="ri-bank-card-line"
                      :loading="processing"
                      @click="simularPagoBancario"
                    >
                      SIMULAR CONFIRMACIÓN BANCARIA (TEST)
                    </VBtn>
                    <VBtn
                      size="small"
                      variant="outlined"
                      color="secondary"
                      prepend-icon="ri-refresh-line"
                      @click="generarQr"
                    >
                      Regenerar QR
                    </VBtn>
                  </div>
                </div>
              </div>

                            <!-- 4. VISTA PAGO MIXTO / COMBINADO (RÉPLICA RESTOTECH CON SELECCIÓN EXPLÍCITA) -->
              <div v-else-if="metodoPago === 'MIXTO'" class="mixto-section pa-3 border rounded bg-surface">
                <div class="d-flex justify-space-between align-center mb-3">
                  <span class="text-subtitle-1 font-weight-bold text-secondary">
                    División de Pago Mixto (Total: Bs. {{ totalCobroBob.toFixed(2) }})
                  </span>
                  <VChip
                    :color="((parseFloat(montoMixtoEfectivo) || 0) + (parseFloat(montoMixtoDigital) || 0)) >= totalCobroBob ? 'success' : 'warning'"
                    size="small"
                    variant="tonal"
                  >
                    Suma: Bs. {{ ((parseFloat(montoMixtoEfectivo) || 0) + (parseFloat(montoMixtoDigital) || 0)).toFixed(2) }}
                  </VChip>
                </div>

                <!-- 1. Distribución de montos -->
                <VRow>
                  <VCol cols="6">
                    <VTextField
                      :model-value="montoMixtoEfectivo"
                      @update:model-value="onMontoMixtoEfectivoChange"
                      label="1. Parte Efectivo (Bs.)"
                      type="number"
                      step="0.5"
                      variant="outlined"
                      density="comfortable"
                      prefix="Bs."
                      prepend-inner-icon="ri-money-dollar-circle-line"
                    />
                  </VCol>
                  <VCol cols="6">
                    <VTextField
                      :model-value="montoMixtoDigital"
                      @update:model-value="onMontoMixtoDigitalChange"
                      :label="'2. Parte ' + (segundoMetodo === 'QR' ? 'QR' : 'Tarjeta') + ' (Bs.)'"
                      type="number"
                      step="0.5"
                      variant="outlined"
                      density="comfortable"
                      prefix="Bs."
                      :prepend-inner-icon="segundoMetodo === 'QR' ? 'ri-qr-code-line' : 'ri-bank-card-line'"
                    />
                  </VCol>
                </VRow>

                <!-- 2. Selector del Segundo Método de Pago -->
                <div class="mt-2 mb-3">
                  <div class="text-caption font-weight-bold text-medium-emphasis mb-1">
                    SELECCIONAR EL SEGUNDO MÉTODO DE PAGO:
                  </div>
                  <div class="d-flex gap-2">
                    <VBtn
                      :variant="segundoMetodo === 'TARJETA' ? 'flat' : 'outlined'"
                      :color="segundoMetodo === 'TARJETA' ? 'warning' : 'default'"
                      class="flex-grow-1 font-weight-bold"
                      size="small"
                      @click="segundoMetodo = 'TARJETA'"
                    >
                      <VIcon icon="ri-bank-card-line" class="me-1" />
                      💳 Tarjeta POS (F3)
                    </VBtn>
                    <VBtn
                      :variant="segundoMetodo === 'QR' ? 'flat' : 'outlined'"
                      :color="segundoMetodo === 'QR' ? 'info' : 'default'"
                      class="flex-grow-1 font-weight-bold"
                      size="small"
                      @click="segundoMetodo = 'QR'"
                    >
                      <VIcon icon="ri-qr-code-line" class="me-1" />
                      📱 QR Simple (F4)
                    </VBtn>
                  </div>
                </div>

                <!-- 3. Formulario correspondiente al 2do método -->
                <div v-if="segundoMetodo === 'TARJETA'" class="pa-2 border rounded bg-background">
                  <div class="text-caption font-weight-bold mb-1 text-warning">
                    Datos del Voucher POS de Tarjeta (Bs. {{ (parseFloat(montoMixtoDigital) || 0).toFixed(2) }})
                  </div>
                  <VRow dense>
                    <VCol cols="6">
                      <VSelect
                        v-model="tarjetaTipo"
                        :items="['DEBITO', 'CREDITO']"
                        label="Tipo Tarjeta"
                        density="compact"
                        variant="outlined"
                        hide-details
                      />
                    </VCol>
                    <VCol cols="6">
                      <div class="d-flex gap-1 align-center">
                        <VTextField
                          v-model="tarjetaIni"
                          label="1ros 4"
                          placeholder="4568"
                          maxlength="4"
                          density="compact"
                          variant="outlined"
                          hide-details
                        />
                        <span>-****-</span>
                        <VTextField
                          v-model="tarjetaFin"
                          label="Últ 4"
                          placeholder="1234"
                          maxlength="4"
                          density="compact"
                          variant="outlined"
                          hide-details
                        />
                      </div>
                    </VCol>
                  </VRow>
                </div>

                <div v-else-if="segundoMetodo === 'QR'" class="pa-2 border rounded bg-background text-center">
                  <div class="text-caption font-weight-bold mb-1 text-info">
                    Cobro QR Simple Bancario (Bs. {{ (parseFloat(montoMixtoDigital) || 0).toFixed(2) }})
                  </div>
                  <div class="d-flex justify-center align-center gap-2">
                    <VChip size="small" color="info" variant="tonal">
                      Ref: QR-NIGHTPOS-MIXTO
                    </VChip>
                    <VChip size="small" color="success" variant="flat">
                      Confirmación Automática Activa ✅
                    </VChip>
                  </div>
                </div>
              </div>
            </VCol>

            <!-- COLUMNA DERECHA: DATOS FISCALES / RECIBO (_frmFacturacion1) -->
            <VCol cols="12" md="5">
              <VCard variant="outlined" class="pa-3 h-100 bg-surface">
                <!-- Selector de Comprobante: Factura SIAT vs Recibo Interno -->
                <div class="d-flex gap-2 mb-3">
                  <VBtn
                    :variant="tipoComprobante === 'FACTURA' ? 'flat' : 'outlined'"
                    :color="tipoComprobante === 'FACTURA' ? 'primary' : 'secondary'"
                    size="small"
                    class="flex-grow-1 font-weight-bold"
                    @click="tipoComprobante = 'FACTURA'"
                  >
                    <VIcon icon="ri-file-shield-line" class="me-1" />
                    Factura SIAT (F8)
                  </VBtn>
                  <VBtn
                    :variant="tipoComprobante === 'RECIBO' ? 'flat' : 'outlined'"
                    :color="tipoComprobante === 'RECIBO' ? 'primary' : 'secondary'"
                    size="small"
                    class="flex-grow-1 font-weight-bold"
                    @click="tipoComprobante = 'RECIBO'"
                  >
                    <VIcon icon="ri-receipt-line" class="me-1" />
                    Recibo / Nota (F9)
                  </VBtn>
                </div>

                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="text-subtitle-2 font-weight-black d-flex align-center gap-1 text-primary">
                    <VIcon :icon="tipoComprobante === 'FACTURA' ? 'ri-file-paper-2-fill' : 'ri-receipt-fill'" size="18" />
                    <span>{{ tipoComprobante === 'FACTURA' ? 'DATOS FACTURA SIAT' : 'DATOS DE RECIBO / CLIENTE' }}</span>
                  </div>
                  <VBtn
                    size="x-small"
                    variant="tonal"
                    color="primary"
                    class="font-weight-bold"
                    @click="setSinNombre"
                  >
                    Sin Nombre (0)
                  </VBtn>
                </div>

                <!-- Tipo de Documento Radio Selector (_rbTipoCI, _rbTipoNIT) -->
                <div class="mb-2">
                  <label class="text-caption font-weight-bold mb-1 d-block">Tipo de Documento:</label>
                  <VBtnToggle
                    v-model="tipoDocumento"
                    mandatory
                    density="compact"
                    color="primary"
                    variant="outlined"
                    class="w-100"
                  >
                    <VBtn value="NIT" class="flex-grow-1 font-weight-bold">NIT</VBtn>
                    <VBtn value="CI" class="flex-grow-1 font-weight-bold">CI</VBtn>
                    <VBtn value="CEX" class="flex-grow-1 font-weight-bold">CEX</VBtn>
                    <VBtn value="PASAPORTE" class="flex-grow-1 font-weight-bold">PAS</VBtn>
                  </VBtnToggle>
                </div>

                <!-- Nro Documento / NIT -->
                <VTextField
                  v-model="numeroDocumento"
                  label="N° NIT / CI del Cliente"
                  variant="outlined"
                  density="compact"
                  class="mb-2"
                  prepend-inner-icon="ri-hashtag"
                  placeholder="0 o NIT"
                />

                <!-- Razon Social -->
                <VTextField
                  v-model="razonSocial"
                  label="Razón Social / Nombre"
                  variant="outlined"
                  density="compact"
                  class="mb-2"
                  prepend-inner-icon="ri-user-line"
                  placeholder="SIN NOMBRE o Nombre del cliente"
                />

                <!-- Correo con Botones Rápidos (@gmail.com, @hotmail.com) -->
                <VTextField
                  v-model="correo"
                  label="Correo Electrónico (Para envío de factura SIAT)"
                  variant="outlined"
                  density="compact"
                  type="email"
                  class="mb-1"
                  prepend-inner-icon="ri-mail-line"
                  placeholder="cliente@ejemplo.com"
                />

                <!-- Quick Email Shortcuts de RestoTech -->
                <div class="d-flex gap-2 mb-3">
                  <VBtn
                    size="x-small"
                    variant="tonal"
                    color="secondary"
                    @click="appendCorreoDomain('@gmail.com')"
                  >
                    @gmail.com
                  </VBtn>
                  <VBtn
                    size="x-small"
                    variant="tonal"
                    color="secondary"
                    @click="appendCorreoDomain('@hotmail.com')"
                  >
                    @hotmail.com
                  </VBtn>
                </div>

                <!-- Checkbox Imprimir Físico (_chbImprimirFisico) -->
                <div class="border rounded pa-2 mb-2 bg-var-theme-background">
                  <VCheckbox
                    v-model="imprimirFisico"
                    label="Imprimir Ticket Térmico 80mm Directo"
                    color="primary"
                    density="compact"
                    hide-details
                    class="font-weight-bold text-caption"
                  />
                </div>

                <!-- Resumen de Consumo -->
                <div class="items-summary mt-1">
                  <div class="text-caption font-weight-bold text-medium-emphasis mb-1">
                    Productos a Facturar ({{ activeVisita?.detalles?.length || 0 }}):
                  </div>
                  <div class="items-scroll" style="max-height: 120px; overflow-y: auto;">
                    <div
                      v-for="item in (activeVisita?.detalles || [])"
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

        <!-- Botones de Acción Táctiles (F12 Cobro Directo e Inmediato) -->
        <VCardActions class="pa-4 bg-surface border-t d-flex justify-space-between align-center flex-wrap gap-2">
          <VBtn
            variant="outlined"
            color="secondary"
            size="large"
            prepend-icon="ri-close-circle-line"
            @click="closeModal"
          >
            Cancelar (ESC)
          </VBtn>

          <VBtn
            color="success"
            variant="flat"
            size="x-large"
            prepend-icon="ri-printer-fill"
            :loading="processing"
            :disabled="!isValid"
            class="px-8 font-weight-black text-h6 letter-spacing-1 elevation-3"
            @click="submitCobro"
          >
            COBRAR E IMPRIMIR FACTURA (F12)
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.cobro-modal-card {
  border-radius: 12px;
  overflow: hidden;
}

.touch-keypad {
  user-select: none;
}

.touch-keypad .v-btn {
  height: 48px;
  border-radius: 8px;
}

.qr-canvas-wrapper canvas {
  display: block;
  margin: 0 auto;
}
</style>