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
        
        // Check if all origins allowed or origin is in whitelist
        $isAllowed = in_array('*', $allowedOrigins) || ($origin && in_array($origin, $allowedOrigins));
        
        if ($isAllowed) {
            // Handle preflight
            if ($request->getMethod() === 'OPTIONS') {
                $responseOrigin = in_array('*', $allowedOrigins) ? '*' : $origin;
                return response()
                    ->setHeader('Access-Control-Allow-Origin',      $responseOrigin)
                    ->setHeader('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->setHeader('Access-Control-Allow-Headers',     'Content-Type, Authorization, X-Requested-With')
                    ->setHeader('Access-Control-Max-Age',           '86400')
                    ->setStatusCode(204);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = $this->config->default['allowedOrigins'];
        
        // Only set CORS headers if all origins allowed or origin is whitelisted
        $isAllowed = in_array('*', $allowedOrigins) || ($origin && in_array($origin, $allowedOrigins));
        
        if ($isAllowed) {
            $responseOrigin = in_array('*', $allowedOrigins) ? '*' : $origin;
            return $response
                ->setHeader('Access-Control-Allow-Origin',      $responseOrigin)
                ->setHeader('Access-Control-Allow-Headers',     'Content-Type, Authorization, X-Requested-With')
                ->setHeader('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->setHeader('Access-Control-Allow-Credentials',  'false');
        }

        return $response;
    }
}
