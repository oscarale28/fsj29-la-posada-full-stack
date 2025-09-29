<?php

namespace App\UserManagement\Controllers;

use App\UserManagement\Services\UserService;
use App\Authentication\Middleware\AuthenticationMiddleware;
use App\Shared\Http\Router;
use InvalidArgumentException;
use Exception;
use OpenApi\Attributes as OA;

/**
 * User Controller - Handles user accommodation management endpoints
 */
class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Add accommodation to user account
     * POST /api/users/accommodations
     */
    #[OA\Post(
        path: "/users/accommodations",
        summary: "Add accommodation to user account",
        description: "Associate an accommodation with the authenticated user's account",
        security: [["bearerAuth" => []]],
        tags: ["User Accommodations"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["accommodation_id"],
                properties: [
                    new OA\Property(
                        property: "accommodation_id",
                        type: "integer",
                        minimum: 1,
                        example: 5
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Accommodation added successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Accommodation added to user account successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user_id", type: "integer", example: 1),
                                new OA\Property(property: "accommodation_id", type: "integer", example: 5)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Validation error or accommodation already added",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Authentication required",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function addAccommodation(): array
    {
        try {
            // Get current authenticated user
            $currentUser = AuthenticationMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'AUTHENTICATION_REQUIRED',
                        'message' => 'Authentication required'
                    ]
                ];
            }

            // Get request data
            $requestData = Router::getRequestBody();

            // Validate required fields
            $this->validateAddAccommodationInput($requestData);

            // Extract accommodation ID
            $accommodationId = (int) $requestData['accommodation_id'];
            $userId = $currentUser['id'];

            // Add accommodation to user account
            $this->userService->addAccommodationToUser($userId, $accommodationId);

            return [
                'status' => 201,
                'data' => [
                    'success' => true,
                    'message' => 'Accommodation added to user account successfully',
                    'data' => [
                        'user_id' => $userId,
                        'accommodation_id' => $accommodationId
                    ]
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage()
                ]
            ];
        } catch (Exception $e) {
            error_log("Add accommodation error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred while adding accommodation'
                ]
            ];
        }
    }

    /**
     * Remove accommodation from user account
     * DELETE /api/users/accommodations/{accommodation_id}
     */
    #[OA\Delete(
        path: "/users/accommodations/{accommodation_id}",
        summary: "Remove accommodation from user account",
        description: "Remove the association between an accommodation and the authenticated user's account",
        security: [["bearerAuth" => []]],
        tags: ["User Accommodations"],
        parameters: [
            new OA\Parameter(
                name: "accommodation_id",
                in: "path",
                required: true,
                description: "ID of the accommodation to remove",
                schema: new OA\Schema(type: "integer", example: 5)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Accommodation removed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Accommodation removed from user account successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user_id", type: "integer", example: 1),
                                new OA\Property(property: "accommodation_id", type: "integer", example: 5)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Validation error or accommodation not found in user's list",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Authentication required",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function removeAccommodation(array $params = []): array
    {
        try {
            // Get current authenticated user
            $currentUser = AuthenticationMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'AUTHENTICATION_REQUIRED',
                        'message' => 'Authentication required'
                    ]
                ];
            }

            // Get accommodation ID from URL parameters
            $accommodationId = isset($params['accommodation_id']) ? (int) $params['accommodation_id'] : null;

            if (!$accommodationId) {
                return [
                    'status' => 400,
                    'data' => [
                        'success' => false,
                        'error' => 'VALIDATION_ERROR',
                        'message' => 'Accommodation ID is required'
                    ]
                ];
            }

            $userId = $currentUser['id'];

            // Remove accommodation from user account
            $this->userService->removeAccommodationFromUser($userId, $accommodationId);

            return [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'message' => 'Accommodation removed from user account successfully',
                    'data' => [
                        'user_id' => $userId,
                        'accommodation_id' => $accommodationId
                    ]
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage()
                ]
            ];
        } catch (Exception $e) {
            error_log("Remove accommodation error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred while removing accommodation'
                ]
            ];
        }
    }

    /**
     * Get user's accommodations
     * GET /api/users/accommodations
     */
    #[OA\Get(
        path: "/users/accommodations",
        summary: "Get user's accommodations",
        description: "Retrieve all accommodations associated with the authenticated user",
        security: [["bearerAuth" => []]],
        tags: ["User Accommodations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "User's accommodations retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "User accommodations retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user_id", type: "integer", example: 1),
                                new OA\Property(
                                    property: "accommodations",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/Accommodation")
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Authentication required",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function getUserAccommodations(): array
    {
        try {
            // Get current authenticated user
            $currentUser = AuthenticationMiddleware::getCurrentUser();
            if (!$currentUser) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'AUTHENTICATION_REQUIRED',
                        'message' => 'Authentication required'
                    ]
                ];
            }

            $userId = $currentUser['id'];

            // Get user's accommodations
            $accommodations = $this->userService->getUserAccommodations($userId);

            return [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'message' => 'User accommodations retrieved successfully',
                    'data' => [
                        'user_id' => $userId,
                        'accommodations' => $accommodations
                    ]
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage()
                ]
            ];
        } catch (Exception $e) {
            error_log("Get user accommodations error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred while retrieving accommodations'
                ]
            ];
        }
    }

    /**
     * Validate add accommodation input data
     */
    private function validateAddAccommodationInput(array $data): void
    {
        $errors = [];

        // Check required fields
        if (empty($data['accommodation_id'])) {
            $errors['accommodation_id'] = ['Accommodation ID is required'];
        } elseif (!is_numeric($data['accommodation_id']) || (int) $data['accommodation_id'] <= 0) {
            $errors['accommodation_id'] = ['Accommodation ID must be a positive integer'];
        }

        if (!empty($errors)) {
            $errorMessage = 'Validation failed: ';
            $errorDetails = [];
            foreach ($errors as $field => $fieldErrors) {
                $errorDetails[] = $field . ' - ' . implode(', ', $fieldErrors);
            }
            $errorMessage .= implode('; ', $errorDetails);

            throw new InvalidArgumentException($errorMessage);
        }
    }
}
