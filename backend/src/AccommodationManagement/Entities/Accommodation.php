<?php

namespace App\AccommodationManagement\Entities;

use DateTime;
use InvalidArgumentException;

/**
 * Accommodation Entity - Domain model representing accommodation data
 */
class Accommodation
{
    private int $id;
    private string $title;
    private string $description;
    private float $price;
    private string $location;
    private ?string $imageUrl;
    private array $amenities;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    /**
     * Constructor
     *
     * @param int $id
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @param array $amenities
     * @param DateTime $createdAt
     * @param DateTime $updatedAt
     */
    public function __construct(
        int $id,
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl,
        array $amenities,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->validateTitle($title);
        $this->validateDescription($description);
        $this->validatePrice($price);
        $this->validateLocation($location);
        $this->validateImageUrl($imageUrl);
        $this->validateAmenities($amenities);

        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->location = $location;
        $this->imageUrl = $imageUrl;
        $this->amenities = $amenities;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create a new Accommodation instance
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $location
     * @param string|null $imageUrl
     * @param array $amenities
     * @return Accommodation
     */
    public static function create(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $imageUrl = null,
        array $amenities = []
    ): Accommodation {
        $now = new DateTime();

        return new self(
            0, // ID will be set by database
            $title,
            $description,
            $price,
            $location,
            $imageUrl,
            $amenities,
            $now,
            $now
        );
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
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

    /**
     * Update accommodation information
     *
     * @param string|null $title
     * @param string|null $description
     * @param float|null $price
     * @param string|null $location
     * @param string|null $imageUrl
     * @param array|null $amenities
     * @return void
     */
    public function updateInfo(
        ?string $title = null,
        ?string $description = null,
        ?float $price = null,
        ?string $location = null,
        ?string $imageUrl = null,
        ?array $amenities = null
    ): void {
        if ($title !== null) {
            $this->validateTitle($title);
            $this->title = $title;
        }

        if ($description !== null) {
            $this->validateDescription($description);
            $this->description = $description;
        }

        if ($price !== null) {
            $this->validatePrice($price);
            $this->price = $price;
        }

        if ($location !== null) {
            $this->validateLocation($location);
            $this->location = $location;
        }

        if ($imageUrl !== null) {
            $this->validateImageUrl($imageUrl);
            $this->imageUrl = $imageUrl;
        }

        if ($amenities !== null) {
            $this->validateAmenities($amenities);
            $this->amenities = $amenities;
        }

        $this->updatedAt = new DateTime();
    }

    /**
     * Add an amenity to the accommodation
     *
     * @param string $amenity
     * @return void
     */
    public function addAmenity(string $amenity): void
    {
        if (empty(trim($amenity))) {
            throw new InvalidArgumentException('Amenity cannot be empty');
        }

        if (!in_array($amenity, $this->amenities, true)) {
            $this->amenities[] = $amenity;
            $this->updatedAt = new DateTime();
        }
    }

    /**
     * Remove an amenity from the accommodation
     *
     * @param string $amenity
     * @return void
     */
    public function removeAmenity(string $amenity): void
    {
        $key = array_search($amenity, $this->amenities, true);
        if ($key !== false) {
            unset($this->amenities[$key]);
            $this->amenities = array_values($this->amenities); // Re-index array
            $this->updatedAt = new DateTime();
        }
    }

    /**
     * Check if accommodation has a specific amenity
     *
     * @param string $amenity
     * @return bool
     */
    public function hasAmenity(string $amenity): bool
    {
        return in_array($amenity, $this->amenities, true);
    }

    /**
     * Get formatted price with currency
     *
     * @param string $currency
     * @return string
     */
    public function getFormattedPrice(string $currency = '$'): string
    {
        return $currency . number_format($this->price, 2);
    }

    // Validation methods
    private function validateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($title) < 3) {
            throw new InvalidArgumentException('Title must be at least 3 characters long');
        }

        if (strlen($title) > 200) {
            throw new InvalidArgumentException('Title cannot exceed 200 characters');
        }
    }

    private function validateDescription(string $description): void
    {
        if (empty(trim($description))) {
            throw new InvalidArgumentException('Description cannot be empty');
        }

        if (strlen($description) < 10) {
            throw new InvalidArgumentException('Description must be at least 10 characters long');
        }

        if (strlen($description) > 2000) {
            throw new InvalidArgumentException('Description cannot exceed 2000 characters');
        }
    }

    private function validatePrice(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Price cannot be negative');
        }

        if ($price > 999999.99) {
            throw new InvalidArgumentException('Price cannot exceed 999,999.99');
        }
    }

    private function validateLocation(string $location): void
    {
        if (empty(trim($location))) {
            throw new InvalidArgumentException('Location cannot be empty');
        }

        if (strlen($location) < 2) {
            throw new InvalidArgumentException('Location must be at least 2 characters long');
        }

        if (strlen($location) > 100) {
            throw new InvalidArgumentException('Location cannot exceed 100 characters');
        }
    }

    private function validateImageUrl(?string $imageUrl): void
    {
        if ($imageUrl === null) {
            return;
        }

        if (empty(trim($imageUrl))) {
            throw new InvalidArgumentException('Image URL cannot be empty if provided');
        }

        if (strlen($imageUrl) > 500) {
            throw new InvalidArgumentException('Image URL cannot exceed 500 characters');
        }

        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid image URL format');
        }
    }

    private function validateAmenities(array $amenities): void
    {
        foreach ($amenities as $amenity) {
            if (!is_string($amenity)) {
                throw new InvalidArgumentException('All amenities must be strings');
            }

            if (empty(trim($amenity))) {
                throw new InvalidArgumentException('Amenity cannot be empty');
            }

            if (strlen($amenity) > 100) {
                throw new InvalidArgumentException('Amenity cannot exceed 100 characters');
            }
        }

        // Check for duplicates
        if (count($amenities) !== count(array_unique($amenities))) {
            throw new InvalidArgumentException('Amenities cannot contain duplicates');
        }
    }

    /**
     * Convert entity to array representation
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'location' => $this->location,
            'image_url' => $this->imageUrl,
            'amenities' => $this->amenities,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
