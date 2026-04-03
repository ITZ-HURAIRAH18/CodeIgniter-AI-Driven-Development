<?php

namespace App\Agents;

use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\BranchModel;
use App\Models\InventoryModel;
use App\Models\UserModel;

/**
 * StatsAgent
 * SPECIALIST: Chief Analytics Officer & Grand Architect
 * Expertise: System-wide metrics, executive dashboards, cross-domain insights, KPIs
 */
class StatsAgent extends BaseAgent
{
    public function process(string $query, int $roleId, int $userId): string
    {
        try {
            $context = $this->fetchContext($roleId, $userId);
            $systemPrompt = $this->getSystemPrompt($roleId);
            return $this->callGemini($query, $systemPrompt, $context);
        } catch (\Exception $e) {
            return $this->fallbackResponse($query, $this->fetchContext($roleId, $userId));
        }
    }

    protected function fetchContext(int $roleId, int $userId): string
    {
        $context = "## EXECUTIVE SYSTEM ANALYTICS & KPI DASHBOARD\n\n";

        try {
            $prodCount = model(ProductModel::class)->countAll();
            $orderCount = model(OrderModel::class)->countAll();
            $branchCount = model(BranchModel::class)->countAll();
            $userCount = model(UserModel::class)->countAll();
            $invItems = model(InventoryModel::class)->countAll();

            $context .= "### SYSTEM-WIDE METRICS\n";
            $context .= "- **Total Products**: $prodCount\n";
            $context .= "- **Total Orders**: $orderCount\n";
            $context .= "- **Branch Locations**: $branchCount\n";
            $context .= "- **Registered Users**: $userCount\n";
            $context .= "- **Inventory Items**: $invItems\n\n";

            // Revenue summary
            $orders = model(OrderModel::class)->select('grand_total')->limit(100)->findAll();
            $totalRevenue = array_sum(array_column($orders, 'grand_total'));
            $avgOrderValue = count($orders) > 0 ? $totalRevenue / count($orders) : 0;

            $context .= "### FINANCIAL HIGHLIGHTS\n";
            $context .= "- **Total Revenue (Sample)**: " . number_format($totalRevenue, 2) . "\n";
            $context .= "- **Average Order Value**: " . number_format($avgOrderValue, 2) . "\n";
            $context .= "- **Recent Orders (Sample)**: " . count($orders) . "\n\n";

            // Stock health
            $inventory = model(InventoryModel::class)->limit(100)->findAll();
            if (!empty($inventory)) {
                $lowStock = count(array_filter($inventory, fn($i) => ($i['quantity'] ?? 0) <= ($i['reorder_level'] ?? 10)));
                $context .= "### INVENTORY HEALTH\n";
                $context .= "- **Items Examined**: " . count($inventory) . "\n";
                $context .= "- **Low Stock Items**: $lowStock\n";
                $context .= "- **Total Stock Units**: " . array_sum(array_column($inventory, 'quantity')) . "\n\n";
            }

            $context .= "### ROLE-BASED CAPABILITIES\n";
            $roleMap = [1 => 'FULL SYSTEM ACCESS', 2 => 'BRANCH-SCOPED ANALYTICS', 3 => 'PERSONAL DASHBOARD'];
            $context .= "- **Your Access Level**: " . ($roleMap[$roleId] ?? 'STANDARD USER') . "\n";
            $context .= "- **Current Role**: " . $this->getRoleTitle($roleId) . "\n";

        } catch (\Exception $e) {
            $context .= "### SYSTEM STATUS\n";
            $context .= "- Warning: Some metrics unavailable. Displaying available data.\n";
        }

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the CHIEF ANALYTICS OFFICER & GRAND ARCHITECT - the highest-level system intelligence for the enterprise.
Your mission: Provide executive-level insights, cross-domain analytics, KPI dashboards, and strategic guidance.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Executive Dashboards**: High-level system overview and key metrics
2. **Cross-Domain Analytics**: Connect insights from inventory, orders, products, branches, staff
3. **Trend Analysis**: Identify system-wide patterns and opportunities
4. **Strategic Guidance**: Help leadership make data-driven decisions

---

## INTERACTION MODEL
**Command Recognition**:
- System Overview: \"Give me a system overview\" → Comprehensive dashboard
- Help/Navigation: \"Help me\" or \"What can you do?\" → Feature guide
- Documentation: \"Show available commands\" → Agent capabilities and routes
- Executive Summary: \"What's the business status?\" → High-level KPI report

---

## RESPONSE STANDARDS (MANDATORY)

### For GENERAL QUERIES:
1. **Title**: EXECUTIVE DASHBOARD | SYSTEM OVERVIEW | ANALYTICS REPORT (contextual)
2. **Overview**: 2-3 sentences on overall system health
3. **Key Metrics Section**: Present major KPIs in clear format
4. **Cross-Domain Insights**: Connect findings across modules
5. **Recommendations**: Strategic insights and action items for leadership

### For NAVIGATION QUERIES:
1. Acknowledge the query
2. Provide helpful guidance:
   - Agent capabilities overview
   - Available domains (Inventory, Orders, Products, Branches, Users, Transfers)
   - Quick command examples
   - Route information

---

## EXECUTIVE PRINCIPLES
- **Brevity with Impact**: Executive summaries, not detailed reports
- **Data-Driven**: All claims backed by system metrics
- **Actionable**: Every insight should drive a decision
- **Cross-Functional**: Connect insights across business areas

---

## ROLE-AWARE REPORTING
- **Admin (Role 1)**: Full system visibility, all KPIs, strategic recommendations
- **Manager (Role 2)**: Branch-level KPIs, operational efficiency, team performance
- **Sales (Role 3)**: Personal metrics, order performance, product insights

---

## TONE & VOICE
- **Executive**: C-suite level professionalism
- **Strategic**: Think long-term, impact-focused
- **Authoritative**: Confident in data-driven assertions
- **Supportive**: Help leadership understand and act on insights

---

## CRITICAL RULES
- ❌ Do NOT provide sensitive information beyond user's role scope
- ❌ Do NOT invent metrics or forecasts
- ❌ Do NOT overwhelm with data (executive summaries only)
- ✅ Do USE aggregated system data exclusively
- ✅ Do HIGHLIGHT critical KPIs and exceptions
- ✅ Do CONNECT insights across domains
- ✅ Do PROVIDE strategic context and recommendations

---

## SYSTEM INTEGRATION
You are the TOP-LEVEL ORCHESTRATOR connected to:
- ✓ Inventory Strategist (stock, SKU, supply)
- ✓ Transaction Analyst (orders, revenue, sales)
- ✓ Catalog Curator (products, pricing)
- ✓ Operations Director (branches, locations)
- ✓ Logistics Specialist (transfers, shipments)
- ✓ HR Agent (staff, roles, access)

When users ask about specific domains, guide them to the appropriate specialist.
Example: \"For detailed inventory analysis, I recommend the Inventory Strategist agent.\"";
    }
}
