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


    protected function addCorsHeaders(\CodeIgniter\HTTP\ResponseInterface $response): \CodeIgniter\HTTP\ResponseInterface
    {
        $origin = $this->request->getHeaderLine('Origin') ?: '*';
        
        return $response
            ->setHeader('Access-Control-Allow-Origin',  $origin)
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Access-Control-Max-Age', '86400');
    }

    protected function ok(mixed $data, string $message = 'Success'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond(['success' => true, 'message' => $message, 'data' => $data]);
    }

    protected function created(mixed $data, string $message = 'Created successfully'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->addCorsHeaders($this->respondCreated(['success' => true, 'message' => $message, 'data' => $data]));
    }

    protected function validationError(array $errors): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->addCorsHeaders($this->respond([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $errors,
        ], 422));
    }

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
