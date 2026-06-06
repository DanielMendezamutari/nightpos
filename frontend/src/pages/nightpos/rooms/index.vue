<script setup>
definePage({ meta: { permission: 'rooms.access' } })

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-rooms-dashboard' })
})
</script>

<template>
  <div />
</template>
