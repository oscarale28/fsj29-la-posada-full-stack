<?php

declare(strict_types=1);

namespace App\UserManagement\Repositories;

use App\Shared\Repositories\RepositoryInterface;
use App\UserManagement\Entities\User;

/**
 * User Repository Interface - Defines user-specific repository operations
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by username
     *
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User;

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by username or email
     *
     * @param string $usernameOrEmail
     * @return User|null
     */
    public function findByUsernameOrEmail(string $usernameOrEmail): ?User;

    /**
     * Check if username exists
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool;

    /**
     * Check if email exists
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool;

    /**
     * Get user accommodations
     *
     * @param int $userId
     * @return array
     */
    public function getUserAccommodations(int $userId): array;

    /**
     * Add accommodation to user
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function addUserAccommodation(int $userId, int $accommodationId): bool;

    /**
     * Remove accommodation from user
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function removeUserAccommodation(int $userId, int $accommodationId): bool;

    /**
     * Check if user has accommodation
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function hasUserAccommodation(int $userId, int $accommodationId): bool;
}
