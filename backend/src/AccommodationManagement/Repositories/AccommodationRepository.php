<?php

declare(strict_types=1);

namespace App\AccommodationManagement\Repositories;

use App\AccommodationManagement\Entities\Accommodation;
use App\AccommodationManagement\Repositories\AccommodationRepositoryInterface;
use App\Shared\Database\DatabaseConnection;
use DateTime;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Accommodation Repository - Data access layer for accommodation operations
 */
class AccommodationRepository implements AccommodationRepositoryInterface
{
    private DatabaseConnection $database;

    public function __construct()
    {
        $this->database = DatabaseConnection::getInstance();
    }

    /**
     * Find accommodation by ID
     *
     * @param int $id
     * @return Accommodation|null
     */
    public function findById(int $id): ?Accommodation
    {
        try {
            $sql = "SELECT * FROM accommodations WHERE id = :id";
            $statement = $this->database->execute($sql, ['id' => $id]);
            $accommodationData = $statement->fetch(PDO::FETCH_ASSOC);

            return $accommodationData ? $this->mapToEntity($accommodationData) : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find accommodation by ID: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find all accommodations
     *
     * @return array
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM accommodations ORDER BY created_at DESC";
            $statement = $this->database->execute($sql);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $accommodationsData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find all accommodations: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find accommodations by location
     *
     * @param string $location
     * @return array
     */
    public function findByLocation(string $location): array
    {
        try {
            $sql = "SELECT * FROM accommodations WHERE location LIKE :location ORDER BY created_at DESC";
            $statement = $this->database->execute($sql, ['location' => '%' . $location . '%']);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $accommodationsData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find accommodations by location: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find accommodations by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return array
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        try {
            $sql = "SELECT * FROM accommodations WHERE price BETWEEN :min_price AND :max_price ORDER BY price ASC";
            $params = [
                'min_price' => $minPrice,
                'max_price' => $maxPrice
            ];
            $statement = $this->database->execute($sql, $params);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $accommodationsData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find accommodations by price range: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find accommodations with specific amenity
     *
     * @param string $amenity
     * @return array
     */
    public function findByAmenity(string $amenity): array
    {
        try {
            $sql = "SELECT * FROM accommodations WHERE JSON_CONTAINS(amenities, :amenity) ORDER BY created_at DESC";
            $statement = $this->database->execute($sql, ['amenity' => json_encode($amenity)]);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $accommodationsData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find accommodations by amenity: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Search accommodations by title or description
     *
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array
    {
        try {
            $sql = "SELECT * FROM accommodations 
                    WHERE title LIKE :search_term1 OR description LIKE :search_term2 
                    ORDER BY created_at DESC";
            $searchParam = '%' . $searchTerm . '%';
            $statement = $this->database->execute($sql, [
                'search_term1' => $searchParam,
                'search_term2' => $searchParam
            ]);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $accommodationsData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to search accommodations: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get accommodations with pagination
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findWithPagination(int $limit, int $offset = 0): array
    {
        try {
            $sql = "SELECT * FROM accommodations ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $params = [
                'limit' => $limit,
                'offset' => $offset
            ];
            $statement = $this->database->execute($sql, $params);
            $accommodationsData = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToEntity'], $accommodationsData);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find accommodations with pagination: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Count total accommodations
     *
     * @return int
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM accommodations";
            $statement = $this->database->execute($sql);

            return (int) $statement->fetchColumn();
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to count accommodations: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Save accommodation (create or update)
     *
     * @param Accommodation $accommodation
     * @return Accommodation
     */
    public function save(mixed $accommodation): Accommodation
    {
        if (!$accommodation instanceof Accommodation) {
            throw new RuntimeException("Expected Accommodation entity");
        }

        return $accommodation->getId() === 0 ? $this->create($accommodation) : $this->update($accommodation);
    }

    /**
     * Create new accommodation
     *
     * @param Accommodation $accommodation
     * @return Accommodation
     */
    private function create(Accommodation $accommodation): Accommodation
    {
        try {
            $sql = "INSERT INTO accommodations (title, description, price, location, image_url, amenities, created_at, updated_at) 
                    VALUES (:title, :description, :price, :location, :image_url, :amenities, :created_at, :updated_at)";

            $params = [
                'title' => $accommodation->getTitle(),
                'description' => $accommodation->getDescription(),
                'price' => $accommodation->getPrice(),
                'location' => $accommodation->getLocation(),
                'image_url' => $accommodation->getImageUrl(),
                'amenities' => json_encode($accommodation->getAmenities()),
                'created_at' => $accommodation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $accommodation->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            $this->database->execute($sql, $params);
            $accommodationId = (int) $this->database->getLastInsertId();

            return $this->findById($accommodationId);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create accommodation: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update existing accommodation
     *
     * @param Accommodation $accommodation
     * @return Accommodation
     */
    private function update(Accommodation $accommodation): Accommodation
    {
        try {
            $sql = "UPDATE accommodations 
                    SET title = :title, description = :description, price = :price, 
                        location = :location, image_url = :image_url, amenities = :amenities, 
                        updated_at = :updated_at 
                    WHERE id = :id";

            $params = [
                'id' => $accommodation->getId(),
                'title' => $accommodation->getTitle(),
                'description' => $accommodation->getDescription(),
                'price' => $accommodation->getPrice(),
                'location' => $accommodation->getLocation(),
                'image_url' => $accommodation->getImageUrl(),
                'amenities' => json_encode($accommodation->getAmenities()),
                'updated_at' => $accommodation->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            $this->database->execute($sql, $params);

            return $this->findById($accommodation->getId());
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to update accommodation: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete accommodation by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM accommodations WHERE id = :id";
            $statement = $this->database->execute($sql, ['id' => $id]);

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to delete accommodation: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if accommodation exists by ID
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM accommodations WHERE id = :id";
            $statement = $this->database->execute($sql, ['id' => $id]);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check accommodation existence: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get users who selected this accommodation
     *
     * @param int $accommodationId
     * @return array
     */
    public function getAccommodationUsers(int $accommodationId): array
    {
        try {
            $sql = "SELECT u.* FROM users u 
                    INNER JOIN user_accommodations ua ON u.id = ua.user_id 
                    WHERE ua.accommodation_id = :accommodation_id 
                    ORDER BY ua.selected_at DESC";

            $statement = $this->database->execute($sql, ['accommodation_id' => $accommodationId]);
            $usersData = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Return raw data for now - will be mapped to User entities by service layer
            return $usersData;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to get accommodation users: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Map database row to Accommodation entity
     *
     * @param array $accommodationData
     * @return Accommodation
     */
    private function mapToEntity(array $accommodationData): Accommodation
    {
        $amenities = [];
        if (!empty($accommodationData['amenities'])) {
            $decodedAmenities = json_decode($accommodationData['amenities'], true);
            $amenities = is_array($decodedAmenities) ? $decodedAmenities : [];
        }

        return new Accommodation(
            (int) $accommodationData['id'],
            $accommodationData['title'],
            $accommodationData['description'],
            (float) $accommodationData['price'],
            $accommodationData['location'],
            $accommodationData['image_url'],
            $amenities,
            new DateTime($accommodationData['created_at']),
            new DateTime($accommodationData['updated_at'])
        );
    }
}
