/**
 * ─────────────────────────────────────────────────────
 * Inventory API Service
 * ─────────────────────────────────────────────────────
 * Handles inventory API calls with branch filtering
 */

import api from '@/api/axios'
import { useBranch } from '@/composables/useBranch'

export const inventoryApi = {
  /**
   * Get inventory with automatic branch filtering for managers
   */
  async getInventory(branchId = null) {
    const { buildBranchQuery } = useBranch()
    
    try {
      const params = buildBranchQuery({ branch_id: branchId })
      const response = await api.get('/inventory', { params })
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch inventory:', error)
      throw error
    }
  },

  /**
   * Get inventory for specific branch
   */
  async getByBranch(branchId) {
    try {
      const response = await api.get(`/inventory/branch/${branchId}`)
      return response.data || []
    } catch (error) {
      console.error(`Failed to fetch inventory for branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Add stock - with branch validation
   * Managers: Can add stock to their managed branch
   * Sales: Cannot add stock at all
   */
  async addStock(branchId, productId, quantity, notes = '') {
    const { currentBranchId, isBranchManager, isSalesUser } = useBranch()
    
    // Sales users have NO access to inventory management
    if (isSalesUser) {
      throw new Error('Sales users cannot manage inventory')
    }

    // Managers can only add stock to their branch
    if (isBranchManager && branchId !== currentBranchId.value) {
      throw new Error('You can only add stock to your managed branch')
    }

    try {
      const response = await api.post('/inventory/add', {
        branch_id: branchId,
        product_id: productId,
        quantity,
        notes,
      })
      return response.data
    } catch (error) {
      console.error('Failed to add stock:', error)
      throw error
    }
  },

  /**
   * Adjust stock - with branch validation
   * Managers: Can adjust stock in their managed branch
   * Sales: Cannot adjust stock at all
   */
  async adjustStock(branchId, productId, quantity, notes = '') {
    const { currentBranchId, isBranchManager, isSalesUser } = useBranch()
    
    // Sales users have NO access to inventory management
    if (isSalesUser) {
      throw new Error('Sales users cannot manage inventory')
    }

    // Managers can only adjust stock in their branch
    if (isBranchManager && branchId !== currentBranchId.value) {
      throw new Error('You can only adjust stock in your managed branch')
    }

    try {
      const response = await api.post('/inventory/adjust', {
        branch_id: branchId,
        product_id: productId,
        quantity,
        notes,
      })
      return response.data
    } catch (error) {
      console.error('Failed to adjust stock:', error)
      throw error
    }
  },

  /**
   * Get inventory logs - with branch filtering
   */
  async getLogs(branchId = null, productId = null) {
    try {
      const params = {}
      if (branchId) params.branch_id = branchId
      if (productId) params.product_id = productId
      
      const response = await api.get('/inventory/logs', { params })
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch inventory logs:', error)
      throw error
    }
  },
}
