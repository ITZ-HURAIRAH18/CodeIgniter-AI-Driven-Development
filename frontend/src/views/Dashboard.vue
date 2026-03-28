<template>
  <div class="space-y-section">
    <!-- Page Header -->
    <div class="mb-section">
      <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
      <p class="text-slate-600 text-sm mt-2">Welcome back. Here's your inventory overview.</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
      <!-- Total Inventory Value -->
      <Card class="border-l-4 border-l-rose-700">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-slate-600 text-sm font-medium">Total Inventory Value</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">${{ dashboardStats.totalInventoryValue.toLocaleString('en-US') }}</p>
            <p class="text-rose-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <TrendingUpIcon class="w-3 h-3" />
              Across {{ branchData.length }} branch{{ branchData.length !== 1 ? 'es' : '' }}
            </p>
          </div>
          <div class="p-3 bg-rose-50 rounded-custom">
            <DollarSignIcon class="w-6 h-6 text-rose-700" />
          </div>
        </div>
      </Card>

      <!-- Total Products -->
      <Card class="border-l-4 border-l-green-600">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-slate-600 text-sm font-medium">Total Products</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ dashboardStats.totalProducts }}</p>
            <p class="text-green-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <TrendingUpIcon class="w-3 h-3" />
              In catalog
            </p>
          </div>
          <div class="p-3 bg-green-50 rounded-custom">
            <BoxIcon class="w-6 h-6 text-green-600" />
          </div>
        </div>
      </Card>

      <!-- Low Stock Items -->
      <Card class="border-l-4 border-l-orange-600">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-slate-600 text-sm font-medium">Low Stock Items</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ dashboardStats.lowStockItems }}</p>
            <p class="text-orange-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <AlertCircleIcon class="w-3 h-3" />
              Requires attention
            </p>
          </div>
          <div class="p-3 bg-orange-50 rounded-custom">
            <AlertTriangleIcon class="w-6 h-6 text-orange-600" />
          </div>
        </div>
      </Card>

      <!-- Pending Orders -->
      <Card class="border-l-4 border-l-cyan-600">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-slate-600 text-sm font-medium">Pending Orders</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ dashboardStats.pendingOrders }}</p>
            <p class="text-cyan-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <ShoppingCartIcon class="w-3 h-3" />
              {{ dashboardStats.totalOrders }} total orders
            </p>
          </div>
          <div class="p-3 bg-cyan-50 rounded-custom">
            <ShoppingCartIcon class="w-6 h-6 text-cyan-600" />
          </div>
        </div>
      </Card>
    </div>

    <!-- Quick Stats & Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
      <!-- Inventory by Branch -->
      <Card title="Inventory by Branch" class="lg:col-span-2">
        <DataTable
          :columns="branchColumns"
          :data="branchData"
          empty-message="No branch data available"
        >
          <template #cell-status="{ value }">
            <Badge :label="value" :variant="getStatusVariant(value)" />
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button class="p-1 text-slate-600 hover:text-rose-700 transition-colors">
                <EyeIcon class="w-4 h-4" />
              </button>
              <button class="p-1 text-slate-600 hover:text-rose-700 transition-colors">
                <EditIcon class="w-4 h-4" />
              </button>
            </div>
          </template>
        </DataTable>
      </Card>

      <!-- Recent Activity -->
      <Card title="Recent Activity">
        <div class="space-y-3">
          <div v-for="(activity, idx) in recentActivities" :key="idx" class="flex gap-3 pb-3 border-b border-border-light last:border-b-0 last:pb-0">
            <div :class="['p-2 rounded-custom flex-shrink-0', getActivityColor(activity.type)]">
              <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-slate-900 text-sm font-medium truncate">{{ activity.title }}</p>
              <p class="text-slate-500 text-xs">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </Card>
    </div>

    <!-- Stock Movement Region -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
      <!-- Top Selling Products -->
      <Card title="Top Selling Products">
        <DataTable
          :columns="productColumns"
          :data="topProducts"
          empty-message="No product data available"
        >
          <template #cell-trend="{ value }">
            <span :class="[
              'text-xs font-semibold px-2 py-1 rounded-full',
              value > 0 ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50'
            ]">
              {{ value > 0 ? '+' : '' }}{{ value }}%
            </span>
          </template>
        </DataTable>
      </Card>

      <!-- Stock Levels Distribution -->
      <Card title="Stock Health">
        <div class="space-y-4">
          <!-- Optimal Stock -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-slate-900">Optimal Levels</span>
              <span class="text-sm font-bold text-slate-900">842</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-green-600 h-2 rounded-full" style="width: 68%"></div>
            </div>
          </div>

          <!-- Low Stock -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-slate-900">Low Stock</span>
              <span class="text-sm font-bold text-slate-900">187</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-orange-600 h-2 rounded-full" style="width: 15%"></div>
            </div>
          </div>

          <!-- Critical Stock -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-slate-900">Critical Level</span>
              <span class="text-sm font-bold text-slate-900">65</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-red-600 h-2 rounded-full" style="width: 5%"></div>
            </div>
          </div>

          <!-- Out of Stock -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-slate-900">Out of Stock</span>
              <span class="text-sm font-bold text-slate-900">23</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-slate-400 h-2 rounded-full" style="width: 2%"></div>
            </div>
          </div>
        </div>
      </Card>
    </div>

    <!-- Branches Overview -->
    <Card title="Branch Performance Overview">
      <DataTable
        :columns="branchPerformanceColumns"
        :data="branchPerformanceData"
        empty-message="No branch data available"
      >
        <template #cell-inventory_value="{ value }">
          <span class="font-medium text-slate-900">${{ value }}</span>
        </template>
        <template #cell-inventory_turnover="{ value }">
          <span :class="[
            'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold',
            value > 4 ? 'bg-green-50 text-green-700' : value > 2 ? 'bg-yellow-50 text-yellow-700' : 'bg-red-50 text-red-700'
          ]">
            {{ value.toFixed(2) }}x
          </span>
        </template>
        <template #actions="{ row }">
          <div class="flex items-center gap-2">
            <button class="px-3 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50 rounded-custom transition-colors">
              View Details
            </button>
          </div>
        </template>
      </DataTable>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
