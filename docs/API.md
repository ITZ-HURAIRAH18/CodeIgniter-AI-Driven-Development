# API Documentation
## Multi-Branch Inventory & Order Management System — v1

**Base URL:** `http://localhost:8080/api/v1`

**Authentication:** `Authorization: Bearer <access_token>`

---

## Auth Endpoints

### POST /auth/login
```json
// Request
{ "email": "admin@system.com", "password": "Admin@12345" }

// Response 200
{
  "success": true,
  "data": {
    "access_token": "eyJ...",
    "refresh_token": "eyJ...",
    "expires_in": 3600,
    "user": { "id": 1, "name": "...", "role": 1, "branch_id": null }
  }
}
```

### POST /auth/logout *(requires auth)*
Blacklists the current token's JTI. No body required.

### POST /auth/refresh
```json
// Request
{ "refresh_token": "eyJ..." }
// Response: { "access_token": "eyJ...", "expires_in": 3600 }
```

---

## Branches *(Admin: full CRUD | Others: read only)*

| Method | Path | Description |
|---|---|---|
| GET | /branches | List all branches with manager info |
| GET | /branches/{id} | Single branch |
| POST | /branches | Create branch |
| PUT | /branches/{id} | Update branch |
| DELETE | /branches/{id} | Soft delete (fails if has inventory) |

---

## Products

| Method | Path | Roles |
|---|---|---|
| GET | /products | All |
| GET | /products/{id} | All |
| POST | /products | Admin |
| PUT | /products/{id} | Admin |
| DELETE | /products/{id} | Admin |

**Create/Update body:**
```json
{
  "sku": "PROD-001",
  "name": "Widget A",
  "cost_price": "10.0000",
  "sale_price": "25.0000",
  "tax_percentage": "17.00",
  "unit": "pcs",
  "status": "active"
}
```

---

## Inventory

| Method | Path | Roles |
|---|---|---|
| GET | /inventory?branch_id=X | All |
| GET | /inventory/branch/{id} | All |
| POST | /inventory/add | Admin, Manager |
| POST | /inventory/adjust | Admin, Manager |
| GET | /inventory/logs?branch_id=X&product_id=Y | Admin, Manager |

**Add Stock:**
```json
{ "branch_id": 1, "product_id": 1, "quantity": 50, "notes": "Initial stock" }
```

**Adjust Stock (sets absolute value):**
```json
{ "branch_id": 1, "product_id": 1, "quantity": 35, "notes": "Stocktake correction" }
```

---

## Orders

| Method | Path | Roles |
|---|---|---|
| GET | /orders | All (branch-scoped for non-admin) |
| GET | /orders/{id} | All |
| POST | /orders | All |
| POST | /orders/{id}/cancel | All/Admin for completed |

**Create Order:**
```json
{
  "branch_id": 1,
  "notes": "Walk-in customer",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 1 }
  ]
}
```

**Error responses:**
- `422` — Insufficient stock: `"Insufficient stock for product #1. Requested: 5, Available: 2"`
- `400` — Validation error
- `500` — Transaction failure (retryable)

---

## Stock Transfers

| Method | Path | Roles |
|---|---|---|
| GET | /transfers | All (branch-scoped) |
| GET | /transfers/{id} | All |
| POST | /transfers | All |
| POST | /transfers/{id}/approve | Admin, Manager |
| POST | /transfers/{id}/reject | Admin, Manager |
| POST | /transfers/{id}/complete | Admin, Manager |

**Create Transfer:**
```json
{
  "from_branch_id": 1,
  "to_branch_id": 2,
  "notes": "Monthly restock",
  "items": [
    { "product_id": 1, "quantity": 20 },
    { "product_id": 2, "quantity": 10 }
  ]
}
```

**Transfer State Machine:**
```
pending → [approve] → approved → [complete] → completed
pending → [reject]  → rejected
```

---

## Error Response Envelope

All errors follow:
```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": { "field": "validation message" }  // only on 422
}
```

## HTTP Status Codes Used

| Code | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict (FK violation) |
| 422 | Unprocessable (stock/validation) |
| 500 | Server Error |
