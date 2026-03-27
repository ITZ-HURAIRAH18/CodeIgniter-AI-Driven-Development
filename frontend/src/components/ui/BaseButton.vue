<template>
  <button
    :class="[
      'inline-flex items-center justify-center gap-2 rounded-md font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2',
      variantClass,
      sizeClass,
      { 'opacity-50 cursor-not-allowed': disabled || loading }
    ]"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <span v-if="loading" class="animate-spin h-4 w-4 border-2 border-white/30 border-t-white rounded-full"></span>
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: v => ['primary', 'secondary', 'ghost', 'danger'].includes(v)
  },
  size: {
    type: String,
    default: 'md',
    validator: v => ['sm', 'md', 'lg'].includes(v)
  },
  loading: Boolean,
  disabled: Boolean,
})

const variantClass = computed(() => {
  const map = {
    primary: 'bg-accent-pink-500 text-white hover:bg-accent-pink-600 focus:ring-accent-pink-500',
    secondary: 'bg-white border border-gray-200 text-gray-900 hover:bg-gray-50 focus:ring-accent-pink-500',
    ghost: 'bg-transparent text-gray-600 hover:bg-gray-100 focus:ring-gray-300',
    danger: 'bg-status-error text-white hover:bg-red-600 focus:ring-red-500',
    success: 'bg-status-success text-white hover:bg-green-600 focus:ring-status-success',
  }
  return map[props.variant]
})

const sizeClass = computed(() => {
  const map = {
    sm: 'px-3 py-1.5 text-xs h-8',
    md: 'px-4 py-2.5 text-sm h-10',
    lg: 'px-6 py-3 text-base h-12',
  }
  return map[props.size]
})
</script>
