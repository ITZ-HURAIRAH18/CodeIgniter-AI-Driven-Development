<template>
  <span
    :class="[
      'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold',
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
    default: 'neutral',
    validator: (value) => ['neutral', 'success', 'warning', 'error', 'info', 'pink', 'teal', 'purple', 'blue'].includes(value)
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
    'neutral': 'bg-gray-100 text-gray-700',
    'success': 'bg-status-success/15 text-status-success',
    'warning': 'bg-status-warning/15 text-status-warning',
    'error': 'bg-status-error/15 text-status-error',
    'info': 'bg-status-info/15 text-status-info',
    'pink': 'bg-accent-pink-100 text-accent-pink-700',
    'teal': 'bg-accent-teal-100 text-accent-teal-700',
    'purple': 'bg-accent-purple-100 text-accent-purple-700',
    'blue': 'bg-accent-blue-100 text-accent-blue-700',
  }
  return variants[props.variant]
})
</script>
