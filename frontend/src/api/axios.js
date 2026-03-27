import axios from 'axios'
import { useAuthStore } from '@/store/auth.store'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  headers: { 'Content-Type': 'application/json' },
  timeout: 15000,
})

// Request interceptor — attach JWT
api.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.accessToken) {
    config.headers.Authorization = `Bearer ${auth.accessToken}`
  }
  return config
})

// Response interceptor — handle 401 (refresh or logout)
api.interceptors.response.use(
  (response) => {
    // Backend wraps all responses: { success, message, data: {...} }
    // Extract the inner 'data' property
    return response.data?.data || response.data
  },
  async (error) => {
    const originalRequest = error.config
    const auth = useAuthStore()

    // Only attempt refresh if: 401 error + not already retried + have refresh token
    if (error.response?.status === 401 && !originalRequest._retry) {
      if (!auth.refreshToken) {
        console.error('❌ 401 Error: No refresh token available. User must re-login.')
        auth.logout()
        window.location.href = '/login'
        return Promise.reject(error.response?.data || error)
      }

      originalRequest._retry = true
      try {
        console.log('🔄 Attempting token refresh...')
        await auth.refreshAccessToken()
        
        // Update the failed request with new token
        originalRequest.headers.Authorization = `Bearer ${auth.accessToken}`
        console.log('✅ Token refreshed. Retrying request...')
        return api(originalRequest)
      } catch (refreshError) {
        console.error('❌ Token refresh failed:', refreshError)
        auth.logout()
        window.location.href = '/login'
        return Promise.reject(refreshError)
      }
    }

    return Promise.reject(error.response?.data || error)
  }
)

export default api
