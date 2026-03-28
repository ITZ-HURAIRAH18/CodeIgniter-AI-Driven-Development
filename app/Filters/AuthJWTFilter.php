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

        $header = $request->getHeaderLine('Authorization');

        if (!str_starts_with($header, 'Bearer ')) {
            return $this->getErrorResponse('Authorization token required.', 401);
        }

        $token = substr($header, 7);

        try {
            $authService = new AuthService();
            $payload = $authService->validateAccessToken($token);
            
            // Store payload as custom property on request
            $request->authPayload = $payload;
        } catch (\Exception $e) {
            return $this->getErrorResponse('Unauthorized: ' . $e->getMessage(), 401);
        } catch (\Throwable $e) {
            return $this->getErrorResponse('Authentication error: ' . $e->getMessage(), 500);
        }

        return null; // continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
