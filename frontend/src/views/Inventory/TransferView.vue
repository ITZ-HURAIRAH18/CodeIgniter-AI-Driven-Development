<template>
  <div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
      <div class="flex items-center gap-2 text-sm text-slate-500 font-medium mb-2">
        <span>Dashboard</span>
        <span class="text-slate-300">/</span>
        <span class="text-slate-900 font-semibold">Stock Transfers</span>
      </div>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Stock Transfers</h1>
          <p class="text-slate-500 text-sm mt-1">Manage inter-branch inventory transfers</p>
        </div>
        <button @click="showModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 h-10 bg-rose-600 text-white rounded-lg font-medium hover:bg-rose-700 transition-colors shadow-sm">
          <PlusIcon class="w-4 h-4" />
          <span>New Transfer</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-lg p-4">
      <div class="flex flex-col md:flex-row gap-3 items-end md:items-center">
        <div class="flex-1">
          <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2 block">Status</label>
          <select 
            v-model="statusFilter"
            class="w-full px-3 py-2 h-10 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_transit">In Transit</option>
            <option value="received">Received</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Transfers Table -->
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="flex flex-col items-center gap-3">
          <div class="w-8 h-8 border-2 border-slate-200 border-t-rose-600 rounded-full animate-spin"></div>
          <p class="text-slate-500 text-sm">Loading transfers...</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredTransfers.length === 0" class="flex items-center justify-center py-16 px-4">
        <div class="text-center">
          <TruckIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <p class="text-slate-600 font-medium text-sm">No transfers found</p>
          <p class="text-slate-500 text-xs mt-1">Create your first transfer to get started</p>
          <button @click="showModal = true" class="inline-flex items-center gap-1 mt-4 px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 rounded-md transition-colors">
            <PlusIcon class="w-4 h-4" />
            New Transfer
          </button>
        </div>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50/80 border-b border-slate-200 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Transfer ID</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Route</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Items</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Initiated By</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr 
              v-for="(t, idx) in filteredTransfers" 
              :key="t.id"
              :class="['transition-colors hover:bg-slate-50/80', idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/30']"
            >
              <!-- Transfer ID -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span class="font-mono font-semibold text-slate-900 text-sm">#{{ t.id }}</span>
              </td>

              <!-- Route (From → To) -->
              <td class="px-6 py-3 whitespace-nowrap">
                <div class="flex items-center gap-2 text-sm">
                  <span class="text-slate-900 font-medium">{{ t.from_branch }}</span>
                  <ArrowRightIcon class="w-4 h-4 text-slate-400" />
                  <span class="text-slate-900 font-medium">{{ t.to_branch }}</span>
                </div>
              </td>

              <!-- Items Count -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                  {{ t.items?.length || 0 }} {{ t.items?.length === 1 ? 'item' : 'items' }}
                </span>
              </td>

              <!-- Initiated By -->
              <td class="px-6 py-3 whitespace-nowrap text-slate-600 text-sm">
                {{ t.initiated_by_name }}
              </td>

              <!-- Status -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-medium', getStatusBadgeClasses(t.status)]">
                  <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotColor(t.status)"></span>
                  {{ formatStatus(t.status) }}
                </span>
              </td>

              <!-- Date -->
              <td class="px-6 py-3 whitespace-nowrap text-slate-600 text-sm">
                {{ formatDate(t.created_at) }}
              </td>

              <!-- Actions -->
              <td class="px-6 py-3 whitespace-nowrap">
                <div class="flex items-center gap-1">
                  <button
                    @click="viewTransferDetails(t)"
                    class="p-1.5 hover:bg-slate-100 rounded-md text-slate-600 hover:text-slate-900 transition-colors"
                    title="View details"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="t.status === 'pending'"
                    @click="approveTransfer(t.id)"
                    class="p-1.5 hover:bg-green-100 rounded-md text-green-600 transition-colors"
                    title="Approve transfer"
                  >
                    <CheckIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="t.status === 'in_transit'"
                    @click="completeTransfer(t.id)"
                    class="p-1.5 hover:bg-blue-100 rounded-md text-blue-600 transition-colors"
                    title="Mark as received"
                  >
                    <CheckCircle2Icon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="t.status === 'pending'"
                    @click="rejectTransfer(t.id)"
                    class="p-1.5 hover:bg-red-100 rounded-md text-red-600 transition-colors"
                    title="Reject transfer"
                  >
                    <XIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New Transfer Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4" @click.self="showModal = false">
          <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
              <h2 class="text-lg font-bold text-slate-900">New Stock Transfer</h2>
              <button @click="showModal = false" class="p-1 hover:bg-slate-100 rounded-md text-slate-600 transition-colors">
                <XIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
              <!-- Alert Messages -->
              <div v-if="modalError" class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ modalError }}
              </div>
              <div v-if="modalSuccess" class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ modalSuccess }}
              </div>

              <form @submit.prevent="submitTransfer" class="space-y-6">

                <!-- Visual Flow Indicator -->
                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                  <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">From Branch</label>
                    <select 
                      v-model="tform.from_branch_id"
                      @change="onFromBranchChange"
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer"
                      required
                    >
                      <option value="">— Select source —</option>
                      <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    <p v-if="tform.from_branch_id" class="text-xs text-slate-500 mt-2">
                      {{ branches.find(b => b.id == tform.from_branch_id)?.name }}
                    </p>
                  </div>

                  <!-- Arrow Icon -->
                  <div class="flex flex-col items-center">
                    <ArrowRightIcon class="w-5 h-5 text-rose-500" />
                  </div>

                  <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">To Branch</label>
                    <select 
                      v-model="tform.to_branch_id"
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer"
                      required
                    >
                      <option value="">— Select destination —</option>
                      <option 
                        v-for="b in branches" 
                        :key="b.id" 
                        :value="b.id"
                        :disabled="b.id === tform.from_branch_id"
                      >
                        {{ b.name }}
                      </option>
                    </select>
                    <p v-if="tform.to_branch_id" class="text-xs text-slate-500 mt-2">
                      {{ branches.find(b => b.id == tform.to_branch_id)?.name }}
                    </p>
                  </div>
                </div>

                <!-- Product Selection Section -->
                <div v-if="tform.from_branch_id" class="space-y-4">
                  <div class="flex items-center gap-2">
                    <ShoppingCartIcon class="w-5 h-5 text-rose-600" />
                    <h3 class="text-sm font-bold text-slate-900">Select Products</h3>
                  </div>

                  <!-- Product Picker -->
                  <div class="space-y-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <div>
                      <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Product *</label>
                      <select 
                        v-model="itemPicker.productId"
                        @change="onProductSelect"
                        class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer"
                      >
                        <option value="">— Choose product —</option>
                        <option
                          v-for="inv in sourceInventory"
                          :key="inv.product_id"
                          :value="inv.product_id"
                          :disabled="inv.quantity === 0"
                        >
                          {{ inv.product_name }} ({{ inv.quantity }} available)
                        </option>
                      </select>
                    </div>

                    <div v-if="itemPicker.productId" class="grid grid-cols-2 gap-3">
                      <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Quantity *</label>
                        <input
                          v-model.number="itemPicker.quantity"
                          type="number"
                          min="1"
                          :max="sourceAvailableQty"
                          class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                          placeholder="Enter quantity"
                        />
                        <p class="text-[10px] text-slate-500 mt-1">Max: {{ sourceAvailableQty }} units</p>
                      </div>
                      <div class="flex items-end">
                        <button
                          type="button"
                          @click="addTransferItem"
                          class="w-full px-4 py-2.5 bg-rose-600 text-white rounded-md font-medium text-sm hover:bg-rose-700 transition-colors"
                        >
                          <PlusIcon class="w-4 h-4 inline mr-1" />
                          Add Item
                        </button>
                      </div>
                    </div>

                    <div v-if="itemError" class="text-xs text-red-600 font-medium">
                      {{ itemError }}
                    </div>
                  </div>

                  <!-- Selected Items List -->
                  <div v-if="tform.items.length > 0" class="space-y-2">
                    <div class="text-xs font-bold text-slate-600 uppercase tracking-widest">Items in Transfer</div>
                    <div class="space-y-2">
                      <div 
                        v-for="(item, idx) in tform.items" 
                        :key="idx"
                        class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-md group"
                      >
                        <div>
                          <p class="text-sm font-medium text-slate-900">{{ item.product_name }}</p>
                          <p class="text-xs text-slate-500">{{ item.quantity }} units</p>
                        </div>
                        <button
                          type="button"
                          @click="removeTransferItem(idx)"
                          class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-red-100 rounded text-red-600"
                        >
                          <TrashIcon class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div v-if="tform.from_branch_id && tform.to_branch_id" class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Notes (Optional)</label>
                  <textarea
                    v-model="tform.notes"
                    placeholder="Reason for transfer, special instructions..."
                    class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 resize-none"
                    rows="3"
                  ></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                  <button
                    type="button"
                    @click="showModal = false"
                    class="px-4 py-2.5 h-10 bg-white border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="submitting || !tform.items.length || !tform.from_branch_id || !tform.to_branch_id"
                    class="inline-flex items-center gap-2 px-4 py-2.5 h-10 bg-rose-600 text-white rounded-lg font-medium text-sm hover:bg-rose-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md"
                  >
                    <CheckCircle2Icon v-if="!submitting" class="w-4 h-4" />
                    <div v-else class="w-4 h-4 border-2 border-white border-t-rose-600 rounded-full animate-spin"></div>
                    {{ submitting ? 'Creating...' : 'Start Transfer' }}
                  </button>
                </div>

              </form>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { PlusIcon, ArrowRightIcon, TruckIcon, EyeIcon, CheckIcon, CheckCircle2Icon, XIcon, ShoppingCartIcon, TrashIcon } from 'lucide-vue-next'
