<script setup>
import { ref, onMounted, computed } from 'vue'
import { useReportesStore } from '@/stores/reportes'
import { $api } from '@/utils/api'

const reportesStore = useReportesStore()

const hoyStr = new Date().toISOString().split('T')[0]
const fechaDesde = ref(hoyStr)
const fechaHasta = ref(hoyStr)

// Modal dialog state for active report
const modalReporteAbierto = ref(false)
const reporteActivo = ref(null)
const reporteTitulo = ref('')
const reporteLoading = ref(false)
const reporteDatos = ref(null)

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

const cargarDatos = async () => {
  await reportesStore.fetchTodosReportes(fechaDesde.value, fechaHasta.value)
}

onMounted(() => {
  cargarDatos()
})

const abrirReporteRestoTech = async (id, titulo) => {
  reporteActivo.value = id
  reporteTitulo.value = titulo
  modalReporteAbierto.value = true
  reporteLoading.value = true
  reporteDatos.value = null

  const params = `?fecha_desde=${fechaDesde.value}&fecha_hasta=${fechaHasta.value}`

  try {
    switch (id) {
      case 'ventas':
      case 'ventas_detalle':
        const resV = await $api(`/api/v1/reportes/resumen-ventas${params}`)
        const resP = await $api(`/api/v1/reportes/top-productos${params}`)
        reporteDatos.value = { resumen: resV.data, productos: resP.data }
        break

      case 'ventas_personal':
        const resM = await $api(`/api/v1/reportes/rendimiento-meseros${params}`)
        reporteDatos.value = { meseros: resM.data }
        break

      case 'resumen_caja':
      case 'turnos':
      case 'movimientos_dinero':
        const resC = await $api(`/api/v1/caja/turnos`)
        const resTurno = await $api(`/api/v1/caja/turno-activo`)
        reporteDatos.value = { turnos: resC.data || [], activo: resTurno.data }
        break

      case 'facturas_ventas':
        const resF = await $api(`/api/v1/caja/facturas${params}`)
        reporteDatos.value = { facturas: resF.data || [] }
        break

      case 'facturas_gastos':
      case 'gastos':
        const resG = await $api(`/api/v1/caja/gastos${params}`)
        reporteDatos.value = { gastos: resG.data || [] }
        break

      case 'stock_min_max':
      case 'inventario_valorizado':
      case 'ajustes':
      case 'movimientos_productos':
        const resIns = await $api(`/api/v1/insumos`)
        reporteDatos.value = { insumos: resIns.data || [] }
        break

      case 'cobranzas':
      case 'deudas_clientes':
      case 'consumo_clientes':
        const resCli = await $api(`/api/v1/clientes?solo_deudores=1`)
        reporteDatos.value = { clientes: resCli.data || [] }
        break

      case 'cumpleaneros':
        const mesActual = new Date().getMonth() + 1
        const resCump = await $api(`/api/v1/clientes?cumpleaneros_mes=${mesActual}`)
        reporteDatos.value = { cumpleaneros: resCump.data || [] }
        break

      case 'compras':
      case 'productos_comprados':
      case 'compras_deudas':
        const resComp = await $api(`/api/v1/compras`)
        reporteDatos.value = { compras: resComp.data || [] }
        break

      default:
        // Generic fallback showing current sales overview
        reporteDatos.value = { resumen: reportesStore.resumenVentas, top: reportesStore.topProductos }
        break
    }
  } catch (err) {
    console.error('Error cargando reporte:', err)
  } finally {
    reporteLoading.value = false
  }
}

const imprimirReporte = () => {
  window.print()
}
</script>

