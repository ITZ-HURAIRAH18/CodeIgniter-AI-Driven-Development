# 🏗️ Multi-Branch Inventory & Order Management System
## Staff-Level Architecture Design Document

---

## 1. DATABASE ARCHITECTURE (Single Source of Truth)

### Complete SQL DDL

```sql
-- ============================================================
-- DATABASE: branch_inventory_db
-- ENGINE: InnoDB (for FK constraints + row-level locking)
-- CHARSET: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS branch_inventory_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE branch_inventory_db;

-- ------------------------------------------------------------
-- ROLES & USERS
-- ------------------------------------------------------------
CREATE TABLE roles (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(50)      NOT NULL,           -- 'admin','branch_manager','sales_user'
    description VARCHAR(255)     NULL,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_role_name (name)
) ENGINE=InnoDB;

CREATE TABLE users (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    role_id      TINYINT UNSIGNED NOT NULL,
    branch_id    INT UNSIGNED     NULL,               -- NULL for admin (no fixed branch)
    name         VARCHAR(100)     NOT NULL,
    email        VARCHAR(150)     NOT NULL,
    password     VARCHAR(255)     NOT NULL,           -- Argon2id hash
    is_active    TINYINT(1)       NOT NULL DEFAULT 1,
    last_login   DATETIME         NULL,
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at   DATETIME         NULL,               -- soft delete
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email),
    KEY idx_role (role_id),
    KEY idx_branch (branch_id),
    CONSTRAINT fk_users_role   FOREIGN KEY (role_id)   REFERENCES roles(id),
    CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- BRANCHES
-- ------------------------------------------------------------
CREATE TABLE branches (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    manager_id INT UNSIGNED NULL,                     -- FK to users (set after users created)
    name       VARCHAR(100) NOT NULL,
    address    TEXT         NULL,
    phone      VARCHAR(30)  NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_branch_name (name),
    KEY idx_manager (manager_id)
    -- FK to manager added after users table created (ALTER below)
) ENGINE=InnoDB;

ALTER TABLE branches
    ADD CONSTRAINT fk_branches_manager
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE users
    ADD CONSTRAINT fk_users_branch
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- PRODUCTS
-- ------------------------------------------------------------
CREATE TABLE products (
    id              INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    sku             VARCHAR(100)       NOT NULL,
    name            VARCHAR(200)       NOT NULL,
    description     TEXT               NULL,
    cost_price      DECIMAL(15,4)      NOT NULL DEFAULT 0.0000,
    sale_price      DECIMAL(15,4)      NOT NULL DEFAULT 0.0000,
    tax_percentage  DECIMAL(5,2)       NOT NULL DEFAULT 0.00,   -- e.g. 17.00 for 17%
    unit            VARCHAR(30)        NULL DEFAULT 'pcs',
    status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at      DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sku (sku),
    KEY idx_status (status),
    FULLTEXT KEY ft_product_search (name, sku)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- INVENTORY (per-branch stock levels — Single Source of Truth)
-- ------------------------------------------------------------
CREATE TABLE inventory (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    branch_id    INT UNSIGNED    NOT NULL,
    product_id   INT UNSIGNED    NOT NULL,
    quantity     INT             NOT NULL DEFAULT 0
                     CHECK (quantity >= 0),            -- DB-level guard
    reorder_level INT            NOT NULL DEFAULT 0,
    updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_branch_product (branch_id, product_id),   -- one row per branch+product
    KEY idx_inv_product (product_id),
    CONSTRAINT fk_inv_branch   FOREIGN KEY (branch_id)  REFERENCES branches(id),
    CONSTRAINT fk_inv_product  FOREIGN KEY (product_id) REFERENCES products(id)
    -- NOTE: FK from branches prevents deletion of a branch that has stock
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- INVENTORY LEDGER (immutable audit trail for every movement)
-- ------------------------------------------------------------
CREATE TABLE inventory_logs (
    id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    branch_id     INT UNSIGNED     NOT NULL,
    product_id    INT UNSIGNED     NOT NULL,
    user_id       INT UNSIGNED     NOT NULL,
    movement_type ENUM('add','adjust','transfer_in','transfer_out','sale','return')
                                   NOT NULL,
    reference_type VARCHAR(50)     NULL,   -- 'order','transfer','manual'
    reference_id  INT UNSIGNED     NULL,   -- FK to orders.id / stock_transfers.id
    qty_before    INT              NOT NULL,
    qty_change    INT              NOT NULL,  -- positive = increase, negative = decrease
    qty_after     INT              NOT NULL,
    notes         TEXT             NULL,
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_branch   (branch_id),
    KEY idx_log_product  (product_id),
    KEY idx_log_ref      (reference_type, reference_id),
    KEY idx_log_created  (created_at),
    CONSTRAINT fk_log_branch  FOREIGN KEY (branch_id)  REFERENCES branches(id),
    CONSTRAINT fk_log_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_log_user    FOREIGN KEY (user_id)    REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- STOCK TRANSFERS (between branches)
-- ------------------------------------------------------------
CREATE TABLE stock_transfers (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    from_branch_id  INT UNSIGNED NOT NULL,
    to_branch_id    INT UNSIGNED NOT NULL,
    initiated_by    INT UNSIGNED NOT NULL,    -- user_id
    status          ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
    notes           TEXT         NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_from (from_branch_id),
    KEY idx_to   (to_branch_id),
    CONSTRAINT fk_st_from FOREIGN KEY (from_branch_id) REFERENCES branches(id),
    CONSTRAINT fk_st_to   FOREIGN KEY (to_branch_id)   REFERENCES branches(id),
    CONSTRAINT fk_st_user FOREIGN KEY (initiated_by)   REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE stock_transfer_items (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    transfer_id     INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    quantity        INT          NOT NULL CHECK (quantity > 0),
    PRIMARY KEY (id),
    UNIQUE KEY uq_transfer_product (transfer_id, product_id),
    CONSTRAINT fk_sti_transfer FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id),
    CONSTRAINT fk_sti_product  FOREIGN KEY (product_id)  REFERENCES products(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ORDERS
-- ------------------------------------------------------------
CREATE TABLE orders (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    branch_id       INT UNSIGNED    NOT NULL,
    user_id         INT UNSIGNED    NOT NULL,   -- who created the order
    order_number    VARCHAR(30)     NOT NULL,   -- e.g. ORD-2025-00001
    status          ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    subtotal        DECIMAL(15,4)   NOT NULL DEFAULT 0.0000,
    tax_amount      DECIMAL(15,4)   NOT NULL DEFAULT 0.0000,
    grand_total     DECIMAL(15,4)   NOT NULL DEFAULT 0.0000,
    notes           TEXT            NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_order_number (order_number),
    KEY idx_order_branch (branch_id),
    KEY idx_order_user   (user_id),
    KEY idx_order_status (status),
    KEY idx_order_created (created_at),
    CONSTRAINT fk_order_branch FOREIGN KEY (branch_id) REFERENCES branches(id),
    CONSTRAINT fk_order_user   FOREIGN KEY (user_id)   REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    order_id    INT UNSIGNED   NOT NULL,
    product_id  INT UNSIGNED   NOT NULL,
    quantity    INT            NOT NULL CHECK (quantity > 0),
    unit_price  DECIMAL(15,4)  NOT NULL,
    tax_pct     DECIMAL(5,2)   NOT NULL DEFAULT 0.00,
    tax_amount  DECIMAL(15,4)  NOT NULL DEFAULT 0.0000,
    line_total  DECIMAL(15,4)  NOT NULL,
    PRIMARY KEY (id),
    KEY idx_oi_order   (order_id),
    KEY idx_oi_product (product_id),
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id),
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- JWT TOKEN BLACKLIST (for logout invalidation)
-- ------------------------------------------------------------
CREATE TABLE token_blacklist (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    token_jti  VARCHAR(100)    NOT NULL,   -- JWT unique ID claim
    expires_at DATETIME        NOT NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_jti (token_jti),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB;
```

