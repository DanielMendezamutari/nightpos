<script setup>
import { ref, computed, onMounted } from 'vue'
import { useClientesStore } from '@/stores/clientes'
import ClienteModal from '@/components/clientes/ClienteModal.vue'
import AbonoModal from '@/components/clientes/AbonoModal.vue'
import AnticipoModal from '@/components/clientes/AnticipoModal.vue'

const clientesStore = useClientesStore()

// State
const searchQuery = ref('')
const filterType = ref('TODOS') // TODOS, DEUDORES, CUMPLEANEROS
const clienteModalOpen = ref(false)
const abonoModalOpen = ref(false)
const anticipoModalOpen = ref(false)
const selectedCliente = ref(null)
const snackbar = ref({ show: false, text: '', color: 'success' })

// Current Month
const mesActual = new Date().getMonth() + 1

const showNotification = (text, color = 'success') => {
  snackbar.value = { show: true, text, color }
}

const loadClientes = async () => {
  const params = {}
  if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
  if (filterType.value === 'DEUDORES') params.solo_deudores = true
  if (filterType.value === 'CUMPLEANEROS') params.cumpleaneros_mes = mesActual

  await clientesStore.fetchClientes(params)
}

onMounted(() => {
  loadClientes()
})

const onFilterChange = (type) => {
  filterType.value = type
  loadClientes()
}

// Metrics
const totalClientesCount = computed(() => clientesStore.clientes.length)
const totalDeudoresCount = computed(() => clientesStore.clientes.filter(c => parseFloat(c.saldo_deuda) > 0).length)
const totalCarteraPorCobrar = computed(() => {
  return clientesStore.clientes.reduce((acc, c) => acc + (parseFloat(c.saldo_deuda) || 0), 0)
})

// Actions
const abrirNuevoCliente = () => {
  selectedCliente.value = null
  clienteModalOpen.value = true
}

const abrirEditarCliente = (cli) => {
  selectedCliente.value = cli
  clienteModalOpen.value = true
}

const abrirAbonar = (cli) => {
  selectedCliente.value = cli
  abonoModalOpen.value = true
}

const abrirAnticipo = (cli) => {
  selectedCliente.value = cli
  anticipoModalOpen.value = true
}

const confirmarEliminar = async (cli) => {
  if (confirm(`¿Está seguro de desactivar al cliente ${cli.nombre_completo || cli.nombre}?`)) {
    const res = await clientesStore.eliminarCliente(cli.id)
    if (res.success) {
      showNotification('Cliente desactivado correctamente')
    } else {
      showNotification(res.message, 'error')
    }
  }
}
</script>

