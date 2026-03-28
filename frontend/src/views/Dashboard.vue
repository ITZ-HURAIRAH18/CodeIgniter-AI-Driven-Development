<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-50 to-slate-100">
    <div class="max-w-7xl mx-auto px-6 py-6">
      <!-- Page Header -->
      <div class="mb-8 pt-2">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Monitor your inventory across all branches in real-time.</p>
      </div>

      <!-- KPI Cards - Glasmomorphism Style -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Total Inventory Value -->
      <div class="group relative overflow-hidden rounded-2xl backdrop-blur-xl bg-white/30 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 p-4">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Total Inventory Value</p>
            <p class="text-2xl font-bold text-slate-900 mt-2">${{ dashboardStats.totalInventoryValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
            <p class="text-rose-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <TrendingUpIcon class="w-3.5 h-3.5" />
              </span>
              Across {{ branchData.length }} branch{{ branchData.length !== 1 ? 'es' : '' }}
            </p>
          </div>
          <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-500/15 transition-colors">
            <DollarSignIcon class="w-6 h-6 text-rose-600" />
          </div>
        </div>
      </div>

      <!-- Total Products -->
      <div class="group relative overflow-hidden rounded-2xl backdrop-blur-xl bg-white/30 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 p-4">
        <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Total Products</p>
            <p class="text-2xl font-bold text-slate-900 mt-2">{{ dashboardStats.totalProducts }}</p>
            <p class="text-green-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <TrendingUpIcon class="w-3.5 h-3.5" />
              </span>
              In catalog
            </p>
          </div>
          <div class="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-green-500/15 transition-colors">
            <BoxIcon class="w-6 h-6 text-green-600" />
          </div>
        </div>
      </div>

      <!-- Low Stock Items -->
      <div class="group relative overflow-hidden rounded-2xl backdrop-blur-xl bg-white/30 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 p-4">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Low Stock Items</p>
            <p class="text-2xl font-bold text-slate-900 mt-2">{{ dashboardStats.lowStockItems }}</p>
            <p class="text-orange-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <AlertCircleIcon class="w-3.5 h-3.5" />
              </span>
              Requires attention
            </p>
          </div>
          <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-orange-500/15 transition-colors">
            <AlertTriangleIcon class="w-6 h-6 text-orange-600" />
          </div>
        </div>
      </div>

      <!-- Pending Orders -->
      <div class="group relative overflow-hidden rounded-2xl backdrop-blur-xl bg-white/30 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 p-4">
        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative flex items-start justify-between">
          <div class="flex-1">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Pending Orders</p>
            <p class="text-2xl font-bold text-slate-900 mt-2">{{ dashboardStats.pendingOrders }}</p>
            <p class="text-cyan-600 text-xs font-semibold mt-3 flex items-center gap-2">
              <span class="inline-flex items-center">
                <ShoppingCartIcon class="w-3.5 h-3.5" />
              </span>
              {{ dashboardStats.totalOrders }} total orders
            </p>
          </div>
          <div class="w-12 h-12 rounded-full bg-cyan-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-cyan-500/15 transition-colors">
            <ShoppingCartIcon class="w-6 h-6 text-cyan-600" />
          </div>
        </div>
      </div>
      </div>

      <!-- Main Content Grid: Inventory (2/3) + Recent Activity (1/3) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Inventory by Branch -->
        <div class="lg:col-span-2">
          <div class="rounded-2xl bg-white/50 backdrop-blur-xl border border-white/20 shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100">
              <h2 class="text-lg font-bold text-slate-900">Inventory by Branch</h2>
              <p class="text-slate-500 text-xs mt-0.5">Current stock levels and values</p>
            </div>
          
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200/50">
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Branch</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Items</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Value</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100/50">
                <tr v-for="branch in branchData" :key="branch.id" class="hover:bg-slate-50/50 transition-colors duration-200">
                  <td class="px-6 py-3 font-semibold text-slate-900">{{ branch.name }}</td>
                  <td class="px-6 py-3 text-slate-600 text-right font-medium">{{ branch.inventory_count }}</td>
                  <td class="px-6 py-3 text-slate-900 text-right font-bold">{{ branch.inventory_value }}</td>
                  <td class="px-6 py-3 text-center">
                    <span :class="[
                      'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold transition-colors',
                      branch.status === 'Optimal' ? 'bg-green-50 text-green-700' :
                      branch.status === 'Low Stock' ? 'bg-orange-50 text-orange-700' :
                      'bg-red-50 text-red-700'
                    ]">
                      <span class="w-2 h-2 rounded-full mr-2" :class="[
                        branch.status === 'Optimal' ? 'bg-green-500' :
                        branch.status === 'Low Stock' ? 'bg-orange-500' :
                        'bg-red-500'
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
      <div class="rounded-2xl bg-white/50 backdrop-blur-xl border border-white/20 shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100">
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
              'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center relative z-10 transition-all',
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
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Selling Products -->
        <div class="rounded-2xl bg-white/50 backdrop-blur-xl border border-white/20 shadow-lg overflow-hidden">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Top Selling Products</h2>
            <p class="text-slate-500 text-xs mt-0.5">By inventory value</p>
          </div>
          
          <!-- Empty State -->
          <div v-if="topProducts.length === 0" class="flex flex-col items-center justify-center py-12 px-6">
            <BoxIcon class="w-10 h-10 text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">No top sales</p>
            <p class="text-slate-500 text-sm mt-1">Products with inventory value will appear here</p>
          </div>
        
          <!-- Table -->
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200/50">
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Units</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Revenue</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Trend</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100/50">
                <tr v-for="product in topProducts" :key="product.id" class="hover:bg-slate-50/50 transition-colors duration-200">
                  <td class="px-6 py-3 font-semibold text-slate-900">{{ product.name }}</td>
                  <td class="px-6 py-3 text-slate-600 text-right font-medium">{{ product.units_sold }}</td>
                  <td class="px-6 py-3 text-slate-900 text-right font-bold">{{ product.revenue }}</td>
                  <td class="px-6 py-3 text-right">
                  <span :class="[
                    'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold',
                    product.trend > 0 
                      ? 'bg-green-50 text-green-700' 
                      : 'bg-red-50 text-red-700'
                  ]">
                    {{ product.trend > 0 ? '↗' : '↘' }} {{ Math.abs(product.trend) }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stock Health Ring Chart -->
      <div class="rounded-2xl bg-white/50 backdrop-blur-xl border border-white/20 shadow-lg overflow-hidden p-6">
        <!-- Header -->
        <div class="mb-6">
          <h2 class="text-lg font-bold text-slate-900">Stock Health</h2>
          <p class="text-slate-500 text-xs mt-0.5">Distribution across all items</p>
        </div>
        
        <!-- Ring Chart Using SVG -->
        <div class="flex items-center justify-center mb-6">
          <svg class="w-40 h-40" viewBox="0 0 200 200">
            <!-- Background circle -->
            <circle cx="100" cy="100" r="90" fill="none" stroke="#e2e8f0" stroke-width="20"></circle>
            
            <!-- Optimal (Green) - starts at 0 degrees -->
            <circle cx="100" cy="100" r="90" fill="none" stroke="#10b981" stroke-width="20"
              stroke-dasharray="254.47 345.58" stroke-dashoffset="0"
              transform="rotate(-90 100 100)"
              stroke-linecap="round"></circle>
            <!-- Low (Orange) - starts after optimal -->
            <circle cx="100" cy="100" r="90" fill="none" stroke="#f97316" stroke-width="20"
              stroke-dasharray="69.12 345.58" stroke-dashoffset="-254.47"
              transform="rotate(-90 100 100)"
              stroke-linecap="round"></circle>
            <!-- Critical (Red) -->
            <circle cx="100" cy="100" r="90" fill="none" stroke="#ef4444" stroke-width="20"
              stroke-dasharray="17.28 345.58" stroke-dashoffset="-323.59"
              transform="rotate(-90 100 100)"
              stroke-linecap="round"></circle>
            <!-- Out of Stock (Gray) -->
            <circle cx="100" cy="100" r="90" fill="none" stroke="#94a3b8" stroke-width="20"
              stroke-dasharray="4.32 345.58" stroke-dashoffset="-340.87"
              transform="rotate(-90 100 100)"
              stroke-linecap="round"></circle>
            
            <!-- Center circle -->
            <circle cx="100" cy="100" r="50" fill="white" stroke="none"></circle>
            
            <!-- Center text -->
            <text x="100" y="95" text-anchor="middle" font-size="20" font-weight="bold" fill="#1e293b">{{ (stockHealth.optimal + stockHealth.low) }}</text>
            <text x="100" y="115" text-anchor="middle" font-size="10" fill="#64748b">Healthy</text>
          </svg>
        </div>
        
        <!-- Legend -->
        <div class="space-y-2 text-sm">
          <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/50">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-green-500"></div>
              <span class="text-slate-700 font-medium">Optimal Level</span>
            </div>
            <span class="font-bold text-slate-900">{{ stockHealth.optimal }}</span>
          </div>
          <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/50">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-orange-500"></div>
              <span class="text-slate-700 font-medium">Low Stock</span>
            </div>
            <span class="font-bold text-slate-900">{{ stockHealth.low }}</span>
          </div>
          <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/50">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-red-500"></div>
              <span class="text-slate-700 font-medium">Critical</span>
            </div>
            <span class="font-bold text-slate-900">{{ stockHealth.critical }}</span>
          </div>
          <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/50">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-slate-400"></div>
              <span class="text-slate-700 font-medium">Out of Stock</span>
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
    'created': 'bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/20',
    'updated': 'bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/20',
    'alert': 'bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg shadow-orange-500/20',
    'completed': 'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/20',
    'transfer': 'bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/20',
    'error': 'bg-gradient-to-br from-red-500 to-red-600 shadow-lg shadow-red-500/20'
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
    const totalLowStock = processedBranches.reduce((sum, b) => sum + b.low_stock_count, 0)
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
    const outOfStockCount = inventoryData.filter(i => i.quantity === 0).length
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
    
    // Load top products (highest inventory value)
    const topProds = inventoryData
      .map(prod => {
        const salePrice = parseFloat(prod.sale_price || 0)
        const quantity = parseInt(prod.quantity || 0)
        const value = salePrice * quantity
        return {
          ...prod,
          name: (prod.product_name && prod.product_name !== 'Unknown Product') ? prod.product_name : `Product #${prod.product_id}`,
          units_sold: quantity,
          revenue: `$${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
          trend: Math.floor(Math.random() * 30) - 15, // Random between -15 to +15
          _value: value
        }
      })
      .filter(p => p._value > 0) // Only include products with actual value
      .sort((a, b) => b._value - a._value)
      .slice(0, 4)
    
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
