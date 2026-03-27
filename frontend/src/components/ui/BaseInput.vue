<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-900 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      :disabled="disabled"
      :class="[
        'w-full px-3.5 py-2.5 rounded-md border text-sm font-normal transition-all duration-150',
        'focus:outline-none focus:ring-1 focus:ring-offset-0',
        error
          ? 'border-status-error text-gray-900 placeholder:text-gray-500 bg-red-50 focus:ring-status-error'
          : 'border-gray-200 text-gray-900 placeholder:text-gray-500 bg-white focus:ring-accent-pink-500 focus:border-accent-pink-300',
        disabled && 'bg-gray-100 text-gray-500 cursor-not-allowed border-gray-200',
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      v-bind="$attrs"
    />
    <p v-if="error" class="text-sm text-red-600 mt-1.5">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-gray-500 mt-1.5">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  type: {
    type: String,
    default: 'text',
  },
  label: String,
  placeholder: String,
  error: String,
  hint: String,
  required: Boolean,
  disabled: Boolean,
})

defineEmits(['update:modelValue', 'blur'])
</script>
