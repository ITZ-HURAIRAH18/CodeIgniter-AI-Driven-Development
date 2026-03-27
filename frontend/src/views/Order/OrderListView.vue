<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">All orders across branches</p>
      </div>
      <router-link to="/orders/create" class="btn btn-primary">+ New Order</router-link>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Order History</span>
        <div class="flex gap-2">
          <input v-model="search" class="form-control" style="max-width:200px" placeholder="Search..." />
          <select v-model="statusFilter" class="form-control" style="max-width:160px">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <div v-if="loading" style="text-align:center;padding:40px"><span class="spinner"></span></div>

      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Order #</th>
              <th>Branch</th>
              <th>Created By</th>
              <th>Items</th>
              <th>Grand Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td class="font-mono">{{ order.order_number }}</td>
              <td>{{ order.branch_name }}</td>
              <td>{{ order.created_by }}</td>
              <td class="text-muted">—</td>
              <td class="font-mono">${{ Number(order.grand_total).toFixed(2) }}</td>
              <td><span :class="statusBadge(order.status)">{{ order.status }}</span></td>
              <td class="text-muted text-sm">{{ formatDate(order.created_at) }}</td>
            </tr>
            <tr v-if="filteredOrders.length === 0">
              <td colspan="7" style="text-align:center;color:var(--clr-text-muted);padding:32px">No orders found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const orders      = ref([])
const loading     = ref(true)
const search       = ref('')
const statusFilter = ref('')

const filteredOrders = computed(() => {
  let data = orders.value
  if (statusFilter.value) data = data.filter(o => o.status === statusFilter.value)
  if (search.value) {
    const q = search.value.toLowerCase()
    data = data.filter(o => o.order_number?.toLowerCase().includes(q) || o.branch_name?.toLowerCase().includes(q))
  }
  return data
})

onMounted(async () => {
  const res = await api.get('/orders')
  orders.value  = res || []
  loading.value = false
})

function statusBadge(status) {
  const map = { completed: 'badge badge-success', pending: 'badge badge-warning', cancelled: 'badge badge-danger', confirmed: 'badge badge-info' }
  return map[status] || 'badge badge-neutral'
}

function formatDate(dt) {
  return dt ? new Date(dt).toLocaleDateString() : '—'
}
</script>

<style scoped>
.page-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px; }
.page-title   { font-size:24px;font-weight:700;letter-spacing:-0.03em; }
.page-subtitle { color:var(--clr-text-muted);font-size:14px;margin-top:4px; }
</style>
