/**
 * ─────────────────────────────────────────────────────
 * useBranch Composable
 * ─────────────────────────────────────────────────────
 * Handles branch-level data filtering for managers & sales users
 * Ensures branch-restricted users only see/work in their assigned branch
 */

import { computed } from 'vue'
import { useAuthStore } from '@/store/auth.store'

export function useBranch() {
  const auth = useAuthStore()

  /**
   * Get current user's branch ID
   * - Managers: their managing branch
   * - Sales users: their assigned branch for working
   * - Admins: null (can access all)
   */
  const currentBranchId = computed(() => auth.user?.branch_id)

  /**
   * Check if user is a branch manager
   */
  const isBranchManager = computed(() => auth.user?.role === 2)

  /**
   * Check if user is admin (can see all branches)
   */
  const isAdmin = computed(() => auth.user?.role === 1)

  /**
   * Check if user is sales user
   * Sales users CAN ONLY work in their assigned branch
   */
  const isSalesUser = computed(() => auth.user?.role === 3)

  /**
   * Check if user is branch-restricted
   * Both managers and sales users are restricted to their branch
   */
  const isBranchRestricted = computed(() => 
    isBranchManager.value || isSalesUser.value
  )

  /**
   * Get effective branch ID for API calls
   * - Managers: their assigned branch ID (required)
   * - Sales: their assigned branch ID (required)
   * - Admin: null (can query all branches)
   */
  const effectiveBranchId = computed(() => {
    if (isBranchRestricted.value) {
      return currentBranchId.value || null
    }
    return null // Admin can see all
  })

  /**
   * Build query params with branch filter
   * Usage: api.get('/inventory', { params: buildBranchQuery() })
   * 
   * For managers & sales: automatically adds branch_id
   * For admins: no branch_id added (can query all)
   */
  const buildBranchQuery = (additionalParams = {}) => {
    const params = { ...additionalParams }
    
    // If manager or sales user, automatically add branch_id
    if (isBranchRestricted.value && currentBranchId.value) {
      params.branch_id = currentBranchId.value
    }
    
    return params
  }

  /**
   * Check if viewing authorized branch
   * Managers can only view their assigned branch
   * Sales users can only view their assigned branch
   * Admins can view all branches
   */
  const canViewBranch = (branchId) => {
    if (isAdmin.value) return true // Admin can view all
    if (isBranchRestricted.value) {
      return branchId === currentBranchId.value // Must be their branch
    }
    return false
  }

  /**
   * Check if can perform action in a branch
   * Sales users CAN act in their branch (create orders)
   * Managers CAN act in their branch (manage inventory)
   * Admins can act in any branch
   */
  const canActInBranch = (branchId) => {
    if (isAdmin.value) return true // Can act anywhere
    if (isBranchRestricted.value && branchId === currentBranchId.value) {
      return true // Can act in their branch
    }
    return false
  }

  /**
   * Filter items to show only those for user's branch
   */
  const filterByBranch = (items, branchIdField = 'branch_id') => {
    if (isAdmin.value) return items // Show all
    if (!currentBranchId.value) return []
    
    return items.filter(item => item[branchIdField] === currentBranchId.value)
  }

  /**
   * Get branch display name
   * For managers: shows their assigned branch name
   * For sales: shows their assigned branch name
   * For others: shows selected branch name
   */
  const getBranchDisplayName = (branchName = null) => {
    if (isBranchRestricted.value && !branchName) {
      return 'My Branch'
    }
    return branchName || 'All Branches'
  }

  return {
    currentBranchId,
    isBranchManager,
    isAdmin,
    isSalesUser,
    isBranchRestricted,
    effectiveBranchId,
    buildBranchQuery,
    canViewBranch,
    canActInBranch,
    filterByBranch,
    getBranchDisplayName,
  }
}
