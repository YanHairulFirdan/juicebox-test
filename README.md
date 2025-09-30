# Juicebox Test Application

A Laravel-based web application featuring user authentication, post management, and weather service integration.

## Overview

This application demonstrates a modern Laravel setup with API authentication, resource management, and third-party service integration. It includes user registration/login functionality, post creation and management, and weather data retrieval capabilities.

### Key Features

-   **User Authentication**: Registration, login, and logout with Sanctum token-based authentication
-   **Post Management**: CRUD operations for user posts with policy-based authorization
-   **Weather Service**: Integration with WeatherAPI for current weather data
-   **Job Processing**: Background job system for weather data updates
-   **Notifications**: Welcome user notifications system
-   **Testing**: Comprehensive test coverage with PHPUnit

## Tech Stack

### Backend

-   **PHP 8.1+**
-   **Laravel 11.x** - Web application framework
-   **MySQL/SQLite** - Database
-   **Laravel Sanctum** - API token authentication
-   **Spatie Laravel Data** - Data transfer objects
-   **Guzzle HTTP** - HTTP client for external APIs

### Development Tools

-   **PHPUnit** - Testing framework
-   **Laravel Sail** - Docker development environment
-   **Composer** - PHP dependency management

## Dependencies

### Core Dependencies

```json
{
    "laravel/framework": "^11.0",
    "laravel/sanctum": "^4.0",
    "spatie/laravel-data": "^4.0",
    "guzzlehttp/guzzle": "^7.0"
}
```

### Development Dependencies

```json
{
    "phpunit/phpunit": "^11.0",
    "mockery/mockery": "^1.6",
    "laravel/sail": "^1.0"
}
```

## Installation

### Option 1: Using Laravel Sail (Recommended)

Laravel Sail provides a Docker-based development environment.

```bash
# Clone the repository
git clone <repository-url>
cd juicebox-test

# Install Composer dependencies
composer install

# Copy environment file
cp .env.example .env

# Start Sail containers
./vendor/bin/sail up -d

# Generate application key
./vendor/bin/sail artisan key:generate

# Run database migrations
./vendor/bin/sail artisan migrate

# Install NPM dependencies and build assetrun build

# (Optional) Seed the database
./vendor/bin/sail artisan db:seed
```

### Option 2: Manual Installation

For local development without Docker.

#### Prerequisites

-   PHP 8.1 or higher
-   Composer
-   Node.js & NPM
-   MySQL or SQLite

#### Steps

```bash
# Clone the repository
git clone <repository-url>
cd juicebox-test

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
# Configure your database credentials in .env
# For MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=juicebox_test
DB_USERNAME=root
DB_PASSWORD=

# For SQLite (simpler setup):
DB_CONNECTION=sqlite
# Create the database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start the development server
php artisan serve
```

## Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME="Juicebox Test"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=juicebox_test
DB_USERNAME=root
DB_PASSWORD=

# Weather Service
WEATHER_API_KEY=your_weatherapi_key
WEATHER_BASE_URI=https://api.weatherapi.com/v1

# Queue Configuration
QUEUE_CONNECTION=database
```

## Project Structure

```
app/
├── Http/Controllers/Api/     # API controllers
├── Http/Requests/           # Form request validation
├── Jobs/                    # Background jobs
├── Models/                  # Eloquent models
├── Notifications/           # User notifications
├── Policies/               # Authorization policies
├── Services/Weather/        # Weather service integration
└── Providers/              # Service providers

tests/
├── Feature/                # Feature tests
└── Unit/                   # Unit tests
```

### Weather Service Setup

1. Get an API key from [WeatherAPI](https://www.weatherapi.com/)
2. Add your API key to `.env`:
    ```env
    WEATHER_API_KEY=your_api_key_here
    ```

#### How to Add Alternative Weather Services

Here's a complete example of adding OpenWeatherMap as an alternative weather service:

**1. Create the Provider Class:**

```php
<?php
// app/Services/Weather/Providers/OpenWeatherMapApi.php

namespace App\Services\Weather\Providers;

use App\Services\Weather\Contracts\WeatherService;
use App\Services\Weather\Dtos\CurrentWeatherDto;
use Illuminate\Support\Facades\Http;

class OpenWeatherMapApi implements WeatherService
{
    public function getCurrentWeather(string $city = 'Perth'): CurrentWeatherDto
    {
        $config = config('weather-service');

        $response = Http::get($config['base_uri'] . '/weather', [
            'q' => $city,
            'appid' => $config['api_key'],
            'units' => 'metric'
        ]);

        $data = $response->json();

        return new CurrentWeatherDto(
            city: $data['name'],
            region: $data['sys']['state'] ?? $data['sys']['country'],
            country: $data['sys']['country'],
            temperature_c: $data['main']['temp'],
            temperature_f: ($data['main']['temp'] * 9/5) + 32,
            condition: $data['weather'][0]['description'],
            condition_icon: "https://openweathermap.org/img/w/{$data['weather'][0]['icon']}.png",
            feels_like_c: $data['main']['feels_like'],
            feels_like_f: ($data['main']['feels_like'] * 9/5) + 32,
            humidity: $data['main']['humidity'],
            wind_kph: ($data['wind']['speed'] ?? 0) * 3.6, // Convert m/s to km/h
            wind_dir: $this->degreeToDirection($data['wind']['deg'] ?? 0),
            uv_index: 0, // Requires separate API call in OpenWeatherMap
            visibility_km: ($data['visibility'] ?? 10000) / 1000,
            last_updated: now()->toISOString(),
            local_time: now()->toISOString(),
        );
    }

