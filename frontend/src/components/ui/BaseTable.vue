<template>
  <div class="bg-surface-elevated border border-gray-200 rounded-lg shadow-xs overflow-hidden">
    <!-- Header -->
    <div v-if="title" class="px-6 py-4 border-b border-gray-100 bg-surface-base flex items-center justify-between">
      <div>
        <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
        <p v-if="subtitle" class="mt-1 text-xs text-gray-500">{{ subtitle }}</p>
      </div>
      <div v-if="$slots.header-action" class="ml-4">
        <slot name="header-action" />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-surface-base">
            <th
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider',
                col.align === 'right' && 'text-right',
              ]"
              :style="col.width ? { width: col.width } : {}"
            >
              {{ col.label }}
            </th>
            <th v-if="$slots.actions" class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, idx) in data"
            :key="idx"
            :class="[
              'border-b border-gray-100 transition-colors duration-150 hover:bg-accent-pink-50',
              idx % 2 === 0 ? 'bg-white' : 'bg-surface-base',
            ]"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-6 py-4 text-sm text-gray-700',
                col.align === 'right' && 'text-right font-medium',
              ]"
            >
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-6 py-4 text-right">
              <slot name="actions" :row="row" />
            </td>
          </tr>
          <tr v-if="!data || data.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-12 text-center">
              <div class="text-gray-500">
                <p class="text-sm font-medium">No data available</p>
                <p class="text-xs mt-1">Try adjusting your filters</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="absolute inset-0 bg-white/50 flex items-center justify-center rounded-lg">
      <div class="text-center">
        <div class="inline-flex items-center">
          <div class="w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  subtitle: String,
  columns: {
    type: Array,
    required: true,
  },
  data: Array,
  loading: Boolean,
})
</script>
