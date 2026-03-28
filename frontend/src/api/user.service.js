import api from '@/api/axios'

export const userService = {
  // Get all users
  async getAllUsers() {
    const response = await api.get('/users')
    return response
  },

  // Create new user (admin only)
  async createUser(userData) {
    const response = await api.post('/users', userData)
    return response
  },

  // Assign branch to user
  async assignBranch(userId, branchId) {
    const response = await api.put(`/users/${userId}/assign-branch`, {
      branch_id: branchId,
    })
    return response
  },

  // Get all branches for dropdown
  async getBranches() {
    const response = await api.get('/branches')
    return response
  },
}
