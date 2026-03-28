<!-- <template>
  <div class="min-h-screen bg-gradient-to-br from-surface-light via-accent-pink-50 to-accent-teal-50 flex items-center justify-center px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="fixed inset-0 pointer-events-none">
      <!-- Gradient blobs -->
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-accent-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-accent-teal-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob-delay"></div>
      <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-accent-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob-slow"></div>
    </div>

    <!-- Main container -->
    <div class="w-full max-w-sm relative z-10">
      <!-- Header section -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-gradient-to-br from-accent-pink-500 to-accent-teal-500 rounded-2xl flex items-center justify-center shadow-lg shadow-accent-pink-200">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
            </svg>
          </div>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Flowventory</h1>
        <p class="text-gray-500">Multi-Branch Inventory Management System</p>
      </div>

      <!-- Login card -->
      <div class="bg-white rounded-2xl shadow-xl shadow-gray-200 p-6 mb-4 border border-gray-100">
        <div class="mb-4">
          <h2 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h2>
          <p class="text-gray-500 text-sm">Sign in to your account to continue</p>
        </div>

        <!-- Error message -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-red-800 text-sm font-medium">{{ error }}</p>
          </div>
        </div>

        <!-- Login form -->
        <form @submit.prevent="handleLogin" class="space-y-4" novalidate>
          <!-- Email field -->
          <div class="mb-2">
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="w-full px-4 py-3 rounded-lg border transition-all duration-200"
                :class="[
                  'border-gray-200 bg-gray-50 text-gray-900 placeholder-gray-400',
                  'focus:outline-none focus:ring-2 focus:ring-accent-pink-500 focus:ring-offset-0 focus:bg-white focus:border-accent-pink-300',
                  errors.email ? 'border-red-300 bg-red-50' : ''
                ]"
                placeholder="admin@company.com"
                autocomplete="email"
                required
              />
              <svg v-if="!errors.email" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Password field -->
          <div class="mb-2">
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full px-4 py-3 rounded-lg border transition-all duration-200"
                :class="[
                  'border-gray-200 bg-gray-50 text-gray-900 placeholder-gray-400',
                  'focus:outline-none focus:ring-2 focus:ring-accent-pink-500 focus:ring-offset-0 focus:bg-white focus:border-accent-pink-300',
                  errors.password ? 'border-red-300 bg-red-50' : ''
                ]"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                  <path d="M15.171 13.576l1.474 1.474a1 1 0 001.414-1.414l-2.99-2.987a4 4 0 00.464-5.6 4 4 0 01-5.478 5.478l1.616 1.649zm-5.171-4.576a2 2 0 114 0 2 2 0 01-4 0z" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <!-- Submit button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full mt-4 bg-gradient-to-r from-accent-pink-500 to-accent-pink-600 hover:from-accent-pink-600 hover:to-accent-pink-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-accent-pink-200"
          >
            <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Test credentials section (updated) -->
        <div class="mt-4 pt-4 border-t border-gray-100">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Demo Credentials</p>
          <div class="space-y-1">
            <button
              type="button"
              @click="fillCreds('admin@system.com', 'Admin@12345')"
              class="w-full p-2 rounded-lg bg-gradient-to-r from-blue-50 to-blue-50 hover:from-blue-100 hover:to-blue-100 transition-all border border-blue-100 hover:border-blue-200 text-left group"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="inline-block px-2 py-0.5 bg-status-info bg-opacity-10 text-status-info rounded text-xs font-semibold mb-0.5">System Admin</div>
                  <p class="text-xs font-medium text-gray-700 group-hover:text-gray-900">admin@system.com</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>

            <button
              type="button"
              @click="fillCreds('manager@branch1.com', 'Manager@12345')"
              class="w-full p-2 rounded-lg bg-gradient-to-r from-emerald-50 to-emerald-50 hover:from-emerald-100 hover:to-emerald-100 transition-all border border-emerald-100 hover:border-emerald-200 text-left group"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="inline-block px-2 py-0.5 bg-accent-teal-500 bg-opacity-10 text-accent-teal-600 rounded text-xs font-semibold mb-0.5">Branch Manager One</div>
                  <p class="text-xs font-medium text-gray-700 group-hover:text-gray-900">manager@branch1.com</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>

            <button
              type="button"
              @click="fillCreds('sales@branch1.com', 'Sales@12345')"
              class="w-full p-2 rounded-lg bg-gradient-to-r from-amber-50 to-amber-50 hover:from-amber-100 hover:to-amber-100 transition-all border border-amber-100 hover:border-amber-200 text-left group"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="inline-block px-2 py-0.5 bg-amber-500 bg-opacity-10 text-amber-700 rounded text-xs font-semibold mb-0.5">Sales User One</div>
                  <p class="text-xs font-medium text-gray-700 group-hover:text-gray-900">sales@branch1.com</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="text-center text-sm text-gray-500">
        © 2026 Flowventory. All rights reserved.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'

const router = useRouter()
const auth = useAuthStore()

const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const validate = () => {
  let valid = true
  errors.email = errors.password = ''

  if (!form.email) {
    errors.email = 'Email is required'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Password is required'
    valid = false
  } else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters'
    valid = false
  }

  return valid
}

const handleLogin = async () => {
  error.value = ''
  if (!validate()) return

  loading.value = true
  try {
    console.log('🔐 Login attempt:', { email: form.email })
    console.log('📍 API URL:', import.meta.env.VITE_API_URL)
    
    const result = await auth.login(form.email, form.password)
    console.log('✅ Login successful:', result)
    
    // Small delay before redirect to allow UI update
    setTimeout(() => {
      router.push('/dashboard')
    }, 500)
  } catch (err) {
    console.error('❌ Full error object:', err)
    
    // Extract error message from different possible sources
    let errorMsg = 'Login failed. '
    
    if (err?.response?.data?.message) {
      errorMsg += err.response.data.message
    } else if (err?.message) {
      errorMsg += err.message
    } else if (err?.data?.message) {
      errorMsg += err.data.message
    } else if (typeof err === 'string') {
      errorMsg += err
    } else {
      errorMsg += 'Please try again or check your backend connection.'
    }
    
    error.value = errorMsg
    loading.value = false
  }
}

const fillCreds = (email, password) => {
  form.email = email
  form.password = password
}
</script>

<style scoped>
@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

@keyframes blob-delay {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(-30px, 50px) scale(0.9);
  }
  66% {
    transform: translate(20px, -20px) scale(1.1);
  }
}

@keyframes blob-slow {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  50% {
    transform: translate(15px, 15px) scale(1.05);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animate-blob-delay {
  animation: blob-delay 7s infinite;
  animation-delay: 2s;
}

.animate-blob-slow {
  animation: blob-slow 8s infinite;
}
</style> -->
