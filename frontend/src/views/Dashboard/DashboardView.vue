<template>
  <div class="dashboard">
    <!-- Page header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ auth.user?.name }} — {{ roleLabel }}</p>
      </div>
      <router-link to="/orders/create" class="btn btn-primary">
        + New Order
      </router-link>
    </div>

    <!-- Stats row -->
    <div class="stats-grid">
      <div class="stat-card" :class="card.color" v-for="card in statCards" :key="card.label">
        <div class="stat-icon">{{ card.icon }}</div>
        <div class="stat-content">
          <div class="stat-value">{{ card.value }}</div>
          <div class="stat-label">{{ card.label }}</div>
          <div class="stat-sub">{{ card.sub }}</div>
        </div>
      </div>
    </div>

    <!-- Two column layout -->
    <div class="dashboard-grid">
      <!-- Recent Orders -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Recent Orders</span>
          <router-link to="/orders" class="btn btn-secondary btn-sm">View All</router-link>
        </div>
        <div v-if="loadingOrders" class="loading-state">
          <span class="spinner"></span> Loading orders...
        </div>
        <div v-else class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Order #</th>
                <th>Branch</th>
                <th>Total</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in recentOrders" :key="order.id">
                <td class="font-mono">{{ order.order_number }}</td>
                <td>{{ order.branch_name }}</td>
                <td class="font-mono">${{ Number(order.grand_total).toFixed(2) }}</td>
                <td><span :class="statusBadge(order.status)">{{ order.status }}</span></td>
              </tr>
              <tr v-if="recentOrders.length === 0">
                <td colspan="4" class="empty-state">No orders yet</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Low Stock Alert -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">⚠ Low Stock Alerts</span>
          <router-link to="/inventory" class="btn btn-secondary btn-sm">Manage</router-link>
        </div>
        <div v-if="loadingInventory" class="loading-state">
          <span class="spinner"></span>
        </div>
        <div v-else>
          <div
            v-for="item in lowStockItems"
            :key="item.id"
            class="low-stock-item"
          >
            <div>
              <div class="lsi-name">{{ item.product_name }}</div>
              <div class="lsi-sku text-muted text-sm">{{ item.sku }}</div>
            </div>
            <div class="lsi-qty">
              <span class="badge badge-danger">{{ item.quantity }} left</span>
            </div>
          </div>
          <div v-if="lowStockItems.length === 0" class="good-state">
            <div class="status-dot green"></div>
            All stock levels are healthy
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'

const auth = useAuthStore()

const roleLabel = computed(() => {
  const map = { 1: 'Administrator', 2: 'Branch Manager', 3: 'Sales User' }
  return map[auth.user?.role] || ''
})

const recentOrders   = ref([])
const lowStockItems  = ref([])
const loadingOrders   = ref(true)
const loadingInventory = ref(true)

const statCards = ref([
  { icon: '◈', label: 'Total Orders',   value: '—', sub: 'Today', color: 'blue' },
  { icon: '◻', label: 'Products',       value: '—', sub: 'Active', color: 'purple' },
  { icon: '◉', label: 'Branches',       value: '—', sub: 'Active', color: 'green' },
  { icon: '⚠', label: 'Low Stock',      value: '—', sub: 'Items needing attention', color: 'orange' },
])

onMounted(async () => {
  try {
    const [ordersRes, productsRes, branchesRes] = await Promise.all([
      api.get('/orders'),
      api.get('/products'),
      api.get('/branches'),
    ])

    const orders   = ordersRes   || []
    const products = productsRes || []
    const branches = branchesRes || []

    recentOrders.value = orders.slice(0, 5)
    loadingOrders.value = false

    // Load inventory for current user's branch
    const branchId = auth.userBranchId || (branches[0]?.id)
    if (branchId) {
      const invRes = await api.get(`/inventory?branch_id=${branchId}`)
      const inventory = invRes || []
      lowStockItems.value = inventory.filter(i => i.quantity <= i.reorder_level && i.quantity >= 0)
      statCards.value[3].value = lowStockItems.value.length
    }
    loadingInventory.value = false

    statCards.value[0].value = orders.length
    statCards.value[1].value = products.length
    statCards.value[2].value = branches.length
  } catch (e) {
    loadingOrders.value = false
    loadingInventory.value = false
  }
})

function statusBadge(status) {
  const map = {
    completed: 'badge badge-success',
    pending:   'badge badge-warning',
    cancelled: 'badge badge-danger',
    confirmed: 'badge badge-info',
  }
  return map[status] || 'badge badge-neutral'
}
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 28px;
}
.page-title   { font-size: 24px; font-weight: 700; letter-spacing: -0.03em; }
.page-subtitle { color: var(--clr-text-muted); font-size: 14px; margin-top: 4px; }

/* Stat cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 22px;
  background: var(--clr-bg-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--radius-lg);
  transition: all var(--trans-base);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}
.stat-card.blue   .stat-icon { background: rgba(99,102,241,0.15); color: var(--clr-accent-light); }
.stat-card.purple .stat-icon { background: rgba(168,85,247,0.15); color: #c084fc; }
.stat-card.green  .stat-icon { background: rgba(34,197,94,0.15);  color: var(--clr-success); }
.stat-card.orange .stat-icon { background: rgba(245,158,11,0.15); color: var(--clr-warning); }
.stat-value { font-size: 28px; font-weight: 700; line-height: 1; letter-spacing: -0.04em; }
.stat-label { font-size: 13px; font-weight: 500; color: var(--clr-text-secondary); margin-top: 4px; }
.stat-sub   { font-size: 11px; color: var(--clr-text-muted); margin-top: 2px; }

/* Dashboard grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 20px;
}
@media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }

.loading-state {
  padding: 32px;
  text-align: center;
  color: var(--clr-text-muted);
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.empty-state {
  text-align: center;
  color: var(--clr-text-muted);
  padding: 24px;
}

/* Low stock items */
.low-stock-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-radius: var(--radius-md);
  transition: background var(--trans-fast);
}
.low-stock-item:hover { background: var(--clr-bg-elevated); }
.lsi-name { font-size: 14px; font-weight: 500; }

.good-state {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 12px;
  color: var(--clr-success);
  font-size: 14px;
}
</style>
