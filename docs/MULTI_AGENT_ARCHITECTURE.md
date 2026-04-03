# Multi-Agent System Architecture - ERP Chatbot

## 🎯 Overview

This is a **God-Level Multi-Agent AI Architecture** for the CodeIgniter ERP system. Instead of one monolithic agent handling all queries, the system now operates as a **Specialized Intelligence Network** where each agent is an expert in its domain.

---

## 🏗️ Architecture

```
ChatbotController (Main Router)
    ↓
[Module Identification]
    ↓
Route to Specialized Agent:
├── InventoryAgent        → Stock Strategist
├── OrdersAgent          → Transaction Analyst
├── ProductsAgent        → Catalog Curator
├── BranchesAgent        → Operations Director
├── TransfersAgent       → Logistics Specialist
├── UsersAgent           → HR Manager
└── StatsAgent           → Chief Analytics Officer
```

---

## 🤖 The Seven Agents

### 1. **InventoryAgent** - Stock Strategist
**Specialty:** Inventory management, stock levels, reorder alerts, SKU performance

**Handles queries like:**
- "Show me current stock levels"
- "What products are low on stock?"
- "Where is SKU-123?"
- "Add 50 units of product ABC"

**System Prompt Focus:**
- Stock health assessment
- Risk identification (low stock, dead stock)
- Reorder point optimization
- Fast-moving vs slow-moving analysis

---

### 2. **OrdersAgent** - Transaction Analyst
**Specialty:** Order processing, revenue tracking, sales analytics, status lifecycle

**Handles queries like:**
- "Show me all orders"
- "What's my total sales today?"
- "Show pending orders"
- "Create new order for customer X"

**System Prompt Focus:**
- Order lifecycle tracking
- Revenue analytics and trends
- Bottleneck detection
- Financial responsibility

---

### 3. **ProductsAgent** - Catalog Curator
**Specialty:** Product information, pricing strategy, catalog management, SKU details

**Handles queries like:**
- "Find product with SKU-123"
- "Show products in category X"
- "What's the margin on product Y?"
- "Add new product to catalog"

**System Prompt Focus:**
- Product discovery
- Pricing strategy and margin analysis
- Catalog integrity
- Category management

---

### 4. **BranchesAgent** - Operations Director
**Specialty:** Branch locations, manager assignments, operational capacity, site status

**Handles queries like:**
- "Show all branch locations"
- "Who manages branch X?"
- "Which branches are inactive?"
- "Create new branch"

**System Prompt Focus:**
- Site management and performance
- Leadership assignments
- Operational health monitoring
- Expansion strategy support

---

### 5. **TransfersAgent** - Logistics Specialist
**Specialty:** Inter-branch transfers, shipment tracking, delivery logistics, supply chain

**Handles queries like:**
- "Track transfer #123"
- "Show pending transfers"
- "What shipments are delayed?"
- "Create stock transfer from branch A to B"

**System Prompt Focus:**
- Shipment tracking
- Delay detection and alerts
- Route optimization
- Delivery accountability

---

### 6. **UsersAgent** - Human Resources Manager
**Specialty:** Staff directory, role management, access control, team structure

**Handles queries like:**
- "Show all users" (Admin only)
- "Who has manager role?"
- "Show inactive staff"
- "Create new user account"

**System Prompt Focus:**
- Directory management
- Access control governance
- Team structure and hierarchy
- Personnel status tracking

---

### 7. **StatsAgent** - Chief Analytics Officer
**Specialty:** System-wide KPIs, executive dashboards, cross-domain analytics, strategic insights

**Handles queries like:**
- "Give me system overview"
- "What's our business status?"
- "Show me KPIs"
- Help/navigation queries

**System Prompt Focus:**
- Executive dashboards
- Cross-domain analytics
- Trend analysis
- Strategic guidance

---

## 🚀 How It Works

### Step 1: Query Entry
User sends a message to `/api/v1/chatbot/query`

```
GET /api/v1/chatbot/query?query=Show%20current%20inventory
```

### Step 2: Module Identification
The **ChatbotController** analyzes the query keywords:

```php
if (preg_match('/inventory|stock|available|level|reorder/', $query)) 
    return 'inventory';  // Route to InventoryAgent

if (preg_match('/order|sale|purchase|transact/', $query)) 
    return 'orders';     // Route to OrdersAgent
```

