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

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $payload = $request->getGlobal('auth_payload');

        if (!$payload) {
            return response()->setJSON([
                'success' => false,
                'message' => 'Authentication required.',
            ])->setStatusCode(401);
        }

        if (empty($arguments)) {
            return null; // No role restriction — any authenticated user passes
        }

        $allowedRoleIds = array_map(
            fn($role) => self::ROLE_MAP[trim($role)] ?? 0,
            $arguments
        );

        if (!in_array((int) $payload->role_id, $allowedRoleIds, true)) {
            return response()->setJSON([
                'success'  => false,
                'message'  => 'Forbidden: You do not have permission to access this resource.',
                'required' => implode(' or ', $arguments),
            ])->setStatusCode(403);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
