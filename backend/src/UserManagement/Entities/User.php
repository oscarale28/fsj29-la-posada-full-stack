<?php

namespace App\UserManagement\Entities;

use App\Shared\Enums\UserRole;
use DateTime;
use InvalidArgumentException;

/**
 * User Entity - Domain model representing user data
 */
class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private UserRole $role;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    /**
     * Constructor
     *
     * @param int $id
     * @param string $username
     * @param string $email
     * @param string $passwordHash
     * @param UserRole $role
     * @param DateTime $createdAt
     * @param DateTime $updatedAt
     */
    public function __construct(
        int $id,
        string $username,
        string $email,
        string $passwordHash,
        UserRole $role,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->validateUsername($username);
        $this->validateEmail($email);
        $this->validatePasswordHash($passwordHash);

        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create a new User instance for registration
     *
     * @param string $username
     * @param string $email
     * @param string $passwordHash
     * @param UserRole|null $role
     * @return User
     */
    public static function create(
        string $username,
        string $email,
        string $passwordHash,
        ?UserRole $role = null
    ): User {
        $now = new DateTime();
        $role ??= UserRole::getDefault();

        return new self(
            0, // ID will be set by database
            $username,
            $email,
            $passwordHash,
            $role,
            $now,
            $now
        );
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Business logic methods
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function isUser(): bool
    {
        return $this->role->isUser();
    }

    /**
     * Update user information
     *
     * @param string|null $username
     * @param string|null $email
     * @return void
     */
    public function updateInfo(?string $username = null, ?string $email = null): void
    {
        if ($username !== null) {
            $this->validateUsername($username);
            $this->username = $username;
        }

        if ($email !== null) {
            $this->validateEmail($email);
            $this->email = $email;
        }

        $this->updatedAt = new DateTime();
    }

    /**
     * Update user password
     *
     * @param string $passwordHash
     * @return void
     */
    public function updatePassword(string $passwordHash): void
    {
        $this->validatePasswordHash($passwordHash);
        $this->passwordHash = $passwordHash;
        $this->updatedAt = new DateTime();
    }

    /**
     * Change user role
     *
     * @param UserRole $role
     * @return void
     */
    public function changeRole(UserRole $role): void
    {
        $this->role = $role;
        $this->updatedAt = new DateTime();
    }

    // Validation methods
    private function validateUsername(string $username): void
    {
        if (empty(trim($username))) {
            throw new InvalidArgumentException('Username cannot be empty');
        }

        if (strlen($username) < 3) {
            throw new InvalidArgumentException('Username must be at least 3 characters long');
        }

        if (strlen($username) > 50) {
            throw new InvalidArgumentException('Username cannot exceed 50 characters');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            throw new InvalidArgumentException('Username can only contain letters, numbers, underscores, and hyphens');
        }
    }

    private function validateEmail(string $email): void
    {
        if (empty(trim($email))) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        if (strlen($email) > 100) {
            throw new InvalidArgumentException('Email cannot exceed 100 characters');
        }
    }

    private function validatePasswordHash(string $passwordHash): void
    {
        if (empty(trim($passwordHash))) {
            throw new InvalidArgumentException('Password hash cannot be empty');
        }

        if (strlen($passwordHash) < 60) {
            throw new InvalidArgumentException('Password hash appears to be invalid (too short)');
        }
    }

    /**
     * Convert entity to array representation
     *
     * @param bool $includePasswordHash
     * @return array<string, mixed>
     */
    public function toArray(bool $includePasswordHash = false): array
    {
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];

        if ($includePasswordHash) {
            $data['password_hash'] = $this->passwordHash;
        }

        return $data;
    }
}
