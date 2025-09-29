<?php

namespace App\Shared\Http\Middleware;

/**
 * CORS Middleware - Handles Cross-Origin Resource Sharing
 */
class CorsMiddleware
{
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;
    private bool $allowCredentials;
    private int $maxAge;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
        bool $allowCredentials = true,
        int $maxAge = 86400 // 24 hours
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowCredentials = $allowCredentials;
        $this->maxAge = $maxAge;
    }

    /**
     * Handle CORS middleware
     */
    public function handle(): ?array
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Set CORS headers
        $this->setCorsHeaders($origin);

        // Handle preflight OPTIONS request
        if ($method === 'OPTIONS') {
            return [
                'status' => 200,
                'data' => []
            ];
        }

        // Continue to next middleware/handler
        return null;
    }

    /**
     * Set CORS headers
     */
    private function setCorsHeaders(string $origin): void
    {
        // Set Access-Control-Allow-Origin
        if ($this->isOriginAllowed($origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        } elseif (in_array('*', $this->allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
        }

        // Set Access-Control-Allow-Methods
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));

        // Set Access-Control-Allow-Headers
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));

        // Set Access-Control-Allow-Credentials
        if ($this->allowCredentials) {
            header('Access-Control-Allow-Credentials: true');
        }

        // Set Access-Control-Max-Age for preflight caching
        header('Access-Control-Max-Age: ' . $this->maxAge);

        // Set additional headers for better compatibility
        header('Access-Control-Expose-Headers: Content-Length, X-JSON');
        header('Vary: Origin');
    }

    /**
     * Check if origin is allowed
     */
    private function isOriginAllowed(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        if (in_array('*', $this->allowedOrigins)) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins);
    }

    /**
     * Create CORS middleware with development settings
     */
    public static function forDevelopment(): self
    {
        return new self(
            allowedOrigins: [
                'http://localhost:3000',
                'http://127.0.0.1:3000',
                'http://localhost:8000',
                'http://fsj29.la-posada',
                'http://fsj29.la-posada:80'
            ],
            allowedMethods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
            allowedHeaders: [
                'Content-Type',
                'Authorization',
                'X-Requested-With',
                'Accept',
                'Origin',
                'Cache-Control',
                'X-File-Name'
            ],
            allowCredentials: true,
            maxAge: 3600 // 1 hour for development
        );
    }

    /**
     * Create CORS middleware with production settings
     */
    public static function forProduction(array $allowedOrigins): self
    {
        return new self(
            allowedOrigins: $allowedOrigins,
            allowedMethods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: [
                'Content-Type',
                'Authorization',
                'X-Requested-With'
            ],
            allowCredentials: true,
            maxAge: 86400 // 24 hours for production
        );
    }

    /**
     * Create CORS middleware with strict settings
     */
    public static function strict(array $allowedOrigins): self
    {
        return new self(
            allowedOrigins: $allowedOrigins,
            allowedMethods: ['GET', 'POST'],
            allowedHeaders: ['Content-Type', 'Authorization'],
            allowCredentials: false,
            maxAge: 3600
        );
    }

    /**
     * Add allowed origin
     */
    public function addAllowedOrigin(string $origin): self
    {
        if (!in_array($origin, $this->allowedOrigins)) {
            $this->allowedOrigins[] = $origin;
        }
        return $this;
    }

    /**
     * Add allowed method
     */
    public function addAllowedMethod(string $method): self
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->allowedMethods)) {
            $this->allowedMethods[] = $method;
        }
        return $this;
    }

    /**
     * Add allowed header
     */
    public function addAllowedHeader(string $header): self
    {
        if (!in_array($header, $this->allowedHeaders)) {
            $this->allowedHeaders[] = $header;
        }
        return $this;
    }

    /**
     * Set allowed origins
     */
    public function setAllowedOrigins(array $origins): self
    {
        $this->allowedOrigins = $origins;
        return $this;
    }

    /**
     * Set allowed methods
     */
    public function setAllowedMethods(array $methods): self
    {
        $this->allowedMethods = array_map('strtoupper', $methods);
        return $this;
    }

    /**
     * Set allowed headers
     */
    public function setAllowedHeaders(array $headers): self
    {
        $this->allowedHeaders = $headers;
        return $this;
    }

    /**
     * Set credentials policy
     */
    public function setAllowCredentials(bool $allow): self
    {
        $this->allowCredentials = $allow;
        return $this;
    }

    /**
     * Set max age for preflight caching
     */
    public function setMaxAge(int $seconds): self
    {
        $this->maxAge = $seconds;
        return $this;
    }

    /**
     * Get current CORS configuration
     */
    public function getConfiguration(): array
    {
        return [
            'allowed_origins' => $this->allowedOrigins,
            'allowed_methods' => $this->allowedMethods,
            'allowed_headers' => $this->allowedHeaders,
            'allow_credentials' => $this->allowCredentials,
            'max_age' => $this->maxAge
        ];
    }
}
