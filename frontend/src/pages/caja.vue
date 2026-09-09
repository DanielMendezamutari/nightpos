<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCajaStore } from '@/stores/caja'
import { useAuthStore } from '@/stores/auth'

const cajaStore = useCajaStore()
const authStore = useAuthStore()

// Active tab
const activeTab = ref('resumen')

// Apertura form
const formApertura = ref({
  monto_inicial_bs: 0,
  monto_inicial_usd: 0,
  observaciones: '',
})

// Movimiento form & dialog
const dialogMovimiento = ref(false)
const formMovimiento = ref({
  tipo: 'EGRESO_GASTO',
  monto: '',
  motivo: '',
  comprobante_nro: '',
})

// Cierre form
const formCierre = ref({
  monto_final_bs: '',
  monto_final_usd: 0,
  observaciones: '',
})

const loadingAction = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

onMounted(async () => {
  await loadCajaData()
  if (cajaStore.isTurnoAbierto) {
    formCierre.value.monto_final_bs = cajaStore.efectivoEsperado.toFixed(2)
  }
})

const loadCajaData = async () => {
  await cajaStore.fetchTurnoActivo()
  if (cajaStore.isTurnoAbierto) {
    await cajaStore.fetchMovimientos()
    await cajaStore.fetchFacturas()
  }
}

// Difference calculation for Cierre
const diferenciaCalculada = computed(() => {
  const contado = parseFloat(formCierre.value.monto_final_bs) || 0
  const esperado = cajaStore.efectivoEsperado
  return Math.round((contado - esperado) * 100) / 100
})

// Open Shift
const submitApertura = async () => {
  loadingAction.value = true
  errorMessage.value = ''
  successMessage.value = ''

  const res = await cajaStore.abrirTurno({
    monto_inicial_bs: parseFloat(formApertura.value.monto_inicial_bs) || 0,
    monto_inicial_usd: parseFloat(formApertura.value.monto_inicial_usd) || 0,
    observaciones: formApertura.value.observaciones,
  })

  loadingAction.value = false

  if (res.success) {
    successMessage.value = 'Turno abierto exitosamente'
    await loadCajaData()
    formCierre.value.monto_final_bs = cajaStore.efectivoEsperado.toFixed(2)
  } else {
    errorMessage.value = res.message || 'Error al abrir turno'
  }
}

// Register Movement
const submitMovimiento = async () => {
  if (!formMovimiento.value.monto || !formMovimiento.value.motivo) {
    alert('Por favor ingrese el monto y motivo del movimiento')
    return
  }

  loadingAction.value = true
  const res = await cajaStore.registrarMovimiento({
    tipo: formMovimiento.value.tipo,
    monto: parseFloat(formMovimiento.value.monto),
    motivo: formMovimiento.value.motivo,
    comprobante_nro: formMovimiento.value.comprobante_nro || null,
  })
  loadingAction.value = false

  if (res.success) {
    dialogMovimiento.value = false
    formMovimiento.value = {
      tipo: 'EGRESO_GASTO',
      monto: '',
      motivo: '',
      comprobante_nro: '',
    }
    successMessage.value = 'Movimiento registrado correctamente'
    formCierre.value.monto_final_bs = cajaStore.efectivoEsperado.toFixed(2)
  } else {
    alert(res.message || 'Error al registrar movimiento')
  }
}

// Close Shift
const submitCierre = async () => {
  if (!confirm(`¿Confirmar arqueo y cierre del turno actual? El sistema registrará una diferencia de Bs. ${diferenciaCalculada.value.toFixed(2)}.`)) {
    return
  }

  loadingAction.value = true
  errorMessage.value = ''

  const res = await cajaStore.cerrarTurno({
    turno_id: cajaStore.turnoActivo.id,
    monto_final_bs: parseFloat(formCierre.value.monto_final_bs) || 0,
    monto_final_usd: parseFloat(formCierre.value.monto_final_usd) || 0,
    observaciones: formCierre.value.observaciones,
  })

  loadingAction.value = false

  if (res.success) {
    successMessage.value = 'Turno cerrado y arqueado correctamente'
  } else {
    errorMessage.value = res.message || 'Error al cerrar turno'
  }
}

