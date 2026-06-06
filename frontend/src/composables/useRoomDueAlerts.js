const STORAGE_SILENT = 'nightpos_room_due_silent'
const STORAGE_SEEN = 'nightpos_room_due_seen_ids'

function playBeepFallback() {
  try {
    const ctx = new AudioContext()
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()

    osc.connect(gain)
    gain.connect(ctx.destination)
    osc.frequency.value = 880
    gain.gain.value = 0.15
    osc.start()
    osc.stop(ctx.currentTime + 0.25)
  }
  catch {
    // ignore
  }
}

export function useRoomDueAlerts() {
  const silenced = ref(localStorage.getItem(STORAGE_SILENT) === '1')
  const seenIds = ref(new Set(JSON.parse(localStorage.getItem(STORAGE_SEEN) || '[]')))

  const persistSeen = () => {
    localStorage.setItem(STORAGE_SEEN, JSON.stringify([...seenIds.value]))
  }

  const playAlertSound = () => {
    if (silenced.value)
      return

    const audio = new Audio('/sounds/room-due.mp3')

    audio.play().catch(() => playBeepFallback())
  }

  const handleDueItems = items => {
    const list = items ?? []
    let played = false

    for (const item of list) {
      const id = item.id
      if (item.checked_at || seenIds.value.has(id))
        continue

      seenIds.value.add(id)
      if (!played) {
        playAlertSound()
        played = true
      }
    }

    if (played)
      persistSeen()
  }

  const markItemReviewed = id => {
    seenIds.value.add(id)
    persistSeen()
  }

  const toggleSilence = () => {
    silenced.value = !silenced.value
    localStorage.setItem(STORAGE_SILENT, silenced.value ? '1' : '0')
  }

  return {
    silenced,
    handleDueItems,
    markItemReviewed,
    toggleSilence,
  }
}
