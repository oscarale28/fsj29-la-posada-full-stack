<?php

namespace App\Authentication\Controllers;

use App\Authentication\Services\AuthenticationService;
use App\Shared\Http\Router;
use InvalidArgumentException;
use Exception;
use OpenApi\Attributes as OA;

/**
 * Auth Controller - Handles authentication endpoints
 */
class AuthController
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user login
     */
    #[OA\Post(
        path: "/auth/login",
        summary: "User login",
        description: "Authenticate user and receive JWT token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", example: "SecurePass123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Invalid credentials",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function login(): array
    {
        try {
            // Get request data
            $requestData = Router::getRequestBody();

            // Validate required fields
            $this->validateLoginInput($requestData);

            // Extract credentials
            $email = trim($requestData['email']);
            $password = $requestData['password'];

            // Attempt login
            $result = $this->authService->login($email, $password);

            return [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'message' => 'Login successful',
                    'token' => $result['token'],
                    'user' => $result['user'],
                    'expires_in' => $result['expires_in']
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
            error_log("Login error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred during login'
                ]
            ];
        }
    }

    /**
     * Handle user registration
     */
    #[OA\Post(
        path: "/auth/register",
        summary: "Register a new user",
        description: "Create a new user account",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "email", "password"],
                properties: [
                    new OA\Property(
                        property: "username",
                        type: "string",
                        minLength: 3,
                        maxLength: 50,
                        pattern: "^[a-zA-Z0-9_-]+$",
                        example: "john_doe"
                    ),
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        maxLength: 100,
                        example: "john@example.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        minLength: 8,
                        maxLength: 255,
                        example: "SecurePass123"
                    ),
                    new OA\Property(
                        property: "role",
                        type: "string",
                        enum: ["user", "admin"],
                        example: "user"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function register(): array
    {
        try {
            // Get request data
            $requestData = Router::getRequestBody();

            // Validate required fields
            $this->validateRegistrationInput($requestData);

            // Extract registration data
            $username = trim($requestData['username']);
            $email = trim($requestData['email']);
            $password = $requestData['password'];
            $role = $requestData['role'] ?? null;

            // Attempt registration
            $result = $this->authService->register($username, $email, $password, $role);

            return [
                'status' => 201,
                'data' => [
                    'success' => true,
                    'message' => 'Registration successful',
                    'token' => $result['token'],
                    'user' => $result['user'],
                    'expires_in' => $result['expires_in']
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
            error_log("Registration error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred during registration'
                ]
            ];
        }
    }

    /**
     * Handle token refresh
     */
    #[OA\Post(
        path: "/auth/refresh",
        summary: "Refresh JWT token",
        description: "Get a new JWT token using the current token",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token refreshed successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid or expired token",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function refresh(): array
    {
        try {
            // Get authorization header
            $headers = Router::getRequestHeaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'MISSING_TOKEN',
                        'message' => 'Authorization token is required'
                    ]
                ];
            }

            // Extract token
            $token = substr($authHeader, 7);

            // Attempt token refresh
            $result = $this->authService->refreshToken($token);

            if (!$result) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'INVALID_TOKEN',
                        'message' => 'Invalid or expired token'
                    ]
                ];
            }

            return [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'message' => 'Token refreshed successfully',
                    'token' => $result['token'],
                    'user' => $result['user'],
                    'expires_in' => $result['expires_in']
                ]
            ];
        } catch (Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred during token refresh'
                ]
            ];
        }
    }

    /**
     * Handle token validation
     */
    #[OA\Post(
        path: "/auth/validate",
        summary: "Validate JWT token",
        description: "Check if the current JWT token is valid",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token is valid",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Token is valid"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid or expired token",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function validate(): array
    {
        try {
            // Get authorization header
            $headers = Router::getRequestHeaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'MISSING_TOKEN',
                        'message' => 'Authorization token is required'
                    ]
                ];
            }

            // Extract token
            $token = substr($authHeader, 7);

            // Validate token
            $result = $this->authService->validateToken($token);

            if (!$result) {
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'error' => 'INVALID_TOKEN',
                        'message' => 'Invalid or expired token'
                    ]
                ];
            }

            return [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'message' => 'Token is valid',
                    'user' => [
                        'id' => $result['user_id'],
                        'email' => $result['email'],
                        'username' => $result['username'],
                        'role' => $result['role']
                    ]
                ]
            ];
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred during token validation'
                ]
            ];
        }
    }

    /**
     * Validate login input data
     */
    private function validateLoginInput(array $data): void
    {
        $errors = [];

        // Check required fields
        if (empty($data['email']) || empty(trim($data['email']))) {
            $errors['email'] = ['Email is required'];
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Email format is invalid'];
        }

        if (empty($data['password'])) {
            $errors['password'] = ['Password is required'];
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

    /**
     * Validate registration input data
     */
    private function validateRegistrationInput(array $data): void
    {
        $errors = [];

        // Check required fields
        if (empty($data['username']) || empty(trim($data['username']))) {
            $errors['username'] = ['Username is required'];
        } elseif (strlen(trim($data['username'])) < 3) {
            $errors['username'] = ['Username must be at least 3 characters long'];
        } elseif (strlen(trim($data['username'])) > 50) {
            $errors['username'] = ['Username cannot exceed 50 characters'];
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', trim($data['username']))) {
            $errors['username'] = ['Username can only contain letters, numbers, underscores, and hyphens'];
        }

        if (empty($data['email']) || empty(trim($data['email']))) {
            $errors['email'] = ['Email is required'];
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Email format is invalid'];
        } elseif (strlen(trim($data['email'])) > 100) {
            $errors['email'] = ['Email cannot exceed 100 characters'];
        }

        if (empty($data['password'])) {
            $errors['password'] = ['Password is required'];
        }

        // Validate role if provided
        if (isset($data['role']) && !empty($data['role'])) {
            $validRoles = ['user', 'admin'];
            if (!in_array($data['role'], $validRoles)) {
                $errors['role'] = ['Role must be either "user" or "admin"'];
            }
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
