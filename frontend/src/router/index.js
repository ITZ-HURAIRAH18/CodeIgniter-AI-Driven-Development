import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Auth/LoginPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/components/layout/AppLayoutPro.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard/Dashboard.vue'),
      },
      {
        path: 'users',
        name: 'UserManagement',
        component: () => import('@/views/Admin/UserManagement.vue'),
        meta: { roles: [1] }, // admin only
      },
      {
        path: 'branches',
        name: 'Branches',
        component: () => import('@/views/Branch/BranchListView.vue'),
        meta: { roles: [1] }, // admin only
      },
      {
        path: 'products',
        name: 'Products',
        component: () => import('@/views/Product/ProductListView.vue'),
        meta: { roles: [1, 2, 3] },
      },
      {
        path: 'inventory',
        name: 'Inventory',
        component: () => import('@/views/Inventory/InventoryView.vue'),
        meta: { roles: [1, 2, 3] },
      },
      {
        path: 'transfers',
        name: 'Transfers',
        component: () => import('@/views/Inventory/TransferView.vue'),
        meta: { roles: [1, 2] },
      },
      {
        path: 'orders',
        name: 'Orders',
        component: () => import('@/views/Order/OrderListView.vue'),
        meta: { roles: [1, 2, 3] },
      },
      {
        path: 'orders/create',
        name: 'CreateOrder',
        component: () => import('@/views/Order/CreateOrderView.vue'),
        meta: { roles: [1, 2, 3] },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.guest && auth.isAuthenticated) {
    return next('/dashboard')
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
    return next('/dashboard') // redirect instead of 403 for UX
  }

  next()
})

export default router
