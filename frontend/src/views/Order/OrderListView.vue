<template>
  <div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
      <div class="flex items-center gap-2 text-sm text-slate-500 font-medium mb-2">
        <span>{{ t('common.dashboard') }}</span>
        <span class="text-slate-300">/</span>
        <span class="text-slate-900 font-semibold">{{ t('common.orders') }}</span>
      </div>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ t('orders.title') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ t('orders.subtitle') }}</p>
        </div>
        <router-link to="/orders/create" class="inline-flex items-center gap-2 px-4 py-2.5 h-10 bg-accent-pink-500 text-white rounded-lg font-medium hover:bg-white hover:text-accent-pink-500 transition-colors shadow-sm">
          <PlusIcon class="w-4 h-4" />
          <span>{{ t('orders.newOrder') }}</span>
        </router-link>
      </div>
    </div>

    <!-- Compact Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-lg p-4">
      <div class="flex flex-col md:flex-row gap-3 items-end md:items-center">
        <!-- Search -->
        <div class="flex-1">
          <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2 block">{{ t('common.search') }}</label>
          <div class="relative group">
            <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-accent-pink-500 transition-colors" />
            <input 
              v-model="search" 
              type="text"
              :placeholder="t('orders.searchOrders')" 
              class="w-full pl-9 pr-4 py-2 h-10 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500/20"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div class="w-full md:w-48">
          <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2 block">{{ t('common.status') }}</label>
          <select 
            v-model="statusFilter"
            class="w-full px-3 py-2 h-10 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-accent-pink-500 focus:ring-1 focus:ring-accent-pink-500/20 cursor-pointer appearance-none"
            style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%236b7280%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.5rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2rem'"
          >
            <option value="">{{ t('orders.allStatus') }}</option>
            <option value="pending">{{ t('status.pending') }}</option>
            <option value="completed">{{ t('status.completed') }}</option>
            <option value="cancelled">{{ t('status.cancelled') }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="flex flex-col items-center gap-3">
          <div class="w-8 h-8 border-2 border-slate-200 border-t-accent-pink-600 rounded-full animate-spin"></div>
          <p class="text-slate-500 text-sm">{{ t('orders.loadingOrders') }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredOrders.length === 0" class="flex items-center justify-center py-16 px-4">
        <div class="text-center">
          <ShoppingCartIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <p class="text-slate-600 font-medium text-sm">{{ t('orders.noOrders') }}</p>
          <p class="text-slate-500 text-xs mt-1">{{ t('orders.createFirstOrder') }}</p>
          <router-link to="/orders/create" class="inline-flex items-center gap-1 mt-4 px-3 py-2 text-sm font-medium text-accent-pink-600 hover:bg-accent-pink-50 rounded-md transition-colors">
            <PlusIcon class="w-4 h-4" />
            {{ t('orders.createOrder') }}
          </router-link>
        </div>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50/80 border-b border-slate-200 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ t('orders.order') }}</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ t('common.branch') }}</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ t('orders.createdBy') }}</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ t('common.total') }}</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ t('common.status') }}</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ t('common.date') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr 
              v-for="(order, idx) in filteredOrders" 
              :key="order.id"
              :class="['transition-colors hover:bg-slate-50/80 cursor-pointer', idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/30']"
              @click="viewOrder(order)"
            >
              <!-- Order Number -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span class="font-semibold text-slate-900 text-sm">#{{ order.order_number }}</span>
              </td>

              <!-- Branch -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span class="text-slate-600 text-sm">{{ order.branch_name }}</span>
              </td>

              <!-- Created By -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span class="text-slate-600 text-sm">{{ order.created_by_name }}</span>
              </td>

              <!-- Total -->
              <td class="px-6 py-3 whitespace-nowrap text-right">
                <span class="font-semibold text-slate-900 text-sm tabular-nums">${{ Number(order.grand_total).toFixed(2) }}</span>
              </td>

              <!-- Status -->
              <td class="px-6 py-3 whitespace-nowrap">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-medium', getStatusBadgeClasses(order.status)]">
                  <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotColor(order.status)"></span>
                  {{ formatStatus(order.status) }}
                </span>
              </td>

              <!-- Date -->
              <td class="px-6 py-3 whitespace-nowrap text-slate-600 text-sm">
                {{ formatDate(order.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { SearchIcon, PlusIcon, ShoppingCartIcon } from 'lucide-vue-next'
import { useAuthStore } from '@/store/auth.store'
import { useI18n } from '@/composables/useI18n'
import api from '@/api/axios'

const router = useRouter()
const auth = useAuthStore()
const { t, language } = useI18n()
const orders = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')

const filteredOrders = computed(() => {
  let data = orders.value
  if (statusFilter.value) data = data.filter(o => o.status === statusFilter.value)
  if (search.value) {
    const q = search.value.toLowerCase()
    data = data.filter(o => 
      o.order_number?.toLowerCase().includes(q) || 
      o.branch_name?.toLowerCase().includes(q)
    )
  }
  return data
})

const getStatusBadgeClasses = (status) => {
  const map = {
    completed: 'bg-accent-pink-50 text-accent-pink-700',
    pending: 'bg-amber-50 text-amber-700',
    cancelled: 'bg-slate-100 text-slate-700',
    confirmed: 'bg-blue-50 text-blue-700'
  }
  return map[status] || 'bg-slate-100 text-slate-700'
}

const getStatusDotColor = (status) => {
  const map = {
    completed: 'bg-accent-pink-500',
    pending: 'bg-amber-500',
    cancelled: 'bg-slate-400',
    confirmed: 'bg-blue-500'
  }
  return map[status] || 'bg-slate-400'
}

const formatStatus = (status) => {
  const map = { 
    completed: t('status.completed'), 
    pending: t('status.pending'), 
    cancelled: t('status.cancelled'), 
    confirmed: t('status.confirmed') 
  }
  return map[status] || status
}

const formatDate = (dt) => {
  return dt ? new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'
}

const viewOrder = (order) => {
  // TODO: Implement order detail view
  console.log('View order:', order)
}

const loadOrders = async () => {
  try {
    let url = '/orders'
    
    // Apply role-based filtering
    if (auth.isSalesUser) {
      // Sales User: only see their own orders
      url = `/orders?created_by_id=${auth.userId}&lang=${language.value}`
    } else {
      url = `/orders?lang=${language.value}`
    }
    // Branch Manager: backend will automatically filter by their managed branches
    // Admin: sees all orders (no filter needed)
    
    const res = await api.get(url)
    orders.value = res || []
  } catch (err) {
    console.error('Failed to load orders:', err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadOrders()
})

watch(language, loadOrders)
</script>
