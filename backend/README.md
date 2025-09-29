# 🏨 Accommodation Management System API

A comprehensive REST API for managing accommodations and user accounts, built with PHP and featuring interactive Swagger UI documentation.

## 🚀 Quick Start with Docker

### Prerequisites
- Docker
- Docker Compose
- Existing database (configured in .env)

### Development Setup

1. **Navigate to backend directory**
   ```bash
   cd backend/
   ```

2. **Ensure your .env file is configured**
   ```env
   # Database Configuration
   DB_HOST=your-database-host
   DB_PORT=3306
   DB_NAME=your-database-name
   DB_USERNAME=your-username
   DB_PASSWORD=your-password

   # JWT Configuration
   JWT_SECRET=your-super-secret-jwt-key
   JWT_EXPIRATION=3600

   # Application Configuration
   APP_ENV=development
   APP_DEBUG=true
   ```

3. **Start the API service**
   ```bash
   docker-compose up -d
   ```

4. **Access the application**
   - **API Documentation**: http://localhost:8080/docs
   - **API Base URL**: http://localhost:8080/api
   - **Health Check**: http://localhost:8080/api/health

### Production Deployment

1. **Build production image**
   ```bash
   docker build -f Dockerfile.prod -t accommodation-api:prod .
   ```

2. **Run production container**
   ```bash
   docker run -d \
     --name accommodation-api \
     -p 80:80 \
     --env-file .env \
     accommodation-api:prod
   ```

## 📚 API Documentation

### Interactive Documentation
Visit http://localhost:8080/docs for the complete interactive API documentation powered by Swagger UI.

### Authentication
Most endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

### Quick Test
1. Visit http://localhost:8080/docs
2. Try the `/api/health` endpoint (no auth required)
3. Register a new user with `/api/auth/register`
4. Login with `/api/auth/login` to get your JWT token
5. Click "Authorize" and enter `Bearer YOUR_JWT_TOKEN`
6. Test the protected endpoints!

## 🛠️ Development

### Local Development (without Docker)
```bash
# Install dependencies
composer install

# Start PHP development server
php -S localhost:8000

# Access API at http://localhost:8000/api
# Access docs at http://localhost:8000/docs
```

### Docker Commands
```bash
# Build and start
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Rebuild after changes
docker-compose up -d --build
```

## 🏗️ Project Structure
```
backend/
├── src/                    # Source code
│   ├── Authentication/     # Auth controllers, services, middleware
│   ├── UserManagement/     # User management
│   ├── AccommodationManagement/ # Accommodation management
│   └── Shared/            # Shared utilities, database, HTTP
├── docs/                  # API documentation files
├── vendor/                # Composer dependencies
├── .env                   # Environment configuration
├── index.php              # Main entry point
├── Dockerfile             # Development Docker image
├── Dockerfile.prod        # Production Docker image
└── docker-compose.yml     # Docker Compose configuration
```

## 🔧 Configuration

The application uses your existing database configuration from the `.env` file. No additional database setup is required.

### Environment Variables
- `DB_HOST` - Database host
- `DB_PORT` - Database port (default: 3306)
- `DB_NAME` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password
- `JWT_SECRET` - Secret key for JWT tokens
- `JWT_EXPIRATION` - Token expiration time in seconds
- `APP_ENV` - Application environment (development/production)
- `APP_DEBUG` - Enable debug mode (true/false)

## 🚦 Health Check

The API includes a health check endpoint at `/api/health` that returns:
```json
{
  "message": "Accommodation Management System API is running",
  "timestamp": "2024-01-15 10:30:00",
  "version": "1.0.0"
}
```

## 📝 API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - User login
- `POST /api/auth/refresh` - Refresh JWT token
- `POST /api/auth/validate` - Validate JWT token

### Public Endpoints
- `GET /api/accommodations` - List accommodations (with filtering)
- `GET /api/accommodations/{id}` - Get specific accommodation

### User Endpoints (Authentication Required)
- `GET /api/users/accommodations` - Get user's accommodations
- `POST /api/users/accommodations` - Add accommodation to user account
- `DELETE /api/users/accommodations/{id}` - Remove accommodation from user account
- `GET /api/user/profile` - Get user profile

### Admin Endpoints (Admin Role Required)
- `POST /api/admin/accommodations` - Create new accommodation