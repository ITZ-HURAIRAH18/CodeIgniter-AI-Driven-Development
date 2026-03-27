<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

/**
 * BaseApiController
 * Provides consistent JSON response envelope for all API controllers.
 */
class BaseApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * Add CORS headers to all responses
     */
    protected function addCorsHeaders(\CodeIgniter\HTTP\ResponseInterface $response): \CodeIgniter\HTTP\ResponseInterface
    {
        $origin = $this->request->getHeaderLine('Origin') ?: '*';
        
        return $response
            ->setHeader('Access-Control-Allow-Origin',  $origin)
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->setHeader('Access-Control-Allow-Credentials', 'true');
    }

    /** 200 OK with data payload */
    protected function ok(mixed $data, string $message = 'Success'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->addCorsHeaders($this->respond(['success' => true, 'message' => $message, 'data' => $data]));
    }

    /** 201 Created */
    protected function created(mixed $data, string $message = 'Created successfully'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->addCorsHeaders($this->respondCreated(['success' => true, 'message' => $message, 'data' => $data]));
    }

    /** 422 Validation Error */
    protected function validationError(array $errors): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->addCorsHeaders($this->respond([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $errors,
        ], 422));
    }

    /** Generic error response */
    protected function apiError(string $message, int $code = 400): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->addCorsHeaders($this->respond(['success' => false, 'message' => $message], $code));
    }

    /** Get authenticated user payload from JWT */
    protected function actor(): object
    {
        return $this->request->authPayload ?? (object)[];
    }
}
