import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => {
    let user = null
    try {
      const stored = localStorage.getItem('bos_user')
      user = (stored && stored !== 'undefined') ? JSON.parse(stored) : null
    } catch (e) {
      user = null
    }
    
    return {
      user,
      accessToken: localStorage.getItem('bos_access_token') || null,
      refreshToken: localStorage.getItem('bos_refresh_token') || null,
      loading: false,
      error: null,
    }
  },

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
        console.log('🔐 Logging in with email:', email)
        // res is now the unwrapped data: { access_token, refresh_token, user, expires_in }
        const res = await api.post('/auth/login', { email, password })
        
        if (!res.access_token || !res.refresh_token) {
          console.error('❌ Response missing tokens:', res)
          throw new Error('Server response missing access_token or refresh_token')
        }
        
        this.setSession(res)
        console.log('✅ Login successful. Tokens stored:', {
          accessToken: res.access_token.substring(0, 20) + '...',
          refreshToken: res.refresh_token.substring(0, 20) + '...',
          user: res.user
        })
        return res
      } catch (err) {
        this.error = err.message || 'Login failed'
        console.error('❌ Login error:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    setSession(data) {
      if (!data.access_token || !data.refresh_token) {
        console.error('❌ setSession called with incomplete token data:', data)
        throw new Error('Missing access_token or refresh_token')
      }

      this.accessToken  = data.access_token
      this.refreshToken = data.refresh_token
      this.user         = data.user

      localStorage.setItem('bos_access_token',  data.access_token)
      localStorage.setItem('bos_refresh_token', data.refresh_token)
      localStorage.setItem('bos_user',          JSON.stringify(data.user))
      
      console.log('✅ Session set in state and localStorage')
    },

    async refreshAccessToken() {
      if (!this.refreshToken) {
        console.error('❌ No refresh token available in store')
        throw new Error('No refresh token available')
      }

      console.log('🔄 Sending refresh token to server...')
      try {
        // res is now the unwrapped data: { access_token, expires_in }
        const res = await api.post('/auth/refresh', { refresh_token: this.refreshToken })
        
        if (!res.access_token) {
          console.error('❌ Refresh response missing access_token:', res)
          throw new Error('Server response missing access_token')
        }
        
        this.accessToken = res.access_token
        localStorage.setItem('bos_access_token', res.access_token)
        console.log('✅ Access token refreshed successfully')
      } catch (err) {
        console.error('❌ Refresh failed:', err.message || err)
        throw err
      }
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