<template>
  <div class="clientes-page pa-4">
    <!-- Top Bar with RestoTech Cashier Parity -->
    <div class="d-flex align-center justify-space-between flex-wrap gap-3 mb-4">
      <div class="d-flex align-center gap-2">
        <VIcon icon="ri-user-star-fill" size="32" color="primary" />
        <div>
          <h2 class="text-h5 font-weight-black mb-0 text-uppercase letter-spacing-1">
            Clientes & Cuentas Corrientes
          </h2>
          <span class="text-caption text-medium-emphasis">
            Directorio de Clientes, Control de Créditos, Anticipos de Reservas | Ribersoft POS
          </span>
        </div>
      </div>

      <VBtn
        color="primary"
        size="large"
        prepend-icon="ri-user-add-line"
        class="font-weight-black elevation-2"
        @click="abrirNuevoCliente"
      >
        NUEVO CLIENTE (F2)
      </VBtn>
    </div>

    <!-- Summary KPI Cards -->
    <VRow class="mb-4">
      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="primary" class="pa-3">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption font-weight-bold text-uppercase">Total Clientes</div>
              <div class="text-h4 font-weight-black">{{ totalClientesCount }}</div>
            </div>
            <VIcon icon="ri-team-line" size="40" class="opacity-40" />
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="error" class="pa-3">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption font-weight-bold text-uppercase">Clientes con Deuda</div>
              <div class="text-h4 font-weight-black">{{ totalDeudoresCount }}</div>
            </div>
            <VIcon icon="ri-alarm-warning-line" size="40" class="opacity-40" />
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="warning" class="pa-3">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption font-weight-bold text-uppercase">Cartera por Cobrar</div>
              <div class="text-h4 font-weight-black">Bs. {{ totalCarteraPorCobrar.toFixed(2) }}</div>
            </div>
            <VIcon icon="ri-hand-coin-line" size="40" class="opacity-40" />
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="info" class="pa-3">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption font-weight-bold text-uppercase">Filtro Activo</div>
              <div class="text-h6 font-weight-black text-uppercase">{{ filterType }}</div>
            </div>
            <VIcon icon="ri-filter-3-line" size="40" class="opacity-40" />
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filter Bar & Search Input (frmClientesCaja Parity) -->
    <VCard variant="outlined" class="pa-3 mb-4 bg-surface">
      <div class="d-flex align-center justify-space-between flex-wrap gap-3">
        <div class="d-flex align-center gap-2 flex-grow-1" style="max-width: 450px;">
          <VTextField
            v-model="searchQuery"
            label="Buscar por Nombre, NIT/CI o Celular..."
            prepend-inner-icon="ri-search-line"
            density="compact"
            variant="outlined"
            hide-details
            clearable
            @update:model-value="loadClientes"
          />
          <VBtn
            variant="tonal"
            color="primary"
            prepend-icon="ri-search-2-line"
            @click="loadClientes"
          >
            Buscar
          </VBtn>
        </div>

        <!-- Filter Chips -->
        <div class="d-flex gap-2 flex-wrap">
          <VChip
            :color="filterType === 'TODOS' ? 'primary' : 'default'"
            :variant="filterType === 'TODOS' ? 'flat' : 'outlined'"
            filter
            class="font-weight-bold cursor-pointer"
            @click="onFilterChange('TODOS')"
          >
            Todos los Clientes
          </VChip>

          <VChip
            :color="filterType === 'DEUDORES' ? 'error' : 'default'"
            :variant="filterType === 'DEUDORES' ? 'flat' : 'outlined'"
            filter
            class="font-weight-bold cursor-pointer"
            @click="onFilterChange('DEUDORES')"
          >
            Solo Deudores (Crédito)
          </VChip>

          <VChip
            :color="filterType === 'CUMPLEANEROS' ? 'info' : 'default'"
            :variant="filterType === 'CUMPLEANEROS' ? 'flat' : 'outlined'"
            filter
            class="font-weight-bold cursor-pointer"
            @click="onFilterChange('CUMPLEANEROS')"
          >
            <VIcon icon="ri-cake-2-line" class="me-1" />
            Cumpleañeros del Mes
          </VChip>
        </div>
      </div>
    </VCard>

    <!-- Table of Clients -->
    <VCard variant="outlined" class="bg-surface overflow-hidden">
      <VTable density="comfortable" hover>
        <thead class="bg-surface-variant">
          <tr>
            <th class="font-weight-bold">CLIENTE</th>
            <th class="font-weight-bold">NIT / CI</th>
            <th class="font-weight-bold">CONTACTO</th>
            <th class="font-weight-bold">CRÉDITO PERMITIDO</th>
            <th class="font-weight-bold">ESTADO DEUDA</th>
            <th class="font-weight-bold">DESCUENTO</th>
            <th class="font-weight-bold text-center">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="clientesStore.loading">
            <td colspan="7" class="text-center py-6">
              <VProgressCircular indeterminate color="primary" />
              <div class="text-caption mt-2">Cargando directorio de clientes...</div>
            </td>
          </tr>

          <tr v-else-if="!clientesStore.clientes.length">
            <td colspan="7" class="text-center py-6 text-medium-emphasis">
              <VIcon icon="ri-user-search-line" size="48" class="mb-2 opacity-50" />
              <div>No se encontraron clientes registrados con los criterios seleccionados.</div>
            </td>
          </tr>

          <tr
            v-for="cli in clientesStore.clientes"
            v-else
            :key="cli.id"
          >
            <!-- Cliente -->
            <td>
              <div class="font-weight-bold text-subtitle-2">
                {{ cli.nombre }} {{ cli.apellidos || '' }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ cli.razon_social || 'Sin Razón Social' }}
              </div>
            </td>

            <!-- NIT/CI -->
            <td>
              <VChip size="small" variant="tonal" color="secondary" class="font-mono">
                {{ cli.tipo_documento }}: {{ cli.ci_nit }}
              </VChip>
            </td>

            <!-- Contacto -->
            <td>
              <div v-if="cli.celular" class="d-flex align-center gap-1 text-caption">
                <VIcon icon="ri-phone-line" size="14" color="success" />
                <span>{{ cli.celular }}</span>
              </div>
              <div v-if="cli.correo" class="d-flex align-center gap-1 text-caption text-medium-emphasis">
                <VIcon icon="ri-mail-line" size="14" />
                <span>{{ cli.correo }}</span>
              </div>
            </td>

            <!-- Límite Crédito -->
            <td>
              <div v-if="cli.permite_credito">
                <VChip size="small" color="primary" variant="tonal" class="font-weight-bold">
                  Hasta Bs. {{ parseFloat(cli.limite_credito || 0).toFixed(2) }}
                </VChip>
              </div>
              <div v-else class="text-caption text-medium-emphasis">
                No habilitado
              </div>
            </td>

            <!-- Estado Deuda -->
            <td>
              <VChip
                v-if="parseFloat(cli.saldo_deuda) > 0"
                size="small"
                color="error"
                class="font-weight-black"
              >
                Debe Bs. {{ parseFloat(cli.saldo_deuda).toFixed(2) }}
              </VChip>
              <VChip
                v-else
                size="small"
                color="success"
                variant="tonal"
                class="font-weight-bold"
              >
                Al Día (Bs. 0.00)
              </VChip>
            </td>

            <!-- Descuento -->
            <td>
              <span v-if="parseFloat(cli.descuento_porcentaje) > 0" class="font-weight-bold text-info">
                {{ parseFloat(cli.descuento_porcentaje).toFixed(0) }}%
              </span>
              <span v-else class="text-medium-emphasis text-caption">0%</span>
            </td>

            <!-- Acciones -->
            <td class="text-center">
              <div class="d-flex align-center justify-center gap-1">
                <!-- Boton Abonar Deuda -->
                <VTooltip text="Registrar Abono / Pago de Deuda" location="top">
                  <template #activator="{ props: tipProps }">
                    <VBtn
                      v-bind="tipProps"
                      icon="ri-hand-coin-fill"
                      size="small"
                      color="success"
                      variant="tonal"
                      :disabled="parseFloat(cli.saldo_deuda) <= 0"
                      @click="abrirAbonar(cli)"
                    />
                  </template>
                </VTooltip>

                <!-- Boton Anticipo -->
                <VTooltip text="Registrar Anticipo de Reserva" location="top">
                  <template #activator="{ props: tipProps }">
                    <VBtn
                      v-bind="tipProps"
                      icon="ri-calendar-event-line"
                      size="small"
                      color="info"
                      variant="tonal"
                      @click="abrirAnticipo(cli)"
                    />
                  </template>
                </VTooltip>

                <!-- Boton Editar -->
                <VTooltip text="Editar Ficha" location="top">
                  <template #activator="{ props: tipProps }">
                    <VBtn
                      v-bind="tipProps"
                      icon="ri-edit-line"
                      size="small"
                      variant="tonal"
                      @click="abrirEditarCliente(cli)"
                    />
                  </template>
                </VTooltip>

                <!-- Boton Eliminar -->
                <VTooltip text="Desactivar Cliente" location="top">
                  <template #activator="{ props: tipProps }">
                    <VBtn
                      v-bind="tipProps"
                      icon="ri-delete-bin-line"
                      size="small"
                      color="error"
                      variant="text"
                      @click="confirmarEliminar(cli)"
                    />
                  </template>
                </VTooltip>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Modals -->
    <ClienteModal
      v-model="clienteModalOpen"
      :cliente="selectedCliente"
      @guardado="loadClientes"
    />

    <AbonoModal
      v-model="abonoModalOpen"
      :cliente="selectedCliente"
      @abono-registrado="loadClientes"
    />

    <AnticipoModal
      v-model="anticipoModalOpen"
      :cliente="selectedCliente"
      @anticipo-registrado="loadClientes"
    />

    <!-- Notification Snackbar -->
    <VSnackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="3000"
      location="top right"
    >
      {{ snackbar.text }}
    </VSnackbar>
  </div>
</template>

<style scoped>
.clientes-page {
  user-select: none;
}
</style>