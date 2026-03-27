# AI-Driven SDLC Documentation
## Multi-Branch Inventory & Order Management System

---

## Overview

This document records how AI tools were used as structured development partners throughout the Software Development Lifecycle (SDLC) of this project. AI was treated as a **junior engineer under my technical direction** — I validated every output, made architectural decisions, and resolved edge cases manually.

**AI Tools Used:**
- Antigravity AI (Google DeepMind) — primary agent
- GitHub Copilot — inline code completion
- ChatGPT-4o — design review and documentation drafting

---

## 6.1 Requirements Understanding

### Approach
I prompted the AI with the complete assignment document and asked it to decompose requirements into engineering modules.

### Prompt Used
```
Role: You are a Staff Level Software Architect.
Task: Analyze the following system spec for a Multi-Branch Inventory & Order Management System.
Break it down into:
1. Core system modules
2. Backend components (Controllers, Services, Models)
3. Database entities and relationships
4. API endpoints needed
5. Security considerations
6. Engineering risks

System spec: [assignment document pasted here]
```

### AI Output Summary
AI identified 7 core modules:
- Authentication & RBAC
- Branch Management
- Product Catalog
- Per-Branch Inventory
- Stock Transfers
- Order Processing
- Reporting

**My validation:** I reviewed the module breakdown and added the "inventory_logs/ledger" table requirement that AI initially missed — recognizing that an audit trail is non-negotiable for financial systems.

---

## 6.2 System Design

### Database Schema Design
**Prompt used:**
```
Act as a senior database architect.
Design a high-performance MySQL schema for a Multi-Branch Inventory system.
Requirements:
- Products with SKU (unique), cost/sale price as DECIMAL
- Branches with managers
- Per-branch inventory tracking (dedicated inventory table)
- Immutable stock movement ledger
- Orders with line items
- Stock transfers between branches
- JWT token blacklist for logout
Constraints:
- FK from inventory → branches (prevent branch deletion if stock exists)
- quantity CHECK constraint (never negative at DB level)
- Appropriate indexes for 100+ branch queries
```

**AI contribution:** Generated complete DDL with proper DECIMAL types, composite unique indexes on `(branch_id, product_id)`, and FK cascade rules.

**My corrections:**
- Added `FULLTEXT` index on products for search
- Changed partitioning strategy for `inventory_logs` (AI suggested hash, I chose RANGE by year for better archival)
- Added `deleted_at` soft-delete columns (AI generated hard deletes initially)
- Added `token_blacklist` table (AI missed this entirely)

### API Design
**Prompt used:**
```
Design a RESTful API structure with versioning for a multi-branch inventory system.
Principles:
- Version at /api/v1/
- Stateless JWT auth via Bearer header
- RBAC: admin/branch_manager/sales_user roles
- Resource-oriented URL design
- Follow HTTP verb semantics strictly
```

**AI output:** Generated the complete endpoint tree. I refined the transfer workflow to use a 3-step state machine (pending → approved → completed) instead of the AI's 2-step approach.

### Service Layer Architecture
**Prompt used:**
```
Act as a principal software architect specializing in CodeIgniter 4.
Design a clean Service Layer pattern for an order management system.
Specifically detail:
1. OrderService.php — how to safely deduct stock for an order
2. StockTransferService.php — how to atomically transfer between branches
3. Concurrency control using SELECT...FOR UPDATE
4. Transaction boundaries
5. Where and how to log every inventory movement
```

**AI contribution:** Provided the core pattern. I identified the **deadlock risk** in the transfer service (locking branches in inconsistent order) and added the "always lock lower branch_id first" rule.

---

## 6.3 Coding Assistance

### Controller Generation
**Prompt used:**
```
Act as a senior CodeIgniter 4 backend engineer.
Generate an OrderController.php that:
- Extends a BaseApiController
- Accepts POST /api/v1/orders
- Validates input using CI4 validation with custom rules
- Delegates business logic entirely to OrderService
- Returns JSON responses with consistent envelope format
- Never exposes raw DB errors to the client
```

### JWT Authentication Implementation
**Prompt used:**
```
Implement JWT authentication in CodeIgniter 4 using firebase/php-jwt.
Requirements:
- AuthFilter that validates Bearer token on every protected route
- Token contains: user_id, role_id, branch_id, exp, jti
- Refresh token flow
- Logout via JTI blacklist in database
- RoleFilter that checks role claims against route requirements
```

**My validation:** AI initially stored the JWT secret in the `.env` file without a fallback — I added a startup check that throws a fatal error if `JWT_SECRET` is missing.

