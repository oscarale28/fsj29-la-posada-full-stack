<?php

namespace App\Shared\Http;

/**
 * Router - Routes HTTP requests to appropriate controllers
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $globalMiddlewares = [];

    /**
     * Add a GET route
     */
    public function get(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Add a PUT route
     */
    public function put(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Add a route with any HTTP method
     */
    public function addRoute(string $method, string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Add global middleware that runs on all routes
     */
    public function addGlobalMiddleware(callable|object $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    /**
     * Add middleware for specific routes
     */
    public function addMiddleware(string $name, callable|object $middleware): void
    {
        $this->middlewares[$name] = $middleware;
    }

    /**
     * Handle incoming HTTP request
     */
    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = $this->getRequestPath();

        // Find matching route
        $route = $this->findRoute($method, $path);

        if (!$route) {
            // Debug information
            $debug = [
                'error' => 'Route not found',
                'method' => $method,
                'path' => $path,
                'original_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'available_routes' => array_keys($this->routes[$method] ?? [])
            ];
            $this->sendResponse(404, $debug);
            return;
        }

        try {
            // Execute global middlewares first
            foreach ($this->globalMiddlewares as $middleware) {
                $result = $this->executeMiddleware($middleware);
                if ($result !== null) {
                    $this->sendResponse($result['status'] ?? 200, $result['data'] ?? []);
                    echo "Available Routes: ";
                    foreach ($this->routes as $route) {
                        echo $route[""] . "" . $route[""];
                    }
                    return;
                }
            }

            // Execute route-specific middlewares
            foreach ($route['middlewares'] as $middlewareName) {
                if (isset($this->middlewares[$middlewareName])) {
                    $result = $this->executeMiddleware($this->middlewares[$middlewareName]);
                    if ($result !== null) {
                        $this->sendResponse($result['status'] ?? 200, $result['data'] ?? []);
                        return;
                    }
                }
            }

            // Execute route handler
            $response = $this->executeHandler($route['handler'], $route['params'] ?? []);

            if (is_array($response)) {
                $this->sendResponse($response['status'] ?? 200, $response['data'] ?? $response);
            } else {
                $this->sendResponse(200, ['data' => $response]);
            }
        } catch (\Exception $e) {
            error_log("Router error: " . $e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal server error']);
        }
    }

    /**
     * Find matching route for method and path
     */
    private function findRoute(string $method, string $path): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        // First try exact match
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }

        // Try pattern matching for dynamic routes
        foreach ($this->routes[$method] as $routePath => $route) {
            $params = $this->matchRoute($routePath, $path);
            if ($params !== null) {
                $route['params'] = $params;
                return $route;
            }
        }

        return null;
    }

    /**
     * Match route pattern with actual path
     */
    private function matchRoute(string $routePath, string $actualPath): ?array
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $actualPath, $matches)) {
            array_shift($matches); // Remove full match

            // Extract parameter names
            preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
            $paramNames = $paramNames[1];

            $params = [];
            foreach ($paramNames as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }

            return $params;
        }

        return null;
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware(callable|object $middleware): ?array
    {
        if (is_callable($middleware)) {
            return $middleware();
        }

        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            return $middleware->handle();
        }

        return null;
    }

    /**
     * Execute route handler
     */
    private function executeHandler(callable|array $handler, array $params = []): mixed
    {
        if (is_callable($handler)) {
            return $handler($params);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;

            if (is_string($controller)) {
                $controller = new $controller();
            }

            if (method_exists($controller, $method)) {
                return $controller->$method($params);
            }
        }

        throw new \RuntimeException('Invalid route handler');
    }

    /**
     * Get request path from URI
     */
    private function getRequestPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        // Remove /backend/index.php from the path if present (for XAMPP setup)
        if (strpos($path, '/backend/index.php') === 0) {
            $path = substr($path, strlen('/backend/index.php'));
            if (empty($path)) {
                $path = '/';
            }
        }

        // Remove /backend prefix if present (for direct backend access)
        if (strpos($path, '/backend/') === 0) {
            $path = substr($path, strlen('/backend'));
            if (empty($path)) {
                $path = '/';
            }
        }

        // Remove trailing slash except for root
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    /**
     * Send JSON response
     */
    private function sendResponse(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get request body as array
     */
    public static function getRequestBody(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    /**
     * Get request headers
     */
    public static function getRequestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        // Fallback for environments where getallheaders() is not available
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $header = ucwords(strtolower($header), '-');
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}
