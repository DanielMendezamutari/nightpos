<script setup>
definePage({ meta: { permission: 'products.create' } })

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-products' })
})
</script>

<template>
  <div class="pa-4 text-center text-medium-emphasis">
    Redirigiendo al catálogo de productos…
  </div>
</template>
