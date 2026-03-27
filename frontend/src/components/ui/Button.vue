<template>
  <button
    :class="[
      'inline-flex items-center justify-center font-medium transition-all duration-150 ease-out',
      'focus:outline-none focus:ring-2 focus:ring-offset-2',
      sizeClasses,
      variantClasses,
      {
        'opacity-50 cursor-not-allowed': disabled,
      }
    ]"
    :disabled="disabled"
    v-bind="$attrs"
  >
    <component
      v-if="icon"
      :is="icon"
      :class="['flex-shrink-0', iconSize]"
      :style="{ marginRight: label ? '0.5rem' : '0' }"
    />
    <span v-if="label">{{ label }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'danger'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  icon: {
    type: Object,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const sizeClasses = computed(() => {
  const sizes = {
    'sm': 'px-3 py-1.5 text-sm',
    'md': 'px-4 py-2 text-sm',
    'lg': 'px-6 py-3 text-base'
  }
  return sizes[props.size]
})

const variantClasses = computed(() => {
  const variants = {
    'primary': 'bg-rose-700 text-white hover:bg-rose-800 focus:ring-rose-300',
    'secondary': 'bg-white text-rose-700 border border-rose-300 hover:bg-rose-50 focus:ring-rose-300',
    'ghost': 'bg-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-300',
    'danger': 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-300'
  }
  return variants[props.variant]
})
</script>
