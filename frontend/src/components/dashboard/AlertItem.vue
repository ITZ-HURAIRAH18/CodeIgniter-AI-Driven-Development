<template>
  <div 
    class="flex gap-4 p-4 rounded-custom border transition-all hover:scale-[1.01] active:scale-[0.99] duration-200 cursor-pointer"
    :class="[
      type === 'error' ? 'bg-red-50/50 border-red-100 text-red-900 group shadow-red-50/50' : 'bg-amber-50/50 border-amber-100 text-amber-900 group shadow-amber-50/50',
      'shadow-soft'
    ]"
  >
    <div :class="['p-2 rounded-lg self-start transition-colors', type === 'error' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600']">
      <AlertCircleIcon v-if="type === 'error'" class="w-4 h-4" />
      <AlertTriangleIcon v-else class="w-4 h-4" />
    </div>
    <div class="flex-1 space-y-1">
      <h4 class="text-xs font-bold tracking-tight uppercase">{{ title }}</h4>
      <p class="text-[11px] font-medium opacity-70">{{ desc }}</p>
      <button :class="['text-[10px] font-bold uppercase tracking-wider mt-2 group-hover:underline', type === 'error' ? 'text-red-700' : 'text-amber-700']">
        {{ action }} →
      </button>
    </div>
  </div>
</template>

<script setup>
import { AlertCircleIcon, AlertTriangleIcon } from 'lucide-vue-next'

defineProps({
  type: { type: String, default: 'error' },
  title: String,
  desc: String,
  action: String,
})
</script>
