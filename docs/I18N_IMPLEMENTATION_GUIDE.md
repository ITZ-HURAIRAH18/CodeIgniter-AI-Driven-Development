# i18n Implementation Guide - Phase 2 (View Integration)

**Created**: March 31, 2026  
**Status**: Template for developers  
**Scope**: Translate all hardcoded strings in Vue Views and Components

---

## Overview

The InvenSync system now has a complete i18n infrastructure. This guide explains how to integrate translations into the remaining Views and Components.

### What's Already Done ✅
- ✅ Core i18n system (`useI18n.js` composable)
- ✅ 3 complete locale files (en, ur, zh)
- ✅ LanguageSwitcher component
- ✅ Dashboard translated (reference implementation)
- ✅ Navigation labels translated (Sidebar)
- ✅ User menu translated (Topbar)

### What Needs Translation ⏳
- Inventory View (buttons, headers, labels)
- Order List View (buttons, headers, labels)
- Create Order View (form labels, options)
- Product List View (buttons, headers, labels)
- Branch Management (if applicable)
- User Management (if applicable)
- Transfer View (buttons, headers, labels)

---

## Step-by-Step Implementation

### Step 1: Import useI18n Composable

At the top of your `<script setup>`, add:

```javascript
import { useI18n } from '@/composables/useI18n'
const { t } = useI18n()
```

### Step 2: Add Translation Keys to Locale Files

Before using `t()` in templates, add the keys to all 3 locale files:

**Pattern**:
```json
{
  "inventory": {
    "title": "Stock Management",
    "adjustButton": "Adjust",
    "transferButton": "Transfer",
    "replenishButton": "Replenish"
  }
}
```

### Step 3: Replace Hardcoded Strings in Templates

**Before**:
```vue
<h1>Stock Management</h1>
<button>Adjust</button>
<button>Transfer</button>
```

**After**:
```vue
<h1>{{ t('inventory.title') }}</h1>
<button>{{ t('inventory.adjustButton') }}</button>
<button>{{ t('inventory.transferButton') }}</button>
```

---

## Locale Keys Reference

### Already Created ✅

```json
{
  "common": {
    "search": "Search",
    "logout": "Logout",
    "save": "Save",
    "cancel": "Cancel",
    "edit": "Edit",
    "delete": "Delete",
    "add": "Add"
  },
  "navigation": {
    "main": "Main",
    "management": "Management",
    "dashboard": "Dashboard",
    "inventory": "Inventory",
    "orders": "Orders",
    "products": "Products",
    "transfers": "Transfers",
    "branches": "Branches",
    "users": "Users"
  },
  "topbar": {
    "searchPlaceholder": "Search...",
    "profileLabel": "Profile",
    "settingsLabel": "Settings",
    "logoutLabel": "Logout"
  },
  "status": {
    "critical": "Critical",
    "optimal": "Optimal",
    "lowStock": "Low Stock",
    "outOfStock": "Out of Stock"
  },
  "roles": {
    "superAdmin": "Super Admin",
    "branchManager": "Branch Manager",
    "salesUser": "Sales User"
  }
}
```

### Still Need to Create ⏳

#### Inventory Keys
```json
{
  "inventory": {
    "title": "Stock Management",
    "subtitle": "Real-time inventory tracking...",
    "selectBranch": "Select branch",
    "adjustButton": "Adjust",
    "transferButton": "Transfer",
    "replenishButton": "Replenish",
    "product": "Product",
    "stockLevel": "Stock Level",
    "quantity": "Quantity",
    "unitPrice": "Unit Price",
    "actions": "Actions",
    "status": "Status",
    "noInventory": "No inventory items found",
    "adjustStockModal": "Adjust Stock",
    "transferStockModal": "Transfer Stock",
    "replenishModal": "Replenish Stock"
  }
}
```

#### Orders Keys
```json
{
  "orders": {
    "title": "Orders",
    "subtitle": "Manage and track all orders",
    "newOrderButton": "New Order",
    "branch": "Branch",
    "selectBranch": "Select branch",
    "total": "Total",
    "date": "Date",
    "status": "Status",
    "noOrders": "No orders found",
    "selectBranchFirst": "Select a branch first",
    "addProduct": "Add Product",
    "quantity": "Quantity",
    "price": "Price",
    "removeItem": "Remove",
    "placeOrder": "Place Order"
  }
}
```

#### Products Keys
```json
{
  "products": {
    "title": "Products",
    "subtitle": "Manage product catalog",
    "addProductButton": "Add Product",
    "searchPlaceholder": "Search by name or SKU...",
    "name": "Product Name",
    "sku": "SKU",
    "category": "Category",
    "cost": "Cost",
    "price": "Price",
    "margin": "Profit Margin",
    "stock": "Stock",
    "actions": "Actions",
    "editButton": "Edit",
    "deleteButton": "Delete",
    "noProducts": "No products found"
  }
}
```

#### Transfers Keys
```json
{
  "transfers": {
    "title": "Stock Transfers",
    "subtitle": "Transfer stock between branches",
    "transferButton": "New Transfer",
    "fromBranch": "From Branch",
    "toBranch": "To Branch",
    "product": "Product",
    "quantity": "Quantity",
    "reason": "Reason",
    "executeButton": "Execute Transfer",
    "noTransfers": "No transfers found",
    "selectSource": "Select source branch",
    "selectDestination": "Select destination branch"
  }
}
```

---

## File-by-File Implementation Checklist

### [ ] InventoryView.vue
**Location**: `frontend/src/views/Inventory/InventoryView.vue`

