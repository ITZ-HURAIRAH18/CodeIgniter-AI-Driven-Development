<template>
  <div class="min-h-screen bg-white">
    <!-- Sidebar -->
    <SidebarPro />

    <!-- Main Content -->
    <div class="pl-60">
      <!-- Topbar -->
      <TopbarPro />

      <!-- Page Content -->
      <main class="mt-16 p-6">
        <router-view v-slot="{ Component }">
          <transition
            enter-active-class="animate-fade-in"
            leave-active-class="animate-fade-out"
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
import SidebarPro from './SidebarPro.vue'
import TopbarPro from './TopbarPro.vue'
</script>