---

## 2. BACKEND SERVICE LAYER PATTERN

### Concurrency Control Strategy

**Problem:** Two concurrent requests for the last item both read `quantity=1`, both deduct, leaving `quantity=-1`.

**Solution:** Pessimistic locking with `SELECT ... FOR UPDATE` inside a transaction.

```
OrderService::createOrder($data, $actor)
───────────────────────────────────────
1.  VALIDATE input (OrderValidator)
2.  $db->transStart()
3.  FOR EACH item in $data['items']:
    a. SELECT quantity FROM inventory
       WHERE branch_id=? AND product_id=?
       FOR UPDATE;                    ← row-level lock acquired
    b. IF quantity < requested → throw InsufficientStockException
    c. qty_before = quantity
    d. UPDATE inventory
       SET quantity = quantity - requested
       WHERE branch_id=? AND product_id=?
    e. INSERT INTO inventory_logs
       (movement_type='sale', qty_before, qty_change=-requested, qty_after)
4.  Generate order_number (atomic sequence)
5.  INSERT INTO orders (...)
6.  INSERT INTO order_items (...)
7.  $db->transCommit()               ← lock released
8.  RETURN OrderEntity
ON EXCEPTION:
    $db->transRollback()
    RE-THROW with context
```

```
StockTransferService::complete($transferId, $actor)
────────────────────────────────────────────────────
1.  LOAD transfer + items
2.  VALIDATE status === 'approved'
3.  $db->transStart()
4.  FOR EACH item:
    a. Lock FROM-branch inventory   → FOR UPDATE
    b. Lock TO-branch inventory     → FOR UPDATE  (always lock in consistent order: lower branch_id first to avoid deadlock)
    c. Check from_branch quantity >= transfer quantity
    d. Deduct from source
    e. Add to destination
    f. Log both movements (transfer_out / transfer_in)
5.  UPDATE stock_transfers SET status='completed'
6.  $db->transCommit()
```

