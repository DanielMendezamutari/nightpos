<script setup>
import { ref, computed, onMounted } from 'vue'
import { useReportesStore } from '@/stores/reportes'

const reportesStore = useReportesStore()

const hoyStr = new Date().toISOString().split('T')[0]
const fechaDesde = ref(hoyStr)
const fechaHasta = ref(hoyStr)
const activeTab = ref('ventas')

const setPreset = (preset) => {
  const hoy = new Date()
  if (preset === 'hoy') {
    fechaDesde.value = hoy.toISOString().split('T')[0]
    fechaHasta.value = hoy.toISOString().split('T')[0]
  } else if (preset === 'ayer') {
    const ayer = new Date()
    ayer.setDate(ayer.getDate() - 1)
    fechaDesde.value = ayer.toISOString().split('T')[0]
    fechaHasta.value = ayer.toISOString().split('T')[0]
  } else if (preset === 'semana') {
    const hace7 = new Date()
    hace7.setDate(hace7.getDate() - 7)
    fechaDesde.value = hace7.toISOString().split('T')[0]
    fechaHasta.value = hoy.toISOString().split('T')[0]
  } else if (preset === 'mes') {
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1)
    fechaDesde.value = primerDia.toISOString().split('T')[0]
    fechaHasta.value = hoy.toISOString().split('T')[0]
  }
  cargarDatos()
}

const cargarDatos = () => {
  reportesStore.fetchTodosReportes(fechaDesde.value, fechaHasta.value)
}

onMounted(() => {
  cargarDatos()
})

const imprimirReporte = () => {
  window.print()
}
</script>

