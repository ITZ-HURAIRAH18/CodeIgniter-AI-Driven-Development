# 🏢 Multi-Branch Inventory & Order Management System

**AI-Driven Development | CodeIgniter 4 + Vue.js 3 | Production-Ready**

![PHP](https://img.shields.io/badge/PHP-8.5.1+-777BB4?style=flat-square&logo=php)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.7.0-EF4223?style=flat-square&logo=codeigniter)
![Vue.js](https://img.shields.io/badge/Vue.js-3.4-4FC08D?style=flat-square&logo=vue.js)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql)
![Tailwind](https://img.shields.io/badge/Tailwind-4.x-06B6D4?style=flat-square&logo=tailwind-css)
![License](https://img.shields.io/badge/License-Proprietary-gray?style=flat-square)

A production-ready, multi-branch inventory and order management platform built with an AI-driven development workflow. Features JWT authentication, role-based access control, atomic order processing, comprehensive audit trails, and an integrated multi-agent AI chatbot for natural language business intelligence.

---

## 📑 Table of Contents

- [Overview](#-overview)
- [Why This Project](#-why-this-project)
- [Key Features](#-key-features)
- [System Requirements](#-system-requirements)
- [Quick Start](#-quick-start)
- [Installation & Setup](#-installation--setup)
- [Architecture](#-architecture)
- [Database Schema](#-database-schema)
- [Authentication & Roles](#-authentication--roles)
- [API Reference](#-api-reference)
- [Security](#-security)
- [Development Workflow](#-development-workflow)
- [Performance Considerations](#-performance-considerations)
- [Testing](#-testing)
- [Deployment Checklist](#-deployment-checklist)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Roadmap](#-roadmap)
- [Additional Documentation](#-additional-documentation)
- [License](#-license)
- [Support](#-support)

---

## 📋 Overview

This system addresses real-world retail challenges: **preventing overselling**, **maintaining audit trails**, **isolating branch data**, and **enabling intelligent querying** through an AI-powered chatbot layer.

| Aspect | Details |
|--------|---------|
| **Backend** | CodeIgniter 4 RESTful API (PHP 8.5.1+) |
| **Frontend** | Vue.js 3 Single-Page Application with Vite |
| **Database** | MySQL 8.0+ with relational integrity |
| **Authentication** | JWT with access/refresh token rotation |
| **Authorization** | 3-role RBAC (Admin, Branch Manager, Sales User) |
| **AI Layer** | Multi-agent chatbot for natural language BI queries |
| **Purpose** | Multi-branch inventory tracking, order processing, and stock transfers |

---

## 💡 Why This Project

| Challenge | Solution |
|-----------|----------|
| **Overselling** | Atomic order processing with pessimistic locking and database transactions |
| **No Audit Trail** | Immutable inventory logs track every stock movement with user, action, and timestamp |
| **Branch Data Leakage** | Role-based filters and branch scoping ensure users only see their branch's data |
| **Race Conditions** | Database-level locks prevent concurrent order conflicts |
| **Complex Queries** | AI chatbot translates natural language into optimized database queries |
| **Manual Stock Transfers** | Transfer workflow with approval and automatic inventory reconciliation |

---

## ✨ Key Features

### 🔐 Authentication & Authorization
- JWT-based bearer token authentication with refresh token rotation
- 3 roles: Admin, Branch Manager, Sales User
- Automatic role-based permission checks on every endpoint
- Token expiration: Access (1 hour), Refresh (7 days)

### 📦 Inventory Management
- Per-branch stock tracking with product-level granularity
- Stock adjustments with immutable audit logging
- Inter-branch stock transfers with approval workflow
- Low-stock alerts and reorder level configuration
- Negative inventory prevention at database level

### 🛒 Order Processing
- Multi-item order creation with automatic tax/subtotal calculation
- Atomic stock deduction via database transactions
- Pessimistic locking prevents race conditions
- Unique order numbering with date-based format (ORD-YYYYMMDD-NNN)
- Soft delete support for order management

### 🏷️ Product Management
- Centralized product catalog shared across branches
- SKU tracking, cost price, sale price, tax percentage
- Active/inactive status management
- Soft delete support

### 🏢 Branch Management
- Independent branch creation with manager assignment
- Branch-level data isolation
- Admin views all branches; managers see only their assigned branch

### 👤 User Management
- User CRUD with role assignment
- Branch manager assignment per branch
- User activation/deactivation
- Password hashing with bcrypt

### 🤖 AI Chatbot (Multi-Agent System)
- Natural language querying for inventory and order data
- 7 specialized agents: Query Router, Data Analyst, Inventory Advisor, Order Analyst, Product Expert, Trend Analyzer, Report Generator
- Context-aware responses with data visualization suggestions
- Fallback to structured API queries when AI is uncertain

---

## 💻 System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **PHP** | 8.5.1 | 8.5.1+ |
| **MySQL** | 8.0 | 8.0+ with InnoDB |
| **Node.js** | 18.x | 20.x LTS |
| **RAM** | 2 GB | 4 GB+ |
| **Disk Space** | 500 MB | 1 GB+ |
| **OS** | Windows 10+, macOS 12+, Linux | Any modern OS |

---

## 🚀 Quick Start

```bash
# Terminal 1: Backend
composer install
cp env .env  # Update database credentials
php spark migrate
php spark db:seed RoleSeeder && php spark db:seed UserSeeder
php spark serve --port 8081

# Terminal 2: Frontend
cd frontend
npm install
npm run dev  # → http://localhost:5174
```

**Default Login:**
- **Email:** `admin@system.com`
- **Password:** `Admin@12345`

---

## 📖 Installation & Setup

### Prerequisites

Ensure the following are installed on your system:

```bash
php --version      # 8.5.1+
composer --version # Latest
mysql --version    # 8.0+
node --version     # 18+
npm --version      # Bundled with Node.js
```

### Step 1: Install Backend Dependencies

```bash
composer install
```

Installs: CodeIgniter 4, firebase/php-jwt, testing packages, and development dependencies.

### Step 2: Configure Environment

```bash
cp env .env
```

Edit `.env` and update the database and JWT sections:

```env
# Database
database.default.hostname = localhost
database.default.database = CodeIgniter
database.default.username = root
database.default.password = your_password
database.default.port = 3306

# JWT
JWT_SECRET = your_super_secret_jwt_key_here_min_32_chars
JWT_ALGORITHM = HS256
JWT_EXPIRED_IN = 3600
JWT_REFRESH_EXPIRED_IN = 604800

# App
CI_ENVIRONMENT = development
app.baseURL = http://localhost:8081/
```

### Step 3: Create Database

```sql
CREATE DATABASE CodeIgniter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Run Migrations & Seeders

```bash
php spark migrate
php spark db:seed RoleSeeder
php spark db:seed UserSeeder
```

### Step 5: Start Backend Server

```bash
php spark serve --port 8081
```

Verify: Visit `http://localhost:8081/api/v1/test` — should return `{"message":"API is running"}`

### Step 6: Setup Frontend

```bash
cd frontend
npm install
```

Create `frontend/.env.local`:

```env
VITE_API_URL=http://localhost:8081/api/v1
```

Start the development server:

```bash
npm run dev
```

### Step 7: Verify Installation

Open `http://localhost:5174` and log in with the admin credentials. The dashboard should display products, inventory levels, and recent orders.

---

## 🏗️ Architecture

### Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend Framework | CodeIgniter | 4.7.0 |
| Backend Language | PHP | 8.5.1+ |
| Database | MySQL | 8.0+ |
| Authentication | JWT (firebase/php-jwt) | 6.11.1 |
| Frontend Framework | Vue.js | 3.4.29 |
| Build Tool | Vite | 5.4.21 |
| HTTP Client | Axios | Latest |
| State Management | Pinia | Latest |
| CSS Framework | Tailwind CSS | 4.x |

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
│  │ - /chat (AI chatbot - multi-agent)                 │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Middleware/Filters                                 │    │
│  │ - AuthJWTFilter (validate bearer tokens)           │    │
│  │ - RoleFilter (permission checks)                   │    │
│  │ - CorsFilter (CORS headers)                        │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Services (Business Logic)                          │    │
│  │ - AuthService (JWT generation/validation)          │    │
│  │ - OrderService (order creation with transactions)  │    │
│  │ - InventoryService (stock management)              │    │
│  │ - StockTransferService (branch transfers)          │    │
│  │ - ChatbotService (multi-agent orchestration)       │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Models (Database Abstraction)                      │    │
│  │ - UserModel, BranchModel, ProductModel             │    │
│  │ - InventoryModel, OrderModel, OrderItemModel       │    │
│  │ - StockTransferModel, InventoryLogModel            │    │
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

### Request Flow

```
1. User opens browser → http://localhost:5174
2. Vue.js loads and prompts for login
3. User enters credentials
4. Frontend sends POST /api/v1/auth/login
5. AuthController validates, AuthService generates JWT
6. Frontend stores access_token in memory
7. Subsequent requests include Authorization: Bearer <token>
8. AuthJWTFilter validates token, RoleFilter checks permissions
9. Controller processes request, calls Service layer
10. Service performs business logic with database transactions
11. Model accesses database via Query Builder
12. JSON response returned to frontend
13. Frontend updates UI reactively
```

### Folder Structure

```
project-root/
├── app/
│   ├── Controllers/Api/V1/       # REST endpoints
│   ├── Filters/                  # JWT, Role, CORS middleware
│   ├── Models/                   # Database abstraction layer
│   ├── Services/                 # Business logic layer
│   ├── Config/                   # Routes, Filters, Database config
│   └── Database/
│       ├── Migrations/           # Schema definitions
│       └── Seeds/               # Test data seeders
├── frontend/
│   └── src/
│       ├── api/                  # Axios HTTP client
│       ├── components/           # Reusable Vue components
│       ├── views/                # Page-level components
│       ├── store/                # Pinia state management
│       └── router/               # Vue Router with guards
├── public/                       # Application entry point
├── writable/                     # Cache, logs, sessions, uploads
└── vendor/                       # PHP dependencies
```

---

## 🗄️ Database Schema

### Core Tables

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| **roles** | System role definitions | ← users.role_id |
| **users** | User accounts with authentication | → roles, → branches (manager) |
| **branches** | Physical store locations | ← users.manager_id |
| **products** | Centralized product catalog | ← inventory, ← order_items |
| **inventory** | Per-branch stock levels | → branches, → products |
| **orders** | Customer orders with totals | → branches, → users |
| **order_items** | Line items within orders | → orders, → products |
| **stock_transfers** | Inter-branch stock movements | → branches (from/to), → products |
| **inventory_logs** | Immutable audit trail | → branches, → products, → users |

### Key Design Decisions

- **Soft Deletes:** `deleted_at` column on users, branches, products, orders
- **Unique Constraints:** SKU (products), email (users), branch+product (inventory), order_number (orders)
- **Foreign Keys:** Enforced at database level for referential integrity
- **Audit Immutability:** inventory_logs are append-only; no UPDATE or DELETE operations

---

## 🔑 Authentication & Roles

### Role Matrix

| Capability | Admin | Branch Manager | Sales User |
|------------|-------|----------------|------------|
| Manage Users | ✅ | ❌ | ❌ |
| Manage Branches | ✅ | ❌ | ❌ |
| Manage Products | ✅ | ❌ | ❌ |
| View All Branches | ✅ | Own only | Own only |
| Create Orders | ✅ | ✅ | ✅ |
| Adjust Inventory | ✅ | ✅ | ❌ |
| Transfer Stock | ✅ | ✅ | ❌ |
| View Audit Logs | ✅ | ✅ | ❌ |
| Approve Transfers | ✅ | ❌ | ❌ |
| Access AI Chatbot | ✅ Full | ✅ Branch-scoped | ❌ |

### Token Lifecycle

1. **Login** → Receive access_token (1hr) + refresh_token (7 days)
2. **Authenticated Requests** → Include `Authorization: Bearer <access_token>`
3. **Token Expiry** → Use refresh_token to obtain new access_token
4. **Logout** → Server invalidates refresh_token

---

## 📡 API Reference

**Base URL:** `http://localhost:8081/api/v1`

### Authentication Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| POST | `/auth/login` | Public | Authenticate and receive tokens |
| POST | `/auth/refresh` | Refresh Token | Obtain new access_token |
| POST | `/auth/logout` | Authenticated | Invalidate refresh_token |
| GET | `/auth/me` | Authenticated | Get current user profile |

### Product Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | `/products` | Authenticated | List products (paginated, searchable) |
| POST | `/products` | Admin | Create new product |
| PUT | `/products/{id}` | Admin | Update product details |
| DELETE | `/products/{id}` | Admin | Soft delete product |

### Branch Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | `/branches` | Admin | List all branches |
| POST | `/branches` | Admin | Create new branch |
| PUT | `/branches/{id}` | Admin | Update branch details |

### Order Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | `/orders` | Authenticated | List orders (branch-scoped) |
| POST | `/orders` | Authenticated | Create order (atomic stock deduction) |
| GET | `/orders/{id}` | Authenticated | Get order with line items |

### Inventory Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | `/inventory` | Authenticated | List stock levels (branch-scoped) |
| POST | `/inventory/adjust` | Admin, Manager | Adjust stock quantity |
| GET | `/inventory/logs` | Admin, Manager | View audit log |

### Transfer Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| POST | `/transfers` | Admin, Manager | Request stock transfer |
| PATCH | `/transfers/{id}/approve` | Admin | Approve pending transfer |

### AI Chatbot Endpoints

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| POST | `/chat` | Admin, Manager | Send natural language query |
| GET | `/chat/history` | Admin, Manager | View chat history |

### Sample: Login Request/Response

```json
// POST /auth/login
{
  "email": "admin@system.com",
  "password": "Admin@12345"
}

// Response
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

### Sample: Create Order

```json
// POST /orders
{
  "branch_id": 1,
  "items": [
    { "product_id": 1, "quantity": 5 },
    { "product_id": 2, "quantity": 3 }
  ]
}

// Response
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order_number": "ORD-20260407-001",
    "subtotal": "200.00",
    "tax_amount": "34.00",
    "grand_total": "234.00",
    "status": "completed",
    "items": [...]
  }
}
```

---

## 🔒 Security

| Layer | Implementation |
|-------|----------------|
| **Authentication** | JWT with access/refresh token rotation, bcrypt password hashing |
| **Authorization** | Role-based filters, branch scoping, endpoint-level permission checks |
| **SQL Injection** | CodeIgniter Query Builder with prepared statements |
| **CORS** | Configurable allowed origins, restricted to frontend domain |
| **Race Conditions** | Pessimistic locking on inventory during order creation |
| **Audit Trail** | Append-only inventory_logs with user, action, timestamp |
| **Token Storage** | In-memory on frontend; httpOnly cookies recommended for production |
| **Input Validation** | Server-side validation on all API endpoints |

---

## 🔄 Development Workflow

1. **Create Feature Branch** from `develop`
2. **Implement Changes** following Architecture.agent.md guidelines
3. **Write Tests** for new functionality
4. **Run Linting & Type Checks** (`npm run lint`, `php spark analyze`)
5. **Submit Pull Request** with description and test results
6. **Code Review** by team member
7. **Merge to Develop** after approval
8. **Deploy to Staging** for integration testing
9. **Release to Production** with version tag

---

## ⚡ Performance Considerations

| Area | Strategy |
|------|----------|
| **Database Indexes** | Primary keys, foreign keys, unique constraints, frequently queried columns |
| **Query Optimization** | Eager loading to prevent N+1 queries, selective field retrieval |
| **Pagination** | All list endpoints support `page` and `per_page` parameters |
| **Caching** | CodeIgniter cache for static data (roles, product catalog) |
| **JWT Statelessness** | No session storage; tokens are self-contained and validated independently |
| **Frontend Bundle** | Vite code splitting, lazy-loaded routes, tree-shaking |

---

## 🧪 Testing

### Backend Testing

```bash
# Run PHPUnit tests
php spark test

# Run specific test suite
vendor/bin/phpunit --filter OrderTest
```

### Manual Testing Scenarios

1. **Login Flow** — Authenticate with each role and verify access levels
2. **Order Creation** — Create order, verify inventory deduction and audit log
3. **Stock Transfer** — Request transfer, approve, verify both branch inventories
4. **Negative Stock Prevention** — Attempt to order more than available stock
5. **Branch Isolation** — Login as manager, confirm only own branch visible

### API Testing with cURL

```bash
# Test login
curl -X POST http://localhost:8081/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@system.com","password":"Admin@12345"}'

# Test authenticated endpoint
curl http://localhost:8081/api/v1/products \
  -H "Authorization: Bearer <access_token>"
```

---

## 📋 Deployment Checklist

- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Generate secure `JWT_SECRET` (minimum 32 characters)
- [ ] Enable HTTPS (`app.cookieSecure = true`)
- [ ] Configure CORS for production domain
- [ ] Move `writable/` directory outside web root
- [ ] Set up automated database backups
- [ ] Configure production logging
- [ ] Enable OPcache for PHP
- [ ] Set up monitoring and alerting
- [ ] Test all API endpoints in staging environment
- [ ] Create production admin account
- [ ] Document API endpoints for development team
- [ ] Configure CI/CD pipeline for automated deployments

---

## 🔧 Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| **CORS Error** | Frontend origin not in allowed list | Add frontend URL to CORS config in `.env` |
| **JWT Token Expired** | Access token expired (1 hour) | Use `/auth/refresh` with refresh_token |
| **Insufficient Stock** | Order quantity exceeds available inventory | Check inventory before creating order |
| **Port Already in Use** | Another process using port 8081 | Use different port: `php spark serve --port 9000` |
| **MySQL Connection Failed** | Incorrect database credentials in `.env` | Verify database name, username, password |
| **VITE Cannot Find API** | Missing or incorrect `VITE_API_URL` | Create `frontend/.env.local` with correct URL |
| **Blank Frontend** | Build error or missing dependencies | Run `npm install` and check browser console |

---

## 🤝 Contributing

1. **Fork the Repository**
2. **Create Feature Branch** (`git checkout -b feature/amazing-feature`)
3. **Commit Changes** (`git commit -m 'feat: add amazing feature'`)
4. **Push to Branch** (`git push origin feature/amazing-feature`)
5. **Open Pull Request** with detailed description

### Commit Message Convention

Follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` — New feature
- `fix:` — Bug fix
- `docs:` — Documentation changes
- `style:` — Formatting changes
- `refactor:` — Code refactoring
- `test:` — Test additions or changes
- `chore:` — Build or tooling changes

---

## 🗺️ Roadmap

| Feature | Status | Description |
|---------|--------|-------------|
| Barcode/QR Scanning | Planned | Mobile scanning for product lookup and stock adjustments |
| Advanced Reporting | Planned | Sales reports, inventory valuation, trend analysis |
| Multi-Currency Support | Planned | Support for international currencies and exchange rates |
| Supplier Management | Planned | Vendor database, purchase orders, supplier performance tracking |
| Mobile App | Planned | React Native app for field operations |
| Email Notifications | Planned | Low-stock alerts, order confirmations, transfer approvals |
| Advanced AI Chatbot | In Progress | Predictive analytics, automated insights, trend forecasting |
| API Rate Limiting | Planned | Prevent abuse with configurable rate limits |

---

## 📚 Additional Documentation

- **Architecture Decisions:** `Architecture.agent.md`
- **AI Development Workflow:** `AI_SDLC.md`
- **Multi-Agent Chatbot Architecture:** `docs/MULTI_AGENT_ARCHITECTURE.md`
- **API Documentation:** `docs/API.md`
- **Technical Assignment:** `Technical Assignment – CodeIgniter + AI-Driven Development.pdf`

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 📞 Support

For issues, questions, or contributions:

- **Email:** muhammadabuhurairah22@example.com
- **Documentation:** See the `/docs` directory
- **Issues:** Open an issue in the repository

---

**Last Updated:** April 7, 2026  
**Version:** 1.0.0 (Phase 2 Complete — AI-Driven Development)