// Anular Factura
const handleAnularFactura = async (factura) => {
  const motivo = prompt(`Ingrese motivo para anular la Factura N° ${factura.nro_factura}:`)
  if (!motivo) return

  const res = await cajaStore.anularFactura(factura.id, motivo)
  if (res.success) {
    alert('Factura anulada con éxito')
    formCierre.value.monto_final_bs = cajaStore.efectivoEsperado.toFixed(2)
  } else {
    alert(res.message || 'Error al anular factura')
  }
}
</script>

<template>
  <div class="caja-page-container">
    <!-- Breadcrumbs / Top Header -->
    <div class="d-flex align-center justify-space-between flex-wrap gap-2 mb-4">
      <div>
        <h2 class="text-h5 font-weight-bold mb-0">Control de Caja & Arqueo de Turnos</h2>
        <span class="text-caption text-medium-emphasis">
          RiberResto POS | Gestión de Efectivo, Arqueo Físico y Movimientos de Sucursal
        </span>
      </div>
      <div class="d-flex align-center gap-2">
        <VChip
          :color="cajaStore.isTurnoAbierto ? 'success' : 'warning'"
          variant="elevated"
          class="font-weight-bold"
        >
          <VIcon :icon="cajaStore.isTurnoAbierto ? 'ri-lock-unlock-line' : 'ri-lock-line'" class="me-1" />
          {{ cajaStore.isTurnoAbierto ? `TURNO #${cajaStore.turnoActivo.id} ABIERTO` : 'CAJA CERRADA' }}
        </VChip>
        <VBtn
          icon="ri-refresh-line"
          size="small"
          variant="tonal"
          color="primary"
          :loading="cajaStore.loading"
          @click="loadCajaData"
        />
      </div>
    </div>

    <!-- Messages -->
    <VAlert v-if="successMessage" type="success" variant="tonal" class="mb-4" closable @click:close="successMessage = ''">
      {{ successMessage }}
    </VAlert>
    <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable @click:close="errorMessage = ''">
      {{ errorMessage }}
    </VAlert>

    <!-- VIEW A: CAJA CERRADA (Apertura de Turno) -->
    <VCard v-if="!cajaStore.isTurnoAbierto" class="pa-8 text-center max-w-700 mx-auto elevation-2">
      <VAvatar color="primary" variant="tonal" size="80" class="mb-4">
        <VIcon icon="ri-safe-2-line" size="44" color="primary" />
      </VAvatar>
      <h3 class="text-h5 font-weight-bold mb-2">Apertura de Turno de Caja</h3>
      <p class="text-body-2 text-medium-emphasis mb-6">
        Para habilitar los cobros de comandas y la emisión de facturas, debe registrar el fondo de efectivo inicial en la gaveta.
      </p>

      <VRow dense class="text-left mb-4">
        <VCol cols="12" md="6">
          <VTextField
            v-model="formApertura.monto_inicial_bs"
            label="Fondo Inicial (Bs.)"
            prefix="Bs."
            type="number"
            variant="outlined"
            density="comfortable"
            class="text-h6 font-weight-bold"
          />
        </VCol>
        <VCol cols="12" md="6">
          <VTextField
            v-model="formApertura.monto_inicial_usd"
            label="Fondo Inicial ($US - Opcional)"
            prefix="$"
            type="number"
            variant="outlined"
            density="comfortable"
          />
        </VCol>
        <VCol cols="12">
          <VTextField
            v-model="formApertura.observaciones"
            label="Observaciones de Apertura"
            variant="outlined"
            density="comfortable"
            placeholder="Ej. Apertura turno mañana - Responsable de caja"
          />
        </VCol>
      </VRow>

      <VBtn
        color="primary"
        size="large"
        variant="elevated"
        prepend-icon="ri-key-line"
        :loading="loadingAction"
        class="px-8 font-weight-bold"
        @click="submitApertura"
      >
        ABRIR TURNO DE CAJA
      </VBtn>
    </VCard>

    <!-- VIEW B: TURNO ABIERTO -->
    <div v-else>
      <!-- Shift Info Bar -->
      <VCard class="mb-4 elevation-1">
        <VCardText class="py-3 px-4">
          <div class="d-flex align-center justify-space-between flex-wrap gap-3">
            <div>
              <span class="text-caption font-weight-bold text-medium-emphasis">CAJERO:</span>
              <strong class="ms-1">{{ cajaStore.turnoActivo?.cajero_nombre || 'Cajero' }}</strong>
            </div>
            <div>
              <span class="text-caption font-weight-bold text-medium-emphasis">FECHA DE APERTURA:</span>
              <strong class="ms-1">{{ cajaStore.turnoActivo?.fecha_inicio }}</strong>
            </div>
            <div>
              <span class="text-caption font-weight-bold text-medium-emphasis">FONDO INICIAL:</span>
              <strong class="ms-1 text-primary">Bs. {{ parseFloat(cajaStore.turnoActivo?.monto_inicial_bs || 0).toFixed(2) }}</strong>
            </div>
            <div>
              <span class="text-caption font-weight-bold text-medium-emphasis">EFECTIVO EN GAVETA:</span>
              <strong class="ms-1 text-success text-h6 font-weight-black">Bs. {{ parseFloat(cajaStore.turnoActivo?.efectivo_esperado || 0).toFixed(2) }}</strong>
            </div>
          </div>
        </VCardText>
      </VCard>

      <!-- 6 Financial KPI Cards (RestoTech faithful) -->
      <VRow class="mb-4 match-height">
        <VCol cols="6" sm="4" md="2">
          <VCard variant="tonal" color="success" class="text-center pa-3 h-100">
            <div class="text-caption font-weight-bold">Ventas Efectivo</div>
            <div class="text-h6 font-weight-black">
              Bs. {{ parseFloat(cajaStore.turnoActivo?.total_ventas_efectivo || 0).toFixed(2) }}
            </div>
          </VCard>
        </VCol>

        <VCol cols="6" sm="4" md="2">
          <VCard variant="tonal" color="info" class="text-center pa-3 h-100">
            <div class="text-caption font-weight-bold">Ventas QR</div>
            <div class="text-h6 font-weight-black">
              Bs. {{ parseFloat(cajaStore.turnoActivo?.total_ventas_qr || 0).toFixed(2) }}
            </div>
          </VCard>
        </VCol>

        <VCol cols="6" sm="4" md="2">
          <VCard variant="tonal" color="warning" class="text-center pa-3 h-100">
            <div class="text-caption font-weight-bold">Ventas Tarjeta</div>
            <div class="text-h6 font-weight-black">
              Bs. {{ parseFloat(cajaStore.turnoActivo?.total_ventas_tarjeta || 0).toFixed(2) }}
            </div>
          </VCard>
        </VCol>

        <VCol cols="6" sm="4" md="2">
          <VCard variant="tonal" color="error" class="text-center pa-3 h-100">
            <div class="text-caption font-weight-bold">Gastos / Egresos</div>
            <div class="text-h6 font-weight-black">
              Bs. {{ parseFloat(cajaStore.turnoActivo?.total_gastos || 0).toFixed(2) }}
            </div>
          </VCard>
        </VCol>

        <VCol cols="6" sm="4" md="2">
          <VCard variant="tonal" color="primary" class="text-center pa-3 h-100">
            <div class="text-caption font-weight-bold">Total Facturado</div>
            <div class="text-h6 font-weight-black">
              Bs. {{ parseFloat(cajaStore.turnoActivo?.total_ventas || 0).toFixed(2) }}
            </div>
          </VCard>
        </VCol>

        <VCol cols="6" sm="4" md="2">
          <VCard variant="flat" color="primary" class="text-center pa-3 h-100 text-white">
            <div class="text-caption font-weight-bold text-white">Efectivo en Caja</div>
            <div class="text-h6 font-weight-black text-white">
              Bs. {{ parseFloat(cajaStore.turnoActivo?.efectivo_esperado || 0).toFixed(2) }}
            </div>
          </VCard>
        </VCol>
      </VRow>

      <!-- Navigation Tabs -->
      <VCard class="elevation-1">
        <VTabs v-model="activeTab" class="v-tabs-pill border-b pa-2">
          <VTab value="resumen">
            <VIcon icon="ri-calculator-line" class="me-1" />
            Arqueo & Cierre de Turno
          </VTab>
          <VTab value="gastos">
            <VIcon icon="ri-hand-coin-line" class="me-1" />
            Gastos y Movimientos ({{ cajaStore.movimientos.length }})
          </VTab>
          <VTab value="facturas">
            <VIcon icon="ri-file-list-3-line" class="me-1" />
            Facturas del Turno ({{ cajaStore.facturas.length }})
          </VTab>
        </VTabs>

        <VCardText class="pa-4">
          <!-- TAB 1: ARQUEO Y CIERRE -->
          <div v-if="activeTab === 'resumen'">
            <VRow>
              <VCol cols="12" md="7">
                <VCard variant="outlined" class="pa-4">
                  <h4 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center gap-1">
                    <VIcon icon="ri-money-dollar-box-line" color="primary" />
                    <span>Conteo Físico de Efectivo en Gaveta</span>
                  </h4>

                  <VTextField
                    v-model="formCierre.monto_final_bs"
                    label="Efectivo Total Contado (Bs.)"
                    prefix="Bs."
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    class="mb-3 text-h5 font-weight-bold"
                  />

                  <VTextField
                    v-model="formCierre.observaciones"
                    label="Observaciones del Cierre de Turno"
                    variant="outlined"
                    density="comfortable"
                    placeholder="Ej. Cuadre verificado conforme, sin discrepancias"
                  />
                </VCard>
              </VCol>

              <VCol cols="12" md="5">
                <VCard variant="outlined" class="pa-4 h-100 text-center d-flex flex-column justify-center">
                  <div class="text-caption text-uppercase font-weight-bold mb-1">
                    Balance de Arqueo
                  </div>

                  <div v-if="diferenciaCalculada === 0" class="pa-3 rounded bg-success-subtle mb-3">
                    <div class="text-h4 font-weight-black text-success">Bs. 0.00</div>
                    <span class="text-caption font-weight-bold text-success">âœ“ CUADRE PERFECTO EXACTO</span>
                  </div>

                  <div v-else-if="diferenciaCalculada > 0" class="pa-3 rounded bg-info-subtle mb-3">
                    <div class="text-h4 font-weight-black text-info">+Bs. {{ Math.abs(diferenciaCalculada).toFixed(2) }}</div>
                    <span class="text-caption font-weight-bold text-info">â–² SOBRANTE EN CAJA</span>
                  </div>

                  <div v-else class="pa-3 rounded bg-error-subtle mb-3">
                    <div class="text-h4 font-weight-black text-error">-Bs. {{ Math.abs(diferenciaCalculada).toFixed(2) }}</div>
                    <span class="text-caption font-weight-bold text-error">â–¼ FALTANTE EN CAJA</span>
                  </div>

                  <VBtn
                    color="error"
                    size="large"
                    variant="flat"
                    prepend-icon="ri-lock-2-line"
                    :loading="loadingAction"
                    class="font-weight-bold mt-2"
                    @click="submitCierre"
                  >
                    CERRAR Y ARQUEAR TURNO
                  </VBtn>
                </VCard>
              </VCol>
            </VRow>
          </div>

          <!-- TAB 2: GASTOS Y MOVIMIENTOS -->
          <div v-if="activeTab === 'gastos'">
            <div class="d-flex justify-space-between align-center mb-3">
              <span class="text-subtitle-1 font-weight-bold">
                Movimientos de Entrada y Salida de Efectivo
              </span>
              <VBtn
                color="error"
                variant="elevated"
                size="small"
                prepend-icon="ri-add-circle-line"
                @click="dialogMovimiento = true"
              >
                + Registrar Gasto / Retiro
              </VBtn>
            </div>

            <VTable density="compact" class="border rounded">
              <thead>
                <tr>
                  <th>Hora</th>
                  <th>Tipo</th>
                  <th>Motivo</th>
                  <th>N° Comprobante</th>
                  <th>Usuario</th>
                  <th class="text-right">Monto</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="cajaStore.movimientos.length === 0">
                  <td colspan="6" class="text-center py-4 text-medium-emphasis">
                    No hay movimientos registrados en este turno
                  </td>
                </tr>
                <tr v-for="m in cajaStore.movimientos" :key="m.id">
                  <td>{{ m.created_at?.split(' ')[1] || m.created_at }}</td>
                  <td>
                    <VChip
                      size="x-small"
                      :color="m.tipo === 'INGRESO' ? 'success' : (m.tipo === 'RETIRO_CAJA' ? 'warning' : 'error')"
                      class="font-weight-bold"
                    >
                      {{ m.tipo }}
                    </VChip>
                  </td>
                  <td>{{ m.motivo }}</td>
                  <td>{{ m.comprobante_nro || '-' }}</td>
                  <td>{{ m.usuario_nombre || 'Cajero' }}</td>
                  <td class="text-right font-weight-bold" :class="m.tipo === 'INGRESO' ? 'text-success' : 'text-error'">
                    {{ m.tipo === 'INGRESO' ? '+' : '-' }}Bs. {{ parseFloat(m.monto).toFixed(2) }}
                  </td>
                </tr>
              </tbody>
            </VTable>
          </div>

          <!-- TAB 3: FACTURAS DEL TURNO -->
          <div v-if="activeTab === 'facturas'">
            <VTable density="compact" class="border rounded">
              <thead>
                <tr>
                  <th>N° Factura</th>
                  <th>Fecha/Hora</th>
                  <th>Cliente / Razón Social</th>
                  <th>NIT/CI</th>
                  <th>Método</th>
                  <th>Estado</th>
                  <th class="text-right">Total Bs.</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="cajaStore.facturas.length === 0">
                  <td colspan="8" class="text-center py-4 text-medium-emphasis">
                    No se han emitido facturas en este turno
                  </td>
                </tr>
                <tr v-for="f in cajaStore.facturas" :key="f.id">
                  <td class="font-weight-bold">#{{ f.nro_factura }}</td>
                  <td>{{ f.fecha_emision }}</td>
                  <td>{{ f.razon_social }}</td>
                  <td>{{ f.numero_documento }}</td>
                  <td>
                    <VChip size="x-small" variant="tonal" color="primary">
                      {{ f.metodo_pago }}
                    </VChip>
                  </td>
                  <td>
                    <VChip
                      size="x-small"
                      :color="f.estado === 'VALIDA' ? 'success' : 'error'"
                      class="font-weight-bold"
                    >
                      {{ f.estado }}
                    </VChip>
                  </td>
                  <td class="text-right font-weight-bold">
                    Bs. {{ parseFloat(f.monto_total).toFixed(2) }}
                  </td>
                  <td class="text-center">
                    <VBtn
                      v-if="f.estado === 'VALIDA'"
                      size="x-small"
                      color="error"
                      variant="text"
                      prepend-icon="ri-close-circle-line"
                      @click="handleAnularFactura(f)"
                    >
                      Anular
                    </VBtn>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </div>
        </VCardText>
      </VCard>
    </div>

    <!-- Modal Registrar Gasto / Movimiento -->
    <VDialog v-model="dialogMovimiento" max-width="480">
      <VCard>
        <VCardItem class="bg-error text-white py-2">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="font-weight-bold">Registrar Movimiento / Gasto</span>
            <VIcon icon="ri-money-dollar-circle-line" />
          </div>
        </VCardItem>
        <VCardText class="pa-4">
          <label class="text-caption font-weight-medium mb-1 d-block">Tipo de Movimiento:</label>
          <VBtnToggle
            v-model="formMovimiento.tipo"
            mandatory
            density="compact"
            color="error"
            variant="outlined"
            class="w-100 mb-3"
          >
            <VBtn value="EGRESO_GASTO" class="flex-grow-1">Gasto Operativo</VBtn>
            <VBtn value="RETIRO_CAJA" class="flex-grow-1">Retiro / Arqueo</VBtn>
            <VBtn value="INGRESO" class="flex-grow-1">Ingreso Extra</VBtn>
          </VBtnToggle>

          <VTextField
            v-model="formMovimiento.monto"
            label="Monto (Bs.)"
            prefix="Bs."
            type="number"
            step="0.50"
            variant="outlined"
            density="comfortable"
            class="mb-3 font-weight-bold"
            autofocus
          />

          <VTextField
            v-model="formMovimiento.motivo"
            label="Motivo o Justificación"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            placeholder="Ej. Compra de insumos de cocina urgentes"
          />

          <VTextField
            v-model="formMovimiento.comprobante_nro"
            label="N° Factura / Recibo / Vale"
            variant="outlined"
            density="comfortable"
            placeholder="Opcional"
          />
        </VCardText>
        <VCardActions class="pa-3 bg-surface-variant d-flex justify-space-between">
          <VBtn variant="outlined" @click="dialogMovimiento = false">Cancelar</VBtn>
          <VBtn color="error" variant="flat" :loading="loadingAction" @click="submitMovimiento">
            Guardar Movimiento
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.max-w-700 {
  max-width: 700px;
}
</style>