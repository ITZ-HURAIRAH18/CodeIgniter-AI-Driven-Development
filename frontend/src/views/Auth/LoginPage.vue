<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4 font-inter relative overflow-hidden">
    <!-- Subtle Background Gradient -->
    <div class="absolute inset-0 opacity-40">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-slate-800/20 rounded-full blur-3xl"></div>
      <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-pink-900/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Centered Login Card -->
    <div class="relative z-10 w-full max-w-md">
      <!-- Error Toast (Flat) -->
      <transition name="slide-down">
        <div v-if="error" class="mb-6 p-4 bg-red-900/20 border border-red-800/40 rounded-lg text-red-100 text-sm flex items-start gap-3">
          <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span>{{ error }}</span>
        </div>
      </transition>

      <!-- Login Card with Subtle Glassmorphism -->
      <div class="backdrop-blur-xl bg-slate-900/40 border border-slate-800 rounded-xl p-8 shadow-2xl shadow-black/20">
        
        <!-- Header -->
        <div class="mb-8">
          <!-- Logo -->
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-lg bg-pink-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M13 6.932V8.75h-2V6.932c-3.036.298-5.545 2.874-5.545 5.968 0 3.376 2.735 6.11 6.11 6.11.993 0 1.928-.232 2.76-.648V21h2v-2.638c.832.416 1.767.648 2.76.648 3.375 0 6.11-2.735 6.11-6.11 0-3.094-2.51-5.67-5.545-5.968V6.932h-2V8.75h-2V6.932zm.5 3.782c2.03 0 3.68 1.65 3.68 3.68s-1.65 3.68-3.68 3.68-3.68-1.65-3.68-3.68 1.65-3.68 3.68-3.68z" />
              </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-50">InvenSync</h1>
          </div>

          <!-- Title & Description -->
          <h2 class="text-xl font-bold text-slate-50 mb-1">Sign In</h2>
          <p class="text-sm text-slate-400 font-normal">Enter your credentials to access the system</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4 mb-6">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-200 mb-1.5">
              Email
            </label>
            <input
              id="email"
              type="email"
              v-model="email"
              placeholder="user@company.com"
              class="w-full px-3.5 py-2.5 border border-slate-700 rounded-lg bg-slate-800/50 text-slate-50 placeholder-slate-500 text-sm transition-all duration-200 focus:outline-none focus:border-pink-600 focus:ring-1 focus:ring-pink-600/50"
            />
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="text-sm font-semibold text-slate-200">
                Password
              </label>
              <a href="#" class="text-xs text-pink-500 hover:text-pink-400 transition-colors font-medium">
                Forgot?
              </a>
            </div>
            <div class="relative">
              <input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 border border-slate-700 rounded-lg bg-slate-800/50 text-slate-50 placeholder-slate-500 text-sm transition-all duration-200 focus:outline-none focus:border-pink-600 focus:ring-1 focus:ring-pink-600/50 pr-10"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-300 transition-colors"
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
            class="w-full bg-pink-600 hover:bg-pink-700 disabled:bg-slate-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 text-sm mt-6"
          >
            <Loader2Icon v-if="isLoading" :size="17" class="animate-spin" />
            <span>{{ isLoading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="relative mb-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-700"></div>
          </div>
          <div class="relative flex justify-center text-xs">
            <span class="px-2 bg-slate-900/40 text-slate-500">or try</span>
          </div>
        </div>

        <!-- Demo Access - Segmented Control -->
        <div class="space-y-2">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Demo Access</p>
          <div class="grid grid-cols-3 gap-2">
            <!-- Admin -->
            <button
              type="button"
              @click="handleDemoLogin({email: 'admin@system.com', password: 'Admin@12345'})"
              class="py-2.5 px-3 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-800/30 hover:bg-slate-800/50 text-slate-300 hover:text-slate-200 text-xs font-medium transition-all duration-200 flex flex-col items-center gap-1.5"
            >
              <ShieldIcon :size="18" class="text-slate-400" />
              <span>Admin</span>
            </button>

            <!-- Manager -->
            <button
              type="button"
              @click="handleDemoLogin({email: 'manager@branch1.com', password: 'Manager@12345'})"
              class="py-2.5 px-3 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-800/30 hover:bg-slate-800/50 text-slate-300 hover:text-slate-200 text-xs font-medium transition-all duration-200 flex flex-col items-center gap-1.5"
            >
              <BarChart3Icon :size="18" class="text-slate-400" />
              <span>Manager</span>
            </button>

            <!-- Sales -->
            <button
              type="button"
              @click="handleDemoLogin({email: 'sales@branch1.com', password: 'Sales@12345'})"
              class="py-2.5 px-3 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-800/30 hover:bg-slate-800/50 text-slate-300 hover:text-slate-200 text-xs font-medium transition-all duration-200 flex flex-col items-center gap-1.5"
            >
              <TagIcon :size="18" class="text-slate-400" />
              <span>Sales</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-6 text-center text-xs text-slate-500">
        <p>Enterprise Edition v2.0.4</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/store/auth.store';
import { Eye as EyeIcon, EyeOff as EyeOffIcon, Loader2 as Loader2Icon, Shield as ShieldIcon, BarChart3 as BarChart3Icon, Tag as TagIcon } from 'lucide-vue-next';

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
/* Slide down transition for error toast */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-1rem);
}

/* Focus states for better accessibility */
input:focus {
  box-shadow: 0 0 0 3px rgb(236 72 153 / 0.1);
}
</style>
