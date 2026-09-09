<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useSalonMesaStore } from '@/stores/salonMesa'
import { useAuthStore } from '@/stores/auth'
import ComandaModal from '@/components/pos/ComandaModal.vue'

const salonStore = useSalonMesaStore()
const authStore = useAuthStore()

const filtroArea = ref('TODAS')
const searchMesa = ref('')
const selectedMesaForComanda = ref(null)
const comandaModalOpen = ref(false)

let autoRefreshInterval = null

onMounted(async () => {
  await loadComandas()
  autoRefreshInterval = setInterval(async () => {
    await loadComandas()
  }, 10000)
})

onUnmounted(() => {
  if (autoRefreshInterval) clearInterval(autoRefreshInterval)
})

const loadComandas = async () => {
  await salonStore.fetchSalones()
}

// Mesas con comanda activa (OCUPADAS o PRECUENTA)
const mesasConComanda = computed(() => {
  let list = salonStore.mesas.filter(m => m.estado === 'OCUPADA' || m.estado === 'PRECUENTA')

  if (searchMesa.value.trim()) {
    const q = searchMesa.value.toLowerCase().trim()
    list = list.filter(m => m.codigo.toLowerCase().includes(q) || m.nombre.toLowerCase().includes(q))
  }

  return list
})

const totalPlatosActivos = computed(() => {
  return mesasConComanda.value.length
})

const abrirTomaPedido = (mesa) => {
  selectedMesaForComanda.value = mesa
  comandaModalOpen.value = true
}

const onComandaEnviada = async () => {
  await loadComandas()
}
</script>

<template>
  <div class="comandas-page-container">
    <!-- Top Header -->
    <div class="d-flex align-center justify-space-between flex-wrap gap-2 mb-4">
      <div>
        <h2 class="text-h5 font-weight-bold mb-0">Monitor de Comandas & Cocina (KDS)</h2>
        <span class="text-caption text-medium-emphasis">
          RiberResto POS | Gestión de Pedidos en Vivo para Cocineros, Barman y Meseros
        </span>
      </div>

      <div class="d-flex align-center gap-2">
        <VChip color="error" variant="elevated" class="font-weight-bold">
          <VIcon icon="ri-fire-line" class="me-1" />
          {{ totalPlatosActivos }} Mesas Activas
        </VChip>
        <VBtn
          icon="ri-refresh-line"
          size="small"
          variant="tonal"
          color="primary"
          :loading="salonStore.loading"
          @click="loadComandas"
        />
      </div>
    </div>

    <!-- Filter Bar -->
    <VCard class="mb-4 elevation-1">
      <VCardText class="py-2 px-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-3">
          <div class="d-flex align-center gap-2">
            <span class="text-caption font-weight-bold text-medium-emphasis">DESTINO:</span>
            <VBtnToggle
              v-model="filtroArea"
              mandatory
              density="compact"
              color="primary"
              variant="outlined"
            >
              <VBtn value="TODAS">TODAS LAS ÁREAS</VBtn>
              <VBtn value="COCINA">COCINA</VBtn>
              <VBtn value="BAR">BAR & BEBIDAS</VBtn>
            </VBtnToggle>
          </div>

          <div class="d-flex align-center gap-2">
            <VTextField
              v-model="searchMesa"
              placeholder="Buscar mesa..."
              prepend-inner-icon="ri-search-line"
              density="compact"
              hide-details
              style="width: 180px;"
            />
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Cards Grid of Active Table Orders -->
    <VRow v-if="mesasConComanda.length > 0" class="match-height">
      <VCol
        v-for="mesa in mesasConComanda"
        :key="mesa.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <VCard class="comanda-ticket-card elevation-2 h-100 d-flex flex-column justify-space-between">
          <!-- Card Header -->
          <VCardItem class="bg-primary text-white py-2 px-3">
            <div class="d-flex align-center justify-space-between w-100">
              <div class="font-weight-black text-h6">
                {{ mesa.codigo }} - {{ mesa.nombre }}
              </div>
              <VChip size="x-small" color="white" variant="tonal" class="font-weight-bold">
                {{ mesa.personas }} pers.
              </VChip>
            </div>
            <div class="d-flex align-center justify-space-between text-caption text-white opacity-90 mt-1">
              <span>Mesero: {{ mesa.mesero_nombre || 'Mesero' }}</span>
              <span class="d-flex align-center gap-1 font-weight-bold">
                <VIcon icon="ri-time-line" size="14" />
                {{ mesa.minutos_abierta }} min
              </span>
            </div>
          </VCardItem>

          <!-- Card Body -->
          <VCardText class="pa-3 flex-grow-1">
            <div class="text-caption font-weight-bold text-medium-emphasis mb-2">
              ESTADO DE ATENCIÓN:
            </div>

            <VChip
              :color="mesa.minutos_abierta > 30 ? 'error' : (mesa.minutos_abierta > 15 ? 'warning' : 'success')"
              size="small"
              class="font-weight-bold mb-3"
            >
              <VIcon icon="ri-timer-line" class="me-1" />
              {{ mesa.minutos_abierta > 30 ? 'DEMORA CRÍTICA' : (mesa.minutos_abierta > 15 ? 'EN ATENCIÓN' : 'RECIÉN ENVIADO') }}
            </VChip>

            <div class="d-flex align-center justify-space-between border-t pt-2 mt-2">
              <span class="text-caption font-weight-bold">TOTAL CONSUMO:</span>
              <span class="text-h6 font-weight-black text-primary">
                Bs. {{ (parseFloat(mesa.total_consumo) || 0).toFixed(2) }}
              </span>
            </div>
          </VCardText>

          <!-- Card Actions -->
          <VCardActions class="pa-3 bg-surface-variant border-t d-flex justify-space-between">
            <VBtn
              size="small"
              color="primary"
              variant="elevated"
              prepend-icon="ri-add-circle-line"
              class="w-100 font-weight-bold"
              @click="abrirTomaPedido(mesa)"
            >
              + Agregar / Modificar Pedido
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <!-- Empty State -->
    <VCard v-else class="pa-12 text-center elevation-1 mt-4">
      <VAvatar color="success" variant="tonal" size="72" class="mb-3">
        <VIcon icon="ri-checkbox-circle-line" size="44" color="success" />
      </VAvatar>
      <h3 class="text-h5 font-weight-bold mb-1">¡Cocina al Día!</h3>
      <p class="text-body-2 text-medium-emphasis mb-0">
        No hay comandas pendientes de preparación en este momento.
      </p>
    </VCard>

    <!-- Modal Toma de Pedidos -->
    <ComandaModal
      v-model="comandaModalOpen"
      :mesa="selectedMesaForComanda"
      @comanda-enviada="onComandaEnviada"
    />
  </div>
</template>

<style scoped>
.comanda-ticket-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(var(--v-border-color), 0.16);
}
</style>