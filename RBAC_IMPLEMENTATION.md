# Role-Based Access Control (RBAC) - Complete Implementation

## System Roles Defined

| Role ID | Role Name | User Count | Access Scope |
|---------|-----------|------------|--------------|
| **1** | `admin` | 1 test user | Global (all branches) |
| **2** | `branch_manager` | 1 test user | Branch 1 only |
| **3** | `sales_user` | 1 test user | Branch 1 only |

---

## Permission Matrix - VERIFIED ✅

| Feature | Admin | Manager | Sales User | Implementation |
|---------|-------|---------|------------|-----------------|
| **Manage Branches** | ✅ Yes | ❌ No | ❌ No | Route filter: `role:admin` |
| **Manage Products** | ✅ Yes | ❌ No | ❌ No | Route filter: `role:admin` |
| **Manage Inventory** | ✅ Yes | ✅ Own Branch | ❌ No | Route filter: `role:admin,branch_manager` |
| **Create Orders** | ❌ **No** | ✅ Yes | ✅ Yes | Route filter: `role:branch_manager,sales_user` |
| **View Orders** | ✅ All | ✅ Own Branch | ✅ Own Branch | No filter (branch-scoped in controller) |
| **View Reports** | ✅ Yes | ✅ Own Branch | ❌ No | Not yet implemented |

---

## Access Control Architecture

### 1. Authentication Layer
**File**: `app/Filters/AuthJWTFilter.php`

- Validates JWT bearer token signature (HS256)
- Extracts payload: `{role_id, branch_id, ...}`
- Stores in request: `$request->authPayload`
- Returns 401 Unauthorized if invalid/expired

### 2. Authorization Layer  
**File**: `app/Filters/RoleFilter.php`

- Parses route filter argument (e.g., `'role:admin,branch_manager'`)
- Converts role names to IDs using map
- Compares user's role_id against allowed IDs
- Returns 403 Forbidden if unauthorized
- Includes CORS headers on error responses

### 3. Branch Scoping Layer
**Files**: `app/Controllers/Api/V1/*.php`

