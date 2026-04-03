<?php

namespace App\Controllers\Api\V1;

use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\BranchModel;
use App\Models\OrderModel;

class ChatbotController extends BaseApiController
{
    private $openRouterKey;
    private $geminiKey;
    private $openRouterUrl = 'https://openrouter.ai/api/v1/chat/completions';
    private $geminiUrl     = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private $aiModel       = 'meta-llama/llama-3-8b-instruct:free';

    public function __construct()
    {
        $this->openRouterKey = getenv('CHATBOT_LLM_API_KEY') ?: $_ENV['CHATBOT_LLM_API_KEY'] ?? null;
        $this->geminiKey     = getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? null;
    }

    /**
     * Handle CORS preflight requests
     */
    public function options()
    {
        return $this->addCorsHeaders($this->response->setStatusCode(204));
    }

    public function query()
    {
        $query = $this->request->getVar('query');
        if (empty($query)) return $this->apiError('Query is required', 400);

        $user = $this->actor();
        $roleId = (int)($user->role_id ?? 0);
        $userId = (int)($user->sub ?? 0);

        $systemPrompt = $this->buildSystemPrompt($roleId, $userId);
        $liveDataSummary = $this->getLiveDataSummary($roleId, $userId, $query);

        try {
            // Try OpenRouter first if API key is available
            $responseText = '';
            $lastError = null;

            if ($this->openRouterKey) {
                try {
                    $responseText = $this->callOpenRouter($query, $systemPrompt, $liveDataSummary);
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    // Fall through to Gemini
                }
            }

            // If OpenRouter failed or not configured, try Gemini
            if (empty($responseText) && $this->geminiKey) {
                try {
                    $responseText = $this->callGemini($query, $systemPrompt, $liveDataSummary);
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    // Fall through to fallback
                }
            }

            // If all AI APIs failed, use intelligent fallback
            if (empty($responseText)) {
                $responseText = $this->getIntelligentFallback($roleId, $userId, $query, $liveDataSummary);
            }

            return $this->respond([
                'success'  => true,
                'message'  => 'Query processed successfully',
                'data'     => ['response' => $responseText]
            ]);
        } catch (\Exception $e) {
            // Final fallback on any error
            $responseText = $this->getIntelligentFallback($roleId, $userId, $query, $liveDataSummary);
            
            return $this->respond([
                'success'  => true,
                'message'  => 'Query processed with fallback',
                'data'     => ['response' => $responseText]
            ]);
        }
    }

    public function suggest()
    {
        $user = $this->actor();
        $roleId = (int)($user->role_id ?? 0);
        $suggestions = [
            1 => ['Show users', 'System stats', 'Branch list'],
            2 => ['Branch inventory', 'Recent orders', 'Stock level summary'],
            3 => ['My orders', 'Search Orange', 'Help']
        ];
        return $this->ok($suggestions[$roleId] ?? ['Help']);
    }

    public function getRoutes()
    {
        return $this->ok([
            'query' => '/api/v1/chatbot/query',
            'suggest' => '/api/v1/chatbot/suggest'
        ], 'Chatbot routes');
    }

    private function buildSystemPrompt(int $roleId, int $userId): string
    {
        $roles = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales'];
        $roleTitle = $roles[$roleId] ?? 'User';
        return "You are a professional Inventory AI Agent for the role: $roleTitle. 
        Rules: 
        1. Answer ONLY using provided LIVE DATA. 
        2. If data missing, say 'I cannot access that data'. 
        3. Use Markdown. 
        4. Be concise.";
    }

    private function getLiveDataSummary(int $roleId, int $userId, string $query): string
    {
        $summary = "LIVE DATA:\n";
        $query = strtolower($query);
        
        // Admin queries about users
        if ($roleId === 1 && strpos($query, 'user') !== false) {
            $users = model(UserModel::class)->select('name, email, role_id')->limit(5)->findAll();
            $summary .= "Users (first 5): " . json_encode($users) . "\n";
            $summary .= "Total Users: " . model(UserModel::class)->countAll() . "\n";
        }
        
        // Branch-related queries
        if (strpos($query, 'branch') !== false) {
            $branches = model(BranchModel::class)->limit(10)->findAll();
            $summary .= "Branches: " . count($branches) . "\n";
        }
        
        // Product/Inventory queries
        if (strpos($query, 'product') !== false || strpos($query, 'inventory') !== false) {
            $products = model(ProductModel::class)->countAll();
            $summary .= "Total Products: " . $products . "\n";
        }
        
        // Order queries
        if (strpos($query, 'order') !== false) {
            $orders = model(OrderModel::class)->countAll();
            $summary .= "Total Orders: " . $orders . "\n";
        }
        
        return $summary;
    }