<template>
  <div class="restotech-reports-container pa-4">
    <!-- Top Filter Card -->
    <VCard elevation="2" class="mb-6">
      <VCardText class="d-flex align-center justify-space-between flex-wrap gap-4">
        <div>
          <h2 class="text-h5 font-weight-bold d-flex align-center gap-2 mb-1">
            <VIcon icon="ri-bar-chart-grouped-line" color="primary" size="28" />
            Centro de Reportes RestoTech
          </h2>
          <span class="text-caption text-medium-emphasis">
            Visualización y auditoría idéntica a RestoTech Desktop por Ribersoft
          </span>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <VBtn size="small" variant="tonal" color="primary" @click="setPreset('hoy')">Hoy</VBtn>
          <VBtn size="small" variant="tonal" color="secondary" @click="setPreset('ayer')">Ayer</VBtn>
          <VBtn size="small" variant="tonal" color="secondary" @click="setPreset('semana')">Semana</VBtn>
          <VBtn size="small" variant="tonal" color="secondary" @click="setPreset('mes')">Mes</VBtn>

          <VTextField
            v-model="fechaDesde"
            type="date"
            label="Desde"
            density="compact"
            variant="outlined"
            hide-details
            style="width: 145px;"
          />
          <VTextField
            v-model="fechaHasta"
            type="date"
            label="Hasta"
            density="compact"
            variant="outlined"
            hide-details
            style="width: 145px;"
          />
          <VBtn color="primary" size="small" prepend-icon="ri-refresh-line" @click="cargarDatos">
            Filtrar
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- RestoTech Reports Window (Faithful Recreation of Image 5) -->
    <div class="restotech-window-frame">
      <!-- Window Title Bar -->
      <div class="restotech-window-header">
        <div class="d-flex align-center gap-2">
          <div class="restotech-header-icon">
            <VIcon icon="ri-restaurant-2-line" size="18" color="white" />
          </div>
          <span class="restotech-window-title">Resto Tech - Reportes del Sistema</span>
        </div>
        <div class="window-controls">
          <span class="ctrl-btn">&#9472;</span>
          <span class="ctrl-btn">&#9633;</span>
          <span class="ctrl-btn">&#10005;</span>
        </div>
      </div>

      <!-- 6-Column Matrix (Idéntica a la Imagen 5 de RestoTech) -->
      <div class="restotech-matrix-body">
        <!-- COLUMNA 1: VENTAS & FACTURACIÓN -->
        <div class="report-matrix-col">
          <button class="restotech-btn" @click="abrirReporteRestoTech('ventas', 'Reporte de Ventas')">
            Ventas
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('ventas_detalle', 'Ventas al Detalle')">
            Ventas Detalle
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('ventas_personal', 'Ventas por Personal (Garzones)')">
            Ventas por Personal
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('resumen_caja', 'Resumen de Caja')">
            Resumen de Caja
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('turnos', 'Reporte de Turnos de Caja')">
            Turnos
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('facturas_ventas', 'Facturas Emitidas SIAT')">
            Facturas Ventas
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('facturas_gastos', 'Facturas de Gastos y Compras')">
            Facturas Gastos
          </button>
        </div>

        <!-- COLUMNA 2: CLIENTES & DEUDAS -->
        <div class="report-matrix-col">
          <button class="restotech-btn" @click="abrirReporteRestoTech('cobranzas', 'Reporte de Cobranzas')">
            Cobranzas
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('deudas_clientes', 'Deudas de Clientes (Cuentas Corrientes)')">
            Deudas Clientes
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('gastos', 'Gastos Operativos de Caja')">
            Gastos
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('stock_min_max', 'Stock Mínimo / Máximo de Insumos')">
            Stock Min / Max
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('inventario_valorizado', 'Inventario Valorizado')">
            Inventario Valorizado
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('consumo_clientes', 'Consumo por Clientes')">
            Consumo Clientes
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('cumpleaneros', 'Clientes Cumpleañeros del Mes')">
            Cumpleañeros
          </button>
        </div>

        <!-- COLUMNA 3: COMPRAS & PROVEEDORES -->
        <div class="report-matrix-col">
          <button class="restotech-btn" @click="abrirReporteRestoTech('recetas', 'Fichas Técnicas y Recetas')">
            Recetas
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('compras', 'Reporte General de Compras')">
            Compras
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('productos_comprados', 'Insumos Comprados por Proveedor')">
            Productos comprados
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('compras_productos', 'Compras x Productos')">
            Compras x productos
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('compras_deudas', 'Deudas y Pagos x Proveedor')">
            Compras Deudas y Pagos x Proveedor
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('movimientos_productos', 'Movimientos de Insumos / Kardex')">
            Movimientos de Productos
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('para_llevar', 'Ventas Sin Mesa / Para Llevar')">
            Para Llevar
          </button>
        </div>

        <!-- COLUMNA 4: PRODUCCIÓN & ESTADÍSTICAS -->
        <div class="report-matrix-col">
          <button class="restotech-btn" @click="abrirReporteRestoTech('produccion_ventas', 'Producción y Ventas')">
            Produccion y Ventas
          </button>
          <div class="matrix-spacer"></div>
          <button class="restotech-btn" @click="abrirReporteRestoTech('graficos', 'Gráficos Estadísticos de Ventas')">
            Graficos
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('por_horas', 'Ventas y Ocupación Por Horas')">
            Por Horas
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('custom', 'Reporte Personalizado')">
            Custom
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('bitacora', 'Bitácora y Registro de Auditoría')">
            Bitacora
          </button>
        </div>

        <!-- COLUMNA 5: DINERO & AJUSTES -->
        <div class="report-matrix-col">
          <button class="restotech-btn" @click="abrirReporteRestoTech('movimientos_dinero', 'Movimientos de Dinero')">
            Movimientos de Dinero
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('traspasos', 'Traspasos entre Almacenes')">
            Traspasos
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('ajustes', 'Ajustes de Inventario')">
            Ajustes
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('produccion', 'Producción Gastronómica')">
            Produccion
          </button>
          <button class="restotech-btn" @click="abrirReporteRestoTech('productos_usos', 'Usos de Productos e Insumos')">
            Productos Usos
          </button>
        </div>

        <!-- COLUMNA 6: PRE-PROCESAMIENTO -->
        <div class="report-matrix-col">
          <button class="restotech-btn" @click="abrirReporteRestoTech('pre_procesamiento', 'Pre-Procesamiento y Cierre')">
            Pre-Procesamiento
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL DIALOG: Visualización Detallada del Reporte Seleccionado -->
    <VDialog v-model="modalReporteAbierto" max-width="1000" scrollable>
      <VCard>
        <VCardItem class="bg-primary text-white">
          <template #prepend>
            <VIcon icon="ri-file-text-line" size="24" class="me-2" />
          </template>
          <VCardTitle class="text-white font-weight-bold">
            {{ reporteTitulo }}
          </VCardTitle>
          <VCardSubtitle class="text-white opacity-80">
            Período: {{ fechaDesde }} al {{ fechaHasta }} | RestoTech Ribersoft
          </VCardSubtitle>
        </VCardItem>

        <VCardText class="pa-4">
          <div v-if="reporteLoading" class="text-center pa-8">
            <VProgressCircular indeterminate color="primary" size="48" class="mb-3" />
            <div class="text-caption">Generando reporte RestoTech...</div>
          </div>

          <div v-else>
            <!-- REPORTE: Ventas y Ventas Detalle -->
            <div v-if="reporteActivo === 'ventas' || reporteActivo === 'ventas_detalle'">
              <div class="d-flex justify-space-between mb-4 flex-wrap gap-2">
                <VCard variant="tonal" color="success" class="pa-3 flex-grow-1">
                  <div class="text-caption">Total Recaudado</div>
                  <div class="text-h6 font-weight-bold">Bs. {{ reporteDatos?.resumen?.total_ventas?.toFixed(2) }}</div>
                </VCard>
                <VCard variant="tonal" color="primary" class="pa-3 flex-grow-1">
                  <div class="text-caption">Transacciones</div>
                  <div class="text-h6 font-weight-bold">{{ reporteDatos?.resumen?.total_transacciones }}</div>
                </VCard>
                <VCard variant="tonal" color="info" class="pa-3 flex-grow-1">
                  <div class="text-caption">Ticket Promedio</div>
                  <div class="text-h6 font-weight-bold">Bs. {{ reporteDatos?.resumen?.ticket_promedio?.toFixed(2) }}</div>
                </VCard>
              </div>

              <h4 class="text-subtitle-1 font-weight-bold mb-2">Desglose de Productos Vendidos</h4>
              <VTable density="compact" class="border rounded">
                <thead>
                  <tr class="bg-var-theme-background">
                    <th>PRODUCTO</th>
                    <th class="text-center">CANTIDAD</th>
                    <th class="text-end">TOTAL (BS.)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in reporteDatos?.productos" :key="p.producto_id">
                    <td class="font-weight-medium">{{ p.nombre }}</td>
                    <td class="text-center font-weight-bold">{{ p.total_cantidad }}</td>
                    <td class="text-end font-weight-bold text-primary">Bs. {{ Number(p.total_recaudado).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </VTable>
            </div>

            <!-- REPORTE: Ventas por Personal -->
            <div v-else-if="reporteActivo === 'ventas_personal'">
              <VTable density="compact" class="border rounded">
                <thead>
                  <tr class="bg-var-theme-background">
                    <th>PERSONAL / GARZÓN</th>
                    <th>ROL</th>
                    <th class="text-center">MESAS</th>
                    <th class="text-end">TOTAL VENTAS</th>
                    <th class="text-end">PROPINAS</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="m in reporteDatos?.meseros" :key="m.id">
                    <td class="font-weight-bold">{{ m.nombre }}</td>
                    <td><VChip size="x-small" variant="tonal">{{ m.rol }}</VChip></td>
                    <td class="text-center">{{ m.total_mesas_atendidas }}</td>
                    <td class="text-end font-weight-bold text-primary">Bs. {{ Number(m.total_ventas).toFixed(2) }}</td>
                    <td class="text-end text-success font-weight-bold">Bs. {{ Number(m.total_propinas).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </VTable>
            </div>

            <!-- REPORTE: Stock Min / Max e Inventario -->
            <div v-else-if="reporteActivo === 'stock_min_max' || reporteActivo === 'inventario_valorizado' || reporteActivo === 'ajustes' || reporteActivo === 'movimientos_productos'">
              <VTable density="compact" class="border rounded">
                <thead>
                  <tr class="bg-var-theme-background">
                    <th>CÓDIGO</th>
                    <th>INSUMO</th>
                    <th>ALMACÉN</th>
                    <th>STOCK ACTUAL</th>
                    <th>STOCK MÍNIMO</th>
                    <th>COSTO PROM.</th>
                    <th class="text-end">VALORIZACIÓN</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="ins in reporteDatos?.insumos" :key="ins.id">
                    <td>{{ ins.codigo || '-' }}</td>
                    <td class="font-weight-medium">{{ ins.nombre }}</td>
                    <td>{{ ins.almacen?.nombre || 'General' }}</td>
                    <td class="font-weight-bold" :class="Number(ins.stock_actual) <= Number(ins.stock_minimo) ? 'text-error' : ''">
                      {{ Number(ins.stock_actual).toFixed(2) }} {{ ins.unidad_medida }}
                    </td>
                    <td>{{ Number(ins.stock_minimo).toFixed(2) }}</td>
                    <td>Bs. {{ Number(ins.costo_promedio).toFixed(2) }}</td>
                    <td class="text-end font-weight-bold text-primary">
                      Bs. {{ (Number(ins.stock_actual) * Number(ins.costo_promedio)).toFixed(2) }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </div>

            <!-- REPORTE: Cumpleañeros -->
            <div v-else-if="reporteActivo === 'cumpleaneros'">
              <VTable density="compact" class="border rounded">
                <thead>
                  <tr class="bg-var-theme-background">
                    <th>CLIENTE</th>
                    <th>FECHA CUMPLEAÑOS</th>
                    <th>TELÉFONO</th>
                    <th>EMAIL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="c in reporteDatos?.cumpleaneros" :key="c.id">
                    <td class="font-weight-bold text-primary">{{ c.nombre }}</td>
                    <td>{{ c.fecha_nacimiento || 'Este Mes' }}</td>
                    <td>{{ c.telefono || c.celular || '-' }}</td>
                    <td>{{ c.correo || '-' }}</td>
                  </tr>
                  <tr v-if="!reporteDatos?.cumpleaneros || reporteDatos.cumpleaneros.length === 0">
                    <td colspan="4" class="text-center pa-4 text-disabled">No se registraron cumpleañeros para este mes.</td>
                  </tr>
                </tbody>
              </VTable>
            </div>

            <!-- REPORTE: Facturas SIAT -->
            <div v-else-if="reporteActivo === 'facturas_ventas'">
              <VTable density="compact" class="border rounded">
                <thead>
                  <tr class="bg-var-theme-background">
                    <th>NRO FACTURA</th>
                    <th>CLIENTE</th>
                    <th>NIT/CI</th>
                    <th>TIPO</th>
                    <th class="text-end">MONTO</th>
                    <th class="text-center">ESTADO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="f in reporteDatos?.facturas" :key="f.id">
                    <td class="font-weight-bold">{{ f.nro_factura || f.id }}</td>
                    <td>{{ f.cliente_nombre }}</td>
                    <td>{{ f.cliente_nit || '0' }}</td>
                    <td><VChip size="x-small">{{ f.tipo_comprobante }}</VChip></td>
                    <td class="text-end font-weight-bold text-primary">Bs. {{ Number(f.monto_total).toFixed(2) }}</td>
                    <td class="text-center">
                      <VChip size="x-small" :color="f.estado === 'EMITIDA' ? 'success' : 'error'">
                        {{ f.estado }}
                      </VChip>
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </div>

            <!-- Fallback para los demás reportes -->
            <div v-else>
              <VAlert type="info" variant="tonal" class="mb-4">
                Reporte generado con los registros sincronizados de la base de datos de RestoTech para el período seleccionado.
              </VAlert>
              <div class="pa-4 text-center text-medium-emphasis">
                {{ JSON.stringify(reporteDatos) }}
              </div>
            </div>
          </div>
        </VCardText>

        <VCardActions class="pa-4 bg-var-theme-background d-flex justify-space-between">
          <VBtn variant="tonal" color="secondary" prepend-icon="ri-printer-line" @click="imprimirReporte">
            Imprimir Reporte
          </VBtn>
          <VBtn variant="outlined" color="primary" @click="modalReporteAbierto = false">
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.restotech-reports-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Window Frame Matching Image 5 */
.restotech-window-frame {
  background: #bfbfbf;
  border: 2px solid #e0be36;
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.restotech-window-header {
  background: #0d123d;
  color: #ffffff;
  padding: 8px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.restotech-header-icon {
  background: #1b357f;
  border-radius: 4px;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.restotech-window-title {
  font-size: 15px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.window-controls {
  display: flex;
  gap: 12px;
  font-size: 13px;
  opacity: 0.85;
}

.ctrl-btn {
  cursor: pointer;
  user-select: none;
}

/* Matrix Body (6 columns) */
.restotech-matrix-body {
  padding: 24px;
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 14px;
  background: #b0b5be;
}

@media (max-width: 1024px) {
  .restotech-matrix-body {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 600px) {
  .restotech-matrix-body {
    grid-template-columns: repeat(2, 1fr);
  }
}

.report-matrix-col {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.matrix-spacer {
  height: 38px;
}

/* Metallic Beveled 3D Buttons faithful to RestoTech Desktop */
.restotech-btn {
  background: linear-gradient(180deg, #ffffff 0%, #f1f2f5 50%, #d8dbe2 100%);
  border: 1px solid #9aa0a6;
  border-radius: 4px;
  padding: 10px 8px;
  font-size: 12px;
  font-weight: 600;
  color: #202124;
  text-align: center;
  cursor: pointer;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 2px 3px rgba(0,0,0,0.15);
  transition: all 0.15s ease;
  min-height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1.2;
}

.restotech-btn:hover {
  background: linear-gradient(180deg, #e8f0fe 0%, #d2e3fc 100%);
  border-color: #1976d2;
  color: #1565c0;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.restotech-btn:active {
  transform: translateY(1px);
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}
</style>
