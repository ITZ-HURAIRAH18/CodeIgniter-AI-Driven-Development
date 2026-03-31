<template>
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

      <!-- Right: Language Switcher, Notifications, User Menu -->
      <div class="flex items-center gap-3">
        <!-- Language Switcher -->
        <LanguageSwitcher />
        
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
              <span class="text-white text-xs font-bold">JD</span>
            </div>
            <span class="text-sm font-medium text-slate-900">John Doe</span>
            <ChevronDownIcon :class="['w-4 h-4 text-slate-500']" />
          </button>

          <!-- Dropdown Menu -->
          <div
            v-if="userMenuOpen"
            @click.outside="userMenuOpen = false"
            class="absolute right-0 mt-2 w-48 bg-white rounded-custom border border-border-default shadow-soft-lg z-50"
          >
            <button
              @click.stop="$emit('profile')"
              class="w-full text-left px-4 py-2 text-sm text-slate-900 hover:bg-rose-50 transition-colors duration-150 first:rounded-t-custom"
            >
              View Profile
            </button>
            <button
              @click.stop="$emit('settings')"
              class="w-full text-left px-4 py-2 text-sm text-slate-900 hover:bg-rose-50 transition-colors duration-150"
            >
              Settings
            </button>
            <button
              @click.stop="$emit('logout')"
              class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150 last:rounded-b-custom"
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
import { ref } from 'vue'
import { BellIcon, ChevronDownIcon } from 'lucide-vue-next'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

const userMenuOpen = ref(false)
const notificationsOpen = ref(false)
const notificationCount = ref(3)

defineProps({
  userName: {
    type: String,
    default: 'John Doe'
  },
  userInitials: {
    type: String,
    default: 'JD'
  }
})

defineEmits(['profile', 'settings', 'logout'])
</script>
