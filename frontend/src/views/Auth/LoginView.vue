<template>
  <div class="login-page">
    <!-- Background decoration -->
    <div class="bg-grid"></div>
    <div class="bg-glow"></div>

    <div class="login-container">
      <!-- Brand -->
      <div class="login-brand">
        <div class="brand-logo">⬡</div>
        <h1>BranchOS</h1>
        <p>Multi-Branch Inventory Management</p>
      </div>

      <!-- Form -->
      <div class="login-card">
        <h2>Sign in to your account</h2>
        <p class="card-subtitle">Enter your credentials to continue</p>

        <div v-if="error" class="alert alert-error">
          <span>⚠</span> {{ error }}
        </div>

        <form @submit.prevent="handleLogin" novalidate>
          <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-control"
              :class="{ 'is-error': errors.email }"
              placeholder="admin@system.com"
              autocomplete="email"
              required
            />
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-control"
                :class="{ 'is-error': errors.password }"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button type="button" class="input-eye" @click="showPassword = !showPassword">
                {{ showPassword ? '○' : '●' }}
              </button>
            </div>
            <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
          </div>

          <button
            type="submit"
            class="btn btn-primary btn-lg w-full"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner"></span>
            <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Test credentials hint -->
        <div class="credentials-hint">
          <p>Test Accounts</p>
          <div class="cred-item" @click="fillCreds('admin@system.com', 'Admin@12345')">
            <span class="badge badge-info">Admin</span>
            admin@system.com
          </div>
          <div class="cred-item" @click="fillCreds('manager@branch1.com', 'Manager@12345')">
            <span class="badge badge-success">Manager</span>
            manager@branch1.com
          </div>
          <div class="cred-item" @click="fillCreds('sales@branch1.com', 'Sales@12345')">
            <span class="badge badge-neutral">Sales</span>
            sales@branch1.com
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store'

const router = useRouter()
const auth   = useAuthStore()

const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

function validate() {
  let valid = true
  errors.email = errors.password = ''

  if (!form.email) { errors.email = 'Email is required.'; valid = false }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email.'; valid = false
  }
  if (!form.password) { errors.password = 'Password is required.'; valid = false }
  else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters.'; valid = false
  }

  return valid
}

async function handleLogin() {
  error.value = ''
  if (!validate()) return

  loading.value = true
  try {
    await auth.login(form.email, form.password)
    router.push('/dashboard')
  } catch (err) {
    error.value = err?.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}

function fillCreds(email, password) {
  form.email    = email
  form.password = password
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--clr-bg-base);
  position: relative;
  overflow: hidden;
}

.bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(99,102,241,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99,102,241,0.05) 1px, transparent 1px);
  background-size: 60px 60px;
}
.bg-glow {
  position: absolute;
  width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(99,102,241,0.12), transparent 70%);
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
  padding: 24px;
}

.login-brand {
  text-align: center;
  margin-bottom: 32px;
}
.brand-logo {
  width: 60px; height: 60px;
  background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  font-size: 28px;
  margin: 0 auto 16px;
  box-shadow: 0 8px 32px var(--clr-accent-glow);
}
.login-brand h1 {
  font-size: 28px;
  font-weight: 700;
  background: linear-gradient(135deg, #fff, var(--clr-accent-light));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.03em;
}
.login-brand p {
  color: var(--clr-text-muted);
  font-size: 14px;
  margin-top: 6px;
}

.login-card {
  background: var(--clr-bg-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--radius-xl);
  padding: 32px;
  box-shadow: var(--shadow-md);
}
.login-card h2 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 4px;
}
.card-subtitle {
  color: var(--clr-text-muted);
  font-size: 14px;
  margin-bottom: 24px;
}

.input-group { position: relative; }
.input-group .form-control { padding-right: 44px; }
.input-eye {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  background: none; border: none;
  color: var(--clr-text-muted); cursor: pointer;
  font-size: 16px;
  padding: 4px;
  transition: color var(--trans-fast);
}
.input-eye:hover { color: var(--clr-text-primary); }

.form-control.is-error { border-color: var(--clr-danger); }
.form-control.is-error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }

/* Credentials hint */
.credentials-hint {
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid var(--clr-border);
}
.credentials-hint p {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--clr-text-muted);
  margin-bottom: 10px;
}
.cred-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  color: var(--clr-text-secondary);
  cursor: pointer;
  transition: all var(--trans-fast);
}
.cred-item:hover {
  background: var(--clr-bg-elevated);
  color: var(--clr-text-primary);
}
</style>
