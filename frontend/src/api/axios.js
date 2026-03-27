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
  (response) => response.data,
  async (error) => {
    const originalRequest = error.config
    const auth = useAuthStore()

    if (error.response?.status === 401 && !originalRequest._retry && auth.refreshToken) {
      originalRequest._retry = true
      try {
        await auth.refreshAccessToken()
        originalRequest.headers.Authorization = `Bearer ${auth.accessToken}`
        return api(originalRequest)
      } catch {
        auth.logout()
        window.location.href = '/login'
      }
    }
    return Promise.reject(error.response?.data || error)
  }
)

export default api
