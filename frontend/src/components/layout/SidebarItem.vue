<template>
  <router-link
    :to="to"
    class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-custom transition-all group"
    :class="[
      active 
        ? 'bg-primary-50 text-primary-600 border border-primary-100 shadow-soft' 
        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 border border-transparent hover:border-slate-100'
    ]"
  >
    <component :is="icons[icon]" class="w-4 h-4 transition-colors group-hover:scale-110 duration-200" />
    <span class="flex-1 tracking-tight">{{ label }}</span>
    <ChevronRightIcon v-if="active" class="w-3 h-3 text-primary-400" />
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
