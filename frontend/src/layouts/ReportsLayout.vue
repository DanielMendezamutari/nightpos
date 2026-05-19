<script setup>
import AppLayout from '../components/AppLayout.vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'

const route = useRoute()
</script>

<template>
  <AppLayout>
    <nav class="reports-subnav" aria-label="Secciones de reportes">
      <RouterLink
        :to="{ name: 'reportes-productos-vendidos' }"
        :class="{ 'reports-subnav__link--active': route.name === 'reportes-productos-vendidos' }"
      >
        Productos vendidos
      </RouterLink>
      <RouterLink
        :to="{ name: 'reportes-ventas' }"
        :class="{ 'reports-subnav__link--active': route.name === 'reportes-ventas' }"
      >
        Ventas
      </RouterLink>
      <RouterLink
        :to="{ name: 'reportes-personal' }"
        :class="{ 'reports-subnav__link--active': route.name === 'reportes-personal' }"
      >
        Por personal
      </RouterLink>
    </nav>
    <div class="reports-modulo">
      <RouterView />
    </div>
  </AppLayout>
</template>

<style scoped>
.reports-subnav {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 18px;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(13, 23, 50, 0.55);
  border: 1px solid rgba(145, 175, 255, 0.18);
}

.reports-subnav a {
  padding: 8px 14px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.88rem;
  text-decoration: none;
  color: var(--color-muted, #97ace4);
  border: 1px solid transparent;
}

.reports-subnav a:hover {
  color: #e8f0ff;
  background: rgba(92, 129, 255, 0.15);
}

.reports-subnav__link--active,
.reports-subnav a.router-link-active {
  color: #fff;
  background: rgba(92, 129, 255, 0.35);
  border-color: rgba(113, 215, 255, 0.35);
}

:root[data-theme='light'] .reports-subnav {
  background: rgba(230, 238, 255, 0.85);
  border-color: rgba(115, 138, 191, 0.28);
}

:root[data-theme='light'] .reports-subnav a {
  color: #4b5f94;
}

:root[data-theme='light'] .reports-subnav a:hover {
  color: #1f2b4d;
}

:root[data-theme='light'] .reports-subnav__link--active,
:root[data-theme='light'] .reports-subnav a.router-link-active {
  color: #1a2744;
  background: rgba(92, 129, 255, 0.22);
  border-color: rgba(80, 110, 200, 0.35);
}

.reports-modulo {
  min-width: 0;
}
</style>
