<?php

declare(strict_types=1);

namespace App\AccommodationManagement\Services;

use App\AccommodationManagement\Entities\Accommodation;
use App\AccommodationManagement\Repositories\AccommodationRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Accommodation Service - Business logic for accommodation operations
 */
class AccommodationService
{
    private AccommodationRepositoryInterface $accommodationRepository;

    public function __construct(AccommodationRepositoryInterface $accommodationRepository)
    {
        $this->accommodationRepository = $accommodationRepository;
    }

    /**
     * Create a new accommodation
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @param array $amenities
     * @return Accommodation
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function createAccommodation(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl = null,
        array $amenities = []
    ): Accommodation {
        // Validate input data
        $this->validateAccommodationData($title, $description, $price, $location, $imageUrl, $amenities);

        // Create accommodation entity
        $accommodation = Accommodation::create($title, $description, $price, $location, $imageUrl, $amenities);

        // Save accommodation
        $savedAccommodation = $this->accommodationRepository->save($accommodation);
        if (!$savedAccommodation) {
            throw new RuntimeException('Failed to create accommodation');
        }

        return $savedAccommodation;
    }

    /**
     * Find accommodation by ID
     *
     * @param int $accommodationId
     * @return Accommodation|null
     */
    public function findAccommodationById(int $accommodationId): ?Accommodation
    {
        return $this->accommodationRepository->findById($accommodationId);
    }

    /**
     * Get all accommodations
     *
     * @return array
     */
    public function getAllAccommodations(): array
    {
        return $this->accommodationRepository->findAll();
    }

    /**
     * Get accommodations with pagination
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAccommodationsWithPagination(int $limit = 10, int $offset = 0): array
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be greater than 0');
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('Offset cannot be negative');
        }

        return $this->accommodationRepository->findWithPagination($limit, $offset);
    }

    /**
     * Search accommodations by title or description
     *
     * @param string $searchTerm
     * @return array
     */
    public function searchAccommodations(string $searchTerm): array
    {
        if (empty(trim($searchTerm))) {
            throw new InvalidArgumentException('Search term cannot be empty');
        }

        if (strlen($searchTerm) < 2) {
            throw new InvalidArgumentException('Search term must be at least 2 characters long');
        }

        return $this->accommodationRepository->search($searchTerm);
    }

    /**
     * Find accommodations by location
     *
     * @param string $location
     * @return array
     */
    public function findAccommodationsByLocation(string $location): array
    {
        if (empty(trim($location))) {
            throw new InvalidArgumentException('Location cannot be empty');
        }

        return $this->accommodationRepository->findByLocation($location);
    }

    /**
     * Find accommodations by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return array
     */
    public function findAccommodationsByPriceRange(float $minPrice, float $maxPrice): array
    {
        if ($minPrice < 0) {
            throw new InvalidArgumentException('Minimum price cannot be negative');
        }

        if ($maxPrice < 0) {
            throw new InvalidArgumentException('Maximum price cannot be negative');
        }

        if ($minPrice > $maxPrice) {
            throw new InvalidArgumentException('Minimum price cannot be greater than maximum price');
        }

        return $this->accommodationRepository->findByPriceRange($minPrice, $maxPrice);
    }

    /**
     * Find accommodations with specific amenity
     *
     * @param string $amenity
     * @return array
     */
    public function findAccommodationsByAmenity(string $amenity): array
    {
        if (empty(trim($amenity))) {
            throw new InvalidArgumentException('Amenity cannot be empty');
        }

        return $this->accommodationRepository->findByAmenity($amenity);
    }

