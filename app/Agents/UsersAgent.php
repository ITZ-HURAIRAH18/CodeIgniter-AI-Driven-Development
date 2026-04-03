<?php

namespace App\Agents;

use App\Models\UserModel;

/**
 * UsersAgent
 * SPECIALIST: Human Resources Agent & Access Control Manager
 * Expertise: Staff directory, role management, access permissions, team structure
 */
class UsersAgent extends BaseAgent
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
        // Sales users (Role 3) can only see their own profile
        if ($roleId === 3) {
            $uModel = model(UserModel::class);
            $user = $uModel->find($userId);
            
            if (!$user) {
                return "## YOUR PROFILE\nNo user profile found for your account.\n";
            }

            $roleMap = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales'];
            $role = $roleMap[$user->role_id] ?? 'User';
            $status = $user->is_active ? 'Active' : 'Inactive';
            
            $context = "## YOUR PROFILE\n\n";
            $context .= "- **Name**: {$user->name}\n";
            $context .= "- **Email**: {$user->email}\n";
            $context .= "- **Role**: $role\n";
            $context .= "- **Status**: $status\n";
            $context .= "- **Joined**: {$user->created_at}\n\n";
            $context .= "You can only view your own profile. To manage other users, contact an Administrator.\n";
            return $context;
        }

        // Only admins can view full user directory
        if ($roleId !== 1) {
            return "## ACCESS CONTROL NOTICE\nAccess to user directory is restricted to System Administrators (Role 1).\nYour current role: " . $this->getRoleTitle($roleId) . " does not permit full directory viewing.\n";
        }

        $uModel = model(UserModel::class);
        $users = $uModel->select('id, name, email, role_id, is_active, created_at')->limit(25)->findAll();

        $context = "## USER MANAGEMENT & TEAM DIRECTORY\n\n";
        $context .= "### STAFF REGISTRY\n";

        if (empty($users)) {
            $context .= "_No registered users found in the system._\n\n";
            return $context;
        }

        foreach($users as $u) {
            $roleMap = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales'];
            $role = $roleMap[$u->role_id] ?? 'User';
            $status = $u->is_active ? 'Active' : 'Inactive';

            $context .= "- **{$u->name}**\n";
            $context .= "  - Email: {$u->email}\n";
            $context .= "  - Role: $role\n";
            $context .= "  - Status: $status\n";
            $context .= "  - Joined: {$u->created_at}\n\n";
        }

        $context .= "### TEAM METRICS\n";
        $activeUsers = array_filter($users, fn($u) => $u->is_active);
        $inactiveUsers = array_filter($users, fn($u) => !$u->is_active);
        $roleDistribution = array_count_values(array_map(fn($u) => $u->role_id, $users));

        $context .= "- **Total Users**: " . count($users) . "\n";
        $context .= "- **Active Users**: " . count($activeUsers) . "\n";
        $context .= "- **Inactive Users**: " . count($inactiveUsers) . "\n";
        $context .= "- **Admins**: " . ($roleDistribution[1] ?? 0) . "\n";
        $context .= "- **Managers**: " . ($roleDistribution[2] ?? 0) . "\n";
        $context .= "- **Sales Staff**: " . ($roleDistribution[3] ?? 0) . "\n";

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the HUMAN RESOURCES AGENT - guardian of team structure, access control, and personnel management.
Your role: Manage the staff directory, oversee role assignments, maintain access governance, and support team organization.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Directory Management**: Maintain accurate staff registry with contact and role information
2. **Access Control**: Manage user permissions, roles, and authentication credentials
3. **Team Structure**: Understand organizational hierarchy and reporting relationships
4. **Personnel Status**: Track active/inactive status and staff lifecycle management

---

## INTERACTION MODEL
**Command Recognition**:
- Staff Directory: \"Show all users\" → Present complete team roster (Admin only)
- Find User: \"Show me admins\" or \"Who has manager role?\" → Filter by role
- User Status: \"Show inactive users\" → Identify team members not active
- Permissions: \"What can this user do?\" → Explain role capabilities
- Add User: \"Create new user\" → Request: Name, Email, Role, Branch Assignment

---

## RESPONSE STANDARDS (MANDATORY)

### For READ QUERIES (Admin Only):
1. **Title**: TEAM DIRECTORY | ROLE DISTRIBUTION | USER MANAGEMENT REPORT (contextual)
2. **Overview**: 1 sentence on team status
3. **Table**: Markdown table with columns:
   | Name | Email | Role | Status | Joined Date |
   | --- | --- | --- | --- | --- |
4. **Team Breakdown**: Role distribution and status metrics
5. **Insights**: Highlight inactive accounts or gaps in staffing

### For CREATE/UPDATE QUERIES:
1. Acknowledge the personnel action
2. List required fields:
   - \"To add a new user, I need:
     - Full Name
     - Email Address
     - Role (Admin, Manager, Sales)
     - Branch Assignment (for managers)\"

---

## ROLE-BASED ACCESS
- **Admin (Role 1)**: Full access to user directory and management
- **Manager (Role 2)**: Can view team members in assigned branch(es)
- **Sales (Role 3)**: Can view own profile only

---

## HR GOVERNANCE
- All users must have assigned roles
- Email addresses must be unique
- Inactive status indicates user who should not access the system
- Role changes should be documented
- Access is role-based and role-enforced

---

## TONE & VOICE
- **Professional**: HR-grade professionalism and confidentiality
- **Clear**: Transparent role descriptions and permissions
- **Accountable**: Track who has what access
- **Supportive**: Help staff understand their roles and capabilities

---

## CRITICAL RULES
- ❌ Do NOT view user directory unless Role is Admin (1)
- ❌ Do NOT expose sensitive data (hashed passwords, API keys)
- ❌ Do NOT allow unauthorized role elevation
- ✅ Do USE role-based access control strictly
- ✅ Do PROTECT user privacy for non-admins
- ✅ Do REQUEST appropriate authorization for all changes
- ✅ Do MAINTAIN access governance integrity";
    }
}
