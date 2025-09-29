<?php

namespace App\UserManagement\Factories;

use App\UserManagement\Entities\User;
use App\Shared\Enums\UserRole;
use App\Shared\Security\PasswordHasher;
use DateTime;
use InvalidArgumentException;

/**
 * UserFactory - Factory class for creating User entities with validation
 */
class UserFactory
{
    private PasswordHasher $passwordHasher;

    public function __construct(PasswordHasher $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Create a new user with default role (regular user)
     *
     * @param string $username
     * @param string $email
     * @param string $plainPassword
     * @return User
     * @throws InvalidArgumentException
     */
    public function createUser(string $username, string $email, string $plainPassword): User
    {
        $this->validatePlainPassword($plainPassword);
        $passwordHash = $this->passwordHasher->hash($plainPassword);

        return User::create($username, $email, $passwordHash, UserRole::USER);
    }

    /**
     * Create a new admin user
     *
     * @param string $username
     * @param string $email
     * @param string $plainPassword
     * @return User
     * @throws InvalidArgumentException
     */
    public function createAdmin(string $username, string $email, string $plainPassword): User
    {
        $this->validatePlainPassword($plainPassword);
        $passwordHash = $this->passwordHasher->hash($plainPassword);

        return User::create($username, $email, $passwordHash, UserRole::ADMIN);
    }

    /**
     * Create a user with a specific role
     *
     * @param string $username
     * @param string $email
     * @param string $plainPassword
     * @param UserRole $role
     * @return User
     * @throws InvalidArgumentException
     */
    public function createUserWithRole(
        string $username,
        string $email,
        string $plainPassword,
        UserRole $role
    ): User {
        $this->validatePlainPassword($plainPassword);
        $passwordHash = $this->passwordHasher->hash($plainPassword);

        return User::create($username, $email, $passwordHash, $role);
    }

    /**
     * Create a user from database data (for repository use)
     *
     * @param array<string, mixed> $data
     * @return User
     * @throws InvalidArgumentException
     */
    public function createFromDatabaseData(array $data): User
    {
        $this->validateDatabaseData($data);

        $role = UserRole::from($data['role']);
        $createdAt = new DateTime($data['created_at']);
        $updatedAt = new DateTime($data['updated_at']);

        return new User(
            (int) $data['id'],
            (string) $data['username'],
            (string) $data['email'],
            (string) $data['password_hash'],
            $role,
            $createdAt,
            $updatedAt
        );
    }

    /**
     * Create multiple users from database data
     *
     * @param array<array<string, mixed>> $dataArray
     * @return array<User>
     * @throws InvalidArgumentException
     */
    public function createMultipleFromDatabaseData(array $dataArray): array
    {
        $users = [];
        foreach ($dataArray as $data) {
            $users[] = $this->createFromDatabaseData($data);
        }
        return $users;
    }

    /**
     * Validate plain password before hashing
     *
     * @param string $plainPassword
     * @return void
     * @throws InvalidArgumentException
     */
    private function validatePlainPassword(string $plainPassword): void
    {
        if (empty(trim($plainPassword))) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        if (strlen($plainPassword) > 255) {
            throw new InvalidArgumentException('Password cannot exceed 255 characters');
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $plainPassword)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $plainPassword)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        // Check for at least one digit
        if (!preg_match('/\d/', $plainPassword)) {
            throw new InvalidArgumentException('Password must contain at least one digit');
        }

        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $plainPassword)) {
            throw new InvalidArgumentException('Password must contain at least one special character');
        }
    }

    /**
     * Validate database data structure
     *
     * @param array<string, mixed> $data
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateDatabaseData(array $data): void
    {
        $requiredFields = ['id', 'username', 'email', 'password_hash', 'role', 'created_at', 'updated_at'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['id']) || (int) $data['id'] <= 0) {
            throw new InvalidArgumentException('Invalid user ID');
        }

        if (!UserRole::isValid($data['role'])) {
            throw new InvalidArgumentException("Invalid user role: {$data['role']}");
        }

        // Validate datetime strings
        try {
            new DateTime($data['created_at']);
            new DateTime($data['updated_at']);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid datetime format in database data');
        }
    }
}
