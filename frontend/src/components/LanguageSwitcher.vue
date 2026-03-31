<template>
  <div class="relative group">
    <!-- Language Button -->
    <button
      class="flex items-center gap-2 px-3 py-2 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors border border-slate-200"
      title="Change language"
    >
      <span class="text-lg">{{ languageFlag }}</span>
      <span class="hidden sm:inline">{{ languageName }}</span>
      <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </button>

    <!-- Language Dropdown Menu -->
    <div
      class="absolute right-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
    >
      <div class="py-2">
        <button
          v-for="lang in availableLanguages"
          :key="lang.code"
          @click="handleSelectLanguage(lang.code)"
          :class="[
            'w-full px-4 py-2 text-left text-sm font-medium transition-colors flex items-center gap-2',
            lang.code === currentLanguage
              ? 'bg-accent-pink-50 text-accent-pink-700'
              : 'text-slate-700 hover:bg-slate-50'
          ]"
        >
          <span class="text-lg">{{ lang.flag }}</span>
          <span class="flex-1">{{ lang.name }}</span>
          <!-- Checkmark for active language -->
          <svg
            v-if="lang.code === currentLanguage"
            class="w-4 h-4 text-accent-pink-600"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from '@/composables/useI18n'

const { setLanguage, language, languageFlag, languageName, getAvailableLanguages } = useI18n()

const currentLanguage = computed(() => language.value)
const availableLanguages = computed(() => getAvailableLanguages())

const handleSelectLanguage = (langCode) => {
  setLanguage(langCode)
  // Optional: Show feedback to user
  console.log(`✅ Language changed to: ${langCode}`)
}
</script>

<style scoped>
/* Ensures dropdown appears above other elements */
.group:hover > div {
  z-index: 50;
}
</style>
