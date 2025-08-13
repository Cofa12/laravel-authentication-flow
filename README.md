# Laravel Authentication Flow

A simple and flexible authentication package for Laravel applications with JWT support. This package provides a complete authentication flow with login, registration, token refresh, and logout functionality.

## Features

- JWT-based authentication
- Login and registration endpoints
- Token refresh mechanism
- Secure logout with token blacklisting
- Configurable token expiration times
- Flexible credential validation

## Requirements

- PHP 8.2 or higher
- Laravel 12.x
- Tymon JWT Auth package

## Installation

### 1. Install the package via Composer

```bash
composer require cofa/laravel-authentication-flow
```

### 2. Configuration (Optional)

The package comes with default configuration that will be automatically merged. If you want to customize the settings, publish the configuration file:

```bash
php artisan vendor:publish --provider="Cofa\LaravelAuthenticationFlow\ApiAuthServiceProvider" --tag="config"
```

This will create a `config/apiauth.php` file in your application that you can modify.

## JWT Configuration

### 1. JWT Setup

The `tymon/jwt-auth` package is automatically installed as a dependency. Laravel will handle the provider registration automatically.

### 2. Generate JWT Secret Key

Generate a secret key for JWT:

```bash
php artisan jwt:secret
```

This will update your `.env` file with a `JWT_SECRET` value.

### 3. Configure Auth Guard

Update your `config/auth.php` file to use the JWT guard:

```php
'guards' => [
    // ...
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### 4. Update User Model

Update your User model to implement the JWT interface:

```php
<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // ...

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

## Configuration Options

You can customize the authentication behavior by modifying the `config/apiauth.php` file:

```php
return [
    'token_ttl' => 60,         // Access token lifetime in minutes
    'refresh_ttl' => 10080,     // Refresh token lifetime in minutes (7 days)
    'guard' => 'api',           // The authentication guard to use
];
```

## Usage

The package provides the following API endpoints:

### Authentication Routes

All routes are prefixed with `/auth`:

- `POST /auth/login` - Login with credentials
- `POST /auth/register` - Register a new user
- `POST /auth/refresh-token` - Refresh access token
- `POST /auth/logout` - Logout and invalidate tokens
- `POST /auth/me` - Get authenticated user information

### Example Requests

#### Login

```http
POST /auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "YourPassword123!"
}
```

Response:

```json
{
    "profile": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "createdAt": "2023-01-01T00:00:00.000000Z",
        "updatedAt": "2023-01-01T00:00:00.000000Z"
    },
    "credentials": {
        "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expiresIn": "2023-01-01T01:00:00.000000Z",
        "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refreshExpiresIn": "2023-01-08T00:00:00.000000Z"
    }
}
```

#### Refresh Token

```http
POST /auth/refresh-token
Content-Type: application/json

{
    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

Response:

```json
{
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expiresIn": "2023-01-01T02:00:00.000000Z"
}
```

## Security

This package implements several security features:

- Password validation with complexity requirements
- Token blacklisting for logout
- Separate access and refresh tokens
- Configurable token lifetimes

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
