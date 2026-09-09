<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCajaStore } from '@/stores/caja'

const cajaStore = useCajaStore()

const searchQuery = ref('')
const filterEstado = ref('TODAS')
const selectedFactura = ref(null)
const ticketModalOpen = ref(false)

onMounted(async () => {
  await cajaStore.fetchFacturas()
})

const filteredFacturas = computed(() => {
  let list = cajaStore.facturas

  if (filterEstado.value !== 'TODAS') {
    list = list.filter(f => f.estado === filterEstado.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(f => 
      f.nro_factura.toString().includes(q) ||
      f.razon_social.toLowerCase().includes(q) ||
      f.numero_documento.includes(q)
    )
  }

  return list
})

// Metrics
const metricas = computed(() => {
  const total = cajaStore.facturas.length
  const validas = cajaStore.facturas.filter(f => f.estado === 'VALIDA')
  const anuladas = cajaStore.facturas.filter(f => f.estado === 'ANULADA')
  const montoValido = validas.reduce((acc, f) => acc + (parseFloat(f.monto_total) || 0), 0)

  return {
    total,
    validasCount: validas.length,
    anuladasCount: anuladas.length,
    montoValido,
  }
})

const abrirTicket = (factura) => {
  selectedFactura.value = factura
  ticketModalOpen.value = true
}

const printTicket = () => {
  window.print()
}

const anularFactura = async (factura) => {
  const motivo = prompt(`Ingrese motivo para anular la Factura N° ${factura.nro_factura} ante el SIAT:`)
  if (!motivo) return

  const res = await cajaStore.anularFactura(factura.id, motivo)
  if (res.success) {
    alert('Factura anulada exitosamente en el sistema y notificada al SIAT')
    await cajaStore.fetchFacturas()
  } else {
    alert(res.message || 'Error al anular factura')
  }
}
</script>

<template>
  <div class="facturas-page-container">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between flex-wrap gap-2 mb-4">
      <div>
        <h2 class="text-h5 font-weight-bold mb-0">Facturación Computarizada en Línea SIAT</h2>
        <span class="text-caption text-medium-emphasis">
          RiberResto POS | Emisión, Consulta Fiscal, Reimpresión y Anulación ante Impuestos Nacionales
        </span>
      </div>

      <div class="d-flex align-center gap-2">
        <VChip color="success" variant="elevated" class="font-weight-bold">
          <VIcon icon="ri-shield-check-line" class="me-1" />
          SIAT En Línea Conectado
        </VChip>
        <VBtn
          icon="ri-refresh-line"
          size="small"
          variant="tonal"
          color="primary"
          :loading="cajaStore.loading"
          @click="cajaStore.fetchFacturas()"
        />
      </div>
    </div>

    <!-- 4 KPI Cards (Invoice Summary from admin-full-version) -->
    <VRow class="mb-4 match-height">
      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="primary" class="pa-4">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold">Total Facturado</span>
            <VAvatar color="primary" size="36" variant="elevated">
              <VIcon icon="ri-money-dollar-circle-line" color="white" size="20" />
            </VAvatar>
          </div>
          <div class="text-h4 font-weight-black text-primary">
            Bs. {{ metricas.montoValido.toFixed(2) }}
          </div>
          <span class="text-caption text-medium-emphasis">Válido para crédito fiscal</span>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="success" class="pa-4">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold">Facturas Válidas</span>
            <VAvatar color="success" size="36" variant="elevated">
              <VIcon icon="ri-checkbox-circle-line" color="white" size="20" />
            </VAvatar>
          </div>
          <div class="text-h4 font-weight-black text-success">
            {{ metricas.validasCount }}
          </div>
          <span class="text-caption text-medium-emphasis">Emitidas correctamente</span>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="error" class="pa-4">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold">Facturas Anuladas</span>
            <VAvatar color="error" size="36" variant="elevated">
              <VIcon icon="ri-close-circle-line" color="white" size="20" />
            </VAvatar>
          </div>
          <div class="text-h4 font-weight-black text-error">
            {{ metricas.anuladasCount }}
          </div>
          <span class="text-caption text-medium-emphasis">Reportadas anuladas</span>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="info" class="pa-4">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold">Total Emisiones</span>
            <VAvatar color="info" size="36" variant="elevated">
              <VIcon icon="ri-file-list-3-line" color="white" size="20" />
            </VAvatar>
          </div>
          <div class="text-h4 font-weight-black text-info">
            {{ metricas.total }}
          </div>
          <span class="text-caption text-medium-emphasis">Correlativo general</span>
        </VCard>
      </VCol>
    </VRow>

    <!-- Table Card -->
    <VCard class="elevation-1">
      <!-- Search & Filters -->
      <VCardText class="py-3 px-4 border-b">
        <div class="d-flex align-center justify-space-between flex-wrap gap-3">
          <div class="d-flex align-center gap-2">
            <span class="text-caption font-weight-bold text-medium-emphasis">ESTADO:</span>
            <VChip
              :color="filterEstado === 'TODAS' ? 'primary' : 'default'"
              :variant="filterEstado === 'TODAS' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-bold"
              @click="filterEstado = 'TODAS'"
            >
              Todas ({{ metricas.total }})
            </VChip>

            <VChip
              color="success"
              :variant="filterEstado === 'VALIDA' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-bold"
              @click="filterEstado = 'VALIDA'"
            >
              Válidas ({{ metricas.validasCount }})
            </VChip>

            <VChip
              color="error"
              :variant="filterEstado === 'ANULADA' ? 'elevated' : 'tonal'"
              size="small"
              class="cursor-pointer font-weight-bold"
              @click="filterEstado = 'ANULADA'"
            >
              Anuladas ({{ metricas.anuladasCount }})
            </VChip>
          </div>

          <div class="d-flex align-center gap-2">
            <VTextField
              v-model="searchQuery"
              placeholder="Buscar por N°, Cliente, NIT..."
              prepend-inner-icon="ri-search-line"
              density="compact"
              hide-details
              style="width: 260px;"
            />
          </div>
        </div>
      </VCardText>

      <!-- Facturas Data Table -->
      <VTable density="compact" hover>
        <thead>
          <tr>
            <th class="font-weight-bold">N° FACTURA</th>
            <th class="font-weight-bold">FECHA / HORA</th>
            <th class="font-weight-bold">RAZÓN SOCIAL</th>
            <th class="font-weight-bold">NIT / CI</th>
            <th class="font-weight-bold">MÉTODO PAGO</th>
            <th class="font-weight-bold">ESTADO SIAT</th>
            <th class="text-right font-weight-bold">TOTAL BS.</th>
            <th class="text-center font-weight-bold">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredFacturas.length === 0">
            <td colspan="8" class="text-center py-8 text-medium-emphasis">
              No se encontraron facturas con los filtros seleccionados
            </td>
          </tr>
          <tr v-for="f in filteredFacturas" :key="f.id">
            <td class="font-weight-bold text-primary">#{{ f.nro_factura }}</td>
            <td>{{ f.fecha_emision }}</td>
            <td class="font-weight-medium">{{ f.razon_social }}</td>
            <td>{{ f.numero_documento }} ({{ f.tipo_documento }})</td>
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
            <td class="text-right font-weight-black">
              Bs. {{ parseFloat(f.monto_total).toFixed(2) }}
            </td>
            <td class="text-center">
              <div class="d-flex justify-center gap-1">
                <VBtn
                  size="x-small"
                  variant="tonal"
                  color="primary"
                  icon="ri-printer-line"
                  title="Ver e Imprimir Ticket"
                  @click="abrirTicket(f)"
                />
                <VBtn
                  v-if="f.estado === 'VALIDA'"
                  size="x-small"
                  variant="tonal"
                  color="error"
                  icon="ri-close-circle-line"
                  title="Anular Factura en SIAT"
                  @click="anularFactura(f)"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Modal Reimpresion de Ticket Fiscal Térmico 80mm -->
    <VDialog v-model="ticketModalOpen" max-width="450">
      <VCard>
        <VCardItem class="bg-primary text-white py-2">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="font-weight-bold">Factura SIAT N° {{ selectedFactura?.nro_factura }}</span>
            <VIcon icon="ri-file-shield-line" />
          </div>
        </VCardItem>

        <VCardText class="pa-4 ticket-printable">
          <div class="text-center mb-3">
            <h4 class="font-weight-black text-h6 mb-0">RIBERRESTO POS</h4>
            <div class="text-caption font-weight-bold">RIBERSOFT BOLIVIA</div>
            <div class="text-caption">NIT: 1028456023 | Telf: 67369293</div>
            <div class="text-caption">Santa Cruz - Bolivia</div>
            <div class="border-b my-2" />
            <div class="text-subtitle-2 font-weight-bold">
              FACTURA N° {{ selectedFactura?.nro_factura }}
            </div>
            <div class="text-caption text-break font-mono" style="font-size: 9px;">
              CUF: {{ selectedFactura?.cuf }}
            </div>
          </div>

          <div class="text-caption mb-2 border-b pb-2">
            <div><strong>Fecha:</strong> {{ selectedFactura?.fecha_emision }}</div>
            <div><strong>Señor(es):</strong> {{ selectedFactura?.razon_social }}</div>
            <div><strong>NIT/CI:</strong> {{ selectedFactura?.numero_documento }}</div>
            <div><strong>Método:</strong> {{ selectedFactura?.metodo_pago }}</div>
            <div><strong>Estado:</strong> {{ selectedFactura?.estado }}</div>
          </div>

          <!-- Items -->
          <div class="border-b pb-2 mb-2">
            <div class="d-flex justify-space-between text-caption font-weight-bold">
              <span>Cant. / Detalle</span>
              <span>Subtotal</span>
            </div>
            <div
              v-for="det in (selectedFactura?.detalles || [])"
              :key="det.id"
              class="d-flex justify-space-between text-caption py-0"
            >
              <span>{{ det.cantidad }}x {{ det.producto_nombre }}</span>
              <span>Bs. {{ parseFloat(det.subtotal).toFixed(2) }}</span>
            </div>
          </div>

          <div class="text-right text-caption mb-3">
            <div class="text-subtitle-1 font-weight-black">
              TOTAL: Bs. {{ parseFloat(selectedFactura?.monto_total || 0).toFixed(2) }}
            </div>
          </div>

          <div class="text-center mt-2">
            <div class="qr-placeholder mx-auto mb-2 pa-2 border rounded" style="width: 120px; height: 120px; background: #fff;">
              <VIcon icon="ri-qr-code-line" size="100" color="black" />
            </div>
            <div class="text-caption font-weight-bold" style="font-size: 10px;">
              "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"
            </div>
            <div class="text-caption text-medium-emphasis mt-1" style="font-size: 9px;">
              Ley N° 453: El proveedor deberá suministrar el servicio en las modalidades y términos ofertados o convenidos.
            </div>
          </div>
        </VCardText>

        <VCardActions class="pa-3 bg-surface-variant d-flex justify-space-between">
          <VBtn variant="outlined" @click="ticketModalOpen = false">Cerrar</VBtn>
          <VBtn color="primary" variant="flat" prepend-icon="ri-printer-line" @click="printTicket">
            Imprimir Ticket
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.font-mono {
  font-family: monospace;
}
</style>