import { ref } from 'vue'

const notifications = ref([])
let seed = 0

function push(type, message, ttl = 4500) {
  const id = ++seed
  notifications.value.push({ id, type, message })

  if (ttl > 0) {
    window.setTimeout(() => remove(id), ttl)
  }
}

function remove(id) {
  notifications.value = notifications.value.filter((n) => n.id !== id)
}

export function useNotificationStore() {
  return {
    notifications,
    remove,
    success: (message, ttl) => push('success', message, ttl),
    error: (message, ttl) => push('error', message, ttl),
    warning: (message, ttl) => push('warning', message, ttl),
    info: (message, ttl) => push('info', message, ttl),
  }
}
