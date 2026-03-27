<template>
  <aside class="h-screen w-64 fixed left-0 top-0 flex flex-col bg-slate-50 border-r border-slate-200 shadow-soft">
    <!-- Logo / Brand -->
    <div class="px-gutter py-section border-b border-slate-200 flex items-center gap-3">
      <div class="w-10 h-10 bg-rose-700 rounded-custom flex items-center justify-center">
        <span class="text-white font-bold text-lg">I</span>
      </div>
      <div>
        <h1 class="font-bold text-slate-900">Inventory</h1>
        <p class="text-xs text-slate-500">ERP System</p>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-2 py-section space-y-1">
      <router-link
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'flex items-center gap-3 px-4 py-3 rounded-custom text-sm font-medium transition-all duration-150',
          'group relative',
          isActive(item.to)
            ? 'bg-rose-50 text-rose-700 shadow-rose-sm'
            : 'text-slate-600 hover:bg-slate-100'
        ]"
      >
        <component :is="item.icon" :class="['w-5 h-5 flex-shrink-0']" />
        <span>{{ item.label }}</span>
        <span
          v-if="item.badge"
          :class="[
            'ml-auto text-xs font-semibold px-2 py-1 rounded-full',
            'bg-rose-100 text-rose-700'
          ]"
        >
          {{ item.badge }}
        </span>
      </router-link>
    </nav>

    <!-- Bottom Section -->
    <div class="border-t border-slate-200 px-2 py-section space-y-2">
      <button
        @click="$emit('settings')"
        :class="[
          'w-full flex items-center gap-3 px-4 py-2 rounded-custom text-sm font-medium',
          'text-slate-600 hover:bg-slate-100 transition-colors duration-150'
        ]"
      >
        <Settings :class="['w-5 h-5']" />
        <span>Settings</span>
      </button>
      <button
        @click="$emit('logout')"
        :class="[
          'w-full flex items-center gap-3 px-4 py-2 rounded-custom text-sm font-medium',
          'text-slate-600 hover:bg-red-50 hover:text-red-600 transition-colors duration-150'
        ]"
      >
        <LogOutIcon :class="['w-5 h-5']" />
        <span>Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Truck,
  Settings,
  LogOutIcon,
  Building2
} from 'lucide-vue-next'

const route = useRoute()

defineProps({
  menuItems: {
    type: Array,
    default: () => [
      { to: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
      { to: '/inventory', label: 'Inventory', icon: Package },
      { to: '/products', label: 'Products', icon: ShoppingCart },
      { to: '/orders', label: 'Orders', icon: ShoppingCart, badge: '3' },
      { to: '/transfers', label: 'Stock Transfers', icon: Truck },
      { to: '/branches', label: 'Branches', icon: Building2 }
    ]
  }
})

const isActive = (path) => route.path === path

defineEmits(['settings', 'logout'])
</script>