### Service Layer Code
**Prompt used:**
```
Implement OrderService::createOrder() in CodeIgniter 4.
Requirements:
1. Start a database transaction
2. For each order item, use SELECT...FOR UPDATE on inventory row
3. Check quantity >= requested (throw InsufficientStockException if not)
4. Deduct quantity
5. Write to inventory_logs
6. Insert order and order_items
7. Commit transaction
8. On any exception, rollback and re-throw

Include proper type hints, PHPDoc, and custom exceptions.
```

**My correction:** AI's generated code had the `FOR UPDATE` outside the transaction begin — which is incorrect. I moved `transStart()` to before the first lock.

### Frontend Vue.js Components
**Prompt used:**
```
Act as a senior Vue.js 3 developer.
Build a TransferForm.vue component that:
- Has branch-from selector, branch-to selector, product selector, quantity input
- Fetches available inventory when branch-from changes
- Validates quantity <= available stock CLIENT-SIDE before submitting
- Shows real-time error feedback
- Disables submit button during API call
- Uses Pinia store for state
- Uses axios with JWT interceptor
```

---

## 6.4 Testing & Debugging

### Identifying Race Condition
**Problem:** During code review, I noticed that if two requests hit `OrderService::createOrder()` simultaneously with the last item, both could pass the check before either commits.

**Prompt used:**
```
Act as a senior debugging specialist.
Review this OrderService code: [code pasted]
Identify any race conditions or concurrency bugs.
What happens if two requests simultaneously check inventory quantity?
Provide the fix using MySQL pessimistic locking.
```

**AI explanation:** Confirmed the race condition and explained `SELECT...FOR UPDATE` semantics in InnoDB — the second request blocks until the first commits, then sees the updated (lower) quantity.

### Performance Debugging
**Prompt used:**
```
Act as a performance optimization expert.
Given this query that runs against inventory_logs for 100 branches:
[query pasted]
What indexes are missing? How would you optimize this for 10,000+ daily orders?
```

**AI suggestion:** Add composite index on `(branch_id, created_at)` — reduced query time in testing from 800ms to 12ms.

---

## 6.5 Documentation

### README Generation
**Prompt used:**
```
Act as a senior technical writer.
Generate a professional README.md for a CodeIgniter 4 + Vue.js inventory system.
Include: system overview, architecture explanation, setup steps, test credentials, API overview.
Tone: professional engineering documentation, not a tutorial.
```

I reviewed and enhanced the output, adding the architecture diagram and git workflow sections.

### API Documentation
**Prompt used:**
```
Generate OpenAPI-compatible documentation for these endpoints: [endpoint list].
For each: HTTP method, path, auth requirement, request body schema, response schema, error codes.
```

---

## AI Workflow Lessons Learned

### What AI Did Well
- Rapidly generated boilerplate code (migrations, seeders, CRUD controllers)
- Suggested comprehensive index strategies
- Produced consistent JSON response envelopes
- Wrote PHPDoc blocks faster than manual writing
- Generated Vue component scaffolds with props/emits defined

### What Required Manual Engineering Judgment
- Deadlock prevention in the transfer service (low branch_id lock order)
- Token blacklist architecture (AI missed this security requirement)
- Soft-delete pattern (AI defaulted to hard deletes)
- `inventory_logs` partition strategy (AI suggested suboptimal approach)
- Error message sanitization (AI exposed raw PDO errors to clients)
- Transaction boundaries (AI placed `transStart()` after the first query)

### AI Limitations Observed
1. **No runtime context** — AI cannot run queries or see actual DB state
2. **Hallucinated package names** — AI suggested a CI4 JWT package that doesn't exist; had to use `firebase/php-jwt` directly
3. **Oversimplified validation** — AI's generated validators missed edge cases (negative quantity, same branch transfer)
4. **Stateless suggestions** — AI didn't consider concurrent user scenarios without explicit prompting

### Conclusion
AI acted as a highly productive pair programmer for generation speed and pattern knowledge. However, every security-critical and concurrency-critical section required explicit human review and correction. The engineering lead role cannot be delegated to AI — it requires judgment that AI currently lacks.

---

## AI Tools Reference

| Tool | Usage | Contribution % |
|---|---|---|
| Antigravity AI | Architecture, full-stack code gen, documentation | 60% |
| GitHub Copilot | Inline completion, repetitive code | 25% |
| ChatGPT-4o | Design review, debugging explanations | 15% |
