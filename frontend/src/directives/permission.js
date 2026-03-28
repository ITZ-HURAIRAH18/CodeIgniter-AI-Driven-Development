/**
 * ─────────────────────────────────────────────────────
 * v-permission Directive
 * ─────────────────────────────────────────────────────
 * Conditionally render elements based on user permissions
 * Usage: v-permission="'products.create'" or v-permission="['orders.view', 'orders.create']"
 */

import { useAuthStore } from '@/store/auth.store'
import { hasPermission, hasAnyPermission } from '@/config/roles'

export const permissionDirective = {
  mounted(el, binding) {
    const auth = useAuthStore()
    const userRole = auth.user?.role

    if (!userRole) {
      el.style.display = 'none'
      return
    }

    const permission = binding.value
    let hasAccess = false

    if (typeof permission === 'string') {
      // Single permission
      hasAccess = hasPermission(userRole, permission)
    } else if (Array.isArray(permission)) {
      // Multiple permissions (OR logic - any permission)
      hasAccess = hasAnyPermission(userRole, permission)
    }

    if (!hasAccess) {
      el.style.display = 'none'
    }
  },

  updated(el, binding) {
    const auth = useAuthStore()
    const userRole = auth.user?.role

    if (!userRole) {
      el.style.display = 'none'
      return
    }

    const permission = binding.value
    let hasAccess = false

    if (typeof permission === 'string') {
      hasAccess = hasPermission(userRole, permission)
    } else if (Array.isArray(permission)) {
      hasAccess = hasAnyPermission(userRole, permission)
    }

    if (!hasAccess) {
      el.style.display = 'none'
    } else {
      // Restore original display value
      el.style.display = ''
    }
  },
}

export default permissionDirective
