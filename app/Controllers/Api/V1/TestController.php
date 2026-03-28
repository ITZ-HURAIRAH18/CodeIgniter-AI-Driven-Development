<?php

namespace App\Controllers\Api\V1;

class TestController extends BaseApiController
{
    /**
     * GET /api/v1/test/db
     * Test database connection
     */
    public function db(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $db = \Config\Database::connect();
            $actor = $this->actor();
            $users = $db->table('users')->countAllResults();
            $admin = $db->table('users')->where('email', 'admin@system.com')->get()->getRow();
            
            $debug = [
                'database_connected' => true,
                'total_users' => $users,
                'admin_user' => $admin ? [
                    'id' => $admin->id,
                    'email' => $admin->email,
                    'name' => $admin->name,
                    'has_password' => !empty($admin->password),
                ] : null,
            ];
            
            // If manager, show their data
            if ((int)($actor->role_id ?? 0) === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);
                
                $productModel = model(\App\Models\ProductModel::class);
                $products = $productModel->where('deleted_at', null)->findAll();
                
                $inventoryModel = model(\App\Models\InventoryModel::class);
                $inventory = $inventoryModel->whereIn('branch_id', $myBranchIds)->findAll();
                
                $orderModel = model(\App\Models\OrderModel::class);
                $orders = $orderModel->findAll();
                
                $debug['manager_debug'] = [
                    'manager_id' => $actor->sub,
                    'managed_branches' => $myBranchIds,
                    'total_products' => count($products),
                    'total_inventory_items' => count($inventory),
                    'total_orders' => count($orders),
                ];
            }
            
            return $this->ok($debug, 'Database test successful');
        } catch (\Exception $e) {
            return $this->apiError('Database error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/test/health
     * Health check endpoint
     */
    public function health(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->ok([
            'status' => 'healthy',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
        ], 'API is healthy');
    }

    /**
     * POST /api/v1/test/login-debug
     * Debug login endpoint
     */
    public function loginDebug(): \CodeIgniter\HTTP\ResponseInterface
    {
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        try {
            $db = \Config\Database::connect();
            $user = $db->table('users')->where('email', $email)->get()->getRow();

            if (!$user) {
                return $this->apiError("User not found: $email", 404);
            }

            $passwordMatch = password_verify($password, $user->password);
            $jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET');

            return $this->ok([
                'user_found' => true,
                'user_email' => $user->email,
                'password_match' => $passwordMatch,
                'jwt_secret_set' => !empty($jwtSecret),
                'jwt_secret_length' => strlen($jwtSecret ?? ''),
            ], 'Debug info');
        } catch (\Exception $e) {
            return $this->apiError('Debug error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * OPTIONS /api/v1/(.*)
     * Handle CORS preflight requests
     */
    public function corsPreFlight(): \CodeIgniter\HTTP\ResponseInterface
    {
        $origin = $this->request->getHeaderLine('Origin') ?: '*';
        
        return response()
            ->setHeader('Access-Control-Allow-Origin',  $origin)
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Access-Control-Max-Age', '86400')
            ->setStatusCode(204);
    }
}
