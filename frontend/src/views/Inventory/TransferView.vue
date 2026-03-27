<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Stock Transfers</h1>
        <p class="page-subtitle">Transfer inventory between branches</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">+ New Transfer</button>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Transfer Requests</span>
        <select v-model="statusFilter" class="form-control" style="max-width:160px">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="completed">Completed</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <div v-if="loading" style="text-align:center;padding:40px"><span class="spinner"></span></div>

      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th><th>From</th><th>To</th><th>Products</th>
              <th>Status</th><th>Date</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in filteredTransfers" :key="t.id">
              <td class="font-mono text-muted">#{{ t.id }}</td>
              <td>{{ t.from_branch }}</td>
              <td>{{ t.to_branch }}</td>
              <td class="text-muted">{{ t.initiated_by_name }}</td>
              <td><span :class="statusBadge(t.status)">{{ t.status }}</span></td>
              <td class="text-muted text-sm">{{ formatDate(t.created_at) }}</td>
              <td>
                <div class="flex gap-2">
                  <button
                    v-if="t.status === 'pending'"
                    class="btn btn-success btn-sm"
                    @click="changeStatus(t.id, 'approve')"
                  >Approve</button>
                  <button
                    v-if="t.status === 'approved'"
                    class="btn btn-primary btn-sm"
                    @click="changeStatus(t.id, 'complete')"
                  >Complete</button>
                  <button
                    v-if="t.status === 'pending'"
                    class="btn btn-danger btn-sm"
                    @click="changeStatus(t.id, 'reject')"
                  >Reject</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredTransfers.length === 0">
              <td colspan="7" style="text-align:center;color:var(--clr-text-muted);padding:32px">No transfers</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Transfer Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <h2 class="modal-title">New Stock Transfer</h2>

        <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
        <div v-if="modalSuccess" class="alert alert-success">{{ modalSuccess }}</div>

        <form @submit.prevent="submitTransfer">
          <!-- Branch selectors -->
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">From Branch *</label>
              <select v-model="tform.from_branch_id" class="form-control" @change="onFromBranchChange" required>
                <option value="">— Select —</option>
                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">To Branch *</label>
              <select v-model="tform.to_branch_id" class="form-control" required>
                <option value="">— Select —</option>
                <option
                  v-for="b in branches" :key="b.id" :value="b.id"
                  :disabled="b.id === tform.from_branch_id"
                >{{ b.name }}</option>
              </select>
            </div>
          </div>

          <!-- Product picker (shows available stock from source) -->
          <div v-if="tform.from_branch_id" class="form-group">
            <label class="form-label">Add Product</label>
            <div class="product-row">
              <select v-model="itemPicker.productId" class="form-control">
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
              <input
                v-model.number="itemPicker.quantity"
                type="number" min="1"
                :max="sourceAvailableQty"
                class="form-control"
                style="max-width:100px"
                placeholder="Qty"
              />
              <button type="button" class="btn btn-secondary" @click="addTransferItem">Add</button>
            </div>
            <span v-if="itemError" class="form-error">{{ itemError }}</span>
          </div>

          <!-- Items list -->
          <div v-if="tform.items.length" class="transfer-items">
            <div class="ti-header">Products to Transfer</div>
            <div v-for="(item, idx) in tform.items" :key="idx" class="transfer-item">
              <div>{{ item.product_name }}</div>
              <div class="flex gap-2" style="align-items:center">
                <span class="badge badge-neutral">{{ item.quantity }} units</span>
                <button type="button" class="oi-remove" @click="tform.items.splice(idx,1)">✕</button>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Notes</label>
            <input v-model="tform.notes" class="form-control" placeholder="Reason for transfer..." />
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="submitting || !tform.items.length">
              <span v-if="submitting" class="spinner"></span>
              {{ submitting ? 'Submitting...' : 'Create Transfer Request' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '@/api/axios'

const transfers    = ref([])
const branches     = ref([])
const sourceInventory = ref([])
const loading      = ref(true)
const showModal    = ref(false)
const submitting   = ref(false)
const modalError   = ref('')
const modalSuccess = ref('')
const statusFilter = ref('')
const itemError    = ref('')

const tform = reactive({
  from_branch_id: '',
  to_branch_id:   '',
  notes:          '',
  items:          [],
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

onMounted(async () => {
  const [transRes, branchRes] = await Promise.all([
    api.get('/transfers'), api.get('/branches')
  ])
  transfers.value = transRes || []
  branches.value  = branchRes || []
  loading.value   = false
})

async function onFromBranchChange() {
  tform.items = []
  sourceInventory.value = []
  if (!tform.from_branch_id) return
  const res = await api.get(`/inventory?branch_id=${tform.from_branch_id}`)
  sourceInventory.value = (res || []).filter(i => i.quantity > 0)
}

function addTransferItem() {
  itemError.value = ''
  if (!itemPicker.productId) { itemError.value = 'Select a product.'; return }
  if (itemPicker.quantity <= 0) { itemError.value = 'Quantity must be > 0.'; return }
  if (itemPicker.quantity > sourceAvailableQty.value) {
    itemError.value = `Cannot exceed available stock (${sourceAvailableQty.value}).`; return
  }

  const inv = sourceInventory.value.find(i => i.product_id === itemPicker.productId)
  const existing = tform.items.find(i => i.product_id === itemPicker.productId)
  if (existing) { existing.quantity += itemPicker.quantity }
  else tform.items.push({ product_id: inv.product_id, product_name: inv.product_name, quantity: itemPicker.quantity })

  itemPicker.productId = ''; itemPicker.quantity = 1
}

async function submitTransfer() {
  submitting.value = true; modalError.value = ''; modalSuccess.value = ''
  try {
    await api.post('/transfers', tform)
    modalSuccess.value = 'Transfer request created!'
    const res = await api.get('/transfers')
    transfers.value = res || []
    Object.assign(tform, { from_branch_id:'', to_branch_id:'', notes:'', items:[] })
    setTimeout(() => { showModal.value = false; modalSuccess.value = '' }, 1500)
  } catch (e) {
    modalError.value = e?.message || 'Failed to create transfer.'
  } finally { submitting.value = false }
}

async function changeStatus(id, action) {
  try {
    await api.post(`/transfers/${id}/${action}`)
    const res = await api.get('/transfers')
    transfers.value = res || []
  } catch (e) { alert(e?.message || 'Action failed.') }
}

function statusBadge(s) {
  const m = { pending:'badge badge-warning', approved:'badge badge-info', completed:'badge badge-success', rejected:'badge badge-danger' }
  return m[s] || 'badge badge-neutral'
}
function formatDate(dt) { return dt ? new Date(dt).toLocaleDateString() : '—' }
</script>

<style scoped>
.page-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px; }
.page-title   { font-size:24px;font-weight:700;letter-spacing:-0.03em; }
.page-subtitle { color:var(--clr-text-muted);font-size:14px;margin-top:4px; }
.form-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.product-row { display:flex;gap:10px;align-items:center; }

.transfer-items { margin:16px 0;border:1px solid var(--clr-border);border-radius:var(--radius-md);overflow:hidden; }
.ti-header { padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:var(--clr-text-muted);background:var(--clr-bg-elevated); }
.transfer-item { display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-top:1px solid var(--clr-border); }
.oi-remove { background:transparent;border:none;color:var(--clr-text-muted);cursor:pointer;font-size:12px;padding:4px;transition:color var(--trans-fast); }
.oi-remove:hover { color:var(--clr-danger); }
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px); }
.modal-box { background:var(--clr-bg-surface);border:1px solid var(--clr-border);border-radius:var(--radius-xl);padding:32px;width:100%;max-width:600px;max-height:90vh;overflow-y:auto; }
.modal-title { font-size:20px;font-weight:700;margin-bottom:20px; }
.modal-actions { display:flex;justify-content:flex-end;gap:12px;margin-top:20px; }
</style>
