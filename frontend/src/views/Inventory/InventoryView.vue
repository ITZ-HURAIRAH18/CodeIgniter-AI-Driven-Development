<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Inventory</h1>
        <p class="page-subtitle">View and manage stock levels by branch</p>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-secondary" @click="showAddModal = true" v-if="canManage">+ Add Stock</button>
        <button class="btn btn-secondary" @click="showAdjustModal = true" v-if="canManage">⟳ Adjust</button>
      </div>
    </div>

    <!-- Branch selector (admin sees all) -->
    <div class="card" style="margin-bottom:20px" v-if="auth.isAdmin">
      <div style="display:flex;gap:12px;align-items:center">
        <label class="form-label" style="margin:0;white-space:nowrap">Branch:</label>
        <select v-model="selectedBranchId" class="form-control" style="max-width:260px" @change="loadInventory">
          <option value="">All Branches</option>
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" style="text-align:center;padding:40px">
      <span class="spinner"></span>
    </div>

    <div v-else class="card">
      <div class="card-header">
        <span class="card-title">Stock Levels</span>
        <input v-model="search" type="text" class="form-control" style="max-width:240px" placeholder="Search products..." />
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>In Stock</th>
              <th>Reorder Level</th>
              <th>Sale Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredInventory" :key="item.id">
              <td>{{ item.product_name }}</td>
              <td class="font-mono text-muted">{{ item.sku }}</td>
              <td>
                <strong :class="item.quantity <= item.reorder_level ? 'text-danger' : ''">
                  {{ item.quantity }}
                </strong>
              </td>
              <td class="text-muted">{{ item.reorder_level }}</td>
              <td class="font-mono">${{ Number(item.sale_price).toFixed(2) }}</td>
              <td>
                <span :class="item.quantity === 0 ? 'badge badge-danger' : item.quantity <= item.reorder_level ? 'badge badge-warning' : 'badge badge-success'">
                  {{ item.quantity === 0 ? 'Out of Stock' : item.quantity <= item.reorder_level ? 'Low' : 'OK' }}
                </span>
              </td>
            </tr>
            <tr v-if="filteredInventory.length === 0">
              <td colspan="6" style="text-align:center;color:var(--clr-text-muted);padding:32px">
                No inventory found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'

const auth = useAuthStore()
const branches = ref([])
const inventory = ref([])
const selectedBranchId = ref(auth.userBranchId || '')
const loading = ref(true)
const search  = ref('')
const showAddModal    = ref(false)
const showAdjustModal = ref(false)

const canManage = computed(() => auth.isAdmin || auth.isBranchManager)

const filteredInventory = computed(() => {
  if (!search.value) return inventory.value
  const q = search.value.toLowerCase()
  return inventory.value.filter(i =>
    i.product_name?.toLowerCase().includes(q) || i.sku?.toLowerCase().includes(q)
  )
})

onMounted(async () => {
  if (auth.isAdmin) {
    const res = await api.get('/branches')
    branches.value = res || []
    if (branches.value.length) selectedBranchId.value = branches.value[0].id
  }
  await loadInventory()
})

async function loadInventory() {
  loading.value = true
  try {
    const branchId = selectedBranchId.value || auth.userBranchId
    if (!branchId) { loading.value = false; return }
    const res = await api.get(`/inventory?branch_id=${branchId}`)
    inventory.value = res || []
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px; }
.page-title   { font-size:24px;font-weight:700;letter-spacing:-0.03em; }
.page-subtitle { color:var(--clr-text-muted);font-size:14px;margin-top:4px; }
.text-danger   { color: var(--clr-danger); }
</style>
