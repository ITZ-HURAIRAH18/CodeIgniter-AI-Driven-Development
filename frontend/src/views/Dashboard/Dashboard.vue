<template>
  <div class="min-h-screen bg-slate-50 font-inter">
    <!-- Sales User Welcome Screen -->
    <div v-if="auth.isSalesUser" class="min-h-screen flex items-center justify-center px-6 py-12">
      <div class="max-w-4xl w-full">
        <!-- Welcome Section -->
        <div class="mb-12">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-lg bg-accent-pink-100 flex items-center justify-center">
              <ShoppingCartIcon class="w-6 h-6 text-accent-pink-600" />
            </div>
            <div>
              <h1 class="text-3xl font-bold text-slate-900">Welcome, {{ auth.user?.name || 'Sales Representative' }}</h1>
              <p class="text-slate-600 text-sm mt-1">Manage your sales and track inventory</p>
            </div>
          </div>
        </div>

        <!-- Main Actions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- View Products Card -->
          <router-link to="/products" class="group relative rounded-xl bg-white border-2 border-slate-300 shadow-sm hover:shadow-lg hover:border-accent-pink-500 transition-all duration-200 overflow-hidden">
            <div class="p-8">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent-pink-50 flex items-center justify-center group-hover:bg-accent-pink-100 transition-colors">
                  <BoxIcon class="w-6 h-6 text-accent-pink-600" />
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-slate-900 text-lg">Browse Products</h3>
                  <p class="text-slate-600 text-sm mt-1">Check available inventory and stock levels</p>
                </div>
              </div>
              <div class="mt-6 inline-flex items-center gap-2 text-accent-pink-600 font-medium text-sm group-hover:gap-3 transition-all">
                View Catalog <span>→</span>
              </div>
            </div>
          </router-link>
          
          <!-- Create Order Card -->
          <router-link to="/orders/create" class="group relative rounded-xl bg-white border-2 border-slate-300 shadow-sm hover:shadow-lg hover:border-accent-pink-500 transition-all duration-200 overflow-hidden">
            <div class="p-8">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent-pink-50 flex items-center justify-center group-hover:bg-accent-pink-100 transition-colors">
                  <ShoppingCartIcon class="w-6 h-6 text-accent-pink-600" />
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-slate-900 text-lg">Create Order</h3>
                  <p class="text-slate-600 text-sm mt-1">Start a new order and process sales</p>
                </div>
              </div>
              <div class="mt-6 inline-flex items-center gap-2 text-accent-pink-600 font-medium text-sm group-hover:gap-3 transition-all">
                New Order <span>→</span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <!-- View All Orders -->
          <router-link to="/orders" class="group p-6 rounded-lg bg-white border-2 border-slate-300 hover:border-accent-pink-500 hover:bg-accent-pink-50 transition-all duration-200 text-center">
            <CheckCircleIcon class="w-6 h-6 text-accent-pink-600 mx-auto mb-2" />
            <p class="font-semibold text-slate-900 text-sm">View Orders</p>
            <p class="text-slate-500 text-xs mt-1">All your orders</p>
          </router-link>
          
          <!-- Products -->
          <router-link to="/products" class="group p-6 rounded-lg bg-white border-2 border-slate-300 hover:border-accent-pink-500 hover:bg-accent-pink-50 transition-all duration-200 text-center">
            <TrendingUpIcon class="w-6 h-6 text-accent-pink-600 mx-auto mb-2" />
            <p class="font-semibold text-slate-900 text-sm">Products</p>
            <p class="text-slate-500 text-xs mt-1">All items</p>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Admin/Manager Dashboard -->
    <div v-else class="max-w-7xl mx-auto px-6 py-6">
      <!-- Page Header -->
      <div class="mb-8 pt-2">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Monitor your inventory across all branches in real-time.</p>
      </div>

      <!-- KPI Cards - Unified Pink & Slate Design -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Total Inventory Value -->
      <div class="group relative overflow-hidden rounded-lg backdrop-blur-md bg-white border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 p-4">
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-600 text-xs font-semibold uppercase tracking-wide">Total Inventory Value</p>
            <p class="text-2xl font-bold text-slate-950 mt-2">${{ dashboardStats.totalInventoryValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
            <p class="text-pink-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <TrendingUpIcon class="w-3.5 h-3.5" />
              </span>
              Across {{ branchData.length }} branch{{ branchData.length !== 1 ? 'es' : '' }}
            </p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0 group-hover:bg-pink-200 transition-colors">
            <DollarSignIcon class="w-6 h-6 text-pink-600" />
          </div>
        </div>
      </div>

      <!-- Total Products -->
      <div class="group relative overflow-hidden rounded-lg backdrop-blur-md bg-white border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 p-4">
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-600 text-xs font-semibold uppercase tracking-wide">Total Products</p>
            <p class="text-2xl font-bold text-slate-950 mt-2">{{ dashboardStats.totalProducts }}</p>
            <p class="text-pink-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <TrendingUpIcon class="w-3.5 h-3.5" />
              </span>
              In catalog
            </p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0 group-hover:bg-pink-200 transition-colors">
            <BoxIcon class="w-6 h-6 text-pink-600" />
          </div>
        </div>
      </div>

      <!-- Low Stock Items -->
      <div class="group relative overflow-hidden rounded-lg backdrop-blur-md bg-white border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 p-4">
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-600 text-xs font-semibold uppercase tracking-wide">Low Stock Items</p>
            <p class="text-2xl font-bold text-slate-950 mt-2">{{ dashboardStats.lowStockItems }}</p>
            <p class="text-rose-700 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <AlertCircleIcon class="w-3.5 h-3.5" />
              </span>
              Requires attention
            </p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-rose-50 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-100 transition-colors">
            <AlertTriangleIcon class="w-6 h-6 text-rose-700" />
          </div>
        </div>
      </div>

      <!-- Pending Orders -->
      <div class="group relative overflow-hidden rounded-lg backdrop-blur-md bg-white border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 p-4">
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-600 text-xs font-semibold uppercase tracking-wide">Pending Orders</p>
            <p class="text-2xl font-bold text-slate-950 mt-2">{{ dashboardStats.pendingOrders }}</p>
            <p class="text-pink-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <ShoppingCartIcon class="w-3.5 h-3.5" />
              </span>
              {{ dashboardStats.totalOrders }} total orders
            </p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0 group-hover:bg-pink-200 transition-colors">
            <ShoppingCartIcon class="w-6 h-6 text-pink-600" />
          </div>
        </div>
      </div>
      </div>

      <!-- Main Content Grid: Inventory (2/3) + Recent Activity (1/3) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Inventory by Branch -->
        <div class="lg:col-span-2">
          <div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200">
              <h2 class="text-lg font-bold text-slate-900">Inventory by Branch</h2>
              <p class="text-slate-500 text-xs mt-0.5">Current stock levels and values</p>
            </div>
          
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Branch</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Items</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Value</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="branch in branchData" :key="branch.id" class="hover:bg-slate-50 transition-colors duration-200">
                  <td class="px-6 py-3 font-semibold text-slate-900">{{ branch.name }}</td>
                  <td class="px-6 py-3 text-slate-600 text-right font-medium">{{ branch.inventory_count }}</td>
                  <td class="px-6 py-3 text-slate-900 text-right font-bold">{{ branch.inventory_value }}</td>
                  <td class="px-6 py-3 text-center">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-1 rounded text-xs font-medium transition-colors',
                      branch.status === 'Optimal' ? 'bg-slate-100 text-slate-700' :
                      branch.status === 'Low Stock' ? 'bg-rose-100 text-rose-700' :
                      'bg-slate-200 text-slate-800'
                    ]">
                      <span class="w-1.5 h-1.5 rounded-full mr-2" :class="[
                        branch.status === 'Optimal' ? 'bg-slate-600' :
                        branch.status === 'Low Stock' ? 'bg-rose-600' :
                        'bg-slate-700'
                      ]"></span>
                      {{ branch.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Recent Activity Timeline -->
      <div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200">
          <h2 class="text-lg font-bold text-slate-900">Recent Activity</h2>
          <p class="text-slate-500 text-xs mt-0.5">Latest updates</p>
        </div>
        
        <!-- Timeline -->
        <div class="p-4 space-y-4">
          <div v-if="recentActivities.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
            <ClockIcon class="w-8 h-8 text-slate-300 mb-2" />
            <p class="text-slate-600 text-sm font-medium">No recent activity</p>
          </div>
          
          <div v-for="(activity, idx) in recentActivities" :key="idx" class="flex gap-4 relative">
            <!-- Timeline line -->
            <div v-if="idx !== recentActivities.length - 1" class="absolute left-5.5 top-12 bottom-0 w-0.5 bg-gradient-to-b from-slate-300 to-transparent"></div>
            
            <!-- Icon -->
            <div :class="[
              'flex-shrink-0 w-10 h-10 rounded flex items-center justify-center relative z-10 transition-all',
              getActivityColor(activity.type)
            ]">
              <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-white" />
            </div>
            
            <!-- Content -->
            <div class="flex-1 mt-0.5">
              <p class="text-xs font-medium text-slate-900 leading-snug">{{ activity.title }}</p>
              <p class="text-xs text-slate-500 mt-1">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

      <!-- Bottom Section: Products & Stock Health -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Top Selling Products (2/3 width) -->
        <div class="lg:col-span-2 rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-slate-200 flex-shrink-0">
            <h2 class="text-lg font-bold text-slate-900">Top Selling Products</h2>
            <p class="text-slate-500 text-xs mt-0.5">By inventory value, grouped by branch • Trend: This week vs Last week</p>
          </div>
          
          <!-- Empty State -->
          <div v-if="topProducts.length === 0" class="flex flex-col items-center justify-center py-12 px-6 flex-grow">
            <BoxIcon class="w-10 h-10 text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">No top sales</p>
            <p class="text-slate-500 text-sm mt-1">Products with inventory value will appear here</p>
          </div>
        
          <!-- Scrollable Table -->
          <div v-else class="overflow-y-auto flex-grow" style="max-height: 320px">
            <table class="w-full text-sm">
              <thead class="sticky top-0 bg-slate-50">
                <tr class="border-b border-slate-200">
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Branch</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Units</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Revenue</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Trend</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="product in topProducts" :key="`${product.id}-${product.branch_id}`" class="hover:bg-slate-50 transition-colors duration-150">
                  <td class="px-6 py-3 font-semibold text-slate-900">{{ product.name }}</td>
                  <td class="px-6 py-3 text-slate-600 font-medium">
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded text-xs font-medium">{{ product.branch_name }}</span>
                  </td>
                  <td class="px-6 py-3 text-slate-600 text-right font-medium">{{ product.units_sold }}</td>
                  <td class="px-6 py-3 text-slate-900 text-right font-bold">{{ product.revenue }}</td>
                  <td class="px-6 py-3 text-right">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold',
                      product.trend > 0 
                        ? 'bg-slate-100 text-slate-700' 
                        : 'bg-rose-100 text-rose-700'
                    ]">
                      {{ product.trend > 0 ? '↗' : '↘' }} {{ Math.abs(product.trend) }}%
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Stock Health Ring Chart (1/3 width) -->
        <div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden p-4">
        <!-- Stock Health Header -->
          <div class="mb-4">
            <h2 class="text-base font-bold text-slate-900">Stock Health</h2>
            <p class="text-slate-500 text-xs mt-0.5">Distribution overview</p>
          </div>
          
          <!-- Ring Chart Using SVG - Readable Size -->
          <div class="flex items-center justify-center mb-4">
            <svg class="w-36 h-36" viewBox="0 0 200 200">
              <!-- Background circle -->
              <circle cx="100" cy="100" r="90" fill="none" stroke="#e2e8f0" stroke-width="20"></circle>
              
              <!-- Optimal (Slate) - starts at 0 degrees -->
              <circle cx="100" cy="100" r="90" fill="none" stroke="#94a3b8" stroke-width="20"
                stroke-dasharray="254.47 345.58" stroke-dashoffset="0"
                transform="rotate(-90 100 100)"
                stroke-linecap="round"></circle>
              <!-- Low (Rose) - starts after optimal -->
              <circle cx="100" cy="100" r="90" fill="none" stroke="#e11d48" stroke-width="20"
                stroke-dasharray="69.12 345.58" stroke-dashoffset="-254.47"
                transform="rotate(-90 100 100)"
                stroke-linecap="round"></circle>
              <!-- Critical (Rose Dark) -->
              <circle cx="100" cy="100" r="90" fill="none" stroke="#be123c" stroke-width="20"
                stroke-dasharray="17.28 345.58" stroke-dashoffset="-323.59"
                transform="rotate(-90 100 100)"
                stroke-linecap="round"></circle>
              <!-- Out of Stock (Slate Dark) -->
              <circle cx="100" cy="100" r="90" fill="none" stroke="#64748b" stroke-width="20"
                stroke-dasharray="4.32 345.58" stroke-dashoffset="-340.87"
                transform="rotate(-90 100 100)"
                stroke-linecap="round"></circle>
              
              <!-- Center circle -->
              <circle cx="100" cy="100" r="50" fill="white" stroke="none"></circle>
              
              <!-- Center text - Larger for readability -->
              <text x="100" y="98" text-anchor="middle" font-size="24" font-weight="bold" fill="#1e293b">{{ (stockHealth.optimal + stockHealth.low) }}</text>
              <text x="100" y="118" text-anchor="middle" font-size="12" fill="#64748b">Healthy</text>
            </svg>
          </div>
          
          <!-- Compact Legend -->
          <div class="space-y-1.5 text-xs">
            <div class="flex items-center justify-between p-1.5 rounded bg-slate-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                <span class="text-slate-700 font-medium">Optimal</span>
              </div>
              <span class="font-bold text-slate-900">{{ stockHealth.optimal }}</span>
            </div>
            <div class="flex items-center justify-between p-1.5 rounded bg-slate-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-rose-600"></div>
                <span class="text-slate-700 font-medium">Low</span>
              </div>
              <span class="font-bold text-slate-900">{{ stockHealth.low }}</span>
            </div>
            <div class="flex items-center justify-between p-1.5 rounded bg-slate-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-rose-800"></div>
                <span class="text-slate-700 font-medium">Critical</span>
              </div>
              <span class="font-bold text-slate-900">{{ stockHealth.critical }}</span>
            </div>
            <div class="flex items-center justify-between p-1.5 rounded bg-slate-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-slate-600"></div>
                <span class="text-slate-700 font-medium">OOS</span>
              </div>
              <span class="font-bold text-slate-900">{{ stockHealth.outOfStock }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
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
  ClockIcon,
  TruckIcon,
  ArrowRightIcon,
  AlertOctagonIcon
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

const stockHealth = ref({
  optimal: 0,
  optimalPercent: 0,
  low: 0,
  lowPercent: 0,
  critical: 0,
  criticalPercent: 0,
  outOfStock: 0,
  outOfStockPercent: 0
})

const productColumns = [
  { key: 'name', label: 'Product', width: 'auto', bold: true },
  { key: 'units_sold', label: 'Units', width: '80px', align: 'right' },
  { key: 'revenue', label: 'Revenue', width: '100px', align: 'right' },
  { key: 'trend', label: 'Trend', width: '80px', align: 'right' }
]

const topProducts = ref([])

const recentActivities = ref([
  { type: 'created', title: 'Loading activities...', time: 'Just now' }
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
    'created': 'bg-pink-600 shadow-sm',
    'updated': 'bg-slate-600 shadow-sm',
    'alert': 'bg-rose-600 shadow-sm',
    'completed': 'bg-pink-600 shadow-sm',
    'transfer': 'bg-slate-700 shadow-sm',
    'error': 'bg-rose-700 shadow-sm'
  }
  return colors[type] || colors['updated']
}

const getActivityIcon = (type) => {
  const icons = {
    'created': PlusIcon,
    'updated': BoxIcon,
    'alert': AlertTriangleIcon,
    'completed': CheckCircleIcon,
    'transfer': TruckIcon,
    'error': AlertOctagonIcon
  }
  return icons[type] || AlertCircleIcon
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  
  if (seconds < 60) return 'Just now'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  return `${days}d ago`
}

// Load dashboard data based on user role
const loadDashboardData = async () => {
  // Skip loading for sales representatives
  if (auth.isSalesUser) {
    loading.value = false
    return
  }

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
    console.log('🔍 First inventory item:', inventoryData[0]) // Debug: Check if reorder_level exists
    
    // Load orders
    const ordersRes = await api.get('/orders')
    const orderData = Array.isArray(ordersRes) ? ordersRes : (ordersRes.data || [])
    console.log('📋 Loaded orders:', orderData.length)
    
    // Load activity logs
    const activityRes = await api.get('/inventory/logs')
    const activityLogs = Array.isArray(activityRes) ? activityRes : (activityRes.data || [])
    console.log('📝 Loaded activity logs:', activityLogs.length)

    // Process branch data with correct inventory value calculation
    const processedBranches = branches.map(branch => {
      const branchInventory = inventoryData.filter(inv => inv.branch_id === branch.id)
      const branchOrders = orderData.filter(ord => ord.branch_id === branch.id)
      
      // Calculate inventory value: sum of (quantity * sale_price)
      const inventoryValue = branchInventory.reduce((total, inv) => {
        const salePrice = parseFloat(inv.sale_price || 0)
        const quantity = parseInt(inv.quantity || 0)
        return total + (salePrice * quantity)
      }, 0)
      
      const lowStockCount = branchInventory.filter(inv => inv.quantity < (inv.reorder_level || 10)).length
      
      // Determine status
      let status = 'Optimal'
      if (lowStockCount > branchInventory.length * 0.5) status = 'Critical'
      else if (lowStockCount > 0) status = 'Low Stock'
      
      return {
        id: branch.id,
        name: branch.name,
        inventory_count: branchInventory.length,
        inventory_value: `$${inventoryValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
        status: status,
        low_stock_count: lowStockCount,
        order_count: branchOrders.length,
        _value: inventoryValue // Store numeric value for calculations
      }
    })
    
    branchData.value = processedBranches
    
    // Calculate dashboard stats with CORRECT inventory value
    const totalLowStock = inventoryData.filter(inv => {
      const quantity = parseInt(inv.quantity || 0)
      const reorderLevel = parseInt(inv.reorder_level || 10)
      const isLow = quantity <= reorderLevel
      console.log(`📊 ${inv.product_name || 'Product'} (ID: ${inv.product_id}): Qty=${quantity}, Reorder=${reorderLevel}, IsLow=${isLow}`)
      return isLow
    }).length
    console.log(`✅ Total Low Stock Count: ${totalLowStock} out of ${inventoryData.length} items`)
    const totalOrders = processedBranches.reduce((sum, b) => sum + b.order_count, 0)
    const totalInventoryValue = processedBranches.reduce((sum, b) => sum + b._value, 0)
    
    dashboardStats.value = {
      totalOrders: totalOrders,
      totalProducts: inventoryData.length,
      lowStockItems: totalLowStock,
      pendingOrders: orderData.filter(o => o.status !== 'completed').length,
      totalInventoryValue: totalInventoryValue
    }
    
    // Calculate stock health metrics
    const optimalCount = inventoryData.filter(i => i.quantity > (i.reorder_level || 10) * 1.5).length
    const lowCount = inventoryData.filter(i => i.quantity <= (i.reorder_level || 10) && i.quantity > 0).length
    const criticalCount = inventoryData.filter(i => i.quantity <= 5 && i.quantity > 0).length
    const outOfStockCount = inventoryData.filter(i => i.quantity === 0 || i.quantity < 0).length
    const total = inventoryData.length || 1
    
    stockHealth.value = {
      optimal: optimalCount,
      optimalPercent: Math.round((optimalCount / total) * 100),
      low: lowCount,
      lowPercent: Math.round((lowCount / total) * 100),
      critical: criticalCount,
      criticalPercent: Math.round((criticalCount / total) * 100),
      outOfStock: outOfStockCount,
      outOfStockPercent: Math.round((outOfStockCount / total) * 100)
    }
    
    // Load top products (highest inventory value) with branch info and real trend
    const topProds = inventoryData
      .map(prod => {
        const salePrice = parseFloat(prod.sale_price || 0)
        const quantity = parseInt(prod.quantity || 0)
        const value = salePrice * quantity
        
        // Calculate real trend based on orders for this product (day-wise)
        const ordersForProduct = orderData.filter(o => o.product_id === prod.product_id && o.branch_id === prod.branch_id)
        
        // Get current period (last 7 days) and previous period orders (7 days before that)
        const now = new Date()
        now.setHours(0, 0, 0, 0) // Start of today
        
        const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        const fourteenDaysAgo = new Date(now.getTime() - 14 * 24 * 60 * 60 * 1000)
        
        const currentWeekOrders = ordersForProduct.filter(o => {
          const orderDate = new Date(o.created_at)
          orderDate.setHours(0, 0, 0, 0)
          return orderDate >= sevenDaysAgo
        }).reduce((sum, o) => sum + (o.quantity || 0), 0)
        
        const previousWeekOrders = ordersForProduct.filter(o => {
          const orderDate = new Date(o.created_at)
          orderDate.setHours(0, 0, 0, 0)
          return orderDate >= fourteenDaysAgo && orderDate < sevenDaysAgo
        }).reduce((sum, o) => sum + (o.quantity || 0), 0)
        
        // Calculate trend percentage (weekly comparison)
        let trend = 0
        if (previousWeekOrders > 0) {
          trend = Math.round(((currentWeekOrders - previousWeekOrders) / previousWeekOrders) * 100)
        } else if (currentWeekOrders > 0) {
          trend = 100 // New sales this week
        }
        
        const branchInfo = branches.find(b => b.id === prod.branch_id)
        
        return {
          ...prod,
          id: prod.id,
          name: (prod.product_name && prod.product_name !== 'Unknown Product') ? prod.product_name : `Product #${prod.product_id}`,
          branch_id: prod.branch_id,
          branch_name: branchInfo?.name || 'Unknown Branch',
          units_sold: quantity,
          revenue: `$${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
          trend: trend,
          _value: value
        }
      })
      .filter(p => p._value > 0) // Only include products with actual value
      .sort((a, b) => b._value - a._value)
      .slice(0, 5)
    
    topProducts.value = topProds
    
    // Load recent activities from logs
    if (activityLogs.length > 0) {
      recentActivities.value = activityLogs
        .slice(0, 3)
        .map(log => {
          // Determine activity type based on movement_type field
          let type = 'updated'
          let title = 'Stock movement'
          
          const movementType = log.movement_type?.toLowerCase() || ''
          const productName = log.product_name || `Product #${log.product_id}`
          const qtyChange = parseInt(log.qty_change || 0)
          
          if (movementType === 'add' || movementType === 'addition') {
            type = 'created'
            title = `✓ Stock added: ${productName}`
            if (qtyChange > 0) title += ` (+${qtyChange})`
          } else if (movementType === 'adjust' || movementType === 'adjustment') {
            type = 'updated'
            title = `→ Stock adjusted: ${productName}`
            if (qtyChange !== 0) title += ` (${qtyChange > 0 ? '+' : ''}${qtyChange})`
          } else if (movementType === 'remove' || movementType === 'deduction') {
            type = 'alert'
            title = `⚠ Stock removed: ${productName}`
            if (qtyChange < 0) title += ` (${qtyChange})`
          } else if (movementType === 'transfer') {
            type = 'transfer'
            title = `↔ Stock transferred: ${productName}`
          } else if (qtyChange < 0) {
            type = 'alert'
            title = `⚠ Low stock: ${productName} (${qtyChange})`
          }
          
          // Add branch name if available
          if (log.branch_name) {
            title += ` @ ${log.branch_name}`
          }
          
          return {
            type,
            title,
            time: formatTimeAgo(log.created_at || log.updated_at)
          }
        })
    }
    
  } catch (error) {
    console.error('Error loading dashboard data:', error)
    recentActivities.value = [
      { type: 'error', title: 'Failed to load activity', time: 'Now' }
    ]
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>
