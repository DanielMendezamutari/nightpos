<script setup>

import { computed } from 'vue'



const props = defineProps({

  order: { type: Object, required: true },

})



const orderId = computed(() => props.order.id)

const status = computed(() => props.order.status)



const isOpen = computed(() => status.value === 'OPEN')

const isSentToBar = computed(() => status.value === 'SENT_TO_BAR')

const isPendingCharge = computed(() => ['SENT_TO_BAR', 'IN_PREPARATION', 'READY'].includes(status.value))

const isBilled = computed(() => status.value === 'BILLED')



const detailRoute = (query = {}) => ({

  name: 'nightpos-waiter-orders-id',

  params: { id: orderId.value },

  query,

})

</script>



<template>

  <div class="waiter-order-actions d-flex flex-column gap-2">

    <!-- OPEN: gestionar + agregar + enviar barra -->

    <template v-if="isOpen">

      <VBtn

        block

        size="large"

        color="primary"

        prepend-icon="ri-edit-line"

        :to="detailRoute()"

      >

        Gestionar

      </VBtn>

      <VBtn

        block

        size="large"

        color="primary"

        variant="tonal"

        prepend-icon="ri-add-line"

        :to="detailRoute({ add: 1 })"

      >

        + Producto

      </VBtn>

      <VBtn

        block

        size="large"

        color="warning"

        variant="tonal"

        prepend-icon="ri-send-plane-line"

        :to="detailRoute({ send: 1 })"

      >

        Enviar barra

      </VBtn>

    </template>



    <!-- SENT_TO_BAR: ver + agregar extra -->

    <template v-else-if="isSentToBar">

      <VBtn

        block

        size="large"

        color="primary"

        variant="tonal"

        prepend-icon="ri-eye-line"

        :to="detailRoute()"

      >

        Ver

      </VBtn>

      <VBtn

        block

        size="large"

        color="primary"

        prepend-icon="ri-add-line"

        :to="detailRoute({ add: 1 })"

      >

        Agregar extra

      </VBtn>

    </template>



    <!-- Pendiente cobro -->

    <template v-else-if="isPendingCharge && !isSentToBar">

      <VBtn

        block

        size="large"

        color="primary"

        variant="tonal"

        prepend-icon="ri-eye-line"

        :to="detailRoute()"

      >

        Ver

      </VBtn>

      <VAlert

        type="info"

        variant="tonal"

        density="compact"

        class="mb-0"

      >

        Pendiente de cobro por caja

      </VAlert>

    </template>



    <!-- BILLED: solo historial -->

    <template v-else-if="isBilled">

      <VBtn

        block

        size="large"

        color="secondary"

        variant="tonal"

        prepend-icon="ri-history-line"

        :to="detailRoute()"

      >

        Ver historial

      </VBtn>

    </template>



    <!-- Otros estados activos (fallback) -->

    <template v-else>

      <VBtn

        block

        size="large"

        color="primary"

        variant="tonal"

        prepend-icon="ri-eye-line"

        :to="detailRoute()"

      >

        Ver

      </VBtn>

    </template>

  </div>

</template>


