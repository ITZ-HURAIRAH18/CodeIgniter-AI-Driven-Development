<template>
  <div class="create-order">
    <div class="page-header">
      <div>
        <h1 class="page-title">Create Order</h1>
        <p class="page-subtitle">Select products, verify stock, and place order</p>
      </div>
      <router-link to="/orders" class="btn btn-secondary">← Back to Orders</router-link>
    </div>

    <!-- Alert -->
    <div v-if="alertMsg" :class="`alert alert-${alertType}`">{{ alertMsg }}</div>

    <div class="order-layout">
      <!-- Left: Order builder -->
      <div class="order-form">

        <!-- Branch selection (admin, manager, sales) -->
        <div class="card" v-if="auth.isAdmin || auth.isBranchManager || auth.isSalesUser">
          <h3 class="section-title">Branch</h3>
          <div class="form-group">
            <label class="form-label">Select Branch</label>
            <select v-model="selectedBranchId" class="form-control" @change="onBranchChange">
              <option value="">— Select branch —</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
        </div>

        <!-- Product Picker -->
        <div class="card">
          <h3 class="section-title">Add Products</h3>

          <div v-if="!effectiveBranchId" class="empty-state-sm">
            Select a branch first
          </div>

          <template v-else>
            <div class="product-picker">
              <div class="form-group">
                <label class="form-label">Product</label>
                <select v-model="picker.productId" class="form-control" @change="onProductSelect">
                  <option value="">— Choose product —</option>
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

              <div class="form-group" v-if="picker.productId">
                <label class="form-label">
                  Quantity
                  <span class="form-hint" style="display:inline; margin-left:8px">
                    Max: {{ availableQty }}
                  </span>
                </label>
                <input
                  v-model.number="picker.quantity"
                  type="number" min="1" :max="availableQty"
                  class="form-control"
                  :class="{ 'is-error': picker.qtyError }"
                  placeholder="0"
                />
                <span v-if="picker.qtyError" class="form-error">{{ picker.qtyError }}</span>
              </div>

              <!-- Price preview -->
              <div class="price-preview" v-if="picker.productId && picker.quantity > 0">
                <span>{{ picker.quantity }} × ${{ unitPrice }}</span>
                <span class="price-line">= ${{ lineTotal }}</span>
              </div>

              <button
                class="btn btn-secondary"
                :disabled="!canAddItem"
                @click="addToOrder"
              >
                + Add to Order
              </button>
            </div>
          </template>
        </div>
      </div>

      <!-- Right: Order summary -->
      <div class="order-summary">
        <div class="card">
          <h3 class="section-title">Order Summary</h3>

          <div v-if="orderItems.length === 0" class="empty-state-sm">
            No items added yet
          </div>

          <div v-else>
            <div class="order-item" v-for="(item, idx) in orderItems" :key="idx">
              <div class="oi-info">
                <div class="oi-name">{{ item.product_name }}</div>
                <div class="oi-qty text-muted text-sm">{{ item.quantity }} × ${{ Number(item.unit_price).toFixed(2) }}</div>
              </div>
              <div class="oi-right">
                <div class="oi-total">${{ Number(item.line_total).toFixed(2) }}</div>
                <button class="oi-remove" @click="removeItem(idx)">✕</button>
              </div>
            </div>

            <div class="order-totals">
              <div class="total-row">
                <span>Subtotal</span>
                <span class="font-mono">${{ subtotal }}</span>
              </div>
              <div class="total-row">
                <span>Tax</span>
                <span class="font-mono">${{ taxTotal }}</span>
              </div>
              <div class="total-row grand">
                <span>Grand Total</span>
                <span class="font-mono">${{ grandTotal }}</span>
              </div>
            </div>

            <div class="form-group mt-4">
              <label class="form-label">Notes (optional)</label>
              <input v-model="notes" type="text" class="form-control" placeholder="Order notes..." />
            </div>

            <button
              class="btn btn-primary btn-lg w-full"
              :disabled="placing || orderItems.length === 0"
              @click="placeOrder"
            >
              <span v-if="placing" class="spinner"></span>
              {{ placing ? 'Placing Order...' : 'Place Order' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'

const auth   = useAuthStore()
const router = useRouter()

// State
const branches      = ref([])
const inventory     = ref([])  // Current branch inventory
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

// For branch managers and sales, fixed to their branch
const effectiveBranchId = computed(() =>
  selectedBranchId.value
)

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

// Computed totals
const subtotal  = computed(() => orderItems.value.reduce((s, i) => s + i.qty_sub, 0).toFixed(2))
const taxTotal  = computed(() => orderItems.value.reduce((s, i) => s + i.tax_amt, 0).toFixed(2))
const grandTotal = computed(() => (parseFloat(subtotal.value) + parseFloat(taxTotal.value)).toFixed(2))

const filteredBranches = computed(() => {
  // Backend API already filters by role:
  // - Admin: gets all branches
  // - Manager: gets only their managed branches
  // - Sales: gets their assigned branch (but sales can't create orders for multiple branches anyway)
  return branches.value
})

onMounted(async () => {
  // Always load all branches for selection
  const res = await api.get('/branches')
  branches.value = res || []
})

async function onBranchChange() {
  inventory.value = []
  orderItems.value = []
  if (selectedBranchId.value) await loadInventory()
}

async function loadInventory() {
  const branchId = effectiveBranchId.value
  if (!branchId) return
  const res = await api.get(`/inventory?branch_id=${branchId}`)
  inventory.value = res || []
}

function onProductSelect() {
  picker.value.quantity = 1
  picker.value.qtyError = ''
}

function addToOrder() {
  // Client-side validation BEFORE hitting API
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

  // Check if product already in order — merge quantities
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
  if (!orderItems.value.length) return

  placing.value = true
  alertMsg.value = ''

  try {
    const payload = {
      branch_id: effectiveBranchId.value,
      notes:     notes.value,
      items: orderItems.value.map(i => ({
        product_id: i.product_id,
        quantity:   i.quantity,
      })),
    }

    const res = await api.post('/orders', payload)
    alertMsg.value  = `Order ${res?.order_number || ''} placed successfully!`
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

<style scoped>
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; margin-bottom: 24px;
}
.page-title   { font-size: 24px; font-weight: 700; letter-spacing: -0.03em; }
.page-subtitle { color: var(--clr-text-muted); font-size: 14px; margin-top: 4px; }

.order-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 1024px) { .order-layout { grid-template-columns: 1fr; } }

.section-title { font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--clr-text-secondary); }
.empty-state-sm { text-align: center; color: var(--clr-text-muted); padding: 24px; font-size: 14px; }
.order-form { display: flex; flex-direction: column; gap: 16px; }

.card { margin-bottom: 0; }

.product-picker { display: flex; flex-direction: column; gap: 12px; }

.price-preview {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 14px;
  background: var(--clr-bg-elevated);
  border-radius: var(--radius-md);
  font-size: 14px;
  color: var(--clr-text-secondary);
}
.price-line { font-weight: 700; color: var(--clr-accent-light); font-size: 16px; }

.is-error { border-color: var(--clr-danger); }

/* Order items */
.order-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid var(--clr-border);
}
.order-item:last-of-type { border-bottom: none; }
.oi-name  { font-size: 14px; font-weight: 500; }
.oi-right { display: flex; align-items: center; gap: 12px; }
.oi-total { font-weight: 600; font-size: 15px; }
.oi-remove {
  background: transparent; border: none;
  color: var(--clr-text-muted); cursor: pointer;
  font-size: 12px; padding: 4px;
  transition: color var(--trans-fast);
}
.oi-remove:hover { color: var(--clr-danger); }

/* Totals */
.order-totals {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--clr-border);
}
.total-row {
  display: flex; justify-content: space-between;
  font-size: 14px; color: var(--clr-text-secondary);
  padding: 5px 0;
}
.total-row.grand {
  font-size: 18px; font-weight: 700;
  color: var(--clr-text-primary);
  margin-top: 8px; padding-top: 12px;
  border-top: 1px solid var(--clr-border-accent);
}
</style>
