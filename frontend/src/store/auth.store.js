import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('bos_user') || 'null'),
    accessToken: localStorage.getItem('bos_access_token') || null,
    refreshToken: localStorage.getItem('bos_refresh_token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.accessToken,
    isAdmin: (state) => state.user?.role === 1,
    isBranchManager: (state) => state.user?.role === 2,
    isSalesUser: (state) => state.user?.role === 3,
    userBranchId: (state) => state.user?.branch_id,
  },

  actions: {
    async login(email, password) {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('/auth/login', { email, password })
        this.setSession(res.data)
        return res
      } catch (err) {
        this.error = err.message || 'Login failed'
        throw err
      } finally {
        this.loading = false
      }
    },

    setSession(data) {
      this.accessToken  = data.access_token
      this.refreshToken = data.refresh_token
      this.user         = data.user

      localStorage.setItem('bos_access_token',  data.access_token)
      localStorage.setItem('bos_refresh_token', data.refresh_token)
      localStorage.setItem('bos_user',          JSON.stringify(data.user))
    },

    async refreshAccessToken() {
      const res = await api.post('/auth/refresh', { refresh_token: this.refreshToken })
      this.accessToken = res.data.access_token
      localStorage.setItem('bos_access_token', res.data.access_token)
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch {} // Ignore errors on logout
      finally {
        this.accessToken  = null
        this.refreshToken = null
        this.user         = null
        localStorage.removeItem('bos_access_token')
        localStorage.removeItem('bos_refresh_token')
        localStorage.removeItem('bos_user')
      }
    },
  },
})
