# Role-Based Access Control (RBAC) Matrix

## System Roles

| Role ID | Role Name | Description | Branch Scope |
|---------|-----------|-------------|--------------|
| **1** | Admin | System administrator with full access | None (global) |
| **2** | Branch Manager | Branch manager with inventory management | Assigned branch only |
| **3** | Sales User | Sales representative for order creation | Assigned branch only |

---

## Permission Matrix

### As Per Requirements

| Feature | Admin | Manager | Sales User |
|---------|-------|---------|------------|
| **Manage Branches** | ✅ Yes | ❌ No | ❌ No |
| **Manage Products** | ✅ Yes | ❌ No | ❌ No |
| **Manage Inventory** | ✅ Yes | ✅ Own Branch | ❌ No |
| **Create Orders** | ❌ No | ✅ Yes | ✅ Yes |
| **View Reports** | ✅ Yes | ✅ Own Branch | ❌ No |

---

## Current Implementation Status

### ✅ Branches Management
- **Read (GET)**: All authenticated roles
- **Create/Update/Delete (POST/PUT/DELETE)**: Admin only
- **Route Filter**: `'role:admin'` on write operations
- **Status**: ✅ MATCHES REQUIREMENTS

### ✅ Products Management  
- **Read (GET)**: All authenticated roles
- **Create/Update/Delete (POST/PUT/DELETE)**: Admin only
- **Route Filter**: `'role:admin'` on write operations
- **Status**: ✅ MATCHES REQUIREMENTS

### ✅ Inventory Management
- **Read (GET)**: All authenticated roles (branch-scoped for managers/sales)
- **Add/Adjust Stock (POST)**: Admin + Branch Manager
- **Route Filter**: `'role:admin,branch_manager'` on write operations
- **Controller Logic**: Managers auto-scoped to their branch
- **Status**: ✅ MATCHES REQUIREMENTS

### ⚠️ Orders Management (NEEDS ADJUSTMENT)
- **Read (GET)**: All authenticated roles (branch-scoped)
- **Create (POST)**: Currently all authenticated users ❌
- **Cancel (POST)**: All authenticated users
- **Route Filter**: None currently
- **Status**: ❌ DOES NOT MATCH - Admin should NOT create orders per requirements

### ⚠️ Reports (NOT YET IMPLEMENTED)
- **View Reports**: Should be available to Admin (all) + Manager (own branch)
- **Sales User**: Should NOT have access
- **Status**: 🔲 NOT IMPLEMENTED

---

## JWT Token Payload Example

```json
{
  "iss": "http://localhost:8081/",
  "sub": "1",
  "jti": "unique-token-id",
  "iat": 1700000000,
  "exp": 1700003600,
  "type": "access",
  "role_id": "1",
  "branch_id": null
}
```

**For Admin (role_id=1, branch_id=null):**
- branch_id is null (no scope)
- Can access all data regardless of branch

**For Manager/Sales (role_id=2,3, branch_id=1):**
- branch_id is set to assigned branch
- Controllers enforce branch scoping

---

## Route-Level Access Control

### Protected with Role Filter

| Endpoint | Method | Required Role | Status |
|----------|--------|----------------|--------|
| `/branches` | POST/PUT/DELETE | Admin | ✅ |
| `/products` | POST/PUT/DELETE | Admin | ✅ |
| `/inventory/add` | POST | Admin, Manager | ✅ |
| `/inventory/adjust` | POST | Admin, Manager | ✅ |
| `/transfers/.*/approve` | POST | Admin, Manager | ✅ |
| `/transfers/.*/reject` | POST | Admin, Manager | ✅ |
| `/orders` | POST | TBD (see issues) | ⚠️ |

### No Filter (All Authenticated Users)

| Endpoint | Method | Access |
|----------|--------|--------|
| `/branches` | GET | All (data scoped in controller) |
| `/products` | GET | All (global data) |
| `/inventory` | GET | All (data scoped in controller) |
| `/orders` | GET/POST | All (data scoped in controller) |

---

## Implementation Files

| File | Purpose | Details |
|------|---------|---------|
| `app/Config/Routes.php` | Route definitions + role filters | Declares role requirements via filter |
| `app/Filters/RoleFilter.php` | Validates user role | Checks if role_id matches allowed roles |
| `app/Filters/AuthJWTFilter.php` | JWT validation | Extracts role_id and branch_id |
| `app/Controllers/Api/V1/OrderController.php` | Order CRUD | Branch scoping logic |
| `app/Controllers/Api/V1/InventoryController.php` | Inventory ops | Manager branch limiting |
| `app/Database/Seeds/UserSeeder.php` | Test users | Creates test credentials |

---

## Test Credentials

```bash
# Admin (full system access)
Email: admin@system.com
Password: Admin@12345
role_id: 1, branch_id: NULL

# Branch Manager (Branch 1 only)
Email: manager@branch1.com
Password: Manager@12345
role_id: 2, branch_id: 1

# Sales User (Branch 1 only)
Email: sales@branch1.com
Password: Sales@12345
role_id: 3, branch_id: 1
```

---

## Issues to Fix

### 🔴 Issue 1: Admin Can Create Orders (VIOLATES REQUIREMENT)
- **Current**: POST /api/v1/orders allowed for all authenticated users
- **Required**: POST /api/v1/orders should block Admin role
- **Fix Needed**: Add role filter `'role:branch_manager,sales_user'` to order creation

### 🔴 Issue 2: Reports Endpoint Missing
- **Current**: No /reports endpoint exists
- **Required**: Endpoint should exist with role+branch scoping
- **Fix Needed**: Create ReportsController with proper RBAC

### 🔴 Issue 3: Sales User Can View Reports (VIOLATES REQUIREMENT)
- **Current**: No report access control
- **Required**: Sales users should NOT have report access
- **Fix Needed**: Reports filter should use `'role:admin,branch_manager'`

---

## Security Checklist

- ✅ Role stored in JWT token
- ✅ Branch isolation enforced at controller level
- ✅ Route-level role filters prevent unauthorized access
- ✅ Client-supplied branch_id overridden in controllers
- ✅ Soft deletes prevent data leakage
- ⚠️ Admin restricted from order creation (needs implementation)
- ⚠️ Reports access control (needs implementation)

---

## Next Steps

1. **Fix Order Creation Filter** - Prevent Admin from creating orders
2. **Create Reports Endpoint** - With proper role+branch filtering
3. **Add Audit Logging** - Track who did what by role
4. **Test Enforcement** - Verify each role can only access allowed data