- Managers/Sales users forced to their assigned branch
- Controllers override `branch_id` from client requests
- Example: `$data['branch_id'] = $actor->branch_id;`
- Prevents privilege escalation (user can't access other branches)

### 4. Data Access Layer
**Files**: `app/Models/*.php` and `app/Services/*.php`

- Soft deletes via `deleted_at` column
- Foreign keys prevent orphaned data
- No role-specific queries (role enforcement at controller level)
- Services don't know about authorization

---

## Route-Level Permissions

### Public Endpoints (No JWT Required)

```
POST   /api/v1/auth/login          # Login to get JWT token
POST   /api/v1/auth/refresh        # Refresh expiring token
```

### Protected Endpoints - All Authenticated Users

```
GET    /api/v1/branches            # View branches (all users, branch-scoped) ✅
GET    /api/v1/products            # View products (all users, global) ✅
GET    /api/v1/inventory           # View inventory (all users, branch-scoped) ✅
GET    /api/v1/orders              # View orders (all users, branch-scoped) ✅
GET    /api/v1/transfers           # View transfers (all users) ✅
```

### Admin-Only Operations

```
POST   /api/v1/branches            # Create branch — role:admin ✅
PUT    /api/v1/branches/:id        # Update branch — role:admin ✅
DELETE /api/v1/branches/:id        # Delete branch — role:admin ✅

POST   /api/v1/products            # Create product — role:admin ✅
PUT    /api/v1/products/:id        # Update product — role:admin ✅
DELETE /api/v1/products/:id        # Delete product — role:admin ✅
```

### Branch Manager Access

```
POST   /api/v1/inventory/add       # Add stock — role:admin,branch_manager ✅
POST   /api/v1/inventory/adjust    # Adjust stock — role:admin,branch_manager ✅
GET    /api/v1/inventory           # Limited to own branch ✅
```

### Manager + Sales User Access

```
POST   /api/v1/orders              # Create order — role:branch_manager,sales_user ✅
POST   /api/v1/orders/:id/cancel   # Cancel order — role:branch_manager,sales_user ✅
GET    /api/v1/orders              # Limited to own branch ✅
```

### Manager Approval Workflows

```
POST   /api/v1/transfers/:id/approve   # Approve transfer — role:admin,branch_manager
POST   /api/v1/transfers/:id/reject    # Reject transfer — role:admin,branch_manager
POST   /api/v1/transfers/:id/complete  # Complete transfer — role:admin,branch_manager
```

---

## Test Credentials & Results

### Admin User
```
Email:    admin@system.com
Password: Admin@12345
Role:     Admin (1)
Branch:   None (global access)

Permissions:
  • Create branches ✅
  • Create products ✅
  • Manage all inventory ✅
  • Create orders ❌ BLOCKED (per requirement)
  • View all data ✅
```

**Test: Try to create order**
```
Response: 403 Forbidden
Message: "Forbidden: You do not have permission to access this resource."
```

### Branch Manager  
```
Email:    manager@branch1.com
Password: Manager@12345
Role:     Branch Manager (2)
Branch:   Branch 1

Permissions:
  • Create branches ❌ BLOCKED
  • Create products ❌ BLOCKED
  • Manage Branch 1 inventory ✅
  • Create orders in Branch 1 ✅
  • View Branch 1 data ✅
  • View other branches ❌ BLOCKED
```

**Test: Create order**
```
Response: 201 Created
Data: {order_id: ..., order_number: ..., total: ...}
```

### Sales User
```
Email:    sales@branch1.com
Password: Sales@12345
Role:     Sales User (3)
Branch:   Branch 1

Permissions:
  • Create branches ❌ BLOCKED
  • Create products ❌ BLOCKED
  • Manage inventory ❌ BLOCKED
  • Create orders in Branch 1 ✅
  • View Branch 1 orders ✅
  • View branch 1 inventory ✅
  • View other branches ❌ BLOCKED
```

**Test: Create order**
```
Response: 201 Created
Data: {order_id: ..., order_number: ..., total: ...}
```

---

## Security Features

### ✅ Implemented

1. **JWT Token-Based Auth**
   - HS256 signature with secret key
   - 1-hour access token TTL
   - 7-day refresh token TTL
   - Token blacklist on logout

2. **Role-Based Access Control**
   - 3 roles with distinct permissions
   - Route-level enforcement via filters
   - Role IDs embedded in JWT

3. **Branch Isolation**
   - Non-admin users forced to assigned branch
   - Server-side enforcement (client input overridden)
   - Branch-scoped queries in controllers

4. **SQL Injection Prevention**
   - Prepared statements via ORM
   - No raw SQL in business logic

5. **Authorization Errors Include CORS Headers**
   - 403 responses include Access-Control-Allow-Origin
   - Frontend can properly receive permission errors

6. **Audit Trail**
   - Soft deletes maintain change history
   - Inventory logs track all stock movements
   - Created_at timestamps on all records

### ⚠️ Not Yet Implemented

1. **Reports Endpoint**
   - Should have role:admin,branch_manager filter
   - Sales users should NOT see reports
   - Endpoint doesn't exist yet

2. **Audit Logging**
   - Who made what changes and when
   - Not yet tracked in separate audit table

3. **API Rate Limiting**
   - No request throttling per role
   - Could abuse with many requests

4. **Multi-Factor Authentication**
   - Only email/password currently
   - Could add 2FA for admin accounts

---

## Implementation Files Reference

| File | Purpose | Lines | Role Checks |
|------|---------|-------|-------------|
| `app/Config/Routes.php` | Route definitions | 100+ | Yes (filter argument) |
| `app/Filters/RoleFilter.php` | Role validation | 70 | Yes (core logic) |
| `app/Filters/AuthJWTFilter.php` | JWT validation | 100+ | Payload extraction |
| `app/Controllers/Api/V1/BranchController.php` | Branch CRUD | 80+ | Branch admin check |
| `app/Controllers/Api/V1/OrderController.php` | Order operations | 100+ | Branch scoping |
| `app/Controllers/Api/V1/InventoryController.php` | Stock management | 120+ | Manager branch limit |
| `app/Models/UserModel.php` | User data layer | 30 | No role logic |
| `app/Database/Seeds/UserSeeder.php` | Test data | 50 | Creates 3 test users |

---

## How to Add New Roles

If you need additional roles (e.g., Accountant, Auditor):

1. **Add to database**: Insert new role into `roles` table
2. **Add to RoleFilter**: Update `ROLE_MAP` in `app/Filters/RoleFilter.php`
3. **Add test user**: Update `UserSeeder.php` with new test credentials
4. **Restrict routes**: Use filter `'role:role_name'` on routes
5. **Branch scoping**: Add logic in controller if needed

---

## Testing Checklist

- [x] Admin: Can manage branches/products
- [x] Admin: CANNOT create orders (403 Forbidden)
- [x] Manager: CAN create orders (201 Created)
- [x] Manager: Can ONLY see own branch data
- [x] Sales: CAN create orders (201 Created)
- [x] Sales: Can ONLY see own branch data
- [x] Invalid token: 401 Unauthorized
- [x] Expired token: 401 Unauthorized  
- [x] Missing role: 403 Forbidden
- [x] CORS headers on all responses

---

## Production Readiness

✅ **Role enforcement** working correctly  
✅ **Branch isolation** server-side enforced  
✅ **JWT tokens** with proper expiration  
✅ **Error responses** include CORS headers  
✅ **Test credentials** available for QA  

⚠️ **Reports endpoint** not yet implemented
⚠️ **Rate limiting** should be added before production
⚠️ **Audit logging** should be enhanced
