# Know API

A Laravel-based knowledge management API for capturing, organizing, and searching development knowledge with git context tracking.

[![CI](https://github.com/jordanpartridge/know-api/workflows/CI/badge.svg)](https://github.com/jordanpartridge/know-api/actions)
[![Tests](https://img.shields.io/badge/tests-22%20passed-green)](https://github.com/jordanpartridge/know-api)

## Features

- 🔐 **User Authentication** - Sanctum-based API authentication with activation system
- 📚 **Knowledge Management** - Full CRUD operations for knowledge entries
- 🏷️ **Tagging System** - Organize knowledge with many-to-many tag relationships
- 🔍 **Search Functionality** - Fulltext search across titles, content, and summaries
- 🌐 **Git Context Tracking** - Automatically capture repository and branch information
- 🔒 **Permission System** - Public/private knowledge with proper access control
- ⚡ **Rate Limiting** - Authentication endpoint protection
- 🧪 **Comprehensive Testing** - 22 tests with 61 assertions

## API Endpoints

### Authentication
```
POST /api/v1/auth/register     # User registration (rate limited: 5/min)
POST /api/v1/auth/login        # User login (rate limited: 5/min)
POST /api/v1/auth/logout       # Logout and revoke token
```

### User Management
```
GET /api/user                  # Get authenticated user info
```

### Knowledge Management
```
GET    /api/v1/knowledge              # List user's knowledge entries
POST   /api/v1/knowledge              # Create new knowledge entry
GET    /api/v1/knowledge/{id}         # View specific knowledge entry
PUT    /api/v1/knowledge/{id}         # Update knowledge entry
DELETE /api/v1/knowledge/{id}         # Delete knowledge entry
```

### Tags & Search
```
GET /api/v1/tags                      # List all available tags
GET /api/v1/search/knowledge?q=term   # Search knowledge by content
```

## Installation

### Requirements
- PHP 8.2+
- PostgreSQL 15+
- Composer

### Setup
```bash
# Clone repository
git clone https://github.com/jordanpartridge/know-api.git
cd know-api

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Start development server
php artisan serve
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test tests/Feature/KnowledgeApiTest.php

# Code quality
./vendor/bin/pint              # Code formatting
./vendor/bin/phpstan analyse   # Static analysis
```

## Usage Examples

### Authentication
```bash
# Register new user
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password"
  }'
```

### Creating Knowledge
```bash
# Create knowledge entry with tags
curl -X POST http://localhost:8000/api/v1/knowledge \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Laravel Sanctum Setup",
    "content": "Step-by-step guide for setting up Laravel Sanctum authentication...",
    "type": "solution",
    "tag_ids": [1, 2],
    "is_public": false,
    "git_context": {
      "repository_name": "my-project",
      "branch_name": "feature/auth",
      "commit_hash": "abc123"
    }
  }'
```

### Searching Knowledge
```bash
# Search across all accessible knowledge
curl -X GET "http://localhost:8000/api/v1/search/knowledge?q=Laravel" \
  -H "Authorization: Bearer {your-token}"
```

## Architecture

### Models
- **User** - User accounts with activation system
- **Knowledge** - Core knowledge entries with content and metadata
- **Tag** - Tagging system with slug-based routing
- **GitContext** - Repository and branch tracking information

### Controllers (Single-Action Pattern)
- **Knowledge\\IndexController** - List knowledge entries
- **Knowledge\\StoreController** - Create knowledge entry
- **Knowledge\\ShowController** - View specific entry
- **Knowledge\\UpdateController** - Update entry
- **Knowledge\\DestroyController** - Delete entry
- **Knowledge\\SearchController** - Search functionality
- **TagController** - List tags

### Database Schema
```sql
-- Core tables
knowledge        # Main knowledge entries
tags            # Tag management with colors
git_contexts    # Repository/branch tracking
knowledge_tag   # Many-to-many pivot table
users          # User accounts with activation
```

## Development Commands

```bash
# User activation (interactive)
php artisan app:activate-user

# Code quality
composer quality     # Run all quality checks
./vendor/bin/pint   # Format code
./vendor/bin/phpstan analyse --memory-limit=256M  # Static analysis

# Testing
php artisan test --coverage  # Run with coverage
```

## Configuration

### Environment Variables
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=know_api
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Add to .env for production
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
```

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Follow coding standards (run `./vendor/bin/pint`)
4. Write tests for new functionality
5. Ensure all tests pass (`php artisan test`)
6. Commit changes (`git commit -m 'Add amazing feature'`)
7. Push to branch (`git push origin feature/amazing-feature`)
8. Open Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

Built with ❤️ using Laravel 12 and modern development practices.