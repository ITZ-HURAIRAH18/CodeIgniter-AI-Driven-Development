<template>
  <div class="space-y-6">
    <!-- Page Header Section -->
    <div class="bg-white border-b border-slate-200 -mx-6 px-6 py-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">{{ t('inventory.title') }}</h1>
          <p class="text-slate-600 text-sm mt-1">{{ t('inventory.subtitle') }}</p>
        </div>
        <!-- <div class="text-right">
          <p class="text-xs text-slate-500">Last updated</p>
          <p class="text-sm font-medium text-slate-900">{{ lastUpdated }}</p>
        </div> -->
      </div>
    </div>

    <!-- Compact Filter/Toolbar Bar -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
      <div class="flex flex-col md:flex-row gap-4 items-end md:items-center justify-between">
        <!-- Left: Filters -->
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto flex-1">
          <!-- Branch Selector -->
          <div v-if="auth.isAdmin || (auth.isBranchManager && branches.length > 0)" class="w-full md:w-56">
            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2 block">{{ t('inventory.node') }}</label>
            <select 
              v-model="selectedBranchId"
              class="w-full px-3 py-2 h-9 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer appearance-none transition-all hover:border-slate-300 shadow-sm"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%236b7280%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.5rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2rem'"
            >
              <option value="">All Nodes</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          
          <!-- Search -->
          <div class="w-full md:flex-1">
            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2 block">{{ t('common.search') }}</label>
            <div class="relative group">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-rose-500 transition-colors" />
              <input 
                v-model="search" 
                type="text"
                :placeholder="t('inventory.searchPlaceholder')" 
                class="w-full pl-10 pr-4 py-2 h-9 bg-white border border-slate-200 rounded-md text-sm text-slate-900 placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 transition-all shadow-sm"
              />
            </div>
          </div>
        </div>

        <!-- Right: Action buttons -->
        <div v-if="canManage" class="flex items-center gap-2 flex-wrap">
          <!-- <button @click="exportData" class="inline-flex items-center justify-center w-9 h-9 text-slate-600 bg-slate-50 border border-slate-200 rounded-md hover:bg-slate-100 hover:border-slate-300 transition-colors shadow-sm" title="Export data">
            <DownloadIcon class="w-4 h-4" />
          </button> -->
          <!-- <button class="inline-flex items-center justify-center w-9 h-9 text-slate-600 bg-slate-50 border border-slate-200 rounded-md hover:bg-slate-100 hover:border-slate-300 transition-colors shadow-sm" title="Filter options">
            <FilterIcon class="w-4 h-4" />
          </button> -->
          <div class="w-px h-5 bg-slate-200"></div>
          <button @click="openAdjustModal" class="inline-flex items-center gap-2 px-3 py-2 h-9 text-sm font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-md hover:bg-slate-100 hover:border-slate-300 transition-colors shadow-sm">
            <Settings2Icon class="w-4 h-4" />
            {{ t('inventory.adjust') }}
          </button>
          <button @click="showTransferModal = true" class="inline-flex items-center gap-2 px-3 py-2 h-9 text-sm font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-md hover:bg-slate-100 hover:border-slate-300 transition-colors shadow-sm">
            <ArrowRightLeft class="w-4 h-4" />
            {{ t('inventory.transfer') }}
          </button>
          <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2 h-9 text-sm font-semibold text-white bg-accent-pink-500 border border-accent-pink-600 rounded-md hover:bg-accent-pink-600 hover:border-accent-pink-700 transition-colors shadow-sm">
            <PlusIcon class="w-4 h-4" />
            {{ t('inventory.replenish') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Enterprise Data Table -->
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
      <div v-if="filteredInventory.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <!-- Table Header -->
          <thead class="bg-slate-50 border-b border-slate-200 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">{{ t('inventory.product') }}</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">{{ t('inventory.stockLevel') }}</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">{{ t('inventory.unitPrice') }}</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">{{ t('inventory.status') }}</th>
              <th v-if="canManage" class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          
          <!-- Table Body -->
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(row, idx) in filteredInventory" :key="row.id" class="hover:bg-slate-50/80 transition-colors duration-150">
              <!-- Product Column -->
              <td class="px-6 py-3 whitespace-nowrap">
                <div class="flex flex-col">
                  <span class="font-semibold text-slate-900 text-sm">{{ row.product_name || 'Unknown' }}</span>
                  <span class="text-xs text-slate-500 font-medium">{{ row.sku || 'N/A' }}</span>
                </div>
              </td>
              
              <!-- Stock Level with Progress Bar -->
              <td class="px-6 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex-1 min-w-[140px]">
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                      <div 
                        :class="['h-full rounded-full transition-all duration-300', getStockBarColor(row.quantity, row.reorder_level)]" 
                        :style="{ width: Math.min((row.quantity / (row.reorder_level * 2)) * 100, 100) + '%' }">
                      </div>
                    </div>
                  </div>
                  <span class="text-sm font-bold text-slate-900 tabular-nums w-12 text-right">{{ row.quantity || 0 }}</span>
                </div>
              </td>
              
              <!-- Unit Price Column -->
              <td class="px-6 py-3 text-right">
                <span class="text-sm font-semibold text-slate-900 tabular-nums">${{ row.sale_price && !isNaN(row.sale_price) ? parseFloat(row.sale_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00' }}</span>
              </td>
              
              <!-- Status Badge Column (Replaces old badge) -->
              <td class="px-6 py-3">
                <span
                  :class="[
                    'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium',
                    getStatusVariantClasses(row.quantity, row.reorder_level)
                  ]"
                >
                  <span 
                    :class="['w-2 h-2 rounded-full', getStatusDotColor(row.quantity, row.reorder_level)]"
                  ></span>
                  {{ getStatusLabel(row.quantity, row.reorder_level) }}
                </span>
              </td>
              
              <!-- Actions Column -->
              <td v-if="canManage" class="px-6 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button 
                    @click="openAdjustModal(row)" 
                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-md transition-colors" 
                    title="Adjust"
                  >
                    <Edit3Icon class="w-4 h-4" />
                  </button>
                  <button 
                    @click="openLogsModal(row)" 
                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-md transition-colors" 
                    title="History"
                  >
                    <HistoryIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Empty State -->
      <div v-else class="flex items-center justify-center py-16 px-4">
        <div class="text-center">
          <ActivityIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <p class="text-slate-600 font-medium text-sm">{{ t('inventory.noItems') }}</p>
          <p class="text-slate-500 text-xs mt-1">{{ t('inventory.startReplenish') }}</p>
        </div>
      </div>
      
      <!-- Table Footer -->
      <div v-if="filteredInventory.length > 0" class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-sm text-slate-600">
        <div>
          {{ t('inventory.showing') }} <span class="font-medium text-slate-900">{{ filteredInventory.length }}</span> {{ t('inventory.of') }} <span class="font-medium text-slate-900">{{ inventory.length }}</span> {{ t('inventory.itemCount') }}
        </div>
      </div>
    </div>

      <!-- Replenish Stock Modal -->
    <Modal :show="showAddModal" :title="t('inventory.replenishTitle')" maxWidth="lg" @close="showAddModal = false">
      <form @submit.prevent="submitAddStock" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ t('inventory.targetNode') }} *</label>
            <select 
              v-model="addForm.branch_id"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>{{ t('inventory.selectBranch') }}</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ t('inventory.resourceUnit') }} *</label>
            <select 
              v-model="addForm.product_id"
              :disabled="!addForm.branch_id"
              required
              class="w-full px-4 py-2.5 rounded-md text-sm font-normal bg-white border border-gray-200 text-gray-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 cursor-pointer appearance-none"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>{{ t('inventory.selectProduct') }}</option>
              <option v-for="p in filteredProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
            </select>
          </div>
        </div>
        <div v-if="addForm.branch_id && addForm.product_id">
          <div class="space-y-1.5 mb-4">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.quantity') }} *</label>
            <div class="relative">
              <PackagePlusIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input v-model.number="addForm.quantity" type="number" min="1" required :placeholder="t('inventory.enterQuantity')" class="w-full pl-10 pr-4 py-2 rounded-md text-lg font-semibold text-slate-900 bg-white border border-slate-200 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 shadow-sm" />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('common.notes') }} ({{ t('common.optional') }})</label>
            <input v-model="addForm.notes" type="text" :placeholder="t('inventory.replenishReason')" class="w-full px-4 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 shadow-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
          <button type="button" @click="showAddModal = false" class="px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-md transition-colors">
            {{ t('common.cancel') }}
          </button>
          <button type="submit" :disabled="submitting" class="px-4 py-2 bg-accent-pink-500 text-white font-medium rounded-md hover:bg-accent-pink-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
            {{ submitting ? t('common.processing') : t('inventory.replenishButton') }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Adjust Stock Modal -->
    <Modal :show="showAdjustModal" :title="t('inventory.adjustTitle') + ': ' + (editingItem?.product_name || t('common.product'))" maxWidth="md" @close="showAdjustModal = false">
      <form @submit.prevent="submitAdjustStock" class="space-y-6">
        <!-- <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg flex items-start gap-3">
           <AlertTriangleIcon class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
           <div class="text-xs font-semibold text-amber-900 uppercase tracking-tight leading-relaxed">
              All stock adjustments are logged for audit purposes.
           </div>
        </div> -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.node') }} *</label>
            <select 
              v-model="editingItem.branch_id"
              required
              class="w-full px-3 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer appearance-none transition-all shadow-sm"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>{{ t('inventory.selectBranch') }}</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.product') }} ({{ t('common.sku') }})</label>
            <select 
              v-model="editingItem.product_id"
              :disabled="!editingItem.branch_id"
              required
              class="w-full px-3 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 disabled:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 cursor-pointer appearance-none transition-all shadow-sm"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>{{ t('inventory.selectProduct') }}</option>
              <option v-for="p in filteredProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
            </select>
          </div>
        </div>
        <div v-if="editingItem.branch_id && editingItem.product_id">
          <div class="space-y-1.5 mb-4">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.setStockLevel') }} *</label>
            <input v-model.number="adjustForm.quantity" type="number" min="0" required :placeholder="t('inventory.enterQuantity')" class="w-full px-4 py-3 rounded-md text-2xl font-bold text-rose-700 bg-rose-50 border border-rose-200 focus:outline-none focus:border-rose-600 focus:ring-1 focus:ring-rose-500/20 shadow-sm" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.reason') }} *</label>
            <input v-model="adjustForm.notes" type="text" :placeholder="t('inventory.reasonPlaceholder')" required class="w-full px-4 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 shadow-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
          <button type="button" @click="showAdjustModal = false" class="px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-md transition-colors">
            {{ t('common.cancel') }}
          </button>
          <button type="submit" :disabled="submitting" class="px-4 py-2 bg-accent-pink-500 text-white font-medium rounded-md hover:bg-accent-pink-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
            {{ submitting ? t('inventory.adjusting') : t('inventory.confirmAdjustment') }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Transfer Stock Modal -->
    <Modal :show="showTransferModal" :title="t('inventory.transferTitle')" maxWidth="md" @close="showTransferModal = false">
      <form @submit.prevent="submitTransferStock" class="space-y-6">
        <!-- <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg flex items-start gap-3">
           <AlertTriangleIcon class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
           <div class="text-xs font-semibold text-blue-900 uppercase tracking-tight leading-relaxed">
              Transfer stock from one node to another. All transfers are logged.
           </div>
        </div> -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.fromNode') }} *</label>
            <select 
              v-model="transferForm.from_branch_id"
              required
              class="w-full px-3 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer appearance-none transition-all shadow-sm"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>{{ t('inventory.selectSource') }}</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.toNode') }} *</label>
            <select 
              v-model="transferForm.to_branch_id"
              required
              class="w-full px-3 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 cursor-pointer appearance-none transition-all shadow-sm"
              style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
            >
              <option value="" disabled selected>{{ t('inventory.selectDestination') }}</option>
              <option v-for="b in filteredBranches" :key="b.id" :value="b.id" :disabled="b.id == transferForm.from_branch_id">{{ b.name }}</option>
            </select>
          </div>
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.product') }} ({{ t('common.sku') }}) *</label>
          <select 
            v-model="transferForm.product_id"
            :disabled="!transferForm.from_branch_id"
            required
              class="w-full px-3 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 disabled:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 cursor-pointer appearance-none transition-all shadow-sm"
            style="backgroundImage: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2712%27 height=%278%27 viewBox=%220 0 12 8%22><path fill=%22%234b5563%22 d=%22M6 6L1 1h10z%22/></svg>'), backgroundRepeat: 'no-repeat', backgroundPosition: 'right 0.75rem center', backgroundSize: '1.2em 1.2em', paddingRight: '2.5rem'"
          >
            <option value="" disabled selected>{{ t('inventory.selectProduct') }}</option>
            <option v-for="p in allProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ t('inventory.quantity') }} *</label>
          <input v-model.number="transferForm.quantity" type="number" min="1" required :placeholder="t('inventory.quantityToTransfer')" class="w-full px-4 py-2 rounded-md text-sm bg-white border border-slate-200 text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20 shadow-sm" />
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
          <button type="button" @click="showTransferModal = false" class="px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-md transition-colors">
            {{ t('common.cancel') }}
          </button>
          <button type="submit" :disabled="submitting" class="px-4 py-2 bg-accent-pink-500 text-white font-medium rounded-md hover:bg-accent-pink-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
            {{ submitting ? t('common.processing') : t('inventory.initiateTransfer') }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Stock History Modal -->
    <Modal :show="showLogsModal" :title="t('inventory.history') + ': ' + (currentLogItem?.product_name || t('common.all'))" maxWidth="lg" @close="showLogsModal = false">
      <div class="space-y-4">
        <div v-if="loadingLogs" class="flex justify-center py-8">
          <div class="text-sm text-slate-600">{{ t('inventory.loadingStockHistory') }}</div>
        </div>
        <div v-else-if="stockLogs.length === 0" class="flex justify-center py-8">
          <div class="text-sm text-slate-600">{{ t('inventory.noStockMovements') }}</div>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b-2 border-slate-200 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left font-bold text-slate-600">{{ t('common.dateTime') }}</th>
                <th class="px-4 py-3 text-left font-bold text-slate-600">{{ t('inventory.movementType') }}</th>
                <th class="px-4 py-3 text-center font-bold text-slate-600 w-24">{{ t('inventory.change') }}</th>
                <th class="px-4 py-3 text-left font-bold text-slate-600">{{ t('common.branch') }}</th>
                <th class="px-4 py-3 text-left font-bold text-slate-600">{{ t('common.notes') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="log in stockLogs" :key="log.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-2.5 text-slate-700 whitespace-nowrap">
                  <div class="font-medium">{{ formatDate(log.created_at) }}</div>
                </td>
                <td class="px-4 py-2.5">
                  <Badge :label="getLogTypeLabel(log.transaction_type)" :variant="getLogBadgeVariant(log.transaction_type)" size="sm" class="font-bold uppercase scale-90 origin-left" />
                </td>
                <td class="px-4 py-2.5 text-center font-mono font-bold text-slate-900">
                  <span :class="getQuantityClass(log.quantity_change)">{{ log.quantity_change || 0 }}</span>
                </td>
                <td class="px-4 py-2.5 text-slate-700 font-medium">{{ log.branch_name || 'N/A' }}</td>
                <td class="px-4 py-2.5 text-slate-600">
                  <span class="text-xs inline-block max-w-xs truncate" :title="log.notes">{{ log.notes || '-' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useAuthStore } from '@/store/auth.store'
import { useI18n } from '@/composables/useI18n'
import api from '@/api/axios'
import { 
  PlusIcon, SearchIcon, ActivityIcon, Edit3Icon, Settings2Icon, 
  HistoryIcon, PackagePlusIcon, AlertTriangleIcon, ArrowRightLeft,
  FilterIcon, DownloadIcon
} from 'lucide-vue-next'

import Card from '@/components/ui/Card.vue'
import Badge from '@/components/ui/Badge.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Select from '@/components/ui/Select.vue'

const auth = useAuthStore()
const { t } = useI18n()
const branches = ref([])
const inventory = ref([])
const allProducts = ref([])
const loading = ref(true)
const submitting = ref(false)
const search  = ref('')
// For sales users, pre-fill with their branch; for managers/admins, leave empty to select
const selectedBranchId = ref(auth.isSalesUser ? (auth.userBranchId || '') : '')

const showAddModal    = ref(false)
const showAdjustModal = ref(false)
const showTransferModal = ref(false)
const showLogsModal   = ref(false)
const editingItem     = ref(null)
const currentLogItem  = ref(null)

const addForm = reactive({
  // Sales users: pre-fill branch; Managers/Admins: leave empty to select
  branch_id: auth.isSalesUser ? (auth.userBranchId || '') : '',
  product_id: '',
  quantity: 1,
  notes: ''
})

const adjustForm = reactive({
  quantity: 0,
  notes: ''
})

const transferForm = reactive({
  from_branch_id: '',
  to_branch_id: '',
  product_id: '',
  quantity: 1
})

const stockLogs = ref([])
const loadingLogs = ref(false)
const lastUpdated = ref('Just now')

const canManage = computed(() => auth.isAdmin || auth.isBranchManager)

const filteredInventory = computed(() => {
  if (!search.value) return inventory.value
  const q = search.value.toLowerCase()
  return inventory.value.filter(i =>
    i.product_name?.toLowerCase().includes(q) || 
    i.sku?.toLowerCase().includes(q) || 
    i.category_name?.toLowerCase().includes(q)
  )
})

const filteredBranches = computed(() => {
  if (auth.isAdmin) return branches.value
  // Branch managers see only branches they manage
  // The API already filters branches for managers, so just return all loaded branches
  if (auth.isBranchManager) {
    console.log('Manager branches:', branches.value)
    return branches.value
  }
  return []
})

const filteredProducts = computed(() => {
  // Only show products if a branch is selected in the form
  if (!addForm.branch_id && !(editingItem.value && editingItem.value.branch_id)) return []
  return allProducts.value
})

const branchOptions = computed(() => filteredBranches.value.map(b => ({ value: b.id, label: b.name })))
const productOptions = computed(() => filteredProducts.value.map(p => ({ value: p.id, label: p.name + ' (' + p.sku + ')' })))

watch(
  () => addForm.branch_id,
  (val) => {
    if (!val) addForm.product_id = ''
  }
)

watch(
  () => editingItem?.branch_id,
  (val) => {
    if (!val && editingItem) editingItem.product_id = ''
  }
)

watch(
  () => selectedBranchId.value,
  () => {
    loadInventory()
  }
)

// Auto-populate current stock level when branch and product are selected in adjust modal
watch(
  () => ({ branch_id: editingItem.value?.branch_id, product_id: editingItem.value?.product_id }),
  ({ branch_id, product_id }) => {
    if (branch_id && product_id) {
      // Find the inventory item matching the selected branch and product
      const currentItem = inventory.value.find(
        item => item.branch_id === branch_id && item.product_id === product_id
      )
      // Set the current stock level in the form
      adjustForm.quantity = currentItem?.quantity || 0
    }
  },
  { deep: true }
)

onMounted(async () => {
  // Load branches and products first, then inventory
  await Promise.all([
    loadBranches(),
    loadProducts()
  ])
  
  // Then load inventory after products are available for joining
  await loadInventory()
})

async function loadBranches() {
  if (auth.isAdmin || auth.isBranchManager) {
    try {
      const res = await api.get('/branches')
      
      // Handle both paginated and direct array responses
      const branchesData = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
      branches.value = branchesData
      
      console.log('Branches loaded:', branches.value.length, 'branches', branches.value)
      
      // Initialize selectedBranchId based on user role
      if (!selectedBranchId.value && branches.value.length > 0) {
        if (auth.isBranchManager) {
          // Branch manager - auto-select their first branch
          selectedBranchId.value = branches.value[0].id
          console.log('Branch manager assigned to branch:', selectedBranchId.value)
        } else if (auth.isAdmin) {
          // Admin - select first branch
          selectedBranchId.value = branches.value[0].id
        }
      }
      
      // Initialize add form with selected branch
      if (!addForm.branch_id && branches.value.length > 0) {
        addForm.branch_id = selectedBranchId.value || branches.value[0].id
      }
    } catch (e) { 
      console.error('Failed to load branches:', e) 
    }
  }
}

async function loadProducts() {
  try {
    const res = await api.get('/products?status=active')
    // Handle paginated response wrapper
    allProducts.value = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
    console.log('Products loaded:', allProducts.value.length, 'items')
  } catch (e) { 
    console.error('Failed to load products:', e)
  }
}

async function loadInventory() {
  loading.value = true
  try {
    let url = '/inventory'
    
    // Admin and managers can load without branch_id (backend filters)
    // but if a branch is selected, use it
    if (selectedBranchId.value) {
      url = `/inventory?branch_id=${selectedBranchId.value}`
    }
    
    console.log('Loading inventory from:', url)
    const res = await api.get(url)
    let inventoryData = res || []
    
    // Ensure inventoryData is an array
    if (!Array.isArray(inventoryData)) {
      inventoryData = inventoryData.data || []
    }
    
    // Join with product data to ensure we have product names and prices
    inventory.value = inventoryData.map(item => {
      const product = allProducts.value.find(p => p.id === item.product_id)
      return {
        ...item,
        product_name: product?.name || item.product_name || 'Unknown Product',
        sku: product?.sku || item.sku || 'N/A',
        sale_price: product?.sale_price || item.sale_price || 0,
        reorder_level: item.reorder_level || 10,
        category_name: product?.category_name || item.category_name || 'N/A'
      }
    })
    
    console.log('Inventory loaded:', inventory.value.length, 'items')
  } catch (e) {
    console.error('Failed to load inventory:', e)
  } finally {
    loading.value = false
  }
}

function openAdjustModal(row) {
  if (!row) {
    // If no row, user clicked global "Adjust Levels" - show empty form
    editingItem.value = {
      product_name: '',
      product_id: '',
      branch_id: addForm.branch_id || selectedBranchId.value || '',
      quantity: 0
    }
  } else {
    // User clicked on a specific row - pre-fill the data
    editingItem.value = { ...row }
  }
  adjustForm.quantity = row?.quantity || 0
  adjustForm.notes = ''
  showAdjustModal.value = true
}

async function submitAddStock() {
  submitting.value = true
  try {
    await api.post('/inventory/add', addForm)
    showAddModal.value = false
    addForm.product_id = ''
    addForm.quantity = 1
    addForm.notes = ''
    await loadInventory()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to replenish stock.')
  } finally {
    submitting.value = false
  }
}

async function submitAdjustStock() {
  if (!editingItem.value) return
  submitting.value = true
  try {
    await api.post('/inventory/adjust', {
      branch_id: editingItem.value.branch_id,
      product_id: editingItem.value.product_id,
      quantity: adjustForm.quantity,
      notes: adjustForm.notes
    })
    showAdjustModal.value = false
    await loadInventory()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to adjust stock.')
  } finally {
    submitting.value = false
  }
}

// Helpers
const getStatusLabel = (q, r) => {
  if (q === 0) return t('status.outOfStock')
  if (q <= r * 0.2) return t('status.critical')
  if (q <= r) return t('inventory.lowStock')
  return t('status.stable')
}

const getStatusVariantClasses = (q, r) => {
  if (q === 0) return 'bg-red-50 text-red-700'
  if (q <= r * 0.2) return 'bg-red-50 text-red-700'
  if (q <= r) return 'bg-amber-50 text-amber-700'
  return 'bg-accent-pink-50 text-accent-pink-700'
}

const getStatusDotColor = (q, r) => {
  if (q === 0) return 'bg-red-500'
  if (q <= r * 0.2) return 'bg-red-500'
  if (q <= r) return 'bg-amber-500'
  return 'bg-accent-pink-500'
}

const getStockBarColor = (q, r) => {
  // Color-coded progress bar: red (<5%), amber (<20%), green (healthy)
  const percentage = (q / (r * 2)) * 100
  if (percentage < 5) return 'bg-red-500'
  if (percentage < 20) return 'bg-amber-500'
  return 'bg-accent-pink-500'
}

async function openLogsModal(row) {
  if (!row) return
  currentLogItem.value = row
  stockLogs.value = []
  loadingLogs.value = true
  showLogsModal.value = true
  
  try {
    const res = await api.get(`/inventory/logs?branch_id=${row.branch_id || row.id}&product_id=${row.product_id}`)
    let logs = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : [])
    
    console.log('Raw logs from API:', logs)
    
    // Map from actual DB fields: movement_type, qty_change, branch_id
    stockLogs.value = logs.map(log => {
      // Find branch name from our loaded branches
      const branch = branches.value.find(b => b.id === log.branch_id)
      const branchName = branch?.name || `Branch ${log.branch_id}`
      
      return {
        id: log.id,
        created_at: log.created_at,
        transaction_type: log.movement_type || 'unknown',
        quantity_change: log.qty_change !== undefined ? log.qty_change : 0,
        branch_name: branchName,
        notes: log.notes || '',
        ...log
      }
    })
    
    console.log('Processed logs for display:', stockLogs.value)
  } catch (e) {
    console.error('Failed to load logs:', e)
    alert('Failed to load stock history.')
  } finally {
    loadingLogs.value = false
  }
}

function exportData() {
  try {
    // Prepare CSV content
    const headers = ['Product', 'SKU', 'Stock Level', 'Unit Price', 'Status']
    const rows = filteredInventory.value.map(item => [
      item.product_name || 'Unknown',
      item.sku || 'N/A',
      item.quantity || 0,
      '$' + (parseFloat(item.sale_price || 0).toFixed(2)),
      getStatusLabel(item.quantity, item.reorder_level)
    ])
    
    // Create CSV
    const csv = [headers, ...rows].map(row => row.join(',')).join('\n')
    
    // Download
    const blob = new Blob([csv], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `stock-inventory-${new Date().toISOString().split('T')[0]}.csv`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    alert('Failed to export data.')
  }
}

async function submitTransferStock() {
  if (!transferForm.from_branch_id || !transferForm.to_branch_id || !transferForm.product_id) {
    alert('Please fill in all required fields')
    return
  }
  
  submitting.value = true
  try {
    await api.post('/transfers', transferForm)
    showTransferModal.value = false
    transferForm.from_branch_id = ''
    transferForm.to_branch_id = ''
    transferForm.product_id = ''
    transferForm.quantity = 1
    await loadInventory()
    alert('Stock transfer initiated successfully!')
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to transfer stock.')
  } finally {
    submitting.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString()
}

const getLogBadgeVariant = (type) => {
  const variants = {
    'add': 'success',
    'adjust': 'warning',
    'transfer_out': 'neutral',
    'transfer_in': 'success',
    'sale': 'error',
    'replenish': 'success',
    'adjustment': 'warning',
    'transfer': 'info',
    'order': 'error'
  }
  return variants[type?.toLowerCase()] || 'neutral'
}

const getLogTypeLabel = (type) => {
  const labels = {
    'add': 'Replenish',
    'adjust': 'Adjustment',
    'transfer_out': 'Transfer Out',
    'transfer_in': 'Transfer In',
    'sale': 'Sale',
    'replenish': 'Replenish',
    'adjustment': 'Adjustment',
    'transfer': 'Transfer',
    'order': 'Sale Order'
  }
  return labels[type?.toLowerCase()] || type || 'Unknown'
}

const getQuantityClass = (qty) => {
  if (qty > 0) return 'text-rose-600'
  if (qty < 0) return 'text-rose-600'
  return 'text-slate-600'
}
</script>
