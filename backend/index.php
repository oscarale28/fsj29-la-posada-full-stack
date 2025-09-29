<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Shared\Http\Router;
use App\Shared\Http\Middleware\CorsMiddleware;
use App\Authentication\Middleware\AuthenticationMiddleware;
use App\Authentication\Controllers\AuthController;
use App\Authentication\Services\AuthenticationService;
use App\Shared\Security\JWTTokenManager;
use App\Shared\Security\PasswordHasher;
use App\UserManagement\Repositories\UserRepository;
use App\UserManagement\Controllers\UserController;
use App\UserManagement\Services\UserService;

// Load environment variables
$envFile = __DIR__ . './.env';
if (file_exists($envFile)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . './');
    $dotenv->safeLoad();
}

try {
    // Initialize router
    $router = new Router();

    // Add global CORS middleware for frontend-backend communication
    $corsMiddleware = CorsMiddleware::forDevelopment();
    $router->addGlobalMiddleware($corsMiddleware);

    // Initialize dependencies
    $jwtManager = JWTTokenManager::getInstance();
    $passwordHasher = new PasswordHasher();
    $userRepository = new UserRepository();

    // Initialize accommodation dependencies
    $accommodationRepository = new \App\AccommodationManagement\Repositories\AccommodationRepository();

    // Initialize services
    $authService = new AuthenticationService($jwtManager, $passwordHasher, $userRepository);
    $accommodationService = new \App\AccommodationManagement\Services\AccommodationService($accommodationRepository);
    $userService = new UserService($userRepository, $accommodationRepository, $passwordHasher);

    // Initialize controllers
    $authController = new AuthController($authService);
    $accommodationController = new \App\AccommodationManagement\Controllers\AccommodationController($accommodationService);
    $userController = new UserController($userService);

    // Initialize middleware
    $authMiddleware = new AuthenticationMiddleware($jwtManager, $userRepository);

    // Register authentication middleware
    $router->addMiddleware('auth', $authMiddleware);
    $router->addMiddleware('admin', AuthenticationMiddleware::requireAdmin());
    $router->addMiddleware('user', AuthenticationMiddleware::requireAuth());

    // Public routes (no authentication required)
    $router->get('/api/accommodations', [$accommodationController, 'listAccommodations']);
    $router->get('/api/accommodations/{id}', [$accommodationController, 'getAccommodation']);

    // Admin-only routes
    $router->post('/api/admin/accommodations', [$accommodationController, 'createAccommodation'], ['auth', 'admin']);

    // User accommodation management routes (authentication required)
    $router->get('/api/users/accommodations', [$userController, 'getUserAccommodations'], ['auth', 'user']);
    $router->post('/api/users/accommodations', [$userController, 'addAccommodation'], ['auth', 'user']);
    $router->delete('/api/users/accommodations/{accommodation_id}', [$userController, 'removeAccommodation'], ['auth', 'user']);

    // Documentation routes
    $router->get('/docs', function () {
        $docsPath = __DIR__ . '/docs/index.html';
        if (file_exists($docsPath)) {
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($docsPath);
            exit;
        } else {
            return [
                'status' => 404,
                'data' => ['error' => 'Documentation not found. Please ensure docs/index.html exists.']
            ];
        }
    });

    // Authentication routes
    $router->post('/api/auth/login', [$authController, 'login']);
    $router->post('/api/auth/register', [$authController, 'register']);
    $router->post('/api/auth/refresh', [$authController, 'refresh']);
    $router->post('/api/auth/validate', [$authController, 'validate']);

    // Handle the request
    $router->handleRequest();
} catch (Exception $e) {
    // Global error handling
    error_log("Application error: " . $e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An unexpected error occurred'
    ], JSON_PRETTY_PRINT);
}
