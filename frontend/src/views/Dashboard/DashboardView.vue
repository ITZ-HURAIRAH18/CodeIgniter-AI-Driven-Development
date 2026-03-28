<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-600 mt-1">Welcome back, {{ auth.user?.name }} — {{ roleLabel }}</p>
      </div>
      <router-link to="/orders/create">
        <BaseButton variant="primary" label="+ Create Order" />
      </router-link>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="stat in statCards"
        :key="stat.label"
        class="bg-surface-elevated border border-gray-200 rounded-xl p-6 shadow-xs hover:shadow-sm transition-shadow duration-200"
        :style="{ borderTopWidth: '3px', borderTopColor: stat.color }"
      >
        <div class="flex items-start justify-between">
          <div class="space-y-2 flex-1">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ stat.label }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ stat.value }}</p>
            <p class="text-xs text-gray-500">{{ stat.sub }}</p>
          </div>
          <div :class="['w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0']" :style="{ backgroundColor: stat.bgColor }">
            <component :is="getIconComponent(stat.icon)" :style="{ color: stat.color, width: '24px', height: '24px' }" />
          </div>
        </div>
      </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Orders (2/3 width) -->
      <div class="lg:col-span-2">
        <BaseCard title="Recent Orders" subtitle="Your latest 5 orders">
          <div v-if="loadingOrders" class="flex items-center justify-center h-48">
            <div class="text-center">
              <div class="inline-flex items-center">
                <div class="w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin"></div>
              </div>
              <p class="text-sm text-gray-500 mt-2">Loading orders...</p>
            </div>
          </div>
          <BaseTable
            v-else
            :columns="orderColumns"
            :data="recentOrders"
            noPadding
          >
            <template #cell-order_number="{ value }">
              <span class="font-mono text-sm text-gray-900">{{ value }}</span>
            </template>
            <template #cell-grand_total="{ value }">
              <span class="font-semibold text-gray-900">${{ Number(value).toFixed(2) }}</span>
            </template>
            <template #cell-status="{ value }">
              <Badge :label="value" :variant="getStatusBadgeVariant(value)" size="sm" />
            </template>
          </BaseTable>
          <template #footer>
            <div class="flex justify-end">
              <router-link to="/orders">
                <BaseButton variant="ghost" label="View all orders →" size="sm" />
              </router-link>
            </div>
          </template>
        </BaseCard>
      </div>

      <!-- Low Stock Alerts (1/3 width) -->
      <div>
        <BaseCard title="Low Stock Alerts" subtitle="Items below reorder level">
          <div v-if="loadingInventory" class="flex items-center justify-center h-48">
            <div class="inline-flex items-center">
              <div class="w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
          </div>
          <div v-else class="space-y-3">
            <template v-if="lowStockItems.length > 0">
              <div
                v-for="item in lowStockItems"
                :key="item.id"
                class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-surface-base hover:shadow-xs transition-shadow duration-200"
              >
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ item.product_name }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ item.sku }}</p>
                </div>
                <Badge :label="`${item.quantity} left`" variant="error" size="sm" />
              </div>
            </template>
            <div v-else class="flex flex-col items-center justify-center py-8">
              <div class="w-12 h-12 rounded-full bg-status-success/15 flex items-center justify-center mb-2">
                <CheckCircle2 :style="{ color: '#10b981', width: '24px', height: '24px' }" />
              </div>
              <p class="text-sm font-medium text-gray-900">All stock healthy</p>
              <p class="text-xs text-gray-500 text-center mt-1">No items below reorder level</p>
            </div>
          </div>
          <template #footer>
            <div class="flex justify-end">
              <router-link to="/inventory">
                <BaseButton variant="ghost" label="Manage inventory →" size="sm" />
              </router-link>
            </div>
          </template>
        </BaseCard>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import Badge from '@/components/ui/Badge.vue'
import { TrendingUp, Package, MapPin, AlertTriangle, CheckCircle2 } from 'lucide-vue-next'

const auth = useAuthStore()

const roleLabel = computed(() => {
  const map = { 1: 'Administrator', 2: 'Branch Manager', 3: 'Sales User' }
  return map[auth.user?.role] || ''
})

const recentOrders = ref([])
const lowStockItems = ref([])
const loadingOrders = ref(true)
const loadingInventory = ref(true)

const statCards = ref([
  { icon: 'TrendingUp', label: 'Total Orders', value: '—', sub: 'This month', color: '#3b82f6', bgColor: '#dbeafe' },
  { icon: 'Package', label: 'Products', value: '—', sub: 'In catalog', color: '#9059ae', bgColor: '#f3e8ff' },
  { icon: 'MapPin', label: 'Branches', value: '—', sub: 'Active', color: '#31a8a2', bgColor: '#d1faf5' },
  { icon: 'AlertTriangle', label: 'Low Stock', value: '—', sub: 'Attention needed', color: '#f59e0b', bgColor: '#fef3c7' },
])

const orderColumns = [
  { key: 'order_number', label: 'Order #' },
  { key: 'branch_name', label: 'Branch' },
  { key: 'grand_total', label: 'Total' },
  { key: 'status', label: 'Status' },
]

function getIconComponent(iconName) {
  const icons = {
    'TrendingUp': TrendingUp,
    'Package': Package,
    'MapPin': MapPin,
    'AlertTriangle': AlertTriangle,
  }
  return icons[iconName] || TrendingUp
}

function getStatusBadgeVariant(status) {
  const map = {
    completed: 'success',
    pending: 'warning',
    cancelled: 'error',
    confirmed: 'info',
  }
  return map[status.toLowerCase()] || 'neutral'
}

onMounted(async () => {
  try {
    const [ordersRes, productsRes, branchesRes] = await Promise.all([
      api.get('/orders'),
      api.get('/products'),
      api.get('/branches'),
    ])
    const orders = ordersRes || []
    const products = productsRes || []
    const branches = branchesRes || []

    let managerBranchIds = []
    if (auth.isBranchManager) {
      managerBranchIds = branches.filter(b => b.manager_id === auth.user?.id).map(b => b.id)
    }

    // Filter data based on role
    const filteredOrders = auth.isAdmin
      ? orders
      : auth.isBranchManager
        ? orders.filter(o => managerBranchIds.includes(o.branch_id))
        : []
    const filteredProducts = auth.isAdmin
      ? products
      : auth.isBranchManager
        ? products.filter(p => managerBranchIds.includes(p.branch_id))
        : []
    const filteredBranches = auth.isAdmin
      ? branches
      : auth.isBranchManager
        ? branches.filter(b => managerBranchIds.includes(b.id))
        : []

    recentOrders.value = filteredOrders.slice(0, 5)
    loadingOrders.value = false

    // Low stock: only for relevant branches
    let lowStock = []
    if (filteredBranches.length > 0) {
      for (const branch of filteredBranches) {
        const invRes = await api.get(`/inventory?branch_id=${branch.id}`)
        const inventory = invRes || []
        lowStock = lowStock.concat(inventory.filter(i => i.quantity <= i.reorder_level && i.quantity >= 0))
      }
    }
    lowStockItems.value = lowStock.slice(0, 5)
    loadingInventory.value = false

    statCards.value[0].value = filteredOrders.length
    statCards.value[1].value = filteredProducts.length
    statCards.value[2].value = filteredBranches.length
    statCards.value[3].value = lowStock.length
  } catch (e) {
    loadingOrders.value = false
    loadingInventory.value = false
  }
})
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
