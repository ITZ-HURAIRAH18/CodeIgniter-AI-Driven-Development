/**
 * ─────────────────────────────────────────────────────
 * Users API Service
 * ─────────────────────────────────────────────────────
 * Handles user management with role-based branch assignment
 */

import api from '@/api/axios'

export const usersApi = {
  /**
   * Get all active users
   */
  async getUsers() {
    try {
      const response = await api.get('/users')
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch users:', error)
      throw error
    }
  },

  /**
   * Create new user (admin only)
   * 
   * NEW WORKFLOW: All users created WITHOUT branch assigned.
   * Branch assignment happens separately via assignBranchToUser.
   * 
   * @param {object} userData - { name, email, password, role_id }
   */
  async createUser(userData) {
    const { name, email, password, role_id } = userData

    // Validate required fields
    if (!name || !email || !password || !role_id) {
      throw new Error('Name, email, password, and role_id are required')
    }

    // Validate password strength
    if (password.length < 8) {
      throw new Error('Password must be at least 8 characters')
    }

    try {
      const data = {
        name,
        email,
        password,
        role_id: parseInt(role_id),
        // All users created WITHOUT branch - branch_id stays null
      }

      const response = await api.post('/users', data)
      return response.data
    } catch (error) {
      console.error('Failed to create user:', error)
      throw error
    }
  },

  /**
   * Assign branch to user
   * 
   * Admin: Can assign any user to any branch
   * Manager: Can assign sales users to branches they manage
   * 
   * @param {number} userId
   * @param {number} branchId
   */
  async assignBranchToUser(userId, branchId) {
    if (!userId || !branchId) {
      throw new Error('userId and branchId are required')
    }

    try {
      const response = await api.put(`/users/${userId}/assign-branch`, {
        branch_id: parseInt(branchId),
      })
      return response.data
    } catch (error) {
      console.error(`Failed to assign branch to user ${userId}:`, error)
      throw error
    }
  },
}
