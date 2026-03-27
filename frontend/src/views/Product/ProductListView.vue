<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle">Product catalog with pricing and tax</p>
      </div>
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openCreate">+ Add Product</button>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Catalog ({{ products.length }})</span>
        <input v-model="search" class="form-control" style="max-width:240px" placeholder="Search SKU or name..." />
      </div>

      <div v-if="loading" style="text-align:center;padding:40px"><span class="spinner"></span></div>

      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>SKU</th><th>Name</th><th>Cost</th><th>Sale Price</th>
              <th>Tax %</th><th>Unit</th><th>Status</th>
              <th v-if="auth.isAdmin">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in filteredProducts" :key="p.id">
              <td class="font-mono text-muted">{{ p.sku }}</td>
              <td>{{ p.name }}</td>
              <td class="font-mono">${{ Number(p.cost_price).toFixed(2) }}</td>
              <td class="font-mono">${{ Number(p.sale_price).toFixed(2) }}</td>
              <td>{{ p.tax_percentage }}%</td>
              <td class="text-muted">{{ p.unit }}</td>
              <td>
                <span :class="p.status === 'active' ? 'badge badge-success' : 'badge badge-danger'">
                  {{ p.status }}
                </span>
              </td>
              <td v-if="auth.isAdmin">
                <div class="flex gap-2">
                  <button class="btn btn-secondary btn-sm" @click="openEdit(p)">Edit</button>
                  <button class="btn btn-danger btn-sm" @click="deleteProduct(p.id)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0">
              <td :colspan="auth.isAdmin ? 8 : 7" style="text-align:center;color:var(--clr-text-muted);padding:32px">
                No products found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Product Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <h2 class="modal-title">{{ editing ? 'Edit Product' : 'New Product' }}</h2>
        <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
        <form @submit.prevent="saveProduct">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">SKU *</label>
              <input v-model="form.sku" class="form-control" required placeholder="PROD-001" :disabled="!!editing" />
            </div>
            <div class="form-group">
              <label class="form-label">Unit</label>
              <input v-model="form.unit" class="form-control" placeholder="pcs" />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Product Name *</label>
            <input v-model="form.name" class="form-control" required placeholder="Widget A" />
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Cost Price *</label>
              <input v-model.number="form.cost_price" type="number" step="0.01" min="0" class="form-control" required />
            </div>
            <div class="form-group">
              <label class="form-label">Sale Price *</label>
              <input v-model.number="form.sale_price" type="number" step="0.01" min="0" class="form-control" required />
            </div>
            <div class="form-group">
              <label class="form-label">Tax % *</label>
              <input v-model.number="form.tax_percentage" type="number" step="0.01" min="0" max="100" class="form-control" required />
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-control">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <input v-model="form.description" class="form-control" placeholder="Optional description" />
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner"></span>
              {{ saving ? 'Saving...' : 'Save Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'

const auth     = useAuthStore()
const products = ref([])
const loading  = ref(true)
const search   = ref('')
const showModal = ref(false)
const editing   = ref(null)
const saving    = ref(false)
const modalError = ref('')

const form = reactive({
  sku: '', name: '', description: '', cost_price: 0,
  sale_price: 0, tax_percentage: 0, unit: 'pcs', status: 'active'
})

const filteredProducts = computed(() => {
  if (!search.value) return products.value
  const q = search.value.toLowerCase()
  return products.value.filter(p =>
    p.name?.toLowerCase().includes(q) || p.sku?.toLowerCase().includes(q))
})

onMounted(async () => {
  const res = await api.get('/products')
  products.value = res || []
  loading.value  = false
})

function openCreate() {
  editing.value = null; modalError.value = ''
  Object.assign(form, { sku:'',name:'',description:'',cost_price:0,sale_price:0,tax_percentage:0,unit:'pcs',status:'active' })
  showModal.value = true
}
function openEdit(p) {
  editing.value = p.id; modalError.value = ''
  Object.assign(form, p)
  showModal.value = true
}

async function saveProduct() {
  saving.value = true; modalError.value = ''
  try {
    if (editing.value) {
      await api.put(`/products/${editing.value}`, form)
    } else {
      await api.post('/products', form)
    }
    const res = await api.get('/products')
    products.value = res || []
    showModal.value = false
  } catch (e) {
    modalError.value = e?.message || 'Save failed.'
  } finally {
    saving.value = false
  }
}

async function deleteProduct(id) {
  if (!confirm('Delete this product?')) return
  await api.delete(`/products/${id}`)
  products.value = products.value.filter(p => p.id !== id)
}
</script>

<style scoped>
.page-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px; }
.page-title   { font-size:24px;font-weight:700;letter-spacing:-0.03em; }
.page-subtitle { color:var(--clr-text-muted);font-size:14px;margin-top:4px; }

.form-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }

/* Modal */
.modal-overlay {
  position:fixed;inset:0;background:rgba(0,0,0,0.7);
  display:flex;align-items:center;justify-content:center;z-index:1000;
  backdrop-filter:blur(4px);
}
.modal-box {
  background:var(--clr-bg-surface);border:1px solid var(--clr-border);
  border-radius:var(--radius-xl);padding:32px;width:100%;max-width:560px;
  max-height:90vh;overflow-y:auto;
}
.modal-title { font-size:20px;font-weight:700;margin-bottom:20px; }
.modal-actions { display:flex;justify-content:flex-end;gap:12px;margin-top:20px; }
</style>
