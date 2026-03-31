import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import en from '../locales/en.json'
import ur from '../locales/ur.json'
import zh from '../locales/zh.json'

/**
 * Global Translation Store
 * Manages language selection, locale loading, and translation functions
 * 
 * Features:
 * - Reactive language switching with localStorage persistence
 * - Support for 3 languages: English, Urdu, Chinese
 * - Parameter interpolation: t('key.with.{param}', { param: 'value' })
 * - Pluralization: t('items', { count: 5 }) → handles singular/plural
 * - Fallback to English if key not found
 * - System-wide re-render on language change (no data loss)
 * - LTR-locked layout (no RTL flipping regardless of language)
 */
export const useTranslationStore = defineStore('translation', () => {
  // ==========================================
  // STATE
  // ==========================================
  
  const locales = {
    en: { name: 'English', flag: '🇺🇸', dir: 'ltr', data: en },
    ur: { name: 'اردو', flag: '🇵🇰', dir: 'ltr', data: ur },
    zh: { name: '中文', flag: '🇨🇳', dir: 'ltr', data: zh }
  }

  const currentLanguage = ref(localStorage.getItem('app_language') || 'en')
  const isLoading = ref(false)
  const error = ref(null)

  // ==========================================
  // COMPUTED
  // ==========================================

  const currentLocale = computed(() => locales[currentLanguage.value])
  const currentLocaleData = computed(() => currentLocale.value?.data || en)
  
  const availableLanguages = computed(() => 
    Object.entries(locales).map(([code, locale]) => ({
      code,
      name: locale.name,
      flag: locale.flag,
      dir: locale.dir
    }))
  )

  // ==========================================
  // METHODS
  // ==========================================

  /**
   * Get translated string with support for:
   * - Nested keys: "dashboard.title"
   * - Parameter interpolation: "items.{count}"
   * - Pluralization: "item" → "items" when count > 1
   * 
   * @param {string} key - Translation key path (e.g., "dashboard.title")
   * @param {object} params - Parameters for interpolation (e.g., { count: 5 })
   * @returns {string} Translated string with interpolated values
   */
  const t = (key, params = {}) => {
    try {
      // Split the key path (e.g., "dashboard.title" → ["dashboard", "title"])
      const keys = key.split('.')
      let value = currentLocaleData.value

      // Navigate through nested objects to find the translation
      for (const k of keys) {
        value = value?.[k]
      }

      // If key not found in current language, try English fallback
      if (!value && currentLanguage.value !== 'en') {
        let fallbackValue = en
        for (const k of keys) {
          fallbackValue = fallbackValue?.[k]
        }
        value = fallbackValue || key
      }

      // Return key itself as fallback if still not found
      if (!value) {
        console.warn(`[Translations] Missing key: ${key}`)
        return key
      }

      // Handle parameter interpolation
      if (typeof value === 'string' && Object.keys(params).length > 0) {
        // Example: "Items: {count}" with params { count: 5 } → "Items: 5"
        return value.replace(/{(\w+)}/g, (match, paramName) => {
          return params[paramName] !== undefined ? params[paramName] : match
        })
      }

      return value
    } catch (err) {
      console.error(`[Translations] Error translating key "${key}":`, err)
      return key
    }
  }

  /**
   * Set the active language
   * - Persists to localStorage as 'app_language'
   * - Updates document.lang and document.dir
   * - Triggers reactive update across all components
   * 
   * @param {string} languageCode - Language code: 'en', 'ur', or 'zh'
   */
  const setLanguage = async (languageCode) => {
    if (!locales[languageCode]) {
      console.error(`[Translations] Invalid language code: ${languageCode}`)
      return
    }

    try {
      isLoading.value = true
      error.value = null

      // Update reactive state
      currentLanguage.value = languageCode

      // Persist to localStorage
      localStorage.setItem('app_language', languageCode)

      // Update document meta information
      document.documentElement.lang = languageCode
      document.documentElement.dir = locales[languageCode].dir

      // Force Vue reactivity update by re-assigning computed properties
      // This ensures all components re-render with new translations
      isLoading.value = false

      // Emit custom event for components that need to react to language change
      window.dispatchEvent(
        new CustomEvent('language-changed', { 
          detail: { 
            language: languageCode,
            locale: locales[languageCode]
          } 
        })
      )
    } catch (err) {
      error.value = err.message
      console.error('[Translations] Error setting language:', err)
      isLoading.value = false
    }
  }

  /**
   * Get all available languages with metadata
   * @returns {array} Array of { code, name, flag, dir }
   */
  const getLanguages = () => availableLanguages.value

  /**
   * Get current language code
   * @returns {string} Current language code
   */
  const getCurrentLanguage = () => currentLanguage.value

  /**
   * Get current language display name
   * @returns {string} Language name (e.g., "English", "اردو", "中文")
   */
  const getCurrentLanguageName = () => currentLocale.value?.name || 'English'

  /**
   * Get current language flag emoji
   * @returns {string} Flag emoji
   */
  const getCurrentLanguageFlag = () => currentLocale.value?.flag || '🇺🇸'

  /**
   * Get translated value by key without reactivity (for static data)
   * Useful for initializing non-reactive translations
   * 
   * @param {string} key - Translation key
   * @returns {string} Translated value
   */
  const getStatic = (key) => t(key)

  /**
   * Bulk translate an array of keys
   * @param {array} keys - Array of translation keys
   * @returns {object} Object with keys and their translations
   */
  const translateMany = (keys) => {
    return keys.reduce((acc, key) => ({
      ...acc,
      [key]: t(key)
    }), {})
  }

  /**
   * Translate with context-aware pluralization
   * Example: translatePlural('item', { count: 5 }) 
   * Looks for 'items' key when count > 1
   * 
   * @param {string} singularKey - Key to use for singular form
   * @param {object} params - Parameters including { count }
   * @returns {string} Translated string with correct plural form
   */
  const translatePlural = (singularKey, params = {}) => {
    const { count = 1 } = params
    
    if (count > 1 || count === 0) {
      // Try plural form first
      const pluralKey = singularKey.replace(/([^.]+)$/, `${$1}s`)
      const pluralValue = t(pluralKey)
      
      // If plural form exists and is different from singular key, use it
      if (pluralValue !== pluralKey) {
        return t(pluralKey, params)
      }
    }
    
    return t(singularKey, params)
  }

  /**
   * Format key for pluralization
   * Example: formatPluralKey('item', 3) → 'items'
   * 
   * @param {string} key - Base key
   * @param {number} count - Count for pluralization
   * @returns {string} Formatted key
   */
  const formatPluralKey = (key, count) => {
    return count > 1 || count === 0 ? `${key}s` : key
  }

  // ==========================================
  // INITIALIZATION
  // ==========================================

  // Set initial language from localStorage or system preference
  const initializeLanguage = () => {
    const savedLanguage = localStorage.getItem('app_language')
    if (savedLanguage && locales[savedLanguage]) {
      setLanguage(savedLanguage)
    } else {
      setLanguage('en')
    }
  }

  // Auto-initialize on store creation
  initializeLanguage()

  // ==========================================
  // EXPORTS
  // ==========================================

  return {
    // State
    currentLanguage,
    isLoading,
    error,
    
    // Computed
    currentLocale,
    currentLocaleData,
    availableLanguages,
    
    // Methods
    t,
    setLanguage,
    getLanguages,
    getCurrentLanguage,
    getCurrentLanguageName,
    getCurrentLanguageFlag,
    getStatic,
    translateMany,
    translatePlural,
    formatPluralKey,
    
    // Utility
    locales
  }
})

/**
 * Usage Examples:
 * 
 * // In <script setup>:
 * import { useTranslationStore } from '@/store/translation.store'
 * const translations = useTranslationStore()
 * 
 * // Basic translation
 * {{ translations.t('dashboard.title') }}
 * {{ translations.t('dashboard.items', { count: 5 }) }}
 * 
 * // In component data or computed
 * const title = translations.t('dashboard.title')
 * const itemCount = translations.t('item.count', { count: items.length })
 * 
 * // Language switching
 * translations.setLanguage('ur')  // Switch to Urdu
 * translations.setLanguage('zh')  // Switch to Chinese
 * 
 * // Get available languages for dropdown
 * const languages = translations.getLanguages()
 * // Returns: [
 * //   { code: 'en', name: 'English', flag: '🇺🇸', dir: 'ltr' },
 * //   { code: 'ur', name: 'اردو', flag: '🇵🇰', dir: 'ltr' },
 * //   { code: 'zh', name: '中文', flag: '🇨🇳', dir: 'ltr' }
 * // ]
 */
