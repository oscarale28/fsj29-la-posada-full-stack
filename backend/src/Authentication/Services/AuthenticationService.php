<?php

namespace App\Authentication\Services;

use App\Shared\Security\JWTTokenManager;
use App\Shared\Security\PasswordHasher;
use App\UserManagement\Entities\User;
use App\UserManagement\Repositories\UserRepository;
use App\Shared\Enums\UserRole;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authentication Service - Handles user login/registration logic
 */
class AuthenticationService
{
    private JWTTokenManager $jwtManager;
    private PasswordHasher $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(
        JWTTokenManager $jwtManager,
        PasswordHasher $passwordHasher,
        UserRepository $userRepository
    ) {
        $this->jwtManager = $jwtManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate user with email and password
     */
    public function login(string $email, string $password): array
    {
        // Validate input
        if (empty(trim($email)) || empty(trim($password))) {
            throw new InvalidArgumentException('Email and password are required');
        }

        // Find user by email
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        // Verify password
        if (!$this->passwordHasher->verify($password, $user->getPasswordHash())) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->jwtManager->generateToken(
            $user->getId(),
            $user->getEmail(),
            $user->getRole()->value
        );

        return [
            'token' => $token,
            'user' => $user->toArray(false), // Don't include password hash
            'expires_in' => 3600 // 1 hour
        ];
    }

    /**
     * Register a new user
     */
    public function register(string $username, string $email, string $password, ?string $role = null): array
    {
        // Validate input
        $this->validateRegistrationInput($username, $email, $password);

        // Check if user already exists
        if ($this->userRepository->findByEmail($email)) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        if ($this->userRepository->findByUsername($username)) {
            throw new InvalidArgumentException('User with this username already exists');
        }

        // Validate password strength
        $passwordValidation = $this->passwordHasher->validatePasswordStrength($password);
        if (!$passwordValidation['valid']) {
            throw new InvalidArgumentException('Password validation failed: ' . implode(', ', $passwordValidation['errors']));
        }

        // Hash password
        $passwordHash = $this->passwordHasher->hash($password);

        // Determine user role
        $userRole = $role ? UserRole::from($role) : UserRole::getDefault();

        // Create user entity
        $user = User::create($username, $email, $passwordHash, $userRole);

        // Save user to database
        $savedUser = $this->userRepository->save($user);

        // Generate JWT token
        $token = $this->jwtManager->generateToken(
            $savedUser->getId(),
            $savedUser->getEmail(),
            $savedUser->getRole()->value
        );

        return [
            'token' => $token,
            'user' => $savedUser->toArray(false), // Don't include password hash
            'expires_in' => 3600 // 1 hour
        ];
    }

    /**
     * Validate JWT token and return user data
     */
    public function validateToken(string $token): ?array
    {
        $payload = $this->jwtManager->validateToken($token);

        if (!$payload) {
            return null;
        }

        // Verify user still exists and is active
        $user = $this->userRepository->findById((int) $payload['sub']);
        if (!$user) {
            return null;
        }

        return [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'role' => $user->getRole()->value,
            'token_payload' => $payload
        ];
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken(string $token): ?array
    {
        $payload = $this->jwtManager->validateToken($token);

        if (!$payload) {
            return null;
        }

        // Get user from database
        $user = $this->userRepository->findById((int) $payload['sub']);
        if (!$user) {
            return null;
        }

        // Generate new token
        $newToken = $this->jwtManager->generateToken(
            $user->getId(),
            $user->getEmail(),
            $user->getRole()->value
        );

        return [
            'token' => $newToken,
            'user' => $user->toArray(false),
            'expires_in' => 3600
        ];
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        // Get user
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // Verify current password
        if (!$this->passwordHasher->verify($currentPassword, $user->getPasswordHash())) {
            throw new InvalidArgumentException('Current password is incorrect');
        }

        // Validate new password strength
        $passwordValidation = $this->passwordHasher->validatePasswordStrength($newPassword);
        if (!$passwordValidation['valid']) {
            throw new InvalidArgumentException('New password validation failed: ' . implode(', ', $passwordValidation['errors']));
        }

        // Hash new password
        $newPasswordHash = $this->passwordHasher->hash($newPassword);

        // Update user password
        $user->updatePassword($newPasswordHash);

        // Save updated user
        $this->userRepository->save($user);

        return true;
    }

    /**
     * Reset user password (admin function)
     */
    public function resetPassword(int $userId): string
    {
        // Get user
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // Generate secure temporary password
        $temporaryPassword = $this->passwordHasher->generateSecurePassword(12);

        // Hash temporary password
        $passwordHash = $this->passwordHasher->hash($temporaryPassword);

        // Update user password
        $user->updatePassword($passwordHash);

        // Save updated user
        $this->userRepository->save($user);

        return $temporaryPassword;
    }

    /**
     * Validate registration input
     */
    private function validateRegistrationInput(string $username, string $email, string $password): void
    {
        if (empty(trim($username))) {
            throw new InvalidArgumentException('Username is required');
        }

        if (empty(trim($email))) {
            throw new InvalidArgumentException('Email is required');
        }

        if (empty(trim($password))) {
            throw new InvalidArgumentException('Password is required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }

    /**
     * Get user by token
     */
    public function getUserByToken(string $token): ?User
    {
        $userId = $this->jwtManager->getUserIdFromToken($token);

        if (!$userId) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    /**
     * Check if user has specific role
     */
    public function userHasRole(string $token, string $requiredRole): bool
    {
        $userRole = $this->jwtManager->getUserRoleFromToken($token);

        if (!$userRole) {
            return false;
        }

        // Admin has access to all roles
        if ($userRole === UserRole::ADMIN->value) {
            return true;
        }

        return $userRole === $requiredRole;
    }
}
