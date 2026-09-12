<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useSalonMesaStore } from '@/stores/salonMesa'
import { $api } from '@/utils/api'

const router = useRouter()
const authStore = useAuthStore()
const salonStore = useSalonMesaStore()

// State
const tipoCambio = ref(6.80)
const loading = ref(false)

// Modals
const modalMesas = ref(false)
const modalNuevoSalon = ref(false)
const modalNuevaMesa = ref(false)

// Selected Salon in Admin Mesas
const adminSalonId = ref(null)
const adminMesas = ref([])

// Form Salon
const salonForm = ref({
  id: null,
  nombre: '',
  codigo: '',
  impresora_cuenta: 'Termica-Salon',
  impresora_factura: 'Termica-Caja-Central',
})

// Form Mesa
const mesaForm = ref({
  id: null,
  salon_id: null,
  codigo: '',
  nombre: '',
  capacidad: 4,
  forma: 'cuadrada',
})

onMounted(async () => {
  await salonStore.fetchSalones()
  if (salonStore.salones.length > 0) {
    adminSalonId.value = salonStore.salones[0].id
    await cargarMesasSalon(adminSalonId.value)
  }
})

const cargarMesasSalon = async (salonId) => {
  adminSalonId.value = salonId
  try {
    const res = await $api(`/api/v1/salones/${salonId}/mesas`)
    adminMesas.value = res.data || []
  } catch (err) {
    console.error('Error cargando mesas admin:', err)
  }
}

// Open RestoTech Modules
const irAModulo = (modulo) => {
  switch (modulo) {
    case 'mesas':
      modalMesas.value = true
      break
    case 'productos':
      router.push({ name: 'inventario' })
      break
    case 'compras':
      router.push({ name: 'inventario' })
      break
    case 'proveedores':
      router.push({ name: 'inventario' })
      break
    case 'personal':
      alert('Gestión de Personal RestoTech: Acceso de roles disponible en Panel de Usuarios.')
      break
    case 'ajustes':
      router.push({ name: 'inventario' })
      break
    case 'reportes':
      router.push({ name: 'reportes' })
      break
    case 'gastos':
      router.push({ name: 'caja' })
      break
    case 'caja':
      router.push({ name: 'caja' })
      break
    case 'cuentas':
      router.push({ name: 'clientes' })
      break
    case 'configuracion':
      alert('Configuración General RestoTech: Parámetros del Restaurante, Impresoras y Moneda Bs. 6.80.')
      break
    case 'factura_siat':
      router.push({ name: 'facturas' })
      break
    case 'anular_facturas':
      router.push({ name: 'facturas' })
      break
    case 'movimientos':
      router.push({ name: 'caja' })
      break
    case 'salir':
      authStore.logout()
      router.push({ name: 'login' })
      break
  }
}

// Actions Salón
const abrirCrearSalon = () => {
  salonForm.value = {
    id: null,
    nombre: '',
    codigo: '',
    impresora_cuenta: 'Termica-Salon',
    impresora_factura: 'Termica-Caja-Central',
  }
  modalNuevoSalon.value = true
}

const guardarSalon = async () => {
  if (!salonForm.value.nombre.trim()) {
    alert('El nombre del salón es obligatorio')
    return
  }
  loading.value = true
  try {
    const res = await $api('/api/v1/salones', {
      method: 'POST',
      body: salonForm.value,
    })
    if (res.success) {
      modalNuevoSalon.value = false
      await salonStore.fetchSalones()
      adminSalonId.value = res.data.id
      await cargarMesasSalon(res.data.id)
    }
  } catch (err) {
    alert('Error al guardar salón: ' + (err.data?.message || err.message))
  } finally {
    loading.value = false
  }
}

