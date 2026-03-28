<template>
  <div class="space-y-section">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-section">
      <div>
        <h1 class="text-4xl font-bold text-slate-900 drop-shadow-sm tracking-tight">Stock Management</h1>
        <p class="text-slate-500 text-sm mt-2 flex items-center gap-2 font-medium">
          <ActivityIcon class="w-4 h-4 text-rose-500" />
          Real-time inventory levels across enterprise network
        </p>
      </div>
      <div v-if="canManage" class="flex items-center gap-2 flex-wrap md:flex-nowrap">
        <BaseButton variant="secondary" @click="openAdjustModal" size="md" class="border-slate-200 shadow-sm" :title="'Adjust stock levels'">
          <Settings2Icon class="w-4 h-4" />
          <span class="hidden sm:inline ml-1.5">Adjust Levels</span>
        </BaseButton>
        <BaseButton variant="secondary" @click="showTransferModal = true" size="md" class="border-slate-200 shadow-sm" :title="'Transfer stock between branches'">
          <ArrowRightLeft class="w-4 h-4" />
          <span class="hidden sm:inline ml-1.5">Transfer Stock</span>
        </BaseButton>
        <BaseButton variant="primary" @click="showAddModal = true" size="md" class="shadow-lg shadow-rose-200" :title="'Replenish stock'">
          <PlusIcon class="w-4 h-4" />
          <span class="hidden sm:inline ml-1.5">Replenish Stock</span>
        </BaseButton>
      </div>
    </div>

    <!-- Controls Bar -->
    <Card class="mb-gutter bg-surface-alt/50">
      <div class="flex flex-col md:flex-row gap-4 items-end md:items-center justify-between">
        <div class="flex-1 flex flex-col md:flex-row gap-4 w-full md:w-auto">
          <!-- Branch Selector (Admin/Multi-branch manager) -->
          <div v-if="auth.isAdmin || (auth.isBranchManager && branches.length > 0)" class="w-full md:w-72">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">Active Node</label>
            <select 
              v-model="selectedBranchId"
              class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold bg-white border-2 border-slate-200 text-slate-900 focus:outline-none focus:border-accent-pink-500 focus:ring-2 focus:ring-accent-pink-500/20 cursor-pointer appearance-none transition-all hover:border-slate-300"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 1rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="">All Branches</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          
          <!-- Search -->
          <div class="w-full md:flex-1">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">Search Analytics</label>
            <div class="relative group">
              <SearchIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-rose-500 transition-colors" />
              <Input v-model="search" placeholder="Product name, SKU, or Category..." class="pl-10 bg-white border-2 border-slate-200 font-medium" />
            </div>
          </div>
        </div>
        
        <div class="flex items-center gap-3 whitespace-nowrap">
           <div class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-2 rounded-lg shadow-soft flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Real-time Sync Active
           </div>
        </div>
      </div>
    </Card>

    <!-- Main Data Table -->
    <Card no-padding class="shadow-lg border-slate-200">
      <div v-if="filteredInventory.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 border-b-2 border-slate-200 sticky top-0">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-tight">Product Stream</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-tight">Cloud Balance</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 uppercase tracking-tight">Unit Value</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-tight">Node Status</th>
              <th v-if="canManage" class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-tight">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="row in filteredInventory" :key="row.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-900 tracking-tight">{{ row.product_name || 'Unknown' }}</span>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ row.sku || 'N/A' }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-4">
                  <div class="w-32 h-2 bg-slate-100 rounded-full overflow-hidden shrink-0 border border-slate-200">
                     <div :class="['h-full rounded-full transition-all duration-300', getStockBarColor(row.quantity, row.reorder_level)]" :style="{ width: Math.min((row.quantity / (row.reorder_level * 2)) * 100, 100) + '%' }"></div>
                  </div>
                  <span :class="['font-black tabular-nums w-12 text-right', row.quantity <= row.reorder_level ? 'text-rose-600' : 'text-slate-900']">
                    {{ row.quantity || 0 }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="font-black text-slate-800 tabular-nums">${{ row.sale_price && !isNaN(row.sale_price) ? parseFloat(row.sale_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00' }}</span>
              </td>
              <td class="px-6 py-4">
                <Badge 
                  :label="getStatusLabel(row.quantity, row.reorder_level)"
                  :variant="getStatusVariant(row.quantity, row.reorder_level)"
                  class="font-bold italic uppercase tracking-tighter"
                />
              </td>
              <td v-if="canManage" class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <BaseButton variant="ghost" size="sm" @click="openAdjustModal(row)" class="h-9 w-9 !p-0" title="Adjust Balance">
                     <Edit3Icon class="w-4 h-4" />
                  </BaseButton>
                  <BaseButton variant="ghost" size="sm" @click="openLogsModal(row)" class="h-9 w-9 !p-0" title="Operational History">
                     <HistoryIcon class="w-4 h-4" />
                  </BaseButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="flex items-center justify-center py-16">
        <div class="text-center">
          <ActivityIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <p class="text-slate-600 font-medium">No inventory items in {{ branches.length > 0 ? branches.length : 'this' }} branch(es)</p>
          <p class="text-slate-400 text-sm">Start by replenishing stock to add items</p>
          <div class="mt-4 text-xs text-slate-500">
            <p v-if="branches.length === 0">⚠️ No branches assigned</p>
            <p v-else>📦 Branches: {{ branches.map(b => b.name).join(', ') }}</p>
          </div>
        </div>
      </div>
    </Card>

      <!-- Replenish Stock Modal -->
    <Modal :show="showAddModal" title="Replenish Cloud Stock" maxWidth="lg" @close="showAddModal = false">
      <form @submit.prevent="submitAddStock" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Target Node *</label>
            <select 
              v-model="addForm.branch_id"
              :disabled="auth.isBranchManager"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>Select branch...</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Resource Unit (SKU) *</label>
            <select 
              v-model="addForm.product_id"
              :disabled="!addForm.branch_id"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>Select product...</option>
              <option v-for="p in filteredProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
            </select>
          </div>
        </div>
        <div v-if="addForm.branch_id && addForm.product_id">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Requisition Quantity *</label>
            <div class="relative">
              <Input v-model.number="addForm.quantity" type="number" min="1" required class="pl-12 text-lg font-black" />
              <PackagePlusIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Operational Log (Optional)</label>
            <Input v-model="addForm.notes" placeholder="Reason for replenishment, reference ID, etc." />
          </div>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Target Node</label>
            <select 
              v-model="editingItem.branch_id"
              :disabled="auth.isBranchManager"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>Select branch...</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Resource Unit (SKU)</label>
            <select 
              v-model="editingItem.product_id"
              :disabled="!editingItem.branch_id"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>Select product...</option>
              <option v-for="p in filteredProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
            </select>
          </div>
        </div>
        <div v-if="editingItem.branch_id && editingItem.product_id">
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

    <!-- Transfer Stock Modal -->
    <Modal :show="showTransferModal" title="Transfer Stock Between Nodes" maxWidth="md" @close="showTransferModal = false">
      <form @submit.prevent="submitTransferStock" class="space-y-6">
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl flex items-start gap-3">
           <AlertTriangleIcon class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
           <div class="text-[11px] font-bold text-blue-900 uppercase tracking-tight leading-relaxed">
              Transfer stock from one branch to another. All transfers are logged and require approval from destination manager.
           </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">From Node *</label>
            <select 
              v-model="transferForm.from_branch_id"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>Select source branch...</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">To Node *</label>
            <select 
              v-model="transferForm.to_branch_id"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>Select destination branch...</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id" :disabled="b.id == transferForm.from_branch_id">{{ b.name }}</option>
            </select>
          </div>
        </div>
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Resource Unit (SKU) *</label>
          <select 
            v-model="transferForm.product_id"
            :disabled="!transferForm.from_branch_id"
            required
            class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
            style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
          >
            <option value="" disabled selected>Select product...</option>
            <option v-for="p in allProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Transfer Quantity *</label>
          <Input v-model.number="transferForm.quantity" type="number" min="1" required placeholder="Enter quantity to transfer" />
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <BaseButton type="button" variant="ghost" @click="showTransferModal = false">Cancel Transfer</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="submitting">
             Initiate Transfer
          </BaseButton>
        </div>
      </form>
    </Modal>

    <!-- Stock History Modal -->
    <Modal :show="showLogsModal" :title="'Stock History: ' + (currentLogItem?.product_name || 'All')" maxWidth="lg" @close="showLogsModal = false">
      <div class="space-y-4">
        <div v-if="loadingLogs" class="flex justify-center py-8">
          <div class="text-sm text-slate-600">Loading stock movement history...</div>
        </div>
        <div v-else-if="stockLogs.length === 0" class="flex justify-center py-8">
          <div class="text-sm text-slate-600">No stock movements recorded</div>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b-2 border-slate-200 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left font-bold text-slate-600">Date/Time</th>
                <th class="px-4 py-3 text-left font-bold text-slate-600">Type</th>
                <th class="px-4 py-3 text-center font-bold text-slate-600 w-24">Qty</th>
                <th class="px-4 py-3 text-left font-bold text-slate-600">Branch</th>
                <th class="px-4 py-3 text-left font-bold text-slate-600">Notes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="log in stockLogs" :key="log.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-2.5 text-slate-700 whitespace-nowrap">
                  <div class="font-medium">{{ formatDate(log.created_at) }}</div>
                </td>
                <td class="px-4 py-2.5">
                  <Badge :label="getLogTypeLabel(log.transaction_type)" :variant="getLogBadgeVariant(log.transaction_type)" size="sm" class="font-bold uppercase scale-90 origin-left" />
                </td>
                <td class="px-4 py-2.5 text-center font-mono font-bold text-slate-900">
                  <span :class="getQuantityClass(log.quantity_change)">{{ log.quantity_change || 0 }}</span>
                </td>
                <td class="px-4 py-2.5 text-slate-700 font-medium">{{ log.branch_name || 'N/A' }}</td>
                <td class="px-4 py-2.5 text-slate-600">
                  <span class="text-xs inline-block max-w-xs truncate" :title="log.notes">{{ log.notes || '-' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
import { 
  PlusIcon, SearchIcon, ActivityIcon, Edit3Icon, Settings2Icon, 
  HistoryIcon, PackagePlusIcon, AlertTriangleIcon, ArrowRightLeft 
} from 'lucide-vue-next'

import Card from '@/components/ui/Card.vue'
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
// For sales users, pre-fill with their branch; for managers/admins, leave empty to select
const selectedBranchId = ref(auth.isSalesUser ? (auth.userBranchId || '') : '')

const showAddModal    = ref(false)
const showAdjustModal = ref(false)
const showTransferModal = ref(false)
const showLogsModal   = ref(false)
const editingItem     = ref(null)
const currentLogItem  = ref(null)

const addForm = reactive({
  // Sales users: pre-fill branch; Managers/Admins: leave empty to select
  branch_id: auth.isSalesUser ? (auth.userBranchId || '') : '',
  product_id: '',
  quantity: 1,
  notes: ''
})

const adjustForm = reactive({
  quantity: 0,
  notes: ''
})

const transferForm = reactive({
  from_branch_id: '',
  to_branch_id: '',
  product_id: '',
  quantity: 1
})

const stockLogs = ref([])
const loadingLogs = ref(false)

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

const filteredBranches = computed(() => {
  if (auth.isAdmin) return branches.value
  // Branch managers see only branches they manage
  // The API already filters branches for managers, so just return all loaded branches
  if (auth.isBranchManager) {
    console.log('Manager branches:', branches.value)
    return branches.value
  }
  return []
})

const filteredProducts = computed(() => {
  // Only show products if a branch is selected in the form
  if (!addForm.branch_id && !(editingItem.value && editingItem.value.branch_id)) return []
  return allProducts.value
})

const branchOptions = computed(() => filteredBranches.value.map(b => ({ value: b.id, label: b.name })))
const productOptions = computed(() => filteredProducts.value.map(p => ({ value: p.id, label: p.name + ' (' + p.sku + ')' })))

watch(
  () => addForm.branch_id,
  (val) => {
    if (!val) addForm.product_id = ''
  }
)

watch(
  () => editingItem?.branch_id,
  (val) => {
    if (!val && editingItem) editingItem.product_id = ''
  }
)

watch(
  () => selectedBranchId.value,
  () => {
    loadInventory()
  }
)

onMounted(async () => {
  // Load branches and products first, then inventory
  await Promise.all([
    loadBranches(),
    loadProducts()
  ])
  
  // Then load inventory after products are available for joining
  await loadInventory()
})

async function loadBranches() {
  if (auth.isAdmin || auth.isBranchManager) {
    try {
      const res = await api.get('/branches')
      
      // Handle both paginated and direct array responses
      const branchesData = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
      branches.value = branchesData
      
      console.log('Branches loaded:', branches.value.length, 'branches', branches.value)
      
      // Initialize selectedBranchId based on user role
      if (!selectedBranchId.value && branches.value.length > 0) {
        if (auth.isBranchManager) {
          // Branch manager - auto-select their first branch
          selectedBranchId.value = branches.value[0].id
          console.log('Branch manager assigned to branch:', selectedBranchId.value)
        } else if (auth.isAdmin) {
          // Admin - select first branch
          selectedBranchId.value = branches.value[0].id
        }
      }
      
      // Initialize add form with selected branch
      if (!addForm.branch_id && branches.value.length > 0) {
        addForm.branch_id = selectedBranchId.value || branches.value[0].id
      }
    } catch (e) { 
      console.error('Failed to load branches:', e) 
    }
  }
}

async function loadProducts() {
  try {
    const res = await api.get('/products?status=active')
    // Handle paginated response wrapper
    allProducts.value = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
    console.log('Products loaded:', allProducts.value.length, 'items')
  } catch (e) { 
    console.error('Failed to load products:', e)
  }
}

async function loadInventory() {
  loading.value = true
  try {
    let url = '/inventory'
    
    // Admin and managers can load without branch_id (backend filters)
    // but if a branch is selected, use it
    if (selectedBranchId.value) {
      url = `/inventory?branch_id=${selectedBranchId.value}`
    }
    
    console.log('Loading inventory from:', url)
    const res = await api.get(url)
    let inventoryData = res || []
    
    // Ensure inventoryData is an array
    if (!Array.isArray(inventoryData)) {
      inventoryData = inventoryData.data || []
    }
    
    // Join with product data to ensure we have product names and prices
    inventory.value = inventoryData.map(item => {
      const product = allProducts.value.find(p => p.id === item.product_id)
      return {
        ...item,
        product_name: product?.name || item.product_name || 'Unknown Product',
        sku: product?.sku || item.sku || 'N/A',
        sale_price: product?.sale_price || item.sale_price || 0,
        reorder_level: item.reorder_level || 10,
        category_name: product?.category_name || item.category_name || 'N/A'
      }
    })
    
    console.log('Inventory loaded:', inventory.value.length, 'items')
  } catch (e) {
    console.error('Failed to load inventory:', e)
  } finally {
    loading.value = false
  }
}

function openAdjustModal(row) {
  if (!row) {
    // If no row, user clicked global "Adjust Levels" - show empty form
    editingItem.value = {
      product_name: '',
      product_id: '',
      branch_id: addForm.branch_id || selectedBranchId.value || '',
      quantity: 0
    }
  } else {
    // User clicked on a specific row - pre-fill the data
    editingItem.value = { ...row }
  }
  adjustForm.quantity = row?.quantity || 0
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

async function openLogsModal(row) {
  if (!row) return
  currentLogItem.value = row
  stockLogs.value = []
  loadingLogs.value = true
  showLogsModal.value = true
  
  try {
    const res = await api.get(`/inventory/logs?branch_id=${row.branch_id || row.id}&product_id=${row.product_id}`)
    let logs = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
    
    console.log('Raw logs from API:', logs)
    
    // Map from actual DB fields: movement_type, qty_change, branch_id
    stockLogs.value = logs.map(log => {
      // Find branch name from our loaded branches
      const branch = branches.value.find(b => b.id === log.branch_id)
      const branchName = branch?.name || `Branch ${log.branch_id}`
      
      return {
        id: log.id,
        created_at: log.created_at,
        transaction_type: log.movement_type || 'unknown',
        quantity_change: log.qty_change !== undefined ? log.qty_change : 0,
        branch_name: branchName,
        notes: log.notes || '',
        ...log
      }
    })
    
    console.log('Processed logs for display:', stockLogs.value)
  } catch (e) {
    console.error('Failed to load logs:', e)
    alert('Failed to load stock history.')
  } finally {
    loadingLogs.value = false
  }
}

async function submitTransferStock() {
  if (!transferForm.from_branch_id || !transferForm.to_branch_id || !transferForm.product_id) {
    alert('Please fill in all required fields')
    return
  }
  
  submitting.value = true
  try {
    await api.post('/transfers', transferForm)
    showTransferModal.value = false
    transferForm.from_branch_id = ''
    transferForm.to_branch_id = ''
    transferForm.product_id = ''
    transferForm.quantity = 1
    await loadInventory()
    alert('Stock transfer initiated successfully!')
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to transfer stock.')
  } finally {
    submitting.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString()
}

const getLogBadgeVariant = (type) => {
  const variants = {
    'add': 'success',
    'adjust': 'warning',
    'transfer_out': 'neutral',
    'transfer_in': 'success',
    'sale': 'error',
    'replenish': 'success',
    'adjustment': 'warning',
    'transfer': 'info',
    'order': 'error'
  }
  return variants[type?.toLowerCase()] || 'neutral'
}

const getLogTypeLabel = (type) => {
  const labels = {
    'add': 'Replenish',
    'adjust': 'Adjustment',
    'transfer_out': 'Transfer Out',
    'transfer_in': 'Transfer In',
    'sale': 'Sale',
    'replenish': 'Replenish',
    'adjustment': 'Adjustment',
    'transfer': 'Transfer',
    'order': 'Sale Order'
  }
  return labels[type?.toLowerCase()] || type || 'Unknown'
}

const getQuantityClass = (qty) => {
  if (qty > 0) return 'text-emerald-600'
  if (qty < 0) return 'text-rose-600'
  return 'text-slate-600'
}
</script>
