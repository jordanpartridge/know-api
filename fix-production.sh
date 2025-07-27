#!/bin/bash

set -e

echo "🔧 Fixing Know API production deployment..."

# Generate APP_KEY if missing
if ! grep -q "APP_KEY=base64:" .env.production; then
    echo "Generating APP_KEY..."
    APP_KEY=$(php artisan key:generate --show)
    sed -i.bak "s/APP_KEY=/APP_KEY=$APP_KEY/" .env.production
fi

# Set database password if missing
if ! grep -q "DB_PASSWORD=" .env.production || grep -q "DB_PASSWORD=$" .env.production; then
    echo "Setting DB_PASSWORD..."
    DB_PASS=$(openssl rand -base64 32 | tr -d "+/=")
    sed -i.bak "s/DB_PASSWORD=/DB_PASSWORD=$DB_PASS/" .env.production
fi

echo "✅ Production environment fixed"

# Build and deploy
echo "🚀 Building Docker image..."
docker build -t know-api .

echo "📦 Creating deployment package..."
tar czf know-api.tar.gz \
    docker-compose.yml \
    .env.production \
    docker/

# Deploy to EC2 instance
echo "🚀 Deploying to production..."
scp -o StrictHostKeyChecking=no know-api.tar.gz ubuntu@54.193.154.122:/tmp/
scp -o StrictHostKeyChecking=no -r docker/ ubuntu@54.193.154.122:/tmp/docker/

# Save Docker image and transfer
echo "💾 Transferring Docker image..."
docker save know-api | gzip > know-api-image.tar.gz
scp -o StrictHostKeyChecking=no know-api-image.tar.gz ubuntu@54.193.154.122:/tmp/

# Execute deployment on remote server
ssh -o StrictHostKeyChecking=no ubuntu@54.193.154.122 << 'EOF'
    set -e
    
    cd /tmp
    
    # Load Docker image
    echo "📥 Loading Docker image..."
    docker load < know-api-image.tar.gz
    
    # Stop existing containers
    echo "🛑 Stopping existing containers..."
    docker-compose down || true
    
    # Extract new configuration
    tar xzf know-api.tar.gz
    
    # Start new containers
    echo "🚀 Starting containers..."
    docker-compose up -d
    
    # Wait for containers to be ready
    echo "⏳ Waiting for containers..."
    sleep 10
    
    # Run migrations
    echo "🗃️ Running migrations..."
    docker-compose exec -T app php artisan migrate --force
    
    # Clear caches
    echo "🧹 Clearing caches..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    
    echo "✅ Deployment complete!"
EOF

# Cleanup local files
rm -f know-api.tar.gz know-api-image.tar.gz .env.production.bak

echo "🎉 Production deployment fixed and updated!"
echo "🌐 API available at: https://know.jordanpartridge.us"