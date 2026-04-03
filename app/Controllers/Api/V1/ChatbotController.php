<?php

namespace App\Controllers\Api\V1;

use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\BranchModel;
use App\Models\OrderModel;
use App\Models\InventoryModel;
use App\Models\StockTransferModel;

/**
 * ChatbotController
 * THE GRAND ARCHITECT: A Multi-Agent AI Ecosystem for ERP Management.
 * Routes user intents to specialized sub-agents with domain-specific intelligence.
 */
class ChatbotController extends BaseApiController
{
    private $geminiKey;
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->geminiKey = getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? null;
    }

    /** Preflight CORS handler */
    public function options() { return $this->addCorsHeaders($this->response->setStatusCode(204)); }

    /** Main Agent Entry Point */
    public function query()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            log_message('error', "[$errno] $errstr in $errfile:$errline");
            return true;
        });

        try {
            $query = $this->request->getVar('query');
            if (empty($query)) return $this->apiError('Query is required', 400);

            $user = $this->actor();
            $roleId = (int)($user->role_id ?? 0);
            $userId = (int)($user->sub ?? 0);

            // Check API key exists
            if (!getenv('GEMINI_API_KEY') && empty($_ENV['GEMINI_API_KEY'] ?? null)) {
                return $this->respond([
                    'success' => true,
                    'data'    => [
                        'response' => '## ⚠️ SYSTEM CONFIGURATION\n\nThe AI service is not yet configured.\n\nPlease contact your administrator to set up the GEMINI_API_KEY.',
                        'module'   => 'system'
                    ]
                ]);
            }

            // STEP 1: Identify module
            $module = $this->identifyModule($query);
            
            // STEP 2: Fetch context
            $context = $this->fetchAgentContext($module, $roleId, $userId);

            // STEP 3: Get system prompt
            $systemPrompt = $this->getAgentSystemPrompt($module, $roleId);

            $responseText = '';
            if ($this->geminiKey) {
                try {
                    set_time_limit(25);
                    $responseText = $this->callGemini($query, $systemPrompt, $context);
                } catch (\Exception $e) {
                    log_message('error', 'Gemini API error: ' . $e->getMessage());
                    $responseText = '';
                }
            }

            // Fallback
            if (empty($responseText)) {
                $responseText = "**PROCESSING COMPLETE**\n\nContext Data Retrieved:\n\n" . substr($context, 0, 400) . "...\n\n" .
                               "*Note: Full AI processing is temporarily unavailable. Showing raw system context.*";
            }

            return $this->addCorsHeaders($this->respond([
                'success' => true,
                'data'    => [
                    'response' => $responseText, 
                    'agent'    => ucfirst($module) . " Specialist",
                    'module'   => $module
                ]
            ]));

        } catch (\Exception $e) {
            log_message('error', 'Chatbot fatal error: ' . $e->getMessage());
            return $this->addCorsHeaders($this->respond([
                'success' => true,
                'data'    => [
                    'response' => '## SYSTEM STATUS\n\nThe system encountered a processing issue. Please try again with a simpler query.\n\nExample: "Show inventory"',
                    'module'   => 'system'
                ]
            ]));
        } finally {
            restore_error_handler();
        }
    }

    /**
     * LOCAL ROUTER: The Grand Architect's primary routing logic.
     * Uses pattern matching to delegate to specialized sub-agents.
     */
    private function identifyModule(string $query): string
    {
        $q = strtolower($query);
        if (preg_match('/inventory|stock|available|level|reorder|quantity|count/', $q)) return 'inventory';
        if (preg_match('/order|sale|purchase|transact|buy|sell|customer|bill/', $q)) return 'orders';
        if (preg_match('/product|item|category|sku|price|detail/', $q)) return 'products';
        if (preg_match('/user|staff|admin|manager|role|profile|member/', $q)) return 'users';
        if (preg_match('/transfer|move|ship|dispatch|logistics/', $q)) return 'transfers';
        if (preg_match('/branch|location|store|warehouse|site|office/', $q)) return 'branches';
        
        return 'general';
    }

    /**
     * AGENT-SPECIFIC DATA FETCHING
     * Provides the raw data context for the sub-agent to analyze.
     */
    private function fetchAgentContext(string $module, int $roleId, int $userId): string
    {
        $branchIds = ($roleId === 2) ? model(BranchModel::class)->getManagerBranchIds($userId) : [];
        $context = "PRIMARY DATA CONTEXT FOR AGENT:\n";

        switch ($module) {
            case 'inventory':
                $invModel = model(InventoryModel::class);
                $data = ($roleId === 1) ? $invModel->getAllWithProductDetails() : $invModel->getByBranchesWithDetails($branchIds);
                foreach(array_slice($data, 0, 20) as $i) {
                    $context .= "- SKU: {$i['sku']} | Product: {$i['product_name']} | Qty: {$i['quantity']} | Branch: {$i['branch_name']} | Reorder: {$i['reorder_level']}\n";
                }
                break;

            case 'orders':
                $orderModel = model(OrderModel::class);
                $orders = ($roleId === 1) ? $orderModel->getWithDetails() : ($roleId === 2 ? $orderModel->getWithDetailsMultiBranch($branchIds) : $orderModel->getWithDetails(null, $userId));
                foreach(array_slice($orders, 0, 15) as $o) {
                    $context .= "- #{$o['order_number']} | Total: {$o['grand_total']} | Status: {$o['status']} | Date: {$o['created_at']} | Branch: {$o['branch_name']}\n";
                }
                break;

            case 'products':
                $prods = model(ProductModel::class)->limit(20)->findAll();
                foreach($prods as $p) {
                    $context .= "- SKU: {$p->sku} | Name: {$p->name} | Price: {$p->sale_price} | Cost: {$p->purchase_price} | Category: " . ($p->category_id ?? 'N/A') . "\n";
                }
                break;

            case 'branches':
                $branches = ($roleId === 1) ? model(BranchModel::class)->getWithManagers() : model(BranchModel::class)->whereIn('id', $branchIds ?: [0])->findAll();
                foreach($branches as $b) {
                    $context .= "- Branch: {$b['name']} | Manager: " . ($b['manager_name'] ?? 'Unassigned') . " | Status: " . ($b['is_active'] ? 'Active' : 'Inactive') . " | Contact: {$b['phone']}\n";
                }
                break;

            case 'transfers':
                $transfers = ($roleId === 1) ? model(StockTransferModel::class)->listAll() : model(StockTransferModel::class)->listAllMultiBranch($branchIds);
                foreach(array_slice($transfers, 0, 15) as $t) {
                    $context .= "- ID: {$t['id']} | From: {$t['from_branch']} | To: {$t['to_branch']} | Status: {$t['status']} | Qty: {$t['total_quantity']}\n";
                }
                break;

            case 'users':
                if ($roleId === 1) {
                    $users = model(UserModel::class)->select('name, email, role_id, is_active')->limit(15)->findAll();
                    foreach($users as $u) {
                        $role = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales'][$u->role_id] ?? 'User';
                        $context .= "- User: {$u->name} | Email: {$u->email} | Role: $role | Active: " . ($u->is_active ? 'Yes' : 'No') . "\n";
                    }
                } else {
                    $context .= "ACCESS DENIED: Role unauthorized for user directory.\n";
                }
                break;

            default:
                $context .= "System State: Admin role active. High-level dashboard visibility enabled. Summary: " . model(ProductModel::class)->countAll() . " Products, " . model(OrderModel::class)->countAll() . " Orders.";
        }

        return $context;
    }

    /**
     * GOD LEVEL PROMPT ENGINE
     * Dynamically constructs the system personality based on the identified module.
     */
    private function getAgentSystemPrompt(string $module, int $roleId): string
    {
        $roleMap = [1 => 'System Administrator', 2 => 'Regional Manager', 3 => 'Sales Associate'];
        $userRole = $roleMap[$roleId] ?? 'System User';

        $base = "You are an elite, specialized AI Agent within an Enterprise Inventory Management Ecosystem.
Current User Role: {$userRole}.
Mode: Real-time Data Analysis & Interactive Command Processing.

---

## CORE DIRECTIVES (APPLY TO ALL AGENTS)
1. **Precision**: Use ONLY provided data. Never extrapolate or hallucinate.
2. **Professionalism**: Maintain a high-tier, executive tone. No emojis. No conversational fluff.
3. **Format**: Always use Markdown Tables for data lists. No exceptions.
4. **Actionable Insights**: Don't just show data; identify trends or issues (e.g., 'Low Stock', 'High-Value Order').
5. **Interactive Intent**: If the user wants to ADD or UPDATE something, act as a 'Registry Agent':
   - Identify missing information.
   - Ask for specific fields clearly (e.g. 'I need Name, Location, Manager').
   - Example: \"I can help you add a branch. Please provide the Branch Name, Manager ID, and Address.\"

---

";

        $prompts = [
            'inventory' => "### ROLE: INVENTORY STRATEGIST
- Focus: Stock health, reorder levels, and SKU performance.
- Goal: Minimize stock-outs and identify dead stock.
- Formatting: Include 'Stock Status' (Healthy, Low, Critical) based on reorder levels.",

            'orders' => "### ROLE: TRANSACTION ANALYST
- Focus: Order flow, revenue tracking, and order status lifecycle.
- Goal: Identify pending bottlenecks and summarize sales performance.
- Formatting: Use columns for Order ID, status, and Total Value.",

            'products' => "### ROLE: CATALOG CURATOR
- Focus: Product details, pricing strategy, and categorization.
- Goal: Help users find items quickly and maintain catalog integrity.
- Formatting: Highlight pricing tiers and SKU identifiers.",

            'branches' => "### ROLE: OPERATIONS DIRECTOR
- Focus: Branch performance, manager assignments, and site status.
- Goal: Manage the physical footprint of the enterprise.
- Interaction: For adding branches, ensure you request Name, Address, and Phone.",

            'transfers' => "### ROLE: LOGISTICS SPECIALIST
- Focus: Inter-branch movement, shipping logs, and transit times.
- Goal: Ensure stock is where it needs to be. Identify delayed transfers.
- Formatting: Focus on Origin vs Destination analysis.",

            'users' => "### ROLE: HUMAN RESOURCE AGENT
- Focus: Access control, activity status, and team structure.
- Goal: Secure management of personnel.",

            'general' => "### ROLE: GRAND ARCHITECT
- Focus: Holistic system health and general navigation.
- Goal: Provide a high-level summary if no specific module is identified."
        ];

        return $base . ($prompts[$module] ?? $prompts['general']) . "\n\n### RESPONSE STRUCTURE (STRICT)
1. Title: Bold, uppercase, no emojis.
2. Content: Analyze the $module query using the provided context.
3. Table: Professional Markdown Table (if applicable).
4. Summary: 1-2 sentences of executive-level insights.";
    }

    private function callGemini(string $query, string $system, string $data): string
    {
        if (!$this->geminiKey) {
            return "AI Service unavailable. Showing context: " . substr($data, 0, 200);
        }

        $url = $this->geminiUrl . '?key=' . trim($this->geminiKey);
        
        // Compress payload to reduce size
        $compactPayload = "Role: " . substr($system, 0, 100) . "\nContext: " . substr($data, 0, 300) . "\nQuery: $query";
        
        $payload = [
            'contents' => [['parts' => [['text' => $compactPayload]]]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);  // 12 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $res = curl_exec($ch);
        $errno = curl_errno($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Handle timeout and network errors gracefully
        if ($errno === CURLE_OPERATION_TIMEDOUT || $errno === CURLE_COULDNT_RESOLVE_HOST) {
            return "Request timed out. Showing system data: " . substr($data, 0, 300) . "...";
        }

        if ($code !== 200) {
            return "API processing data context. " . substr($data, 0, 200) . "...";
        }
        
        $json = json_decode($res, true);
        return trim($json['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    /** Suggestions based on role */
    public function suggest()
    {
        $user = $this->actor();
        $roleId = (int)($user->role_id ?? 0);
        $suggestions = [
            1 => ['System Audit Report', 'Add New Branch', 'High-Level Metrics'],
            2 => ['Inventory at my branch', 'Incoming Transfers', 'Pending Sales'],
            3 => ['Stock Availability', 'Record New Sale', 'Recent Orders']
        ];
        return $this->ok($suggestions[$roleId] ?? ['General Support']);
    }

    public function getRoutes()
    {
        return $this->ok(['query' => '/api/v1/chatbot/query', 'suggest' => '/api/v1/chatbot/suggest']);
    }
}
