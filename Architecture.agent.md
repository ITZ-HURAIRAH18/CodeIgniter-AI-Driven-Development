# 🧠 MASTER ARCHITECTURE – CodeIgniter 4 + Vue 3 INVENTORY SYSTEM

## ⚠️ CORE INSTRUCTION (READ FIRST - IN THIS ORDER)

**BEFORE making ANY changes:**

1. ✅ **READ MEMORY FILES** (`/memories/repo/`) - Project context, design decisions, colors
2. ✅ **READ THIS FILE** (Architecture.agent.md) - System structure & rules
3. ✅ **PLAN IN SESSION MEMORY** (`/memories/session/`) - Your approach for this task
4. ✅ **EXECUTE** - Follow the architecture rules strictly
5. ✅ **LOG** - Update memory with completed changes

**You MUST follow this every single time.**

---

This file defines the **SYSTEM ARCHITECTURE** for BranchOS Inventory Management System.

**Stack**: CodeIgniter 4 (Backend) + Vue 3 Composition API (Frontend)

You MUST:
* Follow this architecture strictly on every change
* Maintain consistency across backend and frontend
* Read memory files BEFORE starting any work
* Update memory files after completing changes

---

# 📋 SYSTEM OVERVIEW

**Project**: Multi-Branch Smart Inventory & Order Management System

**Stack**:
- **Backend**: CodeIgniter 4 (PHP 8.1+)
- **Frontend**: Vue 3 (Composition API)
- **Database**: MySQL 8.0+
- **Authentication**: JWT (Firebase/custom)
- **API Communication**: RESTful JSON with CORS
- **Styling**: Tailwind CSS
- **Icons**: Lucide Vue Next

**Live URL**: localhost:5173 (frontend) + localhost:8000 (backend)

---

# 🎨 UI THEME SYSTEM (From `/memories/repo/design_system_soft_palette.md`)

**Current Design System**: Soft Pastel Professional ERP UI

