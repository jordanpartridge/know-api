# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 API application called "Know" - a knowledge management system with authentication via Laravel Sanctum. The project uses:

- **Backend**: Laravel 12 with PHP 8.2+
- **Database**: SQLite (default), with PostgreSQL support configured
- **Authentication**: Laravel Sanctum for API token authentication  
- **Frontend Assets**: Vite with TailwindCSS 4.0
- **Testing**: PHPUnit with Feature and Unit test suites

## Architecture

### Core Structure
- **API Routes**: Versioned API (`/api/v1/`) with authentication endpoints in `routes/api.php`
- **Authentication**: Sanctum-based token authentication with stateful domain support
- **Database**: Uses SQLite by default with migrations for users, cache, jobs, and personal access tokens
- **Models**: Standard Laravel Eloquent models in `app/Models/`
- **Controllers**: API controllers in `app/Http/Controllers/`

### Key Patterns
- API endpoints are grouped under `/api/v1/` prefix
- Protected routes use `auth:sanctum` middleware
- Database uses SQLite for development, easily switchable to PostgreSQL
- Frontend assets processed through Vite with TailwindCSS

## Development Commands

### Primary Development
```bash
# Start full development environment (server, queue, logs, vite)
composer dev

# Alternative individual commands:
php artisan serve                    # Start development server
npm run dev                         # Start Vite development server
php artisan queue:listen --tries=1  # Start queue worker
php artisan pail --timeout=0       # Start log viewer
```

### Testing
```bash
# Run all tests
composer test
# Or manually:
php artisan test

# Run specific test suites
php artisan test tests/Feature/
php artisan test tests/Unit/

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

### Code Quality
```bash
# Laravel Pint (code formatting)
./vendor/bin/pint

# Clear application cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Reset and re-run migrations
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Asset Building
```bash
# Build assets for production
npm run build

# Development asset compilation
npm run dev
```

## API Structure

### Authentication Endpoints
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User login  
- `POST /api/v1/auth/logout` - User logout (protected)

### Protected Routes
All routes under `auth:sanctum` middleware require valid API tokens.

## Database Configuration

### SQLite (Default)
- Database file: `database/database.sqlite`
- Automatically created during setup

### PostgreSQL (Alternative)
Update `.env` file:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=know
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Environment Setup

1. Copy `.env.example` to `.env`
2. Generate application key: `php artisan key:generate`
3. Configure database settings in `.env`
4. Run migrations: `php artisan migrate`
5. Install dependencies: `composer install && npm install`

## Testing Configuration

Tests use in-memory SQLite database and array drivers for faster execution. PHPUnit configuration includes both Feature and Unit test suites with code coverage for the `app/` directory.