<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Branches</h1>
        <p class="page-subtitle">Manage branch locations and managers</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Add Branch</button>
    </div>

    <div v-if="loading" style="text-align:center;padding:40px"><span class="spinner"></span></div>
    <div v-else class="branches-grid">
      <div v-for="b in branches" :key="b.id" class="branch-card card">
        <div class="branch-header">
          <div class="branch-icon">◉</div>
          <div>
            <div class="branch-name">{{ b.name }}</div>
            <div class="text-muted text-sm">{{ b.address || 'No address' }}</div>
          </div>
          <span :class="b.is_active ? 'badge badge-success' : 'badge badge-danger'" style="margin-left:auto">
            {{ b.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        <div class="branch-meta">
          <div><span class="meta-label">Manager</span>{{ b.manager_name || 'Unassigned' }}</div>
          <div><span class="meta-label">Phone</span>{{ b.phone || '—' }}</div>
        </div>
        <div class="branch-actions">
          <button class="btn btn-secondary btn-sm" @click="openEdit(b)">Edit</button>
          <button class="btn btn-danger btn-sm" @click="deleteBranch(b.id)">Delete</button>
        </div>
      </div>
      <div v-if="branches.length === 0" class="empty-card card">
        No branches found. Create your first branch.
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <h2 class="modal-title">{{ editing ? 'Edit Branch' : 'New Branch' }}</h2>
        <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
        <form @submit.prevent="save">
          <div class="form-group">
            <label class="form-label">Branch Name *</label>
            <input v-model="form.name" class="form-control" required placeholder="Main Branch" />
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <input v-model="form.address" class="form-control" placeholder="123 Street, City" />
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input v-model="form.phone" class="form-control" placeholder="+1-000-000" />
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select v-model="form.is_active" class="form-control">
              <option :value="1">Active</option>
              <option :value="0">Inactive</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner"></span>
              {{ saving ? 'Saving...' : 'Save Branch' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api/axios'

const branches   = ref([])
const loading    = ref(true)
const showModal  = ref(false)
const editing    = ref(null)
const saving     = ref(false)
const modalError = ref('')

const form = reactive({ name: '', address: '', phone: '', is_active: 1 })

onMounted(async () => {
  const res = await api.get('/branches')
  branches.value = res.data || []
  loading.value  = false
})

function openCreate() {
  editing.value = null; modalError.value = ''
  Object.assign(form, { name:'', address:'', phone:'', is_active:1 })
  showModal.value = true
}
function openEdit(b) {
  editing.value = b.id; modalError.value = ''
  Object.assign(form, b)
  showModal.value = true
}

async function save() {
  saving.value = true; modalError.value = ''
  try {
    if (editing.value) await api.put(`/branches/${editing.value}`, form)
    else await api.post('/branches', form)
    const res = await api.get('/branches')
    branches.value = res.data || []
    showModal.value = false
  } catch (e) {
    modalError.value = e?.message || 'Save failed.'
  } finally { saving.value = false }
}

async function deleteBranch(id) {
  if (!confirm('Delete this branch? This will fail if it has active inventory.')) return
  try {
    await api.delete(`/branches/${id}`)
    branches.value = branches.value.filter(b => b.id !== id)
  } catch (e) { alert(e?.message || 'Delete failed.') }
}
</script>

<style scoped>
.page-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px; }
.page-title   { font-size:24px;font-weight:700;letter-spacing:-0.03em; }
.page-subtitle { color:var(--clr-text-muted);font-size:14px;margin-top:4px; }

.branches-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px; }

.branch-card { transition:all var(--trans-base); }
.branch-card:hover { transform:translateY(-2px);box-shadow:var(--shadow-md); }

.branch-header { display:flex;align-items:flex-start;gap:14px;margin-bottom:16px; }
.branch-icon { width:40px;height:40px;background:rgba(99,102,241,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--clr-accent-light);flex-shrink:0; }
.branch-name { font-size:16px;font-weight:600;margin-bottom:2px; }

.branch-meta { display:flex;flex-direction:column;gap:6px;font-size:14px;margin-bottom:16px; }
.meta-label { font-size:11px;text-transform:uppercase;color:var(--clr-text-muted);margin-right:8px;font-weight:600; }

.branch-actions { display:flex;gap:8px; }
.empty-card { text-align:center;color:var(--clr-text-muted);height:120px;display:flex;align-items:center;justify-content:center; }
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px); }
.modal-box { background:var(--clr-bg-surface);border:1px solid var(--clr-border);border-radius:var(--radius-xl);padding:32px;width:100%;max-width:480px; }
.modal-title { font-size:20px;font-weight:700;margin-bottom:20px; }
.modal-actions { display:flex;justify-content:flex-end;gap:12px;margin-top:20px; }
</style>
