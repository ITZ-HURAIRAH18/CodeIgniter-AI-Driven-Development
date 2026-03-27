<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
        <p class="text-sm text-gray-600 mt-1">Track and manage your stock levels across branches</p>
      </div>
      <div v-if="canManage" class="flex items-center gap-3">
        <BaseButton variant="secondary" label="Adjust Levels" @click="showAdjustModal = true" />
        <BaseButton variant="primary" label="+ Add Stock" @click="showAddModal = true" />
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-surface-elevated border border-gray-200 rounded-lg p-5 shadow-xs">
      <div class="flex flex-col md:flex-row gap-4 items-end">
        <!-- Branch Filter -->
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            Branch
          </label>
          <select
            v-model="selectedBranchId"
            @change="loadInventory"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500 focus:border-accent-pink-300 bg-white">
          >
            <option value="">All Branches</option>
            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>

        <!-- Search -->
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            Search
          </label>
          <input
            v-model="search"
            type="text"
            placeholder="Product name, SKU..."
            class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-accent-pink-500 focus:border-accent-pink-300 bg-white"
          />
        </div>

        <!-- Status Filter -->
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            Status
          </label>
          <select
            v-model="statusFilter"
            @change="loadInventory"
            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500 focus:border-accent-pink-300 bg-white"
          >
            <option value="">All Items</option>
            <option value="low">Low Stock</option>
            <option value="optimal">Optimal</option>
            <option value="overstocked">Overstocked</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <BaseCard title="Stock Levels" subtitle="Real-time inventory and reorder information">
      <div v-if="loading" class="flex items-center justify-center h-48">
        <div class="text-center">
          <div class="inline-flex items-center">
            <div class="w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <p class="text-sm text-gray-500 mt-2">Loading inventory...</p>
        </div>
      </div>
      <BaseTable
        v-else
        :columns="columns"
        :data="filteredInventory"
        noPadding
      >
        <template #cell-product_name="{ value, row }">
          <div class="space-y-1">
            <p class="text-sm font-semibold text-gray-900">{{ value }}</p>
            <p class="text-xs text-gray-500 font-mono">{{ row.sku }}</p>
          </div>
        </template>

        <template #cell-quantity="{ row }">
          <div class="flex items-center gap-3">
            <!-- Progress bar -->
            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden flex-shrink-0">
              <div
                :class="'h-full transition-all duration-300 ' + getStatusColor(row.quantity, row.reorder_level)"
                :style="{ width: Math.min((row.quantity / Math.max(row.reorder_level * 2, 1)) * 100, 100) + '%' }"
              ></div>
            </div>
            <span :class="['font-semibold text-right min-w-[60px]', getStatusTextColor(row.quantity, row.reorder_level)]">
              {{ row.quantity }}
            </span>
          </div>
        </template>

        <template #cell-reorder_level="{ value }">
          <span class="text-sm text-gray-600">{{ value }}</span>
        </template>

        <template #cell-sale_price="{ value }">
          <span class="text-sm font-semibold text-gray-900">${{ parseFloat(value).toFixed(2) }}</span>
        </template>

        <template #cell-status="{ row }">
          <Badge :label="getStatusLabel(row.quantity, row.reorder_level)" :variant="getStatusBadgeVariant(row.quantity, row.reorder_level)" size="sm" />
        </template>

        <template v-if="canManage" #actions="{ row }">
          <div class="flex items-center gap-1">
            <button
              @click="openAdjustModal(row)"
              class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:bg-accent-pink-50 hover:text-accent-pink-600 rounded-md transition-colors"
              title="Adjust stock"
            >
              <Edit2 :style="{ width: '18px', height: '18px' }" />
            </button>
            <button
              @click="openLogsModal(row)"
              class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:bg-blue-50 hover:text-status-info rounded-md transition-colors"
              title="View history"
            >
              <History :style="{ width: '18px', height: '18px' }" />
            </button>
          </div>
        </template>
      </BaseTable>
    </BaseCard>

    <!-- Add Stock Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-surface-elevated rounded-xl shadow-lg max-w-md w-full mx-auto border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 bg-surface-base">
          <h2 class="text-lg font-semibold text-gray-900">Add Stock</h2>
          <p class="text-sm text-gray-600 mt-1">Add inventory to your branches</p>
        </div>
        <form @submit.prevent="submitAddStock" class="p-6 space-y-4">
          <BaseInput
            v-model="addForm.branch_id"
            label="Branch"
            type="select"
            required
          />
          <BaseInput
            v-model="addForm.product_id"
            label="Product"
            type="select"
            required
          />
          <BaseInput
            v-model.number="addForm.quantity"
            label="Quantity"
            type="number"
            min="1"
            required
          />
          <BaseInput
            v-model="addForm.notes"
            label="Notes"
            placeholder="Optional notes..."
          />
          <div class="flex gap-3 pt-4">
            <BaseButton variant="ghost" label="Cancel" @click="showAddModal = false" class="flex-1" />
            <BaseButton variant="primary" label="Add Stock" type="submit" :loading="submitting" class="flex-1" />
          </div>
        </form>
      </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-surface-elevated rounded-xl shadow-lg max-w-md w-full mx-auto border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 bg-surface-base">
          <h2 class="text-lg font-semibold text-gray-900">Adjust Stock</h2>
          <p class="text-sm text-gray-600 mt-1">{{ editingItem?.product_name }}</p>
        </div>
        <form @submit.prevent="submitAdjustStock" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Current Quantity</label>
            <p class="text-2xl font-bold text-gray-900">{{ editingItem?.quantity }}</p>
          </div>
          <BaseInput
            v-model.number="adjustForm.quantity"
            label="New Quantity"
            type="number"
            min="0"
            required
          />
          <BaseInput
            v-model="adjustForm.notes"
            label="Reason"
            placeholder="e.g., Physical count, Damage, Return..."
          />
          <div class="flex gap-3 pt-4">
            <BaseButton variant="ghost" label="Cancel" @click="showAdjustModal = false" class="flex-1" />
            <BaseButton variant="primary" label="Update" type="submit" :loading="submitting" class="flex-1" />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import Badge from '@/components/ui/Badge.vue'
