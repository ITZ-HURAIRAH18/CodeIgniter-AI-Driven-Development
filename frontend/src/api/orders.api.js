/**
 * ─────────────────────────────────────────────────────
 * Orders API Service
 * ─────────────────────────────────────────────────────
 * Handles order API calls with branch filtering
 */

import api from '@/api/axios'
import { useBranch } from '@/composables/useBranch'

export const ordersApi = {
  /**
   * Get orders with automatic branch filtering for managers
   */
  async getOrders(branchId = null) {
    const { buildBranchQuery } = useBranch()
    
    try {
      const params = buildBranchQuery({ branch_id: branchId })
      const response = await api.get('/orders', { params })
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch orders:', error)
      throw error
    }
  },

  /**
   * Get single order
   */
  async getOrder(orderId) {
    try {
      const response = await api.get(`/orders/${orderId}`)
      return response.data
    } catch (error) {
      console.error(`Failed to fetch order ${orderId}:`, error)
      throw error
    }
  },

  /**
   * Create order - with branch validation
   * 
   * Sales users: MUST create in their assigned branch
   * Managers: Can create in their managed branch
   * Admins: Can create in any branch
   */
  async createOrder(branchId, items, notes = '') {
    const { currentBranchId, isBranchManager, isAdmin, isSalesUser } = useBranch()
    
    // Sales users can ONLY create orders in their assigned branch
    if (isSalesUser && branchId !== currentBranchId.value) {
      throw new Error('Sales users can only create orders for their assigned branch')
    }

    // Managers can only create orders for their managed branches
    if (isBranchManager && branchId !== currentBranchId.value) {
      throw new Error('You can only create orders for your managed branch')
    }

    try {
      const response = await api.post('/orders', {
        branch_id: branchId,
        items,
        notes,
      })
      return response.data
    } catch (error) {
      console.error('Failed to create order:', error)
      throw error
    }
  },

  /**
   * Cancel order - with branch validation
   */
  async cancelOrder(orderId, reason = '') {
    try {
      const response = await api.post(`/orders/${orderId}/cancel`, {
        reason,
      })
      return response.data
    } catch (error) {
      console.error(`Failed to cancel order ${orderId}:`, error)
      throw error
    }
  },

  /**
   * Get orders by branch
   */
  async getByBranch(branchId) {
    try {
      const response = await api.get('/orders', { 
        params: { branch_id: branchId }
      })
      return response.data || []
    } catch (error) {
      console.error(`Failed to fetch orders for branch ${branchId}:`, error)
      throw error
    }
  },
}
