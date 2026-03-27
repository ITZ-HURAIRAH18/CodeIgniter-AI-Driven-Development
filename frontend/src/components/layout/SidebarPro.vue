<template>
  <aside class="fixed inset-y-0 left-0 w-60 bg-white border-r border-gray-200 flex flex-col">
    <!-- Logo Section -->
    <div class="h-16 px-6 flex items-center border-b border-gray-100 bg-surface-base">
      <div class="flex items-center gap-3 w-full">
        <div class="w-10 h-10 rounded-lg bg-accent-pink-500 flex items-center justify-center flex-shrink-0 shadow-sm">
          <span class="text-white font-bold text-lg leading-none">I</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-bold text-gray-900 truncate">InvenSync</div>
          <div class="text-xs text-accent-pink-600 font-semibold">Enterprise</div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
      <!-- Main Section -->
      <div class="mb-6">
        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
          Main
        </div>
        <div class="space-y-1">
          <router-link
            v-for="item in mainMenu"
            :key="item.to"
            :to="item.to"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all duration-150',
              isActive(item.to)
                ? 'bg-accent-pink-50 text-accent-pink-600 border-l-2 border-l-accent-pink-500'
                : 'text-gray-600 hover:bg-gray-50'
            ]"
          >
            <component :is="item.icon" :class="['w-5 h-5 flex-shrink-0']" />
            <span class="flex-1">{{ item.label }}</span>
            <span
              v-if="item.badge"
              class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-accent-pink-100 text-accent-pink-600"
            >
              {{ item.badge }}
            </span>
          </router-link>
        </div>
      </div>

      <!-- Management Section -->
      <div>
        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
          Management
        </div>
        <div class="space-y-1">
          <router-link
            v-for="item in managementMenu"
            :key="item.to"
            :to="item.to"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all duration-150',
              isActive(item.to)
                ? 'bg-accent-pink-50 text-accent-pink-600 border-l-2 border-l-accent-pink-500'
                : 'text-gray-600 hover:bg-gray-50'
            ]"
          >
            <component :is="item.icon" :class="['w-5 h-5 flex-shrink-0']" />
            <span>{{ item.label }}</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Bottom Section -->
    <div class="border-t border-gray-100 px-3 py-4 space-y-2">
      <button
        @click="$router.push('/settings')"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all duration-150"
      >
        <Settings :class="['w-5 h-5']" />
        <span>Settings</span>
      </button>
      <button
        @click="handleLogout"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-600 hover:bg-status-error/10 hover:text-status-error transition-all duration-150"
      >
        <LogOutIcon :class="['w-5 h-5']" />
        <span>Logout</span>
      </button>
    </div>

    <!-- User Profile Card -->
    <div class="border-t border-gray-100 px-3 py-3 bg-surface-base">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-accent-pink-500 flex items-center justify-center flex-shrink-0 shadow-sm">
          <span class="text-white text-xs font-bold">{{ userInitials }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-medium text-gray-900 truncate">{{ userName }}</div>
          <div class="text-xs text-gray-500 font-medium truncate">{{ userRole }}</div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Truck,
  Building2,
  Settings,
  LogOutIcon,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const mainMenu = [
  { to: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/inventory', label: 'Inventory', icon: Package },
  { to: '/orders', label: 'Orders', icon: ShoppingCart },
]

const managementMenu = [
  { to: '/products', label: 'Products', icon: ShoppingCart },
  { to: '/transfers', label: 'Transfers', icon: Truck },
  { to: '/branches', label: 'Branches', icon: Building2 },
]

const userName = computed(() => auth.user?.name || 'User')
const userRole = computed(() => {
  const roles = { 1: 'Admin', 2: 'Manager', 3: 'Staff' }
  return roles[auth.user?.role] || 'Member'
})
const userInitials = computed(() => 
  userName.value.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
)

const isActive = (path) => route.path.startsWith(path)

const handleLogout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>
