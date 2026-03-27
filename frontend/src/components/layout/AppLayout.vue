<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-brand">
        <div class="brand-icon">⬡</div>
        <span class="brand-name">BranchOS</span>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>

        <router-link to="/dashboard" class="nav-item">
          <span class="nav-icon">▦</span>
          <span class="nav-label">Dashboard</span>
        </router-link>

        <router-link to="/orders" class="nav-item">
          <span class="nav-icon">◈</span>
          <span class="nav-label">Orders</span>
        </router-link>

        <router-link to="/inventory" class="nav-item">
          <span class="nav-icon">◻</span>
          <span class="nav-label">Inventory</span>
        </router-link>

        <template v-if="auth.isAdmin || auth.isBranchManager">
          <router-link to="/transfers" class="nav-item">
            <span class="nav-icon">⇄</span>
            <span class="nav-label">Transfers</span>
          </router-link>
        </template>

        <div class="nav-section-label">Catalog</div>

        <router-link to="/products" class="nav-item">
          <span class="nav-icon">◈</span>
          <span class="nav-label">Products</span>
        </router-link>

        <template v-if="auth.isAdmin">
          <router-link to="/branches" class="nav-item">
            <span class="nav-icon">◉</span>
            <span class="nav-label">Branches</span>
          </router-link>
        </template>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ initials }}</div>
          <div class="user-meta">
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-role">{{ roleLabel }}</div>
          </div>
        </div>
        <button class="btn-logout" @click="handleLogout" title="Logout">⏻</button>
      </div>
    </aside>

    <!-- Main content -->
    <div class="main-area">
      <header class="app-header">
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">☰</button>
        <div class="header-breadcrumb">{{ currentRouteName }}</div>
        <div class="header-right">
          <div class="header-time">{{ currentTime }}</div>
        </div>
      </header>

      <main class="content-area">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore }  from '@/store/auth.store'

const auth   = useAuthStore()
const route  = useRoute()
const router = useRouter()

const sidebarCollapsed = ref(false)
const currentTime = ref('')

const initials = computed(() => {
  const name = auth.user?.name || ''
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const roleLabel = computed(() => {
  const map = { 1: 'Administrator', 2: 'Branch Manager', 3: 'Sales User' }
  return map[auth.user?.role] || 'User'
})

const currentRouteName = computed(() => route.name || 'Dashboard')

let ticker
onMounted(() => {
  const tick = () => {
    currentTime.value = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  }
  tick()
  ticker = setInterval(tick, 30000)
})
onUnmounted(() => clearInterval(ticker))

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
}

/* SIDEBAR */
.sidebar {
  width: var(--sidebar-w);
  background: var(--clr-bg-surface);
  border-right: 1px solid var(--clr-border);
  display: flex;
  flex-direction: column;
  transition: width var(--trans-base);
  flex-shrink: 0;
  overflow: hidden;
}
.sidebar.collapsed { width: 64px; }

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 22px 20px;
  border-bottom: 1px solid var(--clr-border);
}
.brand-icon {
  width: 36px; height: 36px;
  background: var(--clr-accent);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px var(--clr-accent-glow);
}
.brand-name {
  font-size: 18px;
  font-weight: 700;
  color: var(--clr-text-primary);
  letter-spacing: -0.02em;
  white-space: nowrap;
}
.sidebar.collapsed .brand-name { display: none; }

/* NAV */
.sidebar-nav {
  flex: 1;
  padding: 16px 10px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.nav-section-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--clr-text-muted);
  padding: 12px 10px 6px;
  white-space: nowrap;
}
.sidebar.collapsed .nav-section-label { opacity: 0; }

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: var(--radius-md);
  color: var(--clr-text-secondary);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all var(--trans-fast);
  white-space: nowrap;
}
.nav-item:hover { background: var(--clr-bg-hover); color: var(--clr-text-primary); }
.nav-item.router-link-active {
  background: var(--clr-accent-glow);
  color: var(--clr-accent-light);
  border: 1px solid var(--clr-border-accent);
}
.nav-icon {
  width: 20px; height: 20px;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}
.nav-label { overflow: hidden; }
.sidebar.collapsed .nav-label { display: none; }

/* FOOTER */
.sidebar-footer {
  padding: 16px;
  border-top: 1px solid var(--clr-border);
  display: flex;
  align-items: center;
  gap: 10px;
}
.user-info { display: flex; align-items: center; gap: 10px; flex: 1; overflow: hidden; }
.user-avatar {
  width: 34px; height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  flex-shrink: 0;
}
.user-meta { overflow: hidden; }
.user-name  { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role  { font-size: 11px; color: var(--clr-text-muted); }
.sidebar.collapsed .user-meta { display: none; }
.sidebar.collapsed .btn-logout { margin-left: auto; }

.btn-logout {
  background: transparent;
  border: none;
  color: var(--clr-text-muted);
  cursor: pointer;
  font-size: 18px;
  padding: 4px;
  border-radius: var(--radius-sm);
  transition: color var(--trans-fast);
  flex-shrink: 0;
}
.btn-logout:hover { color: var(--clr-danger); }

/* MAIN AREA */
.main-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* HEADER */
.app-header {
  height: 56px;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 0 24px;
  background: var(--clr-bg-surface);
  border-bottom: 1px solid var(--clr-border);
  flex-shrink: 0;
}
.collapse-btn {
  background: transparent;
  border: none;
  color: var(--clr-text-muted);
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
  border-radius: var(--radius-sm);
  transition: color var(--trans-fast);
}
.collapse-btn:hover { color: var(--clr-text-primary); }

.header-breadcrumb {
  font-size: 15px;
  font-weight: 600;
  color: var(--clr-text-primary);
  flex: 1;
}
.header-right { display: flex; align-items: center; gap: 16px; }
.header-time  { font-size: 13px; color: var(--clr-text-muted); font-variant-numeric: tabular-nums; }

/* CONTENT */
.content-area {
  flex: 1;
  overflow-y: auto;
  padding: 28px;
  background: var(--clr-bg-base);
}
</style>