**Color Palette** (Reference: memory files):
- **Primary**: rose-700 (#be185d) - Soft pink accent
- **Alternative Primary**: accent-pink-500 (#ec4899) - For newer components
- **Background**: rose-50 (#fff7f9) - Soft, low eye-strain
- **Text Primary**: slate-900 (#0f172a)
- **Borders**: rose-200/300 (soft, not harsh)
- **Status**: success (green) | warning (orange) | error (red) | info (blue)

**Design Rules** (From memory):
- ✅ Use `ring-1` for focus (not ring-2)
- ✅ Borders: gray-200 only (never harsh)
- ✅ Card backgrounds: `bg-white` with `border-gray-200`
- ✅ Hover states: `bg-rose-50` or increase `shadow-md`
- ✅ No gradients, no neon, no flashy animations
- ✅ Generous whitespace for professional feel
- ✅ Smooth 150ms transitions
- ✅ Semantic color system (green=success, orange=warning, red=error)

**Card Template**:
```vue
<div class="rounded-lg bg-white border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
  <!-- content -->
</div>
```

---

# 👥 ROLE SYSTEM (CRITICAL)

**Roles** (Stored in database with ID):
- `Admin` (ID: 1) - Super admin, all access
- `Manager` (ID: 2) - Branch manager, limited to assigned branches
- `SalesUser` (ID: 3) - Sales representative, order creation only

**Role Implementation**:
- JWT contains `role` field
- Frontend: `useAuthStore` (Pinia) holds `isSalesUser`, `isBranchManager`, `isAdmin` computed properties
- Backend: Filters check `auth()->user()->role`
- Every API endpoint MUST include role-based access control

**Permission Rules**:

| Feature | Admin | Manager | SalesUser |
|---------|-------|---------|-----------|
| View all branches | ✅ | ✅ (own) | ❌ |
| View all inventory | ✅ | ✅ (own) | ❌ |
| Create orders | ✅ | ✅ (own) | ✅ |
| View all orders | ✅ | ✅ (own) | ✅ (own) |
| Manage products | ✅ | ❌ | ❌ |
| Stock transfer | ✅ | ✅ (own) | ❌ |
| Adjust inventory | ✅ | ✅ (own) | ❌ |
| View dashboard | ✅ | ✅ | ✅ (sales view) |

---

# 🏢 CORE MODULES & DATABASE SCHEMA

## **1. Users Table**
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  role INT (1=Admin, 2=Manager, 3=SalesUser),
  branch_id INT (for managers/sales users),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

## **2. Branches Table**
```sql
CREATE TABLE branches (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  address TEXT,
  manager_id INT (FK: users.id),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

## **3. Products Table**
```sql
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  sku VARCHAR(50) UNIQUE,
  cost_price DECIMAL(10,2),
  sale_price DECIMAL(10,2),
  tax_percentage INT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

## **4. Inventory Table**
```sql
CREATE TABLE inventory (
  id INT PRIMARY KEY AUTO_INCREMENT,
  branch_id INT (FK: branches.id),
  product_id INT (FK: products.id),
  quantity INT DEFAULT 0,
  reorder_level INT DEFAULT 10,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE KEY unique_branch_product (branch_id, product_id)
)
```

## **5. Orders Table**
```sql
CREATE TABLE orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  branch_id INT (FK: branches.id),
  created_by_id INT (FK: users.id),
  status VARCHAR(20) DEFAULT 'pending' (pending, completed, cancelled),
  subtotal DECIMAL(12,2),
  tax DECIMAL(12,2),
  total DECIMAL(12,2),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

## **6. Order Items Table**
```sql
CREATE TABLE order_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT (FK: orders.id),
  product_id INT (FK: products.id),
  quantity INT,
  unit_price DECIMAL(10,2),
  tax DECIMAL(10,2),
  subtotal DECIMAL(12,2),
  created_at TIMESTAMP
)
```

## **7. Stock Movements (Inventory Logs) Table**
```sql
CREATE TABLE inventory_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  branch_id INT (FK: branches.id),
  product_id INT (FK: products.id),
  movement_type VARCHAR(30) (add, remove, adjust, transfer, order),
  qty_change INT (positive or negative),
  reference_id INT (order_id or transfer_id),
  notes TEXT,
  created_at TIMESTAMP
)
```

## **8. Stock Transfers Table**
```sql
CREATE TABLE stock_transfers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  from_branch_id INT (FK: branches.id),
  to_branch_id INT (FK: branches.id),
  product_id INT (FK: products.id),
  quantity INT,
  status VARCHAR(20) DEFAULT 'pending' (pending, completed, rejected),
  created_by_id INT (FK: users.id),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

---

# 🔌 API ROUTES & ENDPOINTS

**Base URL**: `http://localhost:8000/api/v1`

## **Authentication**
```
POST   /auth/login          - Login (email + password)
POST   /auth/logout         - Logout
POST   /auth/refresh        - Refresh JWT token
GET    /auth/me             - Current user info
```

## **Branches** (Admin + Manager)
```
GET    /branches            - List branches (filtered by role)
GET    /branches/{id}       - Get branch detail
POST   /branches            - Create branch (Admin only)
PUT    /branches/{id}       - Update branch (Admin only)
```

## **Products** (Admin view)
```
GET    /products            - List all products
GET    /products/{id}       - Get product details
POST   /products            - Create product (Admin only)
PUT    /products/{id}       - Update product (Admin only)
DELETE /products/{id}       - Delete product (Admin only)
```

## **Inventory** (Role-based access)
```
GET    /inventory           - List inventory (filtered by branch + role)
GET    /inventory/{id}      - Get inventory item
POST   /inventory/add       - Add stock (Manager + Admin)
POST   /inventory/adjust    - Adjust stock (Manager + Admin)
POST   /inventory/transfer  - Transfer between branches (Manager + Admin)
```

## **Orders** (Role-based access)
```
GET    /orders              - List orders (filtered by role)
GET    /orders/{id}         - Get order details
POST   /orders              - Create order (All roles)
PUT    /orders/{id}/status  - Update order status (Admin + Manager)
DELETE /orders/{id}         - Cancel order (Creator + Admin)
```

## **Stock Transfers** (Manager + Admin)
```
GET    /transfers           - List transfers
GET    /transfers/{id}      - Get transfer details
POST   /transfers           - Create transfer
PUT    /transfers/{id}/status - Update transfer status
```

## **Inventory Logs / Activity**
```
GET    /inventory/logs      - Get activity feed
```

## **Dashboard** (Role-based)
```
GET    /dashboard/stats     - KPI cards + metrics
GET    /dashboard/top-products - Top selling products (with branch + real trend)
```

---

# 🏗️ BACKEND STRUCTURE (CodeIgniter 4)

## **Directory Structure**:
```
app/
├── Controllers/
│   ├── Api/
│   │   └── V1/
│   │       ├── AuthController.php
│   │       ├── BranchController.php
│   │       ├── ProductController.php
│   │       ├── InventoryController.php
│   │       ├── OrderController.php
│   │       ├── TransferController.php
│   │       └── DashboardController.php
│   ├── BaseController.php
│   └── Home.php
├── Models/
│   ├── UserModel.php
│   ├── BranchModel.php
│   ├── ProductModel.php
│   ├── InventoryModel.php
│   ├── OrderModel.php
│   ├── OrderItemModel.php
│   ├── InventoryLogModel.php
│   └── StockTransferModel.php
├── Services/
│   ├── AuthService.php
│   ├── InventoryService.php
│   ├── OrderService.php
│   ├── StockTransferService.php
│   └── DashboardService.php
├── Filters/
│   ├── AuthJWTFilter.php
│   ├── RoleFilter.php
│   └── CorsFilter.php
├── Exceptions/
│   ├── ApiExceptionHandler.php
│   ├── InsufficientStockException.php
│   └── UnauthorizedException.php
├── Database/
│   ├── Migrations/
│   └── Seeds/
└── Config/
    ├── Routes.php
    ├── Filters.php
    ├── Database.php
    ├── Services.php
    └── [other CI4 configs]
```

## **BACKEND CODE RULES**:

### **Service Layer** (Business Logic)
- ✅ All business logic in `Services/` not Controllers
- ✅ Controllers = thin, just route handling
- ✅ Services handle: validation, calculations, transactions

### **Controllers** (Request/Response)
- ✅ Validate JWT token via AuthJWTFilter
- ✅ Check role via RoleFilter
- ✅ Call Service methods
- ✅ Return JSON response with proper HTTP codes

### **Critical: Race Condition Prevention**
**When processing orders:**
1. Start DB transaction
2. Get inventory row with `SELECT FOR UPDATE` (lock row)
3. Check quantity (re-check, not first check)
4. Deduct stock immediately
5. Create order record
6. Create order items
7. Log stock movement
8. Commit transaction
9. Catch exception, rollback

**Example (OrderService.php)**:
```php
public function createOrder($branch_id, $items) {
    return $this->db->transStart();
    
    // Lock and check stock for each item
    foreach ($items as $item) {
        $inventory = $this->inventoryModel
            ->where('branch_id', $branch_id)
            ->where('product_id', $item['product_id'])
            ->lockForUpdate()  // Lock the row
            ->first();
        
        if (!$inventory || $inventory->quantity < $item['qty']) {
            throw new InsufficientStockException();
        }
        
        // Deduct stock
        $this->inventoryModel->update($inventory->id, [
            'quantity' => $inventory->quantity - $item['qty']
        ]);
    }
    
    // Create order + items
    $order = $this->orderModel->insert([...]);
    foreach ($items as $item) {
        $this->orderItemModel->insert([...]);
    }
    
    $this->db->transComplete();
    return $order;
}
```

### **Role-Based Access Control**
- ✅ Always filter data by role in controllers/services
- ✅ Admin sees ALL
- ✅ Manager sees only managed branches
- ✅ SalesUser sees only own orders/branch

**Example (OrderController.php)**:
```php
public function index() {
    $user = auth()->user();
    
    if ($user->role == 1) { // Admin
        $orders = $this->orderModel->findAll();
    } elseif ($user->role == 2) { // Manager
        $orders = $this->orderModel
            ->whereIn('branch_id', $user->managed_branch_ids)
            ->findAll();
    } else { // SalesUser
        $orders = $this->orderModel
            ->where('created_by_id', $user->id)
            ->findAll();
    }
    
    return $this->respond($orders);
}
```

---

# 🎨 FRONTEND STRUCTURE (Vue 3 + Composition API)

## **Directory Structure**:
```
frontend/
├── src/
│   ├── main.js                 - Main entry
│   ├── App.vue                 - Root component
│   ├── api/
│   │   └── axios.js            - API client with interceptors
│   ├── assets/
│   │   └── [images, fonts]
│   ├── components/
│   │   ├── Common/
│   │   │   ├── Header.vue
│   │   │   ├── Sidebar.vue
│   │   │   └── Modal.vue
│   │   ├── Forms/
│   │   │   ├── OrderForm.vue
│   │   │   └── InventoryForm.vue
│   │   └── [specific components]
│   ├── composables/
│   │   ├── useAuth.js
│   │   ├── useApi.js
│   │   └── [others]
│   ├── config/
│   │   └── constants.js
│   ├── router/
│   │   └── index.js            - Vue Router
│   ├── store/
│   │   ├── auth.store.js       - Pinia store (JWT, user, role)
│   │   └── [other stores]
│   ├── views/
│   │   ├── Dashboard.vue       - Role-based dashboard
│   │   ├── Order/
│   │   │   ├── OrderListView.vue
│   │   │   └── CreateOrderView.vue
│   │   ├── Inventory/
│   │   │   ├── InventoryView.vue
│   │   │   └── TransferView.vue
│   │   ├── Product/
│   │   │   └── ProductListView.vue
│   │   ├── Branch/
│   │   │   └── BranchListView.vue
│   │   └── Auth/
│   │       ├── LoginView.vue
│   │       └── RegisterView.vue
│   ├── directives/
│   │   └── [custom directives]
│   └── App.vue
├── index.html
├── package.json
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
└── .env
```

## **FRONTEND CODE RULES**:

### **Stores (Pinia)**
- ✅ `auth.store.js` = Single source of truth for user state
- ✅ Contains: user data, JWT token, role flags (isSalesUser, isBranchManager, isAdmin)
- ✅ Other stores for: orders, inventory, UI state

### **API Integration** (`api/axios.js`)
- ✅ All API calls through axios instance
- ✅ Interceptors: handle JWT, error responses, CORS
- ✅ Auto-refresh token on 401 response

### **Role-Based Rendering**
- ✅ Use `v-if="auth.isAdmin"` for conditional UI
- ✅ Sales users should NOT see inventory/transfer pages
- ✅ Managers should NOT see admin-only settings

### **Component Structure**
- ✅ Use Composition API (ref, computed, watch, onMounted)
- ✅ Extract logic into composables
- ✅ Components must be reusable and clean

### **Error Handling**
- ✅ Try-catch in all async operations
- ✅ Show error toasts on API failures
- ✅ Log errors for debugging

### **Loading States**
- ✅ Show spinner while loading data
- ✅ Disable buttons during API calls
- ✅ Prevent double-click submissions

---

# 📄 PAGES & FEATURES

## **Dashboard** (`Dashboard.vue`)
- **Admin View**: KPI cards, Inventory by Branch, Recent Activity, Top Selling Products (by branch), Stock Health chart
- **Manager View**: Same as Admin (filtered to own branches)
- **Sales User View**: Welcome section, Browse Products, Create Order, View Orders
- **Top Products Feature**: Shows branch + real trend (weekly: this week vs last week)

## **Inventory Page** (`InventoryView.vue`)
- Admin/Manager only (Sales users blocked)
- List inventory items, Add/Adjust stock, Transfer between branches
- Auto-populate current quantity when editing

## **Orders Page** (`OrderListView.vue`, `CreateOrderView.vue`)
- Create orders (all roles)
- View/Filter orders by role
- Multi-step order creation with validation

## **Products Page** (`ProductListView.vue`)
- View-only product catalog
- Show availability by branch

---

# 🧪 TESTING & DEBUGGING

### **Backend Testing**
- ✅ Test concurrent order processing (no overselling)
- ✅ Test role-based access (verify 403 unauthorized errors)
- ✅ Test transaction rollback on failure
- ✅ Use `php spark test` for unit tests

### **Frontend Testing**
- ✅ Test JWT token refresh
- ✅ Test role-based page access
- ✅ Test form validation

### **Debugging Steps**
1. Check browser console for frontend errors
2. Check `writable/logs/` for backend errors
3. Use Postman to test API endpoints
4. Check database state for data integrity

---

# 🔒 SECURITY CHECKLIST

- ✅ Store JWT in memory, not localStorage (XSS protection)
- ✅ Always validate on backend (never trust frontend)
- ✅ CORS filter allows frontend domain only
- ✅ Rate limit API endpoints
- ✅ Hash passwords with bcrypt
- ✅ Use HTTPS in production
- ✅ Validate user role on EVERY API call

---

# 🚀 DEPLOYMENT CHECKLIST

**Before Production**:
```bash
# Backend
php spark migrate                 # Run migrations
php spark config:cache           # Cache configuration
rm debug.php copy_ci4_boilerplate.php  # Remove unused files

# Frontend
npm install && npm run build     # Build production bundle

# Environment
# Set .env production values
# Set database credentials
# Set JWT_SECRET
# Set CORS allowed origins
```

**Files to Exclude**:
- `vendor/`, `node_modules/`
- `writable/logs/`, `writable/cache/`
- `.env` (use `.env.example`)
- Debug files, temporary files
- `.git/` (in production)

---

# ⚠️ FINAL RULES - READ EVERY TIME YOU MAKE A CHANGE

1. **Follow This Architecture Strictly**: No exceptions
2. **Service Layer Only**: Business logic in Services, NOT controllers
3. **Always Use Transactions**: For order processing, prevent race conditions
4. **Role Check Every Endpoint**: Admin, Manager, SalesUser filters
5. **Frontend Role Checks**: Hide/disable UI based on user role
6. **Real Data Trends**: Calculate weekly trends (not random values)
7. **Error Handling**: Always try-catch, show user-friendly messages
8. **Logging**: Log errors, not success messages (verbose noise)
9. **Database Integrity**: Foreign keys, cascading, prevent negative stock
10. **Test Before Committing**: Verify changes work as expected

---

# 📝 WHEN YOU START ANY NEW FEATURE

✅ Read this Architecture file
✅ Check `/memories/repo/` for project context
✅ Plan in `/memories/session/` before coding
✅ Follow the structure defined here
✅ Update Architecture if structure changes
✅ Log major decisions

---
