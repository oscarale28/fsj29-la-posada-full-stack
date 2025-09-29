<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "username", type: "string", example: "john_doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
        new OA\Property(property: "role", type: "string", enum: ["user", "admin"], example: "user")
    ]
)]
#[OA\Schema(
    schema: "Accommodation",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Luxury Downtown Hotel"),
        new OA\Property(property: "description", type: "string", example: "A beautiful luxury hotel in the heart of downtown"),
        new OA\Property(property: "price", type: "number", format: "float", example: 150.00),
        new OA\Property(property: "location", type: "string", example: "New York, NY"),
        new OA\Property(property: "image_url", type: "string", format: "uri", nullable: true, example: "https://example.com/hotel-image.jpg"),
        new OA\Property(
            property: "amenities",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["wifi", "pool", "gym", "parking"]
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-15 10:30:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-15 10:30:00")
    ]
)]
#[OA\Schema(
    schema: "AuthResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(property: "message", type: "string", example: "Login successful"),
        new OA\Property(property: "token", type: "string", example: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
        new OA\Property(property: "user", ref: "#/components/schemas/User"),
        new OA\Property(property: "expires_in", type: "integer", example: 3600)
    ]
)]
#[OA\Schema(
    schema: "ErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "error", type: "string", example: "VALIDATION_ERROR"),
        new OA\Property(property: "message", type: "string", example: "Validation failed: email - Email is required")
    ]
)]
#[OA\Schema(
    schema: "SuccessResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(property: "message", type: "string", example: "Operation completed successfully"),
        new OA\Property(property: "data", type: "object")
    ]
)]
class Schemas
{
    // This class exists only to hold schema definitions
}
