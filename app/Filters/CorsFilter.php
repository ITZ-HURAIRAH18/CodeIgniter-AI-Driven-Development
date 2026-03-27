<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CorsFilter
 * Handles CORS headers for Vue.js frontend cross-origin requests.
 */
class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $origin = $request->getHeaderLine('Origin') ?: '*';

        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            return response()
                ->setHeader('Access-Control-Allow-Origin',  $origin)
                ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->setHeader('Access-Control-Max-Age',       '86400')
                ->setStatusCode(204);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        $origin = $request->getHeaderLine('Origin') ?: '*';

        return $response
            ->setHeader('Access-Control-Allow-Origin',  $origin)
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }
}