import api from '@/api/axios'

const transfers = ref([])
const branches = ref([])
const sourceInventory = ref([])
const loading = ref(true)
const showModal = ref(false)
const submitting = ref(false)
const modalError = ref('')
const modalSuccess = ref('')
const statusFilter = ref('')
const itemError = ref('')

const tform = reactive({
  from_branch_id: '',
  to_branch_id: '',
  notes: '',
  items: [],
})

const itemPicker = reactive({ productId: '', quantity: 1 })

const filteredTransfers = computed(() => {
  if (!statusFilter.value) return transfers.value
  return transfers.value.filter(t => t.status === statusFilter.value)
})

const sourceAvailableQty = computed(() => {
  const inv = sourceInventory.value.find(i => i.product_id === itemPicker.productId)
  return inv?.quantity || 0
})

const getStatusBadgeClasses = (status) => {
  const map = {
    pending: 'bg-amber-50 text-amber-700',
    in_transit: 'bg-blue-50 text-blue-700',
    received: 'bg-emerald-50 text-emerald-700',
    cancelled: 'bg-slate-100 text-slate-700',
  }
  return map[status] || 'bg-slate-100 text-slate-700'
}

const getStatusDotColor = (status) => {
  const map = {
    pending: 'bg-amber-500',
    in_transit: 'bg-blue-500',
    received: 'bg-emerald-500',
    cancelled: 'bg-slate-400',
  }
  return map[status] || 'bg-slate-400'
}

