<template>
  <div class="min-h-screen bg-white flex font-inter">
    <!-- Left Side - Dark Gradient with Brand Pink (Hidden on mobile) -->
    <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 relative overflow-hidden flex-col justify-between p-12">
      <!-- Animated Background Elements with Brand Pink -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-10 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-15 animate-blob"></div>
        <div class="absolute bottom-20 right-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000"></div>
      </div>

      <!-- Content -->
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-16">
          <div class="w-12 h-12 text-pink-400">
            <FlowventoryLogo />
          </div>
          <div>
            <h1 class="text-3xl font-bold text-white">Flowventory</h1>
            <p class="text-pink-300 text-sm font-medium">Smart Inventory Management</p>
          </div>
        </div>

        <div>
          <h2 class="text-4xl font-bold text-white mb-4 leading-tight">
            Streamline Your Inventory
          </h2>
          <p class="text-slate-300 text-base leading-relaxed max-w-md">
            Real-time tracking, automated workflows, and intelligent insights—all in one powerful platform.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="relative z-10 text-slate-400 text-sm">
        <p>© 2026 Flowventory. Powered by intelligent automation.</p>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-6 md:p-12">
      <div class="w-full max-w-md">
        <!-- Mobile Logo (visible only on mobile) -->
        <div class="md:hidden flex items-center gap-2 mb-8 justify-center">
          <div class="w-10 h-10 text-pink-600">
            <FlowventoryLogo />
          </div>
          <h1 class="text-2xl font-bold text-slate-900">Flowventory</h1>
        </div>

        <!-- Form Header -->
        <div class="mb-8">
          <h2 class="text-xl font-bold text-slate-900">Welcome Back</h2>
          <p class="text-slate-600 text-xs mt-2">
            Sign in to your account to continue
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs flex items-start gap-2">
          <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span class="font-medium">{{ error }}</span>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Email Input -->
          <div class="relative">
            <input
              type="email"
              v-model="email"
              @focus="focusedField = 'email'"
              @blur="focusedField = null"
              placeholder=" "
              class="block w-full px-4 py-2.5 border-b-2 border-slate-200 bg-transparent text-slate-900 placeholder-transparent transition-colors focus:outline-none text-sm"
              :class="{
                'border-b-pink-600': focusedField === 'email',
                'hover:border-b-slate-300': focusedField !== 'email'
              }"
            />
            <label
              :class="{
                'top-0 text-pink-600': focusedField === 'email' || email,
                'top-2 text-slate-500': focusedField !== 'email' && !email
              }"
              class="absolute left-4 transition-all text-xs font-medium pointer-events-none"
            >
              Email Address
            </label>
          </div>

          <!-- Password Input -->
          <div class="relative mt-6">
            <div class="relative">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                @focus="focusedField = 'password'"
                @blur="focusedField = null"
                placeholder=" "
                class="block w-full px-4 py-2.5 pr-10 border-b-2 border-slate-200 bg-transparent text-slate-900 placeholder-transparent transition-colors focus:outline-none text-sm"
                :class="{
                  'border-b-pink-600': focusedField === 'password',
                  'hover:border-b-slate-300': focusedField !== 'password'
                }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-0 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
              >
                <EyeOffIcon v-if="showPassword" :size="16" />
                <EyeIcon v-else :size="16" />
              </button>
            </div>
            <label
              :class="{
                'top-0 text-pink-600': focusedField === 'password' || password,
                'top-2 text-slate-500': focusedField !== 'password' && !password
              }"
              class="absolute left-4 transition-all text-xs font-medium pointer-events-none"
            >
              Password
            </label>
          </div>

          <!-- Forgot Password Link -->
          <div class="flex justify-end pt-2">
            <a href="#" class="text-xs text-pink-600 hover:text-pink-700 font-semibold transition-colors">
              Forgot Password?
            </a>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-pink-600 hover:bg-pink-700 active:bg-pink-800 disabled:bg-slate-300 text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-pink-600/30 hover:shadow-lg hover:shadow-pink-600/40 text-sm mt-6"
          >
            <Loader2Icon v-if="isLoading" :size="16" class="animate-spin" />
            <span>{{ isLoading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="my-6 flex items-center gap-3">
          <div class="flex-1 h-px bg-slate-200"></div>
          <span class="text-xs text-slate-400">or</span>
          <div class="flex-1 h-px bg-slate-200"></div>
        </div>

        <!-- Demo Credentials Section -->
        <div>
          <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-3">Demo Credentials</p>
          <div class="space-y-2">
            <!-- System Admin -->
            <button
              type="button"
              @click="handleDemoLogin({id: 'admin', label: 'System Admin', email: 'admin@system.com', password: 'Admin@12345'})"
              class="w-full p-3 rounded-lg bg-gradient-to-r from-blue-50 to-blue-50 hover:from-blue-100 hover:to-blue-100 transition-all border border-blue-200 hover:border-blue-300 text-left group"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="inline-block px-2 py-0.5 bg-blue-500 bg-opacity-10 text-blue-700 rounded text-xs font-semibold mb-1">System Admin</div>
                  <p class="text-xs font-medium text-slate-700">admin@system.com</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>

            <!-- Branch Manager -->
            <button
              type="button"
              @click="handleDemoLogin({id: 'manager', label: 'Branch Manager', email: 'manager@branch1.com', password: 'Manager@12345'})"
              class="w-full p-3 rounded-lg bg-gradient-to-r from-emerald-50 to-emerald-50 hover:from-emerald-100 hover:to-emerald-100 transition-all border border-emerald-200 hover:border-emerald-300 text-left group"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="inline-block px-2 py-0.5 bg-emerald-500 bg-opacity-10 text-emerald-700 rounded text-xs font-semibold mb-1">Branch Manager</div>
                  <p class="text-xs font-medium text-slate-700">manager@branch1.com</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>

            <!-- Sales User -->
            <button
              type="button"
              @click="handleDemoLogin({id: 'user', label: 'Sales User', email: 'sales@branch1.com', password: 'Sales@12345'})"
              class="w-full p-3 rounded-lg bg-gradient-to-r from-amber-50 to-amber-50 hover:from-amber-100 hover:to-amber-100 transition-all border border-amber-200 hover:border-amber-300 text-left group"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="inline-block px-2 py-0.5 bg-amber-500 bg-opacity-10 text-amber-700 rounded text-xs font-semibold mb-1">Sales User</div>
                  <p class="text-xs font-medium text-slate-700">sales@branch1.com</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/store/auth.store';
import { Eye as EyeIcon, EyeOff as EyeOffIcon, Loader2 as Loader2Icon } from 'lucide-vue-next';
import FlowventoryLogo from '@/components/FlowventoryLogo.vue';

const router = useRouter();
const auth = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoading = ref(false);
const error = ref('');
const focusedField = ref(null);

const handleDemoLogin = (account) => {
  email.value = account.email;
  password.value = account.password;
  error.value = '';
};

const handleSubmit = async () => {
  error.value = '';
  
  // Validate inputs
  if (!email.value || !password.value) {
    error.value = 'Please fill in all fields';
    return;
  }

  isLoading.value = true;
  try {
    console.log('🔐 Login attempt:', { email: email.value });
    console.log('📍 API URL:', import.meta.env.VITE_API_URL);
    
    // Call actual auth API
    const result = await auth.login(email.value, password.value);
    console.log('✅ Login successful:', result);
    
    // Redirect to dashboard
    setTimeout(() => {
      router.push('/dashboard');
    }, 500);
  } catch (err) {
    console.error('❌ Full error object:', err);
    
    // Extract error message from different possible sources
    let errorMsg = 'Login failed. ';
    
    if (err?.response?.data?.message) {
      errorMsg += err.response.data.message;
    } else if (err?.message) {
      errorMsg += err.message;
    } else if (err?.data?.message) {
      errorMsg += err.data.message;
    } else if (typeof err === 'string') {
      errorMsg += err;
    } else {
      errorMsg += 'Please check your credentials and try again.';
    }
    
    error.value = errorMsg;
  } finally {
    isLoading.value = false;
  }
};
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

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
