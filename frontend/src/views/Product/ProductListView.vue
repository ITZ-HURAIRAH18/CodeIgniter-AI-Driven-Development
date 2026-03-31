<template>
  <div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
      <div class="flex items-center gap-2 text-sm text-slate-500 font-medium mb-2">
        <span>Dashboard</span>
        <span class="text-slate-300">/</span>
        <span class="text-slate-900 font-semibold">Products</span>
      </div>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Product Catalog</h1>
          <p class="text-slate-500 text-sm mt-1">Manage your product inventory with pricing and tax</p>
        </div>
        <button v-if="auth.isAdmin" @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 h-10 bg-accent-pink-500 text-white rounded-lg font-medium hover:bg-accent-pink-600 transition-colors shadow-sm">
          <PlusIcon class="w-4 h-4" />
          <span>Add Product</span>
        </button>
      </div>
    </div>

    <!-- Compact Filter Bar -->
    <div class="bg-white border-b border-slate-200 px-6 py-3 flex items-center gap-4 justify-between">
      <div class="flex-1 max-w-sm">
        <div class="relative group">
          <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-rose-500 transition-colors" />
          <input
            v-model="search"
            type="text"
            placeholder="Search by name or SKU..."
            class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
          />
        </div>
      </div>

      <div class="flex items-center gap-3">
        <!-- Status Filter Chips -->
        <div class="flex items-center gap-2">
          <button
            @click="statusFilter = ''"
            :class="['px-3 py-1.5 rounded-full text-xs font-medium transition-colors', statusFilter === '' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
          >
            All
          </button>
          <button
            @click="statusFilter = 'active'"
            :class="['px-3 py-1.5 rounded-full text-xs font-medium transition-colors', statusFilter === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
          >
            Active
          </button>
          <button
            @click="statusFilter = 'inactive'"
            :class="['px-3 py-1.5 rounded-full text-xs font-medium transition-colors', statusFilter === 'inactive' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
          >
            Inactive
          </button>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="flex flex-col items-center gap-3">
          <div class="w-8 h-8 border-2 border-slate-200 border-t-rose-600 rounded-full animate-spin"></div>
          <p class="text-slate-500 text-sm">Loading products...</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredProducts.length === 0" class="flex items-center justify-center py-16 px-4">
        <div class="text-center">
          <PackageIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <p class="text-slate-600 font-medium text-sm">{{ search || statusFilter !== '' ? 'No products match your filters' : 'No products yet' }}</p>
          <p class="text-slate-500 text-xs mt-1">{{ search || statusFilter !== '' ? 'Try adjusting your search' : 'Create your first product to get started' }}</p>
          <button v-if="auth.isAdmin && (search || statusFilter !== '')" @click="search = ''; statusFilter = ''" class="inline-flex items-center gap-1 mt-4 px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 rounded-md transition-colors">
            <XIcon class="w-4 h-4" />
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Cost</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Sale Price</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Tax</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Margin</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr
              v-for="(product, idx) in filteredProducts"
              :key="product.id"
              :class="['transition-colors hover:bg-indigo-50/30 group', idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/30']"
            >
              <!-- Product Name & SKU -->
              <td class="px-6 py-3">
                <div>
                  <p class="text-sm font-bold text-slate-900">{{ product.name }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ product.sku }}</p>
                </div>
              </td>

              <!-- Cost Price -->
              <td class="px-6 py-3 text-right">
                <span class="text-sm font-mono text-slate-700">${{ Number(product.cost_price).toFixed(2) }}</span>
              </td>

              <!-- Sale Price -->
              <td class="px-6 py-3 text-right">
                <span class="text-sm font-mono font-semibold text-slate-900">${{ Number(product.sale_price).toFixed(2) }}</span>
              </td>

              <!-- Tax -->
              <td class="px-6 py-3 text-right">
                <span class="text-sm text-slate-700">{{ Number(product.tax_percentage).toFixed(1) }}%</span>
              </td>

              <!-- Margin Calculation -->
              <td class="px-6 py-3 text-right">
                <span :class="['text-sm font-semibold font-mono', getMarginColor(product)]">
                  {{ getMarginPercent(product) }}%
                </span>
              </td>

              <!-- Status -->
              <td class="px-6 py-3">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-medium', product.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700']">
                  <span :class="['w-1.5 h-1.5 rounded-full', product.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400']"></span>
                  {{ product.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <!-- Quick Actions -->
              <td class="px-6 py-3">
                <div v-if="auth.isAdmin" class="flex items-center gap-1 opacity-100">
                  <button
                    @click="openEdit(product)"
                    class="p-1.5 hover:bg-slate-200 rounded-md text-slate-600 hover:text-slate-900 transition-colors"
                    title="Edit product"
                  >
                    <Edit3Icon class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteProduct(product.id)"
                    class="p-1.5 hover:bg-red-100 rounded-md text-red-600 hover:text-red-700 transition-colors"
                    title="Delete product"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="filteredProducts.length > 0" class="px-6 py-3 border-t border-slate-200 flex items-center justify-between text-sm text-slate-600">
        <span>Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredProducts.length) }} of {{ filteredProducts.length }} products</span>
        <div class="flex items-center gap-2">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="p-1.5 hover:bg-slate-100 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronLeftIcon class="w-4 h-4" />
          </button>
          <button
            @click="currentPage = Math.min(Math.ceil(filteredProducts.length / itemsPerPage), currentPage + 1)"
            :disabled="currentPage >= Math.ceil(filteredProducts.length / itemsPerPage)"
            class="p-1.5 hover:bg-slate-100 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronRightIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Product Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showModal && auth.isAdmin" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4" @click.self="showModal = false">
          <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">{{ editing ? 'Edit Product' : 'Create New Product' }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ editing ? 'Update product information' : 'Add a new product to your catalog' }}</p>
              </div>
              <button @click="showModal = false" class="p-1 hover:bg-slate-100 rounded-md text-slate-600 transition-colors">
                <XIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
              <!-- Alert Messages -->
              <div v-if="modalError" class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ modalError }}
              </div>

              <form @submit.prevent="saveProduct" class="space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                  <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Basic Information</h3>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">SKU *</label>
                      <input
                        v-model="form.sku"
                        type="text"
                        placeholder="PROD-001"
                        required
                        :disabled="!!editing"
                        class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 disabled:bg-slate-50 disabled:text-slate-500"
                      />
                    </div>
                    <div>
                      <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Unit</label>
                      <input
                        v-model="form.unit"
                        type="text"
                        placeholder="pcs, kg, L"
                        class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Product Name *</label>
                    <input
                      v-model="form.name"
                      type="text"
                      placeholder="Premium Widget A"
                      required
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                    />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Description</label>
                    <textarea
                      v-model="form.description"
                      placeholder="Product description and details..."
                      rows="3"
                      class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 resize-none"
                    ></textarea>
                  </div>
                </div>

                <!-- Pricing & Tax -->
                <div class="space-y-4">
                  <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Pricing & Tax</h3>
                  <div class="grid grid-cols-3 gap-4">
                    <div>
                      <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Cost Price *</label>
                      <input
                        v-model.number="form.cost_price"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                      />
                    </div>
                    <div>
                      <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Sale Price *</label>
                      <input
                        v-model.number="form.sale_price"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                      />
                    </div>
                    <div>
                      <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Tax % *</label>
                      <input
                        v-model.number="form.tax_percentage"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        required
                        class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20"
                      />
                    </div>
                  </div>
                </div>

                <!-- Status -->
                <div class="space-y-4">
                  <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Status</h3>
                  <select
                    v-model="form.status"
                    class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer"
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </form>
            </div>

            <!-- Modal Footer -->
            <div class="sticky bottom-0 bg-white border-t border-slate-200 px-6 py-4 flex items-center justify-end gap-3">
              <button
                @click="showModal = false"
                class="px-4 py-2.5 h-10 bg-white border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="saveProduct"
                :disabled="saving"
                class="inline-flex items-center gap-2 px-4 py-2.5 h-10 bg-rose-600 text-white rounded-lg font-medium text-sm hover:bg-rose-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md"
              >
                <CheckCircle2Icon v-if="!saving" class="w-4 h-4" />
                <div v-else class="w-4 h-4 border-2 border-white border-t-rose-600 rounded-full animate-spin"></div>
                {{ editing ? 'Update Product' : 'Create Product' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import { PlusIcon, SearchIcon, PackageIcon, XIcon, Edit3Icon, TrashIcon, ChevronLeftIcon, ChevronRightIcon, CheckCircle2Icon } from 'lucide-vue-next'
import api from '@/api/axios'

const auth = useAuthStore()
const products = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const editing = ref(null)
const saving = ref(false)
const modalError = ref('')
const currentPage = ref(1)
const itemsPerPage = 10

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

const getMarginPercent = (product) => {
  if (!product.cost_price || product.sale_price <= product.cost_price) return 0
  return Math.round(((product.sale_price - product.cost_price) / product.cost_price) * 100)
}

const getMarginColor = (product) => {
  const margin = getMarginPercent(product)
  if (margin >= 40) return 'text-emerald-600'
  if (margin >= 20) return 'text-blue-600'
  if (margin > 0) return 'text-amber-600'
  return 'text-red-600'
}

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

function openEdit(product) {
  editing.value = product.id
  modalError.value = ''
  Object.assign(form, product)
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
