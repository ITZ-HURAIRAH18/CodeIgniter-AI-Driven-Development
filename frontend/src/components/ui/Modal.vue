<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="show" class="fixed inset-0 z-[100] overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 text-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <!-- Modal content -->
        <div 
          class="relative w-full transform overflow-hidden rounded-xl bg-white p-6 text-left align-middle shadow-xl transition-all border border-slate-200"
          :class="maxWidthClass"
        >
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-900 leading-6">{{ title }}</h3>
            <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
              <XIcon class="w-5 h-5" />
            </button>
          </div>

          <div class="mt-2">
            <slot />
          </div>

          <div v-if="$slots.footer" class="mt-8 flex justify-end gap-3">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { XIcon } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  title: String,
  maxWidth: {
    type: String,
    default: 'md'
  }
})

defineEmits(['close'])

const maxWidthClass = computed(() => {
  return {
    'sm': 'max-w-sm',
    'md': 'max-w-md',
    'lg': 'max-w-lg',
    'xl': 'max-w-xl',
    '2xl': 'max-w-2xl',
  }[props.maxWidth]
})
</script>