import { Edit2, History } from 'lucide-vue-next'

const auth = useAuthStore()

const inventory = ref([])
const branches = ref([])
const allProducts = ref([])
const selectedBranchId = ref('')
const search = ref('')
const statusFilter = ref('')
const loading = ref(false)
const submitting = ref(false)
const showAddModal = ref(false)
const showAdjustModal = ref(false)
const editingItem = ref(null)
const adjustForm = ref({ quantity: '', notes: '' })

const canManage = computed(() => auth.user?.role <= 2)

const columns = [
  { key: 'product_name', label: 'Product' },
  { key: 'quantity', label: 'Current Stock' },
  { key: 'reorder_level', label: 'Reorder Level' },
  { key: 'sale_price', label: 'Unit Price' },
  { key: 'status', label: 'Status' },
]

const addForm = ref({
  branch_id: '',
  product_id: '',
  quantity: '',
  notes: '',
})

const filteredInventory = computed(() => {
  return inventory.value.filter(item => {
    const matchSearch = !search.value || 
      item.product_name.toLowerCase().includes(search.value.toLowerCase()) ||
      item.sku.toLowerCase().includes(search.value.toLowerCase())
    
    const matchStatus = !statusFilter.value || 
      (statusFilter.value === 'low' && item.quantity <= item.reorder_level) ||
      (statusFilter.value === 'optimal' && item.quantity > item.reorder_level && item.quantity <= item.reorder_level * 2) ||
      (statusFilter.value === 'overstocked' && item.quantity > item.reorder_level * 2)
    
    return matchSearch && matchStatus
  })
})

function getStatusColor(qty, reorderLevel) {
  if (qty <= reorderLevel) return 'bg-status-error'
  if (qty <= reorderLevel * 1.5) return 'bg-status-warning'
  return 'bg-status-success'
}

function getStatusTextColor(qty, reorderLevel) {
  if (qty <= reorderLevel) return 'text-status-error'
  if (qty <= reorderLevel * 1.5) return 'text-status-warning'
  return 'text-status-success'
}

function getStatusLabel(qty, reorderLevel) {
  if (qty <= reorderLevel) return 'Low Stock'
  if (qty <= reorderLevel * 1.5) return 'Alert'
  return 'Optimal'
}

function getStatusBadgeVariant(qty, reorderLevel) {
  if (qty <= reorderLevel) return 'error'
  if (qty <= reorderLevel * 1.5) return 'warning'
  return 'success'
}

async function loadInventory() {
  loading.value = true
  try {
    const filters = []
    if (selectedBranchId.value) filters.push(`branch_id=${selectedBranchId.value}`)
    
    const query = filters.length ? '?' + filters.join('&') : ''
    const res = await api.get(`/inventory${query}`)
    inventory.value = res || []
  } catch (e) {
    console.error('Failed to load inventory:', e)
  } finally {
    loading.value = false
  }
}

async function loadBranches() {
  try {
    const res = await api.get('/branches')
    branches.value = res || []
  } catch (e) {
    console.error('Failed to load branches:', e)
  }
}

async function loadProducts() {
  try {
    const res = await api.get('/products')
    allProducts.value = res || []
  } catch (e) {
    console.error('Failed to load products:', e)
  }
}

async function submitAddStock() {
  submitting.value = true
  try {
    await api.post('/inventory', {
      branch_id: addForm.value.branch_id || null,
      product_id: addForm.value.product_id,
      quantity: addForm.value.quantity,
      notes: addForm.value.notes,
    })
    showAddModal.value = false
    addForm.value = { branch_id: '', product_id: '', quantity: '', notes: '' }
    await loadInventory()
  } catch (e) {
    console.error('Failed to add stock:', e)
  } finally {
    submitting.value = false
  }
}

function openAdjustModal(item) {
  editingItem.value = item
  adjustForm.value = { quantity: item.quantity, notes: '' }
  showAdjustModal.value = true
}

async function submitAdjustStock() {
  submitting.value = true
  try {
    await api.put(`/inventory/${editingItem.value.id}`, {
      quantity: adjustForm.value.quantity,
      notes: adjustForm.value.notes,
    })
    showAdjustModal.value = false
    await loadInventory()
  } catch (e) {
    console.error('Failed to adjust stock:', e)
  } finally {
    submitting.value = false
  }
}

function openLogsModal(item) {
  editingItem.value = item
  console.log('View logs for:', item)
}

onMounted(() => {
  loadInventory()
  loadBranches()
  loadProducts()
})
</script>
