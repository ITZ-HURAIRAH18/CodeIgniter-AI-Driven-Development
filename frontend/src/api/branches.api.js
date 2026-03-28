/**
 * ─────────────────────────────────────────────────────
 * Branches API Service
 * ─────────────────────────────────────────────────────
 * Handles branch API calls with role-based filtering
 */

import api from '@/api/axios'
import { useBranch } from '@/composables/useBranch'

export const branchesApi = {
  /**
   * Get branches - managers see only their branch
   */
  async getBranches() {
    try {
      const response = await api.get('/branches')
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch branches:', error)
      throw error
    }
  },

  /**
   * Get single branch
   */
  async getBranch(branchId) {
    try {
      const response = await api.get(`/branches/${branchId}`)
      return response.data
    } catch (error) {
      console.error(`Failed to fetch branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Create branch - admin only
   */
  async createBranch(data) {
    try {
      const response = await api.post('/branches', data)
      return response.data
    } catch (error) {
      console.error('Failed to create branch:', error)
      throw error
    }
  },

  /**
   * Update branch - admin only
   */
  async updateBranch(branchId, data) {
    try {
      const response = await api.put(`/branches/${branchId}`, data)
      return response.data
    } catch (error) {
      console.error(`Failed to update branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Delete branch - admin only
   */
  async deleteBranch(branchId) {
    try {
      const response = await api.delete(`/branches/${branchId}`)
      return response.data
    } catch (error) {
      console.error(`Failed to delete branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Get user's accessible branches
   * - Managers: only their assigned branch
   * - Admin: all branches
   * - Sales: all branches (read-only)
   */
  async getAccessibleBranches() {
    try {
      const { isBranchManager, currentBranchId } = useBranch()
      const branches = await this.getBranches()
      
      // Managers only see their branch
      if (isBranchManager && currentBranchId.value) {
        return branches.filter(b => b.id === currentBranchId.value)
      }
      
      return branches
    } catch (error) {
      console.error('Failed to fetch accessible branches:', error)
      throw error
    }
  },

  /**
   * Assign manager(s) to branch - admin only
   * @param {number} branchId
   * @param {array} managerIds - Array of manager user IDs
   */
  async assignManagers(branchId, managerIds) {
    try {
      const response = await api.post(`/branches/${branchId}/assign-manager`, {
        manager_ids: managerIds,
      })
      return response.data
    } catch (error) {
      console.error(`Failed to assign managers to branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Get managers for a branch
   * @param {number} branchId
   */
  async getManagers(branchId) {
    try {
      const response = await api.get(`/branches/${branchId}/managers`)
      return response.data || []
    } catch (error) {
      console.error(`Failed to fetch managers for branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Remove manager from branch - admin only
   * @param {number} branchId
   * @param {number} managerId
   */
  async removeManager(branchId, managerId) {
    try {
      const response = await api.delete(`/branches/${branchId}/remove-manager/${managerId}`)
      return response.data
    } catch (error) {
      console.error(`Failed to remove manager from branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Assign sales user to branch
   * - Admin: can assign any sales to any branch
   * - Manager: can assign sales to branches they manage
   * @param {number} branchId
   * @param {number} userId - Sales user ID
   */
  async assignSalesUser(branchId, userId) {
    try {
      const response = await api.post(`/branches/${branchId}/assign-sales`, {
        user_id: userId,
      })
      return response.data
    } catch (error) {
      console.error(`Failed to assign sales user to branch ${branchId}:`, error)
      throw error
    }
  },

  /**
   * Get sales users assigned to branch
   * @param {number} branchId
   */
  async getSalesUsers(branchId) {
    try {
      const response = await api.get(`/branches/${branchId}/sales`)
      return response.data || []
    } catch (error) {
      console.error(`Failed to fetch sales users for branch ${branchId}:`, error)
      throw error
    }
  },
}
