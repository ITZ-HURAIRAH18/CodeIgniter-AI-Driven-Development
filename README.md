# 🏢 Multi-Branch Inventory & Order Management System

**AI-Driven Development | CodeIgniter 4 + Vue.js 3 | Production-Ready**

---

## 📋 Quick Start

### One-Minute Overview
- **Backend**: CodeIgniter 4 RESTful API (PHP 8.5.1+)
- **Frontend**: Vue.js 3 Single-Page Application
- **Database**: MySQL 8.0+
- **Authentication**: JWT with role-based access control
- **Purpose**: Multi-branch inventory tracking, order management, and stock transfers

### Start Development (5 minutes)

```bash
# Terminal 1: Backend
composer install
php spark migrate
php spark db:seed RoleSeeder
php spark db:seed UserSeeder
php spark serve --port 8081

# Terminal 2: Frontend
cd frontend
npm install
npm run dev  # runs on http://localhost:5174
```

**Then login with:**
- Email: `admin@system.com`
- Password: `Admin@12345`

---

## 📦 System Features

✅ **Multi-Branch Architecture**
- Separate inventory per branch
- Branch manager assigned at creation
- Admin views all branches

✅ **Authentication & Authorization**
- JWT-based bearer token authentication
- 3 roles: Admin, Branch Manager, Sales User
- Automatic role-based permission checks
- Token refresh mechanism

✅ **Inventory Management**
- Add/adjust stock per branch
- Transfer stock between branches
- Never allow negative inventory
- Comprehensive audit logs for all changes

✅ **Order Processing**
- Create orders with multiple items
- Automatic calculation: subtotal, tax, total
- Safe stock deduction (prevents overselling)
- Database transactions ensure atomicity
- Pessimistic locking prevents race conditions

✅ **Product Management**
- Centralized product catalog
- SKU (Stock Keeping Unit) tracking
- Cost price, sale price, tax percentage
- Active/inactive status

✅ **User Management**
- User CRUD operations
- Role assignment
- Branch manager assignment
- User activation/deactivation

✅ **Real-Time Data**
- Live inventory tracking
- Order status updates
- Branch isolation for data privacy

---

## 🏗️ Architecture Overview

### Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Backend Framework** | CodeIgniter | 4.7.0 |
| **Backend Language** | PHP | 8.5.1+ |
| **Database** | MySQL | 8.0+ |
| **Authentication** | JWT (firebase/php-jwt) | 6.11.1 |
| **Frontend Framework** | Vue.js | 3.4.29 |
| **Build Tool** | Vite | 5.4.21 |
| **HTTP Client** | Axios | Latest |
| **State Management** | Pinia | Latest |
| **CSS Framework** | Tailwind CSS | 4.x |

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Vue.js Frontend                          │
│              (Vite Dev Server Port 5174)                    │
│  - Dashboard, Orders, Inventory, Products, Branches        │
└────────────────────────┬──────────────────────────────────┘
                         │ HTTP/CORS
        ┌──────────────  │  ──────────────┐
        │                │                │
        ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────┐
