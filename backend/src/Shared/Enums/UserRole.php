<?php

namespace App\Shared\Enums;

/**
 * UserRole Enum - Defines available user roles in the system
 */
enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';

    /**
     * Get all available roles as an array
     *
     * @return array<string>
     */
    public static function getAllRoles(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Check if a role value is valid
     *
     * @param string $role
     * @return bool
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::getAllRoles(), true);
    }

    /**
     * Get the default role for new users
     *
     * @return UserRole
     */
    public static function getDefault(): UserRole
    {
        return self::USER;
    }

    /**
     * Check if this role has admin privileges
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if this role is a regular user
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }
}
