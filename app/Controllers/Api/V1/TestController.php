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
            $users = $db->table('users')->countAllResults();
            $admin = $db->table('users')->where('email', 'admin@system.com')->get()->getRow();
            
            return $this->ok([
                'database_connected' => true,
                'total_users' => $users,
                'admin_user' => $admin ? [
                    'id' => $admin->id,
                    'email' => $admin->email,
                    'name' => $admin->name,
                    'has_password' => !empty($admin->password),
                ] : null,
            ], 'Database test successful');
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