    private function degreeToDirection(int $degree): string
    {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                      'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        return $directions[round($degree / 22.5) % 16];
    }
}
```

**2. Update the Service Provider:**

Add the new provider to your `WeatherServiceProvider`:

```php
return match($serviceName) {
    'WeatherAPI' => new WeatherApi(),
    'OpenWeatherMap' => new OpenWeatherMapApi(), // Add this line
    default => throw new \Exception("Unsupported weather service: {$serviceName}")
};
```

**3. Environment Configuration:**

To switch to OpenWeatherMap, update your `.env`:

```env
# Switch to OpenWeatherMap
WEATHER_SERVICE_NAME=OpenWeatherMap
WEATHER_SERVICE_BASE_URI=https://api.openweathermap.org/data/2.5
WEATHER_SERVICE_API_KEY=your_openweathermap_api_key
```

**4. Clear Config Cache:**

```bash
./vendor/bin/sail artisan config:clear
```

Now your application will use OpenWeatherMap instead of WeatherAPI, with the same interface and data structure.

## API Documentation

### Postman Collection

For comprehensive API documentation and testing, use our Postman collection located in the project root:

**📁 `Juicebox-Test.postman_collection.json`**

#### How to Import the Collection

1. Open Postman
2. Click "Import" in the top left
3. Select "File" tab
4. Choose the `Juicebox-Test.postman_collection.json` file from the project root
5. Click "Import"

#### Collection Contents

The collection includes the following organized endpoints:

**🔐 User Authentication & Management:**

-   `POST /api/register` - User registration
-   `POST /api/login` - User login
-   `POST /api/logout` - User logout (requires auth)
-   `GET /api/users` - List all users with search & pagination
-   `GET /api/users/:id` - Get specific user details

**📝 Post Management:**

-   `GET /api/posts` - List posts with search functionality
-   `POST /api/posts` - Create new post (requires auth)
-   `GET /api/posts/:id` - Get specific post
-   `PATCH /api/posts/:id` - Update post (requires auth + ownership)
-   `DELETE /api/posts/:id` - Delete post (requires auth + ownership)

#### Environment Setup

After importing, configure your environment variables in Postman:

```json
{
    "base_url": "http://localhost:8000",
    "access_token": "your-token-here"
}
```

**For Laravel Sail users:**

```json
{
    "base_url": "http://localhost",
    "access_token": "your-token-here"
}
```

#### Authentication Flow

1. **Register**: Use the Register endpoint to create a new user
2. **Login**: Use the Login endpoint to get an access token
3. **Set Token**: Copy the `access_token` from the response and set it in your environment variables
4. **Test Protected Routes**: All other endpoints will automatically use the token via `{{access_token}}` variable

#### Example Responses

The collection includes sample responses for:

-   ✅ Successful operations
-   ❌ Validation errors (422)
-   🚫 Authentication errors (401)
-   🔍 Not found errors (404)
-   ⚠️ Server errors (500)

#### Features Included

-   **Pre-configured Variables**: Uses `{{base_url}}` and `{{access_token}}`
-   **Request Examples**: Sample request bodies for POST/PATCH operations
-   **Response Examples**: Multiple response scenarios for each endpoint
-   **Search & Pagination**: Query parameters for listing endpoints
-   **Proper Headers**: Content-Type and Accept headers pre-configured

## Testing

```bash
# Run all tests
./vendor/bin/sail artisan test
# OR for manual installation:
php artisan test

# Run specific test suite
./vendor/bin/sail artisan test --testsuite=Feature

# Run with coverage
./vendor/bin/sail artisan test --coverage
```

### Test Structure

-   **Feature Tests**: End-to-end API testing
-   **Unit Tests**: Individual component testing
-   **Database**: Uses SQLite in-memory database for testing

## Development

### Queue Workers

For background job processing:

```bash
# Start queue worker
./vendor/bin/sail artisan queue:work
```

### Custom Artisan Commands

#### Send Welcome Email Notifications

Send welcome email notifications to users using the custom Artisan command:

```bash
# Send welcome email to a single user
./vendor/bin/sail artisan send:welcome-email john@example.com

# Send welcome emails to multiple users (comma-separated)
./vendor/bin/sail artisan send:welcome-email john@example.com,jane@example.com,admin@example.com

# Send welcome emails to users from a list
./vendor/bin/sail artisan send:welcome-email user1@mail.com,user2@mail.com,user3@mail.com

# For manual installation (without Sail):
php artisan send:welcome-email john@example.com
php artisan send:welcome-email john@example.com,jane@example.com
```

**Command Signature:**

```bash
send:welcome-email emails={emails*}
```

**Parameters:**

-   `emails` - Email addresses of users to send welcome notifications to (comma-separated for multiple users)

**Examples:**

```bash
# Single user
./vendor/bin/sail artisan send:welcome-email test@mail.com

# Multiple users
./vendor/bin/sail artisan send:welcome-email test@mail.com,admin@mail.com,user@mail.com

# Using quotes for better shell handling
./vendor/bin/sail artisan send:welcome-email "user1@example.com,user2@example.com"
```

**Command Output:**

```
Welcome email sent to john@example.com
Welcome email sent to jane@example.com
Welcome email sent to admin@example.com
```

**Important Notes:**

-   Only existing users in the database will receive notifications
-   Non-existent email addresses will be silently ignored
-   The command uses lazy loading for memory efficiency with large user lists
-   Users must exist in the `users` table to receive notifications

**Use Cases:**

-   Send welcome emails to newly imported users
-   Re-send welcome emails to specific users
-   Bulk welcome email campaigns for user segments

### Database Operations

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Run the test suite
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
