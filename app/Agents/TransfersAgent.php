<?php

namespace App\Agents;

use App\Models\StockTransferModel;
use App\Models\BranchModel;

/**
 * TransfersAgent
 * SPECIALIST: Logistics Specialist & Supply Chain Coordinator
 * Expertise: Inter-branch transfers, shipment tracking, transit logistics, delivery status
 */
class TransfersAgent extends BaseAgent
{
    public function process(string $query, int $roleId, int $userId): string
    {
        // Check if this is a CREATE/ADD operation
        if ($this->isCreateOperation($query)) {
            return $this->handleTransferCreation($query);
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
     * Handle transfer creation with professional feedback
     */
    private function handleTransferCreation(string $query): string
    {
        return "## STOCK TRANSFER REQUEST\n\n**Status:** Processing inter-branch transfer\n\n" .
               "To create a stock transfer, please provide the following:\n\n" .
               "| Field | Description | Required |\n" .
               "| --- | --- | --- |\n" .
               "| From Branch | Source/origin branch | ✓ Required |\n" .
               "| To Branch | Destination branch | ✓ Required |\n" .
               "| Product(s) | Product name/SKU and quantities | ✓ Required |\n" .
               "| Expected Delivery | When should it arrive? | Optional |\n" .
               "| Notes | Any special instructions? | Optional |\n\n" .
               "**Example:** Transfer 30 units of Orange from East branch to West branch, expected delivery: tomorrow";
    }

    protected function fetchContext(int $roleId, int $userId): string
    {
        if ($roleId === 3) {
            return "## ACCESS DENIED\nTransfer and logistics information is restricted to Administrators and Branch Managers.\n";
        }

        $branchIds = ($roleId === 2) ? model(BranchModel::class)->getManagerBranchIds($userId) : [];
        $tModel = model(StockTransferModel::class);

        $transfers = ($roleId === 1) 
            ? $tModel->listAll() 
            : $tModel->listAllMultiBranch($branchIds);

        $context = "## LOGISTICS & STOCK TRANSFER DATA\n\n";
        $context .= "### TRANSFER RECORDS & SHIPMENT TRACKING\n";

        if (empty($transfers)) {
            $context .= "_No transfer activity recorded for current scope._\n\n";
            return $context;
        }

        // Organize by status
        $byStatus = [];
        foreach($transfers as $t) {
            $status = $t['status'] ?? 'unknown';
            if (!isset($byStatus[$status])) $byStatus[$status] = [];
            $byStatus[$status][] = $t;
        }

        foreach($transfers as $t) {
            $context .= "- **Transfer ID: {$t['id']}**\n";
            $context .= "  - From: {$t['from_branch']}\n";
            $context .= "  - To: {$t['to_branch']}\n";
            $context .= "  - Status: {$t['status']}\n";
            $context .= "  - Total Quantity: {$t['total_quantity'] ?? 0} units\n";
            $context .= "  - Created: " . ($t['created_at'] ?? 'N/A') . "\n";
            $context .= "  - Expected Delivery: " . ($t['expected_delivery'] ?? 'Not set') . "\n\n";
        }

        $context .= "### TRANSFER METRICS BY STATUS\n";
        foreach($byStatus as $status => $items) {
            $totalQty = array_sum(array_column($items, 'total_quantity'));
            $context .= "- **{$status}**: " . count($items) . " transfers (" . $totalQty . " units total)\n";
        }

        // Flag delayed transfers
        $delayed = array_filter($transfers, fn($t) => 
            strtolower($t['status'] ?? '') === 'pending' && 
            strtotime($t['created_at'] ?? now()) < strtotime('-3 days')
        );

        if (!empty($delayed)) {
            $context .= "\n### ⚠️ ALERTS: DELAYED TRANSFERS\n";
            foreach($delayed as $d) {
                $context .= "- Transfer #{$d['id']}: Pending for more than 3 days (Created: {$d['created_at']})\n";
            }
        }

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the LOGISTICS SPECIALIST - a master of supply chain coordination and inventory movement.
Your mission: Track shipments, optimize inter-branch transfers, flag delivery delays, and ensure stock arrives where needed.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Shipment Tracking**: Monitor transfer status from origin to destination
2. **Delay Detection**: Identify prolonged transfers and bottlenecks in the supply chain
3. **Route Optimization**: Help coordinate transfers based on inventory needs
4. **Delivery Accountability**: Ensure transfers are completed on schedule

---

## INTERACTION MODEL
**Command Recognition**:
- Track Transfer: \"Where's transfer #123?\" → Show complete transfer status and timeline
- Pending Transfers: \"Show me pending transfers\" → List in-progress shipments
- Delayed Shipments: \"What's delayed?\" → Flag transfers overdue for delivery
- Branch-to-Branch: \"Transfer from branch A to B\" → Route planning assistance
- Create Transfer: \"Create new transfer\" → Request: Source Branch, Destination Branch, Products & Quantities

---

## RESPONSE STANDARDS (MANDATORY)

### For READ QUERIES:
1. **Title**: TRANSFER STATUS | SHIPMENT TRACKING | LOGISTICS REPORT (contextual)
2. **Overview**: 1 sentence on overall transfer network status
3. **Table**: Markdown table with columns:
   | Transfer ID | From | To | Status | Quantity | Expected Delivery |
   | --- | --- | --- | --- | --- | --- |
4. **Status Breakdown**: Show counts by transfer status
5. **Alert Section**: Highlight any delayed or problematic transfers

### For CREATE/UPDATE QUERIES:
1. Acknowledge the transfer intent
2. List required fields:
   - \"To create a transfer, I need:
     - Source Branch
     - Destination Branch
     - Product SKU & Quantity (can add multiple)
     - Expected Delivery Date (optional)\"

---

## SUPPLY CHAIN PRINCIPLES
- Transfers should have clear expected delivery dates
- Pending transfers > 3 days warrant investigation
- All transfers must have documented origin and destination
- Quantities must be verified before shipment
- Current User Role: **{$roleTitle}** (transfer scope: " . ($roleId === 1 ? 'ALL BRANCHES' : 'ASSIGNED BRANCHES') . ")

---

## TONE & VOICE
- **Action-Driven**: Focus on keeping inventory moving
- **Detail-Oriented**: Track every shipment precisely
- **Problem-Solver**: Proactively identify and resolve delays
- **Professional**: Clear tracking language and status reporting

---

## CRITICAL RULES
- ❌ Do NOT bypass transfer documentation
- ❌ Do NOT assume delivery dates (use provided data)
- ❌ Do NOT invent transfer records
- ✅ Do USE transfer data exclusively
- ✅ Do ALERT on delayed shipments automatically
- ✅ Do REQUEST all required info before creating transfers
- ✅ Do MAINTAIN complete chain-of-custody documentation";
    }
}
