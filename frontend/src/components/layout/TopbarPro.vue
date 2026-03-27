<template>
  <header class="fixed top-0 left-60 right-0 h-16 bg-white border-b border-gray-200 flex items-center px-6 z-40">
    <div class="flex items-center justify-between w-full gap-6">
      <!-- Left: Search -->
      <div class="flex-1 flex items-center">
        <div class="relative w-full max-w-xs">
          <input
            type="text"
            placeholder="Search..."
            class="w-full h-10 px-3.5 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
          />
          <span class="absolute right-3 top-3 text-gray-400">
            <SearchIcon class="w-4 h-4" />
          </span>
        </div>
      </div>

      <!-- Right: Notifications & User Menu -->
      <div class="flex items-center gap-4">
        <!-- Notifications -->
        <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-md transition-colors duration-150">
          <BellIcon class="w-5 h-5" />
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-brand-500 rounded-full"></span>
        </button>

        <!-- User Menu Dropdown -->
        <div class="relative">
          <button
            @click="isUserMenuOpen = !isUserMenuOpen"
            class="flex items-center gap-2.5 px-3 py-2 rounded-md hover:bg-gray-100 transition-colors duration-150"
          >
            <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center flex-shrink-0">
              <span class="text-white text-xs font-bold">{{ userInitials }}</span>
            </div>
            <div class="hidden sm:block">
              <div class="text-sm font-medium text-gray-900">{{ userName }}</div>
              <div class="text-xs text-gray-500">{{ userRole }}</div>
            </div>
            <ChevronDownIcon :class="['w-4 h-4 text-gray-500', { 'rotate-180': isUserMenuOpen }]" />
          </button>

          <!-- Dropdown Menu -->
          <div
            v-if="isUserMenuOpen"
            @click.outside="isUserMenuOpen = false"
            class="absolute right-0 mt-2 w-44 bg-white rounded-lg border border-gray-200 shadow-lg z-50 animate-fade-in"
          >
            <button
              @click.stop="isUserMenuOpen = false; $router.push('/profile')"
              class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-50 first:rounded-t-lg border-b border-gray-100"
            >
              Profile
            </button>
            <button
              @click.stop="isUserMenuOpen = false; $router.push('/settings')"
              class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-50 border-b border-gray-100"
            >
              Settings
            </button>
            <button
              @click.stop="handleLogout"
              class="w-full text-left px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 last:rounded-b-lg"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'
import { SearchIcon, BellIcon, ChevronDownIcon } from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStore()
const isUserMenuOpen = ref(false)

const userName = computed(() => auth.user?.name || 'User')
const userRole = computed(() => {
  const roles = { 1: 'Admin', 2: 'Manager', 3: 'Staff' }
  return roles[auth.user?.role] || 'Member'
})
const userInitials = computed(() =>
  userName.value.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
)

const handleLogout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>
