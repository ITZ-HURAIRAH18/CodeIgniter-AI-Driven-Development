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

    /** 200 OK with data payload */
    protected function ok(mixed $data, string $message = 'Success'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond(['success' => true, 'message' => $message, 'data' => $data]);
    }

    /** 201 Created */
    protected function created(mixed $data, string $message = 'Created successfully'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respondCreated(['success' => true, 'message' => $message, 'data' => $data]);
    }

    /** 422 Validation Error */
    protected function validationError(array $errors): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $errors,
        ], 422);
    }

    /** Generic error response */
    protected function apiError(string $message, int $code = 400): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond(['success' => false, 'message' => $message], $code);
    }

    /** Get authenticated user payload from JWT */
    protected function actor(): object
    {
        return $this->request->getGlobal('auth_payload');
    }
}