const formatStatus = (status) => {
  const map = {
    pending: 'Pending',
    in_transit: 'In Transit',
    received: 'Received',
    cancelled: 'Cancelled',
  }
  return map[status] || status
}

const formatDate = (dt) => {
  return dt ? new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'
}

onMounted(async () => {
  try {
    const [transRes, branchRes] = await Promise.all([
      api.get('/transfers'),
      api.get('/branches')
    ])
    transfers.value = transRes || []
    branches.value = branchRes || []
    console.log('Transfers loaded:', transfers.value.length)
    console.log('Branches loaded:', branches.value.length)
  } catch (err) {
    console.error('Failed to load data:', err)
    alert('Failed to load transfers or branches. Please refresh the page.')
  } finally {
    loading.value = false
  }
})

async function onFromBranchChange() {
  tform.items = []
  sourceInventory.value = []
  itemPicker.productId = ''
  itemPicker.quantity = 1

  if (!tform.from_branch_id) return

  try {
    const res = await api.get(`/inventory?branch_id=${tform.from_branch_id}`)
    sourceInventory.value = (res || []).filter(i => i.quantity > 0)
  } catch (err) {
    console.error('Failed to load inventory:', err)
  }
}

function onProductSelect() {
  itemPicker.quantity = 1
  itemError.value = ''
}

function addTransferItem() {
  itemError.value = ''

  if (!itemPicker.productId) {
    itemError.value = 'Please select a product.'
    return
  }

  if (itemPicker.quantity <= 0) {
    itemError.value = 'Quantity must be greater than 0.'
    return
  }

  if (itemPicker.quantity > sourceAvailableQty.value) {
    itemError.value = `Cannot exceed available stock (${sourceAvailableQty.value} units).`
    return
  }

  const inv = sourceInventory.value.find(i => i.product_id === itemPicker.productId)
  const existing = tform.items.find(i => i.product_id === itemPicker.productId)

  if (existing) {
    existing.quantity += itemPicker.quantity
  } else {
    tform.items.push({
      product_id: inv.product_id,
      product_name: inv.product_name,
      quantity: itemPicker.quantity,
    })
  }

  itemPicker.productId = ''
  itemPicker.quantity = 1
}

function removeTransferItem(idx) {
  tform.items.splice(idx, 1)
}

async function submitTransfer() {
  if (!tform.items.length || !tform.from_branch_id || !tform.to_branch_id) return

  submitting.value = true
  modalError.value = ''
  modalSuccess.value = ''

  try {
    await api.post('/transfers', tform)
    modalSuccess.value = 'Transfer request created successfully!'

    const res = await api.get('/transfers')
    transfers.value = res || []

    Object.assign(tform, { from_branch_id: '', to_branch_id: '', notes: '', items: [] })
    sourceInventory.value = []
    itemPicker.productId = ''
    itemPicker.quantity = 1

    setTimeout(() => {
      showModal.value = false
      modalSuccess.value = ''
    }, 1500)
  } catch (err) {
    modalError.value = err?.message || 'Failed to create transfer request.'
  } finally {
    submitting.value = false
  }
}

async function approveTransfer(id) {
  try {
    await api.post(`/transfers/${id}/approve`)
    const res = await api.get('/transfers')
    transfers.value = res || []
  } catch (err) {
    alert(err?.message || 'Failed to approve transfer.')
  }
}

async function completeTransfer(id) {
  try {
    await api.post(`/transfers/${id}/complete`)
    const res = await api.get('/transfers')
    transfers.value = res || []
  } catch (err) {
    alert(err?.message || 'Failed to complete transfer.')
  }
}

async function rejectTransfer(id) {
  try {
    await api.post(`/transfers/${id}/reject`)
    const res = await api.get('/transfers')
    transfers.value = res || []
  } catch (err) {
    alert(err?.message || 'Failed to reject transfer.')
  }
}

function viewTransferDetails(t) {
  console.log('View transfer details:', t)
  // TODO: Implement detailed view
}
</script>
