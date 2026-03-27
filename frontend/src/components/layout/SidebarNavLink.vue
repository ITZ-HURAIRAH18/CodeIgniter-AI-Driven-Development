<template>
  <router-link
    :to="to"
    class="flex items-center gap-3 px-3 py-2 text-xs font-bold rounded-custom transition-all group border border-transparent"
    :class="[
      isActive 
        ? 'bg-primary-50 text-primary-600 border-primary-100 shadow-soft-md scale-[1.02]' 
        : 'text-slate-500 hover:bg-white hover:text-slate-800 hover:border-slate-200 hover:shadow-soft'
    ]"
    :title="collapsed ? label : ''"
  >
    <component :is="icons[icon]" class="w-4.5 h-4.5 transition-transform group-hover:scale-110 duration-300" />
    <span v-if="!collapsed" class="flex-1 tracking-tight truncate">{{ label }}</span>
    <div v-if="isActive && !collapsed" class="w-1.5 h-1.5 rounded-full bg-primary-500 shadow-glow"></div>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import * as LucideIcons from 'lucide-vue-next'

const icons = LucideIcons
const route = useRoute()

const props = defineProps({
  label: { type: String, required: true },
  icon: { type: String, required: true },
  to: { type: String, required: true },
  collapsed: { type: Boolean, default: false }
})

const isActive = computed(() => {
  return route.path === props.to || (props.to !== '/' && route.path.startsWith(props.to))
})
</script>

<style scoped>
.shadow-glow {
  box-shadow: 0 0 10px rgba(79, 70, 229, 0.4);
}
</style>