### Step 3: Specialized Agent Processing
1. **Fetch Domain Data** - Agent retrieves only relevant data
2. **Load Specialized Prompt** - Agent's expert system prompt
3. **Call Gemini API** - Send query + data + prompt to Gemini
4. **Return Response** - Professional, formatted answer

### Step 4: Response
```json
{
  "success": true,
  "data": {
    "response": "[Agent's formatted response]",
    "agent": "Inventory Specialist",
    "module": "inventory"
  }
}
```

---

## �️ Sales User Access Restrictions (April 3, 2026)

As of this update, Sales users (Role 3) have been restricted from accessing critical business operations:

**Denied Access Modules:**
- **Inventory Agent**: Cannot view, modify, or manage any inventory data
- **Orders Agent**: Cannot create, view, or manage orders
- **Branches Agent**: Cannot view branch locations or details
- **Transfers Agent**: Cannot track or manage stock transfers

**Limited Access Modules:**
- **Users Agent**: Can ONLY view their own profile, not the team directory
- **Products Agent**: Access to Public Catalog only (read-only)
- **Stats Agent**: Access to Personal statistics only

**Technical Implementation:**
Each agent checks `if ($roleId === 3)` at the start of `fetchContext()` and returns an ACCESS DENIED message before retrieving any data.

---

## �💡 God-Level Prompt Features

### Core Directives (All Agents)
1. **Precision** - Use ONLY provided data, no hallucinations
2. **Professionalism** - Executive tone, no emojis
3. **Formatting** - Always use Markdown tables
4. **Actionable** - Identify trends and issues
5. **Interactive** - Ask for missing info when needed

### Role-Based Responses
- **Admin (Role 1)** - Full visibility, all metrics, strategic insights
- **Manager (Role 2)** - Branch-level focus, operational insights
- **Sales (Role 3)** - Simple, actionable information

### Interactive Features
When user requests CREATE/UPDATE operations:
- Agent identifies as a "Registry Assistant"
- Requests specific fields clearly
- Example: "I can help add inventory. Please provide: Product SKU, Quantity, Branch Location"

---

## 📊 Agent Prompting Architecture

Each agent has a specialized prompt with:

```
1. Role Definition
   "You are the [SPECIALIST NAME] - an expert in [DOMAIN]"

2. Expertise Areas
   - Focus points specific to domain
   - Goals and responsibilities
   - Interaction models

3. Response Standards
   - Exact format (Title, Overview, Table, Insights)
   - Markdown table structure
   - Role-aware variations

4. Critical Rules
   - What NOT to do (❌)
   - What TO do (✅)
   - Data constraints
   - Governance rules

5. Tone & Voice Guide
   - Executive vs. casual
   - Detail level
   - Professionalism standards

6. Domain-Specific Constraints
   - Inventory: Stock health, reorder levels
   - Orders: Revenue accuracy, financial responsibility
   - Products: Pricing integrity, catalog consistency
   - Branches: Organizational structure, hierarchy
   - Transfers: Logistics accuracy, delivery tracking
   - Users: Access control, privacy, governance
   - Stats: Executive-level insights only
```

---

## 🔐 Role-Based Access Control

Each agent respects strict role boundaries:

| Feature | Admin | Manager | Sales |
|---------|-------|---------|-------|
| Inventory | All | Assigned Branches | ❌ DENIED |
| Orders | All | Branch Orders | ❌ DENIED |
| Products | Full Catalog | Full Catalog | Public Catalog |
| Branches | All Locations | Assigned Only | ❌ DENIED |
| Transfers | All | Assigned Branches | ❌ DENIED |
| Users | Full Directory | Staff in Branch | Own Profile Only |
| Stats | Full System | Branch Level | Personal |

**Sales User Restrictions (Role 3):**
- ❌ Cannot access Inventory module
- ❌ Cannot access Orders module
- ❌ Cannot access Branches module
- ❌ Cannot access Transfers module
- ✅ Can only view their own user profile
- ✅ Can access product catalog (public information only)
- ✅ Can access personal statistics

---

## 📁 File Structure

```
app/
├── Controllers/
│   └── Api/V1/
│       └── ChatbotController.php        (Main Router)
│
└── Agents/
    ├── BaseAgent.php                    (Abstract Base Class)
    ├── InventoryAgent.php
    ├── OrdersAgent.php
    ├── ProductsAgent.php
    ├── BranchesAgent.php
    ├── TransfersAgent.php
    ├── UsersAgent.php
    └── StatsAgent.php
```

