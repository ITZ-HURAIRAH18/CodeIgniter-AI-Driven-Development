<template>
  <div class="min-h-screen bg-bg-primary">
    <!-- Sidebar -->
    <aside class="h-screen w-64 fixed left-0 top-0 flex flex-col bg-slate-50 border-r border-border-default shadow-soft">
      <!-- Logo / Brand -->
      <div class="px-gutter py-section border-b border-border-default flex items-center gap-3">
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
      <div class="border-t border-border-default px-2 py-section space-y-2">
        <!-- <button
          @click="handleSettings"
          :class="[
            'w-full flex items-center gap-3 px-4 py-2 rounded-custom text-sm font-medium',
            'text-slate-600 hover:bg-slate-100 transition-colors duration-150'
          ]"
        >
          <Settings :class="['w-5 h-5']" />
          <span>Settings</span>
        </button> -->
        <button
          @click="handleLogout"
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

    <!-- Main Content with Topbar -->
    <div class="ml-64">
      <!-- Topbar -->
      <header class="fixed top-0 left-64 right-0 h-16 bg-white border-b border-border-default shadow-soft z-40">
        <div class="h-full px-gutter flex items-center justify-between">
          <!-- Left: Breadcrumb / Search -->
          <div class="flex-1 flex items-center gap-4">
            <input
              type="text"
              placeholder="Search..."
              :class="[
                'px-3 py-2 rounded-custom text-sm',
                'bg-slate-100 border border-slate-200',
                'text-slate-900 placeholder-slate-500',
                'focus:outline-none focus:bg-white focus:border-rose-300',
                'transition-all duration-150',
                'w-64'
              ]"
            />
          </div>

          <!-- Right: Notifications, User Menu -->
          <div class="flex items-center gap-4">
            <!-- Notifications -->
            <button
              @click="notificationsOpen = !notificationsOpen"
              class="relative p-2 text-slate-600 hover:bg-slate-100 rounded-custom transition-colors duration-150"
            >
              <BellIcon :class="['w-5 h-5']" />
              <span
                v-if="notificationCount > 0"
                class="absolute top-0 right-0 w-4 h-4 bg-rose-600 text-white text-xs rounded-full flex items-center justify-center font-bold"
              >
                {{ notificationCount }}
              </span>
            </button>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center gap-2 px-3 py-2 rounded-custom hover:bg-slate-100 transition-colors duration-150"
              >
                <div class="w-8 h-8 bg-rose-700 rounded-full flex items-center justify-center">
                  <span class="text-white text-xs font-bold">{{ initials }}</span>
                </div>
                <span class="text-sm font-medium text-slate-900">{{ username }}</span>
                <ChevronDownIcon :class="['w-4 h-4 text-slate-500']" />
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="userMenuOpen"
                @click.outside="userMenuOpen = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-custom border border-border-default shadow-soft-lg z-50"
              >
                <button
                  @click.stop="handleProfile"
                  class="w-full text-left px-4 py-2 text-sm text-slate-900 hover:bg-rose-50 transition-colors duration-150 first:rounded-t-custom"
                >
                  View Profile
                </button>
                <button
                  @click.stop="handleSettings"
                  class="w-full text-left px-4 py-2 text-sm text-slate-900 hover:bg-rose-50 transition-colors duration-150"
                >
                  Settings
                </button>
                <button
                  @click.stop="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150 last:rounded-b-custom"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <main class="mt-16 p-section">
        <router-view v-slot="{ Component }">
          <transition 
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'
import { 
  LayoutDashboard,
  Package,
  ShoppingCart,
  Truck,
  Settings,
  LogOutIcon,
  Building2,
  BellIcon,
  ChevronDownIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const userMenuOpen = ref(false)
const notificationsOpen = ref(false)
const notificationCount = ref(3)

const username = ref(auth.user?.name || 'User')
const initials = computed(() => {
  const name = username.value || ''
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const menuItems = [
  { to: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/inventory', label: 'Inventory', icon: Package },
  { to: '/products', label: 'Products', icon: ShoppingCart },
  { to: '/orders', label: 'Orders', icon: ShoppingCart, badge: '3' },
  { to: '/transfers', label: 'Stock Transfers', icon: Truck },
  { to: '/branches', label: 'Branches', icon: Building2 }
]

const isActive = (path) => route.path === path

const handleProfile = () => {
  // Navigate to profile
  router.push('/profile')
}

const handleSettings = () => {
  // Navigate to settings
  router.push('/settings')
}

const handleLogout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    // Force logout even if API call fails
    auth.logout()
    router.push('/login')
  }
}

onMounted(() => {
  // Update username from auth store
  if (auth.user?.name) {
    username.value = auth.user.name
  }
})
</script>

