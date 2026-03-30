<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Top Navigation Bar -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-40">
      <div class="px-6 py-4 flex items-center justify-between">
        <!-- Left: Title -->
        <div>
          <h1 class="text-lg font-semibold text-slate-900">User Management</h1>
          <p class="text-xs text-slate-500 mt-0.5">{{ users.length }} user{{ users.length !== 1 ? 's' : '' }} total</p>
        </div>

        <!-- Center: Global Search -->
        <div class="flex-1 mx-8 max-w-md">
          <div class="relative">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name or email..."
              class="w-full h-9 pl-9 pr-3 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent-pink-500 focus:border-transparent outline-none text-sm transition-all"
            />
          </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-2">
          <!-- Bulk Actions Menu -->
          <div v-if="selectedUsers.length > 0" class="flex items-center gap-2">
            <span class="text-xs text-slate-600 font-medium">{{ selectedUsers.length }} selected</span>
            <button
              @click="showBulkActionsMenu = !showBulkActionsMenu"
              class="px-3 h-9 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 transition-colors relative"
            >
              Actions
              <svg v-if="showBulkActionsMenu" class="absolute right-2 top-2.5 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
              </svg>
              <svg v-else class="absolute right-2 top-2.5 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </button>
            <!-- Bulk Actions Dropdown -->
            <div v-if="showBulkActionsMenu" class="absolute top-14 right-0 bg-white border border-slate-200 rounded-lg shadow-lg z-50 w-48">
              <button
                @click="bulkChangeStatus(true)"
                class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 border-b border-slate-100 flex items-center gap-2"
              >
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Activate Selected
              </button>
              <button
                @click="bulkChangeStatus(false)"
                class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 border-b border-slate-100 flex items-center gap-2"
              >
                <svg class="w-4 h-4 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                Deactivate Selected
              </button>
              <button
                @click="bulkDelete"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete Selected
              </button>
            </div>
          </div>

          <!-- Create User Button -->
          <button
            v-if="isAdmin"
            @click="openDrawer"
            class="px-4 h-9 bg-accent-pink-500 hover:bg-accent-pink-600 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add User
          </button>
        </div>
      </div>
    </div>

    <!-- Access Denied Alert -->
    <div v-if="!isAdmin" class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-center gap-3">
      <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
      </svg>
      <span>Only administrators can create and manage users.</span>
    </div>

    <!-- Main Content -->
    <div v-if="isAdmin" class="px-6 py-6">
      <!-- Users Table Card -->
      <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin">
            <svg class="w-6 h-6 text-accent-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <p class="text-slate-600 text-sm mt-2">Loading users...</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <!-- Checkbox Header -->
                <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs w-10">
                  <input
                    type="checkbox"
                    :checked="selectedUsers.length === filteredUsers.length && filteredUsers.length > 0"
                    @change="toggleSelectAll"
                    class="w-4 h-4 rounded border-slate-300 text-accent-pink-600 focus:ring-accent-pink-500 cursor-pointer"
                  />
                </th>
                <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs">User</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs">Role</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-700 text-xs">Last Active</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-700 text-xs">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr
                v-for="user in filteredUsers"
                :key="user.id"
                class="hover:bg-slate-50 transition-colors"
              >
                <!-- Checkbox -->
                <td class="py-3 px-4">
                  <input
                    type="checkbox"
                    :checked="selectedUsers.includes(user.id)"
                    @change="toggleUserSelection(user.id)"
                    class="w-4 h-4 rounded border-slate-300 text-accent-pink-600 focus:ring-accent-pink-500 cursor-pointer"
                  />
                </td>

                <!-- User Name & Email -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-accent-pink-400 to-accent-pink-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-semibold text-slate-900 text-sm">{{ user.name }}</p>
                      <p class="text-xs text-slate-500">{{ user.email }}</p>
                    </div>
                  </div>
                </td>


                <!-- Role Badge -->
                <td class="py-3 px-4">
                  <span
                    :class="{
                      'bg-accent-pink-50 text-accent-pink-700': user.role_id === 1,
                      'bg-emerald-100 text-emerald-700': user.role_id === 2,
                      'bg-amber-100 text-amber-700': user.role_id === 3,
                    }"
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ getRoleName(user.role_id) }}
                  </span>
                </td>

                <!-- Last Active -->
                <td class="py-3 px-4 text-slate-600 text-sm">
                  {{ formatLastActive(user.last_login) }}
                </td>

                <!-- Status Pill -->
                <td class="py-3 px-4 text-center">
                  <span
                    :class="{
                      'bg-green-100/70 text-green-700 border border-green-200': parseInt(user.is_active) === 1,
                      'bg-slate-100/70 text-slate-600 border border-slate-200': parseInt(user.is_active) !== 1,
                    }"
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ parseInt(user.is_active) === 1 ? '● Active' : '○ Inactive' }}
                  </span>
                </td>
              </tr>

              <tr v-if="filteredUsers.length === 0">
                <td colspan="5" class="py-8 text-center text-slate-500 text-sm">
                  {{ searchQuery ? 'No users found matching your search.' : 'No users found.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create User Drawer -->
    <div v-if="showDrawer" class="fixed inset-0 z-50 flex">
      <!-- Backdrop -->
      <div @click="closeDrawer" class="flex-1 bg-black/30" />

      <!-- Drawer Panel -->
      <div class="w-96 bg-white shadow-2xl flex flex-col animate-in slide-in-from-right">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-900">Create New User</h2>
          <button
            @click="closeDrawer"
            class="text-slate-400 hover:text-slate-600"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
          <form @submit.prevent="handleCreateUser" class="space-y-5">
            <!-- Full Name -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name *</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="John Doe"
                class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-pink-500 focus:border-transparent outline-none text-sm transition-all"
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
                class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-pink-500 focus:border-transparent outline-none text-sm transition-all"
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
                class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-pink-500 focus:border-transparent outline-none text-sm transition-all"
                required
                minlength="8"
              />
              <p class="text-xs text-slate-500 mt-1">Minimum 8 characters</p>
            </div>

            <!-- Role Selection -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role *</label>
              <select
                v-model="form.role_id"
                class="w-full h-9 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-pink-500 focus:border-transparent outline-none text-sm transition-all bg-white"
                required
              >
                <option value="">Select a role</option>
                <option value="1">System Admin</option>
                <option value="2">Branch Manager</option>
                <option value="3">Sales User</option>
              </select>
            </div>

            <!-- Error Message -->
            <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs">
              {{ formError }}
            </div>

            <!-- Success Message -->
            <div v-if="formSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-xs flex items-center gap-2">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              {{ formSuccess }}
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center gap-3">
          <button
            @click="closeDrawer"
            class="flex-1 h-9 px-4 border border-slate-200 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition-colors text-sm"
          >
            Cancel
          </button>
          <button
            @click="handleCreateUser"
            :disabled="creating"
            class="flex-1 h-9 px-4 bg-accent-pink-500 hover:bg-accent-pink-600 disabled:bg-slate-300 text-white rounded-lg font-semibold transition-colors text-sm flex items-center justify-center gap-2"
          >
            <svg v-if="!creating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            {{ creating ? 'Creating...' : 'Create User' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import { userService } from '@/api/user.service'

// Store & Auth
const auth = useAuthStore()
const isAdmin = computed(() => auth.isAdmin)

// UI State
const showDrawer = ref(false)
const showBulkActionsMenu = ref(false)
const loading = ref(true)
const creating = ref(false)
const formError = ref('')
const formSuccess = ref('')
const searchQuery = ref('')

// Data
const users = ref([])
const selectedUsers = ref([])

// Form
const form = ref({
  name: '',
  email: '',
  password: '',
  role_id: '',
})

// Computed Properties
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  
  const query = searchQuery.value.toLowerCase()
  return users.value.filter(user => 
    user.name.toLowerCase().includes(query) || 
    user.email.toLowerCase().includes(query)
  )
})

