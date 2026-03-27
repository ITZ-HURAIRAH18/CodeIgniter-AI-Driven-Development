<?php

namespace App\Exceptions;

use CodeIgniter\Debug\ExceptionHandler;
use Throwable;

/**
 * ApiExceptionHandler
 * Custom exception handler that ensures CORS headers are always included.
 */
class ApiExceptionHandler extends ExceptionHandler
{
    /**
     * Handle exceptions and add CORS headers to all responses
     */
    public function handle(Throwable $exception): void
    {
        // Get the response from parent handler
        parent::handle($exception);
        
        // Add CORS headers to the response
        $this->addCorsHeaders();
    }

    /**
     * Add CORS headers to the response
     */
    private function addCorsHeaders(): void
    {
        try {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
        } catch (\Exception $e) {
            // Silently fail if headers already sent
        }
    }
}
