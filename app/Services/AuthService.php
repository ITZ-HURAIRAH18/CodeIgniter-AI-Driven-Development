<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\TokenBlacklistModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

/**
 * AuthService
 * Handles JWT issuance, validation, refresh, and logout blacklisting.
 */
class AuthService
{
    private string $secret;
    private int    $accessTtl;   // seconds
    private int    $refreshTtl;  // seconds
    private string $algo = 'HS256';

    public function __construct()
    {
        // Try multiple ways to get JWT_SECRET (order is important)
        $this->secret = $_ENV['JWT_SECRET'] 
                     ?? $_SERVER['JWT_SECRET']
                     ?? getenv('JWT_SECRET');
        
        $this->accessTtl  = (int) ($_ENV['JWT_ACCESS_TTL'] ?? $_SERVER['JWT_ACCESS_TTL'] ?? getenv('JWT_ACCESS_TTL') ?? 3600);
        $this->refreshTtl = (int) ($_ENV['JWT_REFRESH_TTL'] ?? $_SERVER['JWT_REFRESH_TTL'] ?? getenv('JWT_REFRESH_TTL') ?? 604800);

        if (empty($this->secret)) {
            // IMPORTANT: Use the same default as fallback
            $this->secret = 'your-super-secret-jwt-key-change-this-in-production-min32chars';
            log_message('warning', 'JWT_SECRET not loaded from environment, using hardcoded default. ' .
                                   'Ensure .env file exists and CI_ENVIRONMENT=development is set.');
        } else {
            log_message('debug', 'JWT_SECRET loaded from environment. TTLs: access=' . $this->accessTtl . 
                                 's, refresh=' . $this->refreshTtl . 's');
        }
    }

    /**
     * Attempt login. Returns token pair or throws on failure.
     *
     * @throws \InvalidArgumentException
     */
    public function login(string $email, string $password): array
    {
        $model = model(UserModel::class);
        $user  = $model->findByEmail($email);

        if (!$user || !password_verify($password, $user->password)) {
            throw new \InvalidArgumentException('Invalid email or password.');
        }

        $model->touchLastLogin($user->id);

        return [
            'access_token'  => $this->issueToken($user, 'access',  $this->accessTtl),
            'refresh_token' => $this->issueToken($user, 'refresh', $this->refreshTtl),
            'expires_in'    => $this->accessTtl,
            'user'          => [
                'id'        => (int) $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => (int) $user->role_id,
                'branch_id' => $user->branch_id ? (int) $user->branch_id : null,
            ],
        ];
    }

    /**
     * Validate an access token and return its payload.
     *
     * @throws \RuntimeException on invalid/expired/blacklisted token
     */
    public function validateAccessToken(string $token): object
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (\Exception $e) {
            throw new \RuntimeException('Token invalid: ' . $e->getMessage());
        }

        if (($payload->type ?? '') !== 'access') {
            throw new \RuntimeException('Token type mismatch.');
        }

        // Check blacklist
        $blacklist = $this->db()->table('token_blacklist')
                               ->where('token_jti', $payload->jti)
                               ->get()
                               ->getRow();
        if ($blacklist) {
            throw new \RuntimeException('Token has been revoked.');
        }

        return $payload;
    }

    /**
     * Issue a new access token from a valid refresh token.
     */
    public function refresh(string $refreshToken): array
    {
        if (empty($refreshToken)) {
            log_message('error', 'Refresh attempted with empty refresh token');
            throw new \RuntimeException('Refresh token is empty.');
        }

        try {
            $payload = JWT::decode($refreshToken, new Key($this->secret, $this->algo));
        } catch (\Exception $e) {
            log_message('error', 'JWT decode failed during refresh: ' . $e->getMessage());
            throw new \RuntimeException('Refresh token invalid: ' . $e->getMessage());
        }

        if (($payload->type ?? '') !== 'refresh') {
            log_message('error', 'Token type mismatch during refresh. Expected "refresh", got "' . ($payload->type ?? 'null') . '"');
            throw new \RuntimeException('Not a refresh token.');
        }

        $user = model(UserModel::class)->find($payload->sub);
        if (!$user || !$user->is_active) {
            log_message('error', 'User not found or inactive during refresh. User ID: ' . ($payload->sub ?? 'null'));
            throw new \RuntimeException('User not found or inactive.');
        }

        log_message('debug', 'Token refreshed successfully for user: ' . $user->id);
        return [
            'access_token' => $this->issueToken((object) $user, 'access', $this->accessTtl),
            'expires_in'   => $this->accessTtl,
        ];
    }

    /**
     * Blacklist a token JTI to invalidate it (logout).
     */
    public function blacklist(string $jti, int $expiresAt): void
    {
        $this->db()->table('token_blacklist')->insert([
            'token_jti'  => $jti,
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // -------------------------------------------------------------------------

    private function issueToken(object $user, string $type, int $ttl): string
    {
        $now = time();
        $payload = [
            'iss'       => base_url(),
            'sub'       => $user->id,
            'jti'       => bin2hex(random_bytes(16)),
            'iat'       => $now,
            'nbf'       => $now,
            'exp'       => $now + $ttl,
            'type'      => $type,
            'role_id'   => $user->role_id,
            'branch_id' => $user->branch_id,
        ];
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    private function db(): \CodeIgniter\Database\ConnectionInterface
    {
        return \Config\Database::connect();
    }
}