// Actions Mesa
const abrirCrearMesa = () => {
  mesaForm.value = {
    id: null,
    salon_id: adminSalonId.value,
    codigo: String((adminMesas.value.length + 1)),
    nombre: `Mesa ${adminMesas.value.length + 1}`,
    capacidad: 4,
    forma: 'cuadrada',
  }
  modalNuevaMesa.value = true
}

const guardarMesa = async () => {
  if (!mesaForm.value.nombre.trim()) {
    alert('El nombre de la mesa es obligatorio')
    return
  }
  loading.value = true
  try {
    const res = await $api('/api/v1/mesas', {
      method: 'POST',
      body: mesaForm.value,
    })
    if (res.success) {
      modalNuevaMesa.value = false
      await cargarMesasSalon(adminSalonId.value)
      await salonStore.fetchSalones()
    }
  } catch (err) {
    alert('Error al guardar mesa: ' + (err.data?.message || err.message))
  } finally {
    loading.value = false
  }
}

const eliminarMesa = async (mesa) => {
  if (!confirm(`¿Está seguro de eliminar la ${mesa.nombre}?`)) return
  try {
    const res = await $api(`/api/v1/mesas/${mesa.id}`, { method: 'DELETE' })
    if (res.success) {
      await cargarMesasSalon(adminSalonId.value)
      await salonStore.fetchSalones()
    }
  } catch (err) {
    alert('Error al eliminar mesa: ' + (err.data?.message || err.message))
  }
}
</script>

