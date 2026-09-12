<script setup>
import { ref, onMounted, computed } from 'vue'
import { useInventarioStore } from '@/stores/inventario'
import { useComandaStore } from '@/stores/comanda'
import InsumoModal from '@/components/inventario/InsumoModal.vue'
import CompraModal from '@/components/inventario/CompraModal.vue'
import ProveedorModal from '@/components/inventario/ProveedorModal.vue'
import ProveedorPagoModal from '@/components/inventario/ProveedorPagoModal.vue'
import RecetaModal from '@/components/inventario/RecetaModal.vue'
import AjusteStockModal from '@/components/inventario/AjusteStockModal.vue'

const store = useInventarioStore()
const comandaStore = useComandaStore()

const currentTab = ref('insumos')
const searchInsumo = ref('')
const filtroAlmacen = ref(null)
const soloBajoStock = ref(false)

// Modals
const modalInsumo = ref(false)
const insumoEditar = ref(null)

const modalCompra = ref(false)

const modalProveedor = ref(false)
const proveedorEditar = ref(null)

const modalPago = ref(false)
const proveedorPago = ref(null)

const modalReceta = ref(false)
const productoReceta = ref(null)

const modalAjuste = ref(false)
const insumoAjuste = ref(null)

onMounted(async () => {
  await store.fetchAlmacenes()
  await store.fetchInsumos()
  await store.fetchCompras()
  await store.fetchProveedores()
  await store.fetchKardex()
  await comandaStore.fetchMenu()
})

const insumosFiltrados = computed(() => {
  return store.insumos.filter(i => {
    const matchSearch = !searchInsumo.value || 
      i.nombre.toLowerCase().includes(searchInsumo.value.toLowerCase()) ||
      (i.codigo && i.codigo.toLowerCase().includes(searchInsumo.value.toLowerCase()))
    const matchAlmacen = !filtroAlmacen.value || i.almacen_id === filtroAlmacen.value
    const matchBajoStock = !soloBajoStock.value || Number(i.stock_actual) <= Number(i.stock_minimo)
    return matchSearch && matchAlmacen && matchBajoStock
  })
})

const abrirNuevoInsumo = () => {
  insumoEditar.value = null
  modalInsumo.value = true
}

const abrirEditarInsumo = (insumo) => {
  insumoEditar.value = insumo
  modalInsumo.value = true
}

const abrirAjustarStock = (insumo) => {
  insumoAjuste.value = insumo
  modalAjuste.value = true
}

const abrirNuevoProveedor = () => {
  proveedorEditar.value = null
  modalProveedor.value = true
}

const abrirEditarProveedor = (prov) => {
  proveedorEditar.value = prov
  modalProveedor.value = true
}

const abrirPagarProveedor = (prov) => {
  proveedorPago.value = prov
  modalPago.value = true
}

const abrirRecetaProducto = (prod) => {
  productoReceta.value = prod
  modalReceta.value = true
}
</script>

