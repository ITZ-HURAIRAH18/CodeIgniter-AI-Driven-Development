<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ t('branches.title') }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ t('branches.subtitle') }}</p>
      </div>
      <BaseButton variant="primary" :label="t('branches.addBranch')" @click="openCreate" />
    </div>

    <!-- Filter Bar -->
    <div class="bg-surface-elevated border border-gray-200 rounded-lg p-5 shadow-xs">
      <div class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            {{ t('common.search') }}
          </label>
          <input
            v-model="search"
            type="text"
            :placeholder="t('branches.searchPlaceholder')"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-accent-pink-500 focus:border-accent-pink-300 bg-white"
          />
        </div>
        <div class="flex-1 min-w-0">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            {{ t('common.status') }}
          </label>
          <select
            v-model="statusFilter"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500 bg-white"
          >
            <option value="">{{ t('common.all') }} {{ t('common.branches') }}</option>
            <option value="active">{{ t('common.active') }}</option>
            <option value="inactive">{{ t('common.inactive') }}</option>
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
        <p class="text-sm text-gray-500 mt-2">{{ t('common.loading') }}</p>
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
              <p class="text-xs text-gray-500 truncate">{{ branch.address || t('common.noAddress') }}</p>
            </div>
          </div>
          <Badge :label="branch.is_active ? t('common.active') : t('common.inactive')" :variant="branch.is_active ? 'success' : 'neutral'" size="sm" />
        </div>

        <!-- Branch Info -->
        <div class="space-y-3 mb-4 pb-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ t('branches.manager') }}</span>
            <span class="text-sm text-gray-900 font-medium">{{ branch.manager_name || '—' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ t('branches.phone') }}</span>
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
            {{ t('common.edit') }}
          </button>
          <button
            @click="deleteBranch(branch.id)"
            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-status-error/10 hover:text-status-error rounded-lg transition-colors"
          >
            <Trash2Icon :style="{ width: '16px', height: '16px' }" />
            {{ t('common.delete') }}
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="filteredBranches.length === 0"
        class="col-span-full flex flex-col items-center justify-center py-12 bg-surface-base border border-gray-100 rounded-xl"
      >
        <MapPinIcon :style="{ width: '48px', height: '48px', color: '#d1d5db', marginBottom: '16px' }" />
        <p class="text-sm font-medium text-gray-900">{{ t('branches.notFound') }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ search ? t('common.adjustFilters') : t('branches.createFirst') }}</p>
      </div>
    </div>

    <!-- Branch Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
      <div class="bg-surface-elevated rounded-xl shadow-2xl max-w-2xl w-full mx-auto border border-gray-200 my-8 flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-gray-100 bg-surface-base sticky top-0 z-10 rounded-t-xl">
          <h2 class="text-lg font-semibold text-gray-900">{{ editing ? t('branches.edit') : t('branches.new') }}</h2>
          <p class="text-sm text-gray-600 mt-1">{{ editing ? t('branches.updateDescription') : t('branches.createDescription') }}</p>
        </div>

        <div class="flex-1 overflow-y-auto">
          <div v-if="modalError" class="mx-6 mt-6 p-4 rounded-lg bg-status-error/10 border border-status-error/20">
            <p class="text-sm text-status-error">{{ modalError }}</p>
          </div>

          <form @submit.prevent="save" class="p-6 space-y-6">
          <!-- Multilingual Content -->
          <div class="space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Multilingual Content *</h3>
            <div class="grid grid-cols-1 gap-4">
              <div class="rounded-md border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-700 mb-3">English (EN)</p>
                <div class="space-y-3">
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('branches.name') }} *</label>
                    <input
                      v-model="form.translations.en.name"
                      type="text"
                      placeholder="Branch Name"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('common.description') }} *</label>
                    <textarea
                      v-model="form.translations.en.description"
                      placeholder="Branch description..."
                      rows="2"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 resize-none"
                    ></textarea>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('branches.address') }} *</label>
                    <input
                      v-model="form.translations.en.address"
                      type="text"
                      placeholder="Branch address..."
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                </div>
              </div>

              <div class="rounded-md border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-700 mb-3">Urdu (UR)</p>
                <div class="space-y-3">
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('branches.name') }} *</label>
                    <input
                      v-model="form.translations.ur.name"
                      type="text"
                      placeholder="شاخ کا نام"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('common.description') }} *</label>
                    <textarea
                      v-model="form.translations.ur.description"
                      placeholder="شاخ کی تفصیل"
                      rows="2"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 resize-none"
                    ></textarea>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('branches.address') }} *</label>
                    <input
                      v-model="form.translations.ur.address"
                      type="text"
                      placeholder="شاخ کا پتہ"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                </div>
              </div>

              <div class="rounded-md border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-700 mb-3">Chinese (ZH)</p>
                <div class="space-y-3">
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('branches.name') }} *</label>
                    <input
                      v-model="form.translations.zh.name"
                      type="text"
                      placeholder="分支名称"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('common.description') }} *</label>
                    <textarea
                      v-model="form.translations.zh.description"
                      placeholder="分支描述"
                      rows="2"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 resize-none"
                    ></textarea>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">{{ t('branches.address') }} *</label>
                    <input
                      v-model="form.translations.zh.address"
                      type="text"
                      placeholder="分支地址"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Location Info -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ t('branches.location') }}</h3>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">{{ t('branches.phone') }}</label>
              <input
                v-model="form.phone"
                type="tel"
                :placeholder="t('branches.placeholderPhone')"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              />
            </div>
          </div>

          <!-- Management -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ t('branches.management') }}</h3>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">{{ t('branches.manager') }}</label>
              <select
                v-model="form.manager_id"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              >
                <option :value="null">{{ t('branches.unassigned') }}</option>
                <!-- Show only managers (role_id = 2) -->
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ t('roles.branchManager') }})
                </option>
                <!-- Debug: Show if no managers found -->
                <option v-if="users.length === 0" disabled>
                  {{ t('branches.noManagersAvailable') }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">{{ t('common.status') }}</label>
              <select
                v-model="form.is_active"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              >
                <option :value="1">{{ t('common.active') }}</option>
                <option :value="0">{{ t('common.inactive') }}</option>
              </select>
            </div>
          </div>

          </form>
        </div>

        <!-- Sticky Actions Footer -->
        <div class="flex gap-3 p-6 border-t border-gray-200 bg-surface-base sticky bottom-0 rounded-b-xl">
          <BaseButton variant="ghost" :label="t('common.cancel')" @click="showModal = false" class="flex-1" />
          <BaseButton variant="primary" :label="t('branches.save')" type="submit" :loading="saving" class="flex-1" @click="save" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useI18n } from '@/composables/useI18n'
