<template>
  <div class="flex h-screen bg-background text-slate-900 overflow-hidden">
    <!-- Fixed Sidebar -->
    <aside 
      class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white border-r border-slate-200 transition-transform lg:static lg:block"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo Area -->
      <div class="flex items-center gap-3 px-6 h-16 border-b border-slate-100">
        <div class="w-8 h-8 rounded-lg bg-rose-700 flex items-center justify-center text-white font-bold shadow-soft flex-shrink-0">
          <Package2Icon class="w-5 h-5" />
        </div>
        <div class="flex-1 min-w-0">
          <span class="text-sm font-bold tracking-tight text-slate-800 uppercase block truncate">BranchOS Cloud</span>
          <span class="text-[10px] font-medium text-slate-500 uppercase block">ERP System</span>
        </div>
      </div>

      <!-- Nav Sections -->
      <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6">
        <div>
          <h3 class="px-3 mb-3 text-[10px] uppercase font-bold text-slate-400 tracking-widest">Main Menu</h3>
          <div class="space-y-1.5">
            <SidebarItem label="Dashboard" icon="LayoutGridIcon" to="/dashboard" active />
            <SidebarItem label="Inventory" icon="PackageIcon" to="/inventory" />
            <SidebarItem label="Products" icon="DatabaseIcon" to="/products" />
            <SidebarItem label="Stock Orders" icon="ShoppingCartIcon" to="/orders" />
          </div>
        </div>

        <div>
          <h3 class="px-3 mb-3 text-[10px] uppercase font-bold text-slate-400 tracking-widest">Enterprise</h3>
          <div class="space-y-1.5">
            <SidebarItem label="Branches" icon="Building2Icon" to="/branches" />
            <SidebarItem label="Transfers" icon="ArrowLeftRightIcon" to="/transfers" />
            <SidebarItem label="Analytics" icon="BarChart3Icon" to="/analytics" />
          </div>
        </div>
      </nav>

      <!-- User Area -->
      <div class="p-4 border-t border-slate-100 bg-slate-50 space-y-3">
        <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white border border-slate-100">
          <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center text-xs font-semibold text-slate-600 flex-shrink-0">
            JD
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-slate-700 truncate">John Doe</p>
            <p class="text-[10px] font-medium text-slate-400 truncate uppercase mt-0.5">Administrator</p>
          </div>
        </div>
        <button class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition-colors bg-slate-100/50">
          <LogOutIcon class="w-4 h-4" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <!-- Top Navbar -->
      <header class="flex items-center justify-between h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 px-6 z-40">
        <div class="flex items-center gap-4 flex-1">
           <button class="lg:hidden p-2 rounded-lg hover:bg-slate-100" @click="sidebarOpen = !sidebarOpen">
              <MenuIcon class="w-5 h-5 text-slate-500" />
           </button>
           
           <div class="relative w-full max-w-sm group">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-primary-500 transition-colors" />
              <input 
                type="text" 
                placeholder="Search resources, SKUs, or orders..." 
                class="erp-input pl-9 w-full bg-slate-50 border-transparent focus:bg-white focus:border-primary-500"
              />
           </div>
        </div>

        <div class="flex items-center gap-2">
           <button class="p-2 relative rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all">
              <BellIcon class="w-5 h-5" />
              <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white ring-red-500 animate-pulse"></span>
           </button>
           <div class="h-6 w-px bg-slate-200 mx-2"></div>
           <BaseButton variant="ghost" class="text-xs font-semibold uppercase tracking-tight text-slate-500 border border-slate-200 bg-slate-50 px-2.5 h-7">
              v1.0.4-stb
           </BaseButton>
        </div>
      </header>

      <!-- Dynamic Content -->
      <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-[1400px] mx-auto">
          <slot></slot>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { 
  Package2Icon, LayoutGridIcon, PackageIcon, DatabaseIcon, ShoppingCartIcon, Building2Icon, 
  ArrowLeftRightIcon, BarChart3Icon, LogOutIcon, SearchIcon, MenuIcon, BellIcon 
} from 'lucide-vue-next'
import SidebarItem from './SidebarItem.vue'
import BaseButton from '../ui/BaseButton.vue'

const sidebarOpen = ref(true)
</script>