import Card from '@/components/ui/Card.vue'
import DataTable from '@/components/ui/DataTable.vue'
import Badge from '@/components/ui/Badge.vue'
import {
  TrendingUpIcon,
  DollarSignIcon,
  BoxIcon,
  AlertCircleIcon,
  AlertTriangleIcon,
  ShoppingCartIcon,
  EyeIcon,
  EditIcon,
  PlusIcon,
  CheckCircleIcon,
  ClockIcon
} from 'lucide-vue-next'

const auth = useAuthStore()

// KPI Data
const branchColumns = [
  { key: 'name', label: 'Branch', width: 'auto', bold: true },
  { key: 'inventory_count', label: 'Items', width: '100px', align: 'right' },
  { key: 'inventory_value', label: 'Value', width: '120px', align: 'right' },
  { key: 'status', label: 'Status', width: '100px' }
]

const branchData = ref([])
const loading = ref(true)
const dashboardStats = ref({
  totalOrders: 0,
  totalProducts: 0,
  lowStockItems: 0,
  pendingOrders: 0,
  totalInventoryValue: 0
})

const productColumns = [
  { key: 'name', label: 'Product', width: 'auto', bold: true },
  { key: 'units_sold', label: 'Units', width: '80px', align: 'right' },
  { key: 'revenue', label: 'Revenue', width: '100px', align: 'right' },
  { key: 'trend', label: 'Trend', width: '80px', align: 'right' }
]

const topProducts = ref([])

const branchPerformanceColumns = [
  { key: 'name', label: 'Branch', width: 'auto', bold: true },
  { key: 'inventory_value', label: 'Inventory Value', width: '150px', align: 'right' },
  { key: 'inventory_turnover', label: 'Turnover Rate', width: '120px', align: 'right' },
  { key: 'avg_days_stock', label: 'Avg Days', width: '100px', align: 'right' }
]

const branchPerformanceData = ref([])

const recentActivities = ref([
  { type: 'created', title: 'New product added', time: '2 hours ago' },
  { type: 'updated', title: 'Inventory transferred', time: '4 hours ago' },
  { type: 'alert', title: '5 items low stock', time: '6 hours ago' },
  { type: 'completed', title: 'Order #12345 completed', time: '1 day ago' },
  { type: 'updated', title: 'Branch sync completed', time: '1 day ago' }
])

