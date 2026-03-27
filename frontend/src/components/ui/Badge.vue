<template>
  <span
    :class="[
      'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium',
      'transition-colors duration-150',
      variantClasses
    ]"
  >
    <component
      v-if="icon"
      :is="icon"
      :class="['w-3 h-3 mr-1.5 flex-shrink-0']"
    />
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'success', 'warning', 'error', 'info'].includes(value)
  },
  label: {
    type: String,
    required: true
  },
  icon: {
    type: Object,
    default: null
  }
})

const variantClasses = computed(() => {
  const variants = {
    'default': 'bg-slate-100 text-slate-700',
    'success': 'bg-green-50 text-green-700',
    'warning': 'bg-orange-50 text-orange-700',
    'error': 'bg-red-50 text-red-700',
    'info': 'bg-cyan-50 text-cyan-700'
  }
  return variants[props.variant]
})
</script>
