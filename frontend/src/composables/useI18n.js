import { ref, computed } from 'vue'
import en from '@/locales/en.json'
import ur from '@/locales/ur.json'
import zh from '@/locales/zh.json'

// Supported languages
const LANGUAGES = {
  en: { name: 'English', flag: '🇺🇸', locale: en },
  ur: { name: 'اردو', flag: '🇵🇰', locale: ur },
  zh: { name: '中文', flag: '🇨🇳', locale: zh }
}

// Current language state - persisted to localStorage
const currentLanguage = ref(localStorage.getItem('app_language') || 'en')

// Watch for changes and persist
const setLanguage = (lang) => {
  if (LANGUAGES[lang]) {
    currentLanguage.value = lang
    localStorage.setItem('app_language', lang)

    // Update document lang attribute for accessibility
    document.documentElement.lang = lang
    // Keep layout LTR (no RTL direction changes)
    document.documentElement.dir = 'ltr'
    document.body.dir = 'ltr'
  }
}

// Get current locale object
const getCurrentLocale = () => {
  return LANGUAGES[currentLanguage.value]?.locale || LANGUAGES.en.locale
}

// Reactive locale computed property
const reactiveLocale = computed(() => getCurrentLocale())

// Main translation function - supports nested keys and interpolation
// Accesses reactiveLocale.value to ensure reactivity
const t = (key, params = {}) => {
  // Access reactiveLocale to establish reactive dependency
  const locale = reactiveLocale.value
  const keys = key.split('.')
  let value = locale

  // Navigate through nested keys
  for (const k of keys) {
    if (value && typeof value === 'object') {
      value = value[k]
    } else {
      return key // Return key if not found
    }
  }

  // Handle interpolation: "Hello {name}" with { name: 'John' }
  if (typeof value === 'string' && Object.keys(params).length > 0) {
    let result = value
    for (const [key, val] of Object.entries(params)) {
      result = result.replace(`{${key}}`, val)
    }
    return result
  }

  return typeof value === 'string' ? value : key
}

// Get all available languages
const getAvailableLanguages = () => {
  return Object.entries(LANGUAGES).map(([code, data]) => ({
    code,
    name: data.name,
    flag: data.flag
  }))
}

// Get current language info
const getCurrentLanguageName = () => {
  return LANGUAGES[currentLanguage.value]?.name || 'English'
}

// Get current language flag
const getCurrentLanguageFlag = () => {
  return LANGUAGES[currentLanguage.value]?.flag || '🇺🇸'
}

// Get current language direction (ltr or rtl)
const getCurrentLanguageDir = () => {
  return LANGUAGES[currentLanguage.value]?.dir || 'ltr'
}

// Check if current language is RTL
const isRTL = computed(() => {
  return getCurrentLanguageDir() === 'rtl'
})

// Computed for reactive language updates
const language = computed(() => currentLanguage.value)
const languageName = computed(() => getCurrentLanguageName())
const languageFlag = computed(() => getCurrentLanguageFlag())
const languageDir = computed(() => getCurrentLanguageDir())

// Initialize language on load
setLanguage(currentLanguage.value)

export function useI18n() {
  return {
    t,
    setLanguage,
    language,
    languageName,
    languageFlag,
    languageDir,
    isRTL,
    getAvailableLanguages,
    getCurrentLanguage: () => currentLanguage.value,
    LANGUAGES
  }
}