│            CodeIgniter 4 REST API                           │
│         (Backend Server Port 8081)                          │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Routes (/api/v1/*)                                 │    │
│  │ - /auth (login, refresh, logout, profile)          │    │
│  │ - /products (CRUD)                                 │    │
│  │ - /branches (CRUD)                                 │    │
│  │ - /inventory (stock levels, transfers)             │    │
│  │ - /orders (create, update, list)                   │    │
│  │ - /users (admin management)                        │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Middleware/Filters                                 │    │
│  │ - AuthJWTFilter (validate bearer tokens)           │    │
│  │ - RoleFilter (permission checks)                   │    │
│  │ - CorsFilter (CORS headers)                        │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Controllers (thin layer)                           │    │
│  │ - AuthController                                   │    │
│  │ - ProductController                                │    │
│  │ - BranchController                                 │    │
│  │ - OrderController                                  │    │
│  │ - InventoryController                              │    │
│  │ - TransferController                               │    │
│  │ - UserController                                   │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Services (Business Logic)                          │    │
│  │ - AuthService (JWT generation/validation)          │    │
│  │ - OrderService (order creation with transactions)  │    │
│  │ - InventoryService (stock management)              │    │
│  │ - StockTransferService (branch transfers)          │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Models (Database Abstraction)                      │    │
│  │ - UserModel                                        │    │
│  │ - BranchModel                                      │    │
│  │ - ProductModel                                     │    │
│  │ - InventoryModel                                   │    │
│  │ - OrderModel                                       │    │
│  │ - StockTransferModel                               │    │
│  │ - InventoryLogModel                                │    │
│  └────────────────────────────────────────────────────┘    │
└────────────────────────┬──────────────────────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────┐
        │    MySQL Database               │
        │  (localhost:3306)               │
        │  - users, branches, products    │
        │  - inventory, orders            │
        │  - transfers, logs              │
        └─────────────────────────────────┘
```

### Request Flow Example

```
1. User opens browser → http://localhost:5174
2. Vue.js loads and prompts for login
3. User enters: admin@system.com / Admin@12345
4. Frontend sends POST /api/v1/auth/login
5. AuthController validates credentials
6. AuthService generates JWT tokens
7. Frontend stores access_token in memory
8. Subsequent requests include Authorization: Bearer <token>
9. AuthJWTFilter validates token on every request
10. RoleFilter checks if user has permission
11. Controller processes request, calls Service
12. Service performs business logic (validation, calculations, transactions)
13. Model accesses database
14. Response JSON sent back to frontend
15. Frontend updates UI with new data
```

### Folder Structure

```
project-root/
│
├── README.md                                    ← You are here
├── AI_SDLC.md                                  ← AI development workflow documentation
├── composer.json                               ← PHP dependencies
├── env                                         ← Environment template (copy to .env)
├── spark                                       ← CodeIgniter CLI tool
│
├── app/
│   ├── Controllers/
│   │   └── Api/V1/                            ← ALL RESTful endpoints
│   │       ├── BaseApiController.php          ← Response wrapper, standardized JSON
│   │       ├── AuthController.php             ← Login, refresh, logout, profile
│   │       ├── ProductController.php          ← Products CRUD
│   │       ├── BranchController.php           ← Branches CRUD
│   │       ├── UserController.php             ← Users management
│   │       ├── OrderController.php            ← Orders CRUD + creation
│   │       ├── InventoryController.php        ← Stock levels and adjustments
│   │       ├── TransferController.php         ← Inter-branch stock transfers
│   │       └── TestController.php             ← Health check endpoint
│   │
│   ├── Filters/                               ← Middleware
│   │   ├── AuthJWTFilter.php                 ← JWT validation
│   │   ├── RoleFilter.php                    ← Permission checks
│   │   └── CorsFilter.php                    ← CORS headers
│   │
│   ├── Models/                                ← Database abstraction
│   │   ├── UserModel.php
│   │   ├── BranchModel.php
│   │   ├── ProductModel.php
│   │   ├── InventoryModel.php
│   │   ├── OrderModel.php
│   │   ├── OrderItemModel.php
│   │   ├── StockTransferModel.php
│   │   └── InventoryLogModel.php
│   │
│   ├── Services/                              ← Business logic layer
│   │   ├── AuthService.php                   ← JWT token generation/validation
│   │   ├── OrderService.php                  ← Order creation with transactions
│   │   ├── InventoryService.php              ← Stock management
│   │   └── StockTransferService.php          ← Branch transfers
│   │
│   ├── Exceptions/
│   │   └── InsufficientStockException.php    ← Custom exception
│   │
│   ├── Config/
│   │   ├── Routes.php                        ← API routing & filter assignment
│   │   ├── Filters.php                       ← Filter configuration
│   │   ├── Database.php                      ← Database connection
│   │   ├── Services.php                      ← Service registration
│   │   └── [other config files]
│   │
│   └── Database/
│       ├── Migrations/                        ← Schema creation scripts
│       │   ├── 2025-01-01-000001_CreateRolesTable.php
│       │   ├── 2025-01-01-000002_CreateBranchesTable.php
│       │   ├── 2025-01-01-000003_CreateUsersTable.php
│       │   ├── 2025-01-01-000004_CreateProductsTable.php
│       │   ├── 2025-01-01-000005_CreateInventoryTable.php
│       │   ├── 2025-01-01-000006_CreateInventoryLogsTable.php
│       │   ├── 2025-01-01-000007_CreateStockTransfersTable.php
│       │   ├── 2025-01-01-000008_CreateOrdersTable.php
│       │   └── [other migrations]
│       │
│       └── Seeds/                            ← Test data
│           ├── RoleSeeder.php                ← Creates roles
│           └── UserSeeder.php                ← Creates users, branches, products
│
├── frontend/                                  ← Vue.js 3 SPA
│   ├── package.json
│   ├── vite.config.js
│   ├── tailwind.config.js
│   ├── postcss.config.js
│   ├── .env.local                           ← API endpoint config (create this)
│   │
│   └── src/
│       ├── main.js                          ← Entry point
│       ├── App.vue                          ← Root component
│       │
│       ├── api/
│       │   └── axios.js                     ← HTTP client with token injection
│       │
│       ├── components/                      ← Reusable UI components
│       │   ├── dashboard/                   ← Dashboard widgets
│       │   ├── ui/                          ← Buttons, forms, tables
│       │   └── layout/                      ← Header, sidebar, footer
│       │
│       ├── views/                           ← Page components (routes)
│       │   ├── LoginView.vue
│       │   ├── DashboardView.vue
│       │   ├── ProductsView.vue
│       │   ├── BranchesView.vue
│       │   ├── InventoryView.vue
│       │   ├── OrdersView.vue
│       │   ├── UsersView.vue
│       │   └── [other views]
│       │
│       ├── store/                           ← Pinia state management
│       │   └── auth.store.js                ← Auth state, user profile
│       │
│       ├── router/                          ← Vue Router configuration
│       │   └── index.js                     ← Routes & guards
│       │
│       └── assets/                          ← Images, CSS, utilities
│
├── public/
│   └── index.php                            ← Application entry point
│
├── writable/
│   ├── cache/                               ← Cache files
│   ├── logs/                                ← Application logs
│   ├── session/                             ← Session files
│   └── uploads/                             ← User uploads
│
└── vendor/                                  ← PHP dependencies (Composer)
    └── [external libraries]
```

---

## 🗄️ Database Schema

### Database Diagram

```
┌──────────────┐
│   roles      │
├──────────────┤
│ id (PK)      │
│ name         │  (1=admin, 2=branch_manager, 3=sales_user)
└──────────────┘
     ↑
     │
┌──────────────────────────────┐
│users                         │
├──────────────────────────────┤
│ id (PK)                      │
│ role_id (FK) ────────────────┼──→ roles.id
│ name                         │
│ email (UNIQUE)               │
│ password (hashed)            │
│ is_active                    │
│ last_login                   │
│ created_at, updated_at       │
│ deleted_at (soft delete)     │
└──────────────────────────────┘
     ↑
     │ (manager_id FK)
┌──────────────────────────────┐
│branches                      │
├──────────────────────────────┤
│ id (PK)                      │
│ manager_id (FK) ─────────────┘ (nullable)
│ name (UNIQUE)                │
│ address                      │
│ phone                        │
│ is_active                    │
│ created_at, updated_at       │
│ deleted_at (soft delete)     │
└──────────────────────────────┘
     ↓
     │ (branch_id FK)
┌──────────────────────────────┐
│inventory                     │
├──────────────────────────────┤
│ id (PK)                      │
│ branch_id (FK)               │
│ product_id (FK) ─┐           │
│ quantity         │           │
│ reorder_level    │           │
│ UNIQUE(branch_id, product_id)│
└──────────────────────────────┘
                  │
                  │ (product_id FK)
┌──────────────────────────────┐
│products                      │
├──────────────────────────────┤
│ id (PK)                      │
│ sku (UNIQUE)                 │
│ name                         │
│ description                  │
│ cost_price                   │
│ sale_price                   │
│ tax_percentage               │
│ unit (PCS, KG, etc)          │
│ status (active/inactive)     │
│ created_at, updated_at       │
│ deleted_at (soft delete)     │
└──────────────────────────────┘
     ↑
     │ (product_id FK)
┌──────────────────────────────┐
│order_items                   │
├──────────────────────────────┤
│ id (PK)                      │
│ order_id (FK) ────────┐      │
│ product_id (FK)       │      │
│ quantity              │      │
│ unit_price            │      │
│ line_total            │      │
│ created_at            │      │
└──────────────────────────────┘
     ↑                │
     │                │
┌──────────────────────────────┐
│orders                        │
├──────────────────────────────┤
│ id (PK)                      │
│ branch_id (FK)               │
│ user_id (FK)                 │
│ order_number (UNIQUE)        │
│ status (pending/complete)    │
│ subtotal                     │
│ tax_amount                   │
│ grand_total                  │
│ notes                        │
│ created_at                   │
│ deleted_at (soft delete)     │
└──────────────────────────────┘

┌──────────────────────────────┐
│stock_transfers              │
├──────────────────────────────┤
│ id (PK)                      │
│ from_branch_id (FK)          │
│ to_branch_id (FK)            │
│ product_id (FK)              │
│ quantity                     │
│ status (pending/approved)    │
│ created_by (FK) → users      │
│ approved_by (FK nullable)    │
│ created_at, updated_at       │
└──────────────────────────────┘

┌──────────────────────────────┐
│inventory_logs (audit trail) │
├──────────────────────────────┤
│ id (PK)                      │
│ branch_id (FK)               │
│ product_id (FK)              │
│ user_id (FK)                 │
│ action (add/subtract/sale)   │
│ quantity_change              │
│ notes                        │
│ created_at (immutable)       │
└──────────────────────────────┘
```

### Table Descriptions

| Table | Purpose |
|-------|---------|
| **roles** | System roles (admin, branch_manager, sales_user) |
| **users** | User accounts with role assignment |
| **branches** | Physical store branches with manager assignment |
| **products** | Product catalog (shared across all branches) |
| **inventory** | Stock levels per branch per product (single source of truth) |
| **orders** | Customer orders with total calculations |
| **order_items** | Line items within orders |
| **stock_transfers** | Tracks stock movements between branches |
| **inventory_logs** | Immutable audit trail of all inventory changes |

---

## 🔐 Test Credentials

Use these credentials to test the system:

### Admin Account
```
Email:    admin@system.com
Password: Admin@12345
Role:     System Administrator (full system access)
Access:   All branches, all features, user management
```

### Branch Manager Accounts
```
Email:    manager@branch1.com
Password: Manager@12345
Role:     Branch Manager (Branch 1)
Access:   Branch 1 only - inventory, orders, reports

Email:    manager@branch2.com
Password: Manager@12345
Role:     Branch Manager (Branch 2)
Access:   Branch 2 only - inventory, orders, reports
```

### Sales User Accounts
```
Email:    sales@branch1.com
Password: Sales@12345
Role:     Sales User (Branch 1)
Access:   Create orders, view inventory for Branch 1

Email:    sales@branch2.com
Password: Sales@12345
Role:     Sales User (Branch 2)
Access:   Create orders, view inventory for Branch 2
```

### Test Branches
```
Branch 1: Main Branch
  - Address: 123 Main Street, City
  - Manager: manager@branch1.com
  - Products: 100x Widget A, 50x Widget B, 25x Gadget Pro

Branch 2: North Branch
  - Address: 456 North Ave, City
  - Manager: manager@branch2.com
  - Products: 75x Widget A, 30x Widget B
```

### Test Products
```
1. Widget A
   - SKU: PROD-001
   - Cost: $10.00
   - Sale Price: $25.00
   - Tax: 17%

2. Widget B
   - SKU: PROD-002
   - Cost: $20.00
   - Sale Price: $50.00
   - Tax: 17%

3. Gadget Pro
   - SKU: PROD-003
   - Cost: $80.00
   - Sale Price: $150.00
   - Tax: 17%
```

---

## 🚀 Installation & Setup

### Prerequisites

Before you start, make sure you have installed:

- **PHP 8.5.1+** — Check with: `php --version`
- **Composer** — Check with: `composer --version`
- **MySQL 8.0+** — Check with: `mysql --version`
- **Node.js 18+** — Check with: `node --version`
- **npm** — Comes with Node.js
- **Git** — Optional, for version control

### Step 1: Clone/Extract Project

```bash
# If cloning from Git
git clone <repository-url>
cd "CodeIgniter + AI-Driven Development"

# OR if you have a ZIP file
unzip project.zip
cd "CodeIgniter + AI-Driven Development"
```

### Step 2: Backend Setup

#### 2.1 Install PHP Dependencies

```bash
composer install
```

This installs:
- CodeIgniter 4 framework
- JWT authentication library
- Testing packages
- Development dependencies

#### 2.2 Configure Environment Variables

```bash
# Copy environment template
cp env .env

# On Windows (if copy doesn't work)
# copy env .env
```

Edit `.env` and update the database section:

```env
# Database Configuration
database.default.hostname = localhost
database.default.database = CodeIgniter
database.default.username = root
database.default.password = student123
database.default.port = 3306

# JWT Configuration
JWT_SECRET = your_super_secret_jwt_key_here_min_32_chars
JWT_ALGORITHM = HS256
JWT_EXPIRED_IN = 3600
JWT_REFRESH_EXPIRED_IN = 604800

# App Configuration
CI_ENVIRONMENT = development
app.baseURL = http://localhost:8081/
app.jsSecure = false
```

#### 2.3 Create Database

```bash
# Using MySQL CLI
mysql -u root -p
CREATE DATABASE CodeIgniter;
exit;

# OR use phpMyAdmin to create database manually
```

#### 2.4 Run Migrations

```bash
# These create all tables
php spark migrate
```

Output should show:
```
Running all new migrations...
✓ Created tables: roles, branches, users, products, inventory, etc.
```

#### 2.5 Seed Test Data

```bash
# Create roles (admin, manager, sales_user)
php spark db:seed RoleSeeder

# Create users, branches, products, and inventory
php spark db:seed UserSeeder
```

Check in MySQL:
```bash
mysql -u root -p CodeIgniter
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM products;
SELECT COUNT(*) FROM inventory;
exit;
```

#### 2.6 Start Backend Server

```bash
php spark serve --port 8081
```

Output:
```
CodeIgniter 4.7.0 Development Server
System up and running!
Serving CodeIgniter on port 8081
```

Visit: http://localhost:8081/api/v1/test

Should see: `{"message":"API is running"}`

### Step 3: Frontend Setup

#### 3.1 Navigate to Frontend Directory

```bash
cd frontend
```

#### 3.2 Install JavaScript Dependencies

```bash
npm install
```

This installs:
- Vue.js 3
- Vite build tool
- Axios HTTP client
- Vue Router
- Pinia state management
- Tailwind CSS

#### 3.3 Configure API Endpoint

Create `.env.local` file in `frontend/` directory:

```env
VITE_API_URL=http://localhost:8081/api/v1
```

#### 3.4 Start Frontend Development Server

```bash
npm run dev
```

Output:
```
  VITE v5.4.21  ready in 234 ms

  ➜  Local:   http://localhost:5174/
  ➜  press h to show help
```

### Step 4: Verify Installation

#### 4.1 Test Backend API

Open terminal and test login endpoint:

```bash
curl -X POST http://localhost:8081/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email":"admin@system.com",
    "password":"Admin@12345"
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLC...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLC...",
    "user": {
      "id": 1,
      "name": "System Admin",
      "email": "admin@system.com",
      "role_id": 1,
      "role": "admin"
    }
  }
}
```

#### 4.2 Test Frontend

Open browser: http://localhost:5174

You should see:
1. Login page
2. Email/Password input fields
3. "Login" button

#### 4.3 Test Full Flow

1. Enter credentials: `admin@system.com` / `Admin@12345`
2. Click Login
3. Should see Dashboard with:
   - Navigation menu
   - Branch selector
   - Product list
   - Inventory details
   - Orders section

#### Troubleshooting

**Backend won't start:**
```bash
# Check PHP version
php --version

# Check if port 8081 is in use
# Windows: netstat -ano | findstr :8081
# Mac/Linux: lsof -i :8081
```

**Database connection failed:**
```bash
# Verify MySQL is running
# Windows: Check Services
# Mac: brew services list
# Linux: systemctl status mysql

# Test connection
mysql -u root -p CodeIgniter

# Check .env database settings
cat .env
```

**Frontend won't start:**
```bash
# Check Node version
node --version  # Should be 18+

# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

---

## 📱 Using the Application

### Dashboard

The dashboard appears after login and shows:
- **Overview Cards**: Total products, orders, branches
- **Recent Orders**: Latest 10 orders
- **Inventory Status**: Low stock alerts
- **Branch Selector**: Switch between branches (if manager)

### Inventory Management

1. Navigate to **Inventory** in left sidebar
2. View stock levels by branch and product
3. **Add Stock**: 
   - Click "Add Stock" button
   - Select product
   - Enter quantity
   - Confirm
4. **Adjust Stock**:
   - Click product row
   - Adjust quantity up/down
   - Confirm
5. **Transfer Stock**:
   - Click "Transfer" button
   - Select from branch, to branch
   - Select product and quantity
   - Confirm

### Order Management

1. Navigate to **Orders** in sidebar
2. **Create Order**:
   - Click "Create Order" button
   - Select branch
   - Add products and quantities
   - System auto-calculates: subtotal, tax, total
   - Confirm (creates order, deducts inventory atomically)
3. **View Orders**:
   - List shows all orders for your branch
   - Click order to see detailed items
   - Status: Pending or Completed

### Product Management (Admin Only)

1. Navigate to **Products** in sidebar
2. **Add Product**:
   - Click "Add Product"
   - Fill: Name, SKU, Cost, Sale Price, Tax %
   - Confirm
3. **Edit Product**:
   - Click product row
   - Modify details
   - Save
4. **Delete Product**:
   - Click product
   - Click "Delete" (soft delete, can be restored)

### Branch Management (Admin Only)

1. Navigate to **Branches** in sidebar
2. **Create Branch**:
   - Click "Add Branch"
   - Fill: Name, Address, Manager (select from manager users)
   - Confirm
3. **Edit Branch**:
   - Click branch
   - Modify details
   - Save
4. **Assign Manager**:
   - Edit branch
   - Select new manager from dropdown
   - Save

### User Management (Admin Only)

1. Navigate to **Users** in sidebar
2. **Create User**:
   - Click "Add User"
   - Fill: Name, Email, Role (admin/manager/sales user)
   - System generates temporary password
   - Confirm
3. **Edit User**:
   - Click user row
   - Modify role or status
   - Save
4. **Activate/Deactivate**:
   - Toggle "Active" checkbox
   - Save

---

## 📡 API Reference

### Base URL
```
http://localhost:8081/api/v1
```

### Authentication

#### Login
```
POST /auth/login
Content-Type: application/json

Request:
{
  "email": "admin@system.com",
  "password": "Admin@12345"
}

Response:
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLC...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLC...",
    "user": {
      "id": 1,
      "name": "System Admin",
      "email": "admin@system.com",
      "role_id": 1
    }
  }
}
```

#### Get Current User Profile
```
GET /auth/me
Authorization: Bearer <access_token>

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "System Admin",
    "email": "admin@system.com",
    "role": "admin"
  }
}
```

#### Refresh Token
```
POST /auth/refresh
Authorization: Bearer <refresh_token>

Response:
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLC...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLC..."
  }
}
```

#### Logout
```
POST /auth/logout
Authorization: Bearer <access_token>

Response:
{
  "success": true,
  "message": "Logged out successfully."
}
```

### Products

#### List Products
```
GET /products?page=1&per_page=20&search=widget
Authorization: Bearer <token>

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sku": "PROD-001",
      "name": "Widget A",
      "cost_price": "10.0000",
      "sale_price": "25.0000",
      "tax_percentage": "17.00",
      "status": "active"
    }
  ]
}
```

#### Create Product
```
POST /products
Authorization: Bearer <token>
Content-Type: application/json

Request:
{
  "sku": "PROD-004",
  "name": "New Product",
  "cost_price": "50.00",
  "sale_price": "100.00",
  "tax_percentage": "17.00"
}

Response:
{
  "success": true,
  "message": "Product created successfully.",
  "data": {
    "id": 4,
    "sku": "PROD-004",
    "name": "New Product",
    ...
  }
}
```

#### Update Product
```
PUT /products/{id}
Authorization: Bearer <token>
Content-Type: application/json

Request: (same fields as create)

Response: Updated product object
```

#### Delete Product
```
DELETE /products/{id}
Authorization: Bearer <token>

Response:
{
  "success": true,
  "message": "Product deleted successfully."
}
```

### Branches

#### List Branches
```
GET /branches
Authorization: Bearer <token>

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Main Branch",
      "address": "123 Main Street",
      "manager_id": 2,
      "manager": {
        "id": 2,
        "name": "Branch Manager One"
      },
      "is_active": true
    }
  ]
}
```

#### Create Branch
```
POST /branches
Authorization: Bearer <token>
Content-Type: application/json

Request:
{
  "name": "South Branch",
  "address": "789 South Street",
  "phone": "+1-000-0003",
  "manager_id": 3
}

Response: Created branch object
```

### Orders

#### List Orders
```
GET /orders?page=1&per_page=20&status=completed
Authorization: Bearer <token>

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-20260329-001",
      "branch_id": 1,
      "user_id": 4,
      "subtotal": "100.00",
      "tax_amount": "17.00",
      "grand_total": "117.00",
      "status": "completed",
      "items": [
        {
          "product_id": 1,
          "product_name": "Widget A",
          "quantity": 5,
          "unit_price": "25.00",
          "line_total": "125.00"
        }
      ]
    }
  ]
}
```

#### Create Order (Main Endpoint)
```
POST /orders
Authorization: Bearer <token>
Content-Type: application/json

Request:
{
  "branch_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 5
    },
    {
      "product_id": 2,
      "quantity": 3
    }
  ]
}

Response:
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "id": 1,
    "order_number": "ORD-20260329-001",
    "branch_id": 1,
    "subtotal": "200.00",
    "tax_amount": "34.00",
    "grand_total": "234.00",
    "status": "completed",
    "items": [...]
  }
}
```

### Inventory

#### List Inventory
```
GET /inventory?branch_id=1
Authorization: Bearer <token>

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "branch_id": 1,
      "product_id": 1,
      "product_name": "Widget A",
      "quantity": 95,
      "reorder_level": 10
    }
  ]
}
```

#### Get Audit Logs
```
GET /inventory/logs?branch_id=1&product_id=1
Authorization: Bearer <token>

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "action": "sale",
      "quantity_change": -5,
      "user": "Sales User One",
      "notes": "Order ORD-20260329-001",
      "created_at": "2026-03-29 10:30:45"
    }
  ]
}
```

### Stock Transfers

#### Create Transfer
```
POST /transfers
Authorization: Bearer <token>
Content-Type: application/json

Request:
{
  "from_branch_id": 1,
  "to_branch_id": 2,
  "product_id": 1,
  "quantity": 20
}

Response:
{
  "success": true,
  "message": "Transfer request created successfully.",
  "data": {
    "id": 1,
    "from_branch": "Main Branch",
    "to_branch": "North Branch",
    "product_name": "Widget A",
    "quantity": 20,
    "status": "pending",
    "created_by": "System Admin"
  }
}
```

#### Approve Transfer (Admin Only)
```
PATCH /transfers/{id}/approve
Authorization: Bearer <token>

Response:
{
  "success": true,
  "message": "Transfer approved. Stock updated.",
  "data": {...}
}
```

---

## 🔐 Security Features

### Authentication
- ✅ **JWT Tokens**: Stateless, secure bearer tokens
- ✅ **Token Expiration**: Access (1 hour), Refresh (7 days)
- ✅ **BCRYPT Hashing**: Passwords hashed, never stored plain text
- ✅ **Bearer Token**: Sent in `Authorization` header

### Authorization
- ✅ **Role-Based Access Control**: Admin, Manager, Sales User
- ✅ **Branch Isolation**: Users only see their branch's data
- ✅ **Automatic Checks**: Every endpoint validates permissions
- ✅ **Soft Deletes**: Audit trail maintained

### Data Protection
- ✅ **SQL Injection Prevention**: Prepared statements, Query Builder
- ✅ **CORS Security**: Whitelist API origins
- ✅ **Input Validation**: Server-side validation on all endpoints
- ✅ **Database Transactions**: Atomicity for critical operations

### Inventory Safety
- ✅ **Pessimistic Locking**: Prevents race conditions
- ✅ **Negative Check**: Inventory never goes below 0
- ✅ **Audit Logs**: Every change tracked with timestamp and user
- ✅ **Transaction Rollback**: Failed orders don't deduct inventory

---

## 🔧 Configuration Files

### Environment Variables (.env)

```env
# App
CI_ENVIRONMENT = development
app.baseURL = http://localhost:8081/

# Database
database.default.hostname = localhost
database.default.database = CodeIgniter
database.default.username = root
database.default.password = student123
database.default.port = 3306

# JWT
JWT_SECRET = your_super_secret_key_min_32_chars
JWT_ALGORITHM = HS256
JWT_EXPIRED_IN = 3600
JWT_REFRESH_EXPIRED_IN = 604800

# Security
app.sessionDriver = files
app.sessionCookieName = PHPSESSID
app.cookieSecure = false
```

### Routes Configuration (app/Config/Routes.php)

```php
// API v1 Routes (all require JWT token)
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], function($routes) {
    
    // Auth (no auth required for login)
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh', ['filter' => 'authJwt']);
    $routes->post('auth/logout', 'AuthController::logout', ['filter' => 'authJwt']);
    $routes->get('auth/me', 'AuthController::me', ['filter' => 'authJwt']);
    
    // Protected endpoints
    $routes->group('', ['filter' => 'authJwt'], function($routes) {
        
        // Products
        $routes->get('products', 'ProductController::list');
        $routes->post('products', 'ProductController::create');
        $routes->put('products/(:num)', 'ProductController::update/$1');
        $routes->delete('products/(:num)', 'ProductController::delete/$1');
        
        // Orders
        $routes->get('orders', 'OrderController::list');
        $routes->post('orders', 'OrderController::create');
        $routes->get('orders/(:num)', 'OrderController::show/$1');
        
        // Inventory
        $routes->get('inventory', 'InventoryController::list');
        $routes->post('inventory/adjust', 'InventoryController::adjust');
        $routes->get('inventory/logs', 'InventoryController::getLogs');
        
        // Stock Transfers
        $routes->post('transfers', 'TransferController::create');
        $routes->patch('transfers/(:num)/approve', 'TransferController::approve/$1');
        
        // Branches (admin only)
        $routes->get('branches', 'BranchController::list', ['filter' => 'role:admin']);
        $routes->post('branches', 'BranchController::create', ['filter' => 'role:admin']);
        $routes->put('branches/(:num)', 'BranchController::update/$1', ['filter' => 'role:admin']);
        
        // Users (admin only)
        $routes->get('users', 'UserController::list', ['filter' => 'role:admin']);
        $routes->post('users', 'UserController::create', ['filter' => 'role:admin']);
        $routes->put('users/(:num)', 'UserController::update/$1', ['filter' => 'role:admin']);
    });
});

// API Health Check (no auth required)
$routes->get('api/v1/test', 'Api\V1\TestController::index');
```

---

## 🧪 Testing the System

### Manual Testing Scenarios

#### Scenario 1: Login as Admin

```bash
# 1. Login
curl -X POST http://localhost:8081/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@system.com","password":"Admin@12345"}'

# Note the access_token from response

# 2. Get user profile
curl http://localhost:8081/api/v1/auth/me \
  -H "Authorization: Bearer <access_token>"

# 3. List all products
curl http://localhost:8081/api/v1/products \
  -H "Authorization: Bearer <access_token>"

# 4. List all branches
curl http://localhost:8081/api/v1/branches \
  -H "Authorization: Bearer <access_token>"
```

#### Scenario 2: Create an Order

```bash
# 1. Login as sales user
curl -X POST http://localhost:8081/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sales@branch1.com","password":"Sales@12345"}'

# Save token

# 2. Create order with 5x Widget A and 3x Widget B
curl -X POST http://localhost:8081/api/v1/orders \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "branch_id": 1,
    "items": [
      {"product_id": 1, "quantity": 5},
      {"product_id": 2, "quantity": 3}
    ]
  }'

# 3. Check inventory was updated
curl http://localhost:8081/api/v1/inventory?branch_id=1 \
  -H "Authorization: Bearer <token>"

# Verify stock for products 1 & 2 decreased
```

#### Scenario 3: Transfer Stock Between Branches

```bash
# 1. Login as admin
curl -X POST http://localhost:8081/api/v1/auth/login \
  -d '{"email":"admin@system.com","password":"Admin@12345"}'

# 2. Create transfer: 20x Widget A from Branch 1 to Branch 2
curl -X POST http://localhost:8081/api/v1/transfers \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "from_branch_id": 1,
    "to_branch_id": 2,
    "product_id": 1,
    "quantity": 20
  }'

# 3. Approve transfer (admin only)
curl -X PATCH http://localhost:8081/api/v1/transfers/1/approve \
  -H "Authorization: Bearer <token>"

# 4. Check inventory for both branches
curl http://localhost:8081/api/v1/inventory?branch_id=1 \
  -H "Authorization: Bearer <token>"
curl http://localhost:8081/api/v1/inventory?branch_id=2 \
  -H "Authorization: Bearer <token>"
```

---

## 📚 Project Structure Summary

```
┌─ Backend (PHP/CodeIgniter)
│  ├─ API Endpoints: 50+ configured routes
│  ├─ Authentication: JWT with refresh tokens
│  ├─ Authorization: Role-based filters
│  ├─ Business Logic: Service layer pattern
│  ├─ Database: 9 tables with relationships
│  └─ Validation: Input validation on all endpoints
│
├─ Frontend (Vue.js)
│  ├─ SPA: Single-page application
│  ├─ Components: Reusable Vue components
│  ├─ State Management: Pinia store
│  ├─ Routing: Vue Router with guards
│  └─ HTTP: Axios with interceptors
│
└─ Database (MySQL)
   ├─ Users & Roles
   ├─ Branches (multi-tenancy)
   ├─ Products & Inventory
   ├─ Orders & Items
   ├─ Stock Transfers
   └─ Audit Logs
```

---

## 🎓 Key Features Explained

### 1. **Multi-Branch Architecture**
Each branch operates independently with:
- Separate inventory per product
- Branch manager assignment
- Branch-specific users
- Isolated order processing
- Admin sees all branches, managers see only their branch

### 2. **Safe Order Processing**
Orders use database transactions to:
1. Validate stock availability
2. Calculate subtotal, tax, total
3. Create order record
4. Deduct inventory atomically
5. Create audit log entry
- If any step fails, entire transaction rolls back
- Pessimistic locking prevents race conditions

### 3. **Role-Based Access Control**
- **Admin**: Full system access, manage users/branches/products
- **Branch Manager**: Manage their branch, view reports
- **Sales User**: Create orders, view inventory

### 4. **Inventory Audit Trail**
Every change tracked:
- Who made the change
- What action (add, subtract, sale, transfer)
- Quantity change
- Timestamp
- Cannot be deleted (immutable log)

---

## ❓ FAQ

**Q: How do I reset the database?**
```bash
php spark migrate:refresh
php spark db:seed RoleSeeder
php spark db:seed UserSeeder
```

**Q: Can I use a different database name?**
Yes, update both:
- `.env` file: `database.default.database = YourDBName`
- Then: `php spark migrate`

**Q: How do I add a new role?**
Edit database directly in `roles` table, then create new seeder users.

**Q: How do I disable a user?**
Use the Users API endpoint to set `is_active = 0`

**Q: How do I backup the database?**
```bash
mysqldump -u root -p CodeIgniter > backup.sql
```

**Q: What if I forget a password?**
Currently no password reset. Re-run seeders to reset test credentials.

---

## 📞 Support & Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| **CORS Error** | Update CORS_ALLOWED_ORIGINS in .env |
| **JWT Token Expired** | Use refresh endpoint to get new token |
| **Insufficient Stock** | Check inventory before creating order |
| **Port Already in Use** | Use different port: `php spark serve --port 9000` |
| **MySQL Connection Failed** | Verify database name, user, password in .env |
| **VITE cannot find API** | Check VITE_API_URL in frontend/.env.local |

### Logs & Debugging

Backend logs: `writable/logs/`
Frontend console: Browser DevTools (F12)
Database logs: MySQL query log (if enabled)

---

## 🚀 Deployment Checklist

- [ ] Set `CI_ENVIRONMENT = production` in .env
- [ ] Update `JWT_SECRET` with secure random key
- [ ] Enable HTTPS (cookies secure = true)
- [ ] Configure CORS for production domain
- [ ] Move writable/ directory outside web root
- [ ] Enable database backups
- [ ] Set up monitoring & logging
- [ ] Test all API endpoints in production
- [ ] Create admin account for production
- [ ] Document API endpoints for team

---

## 📖 Additional Documentation

- Full API Documentation: See `docs/API.md`
- Architecture Decisions: See `system_design.md`
- AI Development Workflow: See `AI_SDLC.md`

---

## ✨ Credits

**Framework**: [CodeIgniter 4](https://codeigniter.com)
**Frontend**: [Vue.js 3](https://vuejs.org)
**Database**: [MySQL](https://www.mysql.com)
**Auth**: [firebase/php-jwt](https://github.com/firebase/php-jwt)

---

**Last Updated**: March 29, 2026
**Version**: 1.0.0 (Phase 2 Complete - AI-Driven Development)