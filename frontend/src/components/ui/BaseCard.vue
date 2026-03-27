<template>
  <div class="bg-surface-elevated border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Header -->
    <div v-if="title" class="px-6 py-4 border-b border-gray-100 bg-surface-base">
      <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="subtitle" class="mt-1 text-xs text-gray-500">{{ subtitle }}</p>
    </div>

    <!-- Content -->
    <div :class="['px-6 py-4', noPadding ? 'p-0' : '']">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="px-6 py-3 border-t border-gray-100 bg-surface-base">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  subtitle: String,
  noPadding: Boolean,
})
</script>