import api from '@/api/axios'
import BaseButton from '@/components/ui/BaseButton.vue'
import Badge from '@/components/ui/Badge.vue'
import { Edit2, Trash2, MapPin } from 'lucide-vue-next'

const { t, language } = useI18n()

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
const allUsers = ref([])

const form = reactive({
  name: '',
  phone: '',
  manager_id: null,
  is_active: 1,
  translations: {
    en: { name: '', description: '', address: '' },
    ur: { name: '', description: '', address: '' },
    zh: { name: '', description: '', address: '' },
  },
})

const filteredBranches = computed(() => {
  return branches.value.filter(b => {
    const matchSearch =
      !search.value ||
      b.name?.toLowerCase().includes(search.value.toLowerCase()) ||
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

const loadBranches = async () => {
  loading.value = true
  try {
    const { getCurrentLanguage } = useI18n()
    const currentLang = getCurrentLanguage()
    
    const [branchRes, userRes] = await Promise.all([
      api.get('/branches', { params: { lang: currentLang } }).catch(() => []),
      api.get('/users').catch(() => [])
    ])
    
    // Handle paginated responses
    branches.value = Array.isArray(branchRes.data) ? branchRes.data : (Array.isArray(branchRes) ? branchRes : [])
    
    // Get all users and filter for branch managers ONLY (role_id = 2, exclude sales users)
    const allUsersList = Array.isArray(userRes.data) ? userRes.data : (Array.isArray(userRes) ? userRes : [])
    allUsers.value = allUsersList
    
    // Log for debugging
    console.log('All users from API:', allUsers.value)
    
    users.value = allUsers.value.filter(u => {
      console.log('Checking user:', u.name, 'role_id:', u.role_id, 'role:', u.role)
      const roleId = u.role_id !== undefined ? u.role_id : u.role
      return roleId === 2 || roleId === '2'  // Only branch managers
    })
    
    console.log('Filtered managers:', users.value)
    console.log('Data loaded:', { branches: branches.value.length, users: users.value.length, managers: users.value.length })
  } catch (e) {
    console.error('Failed to load data:', e)
    branches.value = []
    users.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadBranches)
watch(language, loadBranches)

function openCreate() {
  editing.value = null
  modalError.value = ''
  Object.assign(form, {
    name: '',
    phone: '',
    manager_id: null,
    is_active: 1,
    translations: {
      en: { name: '', description: '', address: '' },
      ur: { name: '', description: '', address: '' },
      zh: { name: '', description: '', address: '' },
    },
  })
  showModal.value = true
}

function openEdit(b) {
  editing.value = b.id
  modalError.value = ''
  Object.assign(form, {
    name: b.name,
    phone: b.phone,
    manager_id: b.manager_id,
    is_active: b.is_active,
    translations: b.translations || {
      en: { name: b.name || '', description: '', address: '' },
      ur: { name: '', description: '', address: '' },
      zh: { name: '', description: '', address: '' },
    },
  })
  showModal.value = true
}

async function save() {
  saving.value = true
  modalError.value = ''
  try {
    const { getCurrentLanguage } = useI18n()
    const currentLang = getCurrentLanguage()
    
    // Ensure manager_id is a number
    const saveData = {
      name: form.name,
      phone: form.phone,
      manager_id: form.manager_id ? parseInt(form.manager_id, 10) : null,
      is_active: form.is_active ? 1 : 0,
      translations: form.translations,
    }
    
    console.log('Saving branch:', saveData)
    
    if (editing.value) {
      await api.put(`/branches/${editing.value}`, saveData)
    } else {
      await api.post('/branches', saveData)
    }
    const res = await api.get('/branches', { params: { lang: currentLang } })
    branches.value = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
    showModal.value = false
  } catch (e) {
    console.error('Save error:', e)
    modalError.value = e?.response?.data?.message || e?.message || 'Failed to save branch.'
  } finally {
    saving.value = false
  }
}

async function deleteBranch(id) {
  if (!confirm(t('branches.confirmDelete'))) return
  try {
    await api.delete(`/branches/${id}`)
    branches.value = branches.value.filter(b => b.id !== id)
  } catch (e) {
    console.error('Failed to delete branch:', e)
  }
}
</script>