    /**
     * Update accommodation
     *
     * @param int $accommodationId
     * @param string|null $title
     * @param string|null $description
     * @param float|null $price
     * @param string|null $location
     * @param string|null $imageUrl
     * @param array|null $amenities
     * @return Accommodation
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function updateAccommodation(
        int $accommodationId,
        ?string $title = null,
        ?string $description = null,
        ?float $price = null,
        ?string $location = null,
        ?string $imageUrl = null,
        ?array $amenities = null
    ): Accommodation {
        $accommodation = $this->accommodationRepository->findById($accommodationId);
        if (!$accommodation) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        // Validate updated data
        if ($title !== null || $description !== null || $price !== null || $location !== null || $imageUrl !== null || $amenities !== null) {
            $this->validateAccommodationData(
                $title ?? $accommodation->getTitle(),
                $description ?? $accommodation->getDescription(),
                $price ?? $accommodation->getPrice(),
                $location ?? $accommodation->getLocation(),
                $imageUrl ?? $accommodation->getImageUrl(),
                $amenities ?? $accommodation->getAmenities()
            );
        }

        // Update accommodation entity
        $accommodation->updateInfo($title, $description, $price, $location, $imageUrl, $amenities);

        // Save updated accommodation
        $updatedAccommodation = $this->accommodationRepository->save($accommodation);
        if (!$updatedAccommodation) {
            throw new RuntimeException('Failed to update accommodation');
        }

        return $updatedAccommodation;
    }

    /**
     * Delete accommodation
     *
     * @param int $accommodationId
     * @return bool
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function deleteAccommodation(int $accommodationId): bool
    {
        if (!$this->accommodationRepository->exists($accommodationId)) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        $result = $this->accommodationRepository->delete($accommodationId);
        if (!$result) {
            throw new RuntimeException('Failed to delete accommodation');
        }

        return true;
    }

    /**
     * Get users who selected this accommodation
     *
     * @param int $accommodationId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getAccommodationUsers(int $accommodationId): array
    {
        if (!$this->accommodationRepository->exists($accommodationId)) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        return $this->accommodationRepository->getAccommodationUsers($accommodationId);
    }

    /**
     * Get total count of accommodations
     *
     * @return int
     */
    public function getAccommodationCount(): int
    {
        return $this->accommodationRepository->count();
    }

    /**
     * Check if accommodation exists
     *
     * @param int $accommodationId
     * @return bool
     */
    public function accommodationExists(int $accommodationId): bool
    {
        return $this->accommodationRepository->exists($accommodationId);
    }

    /**
     * Add amenity to accommodation
     *
     * @param int $accommodationId
     * @param string $amenity
     * @return Accommodation
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function addAmenityToAccommodation(int $accommodationId, string $amenity): Accommodation
    {
        $accommodation = $this->accommodationRepository->findById($accommodationId);
        if (!$accommodation) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        if (empty(trim($amenity))) {
            throw new InvalidArgumentException('Amenity cannot be empty');
        }

        // Add amenity to accommodation
        $accommodation->addAmenity($amenity);

        // Save updated accommodation
        $updatedAccommodation = $this->accommodationRepository->save($accommodation);
        if (!$updatedAccommodation) {
            throw new RuntimeException('Failed to add amenity to accommodation');
        }

        return $updatedAccommodation;
    }

    /**
     * Remove amenity from accommodation
     *
     * @param int $accommodationId
     * @param string $amenity
     * @return Accommodation
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function removeAmenityFromAccommodation(int $accommodationId, string $amenity): Accommodation
    {
        $accommodation = $this->accommodationRepository->findById($accommodationId);
        if (!$accommodation) {
            throw new InvalidArgumentException('Accommodation not found');
        }

        if (empty(trim($amenity))) {
            throw new InvalidArgumentException('Amenity cannot be empty');
        }

        // Remove amenity from accommodation
        $accommodation->removeAmenity($amenity);

        // Save updated accommodation
        $updatedAccommodation = $this->accommodationRepository->save($accommodation);
        if (!$updatedAccommodation) {
            throw new RuntimeException('Failed to remove amenity from accommodation');
        }

        return $updatedAccommodation;
    }

    /**
     * Validate accommodation data
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @param array $amenities
     * @throws InvalidArgumentException
     */
    private function validateAccommodationData(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl,
        array $amenities
    ): void {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('Title is required');
        }

        if (empty(trim($description))) {
            throw new InvalidArgumentException('Description is required');
        }

        if ($price < 0) {
            throw new InvalidArgumentException('Price cannot be negative');
        }

        if (empty(trim($location))) {
            throw new InvalidArgumentException('Location is required');
        }

        if ($imageUrl !== null && !empty(trim($imageUrl)) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid image URL format');
        }

        // Validate amenities
        foreach ($amenities as $amenity) {
            if (!is_string($amenity)) {
                throw new InvalidArgumentException('All amenities must be strings');
            }

            if (empty(trim($amenity))) {
                throw new InvalidArgumentException('Amenity cannot be empty');
            }
        }

        // Check for duplicate amenities
        if (count($amenities) !== count(array_unique($amenities))) {
            throw new InvalidArgumentException('Amenities cannot contain duplicates');
        }
    }
}
