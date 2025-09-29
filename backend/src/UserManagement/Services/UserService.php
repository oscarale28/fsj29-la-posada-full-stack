<?php

declare(strict_types=1);

namespace App\UserManagement\Services;

use App\UserManagement\Entities\User;
use App\UserManagement\Repositories\UserRepositoryInterface;
use App\AccommodationManagement\Repositories\AccommodationRepositoryInterface;
use App\Shared\Security\PasswordHasher;
use App\Shared\Enums\UserRole;
use InvalidArgumentException;
use RuntimeException;

/**
 * User Service - Business logic for user operations
 */
class UserService
{
    private UserRepositoryInterface $userRepository;
    private AccommodationRepositoryInterface $accommodationRepository;
    private PasswordHasher $passwordHasher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AccommodationRepositoryInterface $accommodationRepository,
        PasswordHasher $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->accommodationRepository = $accommodationRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Register a new user
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @param UserRole|null $role
     * @return User
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function registerUser(string $username, string $email, string $password, ?UserRole $role = null): User
    {
        // Validate input
        $this->validateRegistrationData($username, $email, $password);

        // Check if username already exists
        if ($this->userRepository->usernameExists($username)) {
            throw new InvalidArgumentException('Username already exists');
        }

        // Check if email already exists
        if ($this->userRepository->emailExists($email)) {
            throw new InvalidArgumentException('Email already exists');
        }

        // Hash password
        $passwordHash = $this->passwordHasher->hash($password);

        // Create user entity
        $user = User::create($username, $email, $passwordHash, $role);

        // Save user
        $savedUser = $this->userRepository->save($user);
        if (!$savedUser) {
            throw new RuntimeException('Failed to create user');
        }

        return $savedUser;
    }

    /**
     * Find user by ID
     *
     * @param int $userId
     * @return User|null
     */
    public function findUserById(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * Find user by username
     *
     * @param string $username
     * @return User|null
     */
    public function findUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Find user by username or email
     *
     * @param string $usernameOrEmail
     * @return User|null
     */
    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        return $this->userRepository->findByUsernameOrEmail($usernameOrEmail);
    }

    /**
     * Update user information
     *
     * @param int $userId
     * @param string|null $username
     * @param string|null $email
     * @return User
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function updateUser(int $userId, ?string $username = null, ?string $email = null): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // Check if new username already exists (excluding current user)
        if ($username !== null && $this->userRepository->usernameExists($username, $userId)) {
            throw new InvalidArgumentException('Username already exists');
        }

        // Check if new email already exists (excluding current user)
        if ($email !== null && $this->userRepository->emailExists($email, $userId)) {
            throw new InvalidArgumentException('Email already exists');
        }

        // Update user entity
        $user->updateInfo($username, $email);

        // Save updated user
        $updatedUser = $this->userRepository->save($user);
        if (!$updatedUser) {
            throw new RuntimeException('Failed to update user');
        }

        return $updatedUser;
    }

    /**
     * Change user password
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return User
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // Verify current password
        if (!$this->passwordHasher->verify($currentPassword, $user->getPasswordHash())) {
            throw new InvalidArgumentException('Current password is incorrect');
        }

        // Validate new password
        $this->validatePassword($newPassword);

        // Hash new password
        $newPasswordHash = $this->passwordHasher->hash($newPassword);

        // Update user password
        $user->updatePassword($newPasswordHash);

        // Save updated user
        $updatedUser = $this->userRepository->save($user);
        if (!$updatedUser) {
            throw new RuntimeException('Failed to update password');
        }

        return $updatedUser;
    }

    /**
     * Get user accommodations
     *
     * @param int $userId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getUserAccommodations(int $userId): array
    {
        if (!$this->userRepository->exists($userId)) {
            throw new InvalidArgumentException('User not found');
        }

        return $this->userRepository->getUserAccommodations($userId);
    }

    /**
     * Add accommodation to user account
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function addAccommodationToUser(int $userId, int $accommodationId): bool
    {
        // Validate user exists
        if (!$this->userRepository->exists($userId)) {
            throw new InvalidArgumentException('User not found');
        }

        // Validate accommodation exists
        if (!$this->accommodationRepository->exists($accommodationId)) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        // Check if user already has this accommodation
        if ($this->userRepository->hasUserAccommodation($userId, $accommodationId)) {
            throw new InvalidArgumentException('User already has this accommodation');
        }

        // Add accommodation to user
        $result = $this->userRepository->addUserAccommodation($userId, $accommodationId);
        if (!$result) {
            throw new RuntimeException('Failed to add accommodation to user');
        }

        return true;
    }

    /**
     * Remove accommodation from user account
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function removeAccommodationFromUser(int $userId, int $accommodationId): bool
    {
        // Validate user exists
        if (!$this->userRepository->exists($userId)) {
            throw new InvalidArgumentException('User not found');
        }

        // Validate accommodation exists
        if (!$this->accommodationRepository->exists($accommodationId)) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        // Check if user has this accommodation
        if (!$this->userRepository->hasUserAccommodation($userId, $accommodationId)) {
            throw new InvalidArgumentException('User does not have this accommodation');
        }

        // Remove accommodation from user
        $result = $this->userRepository->removeUserAccommodation($userId, $accommodationId);
        if (!$result) {
            throw new RuntimeException('Failed to remove accommodation from user');
        }

        return true;
    }

    /**
     * Check if user has specific accommodation
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     * @throws InvalidArgumentException
     */
    public function userHasAccommodation(int $userId, int $accommodationId): bool
    {
        if (!$this->userRepository->exists($userId)) {
            throw new InvalidArgumentException('User not found');
        }

        if (!$this->accommodationRepository->exists($accommodationId)) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        return $this->userRepository->hasUserAccommodation($userId, $accommodationId);
    }

    /**
     * Get all users (admin only)
     *
     * @return array
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Delete user
     *
     * @param int $userId
     * @return bool
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function deleteUser(int $userId): bool
    {
        if (!$this->userRepository->exists($userId)) {
            throw new InvalidArgumentException('User not found');
        }

        $result = $this->userRepository->delete($userId);
        if (!$result) {
            throw new RuntimeException('Failed to delete user');
        }

        return true;
    }

    /**
     * Validate registration data
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @throws InvalidArgumentException
     */
    private function validateRegistrationData(string $username, string $email, string $password): void
    {
        if (empty(trim($username))) {
            throw new InvalidArgumentException('Username is required');
        }

        if (empty(trim($email))) {
            throw new InvalidArgumentException('Email is required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        $this->validatePassword($password);
    }

    /**
     * Validate password
     *
     * @param string $password
     * @throws InvalidArgumentException
     */
    private function validatePassword(string $password): void
    {
        if (empty($password)) {
            throw new InvalidArgumentException('Password is required');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        if (strlen($password) > 255) {
            throw new InvalidArgumentException('Password cannot exceed 255 characters');
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        // Check for at least one digit
        if (!preg_match('/\d/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one digit');
        }
    }
}