---

## 3. API & SECURITY DESIGN

### RESTful API Structure

```
/api/v1/
├── auth/
│   ├── POST   login              → JWT token pair (access + refresh)
│   ├── POST   logout             → blacklist token
│   └── POST   refresh            → new access token
│
├── users/
│   ├── GET    /                  → list (admin only)
│   ├── POST   /                  → create
│   ├── GET    /{id}
│   ├── PUT    /{id}
│   └── DELETE /{id}
│
├── branches/
│   ├── GET    /
│   ├── POST   /                  → admin only
│   ├── GET    /{id}
│   ├── PUT    /{id}
│   └── DELETE /{id}              → blocked if inventory exists (FK)
│
├── products/
│   ├── GET    /                  → filterable by sku, status
│   ├── POST   /
│   ├── GET    /{id}
│   ├── PUT    /{id}
│   └── DELETE /{id}             → soft delete
│
├── inventory/
│   ├── GET    /                 → ?branch_id=X
│   ├── POST   /add              → add stock
│   ├── POST   /adjust           → adjust stock
│   └── GET    /branch/{id}     → full branch stock report
│
├── transfers/
│   ├── GET    /
│   ├── POST   /                 → create transfer request
│   ├── GET    /{id}
│   ├── POST   /{id}/approve
│   ├── POST   /{id}/reject
│   └── POST   /{id}/complete    → triggers actual stock movement
│
└── orders/
    ├── GET    /                 → filterable by branch, status, date
    ├── POST   /                 → create + deduct stock atomically
    ├── GET    /{id}
    └── POST   /{id}/cancel
```

### RBAC via CI4 Filters

| Route Pattern | Required Role |
|---|---|
| `/api/v1/users/*` | admin |
| `/api/v1/branches POST/PUT/DELETE` | admin |
| `/api/v1/products POST/PUT/DELETE` | admin |
| `/api/v1/inventory/add` | admin, branch_manager |
| `/api/v1/transfers/*/approve` | admin, branch_manager |
| `/api/v1/orders POST` | all authenticated |

---

## 4. CODEIGNITER 4 FOLDER STRUCTURE

```
app/
├── Config/
│   ├── App.php
│   ├── AuthJWT.php          ← JWT secret, TTL, refresh TTL
│   └── Routes.php
│
├── Controllers/
│   └── Api/
│       └── V1/
│           ├── AuthController.php
│           ├── BranchController.php
│           ├── ProductController.php
│           ├── InventoryController.php
│           ├── TransferController.php
│           ├── OrderController.php
│           └── UserController.php
│
├── Entities/
│   ├── UserEntity.php
│   ├── BranchEntity.php
│   ├── ProductEntity.php
│   ├── InventoryEntity.php
│   ├── OrderEntity.php
│   └── StockTransferEntity.php
│
├── Filters/
│   ├── AuthJWTFilter.php        ← validates Bearer token
│   ├── RoleFilter.php           ← checks user role
│   └── CorsFilter.php           ← allows Vue.js origin
│
├── Models/
│   ├── UserModel.php
│   ├── BranchModel.php
│   ├── ProductModel.php
│   ├── InventoryModel.php
│   ├── InventoryLogModel.php
│   ├── OrderModel.php
│   ├── OrderItemModel.php
│   ├── StockTransferModel.php
│   └── StockTransferItemModel.php
│
├── Services/
│   ├── AuthService.php
│   ├── OrderService.php         ← core business logic + transactions
│   ├── InventoryService.php
│   ├── StockTransferService.php
│   └── ReportService.php
│
├── Validation/
│   ├── AuthRules.php
│   ├── OrderRules.php
│   ├── ProductRules.php
│   └── TransferRules.php
│
├── Database/
│   ├── Migrations/
│   │   ├── 2025-01-01-000001_CreateRolesTable.php
│   │   ├── 2025-01-01-000002_CreateBranchesTable.php
│   │   ├── 2025-01-01-000003_CreateUsersTable.php
│   │   ├── 2025-01-01-000004_CreateProductsTable.php
│   │   ├── 2025-01-01-000005_CreateInventoryTable.php
│   │   ├── 2025-01-01-000006_CreateInventoryLogsTable.php
│   │   ├── 2025-01-01-000007_CreateStockTransfersTable.php
│   │   └── 2025-01-01-000008_CreateOrdersTable.php
│   └── Seeds/
│       ├── RoleSeeder.php
│       └── UserSeeder.php
│
└── Helpers/
    ├── jwt_helper.php
    └── response_helper.php

frontend/ (Vue 3 + Vite)
├── src/
│   ├── api/
│   │   ├── axios.js             ← base instance, interceptors
│   │   ├── auth.api.js
│   │   ├── inventory.api.js
│   │   ├── orders.api.js
│   │   └── transfers.api.js
│   ├── composables/
│   │   ├── useAuth.js
│   │   ├── useInventory.js
│   │   └── useOrders.js
│   ├── router/
│   │   └── index.js             ← route guards check role
│   ├── store/
│   │   ├── auth.store.js        ← Pinia
│   │   ├── inventory.store.js
│   │   └── orders.store.js
│   ├── views/
│   │   ├── Auth/
│   │   │   └── LoginView.vue
│   │   ├── Dashboard/
│   │   │   └── DashboardView.vue
│   │   ├── Branch/
│   │   │   ├── BranchListView.vue
│   │   │   └── BranchDetailView.vue
│   │   ├── Product/
│   │   │   └── ProductListView.vue
│   │   ├── Inventory/
│   │   │   ├── InventoryView.vue
│   │   │   └── TransferView.vue
│   │   └── Order/
│   │       ├── OrderListView.vue
│   │       └── CreateOrderView.vue
│   └── components/
│       ├── layout/
│       │   ├── AppSidebar.vue
│       │   └── AppHeader.vue
│       ├── inventory/
│       │   ├── StockBadge.vue
│       │   └── TransferForm.vue
│       └── shared/
│           ├── DataTable.vue
│           ├── ConfirmDialog.vue
│           └── LoadingSpinner.vue
```

