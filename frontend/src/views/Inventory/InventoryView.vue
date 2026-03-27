<template>
  <div class="space-y-section">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-section">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 drop-shadow-sm">Stock Management</h1>
        <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
          <ActivityIcon class="w-3.5 h-3.5 text-rose-500" />
          Real-time inventory levels across enterprise network
        </p>
      </div>
      <div v-if="canManage" class="flex items-center gap-3">
        <BaseButton variant="secondary" @click="openAdjustModal" size="md" class="border-slate-200">
          <Settings2Icon class="w-4 h-4 mr-1.5" />
          Adjust Levels
        </BaseButton>
        <BaseButton variant="primary" @click="showAddModal = true" size="md" class="shadow-rose-sm">
          <PlusIcon class="w-4 h-4 mr-1.5" />
          Replenish Stock
        </BaseButton>
      </div>
    </div>

    <!-- Controls Bar -->
    <Card class="mb-gutter bg-surface-alt/50">
      <div class="flex flex-col md:flex-row gap-4 items-end md:items-center justify-between">
        <div class="flex-1 flex flex-col md:flex-row gap-4 w-full md:w-auto">
          <!-- Branch Selector (Admin/Multi-branch manager) -->
          <div v-if="auth.isAdmin || (branches.length > 1)" class="w-full md:w-64">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5 block">Active Node</label>
            <Select v-model="selectedBranchId" @change="loadInventory" class="w-full">
              <option value="">Full Network Access</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </Select>
          </div>
          
          <!-- Search -->
          <div class="w-full md:w-80">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5 block">Search Analytics</label>
            <div class="relative group">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-rose-500 transition-colors" />
              <Input v-model="search" placeholder="SKU, Product, or Category..." class="pl-9 bg-white" />
            </div>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
           <div class="text-[11px] font-bold text-slate-400 bg-white border border-border-light px-3 py-1.5 rounded-full shadow-soft flex items-center gap-2 italic">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Synchronized with Cloud Nodes
           </div>
        </div>
      </div>
    </Card>

    <!-- Main Data Table -->
    <Card no-padding>
      <DataTable
        :columns="columns"
        :data="filteredInventory"
        :loading="loading"
        empty-message="No operational inventory detected for this branch."
      >
        <template #cell-product_name="{ value, row }">
          <div class="flex flex-col">
            <span class="font-bold text-slate-900 tracking-tight">{{ value }}</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ row.sku }}</span>
          </div>
        </template>
        
        <template #cell-quantity="{ value, row }">
          <div class="flex items-center gap-4">
            <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden shrink-0 border border-slate-200">
               <div :class="['h-full rounded-full transition-all duration-300', getStockBarColor(value, row.reorder_level)]" :style="{ width: Math.min((value / (row.reorder_level * 2)) * 100, 100) + '%' }"></div>
            </div>
            <span :class="['font-black tabular-nums', value <= row.reorder_level ? 'text-rose-600' : 'text-slate-900']">
              {{ value }}
            </span>
          </div>
        </template>

        <template #cell-sale_price="{ value }">
          <span class="font-black text-slate-800 tabular-nums">${{ parseFloat(value).toLocaleString() }}</span>
        </template>

        <template #cell-status="{ row }">
          <Badge 
            :label="getStatusLabel(row.quantity, row.reorder_level)"
            :variant="getStatusVariant(row.quantity, row.reorder_level)"
            class="font-black italic uppercase tracking-tighter scale-90"
          />
        </template>

        <template v-if="canManage" #actions="{ row }">
          <div class="flex items-center gap-1.5">
            <BaseButton variant="ghost" size="sm" @click="openAdjustModal(row)" class="h-8 w-8 !p-0" title="Adjust Balance">
               <Edit3Icon class="w-3.5 h-3.5" />
            </BaseButton>
            <BaseButton variant="ghost" size="sm" @click="openLogsModal(row)" class="h-8 w-8 !p-0" title="Operational History">
               <HistoryIcon class="w-3.5 h-3.5" />
            </BaseButton>
          </div>
        </template>
      </DataTable>
    </Card>

    <!-- Replenish Stock Modal -->
    <Modal :show="showAddModal" title="Replenish Cloud Stock" maxWidth="lg" @close="showAddModal = false">
      <form @submit.prevent="submitAddStock" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Target Node</label>
            <Select v-model="addForm.branch_id" required :disabled="!auth.isAdmin && !auth.isBranchManager">
              <option disabled value="">Select branch...</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </Select>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Resource Unit (SKU)</label>
            <Select v-model="addForm.product_id" required>
              <option disabled value="">Select product...</option>
              <option v-for="p in allProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
            </Select>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Requisition Quantity</label>
          <div class="relative">
            <Input v-model.number="addForm.quantity" type="number" min="1" required class="pl-12 text-lg font-black" />
            <PackagePlusIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Operational Log (Optional)</label>
          <Input v-model="addForm.notes" placeholder="Reason for replenishment, reference ID, etc." />
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <BaseButton type="button" variant="ghost" @click="showAddModal = false">Cancel Requisition</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="submitting" class="shadow-rose-sm">
             Deploy Resources
          </BaseButton>
        </div>
      </form>
    </Modal>

    <!-- Adjust Stock Modal -->
    <Modal :show="showAdjustModal" :title="'Adjust Node Balance: ' + (editingItem?.product_name || 'System')" maxWidth="md" @close="showAdjustModal = false">
      <form @submit.prevent="submitAdjustStock" class="space-y-6">
        <div class="bg-rose-50 border border-border-light p-4 rounded-xl flex items-start gap-3">
           <AlertTriangleIcon class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" />
           <div class="text-[11px] font-bold text-rose-900 uppercase tracking-tight leading-relaxed">
              Caution: Adjustments overwrite theoretical cloud balances. All manual overrides are logged for enterprise auditing.
           </div>
        </div>

        <div class="space-y-4">
           <div>
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5 block">Current Detected Stock</label>
              <div class="text-3xl font-black text-slate-400 italic">{{ editingItem?.quantity || 0 }} UNITS</div>
           </div>

           <div class="space-y-1.5">
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Set Absolute Node Balance</label>
              <Input v-model.number="adjustForm.quantity" type="number" min="0" required class="text-2xl font-black text-rose-700 bg-rose-50/50 border-rose-200" />
           </div>

           <div class="space-y-1.5">
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Adjustment Justification</label>
              <Input v-model="adjustForm.notes" placeholder="Damaged, Found, Sync Error, etc." required />
           </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <BaseButton type="button" variant="ghost" @click="showAdjustModal = false">Abort Adjustment</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="submitting" class="shadow-rose-sm">
             Commit Adjustment
          </BaseButton>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
