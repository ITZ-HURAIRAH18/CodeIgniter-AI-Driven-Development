<template>
  <div class="flex flex-col w-full">
    <div class="overflow-x-auto rounded-custom border border-slate-200 shadow-soft">
      <table class="erp-table min-w-full bg-white">
        <thead class="bg-slate-50">
          <tr>
            <th 
              v-for="col in columns" 
              :key="col.key"
              :class="['text-slate-600', col.align === 'right' ? 'text-right' : 'text-left', col.headerClass]"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
             <td v-for="col in columns" :key="col.key" class="py-3 px-4">
                <div class="h-4 bg-slate-100 rounded w-full"></div>
             </td>
          </tr>
          <tr v-else-if="items.length === 0">
             <td :colspan="columns.length" class="py-12 text-center text-slate-400">
                No records found.
             </td>
          </tr>
          <tr v-else v-for="(item, idx) in items" :key="idx">
            <td 
              v-for="col in columns" 
              :key="col.key"
              :class="[col.align === 'right' ? 'text-right' : 'text-left', col.cellClass]"
            >
              <slot :name="col.key" :item="item" :idx="idx">
                {{ item[col.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- ERP Style compact pagination -->
    <div v-if="pagination" class="flex items-center justify-between py-3 px-1 text-xs text-slate-500 font-medium">
      <div class="flex items-center gap-2">
        <span>Rows per page:</span>
        <select class="bg-transparent border-none focus:ring-0 cursor-pointer text-slate-700">
          <option>20</option>
          <option>50</option>
          <option>100</option>
        </select>
        <span class="ml-4">Showing 1 to {{ items.length }} of 244 entries</span>
      </div>
      <div class="flex items-center gap-1">
        <button class="w-7 h-7 flex items-center justify-center rounded border border-slate-200 hover:bg-slate-50 transition-colors">
          <ChevronLeftIcon class="h-3.5 w-3.5" />
        </button>
        <button class="w-7 h-7 flex items-center justify-center rounded bg-primary-50 border border-primary-200 text-primary-600">
          1
        </button>
        <button class="w-7 h-7 flex items-center justify-center rounded border border-slate-100 text-slate-400 hover:bg-slate-50 transition-colors">
          2
        </button>
        <button class="w-7 h-7 flex items-center justify-center rounded border border-slate-200 hover:bg-slate-50 transition-colors">
          <ChevronRightIcon class="h-3.5 w-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next'

defineProps({
  columns: {
    type: Array, // [{ key: 'id', label: 'ID', align: 'left', headerClass: '', cellClass: '' }]
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
  loading: Boolean,
  pagination: {
    type: Boolean,
    default: true
  }
})
</script>
