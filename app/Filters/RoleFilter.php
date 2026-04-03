<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter
 *
 * Checks that the authenticated user's role matches the required role(s).
 * Usage in Routes.php:
 *   $routes->group('', ['filter' => 'role:admin'], function($routes) { ... });
 *   $routes->group('', ['filter' => 'role:admin,branch_manager'], function($routes) { ... });
 *
 * Role IDs: 1=admin, 2=branch_manager, 3=sales_user
 */
class RoleFilter implements FilterInterface
{
    private const ROLE_MAP = [
        'admin'          => 1,
        'branch_manager' => 2,
        'sales_user'     => 3,
    ];

    private function getErrorResponse(string $message, int $code = 401): ResponseInterface
    {
        $origin = request()->getHeaderLine('Origin') ?: '*';
        
        return response()
            ->setJSON([
                'success' => false,
                'message' => $message,
            ])
            ->setStatusCode($code)
            ->setHeader('Access-Control-Allow-Origin', $origin)
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->setHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        // ✅ Allow CORS preflight OPTIONS requests without authentication
        if ($request->getMethod() === 'OPTIONS') {
            return null; // continue (will be handled by CorsFilter)
        }

        // Try to get or set the auth payload from the Authorization header
        $payload = $request->authPayload ?? null;

        if (!$payload) {
            // Try to extract from Authorization header if it wasn't set by JWT filter
            $authHeader = $request->getHeaderLine('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                try {
                    // Attempt to decode the JWT to get the payload
                    $config = \Config\Services::config('App');
                    $jwt = \Config\Services::jwt();
                    $payload = $jwt->decode($token);
                } catch (\Exception $e) {
                    // Token is invalid, continue to error
                }
            }
        }

        if (!$payload) {
            return $this->getErrorResponse('Authentication required.', 401);
        }

        if (empty($arguments)) {
            return null; // No role restriction — any authenticated user passes
        }

        $allowedRoleIds = array_map(
            fn($role) => self::ROLE_MAP[trim($role)] ?? 0,
            $arguments
        );

        if (!in_array((int) $payload->role_id, $allowedRoleIds, true)) {
            return $this->getErrorResponse(
                'Forbidden: You do not have permission to access this resource.',
                403
            );
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
