<script setup>
import { computed } from 'vue'
import { useNotificationStore } from '../stores/notificationStore'

const notify = useNotificationStore()

/** En plantilla no se puede usar bien notify.notifications.value con refs anidados en un objeto plano. */
const items = computed(() => notify.notifications.value)
</script>

<template>
  <section class="toast-stack" aria-live="polite">
    <article
      v-for="item in items"
      :key="item.id"
      :class="['toast-item', `toast-${item.type}`]"
      @click="notify.remove(item.id)"
    >
      {{ item.message }}
    </article>
  </section>
</template>
