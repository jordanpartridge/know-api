#!/bin/bash

# Know API Deployment Script
set -e

echo "🚀 Starting Know API deployment..."

# Check if required environment variables are set
if [ -z "$DB_PASSWORD" ]; then
    echo "❌ Error: DB_PASSWORD environment variable is required"
    exit 1
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env.production; then
    echo "🔑 Generating application key..."
    php artisan key:generate --env=production --show >> .env.production
    sed -i 's/base64:/APP_KEY=base64:/' .env.production
fi

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose down --remove-orphans
docker-compose build --no-cache
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# Check application status
echo "🔍 Checking application health..."
sleep 5
if curl -f http://localhost/health; then
    echo "✅ Application is healthy!"
else
    echo "❌ Application health check failed"
    docker-compose logs app
    exit 1
fi

echo "🎉 Deployment completed successfully!"
echo "📍 API available at: https://know.jordanpartridge.us"