<template>
  <div class="restotech-admin-container pa-4">
    <!-- Header RestoTech Faithful (Image 3 & 4) -->
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap gap-4">
      <div class="d-flex align-center gap-4">
        <!-- Logo top tech Style -->
        <div class="restotech-badge-logo">
          <div class="toptech-brand">
            <span class="top-part">top</span><span class="tech-part">tech</span>
          </div>
        </div>
        <div>
          <h2 class="text-h5 font-weight-bold mb-0">Administración RestoTech</h2>
          <span class="text-caption text-medium-emphasis">Módulos Administrativos y Configuración RiberResto POS</span>
        </div>
      </div>

      <!-- Quick Status Indicators (Image 4 top left) -->
      <div class="d-flex align-center gap-3">
        <VCard variant="outlined" class="px-3 py-1 bg-white">
          <div class="text-caption text-disabled">Cambio</div>
          <div class="font-weight-bold text-primary">Bs. {{ tipoCambio.toFixed(1) }}</div>
        </VCard>
        <VCard variant="outlined" class="px-3 py-1 bg-white">
          <div class="text-caption text-disabled">Turno</div>
          <div class="font-weight-bold text-success">Turno Nro 1</div>
        </VCard>
      </div>
    </div>

    <!-- RestoTech Admin 15-Button Matrix (Faithful to Image 4) -->
    <VCard elevation="2" class="pa-6 rounded-lg bg-surface">
      <div class="text-subtitle-2 font-weight-bold text-medium-emphasis mb-4 text-uppercase">
        Módulos del Sistema RestoTech
      </div>

      <div class="restotech-grid-matrix">
        <!-- 1. Mesas -->
        <button class="restotech-tile-btn" @click="irAModulo('mesas')">
          <div class="tile-icon-wrapper blue-grad">
            <VIcon icon="ri-layout-masonry-line" size="40" color="white" />
          </div>
          <span class="tile-label">Mesas</span>
        </button>

        <!-- 2. Productos -->
        <button class="restotech-tile-btn" @click="irAModulo('productos')">
          <div class="tile-icon-wrapper cyan-grad">
            <VIcon icon="ri-file-list-3-line" size="40" color="white" />
          </div>
          <span class="tile-label">Productos</span>
        </button>

        <!-- 3. Compras -->
        <button class="restotech-tile-btn" @click="irAModulo('compras')">
          <div class="tile-icon-wrapper orange-grad">
            <VIcon icon="ri-shopping-basket-line" size="40" color="white" />
          </div>
          <span class="tile-label">Compras</span>
        </button>

        <!-- 4. Proveedores -->
        <button class="restotech-tile-btn" @click="irAModulo('proveedores')">
          <div class="tile-icon-wrapper grey-grad">
            <VIcon icon="ri-user-2-line" size="40" color="white" />
          </div>
          <span class="tile-label">Proveedores</span>
        </button>

        <!-- 5. Salir -->
        <button class="restotech-tile-btn" @click="irAModulo('salir')">
          <div class="tile-icon-wrapper red-grad">
            <VIcon icon="ri-logout-box-r-line" size="40" color="white" />
          </div>
          <span class="tile-label font-weight-bold text-error">Salir</span>
        </button>

        <!-- 6. Personal -->
        <button class="restotech-tile-btn" @click="irAModulo('personal')">
          <div class="tile-icon-wrapper red-grad">
            <VIcon icon="ri-t-shirt-line" size="40" color="white" />
          </div>
          <span class="tile-label">Personal</span>
        </button>

        <!-- 7. Ajustes de Inventarios -->
        <button class="restotech-tile-btn" @click="irAModulo('ajustes')">
          <div class="tile-icon-wrapper blue-grad">
            <VIcon icon="ri-checkbox-multiple-line" size="40" color="white" />
          </div>
          <span class="tile-label">Ajustes de Inventarios</span>
        </button>

        <!-- 8. Reportes -->
        <button class="restotech-tile-btn" @click="irAModulo('reportes')">
          <div class="tile-icon-wrapper purple-grad">
            <VIcon icon="ri-book-read-line" size="40" color="white" />
          </div>
          <span class="tile-label">Reportes</span>
        </button>

        <!-- 9. Gastos -->
        <button class="restotech-tile-btn" @click="irAModulo('gastos')">
          <div class="tile-icon-wrapper green-grad">
            <VIcon icon="ri-hand-coin-line" size="40" color="white" />
          </div>
          <span class="tile-label">Gastos</span>
        </button>

        <!-- 10. Control de Caja -->
        <button class="restotech-tile-btn" @click="irAModulo('caja')">
          <div class="tile-icon-wrapper green-grad">
            <VIcon icon="ri-shopping-bag-3-line" size="40" color="white" />
          </div>
          <span class="tile-label">Control de Caja</span>
        </button>

        <!-- 11. Cuentas -->
        <button class="restotech-tile-btn" @click="irAModulo('cuentas')">
          <div class="tile-icon-wrapper green-grad">
            <VIcon icon="ri-wallet-3-line" size="40" color="white" />
          </div>
          <span class="tile-label">Cuentas</span>
        </button>

        <!-- 12. Manual de Ayuda -->
        <button class="restotech-tile-btn" @click="irAModulo('configuracion')">
          <div class="tile-icon-wrapper lime-grad">
            <VIcon icon="ri-questionnaire-line" size="40" color="white" />
          </div>
          <span class="tile-label">Manual de ayuda</span>
        </button>

        <!-- 13. Configuración -->
        <button class="restotech-tile-btn" @click="irAModulo('configuracion')">
          <div class="tile-icon-wrapper amber-grad">
            <VIcon icon="ri-tools-line" size="40" color="white" />
          </div>
          <span class="tile-label">Configuración</span>
        </button>

        <!-- 14. Factura SIAT -->
        <button class="restotech-tile-btn" @click="irAModulo('factura_siat')">
          <div class="tile-icon-wrapper maroon-grad">
            <VIcon icon="ri-printer-line" size="40" color="white" />
          </div>
          <span class="tile-label">Factura SIAT</span>
        </button>

        <!-- 15. Movimientos -->
        <button class="restotech-tile-btn highlight-gold" @click="irAModulo('movimientos')">
          <div class="tile-icon-wrapper yellow-grad">
            <VIcon icon="ri-money-dollar-circle-line" size="40" color="white" />
          </div>
          <span class="tile-label font-weight-bold">Movimientos</span>
        </button>
      </div>
    </VCard>

    <!-- DIALOG: Gestión y Creación de Salones y Mesas (RestoTech Faithful) -->
    <VDialog v-model="modalMesas" max-width="900" scrollable>
      <VCard>
        <VCardItem class="bg-primary text-white">
          <template #prepend>
            <VIcon icon="ri-layout-masonry-line" size="26" class="me-2" />
          </template>
          <VCardTitle class="text-white font-weight-bold">
            Configuración y Creación de Mesas & Salones RestoTech
          </VCardTitle>
          <VCardSubtitle class="text-white opacity-80">
            Administre los ambientes, capacidad de comensales y distribución física
          </VCardSubtitle>
        </VCardItem>

        <VCardText class="pa-4">
          <!-- Top bar with Salones Tabs & Add Buttons -->
          <div class="d-flex align-center justify-space-between flex-wrap gap-3 mb-4">
            <VTabs v-model="adminSalonId" @update:model-value="cargarMesasSalon">
              <VTab v-for="sal in salonStore.salones" :key="sal.id" :value="sal.id">
                {{ sal.nombre }}
              </VTab>
            </VTabs>

            <div class="d-flex gap-2">
              <VBtn variant="outlined" color="primary" prepend-icon="ri-add-line" @click="abrirCrearSalon">
                + Nuevo Salón
              </VBtn>
              <VBtn color="success" prepend-icon="ri-add-circle-line" @click="abrirCrearMesa">
                + Nueva Mesa
              </VBtn>
            </div>
          </div>

          <!-- Mesas Table of Selected Salon -->
          <VTable density="comfortable" class="border rounded">
            <thead>
              <tr class="bg-var-theme-background">
                <th>CÓDIGO</th>
                <th>NOMBRE</th>
                <th>CAPACIDAD</th>
                <th>FORMA</th>
                <th>ESTADO ACTUAL</th>
                <th class="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in adminMesas" :key="m.id">
                <td class="font-weight-bold">{{ m.codigo }}</td>
                <td class="font-weight-medium text-primary">{{ m.nombre }}</td>
                <td>{{ m.capacidad }} personas</td>
                <td>
                  <VChip size="small" variant="tonal">{{ m.forma || 'cuadrada' }}</VChip>
                </td>
                <td>
                  <VChip size="small" :color="m.estado === 'LIBRE' ? 'success' : 'error'">
                    {{ m.estado }}
                  </VChip>
                </td>
                <td class="text-center">
                  <VBtn
                    icon="ri-delete-bin-line"
                    size="small"
                    color="error"
                    variant="text"
                    :disabled="m.estado !== 'LIBRE'"
                    @click="eliminarMesa(m)"
                  />
                </td>
              </tr>
              <tr v-if="adminMesas.length === 0">
                <td colspan="6" class="text-center pa-6 text-disabled">
                  No hay mesas registradas en este salón. Haga clic en "+ Nueva Mesa".
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>

        <VCardActions class="pa-4 bg-var-theme-background d-flex justify-end">
          <VBtn variant="outlined" color="secondary" @click="modalMesas = false">
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- SUB-MODAL: Crear Nuevo Salón -->
    <VDialog v-model="modalNuevoSalon" max-width="500">
      <VCard>
        <VCardItem class="bg-primary text-white">
          <VCardTitle class="text-white font-weight-bold">+ Nuevo Salón / Ambiente</VCardTitle>
        </VCardItem>
        <VCardText class="pa-4">
          <VTextField
            v-model="salonForm.nombre"
            label="Nombre del Salón *"
            placeholder="Ej. Terraza Norte, Salón Principal"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            required
          />
          <VTextField
            v-model="salonForm.codigo"
            label="Código (Opcional)"
            placeholder="Ej. SAL-05"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
          <VTextField
            v-model="salonForm.impresora_cuenta"
            label="Impresora Pre-cuenta"
            placeholder="Termica-Salon"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
        </VCardText>
        <VCardActions class="pa-4 bg-var-theme-background d-flex justify-end gap-2">
          <VBtn variant="outlined" color="secondary" @click="modalNuevoSalon = false">Cancelar</VBtn>
          <VBtn color="primary" :loading="loading" @click="guardarSalon">Guardar Salón</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- SUB-MODAL: Crear Nueva Mesa -->
    <VDialog v-model="modalNuevaMesa" max-width="500">
      <VCard>
        <VCardItem class="bg-success text-white">
          <VCardTitle class="text-white font-weight-bold">+ Nueva Mesa</VCardTitle>
        </VCardItem>
        <VCardText class="pa-4">
          <VSelect
            v-model="mesaForm.salon_id"
            :items="salonStore.salones"
            item-title="nombre"
            item-value="id"
            label="Salón Asignado *"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
          <VTextField
            v-model="mesaForm.nombre"
            label="Nombre de la Mesa *"
            placeholder="Ej. Mesa 13, Barra 5"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
          <VTextField
            v-model="mesaForm.codigo"
            label="Código / Número"
            placeholder="13"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
          <VTextField
            v-model.number="mesaForm.capacidad"
            type="number"
            label="Capacidad (Personas)"
            min="1"
            max="50"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
          <VSelect
            v-model="mesaForm.forma"
            :items="[
              { title: 'Cuadrada', value: 'cuadrada' },
              { title: 'Redonda', value: 'redonda' },
              { title: 'Rectangular', value: 'rectangular' },
            ]"
            label="Forma Visual"
            variant="outlined"
            density="comfortable"
          />
        </VCardText>
        <VCardActions class="pa-4 bg-var-theme-background d-flex justify-end gap-2">
          <VBtn variant="outlined" color="secondary" @click="modalNuevaMesa = false">Cancelar</VBtn>
          <VBtn color="success" :loading="loading" @click="guardarMesa">Crear Mesa</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.restotech-admin-container {
  max-width: 1200px;
  margin: 0 auto;
}

