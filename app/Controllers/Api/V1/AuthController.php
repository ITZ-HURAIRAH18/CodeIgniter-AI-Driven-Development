<?php

namespace App\Controllers\Api\V1;

use App\Services\AuthService;

class AuthController extends BaseApiController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        try {
            $result = $this->authService->login(
                $this->request->getVar('email'),
                $this->request->getVar('password')
            );
            return $this->ok($result, 'Login successful.');
        } catch (\InvalidArgumentException $e) {
            return $this->apiError($e->getMessage(), 401);
        }
    }

    /**
     * POST /api/v1/auth/logout
     * Requires: AuthJWTFilter
     */
    public function logout(): \CodeIgniter\HTTP\ResponseInterface
    {
        $payload = $this->actor();
        $this->authService->blacklist($payload->jti, $payload->exp);
        return $this->ok(null, 'Logged out successfully.');
    }

    /**
     * POST /api/v1/auth/refresh
     */
    public function refresh(): \CodeIgniter\HTTP\ResponseInterface
    {
        $refreshToken = $this->request->getVar('refresh_token');

        if (!$refreshToken) {
            return $this->apiError('refresh_token is required.', 400);
        }

        try {
            $result = $this->authService->refresh($refreshToken);
            return $this->ok($result, 'Token refreshed.');
        } catch (\RuntimeException $e) {
            return $this->apiError($e->getMessage(), 401);
        }
    }

    /**
     * GET /api/v1/auth/me
     * Returns current authenticated user profile.
     */
    public function me(): \CodeIgniter\HTTP\ResponseInterface
    {
        $actor = $this->actor();
        $user  = model('UserModel')->find($actor->sub);

        if (!$user) {
            return $this->apiError('User not found.', 404);
        }

        return $this->ok($user);
    }
}
