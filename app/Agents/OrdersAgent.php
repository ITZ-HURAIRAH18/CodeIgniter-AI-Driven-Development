<?php

namespace App\Agents;

use App\Models\OrderModel;
use App\Models\BranchModel;

/**
 * OrdersAgent
 * SPECIALIST: Transaction Analyst & Revenue Officer
 * Expertise: Order processing, revenue tracking, status lifecycle, sales analytics
 */
class OrdersAgent extends BaseAgent
{
    public function process(string $query, int $roleId, int $userId): string
    {
        // Check if this is a CREATE/ADD operation
        if ($this->isCreateOperation($query)) {
            return $this->handleOrderCreation($query);
        }

        try {
            $context = $this->fetchContext($roleId, $userId);
            $systemPrompt = $this->getSystemPrompt($roleId);
            return $this->callGemini($query, $systemPrompt, $context);
        } catch (\Exception $e) {
            return $this->fallbackResponse($query, $this->fetchContext($roleId, $userId));
        }
    }

    /**
     * Handle order creation with professional feedback
     */
    private function handleOrderCreation(string $query): string
    {
        return "## ORDER CREATION REQUEST\n\n**Status:** Processing new order\n\n" .
               "To create a new order, please provide the following:\n\n" .
               "| Field | Description | Required |\n" .
               "| --- | --- | --- |\n" .
               "| Customer Name | Who is this order for? | ✓ Required |\n" .
               "| Product(s) | Product name/SKU and quantities | ✓ Required |\n" .
               "| Branch/Location | Which branch processes this? | ✓ Required |\n" .
               "| Delivery Date | When needed? | Optional |\n\n" .
               "**Example:** Create order for John with 5x Orange and 2x Apple for East branch";
    }

    protected function fetchContext(int $roleId, int $userId): string
    {
        if ($roleId === 3) {
            return "## ACCESS DENIED\nOrder management is restricted to Administrators and Branch Managers.\n";
        }

        $branchIds = ($roleId === 2) ? model(BranchModel::class)->getManagerBranchIds($userId) : [];
        $orderModel = model(OrderModel::class);

        $orders = ($roleId === 1) 
            ? $orderModel->getWithDetails() 
            : ($roleId === 2 
                ? $orderModel->getWithDetailsMultiBranch($branchIds) 
                : $orderModel->getWithDetails(null, $userId));

        $context = "## ORDER MANAGEMENT SYSTEM DATA\n\n";
        $context .= "### ACTIVE & RECENT ORDERS\n";

        if (empty($orders)) {
            $context .= "_No orders found for current role scope._\n\n";
            return $context;
        }

        // Calculate revenue metrics
        $totalOrders = count($orders);
        $totalRevenue = array_sum(array_column($orders, 'grand_total'));
        $statusCounts = array_count_values(array_column($orders, 'status'));
        $pendingCount = $statusCounts['pending'] ?? 0;

        foreach(array_slice($orders, 0, 20) as $order) {
            $context .= "- **Order #{$order['order_number']}**\n";
            $context .= "  - Customer: {$order['customer_name'] ?? 'N/A'}\n";
            $context .= "  - Amount: {$order['grand_total']} {$order['currency'] ?? 'PKR'}\n";
            $context .= "  - Status: {$order['status']}\n";
            $context .= "  - Branch: {$order['branch_name']}\n";
            $context .= "  - Date: {$order['created_at']}\n";
            $context .= "  - Items: " . ($order['item_count'] ?? 0) . "\n\n";
        }

        $context .= "### REVENUE METRICS\n";
        $context .= "- **Total Orders**: $totalOrders\n";
        $context .= "- **Total Revenue**: $totalRevenue\n";
        $context .= "- **Pending Orders**: $pendingCount\n";
        $context .= "- **Status Breakdown**: " . json_encode($statusCounts) . "\n";

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the TRANSACTION ANALYST - a master of order management and revenue optimization.
Your mission: Track order flow, identify bottlenecks, provide revenue insights, and ensure smooth transaction processing.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Order Lifecycle Tracking**: Monitor status transitions (pending → confirmed → shipped → delivered)
2. **Revenue Analytics**: Summarize sales, identify trends, recognize high-value orders
3. **Bottleneck Detection**: Flag delayed/pending orders that need attention
4. **Customer Insights**: Aggregate order patterns by customer/branch

---

## INTERACTION MODEL
**Command Recognition**:
- View Orders: \"Show me all orders\" → Summarize with status breakdown
- Filter: \"Show pending orders\" or \"Orders from yesterday\" → Filter and present
- Revenue: \"What's my total sales?\" → Calculate and present metrics
- Order Details: \"Details for order #12345\" → Present complete order info
- Create Order: \"Create new order\" → Act as registry agent, request: Customer, Items, Branch

---

## RESPONSE STANDARDS (MANDATORY)

### For READ QUERIES:
1. **Title**: ORDER SUMMARY | REVENUE REPORT | PENDING ORDERS ALERT (pick contextually)
2. **Overview**: 1 sentence high-level status
3. **Table**: Markdown table with columns:
   | Order # | Customer | Amount | Status | Branch | Date |
   | --- | --- | --- | --- | --- | --- |
4. **Status Breakdown**: Show order counts by status
5. **Action Items**: Highlight orders needing immediate attention

### For CREATE/UPDATE QUERIES:
1. Acknowledge intent to create/update
2. List required fields clearly:
   - \"To create an order, I need:
     - Customer Name
     - Product(s) and Quantities
     - Branch Location\"
3. Ask for one set of fields at a time

---

## TONE & VOICE
- **Executive Level**: Business-focused, with financial acumen
- **Trend-Aware**: Mention patterns (\"3 high-value orders today\")
- **Action-Oriented**: Highlight what needs attention NOW
- **Professional**: No jargon unless necessary

---

## FINANCIAL RESPONSIBILITY
- Always round currency to 2 decimal places
- Include currency symbol/code (e.g., PKR)
- Flag unusual patterns (very high/low orders)
- Current User Role: **{$roleTitle}** (scope: " . ($roleId === 1 ? 'ALL BRANCHES' : ($roleId === 2 ? 'ASSIGNED BRANCHES' : 'OWN ORDERS')) . ")

---

## CRITICAL RULES
- ❌ Do NOT invent order numbers or amounts
- ❌ Do NOT modify order status without explicit request
- ❌ Do NOT expose sensitive customer data (full contact details)
- ✅ Do USE provided data exclusively
- ✅ Do PRIORITIZE pending/high-value orders
- ✅ Do REQUEST clarification for ambiguous queries
- ✅ Do OFFER alternative views (e.g., \"Would you like to see by status or date?\")";
    }
}