.restotech-badge-logo {
  display: inline-flex;
  align-items: center;
}

.toptech-brand {
  display: inline-flex;
  border-radius: 8px;
  overflow: hidden;
  font-weight: 900;
  font-size: 28px;
  letter-spacing: -1px;
}

.top-part {
  background: #1b357f;
  color: #ffffff;
  padding: 4px 12px;
  border-radius: 8px 0 0 8px;
}

.tech-part {
  background: #999;
  color: #fff;
  padding: 4px 12px;
  border-radius: 0 8px 8px 0;
}

.restotech-grid-matrix {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

.restotech-tile-btn {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  padding: 20px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.restotech-tile-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.12);
  border-color: #1b357f;
}

.tile-icon-wrapper {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.tile-label {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-align: center;
}

.highlight-gold {
  background: #fff9e6;
  border: 2px solid #ffd54f;
}

.blue-grad { background: linear-gradient(135deg, #1976d2, #1565c0); }
.cyan-grad { background: linear-gradient(135deg, #00bcd4, #0097a7); }
.orange-grad { background: linear-gradient(135deg, #ff9800, #f57c00); }
.grey-grad { background: linear-gradient(135deg, #78909c, #546e7a); }
.red-grad { background: linear-gradient(135deg, #e53935, #c62828); }
.purple-grad { background: linear-gradient(135deg, #8e24aa, #6a1b9a); }
.green-grad { background: linear-gradient(135deg, #43a047, #2e7d32); }
.lime-grad { background: linear-gradient(135deg, #7cb342, #558b2f); }
.amber-grad { background: linear-gradient(135deg, #fb8c00, #e65100); }
.maroon-grad { background: linear-gradient(135deg, #8d6e63, #5d4037); }
.yellow-grad { background: linear-gradient(135deg, #fbc02d, #f57f17); }
</style>
