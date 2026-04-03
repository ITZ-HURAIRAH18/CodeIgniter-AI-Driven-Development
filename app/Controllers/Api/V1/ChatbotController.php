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
 * Advanced Gemini-only AI Assistant for Inventory Management System.
 * Operates as a Main Agent routing queries to data-rich sub-modules.
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

    /** Main Chatbot Query entry point */
    public function query()
    {
        $query = $this->request->getVar('query');
        if (empty($query)) return $this->apiError('Query is required', 400);

        $user = $this->actor();
        $roleId = (int)($user->role_id ?? 0);
        $userId = (int)($user->sub ?? 0);

        // --- STEP 1: Main Agent - Categorization ---
        $module = $this->identifyModule($query);
        
        // --- STEP 2: Sub-Agent - Data Gathering ---
        $dataSummary = $this->fetchModuleData($module, $roleId, $userId);

        // --- STEP 3: Prompt Assembly & Gemini Call ---
        $systemPrompt = $this->buildMainSystemPrompt($roleId);

        try {
            $responseText = '';
            
            if ($this->geminiKey) {
                try {
                    $responseText = $this->callGemini($query, $systemPrompt, $dataSummary);
                } catch (\Exception $e) { 
                    // Log error internally if needed
                }
            }

            // Fallback if Gemini failed or key missing
            if (empty($responseText)) {
                $responseText = $this->getStaticIntelligentResponse($query, $dataSummary, $roleId);
            }

            return $this->addCorsHeaders($this->respond([
                'success' => true,
                'data'    => ['response' => $responseText, 'module' => $module]
            ]));
        } catch (\Exception $e) {
            return $this->apiError('Gemini Agent processing failed: ' . $e->getMessage());
        }
    }

    private function identifyModule(string $query): string
    {
        $q = strtolower($query);
        if (preg_match('/inventory|stock|available|level|reorder|quantity|count/', $q)) return 'inventory';
        if (preg_match('/order|sale|purchase|transact|buy|sell|customer|bill/', $q)) return 'orders';
        if (preg_match('/product|item|category|sku|price|detail/', $q)) return 'products';
        if (preg_match('/user|staff|admin|manager|role|profile|member/', $q)) return 'users';
        if (preg_match('/transfer|move|ship|dispatch|logistics/', $q)) return 'transfers';
        if (preg_match('/branch|location|store|warehouse|site|office/', $q)) return 'branches';
        
        return 'stats';
    }

    private function fetchModuleData(string $module, int $roleId, int $userId): string
    {
        $summary = "SYSTEM CONTEXT MODULE: " . strtoupper($module) . "\n";
        $branchIds = ($roleId === 2) ? model(BranchModel::class)->getManagerBranchIds($userId) : [];

        switch ($module) {
            case 'inventory':
                $invModel = model(InventoryModel::class);
                $data = ($roleId === 1) ? $invModel->getAllWithProductDetails() : $invModel->getByBranchesWithDetails($branchIds);
                $list = "";
                foreach(array_slice($data, 0, 15) as $i) {
                    $list .= "- {$i['product_name']} (SKU: {$i['sku']}): {$i['quantity']} {$i['unit']} at {$i['branch_name']}\n";
                }
                $summary .= "Stock Levels:\n" . ($list ?: "No stock found.") . "\n";
                $lowStock = count(array_filter($data, fn($i) => ($i['quantity'] ?? 0) <= ($i['reorder_level'] ?? 10)));
                $summary .= "Insights: Total Items=" . count($data) . ", Low Stock Items=$lowStock\n";
                break;

            case 'orders':
                $orderModel = model(OrderModel::class);
                if ($roleId === 1) $orders = $orderModel->getWithDetails();
                elseif ($roleId === 2) $orders = $orderModel->getWithDetailsMultiBranch($branchIds);
                else $orders = $orderModel->getWithDetails(null, $userId); 
                $list = "";
                foreach(array_slice($orders, 0, 10) as $o) {
                    $list .= "- Order {$o['order_number']}: Total {$o['grand_total']}, Status: {$o['status']}\n";
                }
                $summary .= "Recent Orders:\n" . ($list ?: "No orders found.") . "\n";
                break;

            case 'products':
                $pModel = model(ProductModel::class);
                $prods = $pModel->limit(15)->findAll();
                $list = "";
                foreach($prods as $p) { $list .= "- {$p->name} (SKU: {$p->sku}): Price {$p->sale_price}\n"; }
                $summary .= "Product Catalog:\n" . ($list ?: "Empty catalog.") . "\n";
                break;

            case 'users':
                if ($roleId !== 1) { $summary .= "Access Restricted.\n"; }
                else {
                    $uModel = model(UserModel::class);
                    $users = $uModel->select('name, email, role_id, is_active')->limit(15)->findAll();
                    $list = "";
                    foreach($users as $u) {
                        $roleName = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales'][$u->role_id] ?? 'User';
                        $list .= "- {$u->name} ({$u->email}) - Role: {$roleName}\n";
                    }
                    $summary .= "User Directory:\n" . ($list ?: "No users.") . "\n";
                }
                break;

            case 'transfers':
                $tModel = model(StockTransferModel::class);
                $transfers = ($roleId === 1) ? $tModel->listAll() : $tModel->listAllMultiBranch($branchIds);
                $list = "";
                foreach(array_slice($transfers, 0, 10) as $t) {
                    $list .= "- Transfer #{$t['id']} -> Status: {$t['status']}, Logic: From {$t['from_branch']} to {$t['to_branch']}\n";
                }
                $summary .= "History:\n" . ($list ?: "No transfers.") . "\n";
                break;

            case 'branches':
                $bModel = model(BranchModel::class);
                $branches = ($roleId === 1) ? $bModel->getWithManagers() : $bModel->whereIn('id', $branchIds ?: [0])->findAll();
                $list = "";
                foreach($branches as $b) { $list .= "- {$b['name']}, Phone: {$b['phone']}, Active: " . ($b['is_active'] ? 'Yes' : 'No') . "\n"; }
                $summary .= "Branches:\n" . ($list ?: "No branches.") . "\n";
                break;

            default:
                $summary .= "General Stats: Products=".model(ProductModel::class)->countAll().", Branches=".model(BranchModel::class)->countAll();
        }

        return $summary;
    }

    private function buildMainSystemPrompt(int $roleId): string
    {
        $roleName = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales'][$roleId] ?? 'User';
        return "You are an advanced Gemini AI Assistant for a complete Inventory Management System.
Your role: Professional business assistant for $roleName.

## SYSTEM CONTEXT
Modules: Inventory, Orders, Products, Users, Transfers, Branches.

## RESPONSE STYLE (STRICT)
- NEVER return JSON.
- ALWAYS use TABLES for data.
- Use Markdown.
- Add insights/summaries.
- Table Format: | Col | Col | Col |

## STRICT RULES
- Answer ONLY based on provided Data Context.
- If data missing, say: 'No data available for this request'.
- DO NOT explain how you work.";
    }

    private function callGemini(string $query, string $system, string $data): string
    {
        $url = $this->geminiUrl . '?key=' . trim($this->geminiKey);
        $ch = curl_init($url);
        $payload = [
            'contents' => [['parts' => [['text' => "Instructions: $system\n\nData Context:\n$data\n\nQuestion: $query"]]]]
        ];
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) throw new \Exception("Gemini API Error: $res");
        $json = json_decode($res, true);
        return trim($json['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    private function getStaticIntelligentResponse(string $query, string $data, int $roleId): string
    {
        return "### ⚠️ Gemini Note\n\nUnable to connect to Gemini. Live data snapshot:\n\n" . substr($data, 0, 400) . "...";
    }

    public function suggest()
    {
        $user = $this->actor();
        $roleId = (int)($user->role_id ?? 0);
        $suggestions = [
            1 => ['Low stock alert', 'User list summary', 'Branch stats'],
            2 => ['Branch inventory', 'Recent transfers'],
            3 => ['Check price', 'My sales']
        ];
        return $this->ok($suggestions[$roleId] ?? ['Help']);
    }

    public function getRoutes()
    {
        return $this->ok(['query' => '/api/v1/chatbot/query', 'suggest' => '/api/v1/chatbot/suggest']);
    }
}
