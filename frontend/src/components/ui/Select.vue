<template>
  <select
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    :class="[
      'w-full px-3 py-2 rounded-custom',
      'text-sm font-normal',
      'bg-white border border-border-default',
      'text-slate-900',
      'transition-all duration-150',
      'focus:outline-none focus:border-rose-300 focus:bg-rose-50 focus:shadow-rose-sm',
      'disabled:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-400',
      'appearance-none cursor-pointer',
      className
    ]"
    :disabled="disabled"
    v-bind="$attrs"
  >
    <option v-if="placeholder" value="">{{ placeholder }}</option>
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Select option...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>
