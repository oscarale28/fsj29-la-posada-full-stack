<?php

namespace App\Authentication\Middleware;

use App\Shared\Security\JWTTokenManager;
use App\UserManagement\Repositories\UserRepositoryInterface;

/**
 * Authentication Middleware - Validates requests and extracts user context
 */
class AuthenticationMiddleware
{
    private JWTTokenManager $jwtManager;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        JWTTokenManager $jwtManager,
        UserRepositoryInterface $userRepository
    ) {
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Handle authentication middleware
     */
    public function handle(): ?array
    {
        $token = $this->extractTokenFromRequest();

        if (!$token) {
            return [
                'status' => 401,
                'data' => ['error' => 'Authentication token required']
            ];
        }

        $payload = $this->jwtManager->validateToken($token);

        if (!$payload) {
            return [
                'status' => 401,
                'data' => ['error' => 'Invalid or expired token']
            ];
        }

        // Verify user still exists in database
        $user = $this->userRepository->findById((int) $payload['sub']);
        if (!$user) {
            return [
                'status' => 401,
                'data' => ['error' => 'User not found']
            ];
        }

        // Store user context in global state for controllers to access
        $GLOBALS['authenticated_user'] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
            'token_payload' => $payload
        ];

        // Continue to next middleware/handler
        return null;
    }

    /**
     * Create middleware for specific role requirement
     */
    public static function requireRole(string $requiredRole): callable
    {
        return function () use ($requiredRole) {
            $user = $GLOBALS['authenticated_user'] ?? null;

            if (!$user) {
                return [
                    'status' => 401,
                    'data' => ['error' => 'Authentication required']
                ];
            }

            // Admin has access to all roles
            if ($user['role'] === 'admin') {
                return null;
            }

            if ($user['role'] !== $requiredRole) {
                return [
                    'status' => 403,
                    'data' => ['error' => 'Insufficient permissions']
                ];
            }

            return null;
        };
    }

    /**
     * Create middleware for admin-only access
     */
    public static function requireAdmin(): callable
    {
        return self::requireRole('admin');
    }

    /**
     * Create middleware for authenticated users (any role)
     */
    public static function requireAuth(): callable
    {
        return function () {
            $user = $GLOBALS['authenticated_user'] ?? null;

            if (!$user) {
                return [
                    'status' => 401,
                    'data' => ['error' => 'Authentication required']
                ];
            }

            return null;
        };
    }

    /**
     * Extract JWT token from request headers
     */
    private function extractTokenFromRequest(): ?string
    {
        $headers = getallheaders();

        // Check Authorization header
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Fallback: check for token in query parameter (not recommended for production)
        return $_GET['token'] ?? null;
    }

    /**
     * Get current authenticated user
     */
    public static function getCurrentUser(): ?array
    {
        return $GLOBALS['authenticated_user'] ?? null;
    }

    /**
     * Check if current user has specific role
     */
    public static function currentUserHasRole(string $role): bool
    {
        $user = self::getCurrentUser();

        if (!$user) {
            return false;
        }

        // Admin has access to all roles
        if ($user['role'] === 'admin') {
            return true;
        }

        return $user['role'] === $role;
    }

    /**
     * Check if current user is admin
     */
    public static function currentUserIsAdmin(): bool
    {
        return self::currentUserHasRole('admin');
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId(): ?int
    {
        $user = self::getCurrentUser();
        return $user ? $user['id'] : null;
    }

    /**
     * Validate token without setting global state (for API validation)
     */
    public function validateTokenOnly(string $token): ?array
    {
        $payload = $this->jwtManager->validateToken($token);

        if (!$payload) {
            return null;
        }

        // Verify user still exists in database
        $user = $this->userRepository->findById((int) $payload['sub']);
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
            'token_payload' => $payload
        ];
    }
}
