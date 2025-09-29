<?php

namespace App\Shared\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

/**
 * JWT Token Manager - Manages JWT token creation and validation (Singleton pattern)
 */
class JWTTokenManager
{
    private static ?JWTTokenManager $instance = null;
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $expirationTime = 3600; // 1 hour in seconds

    private function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default-secret-key-change-in-production';
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): JWTTokenManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate JWT token for user
     */
    public function generateToken(int $userId, string $email, string $role): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expirationTime;

        $payload = [
            'iss' => 'accommodation-management-system',
            'aud' => 'accommodation-management-system',
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $userId,
            'email' => $email,
            'role' => $role
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Validate and decode JWT token
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            return null; // Token expired
        } catch (SignatureInvalidException $e) {
            return null; // Invalid signature
        } catch (BeforeValidException $e) {
            return null; // Token not yet valid
        } catch (\Exception $e) {
            return null; // Other validation errors
        }
    }

    /**
     * Extract user ID from token
     */
    public function getUserIdFromToken(string $token): ?int
    {
        $payload = $this->validateToken($token);
        return $payload ? (int) $payload['sub'] : null;
    }

    /**
     * Extract user role from token
     */
    public function getUserRoleFromToken(string $token): ?string
    {
        $payload = $this->validateToken($token);
        return $payload['role'] ?? null;
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return false;
        } catch (ExpiredException $e) {
            return true;
        } catch (\Exception $e) {
            return true; // Consider invalid tokens as expired
        }
    }

    /**
     * Set custom expiration time
     */
    public function setExpirationTime(int $seconds): void
    {
        $this->expirationTime = $seconds;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
