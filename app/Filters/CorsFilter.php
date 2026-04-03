<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Cors as CorsConfig;

/**
 * CorsFilter
 * Handles CORS headers for Vue.js frontend cross-origin requests.
 */
class CorsFilter implements FilterInterface
{
    protected $config;

    public function __construct()
    {
        $this->config = new CorsConfig();
    }

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = $this->config->default['allowedOrigins'];
        
        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            $isAllowed = in_array('*', $allowedOrigins) || ($origin && in_array($origin, $allowedOrigins));
            
            if ($isAllowed) {
                // If '*' is allowed, we echo the actual origin to be safer (some browsers prefer it)
                $responseOrigin = ($origin && in_array('*', $allowedOrigins)) ? $origin : (in_array('*', $allowedOrigins) ? '*' : $origin);
                
                return response()
                    ->setHeader('Access-Control-Allow-Origin',      $responseOrigin ?: '*')
                    ->setHeader('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->setHeader('Access-Control-Allow-Headers',     'Content-Type, Authorization, X-Requested-With, Accept')
                    ->setHeader('Access-Control-Allow-Credentials',  'true')
                    ->setHeader('Access-Control-Max-Age',           '86400')
                    ->setStatusCode(204);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        $origin = $request->getHeaderLine('Origin') ?: '*';
        $allowedOrigins = $this->config->default['allowedOrigins'];
        
        $isAllowed = in_array('*', $allowedOrigins) || ($origin && in_array($origin, $allowedOrigins));
        
        if ($isAllowed) {
            $responseOrigin = ($origin !== '*' && in_array('*', $allowedOrigins)) ? $origin : $origin;

            $response
                ->setHeader('Access-Control-Allow-Origin',      $responseOrigin)
                ->setHeader('Access-Control-Allow-Headers',     'Content-Type, Authorization, X-Requested-With, Accept')
                ->setHeader('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                ->setHeader('Access-Control-Allow-Credentials',  'true')
                ->setHeader('Access-Control-Max-Age',           '86400');
        }

        return $response;
    }
}