<template>
  <div class="pa-4">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between flex-wrap gap-4 mb-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-archive-line" color="primary" />
          Almacenes & Inventario Gastronómico
        </h2>
        <p class="text-body-1 text-medium-emphasis mb-0">
          Control de stock, materias primas, compras, proveedores y recetas de producción.
        </p>
      </div>

      <!-- Resumen Métricas Rápidas -->
      <div class="d-flex gap-3">
        <VCard variant="tonal" color="warning" class="px-4 py-2 text-center">
          <div class="text-caption">Bajo Stock</div>
          <div class="text-h6 font-weight-bold">{{ store.insumosBajoStock.length }} Insumos</div>
        </VCard>
        <VCard variant="tonal" color="error" class="px-4 py-2 text-center">
          <div class="text-caption">Deuda Proveedores</div>
          <div class="text-h6 font-weight-bold">Bs. {{ store.totalDeudaProveedores.toFixed(2) }}</div>
        </VCard>
      </div>
    </div>

    <!-- Tabs Navegación -->
    <VCard>
      <VTabs v-model="currentTab" class="v-tabs-pill border-b pa-2">
        <VTab value="insumos">
          <VIcon icon="ri-stack-line" class="me-2" /> Insumos & Stock
        </VTab>
        <VTab value="compras">
          <VIcon icon="ri-shopping-cart-2-line" class="me-2" /> Compras & Entradas
        </VTab>
        <VTab value="proveedores">
          <VIcon icon="ri-truck-line" class="me-2" /> Proveedores & Deudas
        </VTab>
        <VTab value="recetas">
          <VIcon icon="ri-restaurant-line" class="me-2" /> Fichas Técnicas (Recetas)
        </VTab>
        <VTab value="almacenes">
          <VIcon icon="ri-building-line" class="me-2" /> Almacenes
        </VTab>
        <VTab value="kardex">
          <VIcon icon="ri-history-line" class="me-2" /> Kardex de Movimientos
        </VTab>
      </VTabs>

      <VCardText class="pa-4">
        <!-- TAB 1: INSUMOS & STOCK -->
        <div v-if="currentTab === 'insumos'">
          <div class="d-flex align-center justify-space-between flex-wrap gap-4 mb-4">
            <div class="d-flex gap-3 flex-grow-1 flex-wrap">
              <VTextField
                v-model="searchInsumo"
                prepend-inner-icon="ri-search-line"
                placeholder="Buscar insumo por nombre o código..."
                density="compact"
                variant="outlined"
                hide-details
                style="max-width: 320px;"
              />
              <VSelect
                v-model="filtroAlmacen"
                :items="[{ id: null, nombre: 'Todos los Almacenes' }, ...store.almacenes]"
                item-title="nombre"
                item-value="id"
                density="compact"
                variant="outlined"
                hide-details
                style="max-width: 220px;"
              />
              <VBtn
                :variant="soloBajoStock ? 'elevated' : 'outlined'"
                :color="soloBajoStock ? 'error' : 'secondary'"
                @click="soloBajoStock = !soloBajoStock"
              >
                <VIcon icon="ri-alert-line" class="me-1" />
                Solo Bajo Stock ({{ store.insumosBajoStock.length }})
              </VBtn>
            </div>

            <VBtn color="primary" @click="abrirNuevoInsumo">
              <VIcon icon="ri-add-line" class="me-1" />
              Nuevo Insumo
            </VBtn>
          </div>

          <VTable class="border rounded" density="comfortable">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre del Insumo</th>
                <th>Almacén</th>
                <th>Unidad</th>
                <th>Costo Promedio</th>
                <th>Stock Actual</th>
                <th>Estado Stock</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="insumosFiltrados.length === 0">
                <td colspan="8" class="text-center py-6 text-medium-emphasis">
                  No se encontraron insumos de almacén
                </td>
              </tr>
              <tr v-for="insumo in insumosFiltrados" :key="insumo.id">
                <td class="font-weight-medium">{{ insumo.codigo || '-' }}</td>
                <td class="font-weight-bold text-primary">{{ insumo.nombre }}</td>
                <td>{{ insumo.almacen?.nombre || 'General' }}</td>
                <td><VChip size="small" variant="tonal">{{ insumo.unidad_medida }}</VChip></td>
                <td>Bs. {{ Number(insumo.costo_promedio).toFixed(2) }}</td>
                <td class="font-weight-bold text-h6">
                  {{ Number(insumo.stock_actual).toFixed(2) }}
                </td>
                <td>
                  <VChip
                    size="small"
                    :color="Number(insumo.stock_actual) <= Number(insumo.stock_minimo) ? 'error' : 'success'"
                    variant="elevated"
                  >
                    {{ Number(insumo.stock_actual) <= Number(insumo.stock_minimo) ? 'BAJO STOCK' : 'ÓPTIMO' }}
                  </VChip>
                </td>
                <td class="text-center">
                  <VBtn icon="ri-equalizer-line" size="small" color="secondary" variant="text" title="Ajustar Stock" @click="abrirAjustarStock(insumo)" />
                  <VBtn icon="ri-pencil-line" size="small" color="primary" variant="text" title="Editar" @click="abrirEditarInsumo(insumo)" />
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <!-- TAB 2: COMPRAS & RECEPCIÓN -->
        <div v-if="currentTab === 'compras'">
          <div class="d-flex align-center justify-space-between mb-4">
            <h3 class="text-h6 font-weight-bold">Historial de Compras & Recepción de Mercadería</h3>
            <VBtn color="primary" @click="modalCompra = true">
              <VIcon icon="ri-add-line" class="me-1" />
              Nueva Compra
            </VBtn>
          </div>

          <VTable class="border rounded" density="comfortable">
            <thead>
              <tr>
                <th>Nro</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Almacén</th>
                <th>Factura / Nota</th>
                <th>Condición</th>
                <th>Total (Bs)</th>
                <th>Detalles</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="store.compras.length === 0">
                <td colspan="8" class="text-center py-6 text-medium-emphasis">
                  No hay compras registradas
                </td>
              </tr>
              <tr v-for="compra in store.compras" :key="compra.id">
                <td class="font-weight-bold">#{{ compra.id }}</td>
                <td>{{ new Date(compra.fecha_compra).toLocaleDateString() }}</td>
                <td class="font-weight-bold">{{ compra.proveedor?.nombre }}</td>
                <td>{{ compra.almacen?.nombre }}</td>
                <td>{{ compra.nro_factura || '-' }}</td>
                <td>
                  <VChip size="small" :color="compra.metodo_pago === 'CONTADO' ? 'success' : 'warning'">
                    {{ compra.metodo_pago }}
                  </VChip>
                </td>
                <td class="font-weight-bold text-primary">Bs. {{ Number(compra.monto_total).toFixed(2) }}</td>
                <td>
                  <span class="text-caption">{{ compra.detalles?.length || 0 }} ítems</span>
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <!-- TAB 3: PROVEEDORES -->
        <div v-if="currentTab === 'proveedores'">
          <div class="d-flex align-center justify-space-between mb-4">
            <h3 class="text-h6 font-weight-bold">Directorio de Proveedores & Deudas</h3>
            <VBtn color="primary" @click="abrirNuevoProveedor">
              <VIcon icon="ri-add-line" class="me-1" />
              Nuevo Proveedor
            </VBtn>
          </div>

          <VTable class="border rounded" density="comfortable">
            <thead>
              <tr>
                <th>Proveedor</th>
                <th>NIT / CI</th>
                <th>Teléfono / Contacto</th>
                <th>Datos Bancarios</th>
                <th>Saldo Deuda</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="store.proveedores.length === 0">
                <td colspan="6" class="text-center py-6 text-medium-emphasis">
                  No hay proveedores registrados
                </td>
              </tr>
              <tr v-for="prov in store.proveedores" :key="prov.id">
                <td>
                  <div class="font-weight-bold text-primary">{{ prov.nombre }}</div>
                  <div class="text-caption text-medium-emphasis">{{ prov.razon_social }}</div>
                </td>
                <td>{{ prov.nit || '-' }}</td>
                <td>
                  <div>{{ prov.telefono || '-' }}</div>
                  <div class="text-caption">{{ prov.contacto }}</div>
                </td>
                <td>
                  <div v-if="prov.banco">{{ prov.banco }} - {{ prov.nro_cuenta }}</div>
                  <div class="text-caption" v-if="prov.titular_cuenta">{{ prov.titular_cuenta }}</div>
                  <span v-else class="text-medium-emphasis">-</span>
                </td>
                <td>
                  <VChip
                    size="small"
                    :color="Number(prov.saldo_deuda) > 0 ? 'error' : 'success'"
                    variant="elevated"
                    class="font-weight-bold"
                  >
                    Bs. {{ Number(prov.saldo_deuda).toFixed(2) }}
                  </VChip>
                </td>
                <td class="text-center">
                  <VBtn
                    v-if="Number(prov.saldo_deuda) > 0"
                    size="small"
                    color="success"
                    variant="tonal"
                    class="me-2"
                    @click="abrirPagarProveedor(prov)"
                  >
                    <VIcon icon="ri-hand-coin-line" class="me-1" /> Pagar
                  </VBtn>
                  <VBtn icon="ri-pencil-line" size="small" color="primary" variant="text" @click="abrirEditarProveedor(prov)" />
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <!-- TAB 4: FICHAS TÉCNICAS (RECETAS) -->
        <div v-if="currentTab === 'recetas'">
          <div class="d-flex align-center justify-space-between mb-4">
            <h3 class="text-h6 font-weight-bold">Fichas Técnicas & Recetas de Platos del Menú</h3>
          </div>

          <VTable class="border rounded" density="comfortable">
            <thead>
              <tr>
                <th>Plato / Producto</th>
                <th>Categoría</th>
                <th>Precio Venta</th>
                <th>Costo Teórico</th>
                <th>Margen Teórico</th>
                <th class="text-center">Ficha Técnica</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="prod in comandaStore.productos" :key="prod.id">
                <td class="font-weight-bold text-primary">{{ prod.nombre }}</td>
                <td>{{ prod.categoria?.nombre || '-' }}</td>
                <td class="font-weight-bold">Bs. {{ Number(prod.precio).toFixed(2) }}</td>
                <td class="font-weight-medium text-error">Bs. {{ Number(prod.costo || 0).toFixed(2) }}</td>
                <td>
                  <VChip
                    size="small"
                    :color="Number(prod.precio) > Number(prod.costo) ? 'success' : 'warning'"
                    variant="tonal"
                  >
                    {{ Number(prod.precio) > 0 ? (((Number(prod.precio) - Number(prod.costo || 0)) / Number(prod.precio)) * 100).toFixed(1) : 0 }}%
                  </VChip>
                </td>
                <td class="text-center">
                  <VBtn
                    color="primary"
                    variant="tonal"
                    size="small"
                    @click="abrirRecetaProducto(prod)"
                  >
                    <VIcon icon="ri-file-list-3-line" class="me-1" />
                    Dosificar Receta
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <!-- TAB 5: ALMACENES -->
        <div v-if="currentTab === 'almacenes'">
          <div class="d-flex align-center justify-space-between mb-4">
            <h3 class="text-h6 font-weight-bold">Almacenes & Centros de Despacho</h3>
          </div>

          <VRow>
            <VCol v-for="alm in store.almacenes" :key="alm.id" cols="12" md="4">
              <VCard variant="outlined" class="pa-4">
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="text-h6 font-weight-bold">{{ alm.nombre }}</div>
                  <VChip size="small" :color="alm.es_interno ? 'info' : 'primary'">
                    {{ alm.es_interno ? 'Interno' : 'Punto de Venta' }}
                  </VChip>
                </div>
                <p class="text-body-2 text-medium-emphasis mb-2">{{ alm.descripcion || 'Sin descripción' }}</p>
                <div class="d-flex justify-space-between text-caption border-t pt-2">
                  <span>Responsable:</span>
                  <span class="font-weight-medium">{{ alm.responsable || 'No asignado' }}</span>
                </div>
                <div class="d-flex justify-space-between text-caption pt-1">
                  <span>Insumos Asignados:</span>
                  <span class="font-weight-bold text-primary">{{ alm.insumos_count || 0 }}</span>
                </div>
              </VCard>
            </VCol>
          </VRow>
        </div>

        <!-- TAB 6: KARDEX -->
        <div v-if="currentTab === 'kardex'">
          <div class="d-flex align-center justify-space-between mb-4">
            <h3 class="text-h6 font-weight-bold">Kardex Inmutable de Movimientos de Stock</h3>
            <VBtn color="secondary" variant="outlined" size="small" @click="store.fetchKardex()">
              <VIcon icon="ri-refresh-line" class="me-1" /> Actualizar
            </VBtn>
          </div>

          <VTable class="border rounded" density="compact">
            <thead>
              <tr>
                <th>Fecha / Hora</th>
                <th>Insumo</th>
                <th>Almacén</th>
                <th>Tipo Movimiento</th>
                <th>Cantidad</th>
                <th>Stock Anterior</th>
                <th>Stock Resultante</th>
                <th>Referencia</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="store.kardex.length === 0">
                <td colspan="8" class="text-center py-6 text-medium-emphasis">
                  No hay movimientos registrados en el Kardex
                </td>
              </tr>
              <tr v-for="k in store.kardex" :key="k.id">
                <td>{{ new Date(k.created_at).toLocaleString() }}</td>
                <td class="font-weight-bold text-primary">{{ k.insumo?.nombre }}</td>
                <td>{{ k.almacen?.nombre }}</td>
                <td>
                  <VChip
                    size="x-small"
                    :color="
                      k.tipo === 'COMPRA' || k.tipo === 'AJUSTE_POSITIVO' ? 'success' :
                      k.tipo === 'CONSUMO_VENTA' ? 'info' : 'error'
                    "
                  >
                    {{ k.tipo }}
                  </VChip>
                </td>
                <td class="font-weight-bold">
                  {{ (k.tipo === 'CONSUMO_VENTA' || k.tipo === 'AJUSTE_NEGATIVO' || k.tipo === 'MERMA' ? '-' : '+') + Number(k.cantidad).toFixed(2) }}
                </td>
                <td>{{ Number(k.stock_anterior).toFixed(2) }}</td>
                <td class="font-weight-bold">{{ Number(k.stock_nuevo).toFixed(2) }}</td>
                <td>{{ k.referencia || '-' }}</td>
              </tr>
            </tbody>
          </VTable>
        </div>
      </VCardText>
    </VCard>

    <!-- Modales -->
    <InsumoModal v-model="modalInsumo" :insumo="insumoEditar" @guardado="store.fetchInsumos" />
    <CompraModal v-model="modalCompra" @guardado="store.fetchCompras" />
    <ProveedorModal v-model="modalProveedor" :proveedor="proveedorEditar" @guardado="store.fetchProveedores" />
    <ProveedorPagoModal v-model="modalPago" :proveedor="proveedorPago" @guardado="store.fetchProveedores" />
    <RecetaModal v-model="modalReceta" :producto="productoReceta" @guardado="comandaStore.fetchMenu" />
    <AjusteStockModal v-model="modalAjuste" :insumo="insumoAjuste" @guardado="store.fetchInsumos" />
  </div>
</template>