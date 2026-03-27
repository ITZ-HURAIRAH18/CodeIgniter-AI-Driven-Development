<template>
  <div class="w-full overflow-x-auto rounded-custom border border-border-default bg-white shadow-soft">
    <table class="w-full">
      <thead>
        <tr class="border-b border-border-default bg-slate-50">
          <th
            v-for="column in columns"
            :key="column.key"
            :class="[
              'px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider',
              'whitespace-nowrap',
              { 'text-right': column.align === 'right' }
            ]"
            :style="{ width: column.width }"
          >
            {{ column.label }}
          </th>
          <th v-if="$slots.actions" class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">
            Actions
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, idx) in data"
          :key="idx"
          :class="[
            'border-b border-border-default transition-colors duration-150',
            'hover:bg-rose-50',
            { 'bg-surface-alt': idx % 2 === 1 }
          ]"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            :class="[
              'px-6 py-4 text-sm text-slate-900',
              { 'text-right': column.align === 'right' },
              { 'font-medium': column.bold }
            ]"
          >
            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
              {{ row[column.key] }}
            </slot>
          </td>
          <td v-if="$slots.actions" class="px-6 py-4 text-right">
            <slot name="actions" :row="row" />
          </td>
        </tr>
        <tr v-if="!data || data.length === 0">
          <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-12 text-center text-slate-500">
            {{ emptyMessage }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
    // Example: [{ key: 'id', label: 'ID', width: '100px', align: 'left', bold: true }]
  },
  data: {
    type: Array,
    default: () => []
  },
  emptyMessage: {
    type: String,
    default: 'No data available'
  }
})
</script>