// Methods
const getRoleName = (roleId) => {
  const roles = {
    1: 'System Admin',
    2: 'Branch Manager',
    3: 'Sales User',
  }
  return roles[roleId] || 'Unknown'
}

const calculateAge = (dateString) => {
  if (!dateString) return null
  const today = new Date()
  const birthDate = new Date(dateString)
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDiff = today.getMonth() - birthDate.getMonth()
  
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--
  }
  
  return age
}

const formatLastActive = (lastLogin) => {
  if (!lastLogin) return 'Never'
  
  const date = new Date(lastLogin)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}

// Drawer Methods
const openDrawer = () => {
  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
  resetForm()
}

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    password: '',
    role_id: '',
  }
  formError.value = ''
  formSuccess.value = ''
}

// Selection Methods
const toggleUserSelection = (userId) => {
  const index = selectedUsers.value.indexOf(userId)
  if (index > -1) {
    selectedUsers.value.splice(index, 1)
  } else {
    selectedUsers.value.push(userId)
  }
  showBulkActionsMenu.value = false
}

const toggleSelectAll = () => {
  if (selectedUsers.value.length === filteredUsers.value.length) {
    selectedUsers.value = []
  } else {
    selectedUsers.value = filteredUsers.value.map(user => user.id)
  }
}

// Bulk Action Methods
const bulkChangeStatus = async (isActive) => {
  try {
    const statusValue = isActive ? 1 : 0
    for (const userId of selectedUsers.value) {
      // Call API to update
      await userService.updateUser(userId, { is_active: statusValue })
      // Find user and update locally
      const user = users.value.find(u => u.id === userId)
      if (user) {
        user.is_active = statusValue
      }
    }
    selectedUsers.value = []
    showBulkActionsMenu.value = false
  } catch (err) {
    console.error('Bulk status update failed:', err)
    alert('Failed to update user status')
  }
}

const bulkDelete = async () => {
  if (!confirm(`Delete ${selectedUsers.value.length} user(s)? This cannot be undone.`)) {
    return
  }

  try {
    for (const userId of selectedUsers.value) {
      await userService.deleteUser(userId)
    }
    
    // Reload users
    await loadUsers()
    selectedUsers.value = []
    showBulkActionsMenu.value = false
  } catch (err) {
    console.error('Bulk delete failed:', err)
    alert('Failed to delete users')
  }
}

// Load & Create Methods
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

    // Prepare data
    const createData = {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      role_id: parseInt(form.value.role_id),
      is_active: 1, // New users are active by default
    }

    // Add date of birth if provided
    console.log('✅ User created:', newUser)

    formSuccess.value = `User "${newUser.name}" created successfully!`
    
    // Clear success message after 2 seconds and close drawer
    setTimeout(() => {
      formSuccess.value = ''
      closeDrawer()
    }, 2000)

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

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>
