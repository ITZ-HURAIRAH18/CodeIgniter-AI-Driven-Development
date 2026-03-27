<?php

namespace App\Controllers\Api\V1;

use App\Services\AuthService;

class AuthController extends BaseApiController
{
    protected $authService = null;

    /**
     * Get or initialize AuthService
     */
    private function getAuthService(): AuthService
    {
        if ($this->authService === null) {
            try {
                $this->authService = new AuthService();
            } catch (\Exception $e) {
                throw new \Exception('Failed to initialize authentication service: ' . $e->getMessage());
            }
        }
        return $this->authService;
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]',
            ];

            if (!$this->validate($rules)) {
                return $this->validationError($this->validator->getErrors());
            }

            try {
                $result = $this->getAuthService()->login(
                    $this->request->getVar('email'),
                    $this->request->getVar('password')
                );
                return $this->ok($result, 'Login successful.');
            } catch (\InvalidArgumentException $e) {
                return $this->apiError($e->getMessage(), 401);
            } catch (\Exception $e) {
                return $this->apiError('Authentication error: ' . $e->getMessage(), 500);
            }
        } catch (\Throwable $e) {
            // Catch any unexpected errors and return with CORS headers
            return $this->apiError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/auth/logout
     * Requires: AuthJWTFilter
     */
    public function logout(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $payload = $this->actor();
            $this->getAuthService()->blacklist($payload->jti, $payload->exp);
            return $this->ok(null, 'Logged out successfully.');
        } catch (\Exception $e) {
            return $this->apiError('Logout error: ' . $e->getMessage(), 500);
        }
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
            $result = $this->getAuthService()->refresh($refreshToken);
            return $this->ok($result, 'Token refreshed successfully.');
        } catch (\RuntimeException $e) {
            return $this->apiError($e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->apiError('Refresh error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/auth/me
     * Returns current authenticated user profile.
     */
    public function me(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $actor = $this->actor();
            $user  = model('UserModel')->find($actor->sub);

            if (!$user) {
                return $this->apiError('User not found.', 404);
            }

            return $this->ok($user);
        } catch (\Exception $e) {
            return $this->apiError('Error fetching user profile: ' . $e->getMessage(), 500);
        }
    }
}
