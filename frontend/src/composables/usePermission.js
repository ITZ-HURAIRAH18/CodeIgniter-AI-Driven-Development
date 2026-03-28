/**
 * ─────────────────────────────────────────────────────
 * usePermission Composable
 * ─────────────────────────────────────────────────────
 * Provides permission checking utilities in Vue components
 * Usage in components:
 *   const { can, canAny, canAll } = usePermission()
 *   can('products.create')  // boolean
 */

import { computed } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import { hasPermission, hasAnyPermission, hasAllPermissions } from '@/config/roles'

export function usePermission() {
  const auth = useAuthStore()

  const userRole = computed(() => auth.user?.role)

  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission key (e.g., 'products.create')
   * @returns {boolean}
   */
  const can = (permission) => {
    if (!userRole.value) return false
    return hasPermission(userRole.value, permission)
  }

  /**
   * Check if user has ANY of the given permissions
   * @param {string[]} permissions - Array of permission keys
   * @returns {boolean}
   */
  const canAny = (permissions) => {
    if (!userRole.value) return false
    return hasAnyPermission(userRole.value, permissions)
  }

  /**
   * Check if user has ALL of the given permissions
   * @param {string[]} permissions - Array of permission keys
   * @returns {boolean}
   */
  const canAll = (permissions) => {
    if (!userRole.value) return false
    return hasAllPermissions(userRole.value, permissions)
  }

  return {
    can,
    canAny,
    canAll,
  }
}
