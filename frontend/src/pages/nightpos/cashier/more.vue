<script setup>

import CashierAccountSection from '@/components/nightpos/cashier/CashierAccountSection.vue'

import CashierShell from '@/components/nightpos/cashier/CashierShell.vue'

import { useCashierMoreMenu } from '@/composables/useCashierMoreMenu'



definePage({

  meta: {

    layout: 'blank',

  },

})



const { visibleSections, hasItems } = useCashierMoreMenu()

</script>



<template>

  <CashierShell active-tab="mas" :show-pending="false">

    <div>

      <h4 class="text-h5 mb-1">

        Más opciones

      </h4>

      <p class="text-body-2 text-medium-emphasis mb-4">

        Accesos secundarios según sus permisos. No incluye el menú administrativo completo.

      </p>



      <VAlert

        v-if="!hasItems"

        type="info"

        variant="tonal"

        class="mb-5"

      >

        No hay opciones adicionales disponibles para su usuario.

      </VAlert>



      <template v-else>

        <section

          v-for="section in visibleSections"

          :key="section.title"

          class="mb-5"

        >

          <p class="text-overline text-medium-emphasis mb-2">

            {{ section.title }}

          </p>



          <VList class="cashier-more-list">

            <VListItem

              v-for="item in section.items"

              :key="item.to"

              :to="{ name: item.to }"

              :prepend-icon="item.icon"

              rounded

              class="mb-2"

            >

              <VListItemTitle class="font-weight-medium">

                {{ item.title }}

              </VListItemTitle>

              <VListItemSubtitle>{{ item.subtitle }}</VListItemSubtitle>

            </VListItem>

          </VList>

        </section>

      </template>



      <VDivider class="mb-5" />



      <CashierAccountSection variant="panel" />

    </div>

  </CashierShell>

</template>



<style scoped>

.cashier-more-list {

  background: transparent;

}

</style>

