<template>
  <input
    :type="type"
    :placeholder="placeholder"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    :class="[
      'w-full px-3 py-2 rounded-custom',
      'text-sm font-normal',
      'bg-white border border-border-default',
      'text-slate-900 placeholder-slate-500',
      'transition-all duration-150',
      'focus:outline-none focus:border-rose-300 focus:bg-rose-50 focus:shadow-rose-sm',
      'disabled:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-400',
      className
    ]"
    v-bind="$attrs"
  />
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  className: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>
