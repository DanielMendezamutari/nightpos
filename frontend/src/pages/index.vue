<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const currentTime = ref(new Date().toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' }))

setInterval(() => {
  currentTime.value = new Date().toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' })
}, 10000)

const kpis = [
  {
    title: 'Ventas del Turno',
    value: 'Bs. 3,450.00',
    subtitle: '+12.4% vs día anterior',
    icon: 'ri-money-dollar-circle-line',
    color: 'success',
  },
  {
    title: 'Mesas Ocupadas',
    value: '14 / 28',
    subtitle: '50% ocupación de salón',
    icon: 'ri-restaurant-2-line',
    color: 'primary',
  },
  {
    title: 'Comandas Activas',
    value: '6 pedidos',
    subtitle: '4 en cocina, 2 en bar',
    icon: 'ri-fire-line',
    color: 'warning',
  },
  {
    title: 'Facturación SIAT',
    value: '42 emitidas',
    subtitle: '100% sincronizadas en línea',
    icon: 'ri-file-shield-line',
    color: 'info',
  },
]

const recentOrders = [
  { table: 'Mesa 4', waiter: 'Mesero (5678)', total: 'Bs. 210.00', status: 'En preparación', color: 'warning' },
  { table: 'Mesa 12', waiter: 'Mesero (5678)', total: 'Bs. 480.00', status: 'Cuenta solicitada', color: 'info' },
  { table: 'Mesa 2', waiter: 'Cajero (1234)', total: 'Bs. 95.00', status: 'Cobrado', color: 'success' },
  { table: 'Mesa 7', waiter: 'Mesero (5678)', total: 'Bs. 340.00', status: 'Consumiendo', color: 'primary' },
]
</script>

<template>
  <div>
    <!-- Welcome Header Banner -->
    <VCard class="mb-6 elevation-2">
      <VCardText class="py-6">
        <VRow align="center">
          <VCol cols="12" md="8">
            <div class="d-flex align-center gap-3 mb-2 flex-wrap">
              <h4 class="text-h4 font-weight-bold text-high-emphasis">
                ¡Bienvenido, {{ authStore.userName }}!
              </h4>
              <VChip
                color="success"
                size="small"
                variant="tonal"
                class="font-weight-bold"
              >
                Turno Abierto • {{ currentTime }}
              </VChip>
            </div>
            <p class="text-body-1 text-medium-emphasis mb-0">
              Sistema <strong>RiberResto POS</strong> • Sucursal: <strong>{{ authStore.branchCode }}</strong> (Casa Ribersoft Demo). Todas las funciones operativas listas para atención táctil.
            </p>
          </VCol>

          <VCol cols="12" md="4" class="text-md-end">
            <a
              href="https://wa.me/59167369293?text=Hola%20Ribersoft,%20soporte%20técnico%20RiberResto%20POS"
              target="_blank"
              class="text-decoration-none"
            >
              <VBtn
                color="success"
                prepend-icon="ri-whatsapp-line"
                variant="elevated"
                class="font-weight-bold"
              >
                Soporte Cel. 67369293
              </VBtn>
            </a>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- KPI Statistics Grid -->
    <VRow class="mb-6">
      <VCol
        v-for="kpi in kpis"
        :key="kpi.title"
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard class="h-100">
          <VCardText class="d-flex align-center gap-4">
            <VAvatar
              :color="kpi.color"
              variant="tonal"
              size="52"
              rounded="lg"
            >
              <VIcon :icon="kpi.icon" size="30" />
            </VAvatar>

            <div class="flex-grow-1">
              <div class="text-body-2 text-medium-emphasis mb-1">
                {{ kpi.title }}
              </div>
              <h5 class="text-h5 font-weight-bold mb-1">
                {{ kpi.value }}
              </h5>
              <div class="text-caption text-medium-emphasis">
                {{ kpi.subtitle }}
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Quick Operations & Recent Activity -->
    <VRow class="mb-6">
      <!-- Fast POS Actions -->
      <VCol cols="12" lg="7">
        <VCard class="h-100">
          <VCardItem title="Accesos Rápidos del Sistema">
            <template #subtitle>
              Módulos principales de atención táctil para restaurantes
            </template>
          </VCardItem>

          <VCardText>
            <VRow>
              <VCol cols="12" sm="6">
                <VCard variant="outlined" class="pa-4 text-center cursor-pointer hover-card">
                  <VAvatar color="primary" variant="tonal" size="50" class="mb-3">
                    <VIcon icon="ri-layout-grid-line" size="28" />
                  </VAvatar>
                  <h6 class="text-h6 font-weight-bold mb-1">
                    Plano de Mesas & Salón
                  </h6>
                  <p class="text-caption text-medium-emphasis mb-3">
                    Control visual interactivo de áreas, salones y cuentas
                  </p>
                  <VBtn size="small" color="primary" block>
                    Módulo Bucle 2
                  </VBtn>
                </VCard>
              </VCol>

              <VCol cols="12" sm="6">
                <VCard variant="outlined" class="pa-4 text-center cursor-pointer hover-card">
                  <VAvatar color="warning" variant="tonal" size="50" class="mb-3">
                    <VIcon icon="ri-file-add-line" size="28" />
                  </VAvatar>
                  <h6 class="text-h6 font-weight-bold mb-1">
                    Comandas & Cocina
                  </h6>
                  <p class="text-caption text-medium-emphasis mb-3">
                    Envío directo a impresoras térmicas y pantalla KDS
                  </p>
                  <VBtn size="small" color="warning" variant="tonal" block>
                    Ver Comandas
                  </VBtn>
                </VCard>
              </VCol>

              <VCol cols="12" sm="6">
                <VCard variant="outlined" class="pa-4 text-center cursor-pointer hover-card">
                  <VAvatar color="success" variant="tonal" size="50" class="mb-3">
                    <VIcon icon="ri-safe-2-line" size="28" />
                  </VAvatar>
                  <h6 class="text-h6 font-weight-bold mb-1">
                    Caja & Turnos
                  </h6>
                  <p class="text-caption text-medium-emphasis mb-3">
                    Apertura, arqueos, retiros y corte X/Z de caja
                  </p>
                  <VBtn size="small" color="success" variant="tonal" block>
                    Arqueo de Caja
                  </VBtn>
                </VCard>
              </VCol>

              <VCol cols="12" sm="6">
                <VCard variant="outlined" class="pa-4 text-center cursor-pointer hover-card">
                  <VAvatar color="info" variant="tonal" size="50" class="mb-3">
                    <VIcon icon="ri-qr-code-line" size="28" />
                  </VAvatar>
                  <h6 class="text-h6 font-weight-bold mb-1">
                    Facturación Bolivia SIAT
                  </h6>
                  <p class="text-caption text-medium-emphasis mb-3">
                    Modalidad computarizada y electrónica con CUFD/CUF
                  </p>
                  <VBtn size="small" color="info" variant="tonal" block>
                    Estado SIAT
                  </VBtn>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Recent Orders / Active Tables -->
      <VCol cols="12" lg="5">
        <VCard class="h-100">
          <VCardItem title="Mesas con Movimiento Activo">
            <template #subtitle>
              Cuentas abiertas en el turno actual
            </template>
          </VCardItem>

          <VCardText class="pa-0">
            <VList lines="two">
              <VListItem
                v-for="order in recentOrders"
                :key="order.table"
                class="px-4"
              >
                <template #prepend>
                  <VAvatar color="primary" variant="tonal" class="me-3">
                    <VIcon icon="ri-restaurant-line" />
                  </VAvatar>
                </template>

                <VListItemTitle class="font-weight-bold">
                  {{ order.table }}
                </VListItemTitle>
                <VListItemSubtitle class="text-caption">
                  Atiende: {{ order.waiter }}
                </VListItemSubtitle>

                <template #append>
                  <div class="text-end">
                    <div class="font-weight-bold text-body-1">
                      {{ order.total }}
                    </div>
                    <VChip :color="order.color" size="x-small" variant="tonal">
                      {{ order.status }}
                    </VChip>
                  </div>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Ribersoft Marketing & Support Card -->
    <VCard class="bg-primary text-white elevation-3">
      <VCardText class="pa-6">
        <VRow align="center">
          <VCol cols="12" md="8">
            <div class="d-flex align-center gap-2 mb-2">
              <VIcon icon="ri-medal-line" size="26" />
              <h5 class="text-h5 font-weight-bold text-white mb-0">
                Ribersoft — Soluciones Tecnológicas de Alta Gama
              </h5>
            </div>
            <p class="text-body-1 mb-0 opacity-90">
              ¿Deseas personalizar <strong>RiberResto POS</strong> con módulos a medida, impresoras fiscales, reportes avanzados o comanderas móviles? Contáctate directamente con nuestro equipo de ingeniería.
            </p>
          </VCol>

          <VCol cols="12" md="4" class="text-md-end">
            <a
              href="https://wa.me/59167369293?text=Hola%20Ribersoft,%20quiero%20información%20sobre%20RiberResto%20POS"
              target="_blank"
              class="text-decoration-none"
            >
              <VBtn
                color="white"
                class="text-primary font-weight-bold"
                size="large"
                prepend-icon="ri-phone-fill"
              >
                Cel. 67369293
              </VBtn>
            </a>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
.hover-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
}
</style>