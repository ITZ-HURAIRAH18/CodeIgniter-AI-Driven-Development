<template>
  <div
    :class="[
      'bg-white rounded-custom border',
      'border-border-default',
      'shadow-soft hover:shadow-soft-md transition-shadow duration-150',
      'overflow-hidden',
      className
    ]"
  >
    <div v-if="title" :class="['px-gutter py-3 border-b border-border-default', titleClass]">
      <h3 :class="['text-lg font-semibold text-slate-900', titleSize]">
        {{ title }}
      </h3>
      <p v-if="subtitle" :class="['text-sm text-slate-600 mt-1']">{{ subtitle }}</p>
    </div>
    <div :class="['px-gutter', { 'py-gutter': !noPadding }]">
      <slot />
    </div>
    <div v-if="$slots.footer" :class="['px-gutter py-3 bg-surface-alt border-t border-border-default']">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  },
  titleClass: {
    type: String,
    default: ''
  },
  titleSize: {
    type: String,
    default: 'text-lg'
  }
})
</script>