const getStatusVariant = (status) => {
  const map = {
    'Optimal': 'success',
    'Low Stock': 'warning',
    'Critical': 'error'
  }
  return map[status] || 'default'
}

const getActivityColor = (type) => {
  const colors = {
    'created': 'bg-green-600',
    'updated': 'bg-blue-600',
    'alert': 'bg-orange-600',
    'completed': 'bg-green-600',
    'error': 'bg-red-600'
  }
  return colors[type] || 'bg-slate-600'
}

const getActivityIcon = (type) => {
  const icons = {
    'created': PlusIcon,
    'updated': ClockIcon,
    'alert': AlertCircleIcon,
    'completed': CheckCircleIcon,
    'error': AlertCircleIcon
  }
  return icons[type] || AlertCircleIcon
}

// Load dashboard data based on user role
const loadDashboardData = async () => {
  try {
    loading.value = true
    console.log('📊 Loading dashboard data for', auth.isBranchManager ? 'manager' : 'admin')
    
    // Load branches - same endpoint for both, backend filters by role
    const branchRes = await api.get('/branches')
    const branches = Array.isArray(branchRes) ? branchRes : (branchRes.data || [])
    console.log('📍 Loaded branches:', branches.length)
    
    // Load inventory data - if manager, gets their branches' inventory only
    const inventoryRes = await api.get('/inventory')
    const inventoryData = Array.isArray(inventoryRes) ? inventoryRes : (inventoryRes.data || [])
    console.log('📦 Loaded inventory items:', inventoryData.length)
    
    // Load orders
    const ordersRes = await api.get('/orders')
    const orderData = Array.isArray(ordersRes) ? ordersRes : (ordersRes.data || [])
    console.log('📋 Loaded orders:', orderData.length)
    
    // Process branch data
    const processedBranches = branches.map(branch => {
      const branchInventory = inventoryData.filter(inv => inv.branch_id === branch.id)
      const branchOrders = orderData.filter(ord => ord.branch_id === branch.id)
      
      const inventoryValue = branchInventory.reduce((sum, inv) => sum + ((inv.quantity || 0) * (inv.sale_price || 0)), 0)
      const lowStockCount = branchInventory.filter(inv => inv.quantity < (inv.reorder_level || 10)).length
      
      // Determine status
      let status = 'Optimal'
      if (lowStockCount > branchInventory.length * 0.5) status = 'Critical'
      else if (lowStockCount > 0) status = 'Low Stock'
      
      return {
        id: branch.id,
        name: branch.name,
        inventory_count: branchInventory.length,
        inventory_value: `$${inventoryValue.toLocaleString('en-US', { minimumFractionDigits: 0 })}`,
        status: status,
        low_stock_count: lowStockCount,
        order_count: branchOrders.length
      }
    })
    
    branchData.value = processedBranches
    
    // Calculate dashboard stats
    const totalInventoryValue = processedBranches.reduce((sum, b) => {
      const val = parseInt(b.inventory_value.replace(/[$,]/g, ''))
      return sum + val
    }, 0)
    
    const totalLowStock = processedBranches.reduce((sum, b) => sum + b.low_stock_count, 0)
    const totalOrders = processedBranches.reduce((sum, b) => sum + b.order_count, 0)
    
    dashboardStats.value = {
      totalOrders: totalOrders,
      totalProducts: inventoryData.length,
      lowStockItems: totalLowStock,
      pendingOrders: orderData.filter(o => o.status !== 'completed').length,
      totalInventoryValue: totalInventoryValue
    }
    
    // Load top products for managed branches
    if (auth.isBranchManager) {
      topProducts.value = inventoryData
        .sort((a, b) => (b.quantity || 0) - (a.quantity || 0))
        .slice(0, 4)
        .map(prod => ({
          name: prod.product_name || 'Unknown Product',
          units_sold: prod.quantity || 0,
          revenue: `$${((prod.quantity || 0) * (prod.sale_price || 0)).toLocaleString('en-US')}`,
          trend: Math.random() * 30 - 15
        }))
    }
    
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>
