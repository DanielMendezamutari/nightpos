<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="app-root">
    <div class="app-root__main">
      <RouterView />
    </div>
    <footer class="app-global-footer" role="contentinfo">
      <span class="app-global-footer__brand">Ribersoft</span>
      <span class="app-global-footer__sep" aria-hidden="true">·</span>
      <a class="app-global-footer__tel" href="tel:+50667369293">6736-9293</a>
    </footer>
  </div>
</template>
