<?php

declare(strict_types=1);

namespace App\Shared\Database;

use PDO;
use PDOException;
use RuntimeException;
use Dotenv\Dotenv;

/**
 * Database connection manager implementing Singleton pattern with connection pooling
 * Provides centralized database connection management with error handling and optimization
 */
class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private ?PDO $connection = null;
    private array $config;
    private int $connectionAttempts = 0;
    private const MAX_CONNECTION_ATTEMPTS = 3;
    private const CONNECTION_TIMEOUT = 30;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Get singleton instance of DatabaseConnection
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get PDO connection instance with connection pooling
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null || !$this->isConnectionAlive()) {
            $this->establishConnection();
        }

        return $this->connection;
    }

    /**
     * Load database configuration from environment variables
     */
    private function loadConfiguration(): void
    {
        // Load environment variables using vlucas/phpdotenv
        $envFile = __DIR__ . '/../../../.env';
        if (file_exists($envFile)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->safeLoad();
        }

        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? 'accommodation_management',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        ];
    }

    /**
     * Establish database connection with retry logic
     */
    private function establishConnection(): void
    {
        $this->connectionAttempts = 0;

        while ($this->connectionAttempts < self::MAX_CONNECTION_ATTEMPTS) {
            try {
                $this->connectionAttempts++;

                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database'],
                    $this->config['charset']
                );

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => self::CONNECTION_TIMEOUT,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['charset']}_unicode_ci",
                    PDO::ATTR_PERSISTENT => true, // Enable connection pooling
                ];

                $this->connection = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    $options
                );

                // Connection successful, break the retry loop
                break;
            } catch (PDOException $e) {
                if ($this->connectionAttempts >= self::MAX_CONNECTION_ATTEMPTS) {
                    throw new RuntimeException(
                        "Failed to establish database connection after {$this->connectionAttempts} attempts: " . $e->getMessage(),
                        is_numeric($e->getCode()) ? (int)$e->getCode() : 0,
                        $e
                    );
                }

                // Wait before retry (exponential backoff)
                sleep($this->connectionAttempts * 2);
            }
        }
    }

    /**
     * Check if current connection is alive
     */
    private function isConnectionAlive(): bool
    {
        if ($this->connection === null) {
            return false;
        }

        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Execute a prepared statement with parameters
     */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        try {
            $statement = $this->getConnection()->prepare($sql);
            $statement->execute($params);
            return $statement;
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database query execution failed: " . $e->getMessage(),
                is_numeric($e->getCode()) ? (int)$e->getCode() : 0,
                $e
            );
        }
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit database transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback database transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Get the last inserted ID
     */
    public function getLastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Close database connection
     */
    public function closeConnection(): void
    {
        $this->connection = null;
    }

    /**
     * Prevent cloning of singleton instance
     */
    private function __clone(): void
    {
        throw new RuntimeException('Cannot clone singleton DatabaseConnection instance');
    }

    /**
     * Prevent unserialization of singleton instance
     */
    public function __wakeup(): void
    {
        throw new RuntimeException('Cannot unserialize singleton DatabaseConnection instance');
    }

    /**
     * Clean up connection on destruction
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}
