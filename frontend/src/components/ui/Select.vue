<template>
  <select
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    :class="[
      'w-full px-4 py-2.5 rounded-md',
      'text-sm font-normal',
      'bg-white border border-gray-200',
      'text-gray-900 placeholder:text-gray-500',
      'transition-all duration-150',
      'focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 focus:bg-white',
      'disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 disabled:border-gray-200',
      'appearance-none cursor-pointer',
      'hover:border-gray-300',
      className
    ]"
    :style="{ backgroundImage: `url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>')`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem' }"
    :disabled="disabled"
    v-bind="$attrs"
  >
    <option v-if="placeholder" value="" disabled select>{{ placeholder }}</option>
    <slot>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </slot>
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
</script>

defineEmits(['update:modelValue'])
</script>
