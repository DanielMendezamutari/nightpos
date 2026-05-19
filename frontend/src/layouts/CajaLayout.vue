<script setup>
import AppLayout from '../components/AppLayout.vue'
import { RouterView } from 'vue-router'
</script>

<template>
  <AppLayout>
    <div class="admin-modulo caja-modulo">
      <RouterView />
    </div>
  </AppLayout>
</template>

<style scoped>
.caja-modulo {
  width: 100%;
}
</style>
