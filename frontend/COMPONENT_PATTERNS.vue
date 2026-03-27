<!-- Quick Reference: Common Component Patterns -->

<!-- KPI Card (from Dashboard) -->
<Card class="border-l-4 border-l-rose-700">
  <div class="flex items-start justify-between">
    <div>
      <p class="text-slate-600 text-sm font-medium">Metric Label</p>
      <p class="text-3xl font-bold text-slate-900 mt-2">$248,500</p>
      <p class="text-rose-700 text-xs font-semibold mt-3 flex items-center gap-1">
        <TrendingUpIcon class="w-3 h-3" />
        +8.2% from last month
      </p>
    </div>
    <div class="p-3 bg-rose-50 rounded-custom">
      <DollarSignIcon class="w-6 h-6 text-rose-700" />
    </div>
  </div>
</Card>

<!-- Form Group -->
<div>
  <label class="block text-sm font-medium text-slate-900 mb-2">Field Label</label>
  <Input v-model="value" placeholder="Enter value..." />
  <p class="text-xs text-slate-500 mt-1">Helper text (optional)</p>
</div>

<!-- Button Group -->
<div class="flex gap-3 justify-end">
  <Button variant="ghost" label="Cancel" @click="handleCancel" />
  <Button variant="primary" label="Save" @click="handleSave" />
</div>

<!-- Status Badge -->
<Badge 
  :label="status" 
  :variant="status === 'Active' ? 'success' : 'warning'"
/>

<!-- Table with Actions -->
<DataTable :columns="columns" :data="data">
  <template #cell-name="{ value }">
    <span class="font-medium text-slate-900">{{ value }}</span>
  </template>
  
  <template #cell-status="{ row }">
    <Badge :label="row.status" :variant="getStatusVariant(row.status)" />
  </template>
  
  <template #actions="{ row }">
    <div class="flex items-center gap-2">
      <button @click="handleEdit(row)" class="p-1 text-slate-600 hover:text-rose-700 transition-colors">
        <EditIcon class="w-4 h-4" />
      </button>
      <button @click="handleDelete(row)" class="p-1 text-slate-600 hover:text-red-600 transition-colors">
        <TrashIcon class="w-4 h-4" />
      </button>
    </div>
  </template>
</DataTable>

<!-- Empty State -->
<Card title="No Data">
  <div class="text-center py-12">
    <EmptyIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
    <p class="text-slate-600 text-sm">No items found</p>
    <p class="text-slate-500 text-xs mt-1">Try adjusting your filters</p>
    <Button variant="primary" label="Create New" class="mt-4" />
  </div>
</Card>

<!-- Loading State -->
<Card>
  <div class="animate-pulse space-y-3">
    <div class="h-8 bg-slate-200 rounded w-1/2"></div>
    <div class="h-20 bg-slate-200 rounded"></div>
    <div class="h-8 bg-slate-200 rounded w-1/3"></div>
  </div>
</Card>

<!-- Divider -->
<div class="border-t border-border-default my-section"></div>

<!-- Section Spacing Examples -->
<div class="space-y-section">           <!-- Large section gaps -->
  <div class="space-y-gutter">           <!-- Card/item gaps -->
    <!-- Content -->
  </div>
</div>

<!-- Grid Patterns -->

<!-- 4-column KPI Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
  <!-- Cards -->
</div>

<!-- 2-column with 3:1 ratio -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
  <div class="lg:col-span-2"><!-- Main content --></div>
  <div><!-- Sidebar --></div>
</div>

<!-- Full width with asymmetric sides -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-gutter">
  <div class="lg:col-span-2.5"><!-- Main --></div>
  <div class="lg:col-span-1.5"><!-- Secondary --></div>
</div>

<!-- Color System Usage -->

<!-- Text Colors -->
<p class="text-slate-900">Primary text</p>
<p class="text-slate-600">Secondary text</p>
<p class="text-slate-500">Tertiary text</p>
<p class="text-slate-400">Disabled text</p>

<!-- Background Colors -->
<div class="bg-bg-primary">Page background</div>
<div class="bg-surface">Card background</div>
<div class="bg-surface-alt">Nested elements</div>
<div class="bg-rose-50">Hover state</div>

<!-- Border Colors -->
<div class="border border-border-light">Subtle border</div>
<div class="border border-border-default">Default border</div>
<div class="border border-slate-300">Strong border</div>

<!-- Status Colors -->
<div class="bg-green-50 text-green-700">Success</div>
<div class="bg-orange-50 text-orange-700">Warning</div>
<div class="bg-red-50 text-red-700">Error</div>
<div class="bg-cyan-50 text-cyan-700">Info</div>

<!-- Typography Hierarchy -->
<h1 class="text-3xl font-bold">Page Title (h1)</h1>              <!-- Display -->
<h2 class="text-2xl font-bold">Section Title (h2)</h2>            <!-- Heading Large -->
<h3 class="text-xl font-bold">Subsection (h3)</h3>                <!-- Heading -->
<h4 class="text-lg font-semibold">Card Title (h4)</h4>            <!-- Title -->
<p class="text-base">Regular body text (p)</p>                      <!-- Body -->
<label class="text-sm font-medium">Form label</label>             <!-- Small -->
<span class="text-xs">Helper text or badge</span>                 <!-- Extra Small -->

<!-- Responsive Patterns -->

<!-- Mobile-first stacking -->
<div class="flex flex-col md:flex-row gap-gutter">
  <div class="md:w-1/2">Section 1</div>
  <div class="md:w-1/2">Section 2</div>
</div>

<!-- Grid that stacks on mobile -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
  <!-- Responsive cards -->
</div>

<!-- Hover & Active States -->

<!-- Button hover -->
<button class="bg-rose-700 hover:bg-rose-800 transition-colors duration-150">
  Click me
</button>

<!-- Card hover -->
<div class="shadow-soft hover:shadow-soft-md transition-shadow duration-150">
  Hover for lift
</div>

<!-- Table row hover -->
<tr class="hover:bg-rose-50 transition-colors duration-150">
  <!-- Content -->
</tr>

<!-- Interactive element -->
<button class="text-slate-600 hover:text-rose-700 transition-colors duration-150">
  Interactive text button
</button>

<!-- Animations -->

<!-- Fade in -->
<div class="fade-in">Content</div>

<!-- Slide in -->
<div class="slide-in">Content</div>

<!-- Smooth transitions -->
<div class="transition-all duration-150 ease-out">
  Smoothly transition on any property change
</div>

<!-- Disabled States -->

<!-- Disabled button -->
<Button disabled label="Disabled" />

<!-- Disabled input -->
<Input :disabled="true" placeholder="Disabled input" />

<!-- Disabled text -->
<p class="text-slate-400">Disabled or inactive state</p>

<!-- Focus States (for keyboard users / accessibility) -->

<!-- Input focus -->
<Input class="focus:outline-none focus:border-rose-300 focus:bg-rose-50 focus:shadow-rose-sm" />

<!-- Button focus -->
<Button class="focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-300" />

<!-- Link focus -->
<a class="focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-300">Link</a>
