<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Products</h1>
        <p class="text-sm text-gray-600 mt-1">Product catalog with pricing and tax information</p>
      </div>
      <BaseButton v-if="auth.isAdmin" variant="primary" @click="openCreate">+ Add Product</BaseButton>
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
            placeholder="Product name, SKU..."
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
            <option value="">All Products</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <BaseCard title="Product Catalog" :subtitle="`${filteredProducts.length} product${filteredProducts.length !== 1 ? 's' : ''}`">
      <div v-if="loading" class="flex items-center justify-center h-48">
        <div class="text-center">
          <div class="inline-flex items-center">
            <div class="w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <p class="text-sm text-gray-500 mt-2">Loading products...</p>
        </div>
      </div>
      <BaseTable
        v-else
        :columns="columns"
        :data="filteredProducts"
        noPadding
      >
        <template #cell-sku="{ value }">
          <span class="font-mono text-sm text-gray-700">{{ value }}</span>
        </template>

        <template #cell-name="{ value }">
          <span class="text-sm font-medium text-gray-900">{{ value }}</span>
        </template>

        <template #cell-cost_price="{ value }">
          <span class="text-sm text-gray-700">${{ Number(value).toFixed(2) }}</span>
        </template>

        <template #cell-sale_price="{ value }">
          <span class="text-sm font-semibold text-gray-900">${{ Number(value).toFixed(2) }}</span>
        </template>

        <template #cell-tax_percentage="{ value }">
          <span class="text-sm text-gray-700">{{ value }}%</span>
        </template>

        <template #cell-status="{ value }">
          <Badge :label="value" :variant="value === 'active' ? 'success' : 'neutral'" size="sm" />
        </template>

        <template v-if="auth.isAdmin" #actions="{ row }">
          <div class="flex items-center gap-2">
            <button
              @click="openEdit(row)"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-accent-pink-50 hover:text-accent-pink-600 rounded-md transition-colors"
            >
              <Edit2Icon :style="{ width: '16px', height: '16px' }" />
              Edit
            </button>
            <button
              @click="deleteProduct(row.id)"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-status-error/10 hover:text-status-error rounded-md transition-colors"
            >
              <Trash2Icon :style="{ width: '16px', height: '16px' }" />
              Delete
            </button>
          </div>
        </template>
      </BaseTable>

      <template v-if="filteredProducts.length === 0" #empty>
        <div class="flex flex-col items-center justify-center py-12">
          <PackageIcon :style="{ width: '48px', height: '48px', color: '#d1d5db', marginBottom: '16px' }" />
          <p class="text-sm font-medium text-gray-900">No products found</p>
          <p class="text-xs text-gray-500 mt-1">Try adjusting your filters</p>
        </div>
      </template>
    </BaseCard>

    <!-- Product Modal -->
    <div v-if="showModal && auth.isAdmin" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-surface-elevated rounded-xl shadow-lg max-w-2xl w-full mx-auto border border-gray-200 max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 bg-surface-base flex-shrink-0">
          <h2 class="text-lg font-semibold text-gray-900">{{ editing ? 'Edit Product' : 'New Product' }}</h2>
          <p class="text-sm text-gray-600 mt-1">{{ editing ? 'Update product information' : 'Create a new product catalog entry' }}</p>
        </div>

        <div class="overflow-y-auto flex-1">
          <div v-if="modalError" class="mx-6 mt-6 p-4 rounded-lg bg-status-error/10 border border-status-error/20">
            <p class="text-sm text-status-error">{{ modalError }}</p>
          </div>

          <form @submit.prevent="saveProduct" class="p-6 space-y-6">
            <!-- Basic Info -->
            <div class="space-y-4">
              <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Basic Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">SKU *</label>
                  <input
                    v-model="form.sku"
                    type="text"
                    placeholder="PROD-001"
                    required
                    :disabled="!!editing"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500 disabled:bg-gray-50 disabled:text-gray-500"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Unit</label>
                  <input
                    v-model="form.unit"
                    type="text"
                    placeholder="pcs, kg, L..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Product Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Widget A"
                  required
                  class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Description</label>
                <textarea
                  v-model="form.description"
                  placeholder="Optional product description"
                  rows="3"
                  class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
                ></textarea>
              </div>
            </div>

            <!-- Pricing -->
            <div class="space-y-4">
              <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Pricing & Tax</h3>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Cost Price *</label>
                  <input
                    v-model.number="form.cost_price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Sale Price *</label>
                  <input
                    v-model.number="form.sale_price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Tax % *</label>
                  <input
                    v-model.number="form.tax_percentage"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
                  />
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="space-y-4">
              <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Status</h3>
              <select
                v-model="form.status"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent-pink-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>

        <!-- Sticky Footer Actions -->
        <div class="px-6 py-4 border-t border-gray-200 bg-surface-base flex-shrink-0 flex gap-3">
          <BaseButton variant="ghost" @click="showModal = false" class="flex-1">Cancel</BaseButton>
          <BaseButton variant="primary" :loading="saving" class="flex-1" @click="saveProduct">Save Product</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import api from '@/api/axios'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import Badge from '@/components/ui/Badge.vue'
import { Edit2, Trash2, Package } from 'lucide-vue-next'

const Edit2Icon = Edit2
const Trash2Icon = Trash2
const PackageIcon = Package

const auth = useAuthStore()
const products = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const editing = ref(null)
const saving = ref(false)
const modalError = ref('')

const form = reactive({
  sku: '',
  name: '',
  description: '',
  cost_price: 0,
  sale_price: 0,
  tax_percentage: 0,
  unit: 'pcs',
  status: 'active',
})

const columns = [
  { key: 'sku', label: 'SKU' },
  { key: 'name', label: 'Product Name' },
  { key: 'cost_price', label: 'Cost' },
  { key: 'sale_price', label: 'Sale Price' },
  { key: 'tax_percentage', label: 'Tax %' },
  { key: 'status', label: 'Status' },
]

const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchSearch =
      !search.value ||
      p.name?.toLowerCase().includes(search.value.toLowerCase()) ||
      p.sku?.toLowerCase().includes(search.value.toLowerCase())

    const matchStatus = !statusFilter.value || p.status === statusFilter.value

    return matchSearch && matchStatus
  })
})

onMounted(async () => {
  try {
    const res = await api.get('/products')
    products.value = res || []
  } catch (e) {
    console.error('Failed to load products:', e)
  } finally {
    loading.value = false
  }
})

function openCreate() {
  editing.value = null
  modalError.value = ''
  Object.assign(form, {
    sku: '',
    name: '',
    description: '',
    cost_price: 0,
    sale_price: 0,
    tax_percentage: 0,
    unit: 'pcs',
    status: 'active',
  })
  showModal.value = true
}

function openEdit(p) {
  editing.value = p.id
  modalError.value = ''
  Object.assign(form, p)
  showModal.value = true
}

async function saveProduct() {
  saving.value = true
  modalError.value = ''
  try {
    if (editing.value) {
      await api.put(`/products/${editing.value}`, form)
    } else {
      await api.post('/products', form)
    }
    const res = await api.get('/products')
    products.value = res || []
    showModal.value = false
  } catch (e) {
    modalError.value = e?.message || 'Failed to save product.'
  } finally {
    saving.value = false
  }
}

async function deleteProduct(id) {
  if (!confirm('Are you sure you want to delete this product?')) return
  try {
    await api.delete(`/products/${id}`)
    products.value = products.value.filter(p => p.id !== id)
  } catch (e) {
    console.error('Failed to delete product:', e)
  }
}
</script>