---

## 5. FRONTEND INTEGRATION: INVENTORY TRANSFER FLOW

```
UI Flow: Transfer Stock Between Branches
─────────────────────────────────────────
Step 1: User selects FROM branch
  → Vue fetches GET /api/v1/inventory?branch_id=X
  → Populates product dropdown with items that have qty > 0

Step 2: User enters quantity
  Client-side validation (before API hit):
    ✓ quantity > 0
    ✓ quantity <= available stock (from Step 1 local state)
    ✓ to_branch !== from_branch
    ✓ product selected

Step 3: User submits form
  → POST /api/v1/transfers { from_branch_id, to_branch_id, items: [{product_id, quantity}] }
  → Show pending status

Step 4: Manager/Admin approves
  → POST /api/v1/transfers/:id/approve

Step 5: Complete (triggers DB transaction)
  → POST /api/v1/transfers/:id/complete
  → Pessimistic locks acquired in consistent order
  → Ledger entries written
  → Response shows updated quantities
```

---

## 6. SCALING STRATEGY FOR 100+ BRANCHES

| Concern | Strategy |
|---|---|
| Query performance on inventory | Composite index `(branch_id, product_id)` — O(log n) lookup |
| inventory_logs growth | Partition by RANGE on `YEAR(created_at)` |
| Reporting across branches | Separate read replica (MySQL replication) + reporting cache |
| Concurrent orders | Row-level `FOR UPDATE` locks, not table locks |
| API response time | Redis cache on product catalog (rarely changes) |
| Hot branches | Connection pooling (PgBouncer-style via ProxySQL) |
| Horizontal scale | Stateless CI4 API → multiple app servers behind load balancer |
| Auth token validation | JWT (stateless) — no session DB hit per request |
| Reporting heavy queries | Materialized/scheduled summaries, not real-time AGGs |

---

## 7. SAFE STOCK DEDUCTION ALGORITHM (Pseudo-code)

```
function safeStockDeduct(branchId, productId, qty, actorId, refType, refId):

    // Phase 1: Acquire pessimistic lock
    row = DB.query(
        "SELECT id, quantity FROM inventory
         WHERE branch_id = ? AND product_id = ?
         FOR UPDATE",
        [branchId, productId]
    )

    if row is null:
        throw NotFoundException("Product not stocked at this branch")

    // Phase 2: Guard clause
    if row.quantity < qty:
        throw InsufficientStockException(
            "Need {qty}, available {row.quantity}"
        )

    qtyBefore = row.quantity
    qtyAfter  = qtyBefore - qty

    // Phase 3: Atomic deduction
    DB.execute(
        "UPDATE inventory SET quantity = ? WHERE id = ?",
        [qtyAfter, row.id]
    )

    // Phase 4: Immutable ledger entry
    DB.execute(
        "INSERT INTO inventory_logs
         (branch_id, product_id, user_id, movement_type,
          reference_type, reference_id, qty_before, qty_change, qty_after)
         VALUES (?, ?, ?, 'sale', ?, ?, ?, ?, ?)",
        [branchId, productId, actorId, refType, refId,
         qtyBefore, -qty, qtyAfter]
    )

    return qtyAfter
```

