<template>
  <button
    :class="[
      'erp-btn',
      variantClasses[variant],
      sizeClasses[size],
      loading && 'opacity-70 pointer-events-none',
      $attrs.class
    ]"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <slot name="icon-left">
      <span v-if="loading" class="animate-spin h-4 w-4 border-2 border-white/30 border-t-white rounded-full"></span>
    </slot>
    <slot></slot>
    <slot name="icon-right"></slot>
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
  },
  size: {
    type: String,
    default: 'md',
  },
  loading: Boolean,
  disabled: Boolean,
})

const variantClasses = {
  primary: 'erp-btn-primary',
  secondary: 'erp-btn-secondary',
  danger: 'erp-btn-danger',
  ghost: 'erp-btn-ghost',
}

const sizeClasses = {
  sm: 'h-8 px-2.5 text-xs',
  md: 'h-9 px-3 text-sm',
  lg: 'h-11 px-5 text-base',
}
</script>
