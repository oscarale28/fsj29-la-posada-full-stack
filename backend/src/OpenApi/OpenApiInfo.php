<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Main OpenAPI specification for the Accommodation Management System
 */
#[OA\Info(
    version: "1.0.0",
    title: "Accommodation Management System API",
    description: "A comprehensive API for managing accommodations and user accounts.

## Authentication
Most endpoints require JWT authentication. Include the JWT token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

## User Roles
- **User**: Can view accommodations and manage their own accommodation selections
- **Admin**: Can create accommodations and perform all user operations",
    contact: new OA\Contact(
        name: "API Support",
        email: "support@accommodations.com"
    ),
    license: new OA\License(
        name: "MIT",
        url: "https://opensource.org/licenses/MIT"
    )
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Development server"
)]
#[OA\Server(
    url: "https://api.accommodations.com/api",
    description: "Production server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "JWT token obtained from login or register endpoint"
)]
#[OA\Tag(
    name: "System",
    description: "System health and status endpoints"
)]
#[OA\Tag(
    name: "Authentication",
    description: "User authentication and authorization"
)]
#[OA\Tag(
    name: "Accommodations",
    description: "Public accommodation browsing and search"
)]
#[OA\Tag(
    name: "Admin",
    description: "Administrative operations (admin role required)"
)]
#[OA\Tag(
    name: "User Accommodations",
    description: "User accommodation management operations"
)]
#[OA\Tag(
    name: "User",
    description: "User profile and account operations"
)]
class OpenApiInfo
{
    // This class exists only to hold the main OpenAPI annotations
}
