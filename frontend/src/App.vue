<template>
  <div id="app-root" class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    <router-view />
    <InventoryChatbot />
  </div>
</template>

<script setup>
import InventoryChatbot from '@/components/InventoryChatbot.vue';
</script>
