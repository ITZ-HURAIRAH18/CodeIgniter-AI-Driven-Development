<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-900 mb-1">User Management</h1>
        <p class="text-slate-600 text-sm">Create and manage system users with role-based access control</p>
      </div>

      <!-- Alert if not admin -->
      <div 
        v-if="!isAdmin"
        class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-center gap-3"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span>Only administrators can create and manage users.</span>
      </div>

      <div v-if="isAdmin" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create User Form (Left Column) -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-bold text-slate-900 mb-5 flex items-center gap-2">
              <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              Create New User
            </h2>

            <form @submit.prevent="handleCreateUser" class="space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="John Doe"
                  class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none text-sm"
                  required
                />
              </div>

              <!-- Email -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email *</label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="user@company.com"
                  class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none text-sm"
                  required
                />
              </div>

              <!-- Password -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password *</label>
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="••••••••"
                  class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none text-sm"
                  required
                  minlength="8"
                />
                <p class="text-xs text-slate-500 mt-1">Min 8 characters</p>
              </div>

              <!-- Role Selection -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role *</label>
                <select
                  v-model="form.role_id"
                  class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none text-sm"
                  required
                >
                  <option value="">Select role</option>
                  <option value="1">System Admin</option>
                  <option value="2">Branch Manager</option>
                  <option value="3">Sales User</option>
                </select>
              </div>

              <!-- Error Message -->
              <div v-if="formError" class="p-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs">
                {{ formError }}
              </div>

              <!-- Success Message -->
              <div v-if="formSuccess" class="p-2.5 bg-green-50 border border-green-200 rounded-lg text-green-700 text-xs">
                ✅ {{ formSuccess }}
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="creating"
                class="w-full h-9 bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 disabled:from-slate-300 disabled:to-slate-400 text-white font-semibold rounded-lg transition-all text-sm flex items-center justify-center gap-2 mt-4"
              >
                <svg v-if="!creating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ creating ? 'Creating...' : 'Create User' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Users List (Right Column) -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <!-- Header with Search -->
            <div class="p-6 border-b border-slate-200 flex items-center justify-between gap-4">
              <div>
                <h2 class="text-base font-bold text-slate-900">Users List</h2>
                <p class="text-xs text-slate-500 mt-1">{{ users.length }} user{{ users.length !== 1 ? 's' : '' }} total</p>
              </div>
              <div class="w-64">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by name or email..."
                  class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none text-sm"
                />
              </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
              <div class="inline-block animate-spin">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </div>
              <p class="text-slate-600 text-sm mt-2">Loading users...</p>
            </div>

            <!-- Users Table -->
            <div v-else class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs">User</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs">Role</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs">Branch</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-700 text-xs">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                  <tr
                    v-for="user in filteredUsers"
                    :key="user.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <!-- User Name & Email -->
                    <td class="py-3 px-4">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-300 to-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                          <p class="font-semibold text-slate-900 text-sm">{{ user.name }}</p>
                          <p class="text-xs text-slate-500">{{ user.email }}</p>
                        </div>
                      </div>
                    </td>

                    <!-- Role -->
                    <td class="py-3 px-4">
                      <span
                        :class="{
                          'bg-blue-100 text-blue-700': user.role_id === 1,
                          'bg-emerald-100 text-emerald-700': user.role_id === 2,
                          'bg-amber-100 text-amber-700': user.role_id === 3,
                        }"
                        class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold"
                      >
                        {{ getRoleName(user.role_id) }}
                      </span>
                    </td>

                    <!-- Branch -->
                    <td class="py-3 px-4 text-slate-600 text-sm">
                      {{ user.branch_id ? `Branch ${user.branch_id}` : '—' }}
                    </td>

                    <!-- Status -->
                    <td class="py-3 px-4 text-center">
                      <span
                        :class="{
                          'bg-green-100 text-green-700': user.is_active,
                          'bg-slate-100 text-slate-600': !user.is_active,
                        }"
                        class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold"
                      >
                        {{ user.is_active ? '✓ Active' : '✕ Inactive' }}
                      </span>
                    </td>
                  </tr>

                  <tr v-if="filteredUsers.length === 0">
                    <td colspan="4" class="py-8 text-center text-slate-500 text-sm">
                      {{ searchQuery ? 'No users found matching your search.' : 'No users found.' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Help Tooltip Panel -->
          <div v-if="showRoleInfo" class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 9a1 1 0 100-2 1 1 0 000 2zm5-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                </svg>
                <h3 class="text-sm font-semibold text-slate-900">Role Permissions Guide</h3>
              </div>
              <button
                @click="showRoleInfo = false"
                class="text-slate-400 hover:text-slate-600"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
            <div class="space-y-2 text-xs text-slate-600">
              <p><strong class="text-slate-900">System Admin:</strong> Full system access, create/manage users, branches</p>
              <p><strong class="text-slate-900">Branch Manager:</strong> Manage inventory & sales for assigned branch</p>
              <p><strong class="text-slate-900">Sales User:</strong> View orders, manage branch inventory</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import { userService } from '@/api/user.service'

const auth = useAuthStore()
const isAdmin = computed(() => auth.isAdmin)

const users = ref([])
const loading = ref(true)
const creating = ref(false)
const formError = ref('')
const formSuccess = ref('')
const searchQuery = ref('')
const showRoleInfo = ref(true)

const form = ref({
  name: '',
  email: '',
  password: '',
  role_id: '',
})

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  
  const query = searchQuery.value.toLowerCase()
  return users.value.filter(user => 
    user.name.toLowerCase().includes(query) || 
    user.email.toLowerCase().includes(query)
  )
})

const getRoleName = (roleId) => {
  const roles = {
    1: 'System Admin',
    2: 'Branch Manager',
    3: 'Sales User',
  }
  return roles[roleId] || 'Unknown'
}

const loadUsers = async () => {
  try {
    loading.value = true
    const data = await userService.getAllUsers()
    users.value = data
  } catch (err) {
    console.error('Failed to load users:', err)
  } finally {
    loading.value = false
  }
}

const handleCreateUser = async () => {
  formError.value = ''
  formSuccess.value = ''

  // Validation
  if (!form.value.name || !form.value.email || !form.value.password || !form.value.role_id) {
    formError.value = 'Please fill in all required fields'
    return
  }

  try {
    creating.value = true

    // Create user (no branch assignment here)
    const createData = {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      role_id: parseInt(form.value.role_id),
    }

    const newUser = await userService.createUser(createData)
    console.log('✅ User created:', newUser)

    // Reset form
    form.value = {
      name: '',
      email: '',
      password: '',
      role_id: '',
    }

    formSuccess.value = `User "${newUser.name}" created successfully!`
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      formSuccess.value = ''
    }, 3000)

    // Reload users list
    await loadUsers()
  } catch (err) {
    console.error('Failed to create user:', err)
    
    if (err?.response?.data?.message) {
      formError.value = err.response.data.message
    } else if (err?.response?.data?.errors) {
      formError.value = Object.values(err.response.data.errors).flat().join(', ')
    } else {
      formError.value = err.message || 'Failed to create user'
    }
  } finally {
    creating.value = false
  }
}

onMounted(() => {
  loadUsers()
})
</script>