**Hardcoded strings to replace**:
- [ ] Page title "Stock Management"
- [ ] Subtitle
- [ ] "Adjust" button text
- [ ] "Transfer" button text  
- [ ] "Replenish" button text
- [ ] "Select branch" placeholder
- [ ] Table headers: "Product", "Stock Level", "Unit Price", "Status", "Actions"
- [ ] Empty state message

**Implementation time**: ~15 minutes

---

### [ ] OrderListView.vue
**Location**: `frontend/src/views/Order/OrderListView.vue`

**Hardcoded strings to replace**:
- [ ] Table headers: "Branch", "Total", "Date", "Status"
- [ ] Search placeholder: "Order number, branch..."
- [ ] Empty state message

**Implementation time**: ~10 minutes

---

### [ ] CreateOrderView.vue
**Location**: `frontend/src/views/Order/CreateOrderView.vue`

**Hardcoded strings to replace**:
- [ ] "Select Branch" step title
- [ ] "Select Branch" form label
- [ ] "— Select branch —" option
- [ ] "Select a branch first" message
- [ ] Form labels (Branch, Product, Quantity)
- [ ] "Add Product" button
- [ ] "Remove" button in table
- [ ] "Place Order" button text

**Implementation time**: ~20 minutes

---

### [ ] ProductListView.vue
**Location**: `frontend/src/views/Product/ProductListView.vue`

**Hardcoded strings to replace**:
- [ ] Page title
- [ ] "Add Product" button
- [ ] search placeholder
- [ ] Table headers: "Name", "SKU", "Category", "Cost", "Price", "Margin", "Stock"
- [ ] Action buttons: "Edit", "Delete"
- [ ] Empty state message

**Implementation time**: ~15 minutes

---

### [ ] StockTransferView.vue (if exists)
**Location**: `frontend/src/views/Inventory/StockTransferView.vue`

**Hardcoded strings to replace**:
- [ ] Page title
- [ ] Form labels: "From Branch", "To Branch", "Product", "Quantity", "Reason"
- [ ] "Execute Transfer" button
- [ ] Table headers
- [ ] Empty state messages

**Implementation time**: ~15 minutes

---

## Testing Checklist

After implementing i18n in each view:

- [ ] Switch to English - all labels should be in English
- [ ] Switch to Urdu - all labels should be in Urdu  
- [ ] Switch to Chinese - all labels should be in Chinese
- [ ] Refresh page - language should persist
- [ ] Check browser console - no errors
- [ ] Verify layout stays LTR (no flipping)
- [ ] Test responsive design on mobile

---

## Common Patterns

### Pattern 1: Simple Text
```vue
<!-- Before -->
<h1>Stock Management</h1>

<!-- After -->
<h1>{{ t('inventory.title') }}</h1>
```

### Pattern 2: Button Text
```vue
<!-- Before -->
<button>Adjust Stock</button>

<!-- After -->
<button>{{ t('inventory.adjust') }}</button>
```

### Pattern 3: Placeholder (Input)
```vue
<!-- Before -->
<input placeholder="Product name, SKU...">

<!-- After -->
<input :placeholder="t('products.searchPlaceholder')">
```

### Pattern 4: Computed or Dynamic Content
```javascript
// Before
const emptyMessage = computed(() => 'No orders found')

// After  
const emptyMessage = computed(() => t('orders.noOrders'))
```

### Pattern 5: Conditional Status
```vue
<!-- Before -->
<span v-if="status === 'critical'">Critical</span>

<!-- After -->
<span>{{ t(`status.${status}`) }}</span>
```

---

## Adding New Locale Keys

When you encounter a new hardcoded string:

1. **Add to all 3 locale files** (en.json, ur.json, zh.json):
```json
{
  "section": {
    "newKey": "English text"
  }
}
```

2. **Use in template**:
```vue
{{ t('section.newKey') }}
```

3. **Test in all languages**

---

## Troubleshooting

### Issue: "t is not defined"
**Solution**: Make sure you imported useI18n in `<script setup>`:
```javascript
import { useI18n } from '@/composables/useI18n'
const { t } = useI18n()
```

### Issue: Text shows key instead of translation
**Possible causes**:
1. Key doesn't exist in locale file - add it
2. Key path is wrong - check spelling and structure
3. Locale file has syntax error - validate JSON

### Issue: Language doesn't change
**Solution**: Check browser console for errors, verify localStorage is working

### Issue: Layout flipped to RTL
**Solution**: useI18n.js keeps everything LTR - this shouldn't happen. Check that document.dir is 'ltr'

---

## Performance Considerations

- Translation lookup is <1ms
- All locales loaded at startup (~50KB total)
- No API calls needed for translations
- Safe to use in computed properties and watchers

---

## Completion Timeline

| Phase | Tasks | Estimated Time |
|-------|-------|---|
| Phase 1 (Done) | Core i18n + Components | ✅ Complete |
| Phase 2 (WIP) | InventoryView | 15 min |
| | OrderListView | 10 min |
| | CreateOrderView | 20 min |
| | ProductListView | 15 min |
| | Transfers/Branches | 30 min |
| | **Total Phase 2** | **~90 min (1.5 hrs)** |
| Phase 3 (Future) | API response translations | TBD |
| | Database-backed preferences | TBD |

---

## Reference

- **Composable**: `frontend/src/composables/useI18n.js`
- **Locale Files**: `frontend/src/locales/` (en.json, ur.json, zh.json)
- **Language Switcher**: `frontend/src/components/LanguageSwitcher.vue`
- **Example Implementation**: `frontend/src/views/Dashboard/Dashboard.vue`

---

**Questions?** Refer to `MULTILINGUAL_SYSTEM.md` for detailed architecture documentation.
