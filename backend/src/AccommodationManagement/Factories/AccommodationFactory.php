<?php

namespace App\AccommodationManagement\Factories;

use App\AccommodationManagement\Entities\Accommodation;
use DateTime;
use InvalidArgumentException;

/**
 * AccommodationFactory - Factory class for creating Accommodation entities
 */
class AccommodationFactory
{
    /**
     * Create a new accommodation
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @param array<string> $amenities
     * @return Accommodation
     * @throws InvalidArgumentException
     */
    public function createAccommodation(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl = null,
        array $amenities = []
    ): Accommodation {
        return Accommodation::create(
            $title,
            $description,
            $price,
            $location,
            $imageUrl,
            $amenities
        );
    }

    /**
     * Create a basic accommodation with minimal required data
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @return Accommodation
     * @throws InvalidArgumentException
     */
    public function createBasicAccommodation(
        string $title,
        string $description,
        float $price,
        string $location
    ): Accommodation {
        return Accommodation::create($title, $description, $price, $location);
    }

    /**
     * Create a premium accommodation with common amenities
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @return Accommodation
     * @throws InvalidArgumentException
     */
    public function createPremiumAccommodation(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl = null
    ): Accommodation {
        $premiumAmenities = [
            'WiFi',
            'Air Conditioning',
            'Private Bathroom',
            'Room Service',
            'Flat Screen TV',
            'Mini Bar',
            'Safe',
            'Balcony'
        ];

        return Accommodation::create(
            $title,
            $description,
            $price,
            $location,
            $imageUrl,
            $premiumAmenities
        );
    }

    /**
     * Create a budget accommodation with basic amenities
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @return Accommodation
     * @throws InvalidArgumentException
     */
    public function createBudgetAccommodation(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl = null
    ): Accommodation {
        $basicAmenities = [
            'WiFi',
            'Shared Bathroom',
            'Basic TV'
        ];

        return Accommodation::create(
            $title,
            $description,
            $price,
            $location,
            $imageUrl,
            $basicAmenities
        );
    }

    /**
     * Create accommodation from database data (for repository use)
     *
     * @param array<string, mixed> $data
     * @return Accommodation
     * @throws InvalidArgumentException
     */
    public function createFromDatabaseData(array $data): Accommodation
    {
        $this->validateDatabaseData($data);

        $amenities = [];
        if (!empty($data['amenities'])) {
            if (is_string($data['amenities'])) {
                $amenities = json_decode($data['amenities'], true) ?? [];
            } elseif (is_array($data['amenities'])) {
                $amenities = $data['amenities'];
            }
        }

        $createdAt = new DateTime($data['created_at']);
        $updatedAt = new DateTime($data['updated_at']);

        return new Accommodation(
            (int) $data['id'],
            (string) $data['title'],
            (string) $data['description'],
            (float) $data['price'],
            (string) $data['location'],
            !empty($data['image_url']) ? (string) $data['image_url'] : null,
            $amenities,
            $createdAt,
            $updatedAt
        );
    }

    /**
     * Create multiple accommodations from database data
     *
     * @param array<array<string, mixed>> $dataArray
     * @return array<Accommodation>
     * @throws InvalidArgumentException
     */
    public function createMultipleFromDatabaseData(array $dataArray): array
    {
        $accommodations = [];
        foreach ($dataArray as $data) {
            $accommodations[] = $this->createFromDatabaseData($data);
        }
        return $accommodations;
    }

    /**
     * Create accommodation with custom amenities from predefined categories
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param array<string> $amenityCategories
     * @param string|null $imageUrl
     * @return Accommodation
     * @throws InvalidArgumentException
     */
    public function createWithAmenityCategories(
        string $title,
        string $description,
        float $price,
        string $location,
        array $amenityCategories,
        ?string $imageUrl = null
    ): Accommodation {
        $amenityMap = [
            'basic' => ['WiFi', 'Basic TV', 'Shared Bathroom'],
            'comfort' => ['Private Bathroom', 'Air Conditioning', 'Room Service'],
            'luxury' => ['Mini Bar', 'Safe', 'Balcony', 'Spa Access'],
            'business' => ['Work Desk', 'Business Center Access', 'Meeting Room Access'],
            'entertainment' => ['Flat Screen TV', 'Gaming Console', 'Streaming Services'],
            'outdoor' => ['Garden View', 'Terrace', 'Pool Access', 'Parking']
        ];

        $selectedAmenities = [];
        foreach ($amenityCategories as $category) {
            if (isset($amenityMap[$category])) {
                $selectedAmenities = array_merge($selectedAmenities, $amenityMap[$category]);
            }
        }

        // Remove duplicates and maintain order
        $selectedAmenities = array_unique($selectedAmenities);

        return Accommodation::create(
            $title,
            $description,
            $price,
            $location,
            $imageUrl,
            $selectedAmenities
        );
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
        $requiredFields = ['id', 'title', 'description', 'price', 'location', 'created_at', 'updated_at'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['id']) || (int) $data['id'] <= 0) {
            throw new InvalidArgumentException('Invalid accommodation ID');
        }

        if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
            throw new InvalidArgumentException('Invalid accommodation price');
        }

        // Validate datetime strings
        try {
            new DateTime($data['created_at']);
            new DateTime($data['updated_at']);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid datetime format in database data');
        }

        // Validate amenities if present
        if (isset($data['amenities']) && !empty($data['amenities'])) {
            if (is_string($data['amenities'])) {
                $decoded = json_decode($data['amenities'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException('Invalid JSON format for amenities');
                }
            } elseif (!is_array($data['amenities'])) {
                throw new InvalidArgumentException('Amenities must be an array or JSON string');
            }
        }
    }
}