<template>
  <div class="reportes-container">
    <!-- Header & Date Controls -->
    <VCard class="mb-6 elevation-1">
      <VCardText>
        <div class="d-flex flex-wrap align-center justify-space-between gap-4">
          <div>
            <h1 class="text-h4 font-weight-bold text-primary d-flex align-center gap-2 mb-1">
              <VIcon icon="ri-bar-chart-grouped-line" size="32" />
              Reportes Gerenciales & Analítica
            </h1>
            <span class="text-subtitle-2 text-medium-emphasis">
              RiberResto POS — Paridad Total RestoTech por Ribersoft
            </span>
          </div>

          <div class="d-flex flex-wrap align-center gap-2">
            <VBtn
              size="small"
              variant="tonal"
              color="primary"
              prepend-icon="ri-calendar-event-line"
              @click="setPreset('hoy')"
            >
              Hoy
            </VBtn>
            <VBtn
              size="small"
              variant="tonal"
              color="secondary"
              @click="setPreset('ayer')"
            >
              Ayer
            </VBtn>
            <VBtn
              size="small"
              variant="tonal"
              color="secondary"
              @click="setPreset('semana')"
            >
              Últimos 7 días
            </VBtn>
            <VBtn
              size="small"
              variant="tonal"
              color="secondary"
              @click="setPreset('mes')"
            >
              Este Mes
            </VBtn>

            <VTextField
              v-model="fechaDesde"
              type="date"
              density="compact"
              label="Desde"
              style="width: 150px"
              hide-details
              @change="cargarDatos"
            />
            <VTextField
              v-model="fechaHasta"
              type="date"
              density="compact"
              label="Hasta"
              style="width: 150px"
              hide-details
              @change="cargarDatos"
            />

            <VBtn
              color="primary"
              prepend-icon="ri-refresh-line"
              :loading="reportesStore.loading"
              @click="cargarDatos"
            >
              Actualizar
            </VBtn>

            <VBtn
              variant="outlined"
              color="secondary"
              prepend-icon="ri-printer-line"
              @click="imprimirReporte"
            >
              Imprimir
            </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- KPI Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard color="primary" variant="tonal" class="h-100">
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase font-weight-medium">Ventas Totales</div>
              <div class="text-h4 font-weight-bold text-primary mt-1">
                Bs. {{ Number(reportesStore.resumenVentas.total_ventas).toFixed(2) }}
              </div>
              <div class="text-caption mt-1">
                {{ reportesStore.resumenVentas.total_transacciones }} operaciones
              </div>
            </div>
            <VAvatar color="primary" size="48" variant="flat">
              <VIcon icon="ri-money-dollar-circle-line" size="28" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard color="info" variant="tonal" class="h-100">
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase font-weight-medium">Ticket Promedio</div>
              <div class="text-h4 font-weight-bold text-info mt-1">
                Bs. {{ Number(reportesStore.resumenVentas.ticket_promedio).toFixed(2) }}
              </div>
              <div class="text-caption mt-1">Por consumo de mesa/orden</div>
            </div>
            <VAvatar color="info" size="48" variant="flat">
              <VIcon icon="ri-receipt-line" size="28" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard color="success" variant="tonal" class="h-100">
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase font-weight-medium">Facturado SIAT</div>
              <div class="text-h4 font-weight-bold text-success mt-1">
                Bs. {{ Number(reportesStore.resumenVentas.total_facturado).toFixed(2) }}
              </div>
              <div class="text-caption mt-1">
                Recibos: Bs. {{ Number(reportesStore.resumenVentas.total_recibos).toFixed(2) }}
              </div>
            </div>
            <VAvatar color="success" size="48" variant="flat">
              <VIcon icon="ri-shield-check-line" size="28" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard
          :color="reportesStore.balanceFinanciero.utilidad_operativa_bruta >= 0 ? 'success' : 'error'"
          variant="tonal"
          class="h-100"
        >
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase font-weight-medium">Utilidad Estimada</div>
              <div class="text-h4 font-weight-bold mt-1">
                Bs. {{ Number(reportesStore.balanceFinanciero.utilidad_operativa_bruta).toFixed(2) }}
              </div>
              <div class="text-caption mt-1">Ventas - Gastos - Insumos</div>
            </div>
            <VAvatar
              :color="reportesStore.balanceFinanciero.utilidad_operativa_bruta >= 0 ? 'success' : 'error'"
              size="48"
              variant="flat"
            >
              <VIcon icon="ri-scales-3-line" size="28" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Main Navigation Tabs -->
    <VCard>
      <VTabs v-model="activeTab" color="primary">
        <VTab value="ventas">
          <VIcon icon="ri-wallet-3-line" class="me-2" />
          Formas de Pago & Ventas
        </VTab>
        <VTab value="productos">
          <VIcon icon="ri-restaurant-line" class="me-2" />
          Top Platos & Productos
        </VTab>
        <VTab value="meseros">
          <VIcon icon="ri-user-star-line" class="me-2" />
          Meseros & Propinas
        </VTab>
        <VTab value="balance">
          <VIcon icon="ri-calculator-line" class="me-2" />
          Balance Operativo
        </VTab>
      </VTabs>

      <VDivider />

      <VCardText>
        <VWindow v-model="activeTab">
          <!-- TAB 1: VENTAS & FORMAS DE PAGO -->
          <VWindowItem value="ventas">
            <h3 class="text-h6 font-weight-bold mb-4">Desglose de Ingresos por Medio de Cobro</h3>
            <VRow>
              <VCol cols="12" md="4">
                <VCard variant="outlined" class="p-4 text-center">
                  <VCardText>
                    <VAvatar color="success" variant="tonal" size="56" class="mb-2">
                      <VIcon icon="ri-money-dollar-box-line" size="32" />
                    </VAvatar>
                    <div class="text-subtitle-1 font-weight-bold">Efectivo en Caja</div>
                    <div class="text-h4 font-weight-bold text-success mt-2">
                      Bs. {{ Number(reportesStore.resumenVentas.desglose_pagos?.efectivo || 0).toFixed(2) }}
                    </div>
                  </VCardText>
                </VCard>
              </VCol>

              <VCol cols="12" md="4">
                <VCard variant="outlined" class="p-4 text-center">
                  <VCardText>
                    <VAvatar color="info" variant="tonal" size="56" class="mb-2">
                      <VIcon icon="ri-qr-code-line" size="32" />
                    </VAvatar>
                    <div class="text-subtitle-1 font-weight-bold">Cobros QR Simple</div>
                    <div class="text-h4 font-weight-bold text-info mt-2">
                      Bs. {{ Number(reportesStore.resumenVentas.desglose_pagos?.qr || 0).toFixed(2) }}
                    </div>
                  </VCardText>
                </VCard>
              </VCol>

              <VCol cols="12" md="4">
                <VCard variant="outlined" class="p-4 text-center">
                  <VCardText>
                    <VAvatar color="warning" variant="tonal" size="56" class="mb-2">
                      <VIcon icon="ri-bank-card-line" size="32" />
                    </VAvatar>
                    <div class="text-subtitle-1 font-weight-bold">Tarjetas POS</div>
                    <div class="text-h4 font-weight-bold text-warning mt-2">
                      Bs. {{ Number(reportesStore.resumenVentas.desglose_pagos?.tarjeta || 0).toFixed(2) }}
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>

            <VCard variant="tonal" class="mt-6">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-subtitle-1 font-weight-bold">Distribución Fiscal</div>
                    <div class="text-caption">Cumplimiento normativa tributaria SIN Bolivia</div>
                  </div>
                  <div class="text-right">
                    <VChip color="success" class="me-2">
                      Facturas Emitidas: Bs. {{ Number(reportesStore.resumenVentas.total_facturado).toFixed(2) }}
                    </VChip>
                    <VChip color="secondary">
                      Recibos Internos: Bs. {{ Number(reportesStore.resumenVentas.total_recibos).toFixed(2) }}
                    </VChip>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VWindowItem>

          <!-- TAB 2: TOP PLATOS & PRODUCTOS -->
          <VWindowItem value="productos">
            <h3 class="text-h6 font-weight-bold mb-4">Ranking de Platos y Productos más Vendidos</h3>
            <VTable density="comfortable" hover>
              <thead>
                <tr>
                  <th class="text-left font-weight-bold">#</th>
                  <th class="text-left font-weight-bold">Producto / Plato</th>
                  <th class="text-center font-weight-bold">Cantidad Vendida</th>
                  <th class="text-right font-weight-bold">Total Recaudado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in reportesStore.topProductos" :key="item.producto_id">
                  <td>
                    <VChip size="small" :color="idx < 3 ? 'primary' : 'default'" variant="flat">
                      {{ idx + 1 }}
                    </VChip>
                  </td>
                  <td class="font-weight-medium">{{ item.nombre }}</td>
                  <td class="text-center">
                    <VChip size="small" color="info" variant="tonal">
                      {{ item.total_cantidad }} uds
                    </VChip>
                  </td>
                  <td class="text-right font-weight-bold text-success">
                    Bs. {{ Number(item.total_recaudado).toFixed(2) }}
                  </td>
                </tr>
                <tr v-if="!reportesStore.topProductos.length">
                  <td colspan="4" class="text-center text-medium-emphasis py-6">
                    No se registran ventas en el período seleccionado.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VWindowItem>

          <!-- TAB 3: MESEROS & PROPINAS -->
          <VWindowItem value="meseros">
            <h3 class="text-h6 font-weight-bold mb-4">Rendimiento Operativo y Liquidación de Propinas</h3>
            <VTable density="comfortable" hover>
              <thead>
                <tr>
                  <th class="text-left font-weight-bold">Mesero / Garzón</th>
                  <th class="text-left font-weight-bold">Rol</th>
                  <th class="text-center font-weight-bold">Mesas Atendidas</th>
                  <th class="text-right font-weight-bold">Ventas Totales</th>
                  <th class="text-right font-weight-bold">Propinas Acumuladas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in reportesStore.rendimientoMeseros" :key="m.id">
                  <td class="font-weight-medium d-flex align-center gap-2 py-3">
                    <VAvatar size="32" color="primary" variant="tonal">
                      <VIcon icon="ri-user-3-line" size="18" />
                    </VAvatar>
                    {{ m.nombre }}
                  </td>
                  <td>
                    <VChip size="x-small" color="secondary">{{ m.rol }}</VChip>
                  </td>
                  <td class="text-center font-weight-bold">{{ m.total_mesas_atendidas }}</td>
                  <td class="text-right font-weight-medium">
                    Bs. {{ Number(m.total_ventas).toFixed(2) }}
                  </td>
                  <td class="text-right font-weight-bold text-success">
                    Bs. {{ Number(m.total_propinas).toFixed(2) }}
                  </td>
                </tr>
                <tr v-if="!reportesStore.rendimientoMeseros.length">
                  <td colspan="5" class="text-center text-medium-emphasis py-6">
                    No hay registros de atención de meseros en este rango.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VWindowItem>

          <!-- TAB 4: BALANCE OPERATIVO -->
          <VWindowItem value="balance">
            <h3 class="text-h6 font-weight-bold mb-4">Estado de Resultados Operativo del Restaurante</h3>
            <VRow>
              <VCol cols="12" md="6">
                <VCard variant="outlined">
                  <VCardText>
                    <div class="d-flex justify-space-between py-2 border-b">
                      <span class="text-subtitle-1 font-weight-medium text-success">
                        (+) Ingresos por Ventas:
                      </span>
                      <span class="text-subtitle-1 font-weight-bold text-success">
                        Bs. {{ Number(reportesStore.balanceFinanciero.total_ingresos_ventas).toFixed(2) }}
                      </span>
                    </div>

                    <div class="d-flex justify-space-between py-2 border-b">
                      <span class="text-subtitle-1 font-weight-medium text-error">
                        (-) Gastos Operativos de Caja:
                      </span>
                      <span class="text-subtitle-1 font-weight-bold text-error">
                        Bs. {{ Number(reportesStore.balanceFinanciero.total_gastos_caja).toFixed(2) }}
                      </span>
                    </div>

                    <div class="d-flex justify-space-between py-2 border-b">
                      <span class="text-subtitle-1 font-weight-medium text-warning">
                        (-) Compras de Insumos / Proveedores:
                      </span>
                      <span class="text-subtitle-1 font-weight-bold text-warning">
                        Bs. {{ Number(reportesStore.balanceFinanciero.total_compras_insumos).toFixed(2) }}
                      </span>
                    </div>

                    <div class="d-flex justify-space-between pt-4">
                      <span class="text-h6 font-weight-bold">
                        (=) Margen Operativo Neto:
                      </span>
                      <span
                        class="text-h5 font-weight-bold"
                        :class="reportesStore.balanceFinanciero.utilidad_operativa_bruta >= 0 ? 'text-success' : 'text-error'"
                      >
                        Bs. {{ Number(reportesStore.balanceFinanciero.utilidad_operativa_bruta).toFixed(2) }}
                      </span>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>

              <VCol cols="12" md="6">
                <VAlert
                  type="info"
                  variant="tonal"
                  title="Control Financiero en Tiempo Real"
                  class="h-100"
                >
                  Este estado financiero cruza automáticamente los cobros registrados en caja, los comprobantes de gastos menores autorizados y las notas de compra emitidas a proveedores de alimentos y bebidas.
                </VAlert>
              </VCol>
            </VRow>
          </VWindowItem>
        </VWindow>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
@media print {
  .reportes-container {
    background: white !important;
    color: black !important;
  }
}
</style>