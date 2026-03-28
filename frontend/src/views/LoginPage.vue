<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-950 to-black flex font-inter">
    <!-- Left Side - Enterprise Branding Panel -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#0f172a] via-[#1a2847] to-[#0f172a] relative overflow-hidden flex-col justify-between p-12">
      <!-- Animated Pink Mesh Gradient -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-pink-500 rounded-full mix-blend-screen filter blur-3xl opacity-10 animate-blob"></div>
        <div class="absolute bottom-20 left-10 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-5 animate-blob-delay"></div>
      </div>

      <!-- Content -->
      <div class="relative z-10">
        <!-- Logo -->
        <div class="mb-16">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-pink-500 to-indigo-600 flex items-center justify-center shadow-2xl shadow-pink-500/30 mb-4">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M13 6.932V8.75h-2V6.932c-3.036.298-5.545 2.874-5.545 5.968 0 3.376 2.735 6.11 6.11 6.11.993 0 1.928-.232 2.76-.648V21h2v-2.638c.832.416 1.767.648 2.76.648 3.375 0 6.11-2.735 6.11-6.11 0-3.094-2.51-5.67-5.545-5.968V6.932h-2V8.75h-2V6.932zm.5 3.782c2.03 0 3.68 1.65 3.68 3.68s-1.65 3.68-3.68 3.68-3.68-1.65-3.68-3.68 1.65-3.68 3.68-3.68z" />
            </svg>
          </div>
          <h1 class="text-4xl font-bold text-white tracking-tight">InvenSync</h1>
          <p class="text-pink-400 text-sm font-medium mt-2">Enterprise Inventory Management</p>
        </div>

        <!-- Value Props -->
        <div>
          <h2 class="text-3xl font-bold text-white mb-4 leading-tight">
            Unified Inventory Control
          </h2>
          <p class="text-slate-300 text-base leading-relaxed max-w-lg">
            Real-time visibility across branches. Automated stock transfers. Intelligent demand forecasting. All in one powerful platform.
          </p>

          <!-- Feature List -->
          <div class="mt-8 space-y-3">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-slate-300 text-sm">Multi-branch synchronization</span>
            </div>
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-slate-300 text-sm">Role-based access control</span>
            </div>
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-slate-300 text-sm">Real-time analytics & alerts</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="relative z-10">
        <p class="text-slate-500 text-xs">Version 2.0.4 • Enterprise Edition</p>
        <p class="text-slate-600 text-xs mt-2">© 2026 InvenSync. All rights reserved.</p>
      </div>
    </div>

    <!-- Right Side - Login Form with Glass Effect -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12">
      <!-- Glass Card -->
      <div class="w-full max-w-sm backdrop-blur-xl bg-white/80 border border-white/20 rounded-2xl p-8 shadow-2xl">
        
        <!-- Mobile Logo (visible only on mobile) -->
        <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-pink-500 to-indigo-600 flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M13 6.932V8.75h-2V6.932c-3.036.298-5.545 2.874-5.545 5.968 0 3.376 2.735 6.11 6.11 6.11.993 0 1.928-.232 2.76-.648V21h2v-2.638c.832.416 1.767.648 2.76.648 3.375 0 6.11-2.735 6.11-6.11 0-3.094-2.51-5.67-5.545-5.968V6.932h-2V8.75h-2V6.932zm.5 3.782c2.03 0 3.68 1.65 3.68 3.68s-1.65 3.68-3.68 3.68-3.68-1.65-3.68-3.68 1.65-3.68 3.68-3.68z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-slate-900">InvenSync</h1>
            <p class="text-pink-600 text-xs font-semibold">Enterprise</p>
          </div>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-slate-900">Sign In</h2>
          <p class="text-slate-500 text-sm mt-2">
            Enter your credentials to access the system
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-start gap-3">
          <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span class="font-medium">{{ error }}</span>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
              Email Address
            </label>
            <input
              id="email"
              type="email"
              v-model="email"
              placeholder="user@company.com"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-lg bg-white text-slate-900 placeholder-slate-400 transition-all duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            />
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label for="password" class="text-sm font-semibold text-slate-700">
                Password
              </label>
              <a href="#" class="text-xs text-slate-400 hover:text-pink-600 transition-colors font-medium">
                Forgot?
              </a>
            </div>
            <div class="relative">
              <input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 border border-slate-200 rounded-lg bg-white text-slate-900 placeholder-slate-400 transition-all duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent pr-10"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
              >
                <EyeOffIcon v-if="showPassword" :size="18" />
                <EyeIcon v-else :size="18" />
              </button>
            </div>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 disabled:from-slate-300 disabled:to-slate-400 text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-pink-600/30 hover:shadow-pink-600/50 text-sm mt-7"
          >
            <Loader2Icon v-if="isLoading" :size="18" class="animate-spin" />
            <span>{{ isLoading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Quick Access Demo -->
        <div class="mt-8 pt-6 border-t border-slate-200">
          <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-3">Quick Access Demo</p>
          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="handleDemoLogin({email: 'admin@system.com', password: 'Admin@12345'})"
              class="py-2 px-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 hover:border-blue-300 text-blue-700 hover:text-blue-800 rounded-lg transition-all text-xs font-semibold"
            >
              Admin
            </button>
            <button
              type="button"
              @click="handleDemoLogin({email: 'manager@branch1.com', password: 'Manager@12345'})"
              class="py-2 px-3 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 hover:border-emerald-300 text-emerald-700 hover:text-emerald-800 rounded-lg transition-all text-xs font-semibold"
            >
              Manager
            </button>
            <button
              type="button"
              @click="handleDemoLogin({email: 'sales@branch1.com', password: 'Sales@12345'})"
              class="py-2 px-3 bg-amber-50 hover:bg-amber-100 border border-amber-200 hover:border-amber-300 text-amber-700 hover:text-amber-800 rounded-lg transition-all text-xs font-semibold"
            >
              Sales
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

const router = useRouter();
const auth = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoading = ref(false);
const error = ref('');

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

.animate-blob-delay {
  animation: blob 7s infinite;
  animation-delay: 2s;
}
</style>

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
