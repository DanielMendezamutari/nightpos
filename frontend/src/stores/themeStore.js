import { ref } from 'vue'

const THEME_KEY = 'nightpos_theme'
const initialTheme = localStorage.getItem(THEME_KEY) || 'dark'
const theme = ref(initialTheme)

applyTheme(theme.value)

function applyTheme(value) {
  document.documentElement.setAttribute('data-theme', value)
}

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  localStorage.setItem(THEME_KEY, theme.value)
  applyTheme(theme.value)
}

export function useThemeStore() {
  return {
    theme,
    toggleTheme,
  }
}
