# 📋 Project Memory & Documentation Guide

## 📁 Where Everything Is Located

### Core Documentation Files (in `/docs/`)
| File | Purpose |
|------|---------|
| **MEMORY_PROJECT_STATUS.md** | ✅ Completed features, pending work, quick reference |
| **MEMORY_DESIGN_SYSTEM.md** | 🎨 Color palette, design rules, component status |
| **MEMORY_SESSION_PROGRESS.md** | 📝 Session history, workflow, alignment items |
| **REFERENCE_GUIDE.md** | 📋 THIS FILE - Quick navigation & instructions |

### Architecture & System Files (in root)
| File | Purpose |
|------|---------|
| **Architecture.agent.md** | 🏗️ MAIN REFERENCE - System structure, API routes, database schema |
| **.env** | 🔑 Environment variables (backend config) |
| **frontend/.env** | 🔑 Frontend environment variables |

### Backend Code Structure
```
app/
├── Controllers/Api/V1/     - API endpoints (Orders, Inventory, Products, etc.)
├── Services/               - Business logic (OrderService, InventoryService, etc.)
├── Models/                 - Database models
├── Filters/                - Auth & CORS filters
├── Exceptions/             - Custom exceptions
└── Database/               - Migrations & Seeds
```

### Frontend Code Structure
```
frontend/src/
├── views/                  - Page components (Dashboard, Orders, Inventory)
├── store/                  - Pinia stores (auth.store.js)
├── api/                    - Axios client configuration
├── components/             - Reusable Vue components
└── router/                 - Vue Router configuration
```

---

## 🚀 When to Check These Files

### Before Starting ANY Change
1. **Read** `MEMORY_PROJECT_STATUS.md` (30 seconds)
   - What's already done?
   - What's pending?
   - What were recent changes?

2. **Read** `Architecture.agent.md` (2 minutes)
   - What's the system structure?
   - What are the API routes?
   - What are the backend/frontend patterns?

3. **Read** `MEMORY_DESIGN_SYSTEM.md` (1 minute)
   - What colors should I use?
   - What design rules apply?

### Before Making Code Changes
4. **Check** the specific component/API being modified
   - Follow the patterns documented in Architecture.agent.md

5. **After completing** your changes
6. **Update** `MEMORY_SESSION_PROGRESS.md` with what you did

---

## 📞 Quick Reference

### Color Palette (From Design System)
```
PRIMARY: rose-700 (#be185d)
LIGHT: gray-50
BORDERS: gray-200
SUCCESS: #10b981
WARNING: #f59e0b
ERROR: #ef4444
INFO: #3b82f6
```

### API Endpoints (Base: http://localhost:8000/api/v1)
```
Auth: POST /auth/login, /auth/logout
Orders: GET /orders, POST /orders, PUT /orders/{id}/status
Inventory: GET /inventory, POST /inventory/add, POST /inventory/adjust, POST /inventory/transfer
Products: GET /products
Branches: GET /branches
Transfers: GET /transfers, POST /transfers
Dashboard: GET /dashboard/stats
```

### User Roles (Database IDs)
```
Admin = 1         (All access)
Manager = 2       (Own branches only)
SalesUser = 3     (Order creation only)
```

### Database Tables
```
users, branches, products, inventory, orders, order_items, 
inventory_logs, stock_transfers
```

### Key URLs
```
Backend: http://localhost:8000
Frontend: http://localhost:5173
```

---

## ✅ Completed Features Checklist

### Backend
- [x] Authentication (JWT)
- [x] Role-Based Access Control
- [x] API Endpoints (all major)
- [x] Service Layer Pattern
- [x] CORS Filter
- [x] Database Models
- [x] Stock Movement Tracking

### Frontend
- [x] Dashboard (Admin/Manager/Sales views)
- [x] Order Management
- [x] Inventory Management
- [x] Products View
- [x] Pinia Authentication Store
- [x] API Client
- [x] Role-Based Page Access

### Design/UI
- [x] Design System (soft pastel)
- [x] Dashboard KPI Cards
- [x] Recent Activity Feed
- [x] Top Selling Products (with branches + real trends)
- [x] Stock Health Chart
- [x] Sales User Dashboard

---

## 🔄 In Progress / Pending

### Needs Attention
- [ ] Color consistency (rose-700 vs accent-pink-500 standardization)
- [ ] Mobile sidebar menu
- [ ] Form validation enhancements
- [ ] Advanced search/filters
- [ ] Export reports (PDF/Excel)

---

## 📝 How to Update These Files

### After completing a feature:

**Update MEMORY_PROJECT_STATUS.md**:
1. Add feature to ✅ Completed Features section
2. Add entry to "Recent Changes (Last 5)"
3. Move from "Pending Features" if applicable

**Update MEMORY_SESSION_PROGRESS.md**:
1. Add session summary
2. Document what was completed
3. Note any alignment items

---

## 🎯 The Golden Rule

**READ → ARCHITECTURE → PLAN → EXECUTE → LOG**

Always follow this order before making changes to the system.

---

## ❓ Questions?

- **"What's already done?"** → Check MEMORY_PROJECT_STATUS.md
- **"What colors should I use?"** → Check MEMORY_DESIGN_SYSTEM.md  
- **"How is the system structured?"** → Check Architecture.agent.md
- **"Where is this feature?"** → Check the backend/frontend code structure above
- **"What happened last time?"** → Check MEMORY_SESSION_PROGRESS.md

---

**Created**: March 30, 2026
**Last Updated**: March 30, 2026
