<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Branches</h1>
        <p class="text-sm text-gray-600 mt-1">Manage branch locations and assigned managers</p>
      </div>
      <BaseButton variant="primary" label="+ Add Branch" @click="openCreate" />
    </div>

    <!-- Filter Bar -->
    <div class="bg-surface-elevated border border-gray-200 rounded-lg p-5 shadow-xs">
      <div class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            Search
          </label>
          <input
            v-model="search"
            type="text"
            placeholder="Branch name, city, manager..."
            class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-accent-pink-500 focus:border-accent-pink-300 bg-white"
          />
        </div>
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            Status
          </label>
          <select
            v-model="statusFilter"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500 bg-white"
          >
            <option value="">All Branches</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Branches Grid -->
    <div v-if="loading" class="flex items-center justify-center h-48">
      <div class="text-center">
        <div class="inline-flex items-center">
          <div class="w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">Loading branches...</p>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="branch in filteredBranches"
        :key="branch.id"
        class="bg-surface-elevated border border-gray-200 rounded-xl p-6 shadow-xs hover:shadow-sm transition-all duration-200"
      >
        <!-- Branch Header -->
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-start gap-3 flex-1">
            <div class="w-10 h-10 rounded-lg bg-accent-pink-100 flex items-center justify-center flex-shrink-0">
              <MapPin :style="{ width: '20px', height: '20px', color: '#e75ab8' }" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-semibold text-gray-900 truncate">{{ branch.name }}</h3>
              <p class="text-xs text-gray-500 truncate">{{ branch.address || 'No address' }}</p>
            </div>
          </div>
          <Badge :label="branch.is_active ? 'Active' : 'Inactive'" :variant="branch.is_active ? 'success' : 'neutral'" size="sm" />
        </div>

        <!-- Branch Info -->
        <div class="space-y-3 mb-4 pb-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Manager</span>
            <span class="text-sm text-gray-900 font-medium">{{ branch.manager_name || '—' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Phone</span>
            <span class="text-sm text-gray-900 font-mono">{{ branch.phone || '—' }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <button
            @click="openEdit(branch)"
            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-accent-pink-50 hover:text-accent-pink-600 rounded-lg transition-colors"
          >
            <Edit2Icon :style="{ width: '16px', height: '16px' }" />
            Edit
          </button>
          <button
            @click="deleteBranch(branch.id)"
            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-status-error/10 hover:text-status-error rounded-lg transition-colors"
          >
            <Trash2Icon :style="{ width: '16px', height: '16px' }" />
            Delete
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="filteredBranches.length === 0"
        class="col-span-full flex flex-col items-center justify-center py-12 bg-surface-base border border-gray-100 rounded-xl"
      >
        <MapPinIcon :style="{ width: '48px', height: '48px', color: '#d1d5db', marginBottom: '16px' }" />
        <p class="text-sm font-medium text-gray-900">No branches found</p>
        <p class="text-xs text-gray-500 mt-1">{{ search ? 'Try adjusting your filters' : 'Create your first branch to get started' }}</p>
      </div>
    </div>

    <!-- Branch Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-surface-elevated rounded-xl shadow-lg max-w-2xl w-full mx-auto border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 bg-surface-base">
          <h2 class="text-lg font-semibold text-gray-900">{{ editing ? 'Edit Branch' : 'New Branch' }}</h2>
          <p class="text-sm text-gray-600 mt-1">{{ editing ? 'Update branch information' : 'Create a new branch location' }}</p>
        </div>

        <div v-if="modalError" class="mx-6 mt-6 p-4 rounded-lg bg-status-error/10 border border-status-error/20">
          <p class="text-sm text-status-error">{{ modalError }}</p>
        </div>

        <form @submit.prevent="save" class="p-6 space-y-6">
          <!-- Location Info -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Location</h3>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Branch Name *</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Main Branch"
                required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Address</label>
              <input
                v-model="form.address"
                type="text"
                placeholder="123 Street, City, Country"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                placeholder="+1-000-000-0000"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              />
            </div>
          </div>

          <!-- Management -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Management</h3>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Branch Manager</label>
              <select
                v-model="form.manager_id"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              >
                <option :value="null">— Unassigned —</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Status</label>
              <select
                v-model="form.is_active"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              >
                <option :value="1">Active</option>
                <option :value="0">Inactive</option>
              </select>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-gray-200">
            <BaseButton variant="ghost" label="Cancel" @click="showModal = false" class="flex-1" />
            <BaseButton variant="primary" label="Save Branch" type="submit" :loading="saving" class="flex-1" />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '@/api/axios'
import BaseButton from '@/components/ui/BaseButton.vue'
import Badge from '@/components/ui/Badge.vue'
import { Edit2, Trash2, MapPin } from 'lucide-vue-next'

const Edit2Icon = Edit2
const Trash2Icon = Trash2
const MapPinIcon = MapPin

const branches = ref([])
const users = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const editing = ref(null)
const saving = ref(false)
const modalError = ref('')

const form = reactive({
  name: '',
  address: '',
  phone: '',
  manager_id: null,
  is_active: 1,
})

const filteredBranches = computed(() => {
  return branches.value.filter(b => {
    const matchSearch =
      !search.value ||
      b.name?.toLowerCase().includes(search.value.toLowerCase()) ||
      b.address?.toLowerCase().includes(search.value.toLowerCase()) ||
      b.manager_name?.toLowerCase().includes(search.value.toLowerCase())

    let matchStatus = true
    if (statusFilter.value === 'active') {
      matchStatus = b.is_active === 1 || b.is_active === true
    } else if (statusFilter.value === 'inactive') {
      matchStatus = b.is_active === 0 || b.is_active === false
    }

    return matchSearch && matchStatus
  })
})

onMounted(async () => {
  loading.value = true
  try {
    const [branchRes, userRes] = await Promise.all([
      api.get('/branches').catch(() => []),
      api.get('/users').catch(() => [])
    ])
    branches.value = Array.isArray(branchRes) ? branchRes : []
    users.value = Array.isArray(userRes) ? userRes.filter(u => u.role >= 2) : []
  } catch (e) {
    console.error('Failed to load data:', e)
    branches.value = []
    users.value = []
  } finally {
    loading.value = false
  }
})

function openCreate() {
  editing.value = null
  modalError.value = ''
  Object.assign(form, {
    name: '',
    address: '',
    phone: '',
    manager_id: null,
    is_active: 1,
  })
  showModal.value = true
}

function openEdit(b) {
  editing.value = b.id
  modalError.value = ''
  Object.assign(form, b)
  showModal.value = true
}

async function save() {
  saving.value = true
  modalError.value = ''
  try {
    if (editing.value) {
      await api.put(`/branches/${editing.value}`, form)
    } else {
      await api.post('/branches', form)
    }
    const res = await api.get('/branches')
    branches.value = res || []
    showModal.value = false
  } catch (e) {
    modalError.value = e?.message || 'Failed to save branch.'
  } finally {
    saving.value = false
  }
}

async function deleteBranch(id) {
  if (!confirm('Are you sure you want to delete this branch?')) return
  try {
    await api.delete(`/branches/${id}`)
    branches.value = branches.value.filter(b => b.id !== id)
  } catch (e) {
    console.error('Failed to delete branch:', e)
  }
}
</script>
