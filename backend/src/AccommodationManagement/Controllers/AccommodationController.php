<?php

declare(strict_types=1);

namespace App\AccommodationManagement\Controllers;

use App\AccommodationManagement\Services\AccommodationService;
use App\Authentication\Middleware\AuthenticationMiddleware;
use App\Shared\Http\Router;
use InvalidArgumentException;
use RuntimeException;
use OpenApi\Attributes as OA;

/**
 * Accommodation Controller - Handles accommodation endpoints
 */
class AccommodationController
{
    private AccommodationService $accommodationService;

    public function __construct(AccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }

    /**
     * GET /api/accommodations - List all accommodations (public endpoint)
     */
    #[OA\Get(
        path: "/accommodations",
        summary: "List all accommodations",
        description: "Get a list of all available accommodations with optional filtering",
        tags: ["Accommodations"],
        parameters: [
            new OA\Parameter(
                name: "limit",
                in: "query",
                description: "Maximum number of accommodations to return",
                schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100, example: 10)
            ),
            new OA\Parameter(
                name: "offset",
                in: "query",
                description: "Number of accommodations to skip",
                schema: new OA\Schema(type: "integer", minimum: 0, example: 0)
            ),
            new OA\Parameter(
                name: "location",
                in: "query",
                description: "Filter by location",
                schema: new OA\Schema(type: "string", example: "New York")
            ),
            new OA\Parameter(
                name: "min_price",
                in: "query",
                description: "Minimum price filter",
                schema: new OA\Schema(type: "number", format: "float", minimum: 0, example: 50.00)
            ),
            new OA\Parameter(
                name: "max_price",
                in: "query",
                description: "Maximum price filter",
                schema: new OA\Schema(type: "number", format: "float", minimum: 0, example: 200.00)
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search in title and description",
                schema: new OA\Schema(type: "string", example: "luxury hotel")
            ),
            new OA\Parameter(
                name: "amenity",
                in: "query",
                description: "Filter by amenity",
                schema: new OA\Schema(type: "string", example: "wifi")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of accommodations",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "accommodations",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Accommodation")
                        ),
                        new OA\Property(property: "total", type: "integer", example: 25),
                        new OA\Property(
                            property: "filters_applied",
                            type: "object",
                            properties: [
                                new OA\Property(property: "search", type: "string", nullable: true),
                                new OA\Property(property: "location", type: "string", nullable: true),
                                new OA\Property(property: "min_price", type: "number", nullable: true),
                                new OA\Property(property: "max_price", type: "number", nullable: true),
                                new OA\Property(property: "amenity", type: "string", nullable: true),
                                new OA\Property(property: "limit", type: "integer", nullable: true),
                                new OA\Property(property: "offset", type: "integer", nullable: true)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid request parameters",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function listAccommodations(array $params = []): array
    {
        try {
            // Get query parameters for pagination and filtering
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : null;
            $location = $_GET['location'] ?? null;
            $minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
            $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;
            $search = $_GET['search'] ?? null;
            $amenity = $_GET['amenity'] ?? null;

            $accommodations = [];

            // Apply filters based on query parameters
            if ($search) {
                $accommodations = $this->accommodationService->searchAccommodations($search);
            } elseif ($location) {
                $accommodations = $this->accommodationService->findAccommodationsByLocation($location);
            } elseif ($minPrice !== null && $maxPrice !== null) {
                $accommodations = $this->accommodationService->findAccommodationsByPriceRange($minPrice, $maxPrice);
            } elseif ($amenity) {
                $accommodations = $this->accommodationService->findAccommodationsByAmenity($amenity);
            } elseif ($limit !== null) {
                $accommodations = $this->accommodationService->getAccommodationsWithPagination($limit, $offset ?? 0);
            } else {
                $accommodations = $this->accommodationService->getAllAccommodations();
            }

            // Convert entities to array format for JSON response
            $accommodationData = array_map(function ($accommodation) {
                return [
                    'id' => $accommodation->getId(),
                    'title' => $accommodation->getTitle(),
                    'description' => $accommodation->getDescription(),
                    'price' => $accommodation->getPrice(),
                    'location' => $accommodation->getLocation(),
                    'image_url' => $accommodation->getImageUrl(),
                    'amenities' => $accommodation->getAmenities(),
                    'created_at' => $accommodation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $accommodation->getUpdatedAt()->format('Y-m-d H:i:s')
                ];
            }, $accommodations);

            return [
                'status' => 200,
                'data' => [
                    'accommodations' => $accommodationData,
                    'total' => count($accommodationData),
                    'filters_applied' => [
                        'search' => $search,
                        'location' => $location,
                        'min_price' => $minPrice,
                        'max_price' => $maxPrice,
                        'amenity' => $amenity,
                        'limit' => $limit,
                        'offset' => $offset
                    ]
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 400,
                'data' => [
                    'error' => 'Invalid request parameters',
                    'message' => $e->getMessage()
                ]
            ];
        } catch (RuntimeException $e) {
            error_log("AccommodationController::listAccommodations error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'error' => 'Failed to retrieve accommodations',
                    'message' => 'An error occurred while fetching accommodations',
                    'debug' => $e->getMessage() // Debug temporal
                ]
            ];
        } catch (\Exception $e) {
            error_log("AccommodationController::listAccommodations unexpected error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred',
                    'debug' => $e->getMessage() // Debug temporal
                ]
            ];
        }
    }

    /**
     * POST /api/admin/accommodations - Create new accommodation (admin-only endpoint)
     */
    #[OA\Post(
        path: "/admin/accommodations",
        summary: "Create new accommodation",
        description: "Create a new accommodation (admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Admin"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "description", "price", "location"],
                properties: [
                    new OA\Property(
                        property: "title",
                        type: "string",
                        minLength: 1,
                        maxLength: 255,
                        example: "Luxury Downtown Hotel"
                    ),
                    new OA\Property(
                        property: "description",
                        type: "string",
                        minLength: 1,
                        example: "A beautiful luxury hotel in the heart of downtown"
                    ),
                    new OA\Property(
                        property: "price",
                        type: "number",
                        format: "float",
                        minimum: 0,
                        example: 150.00
                    ),
                    new OA\Property(
                        property: "location",
                        type: "string",
                        minLength: 1,
                        maxLength: 255,
                        example: "New York, NY"
                    ),
                    new OA\Property(
                        property: "image_url",
                        type: "string",
                        format: "uri",
                        nullable: true,
                        example: "https://example.com/hotel-image.jpg"
                    ),
                    new OA\Property(
                        property: "amenities",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["wifi", "pool", "gym", "parking"]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Accommodation created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Accommodation created successfully"),
                        new OA\Property(property: "accommodation", ref: "#/components/schemas/Accommodation"),
                        new OA\Property(
                            property: "created_by",
                            type: "object",
                            properties: [
                                new OA\Property(property: "admin_id", type: "integer", example: 1),
                                new OA\Property(property: "admin_username", type: "string", example: "admin_user")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid accommodation data",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 403,
                description: "Admin privileges required",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function createAccommodation(array $params = []): array
    {
        try {
            // Verify admin authorization
            $currentUser = AuthenticationMiddleware::getCurrentUser();
            if (!$currentUser || $currentUser['role'] !== 'admin') {
                return [
                    'status' => 403,
                    'data' => [
                        'error' => 'Access denied',
                        'message' => 'Admin privileges required to create accommodations'
                    ]
                ];
            }

            // Get request body data
            $requestData = Router::getRequestBody();

            // Validate required fields
            $requiredFields = ['title', 'description', 'price', 'location'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($requestData[$field]) || empty(trim((string) $requestData[$field]))) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return [
                    'status' => 400,
                    'data' => [
                        'error' => 'Missing required fields',
                        'missing_fields' => $missingFields,
                        'required_fields' => $requiredFields
                    ]
                ];
            }

            // Extract and validate data
            $title = trim((string) $requestData['title']);
            $description = trim((string) $requestData['description']);
            $price = (float) $requestData['price'];
            $location = trim((string) $requestData['location']);
            $imageUrl = isset($requestData['image_url']) ? trim((string) $requestData['image_url']) : null;
            $amenities = isset($requestData['amenities']) && is_array($requestData['amenities'])
                ? $requestData['amenities']
                : [];

            // Additional validation
            if ($price < 0) {
                return [
                    'status' => 400,
                    'data' => [
                        'error' => 'Invalid price',
                        'message' => 'Price cannot be negative'
                    ]
                ];
            }

            // Create accommodation using service
            $accommodation = $this->accommodationService->createAccommodation(
                $title,
                $description,
                $price,
                $location,
                $imageUrl,
                $amenities
            );

            // Return success response with created accommodation data
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Accommodation created successfully',
                    'accommodation' => [
                        'id' => $accommodation->getId(),
                        'title' => $accommodation->getTitle(),
                        'description' => $accommodation->getDescription(),
                        'price' => $accommodation->getPrice(),
                        'location' => $accommodation->getLocation(),
                        'image_url' => $accommodation->getImageUrl(),
                        'amenities' => $accommodation->getAmenities(),
                        'created_at' => $accommodation->getCreatedAt()->format('Y-m-d H:i:s'),
                        'updated_at' => $accommodation->getUpdatedAt()->format('Y-m-d H:i:s')
                    ],
                    'created_by' => [
                        'admin_id' => $currentUser['id'],
                        'admin_username' => $currentUser['username']
                    ]
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 400,
                'data' => [
                    'error' => 'Invalid accommodation data',
                    'message' => $e->getMessage()
                ]
            ];
        } catch (RuntimeException $e) {
            error_log("AccommodationController::createAccommodation error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'error' => 'Failed to create accommodation',
                    'message' => 'An error occurred while creating the accommodation'
                ]
            ];
        } catch (\Exception $e) {
            error_log("AccommodationController::createAccommodation unexpected error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred'
                ]
            ];
        }
    }

    /**
     * GET /api/accommodations/{id} - Get single accommodation by ID (public endpoint)
     */
    #[OA\Get(
        path: "/accommodations/{id}",
        summary: "Get accommodation by ID",
        description: "Retrieve a specific accommodation by its ID",
        tags: ["Accommodations"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Accommodation ID",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Accommodation details",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "accommodation", ref: "#/components/schemas/Accommodation")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Accommodation not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function getAccommodation(array $params = []): array
    {
        try {
            $accommodationId = isset($params['id']) ? (int) $params['id'] : null;

            if (!$accommodationId) {
                return [
                    'status' => 400,
                    'data' => [
                        'error' => 'Invalid accommodation ID',
                        'message' => 'Accommodation ID is required'
                    ]
                ];
            }

            $accommodation = $this->accommodationService->findAccommodationById($accommodationId);

            if (!$accommodation) {
                return [
                    'status' => 404,
                    'data' => [
                        'error' => 'Accommodation not found',
                        'message' => 'No accommodation found with the specified ID'
                    ]
                ];
            }

            return [
                'status' => 200,
                'data' => [
                    'accommodation' => [
                        'id' => $accommodation->getId(),
                        'title' => $accommodation->getTitle(),
                        'description' => $accommodation->getDescription(),
                        'price' => $accommodation->getPrice(),
                        'location' => $accommodation->getLocation(),
                        'image_url' => $accommodation->getImageUrl(),
                        'amenities' => $accommodation->getAmenities(),
                        'created_at' => $accommodation->getCreatedAt()->format('Y-m-d H:i:s'),
                        'updated_at' => $accommodation->getUpdatedAt()->format('Y-m-d H:i:s')
                    ]
                ]
            ];
        } catch (\Exception $e) {
            error_log("AccommodationController::getAccommodation unexpected error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred'
                ]
            ];
        }
    }
}
