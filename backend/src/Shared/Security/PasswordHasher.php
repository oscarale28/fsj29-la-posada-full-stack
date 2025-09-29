<?php

namespace App\Shared\Security;

/**
 * Password Hasher - Handles password hashing and verification
 */
class PasswordHasher
{
    private int $cost;

    public function __construct(int $cost = 12)
    {
        $this->cost = $cost;
    }

    /**
     * Hash a password using bcrypt
     */
    public function hash(string $password): string
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->cost]);

        if ($hashedPassword === false) {
            throw new \RuntimeException('Failed to hash password');
        }

        return $hashedPassword;
    }

    /**
     * Verify a password against its hash
     */
    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a hash needs to be rehashed (e.g., cost has changed)
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Generate a secure random password
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    /**
     * Set the cost parameter for hashing
     */
    public function setCost(int $cost): void
    {
        if ($cost < 4 || $cost > 31) {
            throw new \InvalidArgumentException('Cost must be between 4 and 31');
        }
        $this->cost = $cost;
    }

    /**
     * Get the current cost parameter
     */
    public function getCost(): int
    {
        return $this->cost;
    }
}
