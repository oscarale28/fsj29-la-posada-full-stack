<?php

declare(strict_types=1);

namespace App\AccommodationManagement\Repositories;

use App\Shared\Repositories\RepositoryInterface;
use App\AccommodationManagement\Entities\Accommodation;

/**
 * Accommodation Repository Interface - Defines accommodation-specific repository operations
 */
interface AccommodationRepositoryInterface extends RepositoryInterface
{
    /**
     * Find accommodations by location
     *
     * @param string $location
     * @return array
     */
    public function findByLocation(string $location): array;

    /**
     * Find accommodations by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return array
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): array;

    /**
     * Find accommodations with specific amenity
     *
     * @param string $amenity
     * @return array
     */
    public function findByAmenity(string $amenity): array;

    /**
     * Search accommodations by title or description
     *
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array;

    /**
     * Get accommodations with pagination
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findWithPagination(int $limit, int $offset = 0): array;

    /**
     * Count total accommodations
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get users who selected this accommodation
     *
     * @param int $accommodationId
     * @return array
     */
    public function getAccommodationUsers(int $accommodationId): array;
}
