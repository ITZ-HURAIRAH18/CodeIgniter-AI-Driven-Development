<template>
  <router-link
    :to="to"
    class="flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-lg transition-all group whitespace-nowrap"
    :class="[
      active 
        ? 'bg-rose-50 text-rose-700 border border-rose-100 shadow-soft' 
        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800 border border-transparent'
    ]"
  >
    <component :is="icons[icon]" class="w-4 h-4 transition-colors flex-shrink-0" />
    <span class="flex-1 truncate">{{ label }}</span>
    <ChevronRightIcon v-if="active" class="w-3 h-3 text-rose-400 flex-shrink-0" />
  </router-link>
</template>

<script setup>
import * as LucideIcons from 'lucide-vue-next'
import { ChevronRightIcon } from 'lucide-vue-next'

const icons = LucideIcons

defineProps({
  label: { type: String, required: true },
  icon: { type: String, required: true },
  to: { type: String, default: '#' },
  active: { type: Boolean, default: false }
})
</script>
