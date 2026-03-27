# Multi-Branch Inventory & Order Management System

## System Overview
A production-ready Multi-Branch Inventory & Order Management System built with **CodeIgniter 4** (RESTful API backend) and **Vue.js 3** (frontend SPA). Supports multiple branches, each with independent stock, role-based access control, safe concurrent order processing, and a full stock transfer workflow.

## Architecture

### Tech Stack
| Layer | Technology |
|---|---|
| Backend | CodeIgniter 4 (PHP 8.1+) |
| Database | MySQL 8.0+ |
| Auth | JWT (firebase/php-jwt) |
| Frontend | Vue.js 3 + Vite + Pinia |
| API | RESTful, versioned at `/api/v1/` |

### MVC + Service Layer Implementation
Controllers are **thin** — they only handle HTTP input/output. All business logic lives in `app/Services/`.

```
HTTP Request → Router → Filter (JWT + RBAC) → Controller → Service → Model → DB
                                                         ↑
                                               Validation Rules
```

### Database Design
- **inventory** table: single source of truth for per-branch stock (unique index on `branch_id + product_id`)
- **inventory_logs**: immutable ledger of every stock movement (add/adjust/sale/transfer)
- **DECIMAL(15,4)** for all financial columns
- **SELECT ... FOR UPDATE** inside transactions prevents overselling
- Foreign keys prevent branch deletion if stock exists

### API Structure
All endpoints versioned under `/api/v1/`. Auth via `Authorization: Bearer <jwt>`.

## Setup Instructions

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js 18+

### Backend Setup
```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp env .env
# Edit .env: set database credentials, JWT_SECRET, baseURL

# 3. Run migrations
php spark migrate

# 4. Seed database
php spark db:seed RoleSeeder
php spark db:seed UserSeeder

# 5. Start server
php spark serve
# → API available at http://localhost:8080
```

### Frontend Setup
```bash
cd frontend

# Install dependencies
npm install

# Configure API base URL
cp .env.example .env
# Edit VITE_API_URL=http://localhost:8080/api/v1

# Start dev server
npm run dev
# → UI available at http://localhost:5173
```

## Test Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@system.com | Admin@12345 |
| Branch Manager | manager@branch1.com | Manager@12345 |
| Sales User | sales@branch1.com | Sales@12345 |

## Key Features
- ✅ JWT Authentication with refresh tokens + logout blacklisting
- ✅ Role-Based Access Control (Admin / Branch Manager / Sales User)
- ✅ Multi-branch inventory management
- ✅ Stock never goes negative (DB constraint + pessimistic lock)
- ✅ Atomic order processing with database transactions
- ✅ Full stock transfer workflow (request → approve → complete)
- ✅ Immutable inventory audit ledger
- ✅ RESTful API versioned at `/api/v1`
- ✅ Vue.js SPA with responsive dashboard

## API Documentation
See `docs/API.md` for complete endpoint reference.

## Architecture Documentation
See `docs/ARCHITECTURE.md` for detailed design decisions.

## Git Workflow
Commit history follows logical development phases:
1. `init: project scaffold & architecture`
2. `feat(db): migrations and seeders`  
3. `feat(auth): JWT authentication`
4. `feat(products): product CRUD`
5. `feat(inventory): stock management + ledger`
6. `feat(orders): transactional order processing`
7. `feat(transfers): cross-branch transfer workflow`
8. `feat(frontend): Vue.js SPA integration`