    /**
     * Intelligent fallback response when AI APIs are unavailable
     */
    private function getIntelligentFallback(int $roleId, int $userId, string $query, string $liveData): string
    {
        $query = strtolower(trim($query));
        $roles = [1 => 'Admin', 2 => 'Manager', 3 => 'Sales Representative'];
        $roleTitle = $roles[$roleId] ?? 'User';
        
        // Parse query intent and provide structured response
        if (strpos($query, 'user') !== false && $roleId === 1) {
            $users = model(UserModel::class)->select('name, email, role_id')->limit(5)->findAll();
            $userList = implode(', ', array_column($users, 'name'));
            return "## Users List\n\n**Total Users:** " . model(UserModel::class)->countAll() . "\n\n**First 5 Users:**\n- " . str_replace(',', "\n- ", $userList) . "\n\n*Note: As an Admin, you have full access to all user data.*";
        }
        
        if (strpos($query, 'branch') !== false) {
            $branches = model(BranchModel::class)->limit(10)->findAll();
            if (empty($branches)) {
                return "## Branches\n\nNo branches found in the system.\n\nWould you like to create a new branch?";
            }
            $branchList = implode("\n- ", array_column($branches, 'name'));
            return "## Branches\n\n**Total Branches:** " . count($branches) . "\n\n- " . $branchList;
        }
        
        if (strpos($query, 'product') !== false || strpos($query, 'inventory') !== false) {
            $products = model(ProductModel::class)->limit(10)->findAll();
            $count = model(ProductModel::class)->countAll();
            if (empty($products)) {
                return "## Products\n\nNo products found in the system.\n\nWould you like to add new products?";
            }
            $productList = implode("\n- ", array_column($products, 'name'));
            return "## Products\n\n**Total Products:** {$count}\n\n**Sample Products:**\n- " . $productList;
        }
        
        if (strpos($query, 'order') !== false) {
            $orders = model(OrderModel::class)->limit(10)->findAll();
            $count = model(OrderModel::class)->countAll();
            if (empty($orders)) {
                return "## Orders\n\nNo orders found in the system.\n\nWould you like to create a new order?";
            }
            return "## Orders\n\n**Total Orders:** {$count}\n\n**Recent Orders:** Found " . count($orders) . " orders in the system.\n\nYou can view all orders from the Orders page.";
        }
        
        if (strpos($query, 'stat') !== false || strpos($query, 'dashboard') !== false) {
            $stats = [
                'Users' => model(UserModel::class)->countAll(),
                'Branches' => model(BranchModel::class)->countAll(),
                'Products' => model(ProductModel::class)->countAll(),
                'Orders' => model(OrderModel::class)->countAll()
            ];
            $statsText = implode("\n- ", array_map(fn($k, $v) => "$k: $v", array_keys($stats), $stats));
            return "## System Statistics\n\n- " . $statsText;
        }
        
        // Default helpful response
        return "Hello! I'm your Inventory AI Assistant for **{$roleTitle}** role.\n\nI can help you with:\n- **Users** (Admin only)\n- **Branches** management\n- **Products** and **Inventory** tracking\n- **Orders** processing\n- **Statistics** and reports\n\nWhat would you like to know?";
    }

    private function callOpenRouter($query, $systemPrompt, $liveData)
    {
        $ch = curl_init($this->openRouterUrl);
        $payload = [
            'model' => $this->aiModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt . "\n\nContext:\n" . $liveData],
                ['role' => 'user', 'content' => $query]
            ]
        ];
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . trim($this->openRouterKey)
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50); // 50 seconds timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 seconds connection timeout
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) throw new \Exception("cURL Error: $error");
        if ($code !== 200) throw new \Exception("Code $code: $res");
        $json = json_decode($res, true);
        $text = $json['choices'][0]['message']['content'] ?? '';
        return trim($text);
    }

    private function callGemini($query, $systemPrompt, $liveData)
    {
        // Fixed URL with key in query for v1beta compatibility
        $url = $this->geminiUrl . '?key=' . trim($this->geminiKey);
        $ch = curl_init($url);
        $payload = [
            'contents' => [
                ['parts' => [['text' => "System: $systemPrompt\nContext: $liveData\nUser: $query"]]]
            ]
        ];
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50); // 50 seconds timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 seconds connection timeout
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) throw new \Exception("cURL Error: $error");
        if ($code !== 200) throw new \Exception("Code $code: $res");
        $json = json_decode($res, true);
        // Robust candidate parsing
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return trim($text);
    }
}
