<template>
  <div class="erp-card p-5 group hover:border-primary-200 transition-colors">
    <div class="flex items-start justify-between">
      <div class="space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-primary-500 transition-colors">
          {{ label }}
        </p>
        <h3 class="text-xl font-bold text-slate-800 tracking-tight">{{ value }}</h3>
      </div>
      <div :class="['p-2 rounded-lg transition-all group-hover:scale-110 duration-300 shadow-soft-md', iconColor]">
        <component :is="icons[icon]" class="w-5 h-5" />
      </div>
    </div>
    
    <div class="mt-4 flex items-center gap-1.5 pt-4 border-t border-slate-50">
      <TrendingUpIcon v-if="trendUp" class="w-3.5 h-3.5 text-emerald-500" />
      <TrendingDownIcon v-else class="w-3.5 h-3.5 text-red-500" />
      <span :class="['text-[11px] font-bold tracking-tight', trendUp ? 'text-emerald-600' : 'text-red-600']">
        {{ trend }}
      </span>
    </div>
  </div>
</template>

<script setup>
import * as LucideIcons from 'lucide-vue-next'
import { TrendingUpIcon, TrendingDownIcon } from 'lucide-vue-next'

const icons = LucideIcons

defineProps({
  label: String,
  value: String,
  trend: String,
  trendUp: Boolean,
  icon: String,
  iconColor: String,
})
</script>
