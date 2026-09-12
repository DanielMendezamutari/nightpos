<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useComandaStore } from '@/stores/comanda'
import CobroFacturacionModal from '@/components/pos/CobroFacturacionModal.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  mesa: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'cerrado', 'mesa-liberada'])

const comandaStore = useComandaStore()

const activeTab = ref('items') // 'items' | 'iguales' | 'parcial'
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Dialog Cobro Subcuenta
const dialogCobro = ref(false)
const subcuentaParaCobro = ref(null)

// Split Nx
const personasSplit = ref(3)

// Pago Parcial form
const formParcial = reactive({
  monto: null,
  metodo_pago: 'EFECTIVO',
  notas: '',
})

watch(
  () => props.modelValue,
  async (isOpen) => {
    if (isOpen && props.mesa?.id) {
      errorMessage.value = ''
      successMessage.value = ''
      await cargarDatos()
    }
  }
)

const cargarDatos = async () => {
  if (!props.mesa?.id) return
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await comandaStore.fetchSubcuentas(props.mesa.id)
    if (!res.success) {
      errorMessage.value = res.message || 'Error cargando subcuentas'
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message || 'Error al conectar'
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
  emit('cerrado')
}

// Crear nueva subcuenta
const agregarNuevaSubcuenta = async () => {
  const nombre = prompt('Ingrese nombre para la nueva cuenta (Ej. Persona 2, Carlos, etc.):')
  if (nombre === null) return // Cancelo

  loading.value = true
  try {
    const res = await comandaStore.crearSubcuenta(props.mesa.id, nombre.trim())
    if (res.success) {
      successMessage.value = res.message
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message
  } finally {
    loading.value = false
  }
}

// Mover producto a otra subcuenta
const moverItem = async (detalle, subDestinoId) => {
  if (!subDestinoId) return

  let cantidad = detalle.cantidad
  if (detalle.cantidad > 1) {
    const cantInput = prompt(`¿Cuántas unidades de "${detalle.producto_nombre}" desea transferir? (Disponible: ${detalle.cantidad}):`, detalle.cantidad)
    if (!cantInput) return
    const cantNum = parseFloat(cantInput)
    if (isNaN(cantNum) || cantNum <= 0 || cantNum > detalle.cantidad) {
      alert('Cantidad inválida')
      return
    }
    cantidad = cantNum
  }

  loading.value = true
  try {
    const res = await comandaStore.moverItemSubcuenta(props.mesa.id, {
      detalle_id: detalle.id,
      subcuenta_destino_id: subDestinoId,
      cantidad: cantidad,
    })
    if (res.success) {
      successMessage.value = 'Producto transferido'
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message
  } finally {
    loading.value = false
  }
}

// Juntar Cuentas (juntarCuentas de RestoTech)
const juntarTodasLasCuentas = async () => {
  if (!confirm('¿Confirma juntar y reunificar todas las subcuentas pendientes en la Cuenta Principal?')) return

  loading.value = true
  try {
    const res = await comandaStore.juntarCuentas(props.mesa.id)
    if (res.success) {
      successMessage.value = res.message
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message
  } finally {
    loading.value = false
  }
}

// Dividir en partes iguales
const ejecutarSplitNx = async () => {
  if (!personasSplit.value || personasSplit.value < 2) return

  loading.value = true
  try {
    const res = await comandaStore.dividirEnPartesIguales(props.mesa.id, parseInt(personasSplit.value))
    if (res.success) {
      successMessage.value = res.message
      activeTab.value = 'items'
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message
  } finally {
    loading.value = false
  }
}

// Registrar Pago Parcial
const guardarPagoParcial = async () => {
  if (!formParcial.monto || formParcial.monto <= 0) {
    errorMessage.value = 'Ingrese un monto válido a abonar'
    return
  }

  loading.value = true
  try {
    const res = await comandaStore.registrarPagoParcial(props.mesa.id, {
      monto: parseFloat(formParcial.monto),
      metodo_pago: formParcial.metodo_pago,
      notas: formParcial.notas?.trim() || null,
    })

    if (res.success) {
      successMessage.value = res.message
      formParcial.monto = null
      formParcial.notas = ''
      await cargarDatos()
    }
  } catch (err) {
    errorMessage.value = err.data?.message || err.message
  } finally {
    loading.value = false
  }
}

// Abrir cobro para subcuenta específica
const abrirCobroSubcuenta = (sub) => {
  subcuentaParaCobro.value = sub
  dialogCobro.value = true
}

// Cobro exitoso de subcuenta
const onCobroSubcuentaExitoso = async (data) => {
  dialogCobro.value = false
  subcuentaParaCobro.value = null
  successMessage.value = 'Subcuenta cobrada exitosamente'
  await cargarDatos()

  if (data?.mesa_liberada || comandaStore.subcuentasData?.saldo_pendiente === 0) {
    emit('mesa-liberada', props.mesa)
    setTimeout(() => {
      closeModal()
    }, 1500)
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="1100"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="rounded-lg elevation-4">
      <!-- CABECERA -->
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-split-cells-vertical" size="24" />
          <div>
            <div class="text-h6 font-weight-bold">
              Separar & Dividir Cuenta — Mesa #{{ mesa?.codigo || mesa?.nombre || mesa?.id }}
            </div>
            <div class="text-caption text-white-opacity-80">
              RestoTech frmSepararCuentas & Split Bill Multicomensal
            </div>
          </div>
        </div>
        <VBtn icon variant="text" color="white" @click="closeModal">
          <VIcon icon="ri-close-line" />
        </VBtn>
      </VCardTitle>

      <!-- BARRA DE RESUMEN DE SALDOS -->
      <VCardText class="pa-4 bg-surface-variant border-b">
        <VRow dense align="center">
          <VCol cols="12" sm="4">
            <div class="text-caption text-medium-emphasis">Total Consumo Mesa</div>
            <div class="text-h5 font-weight-black text-primary">
              Bs. {{ (comandaStore.subcuentasData?.total_visita || 0).toFixed(2) }}
            </div>
          </VCol>
          <VCol cols="12" sm="4">
            <div class="text-caption text-medium-emphasis">Monto Pagado / Abonado</div>
            <div class="text-h5 font-weight-black text-success">
              Bs. {{ (comandaStore.subcuentasData?.total_pagado || 0).toFixed(2) }}
            </div>
          </VCol>
          <VCol cols="12" sm="4">
            <div class="text-caption text-medium-emphasis">Saldo Pendiente por Cobrar</div>
            <div
              class="text-h5 font-weight-black"
              :class="(comandaStore.subcuentasData?.saldo_pendiente || 0) === 0 ? 'text-success' : 'text-error'"
            >
              Bs. {{ (comandaStore.subcuentasData?.saldo_pendiente || 0).toFixed(2) }}
            </div>
          </VCol>
        </VRow>
      </VCardText>

      <!-- PESTAÑAS -->
      <VTabs v-model="activeTab" class="border-b bg-surface px-4">
        <VTab value="items">
          <VIcon icon="ri-list-check-2" class="me-1" />
          Separar por Productos (frmSepararCuentas)
        </VTab>
        <VTab value="iguales">
          <VIcon icon="ri-pie-chart-2-line" class="me-1" />
          Partes Iguales (Split Nx)
        </VTab>
        <VTab value="parcial">
          <VIcon icon="ri-hand-coin-line" class="me-1" />
          Abono / Pago Parcial (frmPagoParcial)
        </VTab>
      </VTabs>

      <!-- CUERPO DE PESTAÑAS -->
      <VCardText class="pa-4 pt-5">
        <VAlert v-if="errorMessage" type="error" variant="tonal" density="compact" class="mb-4 font-weight-medium" closable @click:close="errorMessage = ''">
          {{ errorMessage }}
        </VAlert>
        <VAlert v-if="successMessage" type="success" variant="tonal" density="compact" class="mb-4 font-weight-medium" closable @click:close="successMessage = ''">
          {{ successMessage }}
        </VAlert>

        <!-- TAB 1: SEPARAR POR PRODUCTOS -->
        <div v-if="activeTab === 'items'">
          <div class="d-flex align-center justify-space-between flex-wrap gap-2 mb-4">
            <div class="text-body-2 text-medium-emphasis">
              Mueva productos entre comensales. Cada cuenta puede cobrarse de forma independiente.
            </div>
            <div class="d-flex gap-2">
              <VBtn
                color="secondary"
                variant="outlined"
                size="small"
                prepend-icon="ri-git-merge-line"
                :disabled="loading || (comandaStore.subcuentasData?.subcuentas?.length || 0) <= 1"
                @click="juntarTodasLasCuentas"
              >
                Juntar Cuentas (Restaurar)
              </VBtn>
              <VBtn
                color="primary"
                variant="elevated"
                size="small"
                prepend-icon="ri-user-add-line"
                :loading="loading"
                @click="agregarNuevaSubcuenta"
              >
                + Nueva Cuenta Comensal
              </VBtn>
            </div>
          </div>

          <!-- GRID HORIZONTAL DE SUBCUENTAS -->
          <div class="d-flex gap-4 overflow-x-auto pb-4">
            <VCard
              v-for="sub in comandaStore.subcuentasData?.subcuentas"
              :key="sub.id"
              variant="outlined"
              class="flex-grow-1 flex-shrink-0 rounded-lg elevation-1"
              style="min-width: 320px; max-width: 380px;"
              :class="{ 'border-success': sub.estado === 'COBRADA' }"
            >
              <!-- Cabecera de la Subcuenta -->
              <VCardItem class="py-3 px-4" :class="sub.estado === 'COBRADA' ? 'bg-success-lighten-5' : 'bg-primary-lighten-5'">
                <div class="d-flex align-center justify-space-between w-100">
                  <div>
                    <h4 class="text-subtitle-1 font-weight-bold mb-0">{{ sub.nombre_comensal }}</h4>
                    <span class="text-caption text-medium-emphasis">Cuenta #{{ sub.numero_subcuenta }}</span>
                  </div>
                  <VChip
                    size="x-small"
                    :color="sub.estado === 'COBRADA' ? 'success' : 'warning'"
                    class="font-weight-bold"
                  >
                    {{ sub.estado === 'COBRADA' ? '✓ PAGADA' : 'PENDIENTE' }}
                  </VChip>
                </div>
              </VCardItem>

              <!-- Lista de Items en esta Subcuenta -->
              <VCardText class="pa-3" style="min-height: 200px; max-height: 360px; overflow-y: auto;">
                <div v-if="!sub.detalles || sub.detalles.length === 0" class="text-center py-8 text-medium-emphasis">
                  <VIcon icon="ri-inbox-line" size="28" class="text-disabled mb-1" />
                  <div class="text-caption">Sin productos asignados</div>
                </div>

                <div
                  v-for="item in sub.detalles"
                  :key="item.id"
                  class="d-flex align-center justify-space-between py-2 border-b"
                >
                  <div class="pe-2">
                    <div class="text-body-2 font-weight-bold">{{ item.producto_nombre }}</div>
                    <div class="text-caption text-medium-emphasis">
                      {{ item.cantidad }} x Bs. {{ parseFloat(item.precio_unitario).toFixed(2) }}
                    </div>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <strong class="text-body-2 text-primary font-weight-black">
                      Bs. {{ parseFloat(item.subtotal).toFixed(2) }}
                    </strong>

                    <!-- Menu de transferencia a otra subcuenta -->
                    <VMenu v-if="sub.estado !== 'COBRADA' && (comandaStore.subcuentasData?.subcuentas?.length || 0) > 1">
                      <template #activator="{ props: menuProps }">
                        <VBtn icon size="x-small" variant="text" color="primary" v-bind="menuProps">
                          <VIcon icon="ri-arrow-right-circle-line" size="18" />
                        </VBtn>
                      </template>
                      <VList density="compact">
                        <VListSubheader>Mover a...</VListSubheader>
                        <VListItem
                          v-for="otra in comandaStore.subcuentasData?.subcuentas.filter(s => s.id !== sub.id && s.estado !== 'COBRADA')"
                          :key="otra.id"
                          @click="moverItem(item, otra.id)"
                        >
                          <VListItemTitle>{{ otra.nombre_comensal }}</VListItemTitle>
                        </VListItem>
                      </VList>
                    </VMenu>
                  </div>
                </div>
              </VCardText>

              <!-- Pie de la Subcuenta -->
              <VDivider />
              <VCardActions class="pa-3 bg-surface d-flex flex-column align-stretch gap-2">
                <div class="d-flex justify-space-between align-center">
                  <span class="text-caption font-weight-bold text-uppercase">Total Cuenta:</span>
                  <span class="text-h6 font-weight-black text-primary">
                    Bs. {{ (sub.total || 0).toFixed(2) }}
                  </span>
                </div>

                <VBtn
                  v-if="sub.estado !== 'COBRADA'"
                  color="success"
                  variant="elevated"
                  size="small"
                  class="font-weight-bold w-100"
                  prepend-icon="ri-secure-payment-line"
                  :disabled="sub.total <= 0"
                  @click="abrirCobroSubcuenta(sub)"
                >
                  Cobrar Cuenta (Bs. {{ (sub.total || 0).toFixed(2) }})
                </VBtn>
                <div v-else class="text-caption text-center text-success font-weight-bold py-1">
                  <VIcon icon="ri-check-double-line" start size="16" />
                  Factura / Recibo emitido
                </div>
              </VCardActions>
            </VCard>
          </div>
        </div>

        <!-- TAB 2: PARTES IGUALES (SPLIT NX) -->
        <div v-if="activeTab === 'iguales'" class="py-4 max-w-700 mx-auto text-center">
          <VAvatar color="primary" variant="tonal" size="64" class="mb-3">
            <VIcon icon="ri-pie-chart-2-line" size="36" color="primary" />
          </VAvatar>
          <h3 class="text-h6 font-weight-bold mb-1">División Equitativa de la Mesa</h3>
          <p class="text-body-2 text-medium-emphasis mb-6">
            Divida el total de <strong>Bs. {{ (comandaStore.subcuentasData?.total_visita || 0).toFixed(2) }}</strong> en partes iguales para que cada persona pague su cuota exacta.
          </p>

          <VRow justify="center" class="mb-4">
            <VCol cols="12" sm="8">
              <label class="text-caption font-weight-bold mb-2 d-block">Número de Comensales:</label>
              <VBtnToggle
                v-model="personasSplit"
                mandatory
                color="primary"
                variant="outlined"
                density="comfortable"
                class="w-100 mb-4"
              >
                <VBtn :value="2" class="flex-grow-1">2 Personas</VBtn>
                <VBtn :value="3" class="flex-grow-1">3 Personas</VBtn>
                <VBtn :value="4" class="flex-grow-1">4 Personas</VBtn>
                <VBtn :value="5" class="flex-grow-1">5 Personas</VBtn>
                <VBtn :value="6" class="flex-grow-1">6 Personas</VBtn>
              </VBtnToggle>

              <VCard variant="tonal" color="primary" class="pa-4 rounded-lg mb-4">
                <div class="text-caption font-weight-bold text-uppercase">Cada persona pagará:</div>
                <div class="text-h4 font-weight-black my-1">
                  Bs. {{ ((comandaStore.subcuentasData?.total_visita || 0) / personasSplit).toFixed(2) }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  Se generarán {{ personasSplit }} cuentas individuales con recibo/factura independiente.
                </div>
              </VCard>

              <VBtn
                color="primary"
                size="large"
                variant="elevated"
                class="font-weight-bold px-8"
                prepend-icon="ri-magic-line"
                :loading="loading"
                @click="ejecutarSplitNx"
              >
                Generar {{ personasSplit }} Cuentas Iguales
              </VBtn>
            </VCol>
          </VRow>
        </div>

        <!-- TAB 3: PAGO PARCIAL / ABONO A MESA (frmPagoParcial) -->
        <div v-if="activeTab === 'parcial'" class="py-2">
          <VRow>
            <VCol cols="12" md="6">
              <VCard variant="outlined" class="pa-4 rounded-lg">
                <h4 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center gap-1">
                  <VIcon icon="ri-hand-coin-line" color="primary" />
                  <span>Registrar Abono a la Mesa (frmPagoParcial)</span>
                </h4>

                <VTextField
                  v-model.number="formParcial.monto"
                  label="Monto a Abonar (Bs.)"
                  prefix="Bs."
                  type="number"
                  step="1.00"
                  variant="outlined"
                  density="comfortable"
                  class="mb-3 font-weight-bold"
                  placeholder="0.00"
                  :max="comandaStore.subcuentasData?.saldo_pendiente"
                />

                <VSelect
                  v-model="formParcial.metodo_pago"
                  label="Medio de Pago"
                  :items="[
                    { title: '💵 Efectivo', value: 'EFECTIVO' },
                    { title: '📱 QR Digital', value: 'QR' },
                    { title: '💳 Tarjeta Débito/Crédito', value: 'TARJETA' },
                  ]"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="comfortable"
                  class="mb-3"
                />

                <VTextField
                  v-model="formParcial.notas"
                  label="Notas / Nombre de quien abona"
                  placeholder="Ej. Abono Juan Pérez antes de retirarse"
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                />

                <VBtn
                  color="success"
                  variant="elevated"
                  class="font-weight-bold w-100"
                  prepend-icon="ri-check-line"
                  :loading="loading"
                  @click="guardarPagoParcial"
                >
                  Registrar Abono de Bs. {{ parseFloat(formParcial.monto || 0).toFixed(2) }}
                </VBtn>
              </VCard>
            </VCol>

            <!-- Historial de Abonos Parciales -->
            <VCol cols="12" md="6">
              <VCard variant="outlined" class="pa-4 rounded-lg h-100">
                <h4 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center gap-1">
                  <VIcon icon="ri-history-line" color="secondary" />
                  <span>Historial de Abonos Registrados</span>
                </h4>

                <VTable density="compact" class="border rounded">
                  <thead>
                    <tr>
                      <th>Hora</th>
                      <th>Método</th>
                      <th>Comprobante</th>
                      <th class="text-right">Monto</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="!comandaStore.subcuentasData?.pagos_parciales || comandaStore.subcuentasData?.pagos_parciales.length === 0">
                      <td colspan="4" class="text-center py-4 text-medium-emphasis">
                        No hay abonos parciales registrados en esta mesa
                      </td>
                    </tr>
                    <tr v-for="p in comandaStore.subcuentasData?.pagos_parciales" :key="p.id">
                      <td>{{ p.created_at?.split('T')[1]?.substring(0, 8) || p.created_at }}</td>
                      <td>
                        <VChip size="x-small" color="primary" variant="tonal">{{ p.metodo_pago }}</VChip>
                      </td>
                      <td><code>{{ p.comprobante_nro }}</code></td>
                      <td class="text-right font-weight-bold text-success">
                        Bs. {{ parseFloat(p.monto).toFixed(2) }}
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </VCard>
            </VCol>
          </VRow>
        </div>
      </VCardText>

      <!-- PIE -->
      <VDivider />
      <VCardActions class="pa-4 bg-surface d-flex justify-end">
        <VBtn variant="tonal" color="default" @click="closeModal">
          Cerrar
        </VBtn>
      </VCardActions>
    </VCard>

    <!-- Modal Cobro de Subcuenta Especializado -->
    <CobroFacturacionModal
      v-if="dialogCobro"
      v-model="dialogCobro"
      :mesa="mesa"
      :visita="{
        id: comandaStore.subcuentasData?.visita_id,
        total: subcuentaParaCobro?.total,
        detalles: subcuentaParaCobro?.detalles || [],
        subcuenta_id: subcuentaParaCobro?.id,
        nombre_comensal: subcuentaParaCobro?.nombre_comensal,
      }"
      @cobro-exitoso="onCobroSubcuentaExitoso"
    />
  </VDialog>
</template>

<style scoped>
.max-w-700 {
  max-width: 700px;
}
</style>
