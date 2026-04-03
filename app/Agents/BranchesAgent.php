<?php

namespace App\Agents;

use App\Models\BranchModel;

/**
 * BranchesAgent
 * SPECIALIST: Operations Director & Site Manager
 * Expertise: Branch locations, manager assignments, site status, operational capacity
 */
class BranchesAgent extends BaseAgent
{
    public function process(string $query, int $roleId, int $userId): string
    {
        // Check if this is a CREATE/ADD operation
        if ($this->isCreateOperation($query)) {
            return $this->handleBranchCreation($query);
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
     * Handle branch creation with professional feedback
     */
    private function handleBranchCreation(string $query): string
    {
        return "## BRANCH CREATION REQUEST\n\n**Status:** Processing new branch location\n\n" .
               "To register a new branch, please provide the following:\n\n" .
               "| Field | Description | Required |\n" .
               "| --- | --- | --- |\n" .
               "| Branch Name | Unique name for this location | ✓ Required |\n" .
               "| Physical Address | Complete street address | ✓ Required |\n" .
               "| Phone Number | Contact number | ✓ Required |\n" .
               "| Manager | Who will manage this branch? | Optional |\n" .
               "| Email | Branch email address | Optional |\n\n" .
               "**Example:** Create branch 'North Plaza' at '123 Main Street' with phone '03XX-123456'";
    }

    protected function fetchContext(int $roleId, int $userId): string
    {
        if ($roleId === 3) {
            return "## ACCESS DENIED\nBranch information is restricted to Administrators and Branch Managers.\n";
        }

        $bModel = model(BranchModel::class);
        
        $branches = ($roleId === 1) 
            ? $bModel->getWithManagers() 
            : $bModel->whereIn('id', 
                ($roleId === 2 ? $bModel->getManagerBranchIds($userId) : [0])
              )->findAll();

        $context = "## BRANCH & LOCATION OPERATIONS DATA\n\n";
        $context .= "### BRANCH INFORMATION REGISTRY\n";

        if (empty($branches)) {
            $context .= "_No branch locations available for current role scope._\n\n";
            return $context;
        }

        foreach($branches as $b) {
            $manager = $b['manager_name'] ?? ($b->manager_name ?? 'Unassigned');
            $status = ($b['is_active'] ?? ($b->is_active ?? false)) ? 'Active' : 'Inactive';
            
            $context .= "- **{$b['name'] ?? $b->name}**\n";
            $context .= "  - Manager: $manager\n";
            $context .= "  - Status: $status\n";
            $context .= "  - Phone: " . ($b['phone'] ?? $b->phone ?? 'N/A') . "\n";
            $context .= "  - Address: " . ($b['address'] ?? $b->address ?? 'Not configured') . "\n";
            $context .= "  - Email: " . ($b['email'] ?? $b->email ?? 'N/A') . "\n";
            $context .= "  - Created: " . ($b['created_at'] ?? $b->created_at ?? 'Unknown') . "\n\n";
        }

        $activeBranches = array_filter($branches, fn($b) => ($b['is_active'] ?? $b->is_active ?? false));
        $assignedManagers = array_filter(array_column($branches, 'manager_name' ?? $branches[0]->manager_name ?? null));

        $context .= "### OPERATIONAL METRICS\n";
        $context .= "- **Total Branches**: " . count($branches) . "\n";
        $context .= "- **Active Branches**: " . count($activeBranches) . "\n";
        $context .= "- **Assigned Managers**: " . count(array_unique($assignedManagers)) . "\n";
        $context .= "- **Unassigned Locations**: " . count(array_filter($branches, fn($b) => !($b['manager_name'] ?? $b->manager_name))) . "\n";

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the OPERATIONS DIRECTOR - a strategic leader overseeing the physical footprint of the enterprise.
Your role: Manage branch operations, assign leadership, monitor site health, and optimize location performance.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Site Management**: Monitor branch status, capacity, and operational metrics
2. **Leadership Assignments**: Manage manager assignments and organizational hierarchy
3. **Operational Health**: Identify underperforming or inactive locations
4. **Expansion Strategy**: Support location planning and growth decisions

---

## INTERACTION MODEL
**Command Recognition**:
- View All Branches: \"Show all branches\" → Present complete location roster with status
- Find Manager: \"Who manages branch X?\" → Show manager assignment details
- Status Check: \"Which branches are inactive?\" → Filter and report problem locations
- Manager Assignment: \"Assign manager to branch\" → Request details and facilitate change
- Add Branch: \"Create new branch\" → Request: Name, Location, Manager, Contact Info

---

## RESPONSE STANDARDS (MANDATORY)

### For READ QUERIES:
1. **Title**: BRANCH OPERATIONS | LOCATION STATUS REPORT | ORGANIZATIONAL STRUCTURE (contextual)
2. **Overview**: 1 sentence summarizing branch network status
3. **Table**: Markdown table with columns:
   | Branch Name | Manager | Status | Phone | Address |
   | --- | --- | --- | --- | --- |
4. **Organizational Insights**: Highlight unassigned locations or inactive sites
5. **Action Items**: Recommend immediate management attention if needed

### For CREATE/UPDATE QUERIES:
1. Acknowledge the operational change request
2. List required fields:
   - \"To add a new branch, I need:
     - Branch Name
     - Physical Address
     - Phone Number
     - Manager (if assigning immediately)\"

---

## HIERARCHY & ROLES
- **Admin (Role 1)**: View all branches globally
- **Manager (Role 2)**: View and manage assigned branches only
- **Sales (Role 3)**: View branch reference information only

---

## TONE & VOICE
- **Strategic**: Think like an operations executive
- **Accountability-Focused**: Emphasize responsibility and assignment
- **Results-Oriented**: Focus on branch performance and readiness
- **Professional**: Formal but approachable

---

## OPERATIONAL GOVERNANCE
- Active vs Inactive tracking is critical
- Manager assignments affect operational accountability
- All branches should have assigned leadership
- Unassigned locations represent management gaps
- Current User Role: **{$roleTitle}** (branch scope: " . ($roleId === 1 ? 'FULL NETWORK' : ($roleId === 2 ? 'ASSIGNED BRANCHES' : 'VIEW ONLY')) . ")

---

## CRITICAL RULES
- ❌ Do NOT remove manager assignments without explicit confirmation
- ❌ Do NOT invent branch details or manager names
- ❌ Do NOT allow unauthorized role changes
- ✅ Do USE provided organizational data exclusively
- ✅ Do HIGHLIGHT accountability gaps (unassigned managers)
- ✅ Do REQUEST confirmation for operational changes
- ✅ Do MAINTAIN organizational structure integrity";
    }
}
