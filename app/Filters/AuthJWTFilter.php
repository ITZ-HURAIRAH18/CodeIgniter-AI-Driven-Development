<?php

namespace App\Filters;

use App\Services\AuthService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthJWTFilter
 *
 * Validates the Bearer JWT on every protected route.
 * Sets 'auth_payload' in the request's userData for downstream use.
 */
class AuthJWTFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $header = $request->getHeaderLine('Authorization');

        if (!str_starts_with($header, 'Bearer ')) {
            return response()->setJSON([
                'success' => false,
                'message' => 'Authorization token required.',
            ])->setStatusCode(401);
        }

        $token = substr($header, 7);

        try {
            $payload = (new AuthService())->validateAccessToken($token);
            // Store payload in request globals for controllers
            $request->setGlobal('auth_payload', $payload);
        } catch (\Throwable $e) {
            return response()->setJSON([
                'success' => false,
                'message' => 'Unauthorized: ' . $e->getMessage(),
            ])->setStatusCode(401);
        }

        return null; // continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