import { 
  PlusIcon, SearchIcon, ActivityIcon, Edit3Icon, Settings2Icon, 
  HistoryIcon, PackagePlusIcon, AlertTriangleIcon 
} from 'lucide-vue-next'

import Card from '@/components/ui/Card.vue'
import DataTable from '@/components/ui/DataTable.vue'
import Badge from '@/components/ui/Badge.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Select from '@/components/ui/Select.vue'

const auth = useAuthStore()
const branches = ref([])
const inventory = ref([])
const allProducts = ref([])
const loading = ref(true)
const submitting = ref(false)
const search  = ref('')
const selectedBranchId = ref(auth.userBranchId || '')

const showAddModal    = ref(false)
const showAdjustModal = ref(false)
const showLogsModal   = ref(false)
const editingItem     = ref(null)

const addForm = reactive({
  branch_id: auth.userBranchId || '',
  product_id: '',
  quantity: 1,
  notes: ''
})

const adjustForm = reactive({
  quantity: 0,
  notes: ''
})

const columns = [
  { key: 'product_name', label: 'Product Stream', bold: true },
  { key: 'quantity', label: 'Cloud Balance' },
  { key: 'sale_price', label: 'Unit Value', align: 'right' },
  { key: 'status', label: 'Node Status' },
]

const canManage = computed(() => auth.isAdmin || auth.isBranchManager)

const filteredInventory = computed(() => {
  if (!search.value) return inventory.value
  const q = search.value.toLowerCase()
  return inventory.value.filter(i =>
    i.product_name?.toLowerCase().includes(q) || 
    i.sku?.toLowerCase().includes(q) || 
    i.category_name?.toLowerCase().includes(q)
  )
})

onMounted(async () => {
  await Promise.all([
    loadBranches(),
    loadProducts(),
    loadInventory()
  ])
})

async function loadBranches() {
  if (auth.isAdmin || auth.isBranchManager) {
    try {
      const res = await api.get('/branches')
      branches.value = res || []
      // Auto-select first branch if admin and none selected
      if (auth.isAdmin && !selectedBranchId.value && branches.value.length) {
        selectedBranchId.value = branches.value[0].id
      }
    } catch (e) { console.error(e) }
  }
}

async function loadProducts() {
  try {
    const res = await api.get('/products?status=active')
    // Handle paginated response if wrapped
    allProducts.value = res.data || res || []
  } catch (e) { console.error(e) }
}

async function loadInventory() {
  loading.value = true
  try {
    const branchId = selectedBranchId.value || auth.userBranchId
    if (!branchId && !auth.isAdmin) { loading.value = false; return }
    
    const url = branchId ? `/inventory?branch_id=${branchId}` : '/inventory'
    const res = await api.get(url)
    inventory.value = res || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openAdjustModal(row) {
  if (!row) {
    // If no row, user clicked global "Adjust Levels" - we might need to select item first
    // For now, let's just use the first row if available, or do nothing
    if (inventory.value.length > 0) row = inventory.value[0]
    else return
  }
  editingItem.value = row
  adjustForm.quantity = row.quantity
  adjustForm.notes = ''
  showAdjustModal.value = true
}

async function submitAddStock() {
  submitting.value = true
  try {
    await api.post('/inventory/add', addForm)
    showAddModal.value = false
    addForm.product_id = ''
    addForm.quantity = 1
    addForm.notes = ''
    await loadInventory()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to replenish stock.')
  } finally {
    submitting.value = false
  }
}

async function submitAdjustStock() {
  if (!editingItem.value) return
  submitting.value = true
  try {
    await api.post('/inventory/adjust', {
      branch_id: editingItem.value.branch_id,
      product_id: editingItem.value.product_id,
      quantity: adjustForm.quantity,
      notes: adjustForm.notes
    })
    showAdjustModal.value = false
    await loadInventory()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to adjust stock.')
  } finally {
    submitting.value = false
  }
}

// Helpers
const getStatusLabel = (q, r) => {
  if (q === 0) return 'DEPLETED'
  if (q <= r) return 'LOW NODE'
  return 'STABLE'
}

const getStatusVariant = (q, r) => {
  if (q === 0) return 'error'
  if (q <= r) return 'warning'
  return 'success'
}

const getStockBarColor = (q, r) => {
  if (q === 0) return 'bg-rose-500'
  if (q <= r) return 'bg-amber-500'
  return 'bg-emerald-500'
}
</script>
