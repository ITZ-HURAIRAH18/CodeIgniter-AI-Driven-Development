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
            <p class="text-3xl font-bold text-slate-900 mt-2">$248,500</p>
            <p class="text-rose-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <TrendingUpIcon class="w-3 h-3" />
              +8.2% from last month
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
            <p class="text-3xl font-bold text-slate-900 mt-2">1,247</p>
            <p class="text-green-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <TrendingUpIcon class="w-3 h-3" />
              +42 this month
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
            <p class="text-3xl font-bold text-slate-900 mt-2">23</p>
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
            <p class="text-3xl font-bold text-slate-900 mt-2">156</p>
            <p class="text-cyan-700 text-xs font-semibold mt-3 flex items-center gap-1">
              <ShoppingCartIcon class="w-3 h-3" />
              $45,230 in value
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
import { ref } from 'vue'
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
  ClockIcon,
  AlertIcon
} from 'lucide-vue-next'

// KPI Data
const branchColumns = [
  { key: 'name', label: 'Branch', width: 'auto', bold: true },
  { key: 'inventory_count', label: 'Items', width: '100px', align: 'right' },
  { key: 'inventory_value', label: 'Value', width: '120px', align: 'right' },
  { key: 'status', label: 'Status', width: '100px' }
]

const branchData = [
  { name: 'Main Branch', inventory_count: 4521, inventory_value: '$125,430', status: 'Optimal' },
  { name: 'Downtown Hub', inventory_count: 3847, inventory_value: '$98,230', status: 'Optimal' },
  { name: 'West Side', inventory_count: 2156, inventory_value: '$52,340', status: 'Low Stock' },
  { name: 'Airport Zone', inventory_count: 1823, inventory_value: '$45,200', status: 'Critical' }
]

const productColumns = [
  { key: 'name', label: 'Product', width: 'auto', bold: true },
  { key: 'units_sold', label: 'Units', width: '80px', align: 'right' },
  { key: 'revenue', label: 'Revenue', width: '100px', align: 'right' },
  { key: 'trend', label: 'Trend', width: '80px', align: 'right' }
]

const topProducts = [
  { name: 'Premium Widget A', units_sold: 425, revenue: '$12,750', trend: 12.5 },
  { name: 'Standard Item B', units_sold: 312, revenue: '$8,430', trend: 5.2 },
  { name: 'Deluxe Package C', units_sold: 287, revenue: '$14,500', trend: -2.1 },
  { name: 'Basic Option D', units_sold: 201, revenue: '$3,218', trend: 18.9 }
]

const branchPerformanceColumns = [
  { key: 'name', label: 'Branch', width: 'auto', bold: true },
  { key: 'inventory_value', label: 'Inventory Value', width: '150px', align: 'right' },
  { key: 'inventory_turnover', label: 'Turnover Rate', width: '120px', align: 'right' },
  { key: 'avg_days_stock', label: 'Avg Days', width: '100px', align: 'right' }
]

const branchPerformanceData = [
  { name: 'Main Branch', inventory_value: '125,430', inventory_turnover: 5.2, avg_days_stock: 70 },
  { name: 'Downtown Hub', inventory_value: '98,230', inventory_turnover: 4.8, avg_days_stock: 76 },
  { name: 'West Side', inventory_value: '52,340', inventory_turnover: 3.2, avg_days_stock: 114 },
  { name: 'Airport Zone', inventory_value: '45,200', inventory_turnover: 2.8, avg_days_stock: 130 }
]

const recentActivities = [
  { type: 'created', title: 'New product added', time: '2 hours ago' },
  { type: 'updated', title: 'Inventory transferred', time: '4 hours ago' },
  { type: 'alert', title: '5 items low stock', time: '6 hours ago' },
  { type: 'completed', title: 'Order #12345 completed', time: '1 day ago' },
  { type: 'updated', title: 'Branch sync completed', time: '1 day ago' }
]

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
    'alert': AlertIcon,
    'completed': CheckCircleIcon,
    'error': AlertIcon
  }
  return icons[type] || AlertIcon
}
</script>
