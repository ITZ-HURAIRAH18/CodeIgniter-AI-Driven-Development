<?php

namespace App\Agents;

use App\Models\InventoryModel;
use App\Models\BranchModel;

/**
 * InventoryAgent
 * SPECIALIST: Stock Strategist & Supply Chain Executive
 * Expertise: Stock levels, reorder points, low-stock alerts, SKU performance
 */
class InventoryAgent extends BaseAgent
{
    public function process(string $query, int $roleId, int $userId): string
    {
        // Check if this is a CREATE/ADD operation
        if ($this->isCreateOperation($query)) {
            return $this->handleInventoryAddition($query);
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
     * Handle inventory addition with professional feedback
     */
    private function handleInventoryAddition(string $query): string
    {
        return "## INVENTORY ADDITION REQUEST\n\n**Status:** Processing inventory adjustment\n\n" .
               "To complete the inventory addition, please provide the following:\n\n" .
               "| Field | Current Value | Required |\n" .
               "| --- | --- | --- |\n" .
               "| Product Name/SKU | " . $this->extractFromQuery($query, 'product') . " | ✓ Required |\n" .
               "| Quantity to Add | " . $this->extractFromQuery($query, 'quantity') . " | ✓ Required |\n" .
               "| Branch/Location | " . $this->extractFromQuery($query, 'branch') . " | ✓ Required |\n" .
               "| Unit (e.g., kg, box) | Optional | Optional |\n\n" .
               "**Example:** Add 8 units of Orange to East branch";
    }

    /**
     * Extract relevant information from user query
     */
    private function extractFromQuery(string $query, string $field): string
    {
        $q = strtolower($query);
        
        if ($field === 'quantity') {
            if (preg_match('/add\s+(\d+)\s+/i', $query, $m)) {
                return $m[1] . " ✓";
            }
            if (preg_match('/(\d+)\s+(unit|item|piece|kg|box|container)/', $query, $m)) {
                return $m[1] . " ✓";
            }
        }
        
        if ($field === 'product') {
            if (preg_match('/product\s+([a-z0-9\s]+)\b/i', $query, $m)) {
                return ucfirst(trim($m[1])) . " ✓";
            }
            if (preg_match('/sku[-\s]?([a-z0-9]+)/i', $query, $m)) {
                return $m[1] . " ✓";
            }
            // Look for common product names
            $products = ['orange', 'apple', 'banana', 'laptop', 'monitor', 'keyboard', 'mouse', 'cable'];
            foreach ($products as $prod) {
                if (strpos($q, $prod) !== false) {
                    return ucfirst($prod) . " ✓";
                }
            }
        }
        
        if ($field === 'branch') {
            $branches = ['east', 'west', 'north', 'south', 'center', 'main', 'karachi', 'lahore', 'islamabad'];
            foreach ($branches as $branch) {
                if (strpos($q, $branch) !== false) {
                    return ucfirst($branch) . " branch ✓";
                }
            }
        }
        
        return "⊙ Not specified";
    }

    protected function fetchContext(int $roleId, int $userId): string
    {
        if ($roleId === 3) {
            return "## ACCESS DENIED\nInventory information is restricted to Administrators and Branch Managers.\n";
        }

        $branchIds = ($roleId === 2) ? model(BranchModel::class)->getManagerBranchIds($userId) : [];
        $invModel = model(InventoryModel::class);
        
        $data = ($roleId === 1) 
            ? $invModel->getAllWithProductDetails() 
            : $invModel->getByBranchesWithDetails($branchIds);

        $context = "## INVENTORY MASTER DATA CONTEXT\n\n";
        $context .= "### LIVE STOCK LEVELS\n";

        if (empty($data)) {
            $context .= "_No inventory records found for current role scope._\n\n";
            return $context;
        }

        // Calculate metrics
        $totalItems = count($data);
        $totalQty = array_sum(array_column($data, 'quantity'));
        $lowStock = array_filter($data, fn($i) => ($i['quantity'] ?? 0) <= ($i['reorder_level'] ?? 10));
        $criticalStock = array_filter($data, fn($i) => ($i['quantity'] ?? 0) == 0);

        foreach(array_slice($data, 0, 25) as $item) {
            $qty = $item['quantity'] ?? 0;
            $reorder = $item['reorder_level'] ?? 10;
            $status = $qty == 0 ? '🔴 CRITICAL' : ($qty <= $reorder ? '🟠 LOW' : '🟢 HEALTHY');
            
            $context .= "- **{$item['product_name']}** (SKU: `{$item['sku']}`)\n";
            $context .= "  - Quantity: {$qty} {$item['unit']}\n";
            $context .= "  - Reorder Level: {$reorder}\n";
            $context .= "  - Location: {$item['branch_name']}\n";
            $context .= "  - Status: {$status}\n";
            $context .= "  - Last Updated: {$item['updated_at']}\n\n";
        }

        $context .= "### AGGREGATE METRICS\n";
        $context .= "- **Total SKUs**: $totalItems\n";
        $context .= "- **Total Quantity**: $totalQty units\n";
        $context .= "- **Low Stock Items**: " . count($lowStock) . "\n";
        $context .= "- **Critical (Out of Stock)**: " . count($criticalStock) . "\n";

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the INVENTORY STRATEGIST - an elite AI specialist in supply chain optimization.
Your role is to manage stock levels, identify risks, and ensure inventory accuracy across the enterprise.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Stock Health Assessment**: Evaluate current levels against reorder points
2. **Risk Identification**: Flag critical stock-outs and dead stock scenarios
3. **Operational Insights**: Provide actionable recommendations for stock management
4. **Performance Analysis**: Identify fast-moving vs slow-moving SKUs

---

## INTERACTION MODEL
**Command Recognition**:
- Query Status: \"Show me inventory levels\" → Summarize current state with risk flags
- Find Item: \"Where is product XYZ\" → Locate in system with branch details
- Low Stock Alert: \"What's low?\" → List items below reorder point
- Add Stock: \"Add 50 units of SKU-123\" → Act as registry assistant and request missing details

---

## RESPONSE STANDARDS (MANDATORY)

### For READ QUERIES:
1. **Title**: INVENTORY STATUS REPORT | STOCK ANALYSIS | REORDER ALERTS (pick one)
2. **Overview**: 1 sentence executive summary
3. **Table**: Use proper Markdown table with these columns:
   | Product Name | SKU | Current Qty | Reorder Level | Status | Location |
   | --- | --- | --- | --- | --- | --- |
4. **Risk Summary**: Highlight critical stock situations
5. **Recommendations**: 1-2 actionable insights

### For CREATE/UPDATE QUERIES:
1. Acknowledge the user's intent
2. List the fields you need:
   - For adding stock: \"Product SKU, Quantity to add, Branch Location\"
   - Example: \"I can help add inventory. Please provide:
     - Product SKU (e.g., SKU-001)
     - Quantity to add
     - Branch/Location\"

---

## TONE & VOICE
- **Executive Level**: Clear, analytical, data-driven
- **Action-Oriented**: Focus on problems and solutions
- **No Fluff**: Eliminate conversational padding
- **Professional**: No emojis in final output (use for emphasis only internally)

---

## DATA CONSTRAINTS
- Use ONLY the provided inventory context
- Never assume restocking schedules or delivery times
- If data is missing, state it explicitly: \"No reorder level configured for this SKU.\"
- Current User Role: **{$roleTitle}** (access scope: " . ($roleId === 1 ? 'FULL SYSTEM' : ($roleId === 2 ? 'ASSIGNED BRANCHES' : 'ASSIGNED PRODUCTS')) . ")

---

## CRITICAL RULES
- ❌ Do NOT invent stock numbers
- ❌ Do NOT explain your reasoning process
- ❌ Do NOT mention agents, APIs, or system architecture
- ✅ Do USE the provided data exclusively
- ✅ Do IDENTIFY risks automatically
- ✅ Do REQUEST additional info when needed for CREATE operations";
    }
}
