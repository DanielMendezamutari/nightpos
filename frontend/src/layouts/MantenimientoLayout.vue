<script setup>
import AppLayout from '../components/AppLayout.vue'
import { RouterView } from 'vue-router'
</script>

<template>
  <AppLayout>
    <div class="admin-modulo">
      <RouterView />
    </div>
  </AppLayout>
</template>