---

## 🧪 Testing the System

### Test Inventory Agent
```
GET /api/v1/chatbot/query?query=Show%20inventory%20levels
```

### Test Orders Agent
```
GET /api/v1/chatbot/query?query=What%20are%20my%20total%20sales
```

### Test Products Agent
```
GET /api/v1/chatbot/query?query=Find%20SKU-001
```

### Test Branches Agent
```
GET /api/v1/chatbot/query?query=Show%20all%20branches
```

### Test Transfers Agent
```
GET /api/v1/chatbot/query?query=Track%20transfer%20status
```

### Test Users Agent
```
GET /api/v1/chatbot/query?query=Show%20admin%20users
```

### Test Stats Agent
```
GET /api/v1/chatbot/query?query=System%20overview
```

---

## ⚙️ Configuration

### Environment Variables Required
```
GEMINI_API_KEY=your-gemini-api-key-here
```

### Optional: Fallback Behavior
If Gemini API is unavailable, each agent displays raw context data with a system notice.

---

## 🎨 Response Example

### Query: "What's my current inventory?"

#### Response from InventoryAgent:
```
# INVENTORY STATUS REPORT

Current inventory levels across your assigned branches show 127 SKUs with 2,450 total units.

## Stock Levels

| Product Name | SKU | Current Qty | Reorder Level | Status | Location |
| --- | --- | --- | --- | --- | --- |
| Premium Laptop | SKU-001 | 45 | 10 | 🟢 HEALTHY | Karachi Branch |
| Monitor 27" | SKU-002 | 8 | 15 | 🟠 LOW | Lahore Branch |
| USB Cable | SKU-003 | 0 | 20 | 🔴 CRITICAL | Islamabad Branch |

## Risk Summary
- **Critical Stock**: 3 items (USB Cable, Power Supply, Keyboard)
- **Low Stock**: 12 items

## Recommendations
- Immediately reorder USB Cable and Power Supply
- Expedite inbound transfer to Islamabad branch
- Consider increasing reorder level for fast-moving items
```

---

## 🔄 Adding New Agents

To add a new specialized agent:

1. **Create Agent Class**
```php
namespace App\Agents;

class MyNewAgent extends BaseAgent {
    public function process(string $query, int $roleId, int $userId): string { }
    protected function fetchContext(int $roleId, int $userId): string { }
    protected function getSystemPrompt(int $roleId): string { }
}
```

2. **Update Router**
```php
// In ChatbotController.php
private function identifyModule(string $query): string {
    if (preg_match('/my-keywords/', $query)) return 'mynewmodule';
}

private function routeToSubAgent(...) {
    case 'mynewmodule':
        return $this->callMyNewAgent($query, $roleId, $userId);
}

private function callMyNewAgent(...) {
    $agent = new \App\Agents\MyNewAgent($this->geminiKey);
    return $agent->process($query, $roleId, $userId);
}
```

---

## 🎯 Best Practices

1. **Prompt Engineering**
   - Keep prompts under 2000 tokens
   - Be specific about output format
   - Include role-aware instructions

2. **Data Fetching**
   - Limit to 20-30 records per agent call
   - Pre-aggregate metrics
   - Respect role-based scoping

3. **Error Handling**
   - Graceful fallback to raw context
   - Clear error messages
   - Never expose API keys

4. **Performance**
   - Cache agent prompts if possible
   - Implement rate limiting
   - Monitor API usage

---

## 📞 Support & Troubleshooting

### Agent not responding?
- Check GEMINI_API_KEY is set
- Verify role permissions
- Check server logs

### Wrong agent being called?
- Review keyword patterns in `identifyModule()`
- Ensure keywords are specific enough
- Test with exact query strings

### Response format is wrong?
- Review the agent's `getSystemPrompt()` method
- Check data structure in `fetchContext()`
- Verify Gemini response parsing

---

## 🚀 Future Enhancements

- [ ] Agent versioning system
- [ ] Custom agent templates
- [ ] Multi-turn conversation support
- [ ] Agent-to-agent collaboration
- [ ] Advanced analytics dashboard
- [ ] Caching layer for common queries
- [ ] A/B testing for prompt variations

---

**Created:** April 3, 2026  
**Version:** 1.0 - Initial Multi-Agent Architecture  
**Status:** Production Ready
