<template>
  <div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
      <div class="flex items-center gap-2 text-sm text-slate-500 font-medium mb-2">
        <span>{{ t('common.dashboard') }}</span>
        <span class="text-slate-300">/</span>
        <router-link to="/orders" class="hover:text-slate-700 transition-colors">{{ t('common.orders') }}</router-link>
        <span class="text-slate-300">/</span>
        <span class="text-slate-900 font-semibold">{{ t('orders.createOrder') }}</span>
      </div>
      <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ t('orders.createNewOrder') }}</h1>
    </div>

    <!-- Progress Stepper -->
    <div class="bg-white border border-slate-200 rounded-lg p-6">
      <div class="flex items-center justify-between">
        <!-- Step 1: Select Branch -->
        <div class="flex items-center flex-1">
          <div :class="['flex items-center justify-center w-8 h-8 rounded-full font-semibold text-xs', step >= 1 ? 'bg-accent-pink-600 text-white' : 'bg-slate-200 text-slate-600']">         
            1
          </div>
          <div class="ml-3">
            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('orders.step1') }}</p>
            <p class="text-sm font-medium text-slate-900">{{ t('orders.selectBranch') }}</p>
          </div>
        </div>

        <!-- Connector 1 -->
        <div :class="['h-0.5 mx-4', step >= 2 ? 'bg-accent-pink-600' : 'bg-slate-200']" style="flex:1;max-width:40px"></div>

        <!-- Step 2: Add Items -->
        <div class="flex items-center flex-1">
          <div :class="['flex items-center justify-center w-8 h-8 rounded-full font-semibold text-xs', step >= 2 ? 'bg-accent-pink-600 text-white' : 'bg-slate-200 text-slate-600']">        
            2
          </div>
          <div class="ml-3">
            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('orders.step2') }}</p>
            <p class="text-sm font-medium text-slate-900">{{ t('orders.addItems') }}</p>
          </div>
        </div>

        <!-- Connector 2 -->
        <div :class="['h-0.5 mx-4', step >= 3 ? 'bg-accent-pink-600' : 'bg-slate-200']" style="flex:1;max-width:40px"></div>

        <!-- Step 3: Review & Place -->
        <div class="flex items-center flex-1">
          <div :class="['flex items-center justify-center w-8 h-8 rounded-full font-semibold text-xs', step >= 3 ? 'bg-accent-pink-600 text-white' : 'bg-slate-200 text-slate-600']">        
            3
          </div>
          <div class="ml-3">
            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('orders.step3') }}</p>
            <p class="text-sm font-medium text-slate-900">{{ t('orders.reviewPlace') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert -->
    <div v-if="alertMsg" :class="['rounded-lg border px-4 py-3 text-sm font-medium', alertType === 'success' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-red-50 border-red-300 text-red-800']">
      {{ alertMsg }}
    </div>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-3 gap-6 auto-rows-max">

      <!-- LEFT: 2/3 - Form Column -->
      <div class="col-span-2 space-y-6">

        <!-- Branch Selection Card -->
        <div v-if="auth.isAdmin || auth.isBranchManager || auth.isSalesUser" class="bg-white border border-slate-200 rounded-lg p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-accent-pink-50 flex items-center justify-center">
              <CheckCircle2Icon class="w-4 h-4 text-accent-pink-600" />
            </div>
            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">{{ t('orders.selectBranch') }}</h2>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">{{ t('common.branch') }} *</label>
            <select 
              v-model="selectedBranchId" 
              @change="onBranchChange"
              class="w-full px-3 py-2.5 h-10 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500/20 cursor-pointer"
            >
              <option value="">— {{ t('inventory.selectBranch') }} —</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
        </div>

        <!-- Add Products Card -->
        <div class="bg-white border border-slate-200 rounded-lg p-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 rounded-full" :class="selectedBranchId ? 'bg-accent-pink-50' : 'bg-slate-100'">
              <ShoppingCartIcon class="w-4 h-4" :class="selectedBranchId ? 'text-accent-pink-600' : 'text-slate-400'" style="margin:2px auto" />
            </div>
            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">{{ t('orders.addProducts') }}</h2>
          </div>

          <div v-if="!selectedBranchId" class="text-center py-8 px-4">
            <ShoppingCartIcon class="w-8 h-8 text-slate-300 mx-auto mb-2" />
            <p class="text-slate-600 text-sm font-medium">{{ t('orders.selectBranchFirst') }}</p>
          </div>

          <template v-else>
            <div class="space-y-4">
              <!-- Product Selection -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">{{ t('common.product') }} *</label>
                <select 
                  v-model="picker.productId" 
                  @change="onProductSelect"
                  class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500/20 cursor-pointer"
                >
                  <option value="">— {{ t('inventory.selectProducts') }} —</option>
                  <option
                    v-for="item in inventoryOptions"
                    :key="item.product_id"
                    :value="item.product_id"
                    :disabled="item.quantity === 0"
                  >
                    {{ item.product_name }} ({{ item.sku }}) — {{ item.quantity }} in stock
                  </option>
                </select>
              </div>

              <!-- Product Details Card (if selected) -->
              <div v-if="selectedInvItem" class="bg-slate-50 border border-slate-200 rounded-md p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('common.product') }}</p>
                    <p class="text-sm font-medium text-slate-900 mt-1">{{ selectedInvItem.product_name }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('common.sku') }}</p>
                    <p class="text-sm font-mono text-slate-900 mt-1">{{ selectedInvItem.sku }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('inventory.stockLevel') }}</p>
                    <p class="text-sm font-medium text-slate-900 mt-1">{{ selectedInvItem.quantity }} {{ t('common.units') }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('inventory.unitPrice') }}</p>
                    <p class="text-sm font-mono font-medium text-slate-900 mt-1">${{ Number(selectedInvItem.sale_price).toFixed(2) }}</p>
                  </div>
                </div>
              </div>

              <!-- Quantity Input -->
              <div v-if="picker.productId">
                <div class="flex items-end justify-between mb-2">
                  <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ t('inventory.quantity') }} *</label>
                  <span class="text-xs text-slate-500">{{ t('inventory.maxUnits', { count: availableQty }) }}</span>
                </div>
                <div class="flex gap-2">
                  <input
                    v-model.number="picker.quantity"
                    type="number" 
                    min="1" 
                    :max="availableQty"
                    class="flex-1 px-3 py-2.5 h-10 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500/20"
                    :class="{ 'border-red-500 ring-1 ring-red-500/20': picker.qtyError }"
                    placeholder="0"
                  />
                  <button
                    @click="addToOrder"
                    :disabled="!canAddItem"
                    class="px-4 h-10 bg-accent-pink-600 text-white rounded-md font-medium text-sm hover:bg-accent-pink-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <PlusIcon class="w-4 h-4" />
                  </button>
                </div>
                <p v-if="picker.qtyError" class="text-xs text-red-600 mt-2">{{ picker.qtyError }}</p>
                <div v-else-if="picker.quantity > 0" class="flex items-center justify-between px-3 py-2.5 mt-2 bg-accent-pink-50 border border-accent-pink-200 rounded-md">
                  <span class="text-sm text-accent-pink-700">{{ picker.quantity }} × ${{ unitPrice }}</span>
                  <span class="text-sm font-semibold text-accent-pink-900">= ${{ lineTotal }}</span>
                </div>
              </div>
            </div>
          </template>
        </div>

      </div>

      <!-- RIGHT: 1/3 - Sticky Summary Column -->
      <div class="col-span-1">
        <div class="sticky top-6 bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-6">{{ t('orders.orderSummary') }}</h3>

          <!-- Empty State -->
          <div v-if="orderItems.length === 0" class="text-center py-8">
            <ShoppingBagIcon class="w-8 h-8 text-slate-300 mx-auto mb-2" />
            <p class="text-slate-600 text-sm font-medium">{{ t('orders.noItems') }}</p>
            <p class="text-slate-500 text-xs mt-1">{{ t('orders.addNotesPlaceholder') }}</p>
          </div>

          <!-- Order Items List -->
          <div v-else class="space-y-4">
            <!-- Items -->
            <div class="space-y-3 mb-4 pb-4 border-b border-slate-200">
              <div 
                v-for="(item, idx) in orderItems" 
                :key="idx"
                class="flex items-start justify-between gap-2 group"
              >
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-slate-900 truncate">{{ item.product_name }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ item.quantity }} × ${{ Number(item.unit_price).toFixed(2) }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-semibold text-slate-900 tabular-nums">${{ Number(item.line_total).toFixed(2) }}</span>
                  <button
                    @click="removeItem(idx)"
                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-red-50 rounded text-red-600 hover:text-red-700"
                  >
                    <TrashIcon class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Breakdown -->
            <div class="space-y-2.5">
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">{{ t('common.subtotal') }}</span>
                <span class="font-mono text-slate-900">{{ subtotal }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">{{ t('common.tax') }}</span>
                <span class="font-mono text-slate-900">{{ taxTotal }}</span>
              </div>
              <div class="pt-2.5 border-t border-slate-200 flex items-center justify-between">
                <span class="font-semibold text-slate-900">{{ t('common.grandTotal') }}</span>
                <span class="text-lg font-bold text-accent-pink-600 font-mono">{{ grandTotal }}</span>
              </div>
            </div>

            <!-- Notes -->
            <div class="mt-4 pt-4 border-t border-slate-200">
              <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">{{ t('common.notes') }} ({{ t('common.optional') }})</label>
              <textarea 
                v-model="notes"
                :placeholder="t('orders.addNotesPlaceholder')"
                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-md focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500/20 resize-none"
                rows="2"
              ></textarea>
            </div>

            <!-- Place Order Button -->
            <button
              @click="placeOrder"
              :disabled="placing || orderItems.length === 0 || !selectedBranchId"
              class="w-full mt-4 px-4 py-2.5 h-10 bg-accent-pink-600 text-white rounded-lg font-medium text-sm hover:bg-accent-pink-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <CheckCircle2Icon v-if="!placing" class="w-4 h-4" />
              <div v-else class="w-4 h-4 border-2 border-white border-t-accent-pink-600 rounded-full animate-spin"></div>
              {{ placing ? t('orders.placingOrder') : t('orders.placeOrder') }}
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'
import { useI18n } from '@/composables/useI18n'
import { ShoppingCartIcon, ShoppingBagIcon, TrashIcon, PlusIcon, CheckCircle2Icon } from 'lucide-vue-next'
import api from '@/api/axios'

const auth   = useAuthStore()
const router = useRouter()
const { t, language } = useI18n()

// State
const branches      = ref([])
const inventory     = ref([])
const orderItems    = ref([])
const selectedBranchId = ref('')
const notes         = ref('')
const placing       = ref(false)
const alertMsg      = ref('')
const alertType     = ref('error')

const picker = ref({
  productId: '',
  quantity:  1,
  qtyError:  '',
})

// Current step in stepper (1, 2, or 3)
const step = computed(() => {
  if (!selectedBranchId.value) return 1
  if (orderItems.value.length === 0) return 2
  return 3
})

const inventoryOptions = computed(() =>
  inventory.value.filter(i => i.quantity > 0)
)

const selectedInvItem = computed(() =>
  inventory.value.find(i => i.product_id === picker.value.productId)
)

const availableQty = computed(() => selectedInvItem.value?.quantity || 0)
const unitPrice    = computed(() => Number(selectedInvItem.value?.sale_price || 0).toFixed(2))

const lineTotal = computed(() => {
  if (!selectedInvItem.value || !picker.value.quantity) return '0.00'
  const sub = picker.value.quantity * Number(selectedInvItem.value.sale_price)
  const tax = sub * (Number(selectedInvItem.value.tax_percentage) / 100)
  return (sub + tax).toFixed(2)
})

const canAddItem = computed(() => {
  return picker.value.productId &&
         picker.value.quantity > 0 &&
         picker.value.quantity <= availableQty.value
})

// Totals
const subtotal  = computed(() => '$' + orderItems.value.reduce((s, i) => s + i.qty_sub, 0).toFixed(2))
const taxTotal  = computed(() => '$' + orderItems.value.reduce((s, i) => s + i.tax_amt, 0).toFixed(2))
const grandTotal = computed(() => '$' + (parseFloat(orderItems.value.reduce((s, i) => s + i.qty_sub, 0)) + parseFloat(orderItems.value.reduce((s, i) => s + i.tax_amt, 0))).toFixed(2))

const filteredBranches = computed(() => branches.value)

const loadBranches = async () => {
  const res = await api.get('/branches', { params: { lang: language.value } })
  branches.value = res || []
}

onMounted(async () => {
  await loadBranches()
})

watch(language, async () => {
  await loadBranches()
  if (selectedBranchId.value) await loadInventory()
})

async function onBranchChange() {
  inventory.value = []
  orderItems.value = []
  if (selectedBranchId.value) await loadInventory()
}

async function loadInventory() {
  if (!selectedBranchId.value) return
  const res = await api.get(`/inventory?branch_id=${selectedBranchId.value}&lang=${language.value}`)
  inventory.value = res || []
}

function onProductSelect() {
  picker.value.quantity = 1
  picker.value.qtyError = ''
}

function addToOrder() {
  picker.value.qtyError = ''

  if (picker.value.quantity <= 0) {
    picker.value.qtyError = 'Quantity must be greater than 0.'
    return
  }
  if (picker.value.quantity > availableQty.value) {
    picker.value.qtyError = `Cannot exceed available stock (${availableQty.value}).`
    return
  }

  const inv = selectedInvItem.value
  const qty = picker.value.quantity
  const sub = qty * Number(inv.sale_price)
  const tax = sub * (Number(inv.tax_percentage) / 100)

  // Merge if product already in order
  const existing = orderItems.value.find(i => i.product_id === inv.product_id)
  if (existing) {
    const newQty = existing.quantity + qty
    if (newQty > availableQty.value) {
      picker.value.qtyError = `Total would exceed available stock (${availableQty.value}).`
      return
    }
    existing.quantity  += qty
    existing.qty_sub   += sub
    existing.tax_amt   += tax
    existing.line_total = (existing.qty_sub + existing.tax_amt).toFixed(2)
  } else {
    orderItems.value.push({
      product_id:   inv.product_id,
      product_name: inv.product_name,
      quantity:     qty,
      unit_price:   inv.sale_price,
      tax_pct:      inv.tax_percentage,
      qty_sub:      sub,
      tax_amt:      tax,
      line_total:   (sub + tax).toFixed(2),
    })
  }

  // Reset picker
  picker.value.productId = ''
  picker.value.quantity  = 1
}

function removeItem(idx) {
  orderItems.value.splice(idx, 1)
}

async function placeOrder() {
  if (!orderItems.value.length || !selectedBranchId.value) return

  placing.value = true
  alertMsg.value = ''

  try {
    const payload = {
      branch_id: selectedBranchId.value,
      notes:     notes.value,
      items: orderItems.value.map(i => ({
        product_id: i.product_id,
        quantity:   i.quantity,
      })),
    }

    const res = await api.post('/orders', payload)
    alertMsg.value  = `Order #${res?.order_number || ''} placed successfully!`
    alertType.value = 'success'

    setTimeout(() => router.push('/orders'), 1500)
  } catch (err) {
    alertMsg.value  = err?.message || 'Order failed. Please try again.'
    alertType.value = 'error'
  } finally {
    placing.value = false
  }
}
</script>
