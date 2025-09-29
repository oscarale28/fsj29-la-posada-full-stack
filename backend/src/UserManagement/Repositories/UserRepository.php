<?php

declare(strict_types=1);

namespace App\UserManagement\Repositories;

use App\UserManagement\Entities\User;
use App\UserManagement\Repositories\UserRepositoryInterface;
use App\Shared\Enums\UserRole;
use App\Shared\Database\DatabaseConnection;
use DateTime;
use PDO;
use PDOException;
use RuntimeException;

/**
 * User Repository - Data access layer for user operations
 */
class UserRepository implements UserRepositoryInterface
{
    private DatabaseConnection $database;

    public function __construct()
    {
        $this->database = DatabaseConnection::getInstance();
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $statement = $this->database->execute($sql, ['id' => $id]);
            $userData = $statement->fetch(PDO::FETCH_ASSOC);

            return $userData ? $this->mapToEntity($userData) : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find user by ID: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find user by username
     *
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE username = :username";
            $statement = $this->database->execute($sql, ['username' => $username]);
            $userData = $statement->fetch(PDO::FETCH_ASSOC);

            return $userData ? $this->mapToEntity($userData) : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find user by username: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $statement = $this->database->execute($sql, ['email' => $email]);
            $userData = $statement->fetch(PDO::FETCH_ASSOC);

            return $userData ? $this->mapToEntity($userData) : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find user by email: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find user by username or email
     *
     * @param string $usernameOrEmail
     * @return User|null
     */
    public function findByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE username = :identifier OR email = :identifier";
            $statement = $this->database->execute($sql, ['identifier' => $usernameOrEmail]);
            $userData = $statement->fetch(PDO::FETCH_ASSOC);

            return $userData ? $this->mapToEntity($userData) : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find user by username or email: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find all users
     *
     * @return array
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $statement = $this->database->execute($sql);
            $usersData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $usersData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find all users: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Save user (create or update)
     *
     * @param User $user
     * @return User
     */
    public function save(mixed $user): User
    {
        if (!$user instanceof User) {
            throw new RuntimeException("Expected User entity");
        }

        return $user->getId() === 0 ? $this->create($user) : $this->update($user);
    }

    /**
     * Create new user
     *
     * @param User $user
     * @return User
     */
    private function create(User $user): User
    {
        try {
            $sql = "INSERT INTO users (username, email, password_hash, role, created_at, updated_at) 
                    VALUES (:username, :email, :password_hash, :role, :created_at, :updated_at)";

            $params = [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password_hash' => $user->getPasswordHash(),
                'role' => $user->getRole()->value,
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            $this->database->execute($sql, $params);
            $userId = (int) $this->database->getLastInsertId();

            return $this->findById($userId);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create user: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update existing user
     *
     * @param User $user
     * @return User
     */
    private function update(User $user): User
    {
        try {
            $sql = "UPDATE users 
                    SET username = :username, email = :email, password_hash = :password_hash, 
                        role = :role, updated_at = :updated_at 
                    WHERE id = :id";

            $params = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password_hash' => $user->getPasswordHash(),
                'role' => $user->getRole()->value,
                'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            $this->database->execute($sql, $params);

            return $this->findById($user->getId());
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to update user: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete user by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $statement = $this->database->execute($sql, ['id' => $id]);

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to delete user: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if user exists by ID
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE id = :id";
            $statement = $this->database->execute($sql, ['id' => $id]);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check user existence: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if username exists
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
            $params = ['username' => $username];

            if ($excludeUserId !== null) {
                $sql .= " AND id != :exclude_id";
                $params['exclude_id'] = $excludeUserId;
            }

            $statement = $this->database->execute($sql, $params);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check username existence: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if email exists
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $params = ['email' => $email];

            if ($excludeUserId !== null) {
                $sql .= " AND id != :exclude_id";
                $params['exclude_id'] = $excludeUserId;
            }

            $statement = $this->database->execute($sql, $params);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check email existence: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get user accommodations
     *
     * @param int $userId
     * @return array
     */
    public function getUserAccommodations(int $userId): array
    {
        try {
            $sql = "SELECT a.* FROM accommodations a 
                    INNER JOIN user_accommodations ua ON a.id = ua.accommodation_id 
                    WHERE ua.user_id = :user_id 
                    ORDER BY ua.selected_at DESC";

            $statement = $this->database->execute($sql, ['user_id' => $userId]);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Return raw data for now - will be mapped to Accommodation entities by service layer
            return $accommodationsData;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to get user accommodations: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Add accommodation to user
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function addUserAccommodation(int $userId, int $accommodationId): bool
    {
        try {
            $sql = "INSERT INTO user_accommodations (user_id, accommodation_id, selected_at) 
                    VALUES (:user_id, :accommodation_id, :selected_at)";

            $params = [
                'user_id' => $userId,
                'accommodation_id' => $accommodationId,
                'selected_at' => (new DateTime())->format('Y-m-d H:i:s')
            ];

            $this->database->execute($sql, $params);

            return true;
        } catch (PDOException $e) {
            // Handle duplicate key constraint
            if ($e->getCode() === '23000') {
                return false; // Already exists
            }
            throw new RuntimeException("Failed to add user accommodation: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove accommodation from user
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function removeUserAccommodation(int $userId, int $accommodationId): bool
    {
        try {
            $sql = "DELETE FROM user_accommodations 
                    WHERE user_id = :user_id AND accommodation_id = :accommodation_id";

            $params = [
                'user_id' => $userId,
                'accommodation_id' => $accommodationId
            ];

            $statement = $this->database->execute($sql, $params);

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to remove user accommodation: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if user has accommodation
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function hasUserAccommodation(int $userId, int $accommodationId): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM user_accommodations 
                    WHERE user_id = :user_id AND accommodation_id = :accommodation_id";

            $params = [
                'user_id' => $userId,
                'accommodation_id' => $accommodationId
            ];

            $statement = $this->database->execute($sql, $params);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check user accommodation: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Map database row to User entity
     *
     * @param array $userData
     * @return User
     */
    private function mapToEntity(array $userData): User
    {
        return new User(
            (int) $userData['id'],
            $userData['username'],
            $userData['email'],
            $userData['password_hash'],
            UserRole::from($userData['role']),
            new DateTime($userData['created_at']),
            new DateTime($userData['updated_at'])
        );
    }
}
