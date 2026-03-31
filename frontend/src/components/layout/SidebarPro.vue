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
          {{ t('navigation.main') }}
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
            <span class="flex-1">{{ t(item.labelKey) }}</span>
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
          {{ t('navigation.management') }}
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
            <span>{{ t(item.labelKey) }}</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Bottom Section -->
    <div class="border-t border-gray-100 px-3 py-4 space-y-2">

      <button
        @click="handleLogout"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-600 hover:bg-status-error/10 hover:text-status-error transition-all duration-150"
      >
        <LogOutIcon :class="['w-5 h-5']" />
        <span>{{ t('common.logout') }}</span>
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
          <div class="text-xs text-gray-500 font-medium truncate">{{ userRoleLabel }}</div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'
import { useI18n } from '@/composables/useI18n'
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Truck,
  Building2,
  Users,
  Settings,
  LogOutIcon,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { t } = useI18n()

// ─── Role Constants ───
const ROLES = {
  SUPER_ADMIN: 1,
  BRANCH_MANAGER: 2,
  SALES_USER: 3,
}

// ─── Menu Configuration ───
const allMenuItems = {
  // Main Section
  main: [
    {
      to: '/dashboard',
      label: 'Dashboard',
      labelKey: 'navigation.dashboard',
      icon: LayoutDashboard,
      roles: [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER], // visible to all
    },
    {
      to: '/inventory',
      label: 'Inventory',
      labelKey: 'navigation.inventory',
      icon: Package,
      roles: [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER], // Admin & Manager only
    },
    {
      to: '/orders',
      label: 'Orders',
      labelKey: 'navigation.orders',
      icon: ShoppingCart,
      roles: [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER], // visible to all
    },
  ],
  // Management Section
  management: [
    {
      to: '/users',
      label: 'Users',
      labelKey: 'navigation.users',
      icon: Users,
      roles: [ROLES.SUPER_ADMIN], // Admin only
    },
    {
      to: '/products',
      label: 'Products',
      labelKey: 'navigation.products',
      icon: ShoppingCart,
      roles: [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER], // visible to all (read-only for manager & sales)
    },
    {
      to: '/transfers',
      label: 'Transfers',
      labelKey: 'navigation.transfers',
      icon: Truck,
      roles: [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER], // Admin & Manager only
    },
    {
      to: '/branches',
      label: 'Branches',
      labelKey: 'navigation.branches',
      icon: Building2,
      roles: [ROLES.SUPER_ADMIN], // Admin only
    },
  ],
}

// ─── Computed Properties ───
const userRole = computed(() => auth.user?.role)

const userName = computed(() => auth.user?.name || 'User')

const userRoleLabel = computed(() => {
  const roleMap = {
    [ROLES.SUPER_ADMIN]: 'roles.superAdmin',
    [ROLES.BRANCH_MANAGER]: 'roles.branchManager',
    [ROLES.SALES_USER]: 'roles.salesUser',
  }
  const roleKey = roleMap[userRole.value]
  return roleKey ? t(roleKey) : t('roles.member')
})

const userInitials = computed(() => 
  userName.value.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
)

// ─── Filter Menu Items by Role ───
const mainMenu = computed(() => 
  allMenuItems.main.filter(item => item.roles.includes(userRole.value))
)

const managementMenu = computed(() => 
  allMenuItems.management.filter(item => item.roles.includes(userRole.value))
)

// ─── Check if Route is Active ───
const isActive = (path) => route.path.startsWith(path)

// ─── Logout Handler ───
const handleLogout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>
